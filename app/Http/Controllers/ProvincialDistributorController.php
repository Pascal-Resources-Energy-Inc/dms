<?php

namespace App\Http\Controllers;

use App\Area;
use App\AreaAd;
use App\AreaDistributor;
use App\Center;
use App\ProvincialDistributor;
use App\ProvincialDistributorArea;
use App\User;
use App\Services\DistributorProjectAreas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProvincialDistributorController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = ProvincialDistributor::withoutPersonnel();
        $totalAds = (clone $baseQuery)->count();
        $activeAds = (clone $baseQuery)->where('status', 'Active')->count();
        $inactiveAds = (clone $baseQuery)->where('status', 'Inactive')->count();
        $totalAwardedAreas = ProvincialDistributorArea::whereIn('provincial_distributor_id', (clone $baseQuery)->select('id'))->count();

        $regions = (clone $baseQuery)
            ->whereNotNull('location_region')
            ->where('location_region', '<>', '')
            ->distinct()
            ->orderBy('location_region')
            ->pluck('location_region');

        $projectTypes = ProvincialDistributorArea::whereIn('provincial_distributor_id', (clone $baseQuery)->select('id'))
            ->whereNotNull('project_type')
            ->where('project_type', '<>', '')
            ->distinct()
            ->orderBy('project_type')
            ->pluck('project_type');

        $ads = ProvincialDistributor::withoutPersonnel()->with([
                'areas' => function ($query) {
                    $query->orderBy('project_type')->orderBy('area_name');
                },
                'trashedAreas' => function ($query) {
                    $query->orderByDesc('deleted_at');
                },
                'userAds',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
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
                $area = trim((string) $request->area);
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
            'centers' => Center::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
            'indexRoute' => 'pds',
            'viewRoute' => 'pd.view',
            'storeRoute' => 'pds.store',
            'updateRoute' => 'pds.update',
            'destroyRoute' => 'pds.destroy',
            'updateAreasRoute' => 'pds.areas.update',
            'distributorTitle' => 'Provincial Distributors',
            'distributorSingular' => 'Provincial Distributor',
            'distributorCopy' => 'Manage provincial distributor accounts, business information, and awarded territories.',
            'showCreateButton' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProfile($request);

        DB::beginTransaction();

        try {
            $avatarPath = $this->upload($request, 'avatar', 'uploads/provincial_distributor');
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email_address'],
                'role' => User::ROLE_PROVINCIAL_DISTRIBUTOR,
                'password' => bcrypt('12345678'),
            ]);

            $profile = ProvincialDistributor::create(array_merge(
                $this->profilePayload($request, $data),
                [
                    'user_id' => $user->id,
                    'ad_reference' => $this->nextReference(),
                    'avatar' => $avatarPath,
                    'contract_attachments' => $this->mergeContractAttachments($request, new ProvincialDistributor()),
                    'status' => 'Active',
                ]
            ));

            foreach (collect($request->input('area_name', []))->filter()->unique() as $areaName) {
                ProvincialDistributorArea::create([
                    'provincial_distributor_id' => $profile->id,
                    'user_id' => $user->id,
                    'area_name' => $areaName,
                    'user_role' => User::ROLE_PROVINCIAL_DISTRIBUTOR,
                ]);
            }

            $this->syncLegacyProfile($profile->fresh('areas'));
            DB::commit();

            return redirect()->route('pds')->with('success', 'Provincial Distributor created successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function view($id)
    {
        $ad = ProvincialDistributor::with([
            'areas' => function ($query) {
                $query->orderBy('project_type')->orderBy('area_name');
            },
            'trashedAreas' => function ($query) {
                $query->orderByDesc('deleted_at');
            },
            'userAds',
        ])->findOrFail($id);

        return view('area_distributor.view', [
            'ad' => $ad,
            'indexRoute' => 'pds',
            'distributorSingular' => 'Provincial Distributor',
        ]);
    }

    public function update(Request $request, $id)
    {
        $profile = ProvincialDistributor::with('userAds')->findOrFail($id);
        $businessDetails = $request->validate([
            'nature_of_business' => ['required', 'string', Rule::in(config('distributors.natures_of_business'))],
        ], [
            'nature_of_business.required' => 'Please select the nature of business.',
            'nature_of_business.in' => 'Please choose a nature of business from the available options.',
        ]);
        $data = $this->validateProfile($request, $profile);
        $projectAreaChanges = $request->has('sync_project_areas')
            ? app(DistributorProjectAreas::class)->validate($request, $profile)
            : null;

        if ($request->has('same_as_delivery_address')) {
            $request->merge(['delivery_address' => $request->address]);
        }

        $areaOfferSheetPaths = [];
        DB::beginTransaction();

        try {
            $fullName = trim(collect([
                $request->first_name,
                $request->middle_name,
                $request->last_name,
            ])->filter()->implode(' ')) ?: $data['name'];

            if ($profile->userAds) {
                User::whereKey($profile->user_id)->update([
                    'name' => $fullName,
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'email' => $data['email_address'],
                    'birthdate' => $request->birthdate,
                    'type' => json_encode(array_values(array_filter($request->input('type', [])))),
                ]);
            }

            $payload = $this->profilePayload($request, $data);
            $payload['name'] = $fullName;
            $payload['nature_of_business'] = $businessDetails['nature_of_business'];
            $payload['status'] = $request->input('status', $profile->status ?: 'Active');
            $payload['withholding_tax'] = $request->input('withholding_tax') ? 1 : 0;
            $payload['contract_attachments'] = $this->mergeContractAttachments($request, $profile);

            foreach ([
                'avatar' => 'uploads/provincial_distributor',
                'attachment' => 'uploads/attachments',
                'store_picture' => 'uploads/store_pictures',
            ] as $field => $directory) {
                $uploaded = $this->upload($request, $field, $directory, $profile->{$field});
                if ($uploaded !== null) {
                    $payload[$field] = $uploaded;
                }
            }

            $profile->update($payload);

            if ($projectAreaChanges !== null) {
                app(DistributorProjectAreas::class)->sync($profile, $projectAreaChanges, $areaOfferSheetPaths);
            }

            $this->syncLegacyProfile($profile->fresh('areas'));
            DB::commit();
            if ($request->expectsJson()) {
                session()->flash('success', 'Distributor updated successfully.');
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Provincial Distributor updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            app(DistributorProjectAreas::class)->discardUploads($areaOfferSheetPaths);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to save the distributor. Please try again.'], 500);
            }

            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function updateAreas(Request $request, $id)
    {
        $profile = ProvincialDistributor::findOrFail($id);
        $changes = app(\App\Services\ManageDistributorAreas::class)->validate($request, $profile);
        $service = app(DistributorProjectAreas::class);
        $uploadedPaths = [];
        DB::beginTransaction();
        try {
            $service->sync($profile, $changes, $uploadedPaths);
            $this->syncLegacyProfile($profile->fresh('areas'));
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Provincial Distributor areas updated successfully.']);
        } catch (\Throwable $exception) {
            DB::rollBack();
            $service->discardUploads($uploadedPaths);
            report($exception);
            return response()->json(['status' => false, 'message' => 'Unable to save the awarded areas. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        $profile = ProvincialDistributor::with('areas')->findOrFail($id);

        DB::transaction(function () use ($profile) {
            $profile->areas()->delete();
            $profile->delete();

            $legacy = AreaDistributor::where('user_id', $profile->user_id)->first();
            if ($legacy) {
                $legacy->update(['status' => 'Inactive']);
                AreaAd::where('ad_id', $legacy->id)->delete();
            }

            if (Schema::hasColumn('users', 'status')) {
                User::whereKey($profile->user_id)->update(['status' => 'Inactive']);
            }
        });

        return redirect()->route('pds')->with('success', 'Provincial Distributor removed successfully.');
    }

    private function validateProfile(Request $request, ProvincialDistributor $profile = null)
    {
        $userId = $profile ? $profile->user_id : null;

        return $request->validate([
            'name' => 'required|string|max:255',
            'store_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('provincial_distributors', 'store_code')->ignore(optional($profile)->id),
            ],
            'email_address' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'contact_number' => 'required|string|max:50',
            'facebook' => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'has_service_vehicle' => 'required|boolean',
            'business_years_operation' => 'required|integer|min:0|max:200',
            'contract_attachments' => 'nullable|array|max:10',
            'contract_attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'address' => 'required|string|max:1000',
            'delivery_address' => 'nullable|string|max:1000',
            'street_address' => 'nullable|string|max:255',
            'location_region' => 'nullable|string|max:255',
            'location_province' => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:255',
            'location_barangay' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|in:Active,Inactive',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'attachment' => 'nullable|file|max:10240',
            'store_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'area_name' => $profile ? 'nullable|array' : 'required|array|min:1',
            'area_name.*' => 'string|max:255|distinct',
        ]);
    }

    private function profilePayload(Request $request, array $data)
    {
        return [
            'name' => $data['name'],
            'store_code' => $data['store_code'],
            'email_address' => $data['email_address'],
            'contact_number' => $data['contact_number'],
            'facebook' => $request->facebook,
            'address' => $request->address,
            'delivery_address' => $request->delivery_address,
            'street_address' => $request->street_address,
            'location_region' => $request->location_region,
            'location_province' => $request->location_province,
            'location_city' => $request->location_city,
            'location_barangay' => $request->location_barangay,
            'zipcode' => $request->zipcode,
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'has_service_vehicle' => (bool) $request->input('has_service_vehicle'),
            'business_years_operation' => (int) $request->input('business_years_operation'),
            'tin' => trim((string) $request->tin) ?: null,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'joining_date' => $request->joining_date,
        ];
    }

    private function syncLegacyProfile(ProvincialDistributor $profile)
    {
        $legacy = AreaDistributor::firstOrNew(['user_id' => $profile->user_id]);
        $legacy->fill(collect($profile->getAttributes())->only($legacy->getFillable())->all());
        $legacy->contract_attachments = $profile->contract_attachments ?: [];
        $legacy->save();

        AreaAd::withTrashed()->where('ad_id', $legacy->id)->forceDelete();

        foreach ($profile->areas()->withTrashed()->get() as $area) {
            $legacyArea = AreaAd::create([
                'ad_id' => $legacy->id,
                'ad_user_id' => $profile->user_id,
                'project_type' => $area->project_type,
                'area_name' => $area->area_name,
                'joining_date' => $area->joining_date,
                'package_amount' => $area->package_amount,
                'business_name' => $area->business_name,
                'business_type' => $area->business_type,
                'offer_sheet_path' => $area->offer_sheet_path,
                'offer_sheet_name' => $area->offer_sheet_name,
                'user_role' => User::ROLE_PROVINCIAL_DISTRIBUTOR,
            ]);
            if ($area->deleted_at) {
                $legacyArea->deleted_at = $area->deleted_at;
                $legacyArea->save();
            }
        }
    }

    private function nextReference()
    {
        $lastReference = ProvincialDistributor::withTrashed()
            ->whereNotNull('ad_reference')
            ->orderByDesc('id')
            ->value('ad_reference');
        $number = $lastReference ? ((int) preg_replace('/\D+/', '', $lastReference)) + 1 : 1;

        return 'PRPD' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    private function upload(Request $request, $field, $directory, $oldPath = null)
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        if (!is_dir(public_path($directory))) {
            mkdir(public_path($directory), 0755, true);
        }

        $file = $request->file($field);
        $filename = $field . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);

        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        return $directory . '/' . $filename;
    }

    private function mergeContractAttachments(Request $request, ProvincialDistributor $profile)
    {
        $attachments = collect($profile->contract_attachments ?: [])->filter()->values();

        if (!$request->hasFile('contract_attachments')) {
            return $attachments->all();
        }

        $directory = 'uploads/contracts';

        if (!is_dir(public_path($directory))) {
            mkdir(public_path($directory), 0755, true);
        }

        $newAttachments = collect($request->file('contract_attachments'))
            ->filter()
            ->map(function ($file) use ($directory) {
                $filename = 'contract_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($directory), $filename);

                return $directory . '/' . $filename;
            });

        return $attachments->merge($newAttachments)->unique()->values()->all();
    }
}
