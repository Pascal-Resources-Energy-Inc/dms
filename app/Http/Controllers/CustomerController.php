<?php

namespace App\Http\Controllers;
use App\Stove;
use App\User;
use App\TransactionDetail;
use Illuminate\Http\Request;
use App\Client;
use App\Center;
use RealRashid\SweetAlert\Facades\Alert;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    //
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->role === 'Admin';
        $centers = Center::get();
        $stoves = Stove::where('client_id',null)->get();
        $adminCrmCustomers = collect();
        $adminCrm2Customers = collect();
        $regularCustomers = collect();

        if ($isAdmin) {
            $adminCrmCustomers = $this->crmClients('admin_crms', 'Project Rise');
            $adminCrm2Customers = $this->crmClients('admin_crms2', 'Project Genesis');
            $regularCustomers = Client::with(['transactions', 'serial'])
                ->get()
                ->map(function ($customer) {
                    $customer->source = 'Regular';
                    $customer->source_label = 'Regular';
                    $this->applyLocalCustomerPoints($customer);

                    return $customer;
                });

            $customers = $adminCrmCustomers
                ->merge($adminCrm2Customers)
                ->merge($regularCustomers)
                ->values();
        } else {
            $customers = Client::with(['transactions', 'serial'])->get()
                ->map(function ($customer) {
                    $this->applyLocalCustomerPoints($customer);

                    return $customer;
                });
        }
        
        $activeCustomers = $customers->filter(function ($customer) {
            return strcasecmp((string) $customer->status, 'Active') === 0;
        })->count();

        $inactiveCustomers = $customers->filter(function ($customer) {
            return strcasecmp((string) $customer->status, 'Inactive') === 0;
        })->count();

        return view('customers',
            array(
                'stoves' => $stoves,
                'customers' => $customers,
                'adminCrmCustomers' => $adminCrmCustomers,
                'adminCrm2Customers' => $adminCrm2Customers,
                'regularCustomers' => $regularCustomers,
                'centers' => $centers,
                'activeCustomers' => $activeCustomers,
                'inactiveCustomers' => $inactiveCustomers
            )
        );
    }

    private function crmClients($connection, $label)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();
            $table = $this->crmClientTable($connection);

            if (! $table) {
                return collect();
            }

            $query = DB::connection($connection)->table($table)
                ->select($table . '.*');

            if (
                $schema->hasTable('stoves') &&
                $schema->hasColumn($table, 'serial_number') &&
                $schema->hasColumn('stoves', 'id') &&
                $schema->hasColumn('stoves', 'serial_number')
            ) {
                $stoveSelects = [
                    'stoves.serial_number as stove_serial_number',
                ];

                if ($schema->hasColumn('stoves', 'remarks')) {
                    $stoveSelects[] = 'stoves.remarks as stove_remarks';
                }

                $query->leftJoin('stoves', $table . '.serial_number', '=', 'stoves.id')
                    ->addSelect($stoveSelects);
            }

            if ($schema->hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if ($schema->hasColumn($table, 'id')) {
                $query->orderByDesc('id');
            } elseif ($schema->hasColumn($table, 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->get()->map(function ($client) use ($connection, $label) {
                $client = $this->normalizeCrmClient($client, $connection, $label);
                $this->applyCrmCustomerPoints($client, $connection);

                return $client;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function crmClientTable($connection)
    {
        $schema = DB::connection($connection)->getSchemaBuilder();

        if ($schema->hasTable('clients')) {
            return 'clients';
        }

        if ($schema->hasTable('customers')) {
            return 'customers';
        }

        return null;
    }

    private function normalizeCrmClient($client, $connection, $label)
    {
        $location = collect([
            $client->street_address ?? null,
            $client->location_barangay ?? null,
            $client->location_city ?? null,
            $client->location_province ?? null,
            $client->location_region ?? null,
        ])->filter()->implode(', ');

        $client->source = $connection;
        $client->source_label = $label;
        $client->client_reference = $client->client_reference ?? ($client->customer_reference ?? ('CRM-' . ($client->id ?? '')));
        $client->name = $client->name ?? trim(collect([
            $client->first_name ?? null,
            $client->middle_name ?? null,
            $client->last_name ?? null,
        ])->filter()->implode(' '));
        $client->name = $client->name ?: '-';
        $client->number = $client->number ?? ($client->phone_number ?? ($client->contact_number ?? '-'));
        $client->email_address = $client->email_address ?? ($client->email ?? '-');
        $client->facebook = $client->facebook ?? '-';
        $client->avatar = $client->avatar ?? null;
        $client->street_address = $client->street_address ?? null;
        $client->location_region = $client->location_region ?? null;
        $client->location_province = $client->location_province ?? null;
        $client->location_city = $client->location_city ?? null;
        $client->location_barangay = $client->location_barangay ?? null;
        $client->postal_code = $client->postal_code ?? null;
        $client->address = $client->address ?? ($location ?: '-');
        $client->center = $client->center ?? '-';
        $client->spo = $client->spo ?? '-';
        $client->status = ucfirst(strtolower((string) ($client->status ?? 'Active')));
        $client->valid_id = $client->valid_id ?? null;
        $client->valid_id_number = $client->valid_id_number ?? null;
        $client->valid_file = $client->valid_file ?? null;
        $client->signature = $client->signature ?? null;
        $client->user = null;
        $client->transactions = collect();
        $client->total_points = (float) ($client->total_points ?? 0);
        $client->serial = (object) [
            'serial_number' => $client->stove_serial_number ?? ($client->serial_number ?? ($client->serial ?? null)),
            'remarks' => $client->stove_remarks ?? null,
        ];
        $client->serial_number = $client->serial->serial_number ?: '-';

        return $client;
    }

    private function applyLocalCustomerPoints($customer)
    {
        $customer->total_points = (float) $customer->transactions->sum('points_client');
    }

    private function applyCrmCustomerPoints($customer, $connection)
    {
        $totalPoints = $this->crmCustomerPointsSum($connection, $customer);

        $customer->total_points = $totalPoints;
        $customer->transactions = collect([(object) ['points_client' => $totalPoints]]);
    }

    private function crmCustomerPointsSum($connection, $customer)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('transaction_details')) {
                return 0;
            }

            $pointsColumn = collect(['points_client', 'customer_points', 'points', 'total_points'])
                ->first(function ($column) use ($schema) {
                    return $schema->hasColumn('transaction_details', $column);
                });

            if (!$pointsColumn) {
                return 0;
            }

            $customerIds = collect([
                    $customer->id ?? null,
                    $customer->client_id ?? null,
                    $customer->customer_id ?? null,
                    $customer->user_id ?? null,
                ])
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->unique()
                ->values()
                ->all();

            if (empty($customerIds)) {
                return 0;
            }

            $query = DB::connection($connection)->table('transaction_details');

            $idColumns = collect(['client_id', 'customer_id', 'user_id'])
                ->filter(function ($column) use ($schema) {
                    return $schema->hasColumn('transaction_details', $column);
                })
                ->values();

            if ($idColumns->isEmpty()) {
                return 0;
            }

            $query->where(function ($inner) use ($idColumns, $customerIds) {
                foreach ($idColumns as $index => $column) {
                    if ($index === 0) {
                        $inner->whereIn($column, $customerIds);
                    } else {
                        $inner->orWhereIn($column, $customerIds);
                    }
                }
            });

            if ($schema->hasColumn('transaction_details', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            return (float) $query->sum($pointsColumn);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    private function crmClientTransactions($connection, $customer)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (! $schema->hasTable('transaction_details')) {
                return collect();
            }

            $query = DB::connection($connection)->table('transaction_details');
            $customerIds = collect([$customer->id ?? null, $customer->user_id ?? null])
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($schema->hasColumn('transaction_details', 'client_id')) {
                $query->whereIn('client_id', $customerIds);
            } elseif ($schema->hasColumn('transaction_details', 'customer_id')) {
                $query->whereIn('customer_id', $customerIds);
            } elseif ($schema->hasColumn('transaction_details', 'user_id')) {
                $query->whereIn('user_id', $customerIds);
            } else {
                return collect();
            }

            if ($schema->hasColumn('transaction_details', 'id')) {
                $query->orderByDesc('id');
            } elseif ($schema->hasColumn('transaction_details', 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->get()->map(function ($transaction) {
                $qty = (float) ($transaction->qty ?? $transaction->quantity ?? 0);
                $amount = (float) ($transaction->amount ?? $transaction->total_amount ?? 0);
                $price = $transaction->price ?? ($qty > 0 && $amount > 0 ? $amount / $qty : 0);

                $transaction->item = $transaction->item ?? ($transaction->product ?? ($transaction->product_name ?? '-'));
                $transaction->qty = $qty;
                $transaction->points_client = $transaction->points_client ?? ($transaction->customer_points ?? ($transaction->points ?? ($transaction->total_points ?? 0)));
                $transaction->price = $price;
                $transaction->created_at = $transaction->created_at ?? ($transaction->date ?? now());

                return $transaction;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    public function viewAdminCrmCustomer(Request $request, $source, $id)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $connections = [
            'admin_crms' => 'Project Rise',
            'admin_crms2' => 'Project Genesis',
        ];

        abort_unless(array_key_exists($source, $connections), 404);

        $table = $this->crmClientTable($source);
        abort_unless($table, 404);

        $schema = DB::connection($source)->getSchemaBuilder();
        $query = DB::connection($source)->table($table)
            ->select($table . '.*')
            ->where($table . '.id', $id);

        if (
            $schema->hasTable('stoves') &&
            $schema->hasColumn($table, 'serial_number') &&
            $schema->hasColumn('stoves', 'id') &&
            $schema->hasColumn('stoves', 'serial_number')
        ) {
            $stoveSelects = [
                'stoves.serial_number as stove_serial_number',
            ];

            if ($schema->hasColumn('stoves', 'remarks')) {
                $stoveSelects[] = 'stoves.remarks as stove_remarks';
            }

            $query->leftJoin('stoves', $table . '.serial_number', '=', 'stoves.id')
                ->addSelect($stoveSelects);
        }

        if ($schema->hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $customer = $query->first();
        abort_unless($customer, 404);

        $customer = $this->normalizeCrmClient($customer, $source, $connections[$source]);
        $transactions = $this->crmClientTransactions($source, $customer);

        return view('customer', [
            'customer' => $customer,
            'transactions' => $transactions,
            'centers' => collect(),
            'stoves' => collect(),
            'isRemoteCustomer' => true,
        ]);
    }

    public function view(Request $request,$id)
    {
        $transactions = TransactionDetail::where('client_id',$id)->orderBy('id','desc')->get();
        $customer = Client::with(['user', 'serial'])->findOrfail($id);
        $centers = Center::get();
        $stoves = Stove::whereNull('client_id')
            ->orWhere('client_id', $customer->id)
            ->get();

        return view('customer',
            array(
                'customer' => $customer,
                'transactions' => $transactions,
                'centers' => $centers,
                'stoves' => $stoves,
                'isRemoteCustomer' => false,
                
            )
        );
    }
    public function show(Request $request)
    {
        return view('customer-dashboard');
    }
    public function newCustomer(Request $request)
    {
        $stoves = Stove::where('client_id',null)->get();
        return view('new-customer',
            array(
                'stoves' => $stoves
            )
        );
    }

    public function saveCustomer(Request $request)
    {
        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        $user = new User;
        // $user->name = $fullName;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email_address;
        $user->role = 'Client';
        $user->birthdate = $request->birthdate;
        $user->age = $request->age;
        $user->password = bcrypt('12345678');
        $user->save();

        // Generate Client Reference
        $latestClient = Client::orderBy('id', 'desc')->first();

        if ($latestClient && $latestClient->client_reference) {
            $number = intval(substr($latestClient->client_reference, 3)) + 1;
        } else {
            $number = 1;
        }

        $client_reference = 'PRC' . str_pad($number, 5, '0', STR_PAD_LEFT);

        $customer = new Client;
        $customer->client_reference = $client_reference;
        $customer->user_id = $user->id;
        $customer->name = $fullName;
        $customer->email_address = $request->email_address;
        $customer->number = $request->phone_number;
        $customer->facebook = $request->facebook;
        $customer->address = $request->address;
        $customer->serial_number = $request->serial_number;
        $customer->location_region = $request->location_region;
        $customer->location_province = $request->location_province;
        $customer->location_city = $request->location_city;
        $customer->location_barangay = $request->location_barangay;
        $customer->postal_code = $request->postal_code;
        $customer->street_address = $request->street_address;
        $customer->spo = $request->spo;
        $customer->center = $request->center;
        $customer->status = $request->status;
        if (Schema::hasColumn('clients', 'latitude')) {
            $customer->latitude = $request->latitude;
        }
        if (Schema::hasColumn('clients', 'longitude')) {
            $customer->longitude = $request->longitude;
        }
        $customer->save();

        $serial_number = Stove::findOrfail($request->serial_number);
        $serial_number->client_id = $customer->id;
        $serial_number->save();


        Alert::success('Successfully encoded')->persistent('Dismiss');
        return redirect('view-client/' . $customer->id);
    }

    public function update(Request $request, $id)
    {
        $customer = Client::findOrFail($id);

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        if ($customer->user_id) {
            User::where('id', $customer->user_id)->update([
                'name' => $fullName ?: $customer->name,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email_address,
                'birthdate' => $request->birthdate,
                'age' => $request->age,
            ]);
        }

        $newSerialId = $request->serial_number;
        if ($newSerialId && (int) $newSerialId !== (int) $customer->serial_number) {
            if ($customer->serial_number) {
                $oldSerial = Stove::find($customer->serial_number);
                if ($oldSerial && (int) $oldSerial->client_id === (int) $customer->id) {
                    $oldSerial->client_id = null;
                    $oldSerial->save();
                }
            }

            $newSerial = Stove::findOrFail($newSerialId);
            $newSerial->client_id = $customer->id;
            $newSerial->save();
            $customer->serial_number = $newSerialId;
        }

        $customer->name = $fullName ?: $customer->name;
        $customer->email_address = $request->email_address;
        $customer->number = $request->number;
        $customer->facebook = $request->facebook;
        $customer->address = $request->address;
        $customer->location_region = $request->location_region;
        $customer->location_province = $request->location_province;
        $customer->location_city = $request->location_city;
        $customer->location_barangay = $request->location_barangay;
        $customer->postal_code = $request->postal_code;
        $customer->street_address = $request->street_address;
        $customer->spo = $request->spo;
        $customer->center = $request->center;
        $customer->status = $request->status;
        if (Schema::hasColumn('clients', 'latitude')) {
            $customer->latitude = $request->latitude;
        }
        if (Schema::hasColumn('clients', 'longitude')) {
            $customer->longitude = $request->longitude;
        }
        $customer->save();

        Alert::success('Success', 'Customer updated successfully!')->persistent('Dismiss');
        return redirect()->back();
    }
    
    public function changeAvatar(Request $request, $id)
    {
        $customer = Client::findOrfail($id);
        
        $imageData = $request->image_data;
        
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageType = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        } else {
            Alert::error('Invalid image format')->persistent('Dismiss');
            return back();
        }
        
        $imageData = base64_decode($imageData);
        
        if ($imageData === false) {
            Alert::error('Failed to decode image')->persistent('Dismiss');
            return back();
        }
        
        $directory = public_path('avatar-client');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fileName = 'avatar_' . $customer->id . '_' . time() . '.png';
        $filePath = $directory . '/' . $fileName;
        
        if (file_put_contents($filePath, $imageData)) {
            if ($customer->avatar && 
                $customer->avatar !== url('design/assets/images/profile/user-1.png') && 
                file_exists(public_path(str_replace(url('/'), '', $customer->avatar)))) {
                unlink(public_path(str_replace(url('/'), '', $customer->avatar)));
            }
            
            $customer->avatar = 'avatar-client/' . $fileName;
            $customer->save();
            
            Alert::success('Successfully Uploaded')->persistent('Dismiss');
        } else {
            Alert::error('Failed to save image')->persistent('Dismiss');
        }
        
        return back();
    }
    public function uploadValidId(Request $request,$id)
    {
        // dd($request->all());
        $customer = Client::findOrfail($id);
        $customer->valid_id = $request->valid_id_type;
        $customer->valid_id_number = $request->id_number;

        $attachment = $request->file('id_file');
        $original_name = $attachment->getClientOriginalName();
        $name = time().'_'.$attachment->getClientOriginalName();
        $attachment->move(public_path().'/valid_ids/', $name);
        $file_name = '/valid_ids/'.$name;

        $customer->valid_file = $file_name;
        $customer->save();

        Alert::success('Successfully Uploaded')->persistent('Dismiss');
        return back();
    }
    public function contractSign(Request $request,$id)
    {
        // dd($request->all());

        $customer = Client::findOrfail($id);

        $attachment = $request->file('contract_signature');
        $original_name = $attachment->getClientOriginalName();
        $name = time().'_'.$attachment->getClientOriginalName();
        $attachment->move(public_path().'/signatures/', $name);
        $file_name = '/signatures/'.$name;
        $customer->signature = $file_name;

        $customer->save();

        Alert::success('Successfully Uploaded')->persistent('Dismiss');
       return redirect()->to('view-client/' . $customer->id);
    }

  public function getUser($id)
{
   $serials = Stove::where('serial_number', 'like', '%' . $id . '%')->first();
   if($serials)
   {
   $client = Client::findOrfail($serials->client_id);
    $user = User::find($client->user_id);

    if ($user) {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $client->id,
                'name' => $user->name
            ]
        ]);
    } else {
        return response()->json(['success' => false], 404);
    }
       }
       else

       {
         return response()->json(['success' => false], 404);
       }
       
}
    public function sign($id)
    {
        $customer = Client::findOrfail($id);

        return view('signature',
        array(
        'customer' => $customer
        ));
    }

    public function regions()
    {
        try {
            return response()->json($this->psgcGet('regions'));
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function provinces($region)
    {
        try {
            $regionCode = $this->resolvePsgcCode('regions', $region);

            return response()->json($this->psgcGet("regions/{$regionCode}/provinces"));
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function regionCities($region)
    {
        try {
            $regionCode = $this->resolvePsgcCode('regions', $region);

            return response()->json($this->psgcGet("regions/{$regionCode}/cities-municipalities"));
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function cities($province)
    {
        try {
            $provinceCode = $this->resolvePsgcCode('provinces', $province);

            return response()->json($this->psgcGet("provinces/{$provinceCode}/cities-municipalities"));
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function barangays($city)
    {
        try {
            $cityCode = $this->resolvePsgcCode('cities-municipalities', $city);

            return response()->json($this->psgcGet("cities-municipalities/{$cityCode}/barangays"));
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    private function psgcGet($path)
    {
        $client = new GuzzleClient([
            'base_uri' => 'https://psgc.cloud/api/',
            'timeout' => 10,
        ]);

        $response = $client->get($path);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function resolvePsgcCode($endpoint, $value)
    {
        if (preg_match('/^\d+$/', $value)) {
            return $value;
        }

        $normalize = function ($text) {
            $text = preg_replace('/\s+/', ' ', trim((string) $text));
            $text = preg_replace('/^(city|municipality)\s+of\s+/i', '', $text);

            return mb_strtolower($text);
        };

        $normalizedValue = $normalize($value);
        $items = $this->psgcGet($endpoint);
        $match = collect($items)->first(function ($item) use ($normalizedValue, $normalize) {
            return $normalize($item['name'] ?? '') === $normalizedValue;
        });

        return $match['code'] ?? $value;
    }
}
