<?php

namespace App\Http\Controllers;

use App\AreaDistributor;
use App\AreaAd;
use App\Center;
use App\Dealer;
use App\User;
use App\Area;
use App\Item;
use App\Product;
use App\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AreaDistributorController extends Controller
{
    private $crmConnections = [
        'admin_crms' => 'Project Rise',
        'admin_crms2' => 'Project Genesis',
    ];

    public function index(Request $request)
    {
        $centers = Center::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $baseQuery = AreaDistributor::whereHas('userAds', function ($query) {
            $query->where('role', 'Area Distributor');
        });

        $totalAds = (clone $baseQuery)->count();
        $activeAds = (clone $baseQuery)->where('status', 'Active')->count();
        $inactiveAds = (clone $baseQuery)->where('status', 'Inactive')->count();
        $totalAwardedAreas = AreaAd::whereHas('distributor.userAds', function ($query) {
            $query->where('role', 'Area Distributor');
        })->count();

        $regions = (clone $baseQuery)
            ->whereNotNull('location_region')
            ->where('location_region', '<>', '')
            ->distinct()
            ->orderBy('location_region')
            ->pluck('location_region');

        $projectTypes = AreaAd::whereHas('distributor.userAds', function ($query) {
                $query->where('role', 'Area Distributor');
            })
            ->whereNotNull('project_type')
            ->where('project_type', '<>', '')
            ->distinct()
            ->orderBy('project_type')
            ->pluck('project_type');

        $ads = AreaDistributor::with(['areas' => function ($query) {
                $query->orderBy('project_type')->orderBy('area_name');
            }, 'trashedAreas' => function ($query) {
                $query->orderByDesc('deleted_at');
            }, 'userAds'])
            ->whereHas('userAds', function ($q) {
                $q->where('role', 'Area Distributor');
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('store_code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhere('contact_number', 'like', '%' . $search . '%')
                        ->orWhere('location_region', 'like', '%' . $search . '%')
                        ->orWhereHas('areas', function ($areaQuery) use ($search) {
                            $areaQuery->where('area_name', 'like', '%' . $search . '%')
                                ->orWhere('project_type', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('region'), function ($query) use ($request) {
                $query->where('location_region', $request->region);
            })
            ->when($request->filled('project_type'), function ($query) use ($request) {
                $query->whereHas('areas', function ($areaQuery) use ($request) {
                    $areaQuery->where('project_type', $request->project_type);
                });
            })
            ->when($request->filled('area'), function ($query) use ($request) {
                $area = trim($request->area);
                $query->whereHas('areas', function ($areaQuery) use ($area) {
                    $areaQuery->where('area_name', 'like', '%' . $area . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->appends($request->query());

        return view('area_distributor.index', [
            'ads' => $ads,
            'activeAds' => $activeAds,
            'inactiveAds' => $inactiveAds,
            'totalAds' => $totalAds,
            'totalAwardedAreas' => $totalAwardedAreas,
            'regions' => $regions,
            'projectTypes' => $projectTypes,
            'centers' => $centers,
            'areas' => $areas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $imagePath = null;

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/area_distributor'), $filename);
            $imagePath = 'uploads/area_distributor/' . $filename;
        }

        $fullAddress = $request->address;

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email_address;
        $user->role = 'Area Distributor';
        $user->password = bcrypt('12345678');

        if ($imagePath) {
            $user->avatar = $imagePath;
        }

        $user->save();
        
        $latestAd = AreaDistributor::orderBy('id', 'desc')->first();

        $number = ($latestAd && $latestAd->ad_reference)
            ? intval(substr($latestAd->ad_reference, 4)) + 1
            : 1;

        $ad_reference = 'PRAD' . str_pad($number, 5, '0', STR_PAD_LEFT);


        $areaDistributor = new AreaDistributor;
        $areaDistributor->user_id = $user->id;
        $areaDistributor->ad_reference = $ad_reference;
        $areaDistributor->name = $request->name;
        $areaDistributor->store_code = $request->store_code;
        $areaDistributor->email_address = $request->email_address;
        $areaDistributor->contact_number = $request->contact_number;
        $areaDistributor->facebook = $request->facebook;
        $areaDistributor->address = $fullAddress;
        $areaDistributor->business_name = $request->business_name;
        $areaDistributor->business_type = $request->business_type;
        $areaDistributor->latitude = $request->latitude;
        $areaDistributor->longitude = $request->longitude;
        $areaDistributor->status = "Active";

        if ($imagePath) {
            $areaDistributor->avatar = $imagePath;
        }

        $areaDistributor->save();

        foreach ($request->area_name as $area) {
            AreaAd::create([
                'ad_id' => $areaDistributor->id,
                'area_name' => $area

            ]);
        }

        return redirect()->route('ads')->with('success', 'Successfully encoded');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AreaDistributor  $areaDistributor
     * @return \Illuminate\Http\Response
     */
    public function show(AreaDistributor $areaDistributor)
    {
        //
    }

    public function view($id)
    {
        $ad = AreaDistributor::with(['areas', 'trashedAreas', 'userAds'])->findOrFail($id);

        return view('area_distributor.view', compact('ad'));
    }

    public function edit($id)
    {
        $ad = AreaDistributor::with('areas')->findOrFail($id);
        $centers = Center::all();
        $areas = Area::all();

        return view('area_distributor.edit', compact('ad', 'centers', 'areas'));
    }

    public function update(Request $request, $id)
    {
        if ($request->has('same_as_delivery_address')) {
            $request->merge([
                'delivery_address' => $request->address,
            ]);
        }

        $request->validate([
            'delivery_address' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {

            $ad = AreaDistributor::findOrFail($id);
            $fullName = trim(collect([
                $request->first_name,
                $request->middle_name,
                $request->last_name,
            ])->filter()->implode(' '));

            if ($ad->user_id) {
                $selectedTypes = array_values(array_filter($request->input('type', [])));

                User::where('id', $ad->user_id)->update([
                    'name' => $fullName ?: $request->name,
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email_address,
                    'birthdate' => $request->birthdate,
                    'type' => json_encode($selectedTypes),
                ]);
            }

            $avatarPath = $ad->avatar;
            $attachmentPath = $ad->attachment;

            if ($request->hasFile('avatar')) {

                if (
                    $ad->avatar &&
                    file_exists(public_path($ad->avatar))
                ) {
                    unlink(public_path($ad->avatar));
                }

                $file = $request->file('avatar');

                $filename = time() . '_' . $file->getClientOriginalName();

                $path = 'uploads/area_distributor';

                $file->move(public_path($path), $filename);

                $avatarPath = $path . '/' . $filename;
            }

            if ($request->hasFile('attachment')) {

                if (
                    $ad->attachment &&
                    file_exists(public_path($ad->attachment))
                ) {
                    unlink(public_path($ad->attachment));
                }

                $file = $request->file('attachment');

                $filename = time() . '_' . $file->getClientOriginalName();

                $path = 'uploads/attachments';

                $file->move(public_path($path), $filename);

                $attachmentPath = $path . '/' . $filename;
            }

            $ad->update([
                'name' => $fullName ?: $request->name,
                'store_code' => $request->store_code,
                'email_address' => $request->email_address,
                'contact_number' => $request->contact_number,
                'facebook' => $request->facebook,
                'address' => $request->address,
                'delivery_address' => $request->delivery_address,
                'street_address' => $request->street_address,
                'location_region' => $request->location_region,
                'location_province' => $request->location_province,
                'location_city' => $request->location_city,
                'location_barangay' => $request->location_barangay,
                'zipcode' => $request->zipcode,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $request->status,
                'avatar' => $avatarPath,
                'attachment' => $attachmentPath,
                'withholding_tax' => $request->withholding_tax ? 1 : 0,
            ]);

            if ($request->has('sync_project_areas')) {
                $selectedProjectTypes = collect($request->input('type', []))
                    ->filter(function ($type) {
                        return in_array($type, ['Project Rise', 'Project Genesis'], true);
                    })
                    ->values()
                    ->all();

                $rows = collect($request->input('rows', []))->map(function ($row) {
                    return [
                        'id' => isset($row['id']) ? trim($row['id']) : null,
                        'project_type' => isset($row['project_type']) ? trim($row['project_type']) : null,
                        'area_name' => isset($row['area_name']) ? trim($row['area_name']) : null,
                        'joining_date' => isset($row['joining_date']) ? trim($row['joining_date']) : null,
                    ];
                })->filter(function ($row) use ($selectedProjectTypes) {
                    return !empty($row['area_name']) &&
                        !empty($row['project_type']) &&
                        in_array($row['project_type'], $selectedProjectTypes, true);
                });

                $submittedIds = [];
                $userRole = optional($ad->userAds)->role ?: 'Area Distributor';

                foreach ($rows as $row) {
                    if (!empty($row['id'])) {
                        $area = AreaAd::where('ad_id', $ad->id)
                            ->where('id', $row['id'])
                            ->first();

                        if ($area) {
                            $area->update([
                                'project_type' => $row['project_type'],
                                'area_name' => $row['area_name'],
                                'joining_date' => $row['joining_date'] ?: null,
                                'user_role' => $userRole,
                            ]);

                            $submittedIds[] = $area->id;
                        }

                        continue;
                    }

                    $newArea = AreaAd::create([
                        'ad_id' => $ad->id,
                        'ad_user_id' => $ad->user_id,
                        'project_type' => $row['project_type'],
                        'area_name' => $row['area_name'],
                        'joining_date' => $row['joining_date'] ?: null,
                        'user_role' => $userRole,
                    ]);

                    $submittedIds[] = $newArea->id;
                }

                $areaQuery = AreaAd::where('ad_id', $ad->id)
                    ->whereIn('project_type', ['Project Rise', 'Project Genesis']);

                if (count($submittedIds) > 0) {
                    $areaQuery->whereNotIn('id', $submittedIds)->delete();
                } else {
                    $areaQuery->delete();
                }
            }

            DB::commit();

            return back()->with(
                'success',
                'Partner updated successfully.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email_address' => 'required|email',
    //         'contact_number' => 'required',
    //         'business_name' => 'required',
    //         'business_type' => 'required',
    //         'area_name' => 'required|array',
    //     ]);

    //     $areaDistributor = AreaDistributor::findOrFail($id);

    //     // ✅ Update user
    //     if ($areaDistributor->user_id) {
    //         User::where('id', $areaDistributor->user_id)->update([
    //             'name' => $request->name,
    //             'email' => $request->email_address,
    //             'birthdate' => $request->birthdate
    //         ]);
    //     }

    //     // ✅ Image Upload
    //     if ($request->hasFile('avatar')) {

    //         // delete old
    //         if ($areaDistributor->avatar && file_exists(public_path($areaDistributor->avatar))) {
    //             unlink(public_path($areaDistributor->avatar));
    //         }

    //         $file = $request->file('avatar');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('uploads/area_distributor'), $filename);

    //         // ✅ IMPORTANT: assign BEFORE update
    //         $areaDistributor->avatar = 'uploads/area_distributor/' . $filename;
    //     }

    //     // ✅ Update main data
    //     $areaDistributor->update([
    //         'name' => $request->name,
    //         'store_code' => $request->store_code,
    //         'email_address' => $request->email_address,
    //         'contact_number' => $request->contact_number,
    //         'facebook' => $request->facebook,
    //         'address' => $request->address,
    //         'business_name' => $request->business_name,
    //         'business_type' => $request->business_type,
    //         'joining_date' => $request->joining_date,
    //         'latitude' => $request->latitude,
    //         'longitude' => $request->longitude,
    //         'status' => $request->status,
    //     ]);

    //     // ✅ Sync Areas (cleaner)
    //     AreaAd::where('ad_id', $areaDistributor->id)->delete();

    //     foreach ($request->area_name as $area) {
    //         AreaAd::create([
    //             'ad_id' => $areaDistributor->id,
    //             'area_name' => $area
    //         ]);
    //     }

    //     return back()->with('success', 'Updated successfully');
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AreaDistributor  $areaDistributor
     * @return \Illuminate\Http\Response
     */
    public function destroy(AreaDistributor $areaDistributor)
    {
        //
    }

    public function geocodeLocation(Request $request)
    {
        try {
            $request->validate([
                'barangay' => 'required|string',
                'city' => 'required|string',
                'province' => 'required|string',
            ]);

            $barangay = $request->input('barangay');
            $city = $request->input('city');
            $province = $request->input('province');
            
            $query = urlencode("{$barangay}, {$city}, {$province}, Philippines");
            $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&countrycodes=ph";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DealerRegistrationApp/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                \Log::error('Geocoding cURL error: ' . $error);
            }
            
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                
                if (!empty($data)) {
                    return response()->json([
                        'success' => true,
                        'lat' => $data[0]['lat'],
                        'lng' => $data[0]['lon']
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Location not found'
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Geocoding error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Geocoding failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 200);
        }
    }

    public function myDealer(Request $request)
    {
        $user = auth()->user();

        // $centers = $user->ad->areas->pluck('area_name')->toArray();
        $areas = optional($user->ad)
            ->areas
            ? $user->ad->areas->pluck('area_name')->toArray()
            : [];

        $adUser = optional(auth()->user()->ad)->id;
        $pendingOrdersCount = OrderDetail::where('ad_id', $adUser)
            ->where('status', 'Pending')
            ->count();

        $localDealers = Dealer::with([
            'orders' => function ($q) {
                $q->where('status', 'Completed')->select('dealer_id', 'item', \DB::raw('SUM(qty) as total_qty'))
                ->groupBy('dealer_id', 'item');
            },
            'sales' => function ($q) {
                $q->select('dealer_id', 'item', \DB::raw('SUM(qty) as total_qty'))
                ->groupBy('dealer_id', 'item');
            }
        ])->whereIn('area', $areas)->get()->toBase();

        $crmDealers = $this->adCrmDealers($areas);
        $dealers = $localDealers
            ->merge($crmDealers)
            ->sortBy('name')
            ->values();
       
        // $items = Item::select('item')->get(); // master list of items
        $items = Product::select('product_name')->where('ad_user_id', $user->id)->where('status', 'Activate')->get()->toBase()
            ->merge($this->adCrmProducts())
            ->filter(function ($item) {
                return !empty($item->product_name);
            })
            ->unique(function ($item) {
                return strtolower(trim((string) $item->product_name));
            })
            ->sortBy('product_name')
            ->values();
        // dd($items);
        $activeDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Active') === 0;
        })->count();

        $inactiveDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Inactive') === 0;
        })->count();

        return view('dealers', [
            'dealers' => $dealers,
            'items' => $items,
            'activeDealers' => $activeDealers,
            'inactiveDealers' => $inactiveDealers,
            'pendingOrdersCount' => $pendingOrdersCount
        ]);
    }

    private function adCrmDealers(array $areas)
    {
        $areas = collect($areas)->filter()->values();

        if ($areas->isEmpty()) {
            return collect();
        }

        return collect($this->crmConnections)->flatMap(function ($label, $connection) use ($areas) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (!$schema->hasTable('dealers') || !$schema->hasColumn('dealers', 'area')) {
                    return collect();
                }

                $dealers = DB::connection($connection)->table('dealers')
                    ->whereIn('area', $areas);

                if ($schema->hasColumn('dealers', 'deleted_at')) {
                    $dealers->whereNull('deleted_at');
                }

                return $dealers->get()->map(function ($dealer) use ($connection, $label) {
                    $dealer = $this->normalizeAdCrmDealer($dealer, $connection, $label);
                    $dealer->orders = $this->adCrmDealerItemTotals($connection, 'order_details', $dealer, true);
                    $dealer->sales = $this->adCrmDealerItemTotals($connection, 'transaction_details', $dealer, false);

                    return $dealer;
                });
            } catch (\Exception $exception) {
                return collect();
            }
        })->values();
    }

    private function normalizeAdCrmDealer($dealer, $connection, $label)
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
        $dealer->is_remote = true;
        $dealer->dealer_reference = $dealer->dealer_reference ?? ('CRM-' . ($dealer->id ?? ''));
        $dealer->dealer_type = $dealer->dealer_type ?? 'Project';
        $dealer->name = $dealer->name ?? '-';
        $dealer->store_name = $dealer->store_name ?? '-';
        $dealer->store_type = $dealer->store_type ?? '-';
        $dealer->number = $dealer->number ?? ($dealer->contact_number ?? '-');
        $dealer->address = $dealer->address ?? ($location ?: '-');
        $dealer->area = $dealer->area ?? ($dealer->sales_territory ?? '-');
        $dealer->status = ucfirst(strtolower((string) ($dealer->status ?? 'Active')));
        $dealer->user_id = $dealer->user_id ?? null;

        return $dealer;
    }

    private function adCrmDealerItemTotals($connection, $table, $dealer, $completedOnly)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (
                !$schema->hasTable($table) ||
                !$schema->hasColumn($table, 'dealer_id') ||
                !$schema->hasColumn($table, 'qty')
            ) {
                return collect();
            }

            $itemColumn = collect(['item', 'product_name', 'product'])->first(function ($column) use ($schema, $table) {
                return $schema->hasColumn($table, $column);
            });

            if (!$itemColumn) {
                return collect();
            }

            $dealerIds = collect([$dealer->user_id ?? null, $dealer->id ?? null])
                ->filter()
                ->unique()
                ->values();

            if ($dealerIds->isEmpty()) {
                return collect();
            }

            $query = DB::connection($connection)->table($table)
                ->select($itemColumn . ' as item', DB::raw('SUM(qty) as total_qty'))
                ->whereIn('dealer_id', $dealerIds)
                ->groupBy($itemColumn);

            if ($completedOnly && $schema->hasColumn($table, 'status')) {
                $query->where('status', 'Completed');
            }

            return $query->get();
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function adCrmProducts()
    {
        return collect(array_keys($this->crmConnections))->flatMap(function ($connection) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (!$schema->hasTable('products') || !$schema->hasColumn('products', 'product_name')) {
                    return collect();
                }

                $query = DB::connection($connection)->table('products')->select('product_name');

                if ($schema->hasColumn('products', 'status')) {
                    $query->where(function ($query) {
                        $query->where('status', 'Activate')->orWhereNull('status');
                    });
                }

                return $query->get();
            } catch (\Exception $exception) {
                return collect();
            }
        });
    }

    public function megaDealers()
    {
        $centers = Center::get();
        $areas = Area::get();

        $activeAds = AreaDistributor::whereHas('userAds', function ($q) {
            $q->where('role', 'Mega Dealer')
                ->where('status', 'Active');
        })->count();

        $inactiveAds = AreaDistributor::whereHas('userAds', function ($q) {
            $q->where('role', 'Mega Dealer')
                ->where('status', 'Inactive');
        })->count();

        $ads = AreaDistributor::with(['areas', 'trashedAreas', 'userAds'])
            ->whereHas('userAds', function ($q) {
                $q->where('role', 'Mega Dealer');
            })
            ->get();

        return view('mds.index', [
            'ads' => $ads,
            'activeAds' => $activeAds,
            'inactiveAds' => $inactiveAds,
            'centers' => $centers,
            'areas' => $areas
        ]);
    }

    public function updateAreas(Request $request, $id)
    {
        $ad = AreaDistributor::findOrFail($id);

        $rows = collect($request->input('rows', []))->map(function ($row) {
            return [
                'id' => isset($row['id']) ? trim($row['id']) : null,
                'area_name' => isset($row['area_name']) ? trim($row['area_name']) : null,
                'joining_date' => isset($row['joining_date']) ? trim($row['joining_date']) : null,
            ];
        })->filter(function ($row) {
            return !empty($row['area_name']);
        });
        // dd($rows);
        DB::beginTransaction();

        try {
            $submittedIds = [];

            foreach ($rows as $row) {
                if (!empty($row['id'])) {
                    $area = AreaAd::where('ad_id', $ad->id)
                        ->where('id', $row['id'])
                        ->first();

                    if ($area) {
                        $area->update([
                            'project_type' => null,
                            'area_name' => $row['area_name'],
                            'joining_date' => $row['joining_date'] ?: null,
                        ]);

                        $submittedIds[] = $area->id;
                    }

                    continue;
                }

                $new = AreaAd::create([
                    'ad_id' => $ad->id,
                    'ad_user_id' => $ad->user_id,
                    'project_type' => null,
                    'area_name' => $row['area_name'],
                    'joining_date' => $row['joining_date'] ?: null,
                    'user_role' => 'Area Distributor',
                ]);

                $submittedIds[] = $new->id;
            }

            if (count($submittedIds) > 0) {
                AreaAd::where('ad_id', $ad->id)
                    ->whereNotIn('id', $submittedIds)
                    ->delete();
            } else {
                AreaAd::where('ad_id', $ad->id)->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Areas updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
