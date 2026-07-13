<?php

namespace App\Http\Controllers;
use RealRashid\SweetAlert\Facades\Alert;
use App\User;
use App\Dealer;
use App\Center;
use App\TransactionDetail;
use App\Item;
use App\Area;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DealerController extends Controller
{
    //
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->role === 'Admin';
        $items = Item::select('item')->get(); // master list of items
        $centers = Center::get();
        $areas = Area::with('areaAd.distributor')->get();
        
        $adminCrmDealers = collect();
        $adminCrm2Dealers = collect();
        $adminRegularDealers = collect();

        if ($isAdmin) {
            $adminCrmDealers = $this->crmDealers('admin_crms', 'Admin CRM 1');
            $adminCrm2Dealers = $this->crmDealers('admin_crms2', 'Admin CRM 2');
            $adminRegularDealers = Dealer::with(['user', 'orders', 'sales'])
                ->where(function ($query) {
                    $query->where('dealer_type', 'Regular')
                        ->orWhere('dealer_type', 'regular');
                })
                ->get()
                ->map(function ($dealer) {
                    $dealer->source = 'Regular';
                    $dealer->source_label = 'Regular';
                    $this->applyLocalDealerMetrics($dealer);

                    return $dealer;
                });
            $dealers = $adminCrmDealers
                ->merge($adminCrm2Dealers)
                ->merge($adminRegularDealers)
                ->values();
        } else {
            $dealers = Dealer::with(['user', 'orders', 'sales'])->get()
                ->map(function ($dealer) {
                    $this->applyLocalDealerMetrics($dealer);

                    return $dealer;
                });
        }

        $activeDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Active') === 0;
        })->count();

        $inactiveDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Inactive') === 0;
        })->count();

        return view('dealers',
            array(
                'dealers' => $dealers,
                'adminCrmDealers' => $adminCrmDealers,
                'adminCrm2Dealers' => $adminCrm2Dealers,
                'adminRegularDealers' => $adminRegularDealers,
                'activeDealers' => $activeDealers,
                'inactiveDealers' => $inactiveDealers,
                'items' => $items,
                'centers' => $centers,
                'areas' => $areas

            )
        );
    }

    private function crmDealers($connection, $label)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (! $schema->hasTable('dealers')) {
                return collect();
            }

            $query = DB::connection($connection)->table('dealers');

            if ($schema->hasColumn('dealers', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if ($schema->hasColumn('dealers', 'id')) {
                $query->orderByDesc('id');
            } elseif ($schema->hasColumn('dealers', 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->get()
                ->map(function ($dealer) use ($connection, $label) {
                    $dealer = $this->normalizeCrmDealer($dealer, $connection, $label);
                    $this->applyCrmDealerMetrics($dealer, $connection);

                    return $dealer;
                });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function normalizeCrmDealer($dealer, $connection, $label)
    {
        $location = collect([
            $dealer->street_address ?? null,
            $dealer->location_barangay ?? null,
            $dealer->location_city ?? null,
            $dealer->location_province ?? null,
            $dealer->location_region ?? null,
        ])->filter()->implode(', ');

        $dealer->source = $connection;
        $dealer->source_label = $label;
        $dealer->dealer_reference = $dealer->dealer_reference ?? ('CRM-' . ($dealer->id ?? ''));
        $dealer->dealer_type = $dealer->dealer_type ?? 'Project';
        $dealer->name = $dealer->name ?? '-';
        $dealer->avatar = $dealer->avatar ?? null;
        $dealer->store_name = $dealer->store_name ?? '-';
        $dealer->store_type = $dealer->store_type ?? '-';
        $dealer->email_address = $dealer->email_address ?? ($dealer->email ?? '-');
        $dealer->facebook = $dealer->facebook ?? '-';
        $dealer->location_region = $dealer->location_region ?? '-';
        $dealer->location_province = $dealer->location_province ?? '-';
        $dealer->location_city = $dealer->location_city ?? '-';
        $dealer->location_barangay = $dealer->location_barangay ?? '-';
        $dealer->number = $dealer->number ?? ($dealer->contact_number ?? '-');
        $dealer->address = $dealer->address ?? ($location ?: '-');
        $dealer->area = $dealer->area ?? ($dealer->sales_territory ?? '-');
        $dealer->status = ucfirst(strtolower((string) ($dealer->status ?? 'Active')));
        $dealer->valid_id = $dealer->valid_id ?? null;
        $dealer->valid_id_number = $dealer->valid_id_number ?? null;
        $dealer->signature = $dealer->signature ?? null;
        $dealer->orders = collect();
        $dealer->sales = collect();
        $dealer->stock_qty = (float) ($dealer->stock_qty ?? 0);
        $dealer->sold_qty = (float) ($dealer->sold_qty ?? 0);

        return $dealer;
    }

    private function applyLocalDealerMetrics($dealer)
    {
        $dealer->stock_qty = (float) $dealer->orders->sum('qty');
        $dealer->sold_qty = (float) $dealer->sales->sum('qty');
    }

    private function applyCrmDealerMetrics($dealer, $connection)
    {
        $stockQty = $this->crmDealerQtySum($connection, 'order_details', $dealer);
        $soldQty = $this->crmDealerQtySum($connection, 'transaction_details', $dealer);

        $dealer->stock_qty = $stockQty;
        $dealer->sold_qty = $soldQty;
        $dealer->orders = collect([(object) ['qty' => $stockQty]]);
        $dealer->sales = collect([(object) ['qty' => $soldQty]]);
    }

    private function crmDealerQtySum($connection, $table, $dealer)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable($table)) {
                return 0;
            }

            $qtyColumn = collect(['qty', 'quantity'])
                ->first(function ($column) use ($schema, $table) {
                    return $schema->hasColumn($table, $column);
                });

            if (!$qtyColumn) {
                return 0;
            }

            $dealerIds = collect([
                    $dealer->user_id ?? null,
                    $dealer->dealer_id ?? null,
                    $dealer->id ?? null,
                ])
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->unique()
                ->values()
                ->all();

            if (empty($dealerIds)) {
                return 0;
            }

            $query = DB::connection($connection)->table($table);

            if ($schema->hasColumn($table, 'dealer_id') && $schema->hasColumn($table, 'user_id')) {
                $query->where(function ($inner) use ($dealerIds) {
                    $inner->whereIn('dealer_id', $dealerIds)
                        ->orWhereIn('user_id', $dealerIds);
                });
            } elseif ($schema->hasColumn($table, 'dealer_id')) {
                $query->whereIn('dealer_id', $dealerIds);
            } elseif ($schema->hasColumn($table, 'user_id')) {
                $query->whereIn('user_id', $dealerIds);
            } else {
                return 0;
            }

            if ($schema->hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            return (float) $query->sum($qtyColumn);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    private function crmTransactions($connection, $dealer)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (! $schema->hasTable('transaction_details')) {
                return collect();
            }

            $query = DB::connection($connection)->table('transaction_details');
            $dealerIds = collect([$dealer->user_id ?? null, $dealer->id ?? null])->filter()->unique()->values()->all();

            if ($schema->hasColumn('transaction_details', 'dealer_id')) {
                $query->whereIn('dealer_id', $dealerIds);
            } elseif ($schema->hasColumn('transaction_details', 'user_id')) {
                $query->whereIn('user_id', $dealerIds);
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
                $transaction->points_client = $transaction->points_client ?? ($transaction->points ?? 0);
                $transaction->price = $price;
                $transaction->created_at = $transaction->created_at ?? now();

                return $transaction;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function paginateCollection($items, Request $request, $perPage = 10)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = collect($items);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    public function viewAdminCrmDealer(Request $request, $source, $id)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $connections = [
            'admin_crms' => 'Admin CRM 1',
            'admin_crms2' => 'Admin CRM 2',
        ];

        abort_unless(array_key_exists($source, $connections), 404);

        $schema = DB::connection($source)->getSchemaBuilder();
        abort_unless($schema->hasTable('dealers'), 404);

        $query = DB::connection($source)->table('dealers')->where('id', $id);

        if ($schema->hasColumn('dealers', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $dealer = $query->first();
        abort_unless($dealer, 404);

        $dealer = $this->normalizeCrmDealer($dealer, $source, $connections[$source]);
        $allTransactions = $this->crmTransactions($source, $dealer);
        $transactions = $this->paginateCollection($allTransactions, $request);
        $transactionStats = [
            'count' => $allTransactions->count(),
            'qty' => $allTransactions->sum('qty'),
            'amount' => $allTransactions->sum(function ($transaction) {
                return (float) $transaction->qty * (float) $transaction->price;
            }),
            'points' => $allTransactions->sum('points_client'),
        ];

        return view('dealer', [
            'dealer' => $dealer,
            'transactions' => $transactions,
            'transactionStats' => $transactionStats,
            'centers' => collect(),
            'areas' => collect(),
            'isRemoteDealer' => true,
        ]);
    }

    public function megaDealers(Request $request)
    {
        $activeDealers = Dealer::where('status', 'Active')
            ->whereHas('user', function ($q) {
                $q->where('role', 'Mega Dealer');
            })
            ->count();

        $inactiveDealers = Dealer::where('status', 'Inactive')
            ->whereHas('user', function ($q) {
                $q->where('role', 'Mega Dealer');
            })
            ->count();

        $items = Item::select('item')->get();
        $centers = Center::get();
        $areas = Area::with('areaAd.distributor')->get();
        $dealers = Dealer::with(['user', 'orders', 'sales'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'Mega Dealer');
            })
            ->get()
            ->map(function ($dealer) {
                $this->applyLocalDealerMetrics($dealer);

                return $dealer;
            });

        return view('dealers', [
            'dealers' => $dealers,
            'activeDealers' => $activeDealers,
            'inactiveDealers' => $inactiveDealers,
            'items' => $items,
            'centers' => $centers,
            'areas' => $areas,
            'dealerPageTitle' => 'Mega Dealers',
            'dealerSingularTitle' => 'Mega Dealer',
            'dealerRouteName' => 'mds',
        ]);
    }
    
    public function show(Request $request)
    {
        return view('dashboard-dealer');
    }

    public function newDealer(Request $request)
    {
        $dealerType = 'Regular';

        $request->validate([
            'dealer_type' => 'nullable|in:Regular',
            'spo' => 'nullable',
            'center' => 'nullable',
        ]);

        if ($this->dealerDuplicateExists(
            $request->first_name,
            $request->last_name,
            $request->mothers_name
        )) {
            $message = "Dealer with same First Name, Last Name, and Mother's Name already exists.";

            Alert::error('Duplicate dealer', $message)->persistent('Dismiss');

            return redirect()->back()
                ->withErrors(['dealer_duplicate' => $message])
                ->withInput();
        }

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        $user = new User;
        $user->name = $fullName;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->mothers_name = $request->mothers_name;
        $user->email = $request->email_address;
        $user->role = 'Dealer';
        $user->birthdate = $request->birthdate;
        $user->age = $request->age;
        $user->password = bcrypt('12345678');
        $user->save();

        $dealer = new Dealer;
        $dealer->user_id = $user->id;
        $dealer->dealer_reference = $this->nextDealerReference($dealerType);
        $dealer->name = $fullName;
        if (Schema::hasColumn('dealers', 'dealer_type')) {
            $dealer->dealer_type = $dealerType;
        }
        $dealer->spo = $dealerType === 'Project' ? $request->spo : null;
        $dealer->email_address = $request->email_address;
        $dealer->number = $request->number;
        $dealer->facebook = $request->facebook;
        $dealer->address = $request->address;
        $dealer->location_region = $request->location_region;
        $dealer->location_province = $request->location_province;
        $dealer->location_city = $request->location_city;
        $dealer->location_barangay = $request->location_barangay;
        if (Schema::hasColumn('dealers', 'postal_code')) {
            $dealer->postal_code = $request->postal_code;
        }
        if (Schema::hasColumn('dealers', 'street_address')) {
            $dealer->street_address = $request->street_address;
        }
        $dealer->store_name = $request->store_name;
        $dealer->store_type = $request->store_type;
        $dealer->center = $dealerType === 'Project' ? $request->center : null;
        $dealer->area = $request->area;
        $dealer->latitude = $request->latitude;
        $dealer->longitude = $request->longitude;
        $dealer->status = "Active";
        $dealer->save();
        

        Alert::success('Successfully encoded')->persistent('Dismiss');
        return redirect('view-dealer/' . $dealer->id);
    }

    public function checkDuplicate(Request $request)
    {
        $exists = $this->dealerDuplicateExists(
            $request->first_name,
            $request->last_name,
            $request->mothers_name
        );

        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? "Dealer with same First Name, Last Name, and Mother's Name already exists."
                : null,
        ]);
    }

    private function dealerDuplicateExists($firstName, $lastName, $mothersName)
    {
        $firstName = trim((string) $firstName);
        $lastName = trim((string) $lastName);
        $mothersName = trim((string) $mothersName);

        if (!$firstName || !$lastName || !$mothersName) {
            return false;
        }

        return User::where('role', 'Dealer')
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower($firstName)])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower($lastName)])
            ->whereRaw('LOWER(TRIM(mothers_name)) = ?', [mb_strtolower($mothersName)])
            ->exists();
    }

    public function view(Request $request,$id)
    {
        $dealer = Dealer::with('user')->findOrfail($id);
        $transactionQuery = TransactionDetail::where('dealer_id',$dealer->user_id);
        $transactionRows = (clone $transactionQuery)->get();
        $transactionStats = [
            'count' => $transactionRows->count(),
            'qty' => $transactionRows->sum('qty'),
            'amount' => $transactionRows->sum(function ($transaction) {
                return (float) $transaction->qty * (float) $transaction->price;
            }),
            'points' => $transactionRows->sum('points_client'),
        ];
        $transactions = $transactionQuery->orderBy('id','desc')->paginate(10)->appends($request->query());
        $centers = Center::get();
        $areas = Area::with('areaAd.distributor')->get();
        // dd($dealer);
        return view('dealer',
            array(
                'dealer' => $dealer,
                'transactions' => $transactions,
                'transactionStats' => $transactionStats,
                'centers' => $centers,
                'areas' => $areas,
            )
        );
    }
    public function changeAvatar(Request $request, $id)
    {
        $dealer = Dealer::findOrfail($id);
        
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
        
        $directory = public_path('avatar-dealer');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fileName = 'avatar_dealer_' . $dealer->id . '_' . time() . '.png';
        $filePath = $directory . '/' . $fileName;
        
        if (file_put_contents($filePath, $imageData)) {
            if ($dealer->avatar && 
                $dealer->avatar !== url('design/assets/images/profile/user-1.png') && 
                file_exists(public_path(str_replace(url('/'), '', $dealer->avatar)))) {
                unlink(public_path(str_replace(url('/'), '', $dealer->avatar)));
            }
            
            $dealer->avatar = 'avatar-dealer/' . $fileName;
            $dealer->save();
            
            Alert::success('Successfully Uploaded')->persistent('Dismiss');
        } else {
            Alert::error('Failed to save image')->persistent('Dismiss');
        }
        
        return back();
    }

    public function uploadValidId(Request $request,$id)
    {
        // dd($request->all());
        $customer = Dealer::findOrfail($id);
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

        $customer = Dealer::findOrfail($id);

        $attachment = $request->file('contract_signature');
        $original_name = $attachment->getClientOriginalName();
        $name = time().'_'.$attachment->getClientOriginalName();
        $attachment->move(public_path().'/signatures/', $name);
        $file_name = '/signatures/'.$name;
        $customer->signature = $file_name;

        $customer->save();

        Alert::success('Successfully Uploaded')->persistent('Dismiss');
        return redirect()->to('view-dealer/' . $customer->id);
    }

    public function sign($id)
    {
        $dealer = Dealer::findOrfail($id);

        return view('signature_dealer',
        array(
        'dealer' => $dealer
        ));
    }

    public function update(Request $request, $id)
    {
        $dealer = Dealer::findOrFail($id);
        $isAdmin = auth()->user()->role === 'Admin';
        $existingDealerType = Schema::hasColumn('dealers', 'dealer_type')
            ? ($dealer->dealer_type ?: 'Project')
            : 'Project';
        $dealerType = $isAdmin
            ? (strtolower((string) $request->dealer_type) === 'regular' ? 'Regular' : 'Project')
            : $existingDealerType;

        $request->validate([
            'dealer_type' => $isAdmin ? 'required|in:Project,Regular' : 'nullable',
            'spo' => $dealerType === 'Project' ? 'required|string|max:255' : 'nullable',
            'center' => $dealerType === 'Project' ? 'required|string|max:255' : 'nullable',
        ]);

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        if ($dealer->user_id) {

            User::where('id', $dealer->user_id)->update([
                'name' => $fullName ?: $request->name,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
            ]);
        }

        $dealer->name = $fullName ?: $request->name;
        $dealer->number = $request->number;
        $dealer->address = $request->address;
        $dealer->street_address = $request->street_address;
        $dealer->store_name = $request->store_name;
        $dealer->store_type = $request->store_type;
        if ($isAdmin && strcasecmp($existingDealerType, $dealerType) !== 0) {
            $dealer->dealer_reference = $this->nextDealerReference($dealerType);
        }
        if (Schema::hasColumn('dealers', 'dealer_type')) {
            $dealer->dealer_type = $dealerType;
        }
        $dealer->spo = $dealerType === 'Project' ? $request->spo : null;
        $dealer->facebook = $request->facebook;
        $dealer->email_address = $request->email_address;
        $dealer->location_region = $request->location_region;
        $dealer->location_province = $request->location_province;
        $dealer->location_city = $request->location_city;
        $dealer->location_barangay = $request->location_barangay;
        $dealer->postal_code = $request->postal_code;
        $dealer->center = $dealerType === 'Project' ? $request->center : null;
        $dealer->area = $request->area;
        $dealer->latitude = $request->latitude;
        $dealer->longitude = $request->longitude;

        $dealer->save();

        Alert::success('Success', 'Dealer updated successfully!');
        return redirect()->back();
    }

    private function nextDealerReference($dealerType)
    {
        if (strcasecmp((string) $dealerType, 'Regular') === 0) {
            $year = date('Y');
            $prefix = 'DL' . $year;
            $padding = 4;
        } else {
            $prefix = 'PRD';
            $padding = 5;
        }

        $latestSequence = Dealer::where('dealer_reference', 'like', $prefix . '%')
            ->pluck('dealer_reference')
            ->map(function ($reference) use ($prefix) {
                $suffix = substr(strtoupper(trim((string) $reference)), strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?: 0;

        return $prefix . str_pad($latestSequence + 1, $padding, '0', STR_PAD_LEFT);
    }

    public function getZipCode1(Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'zipcode' => null,
                'message' => 'Missing coordinates'
            ]);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10
            ]);

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'headers' => [
                    'User-Agent' => 'LaravelApp/1.0 (zipcode lookup)'
                ],
                'query' => [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'addressdetails' => 1
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            $zipcode = null;

            if (!empty($data['address'])) {
                $address = $data['address'];

                $zipcode =
                    $address['postcode']
                    ?? $address['postal_code']
                    ?? $address['zip']
                    ?? null;
            }

            return response()->json([
                'success' => true,
                'zipcode' => $zipcode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'zipcode' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function ncrCities()
    {
        $cities = City::where('region', 'NCR')
            ->orderBy('name')
            ->get(['name']);

        return response()->json($cities);
    }
}
