<?php

namespace App\Http\Controllers;

use App\User;
use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DmsController extends Controller
{
    private $data;

    public function __construct(DashboardDataService $data)
    {
        $this->data = $data;
    }

    public function analytics()
    {
        return view('dms.analytics', [
            'dashboard' => $this->data->build(),
            'salesByItem' => $this->data->salesByItem(),
            'dealerTypes' => $this->data->dealerTypeBreakdown(),
        ]);
    }

    public function inventory(Request $request)
    {
        return view('dms.inventory', [
            'summary' => $this->data->inventorySummaryPublic(),
            'products' => $this->data->products($request->get('q')),
            'movements' => $this->data->inventoryMovements($request->get('q')),
            'q' => $request->get('q'),
        ]);
    }

    public function purchases(Request $request)
    {
        return view('dms.purchases', [
            'orders' => $this->data->purchaseOrders($request->get('q')),
            'q' => $request->get('q'),
        ]);
    }

    public function orders(Request $request)
    {
        return view('dms.orders', [
            'orders' => $this->data->customerOrders($request->get('q')),
            'q' => $request->get('q'),
        ]);
    }

    public function sales()
    {
        return view('dms.sales', [
            'dashboard' => $this->data->build(),
            'salesByItem' => $this->data->salesByItem(),
            'topDealers' => $this->data->topDealersPublic(),
        ]);
    }

    public function dealers(Request $request)
    {
        return view('dms.dealers', [
            'dealers' => $this->data->dealers($request->get('q')),
            'areas' => $this->data->areaDistributors(),
            'q' => $request->get('q'),
        ]);
    }

    public function outlets()
    {
        return view('dms.outlets', [
            'dealerTypes' => $this->data->dealerTypeBreakdown(),
            'topDealers' => $this->data->topDealersPublic(),
        ]);
    }

    public function pricing()
    {
        return view('dms.pricing', [
            'products' => $this->data->products(),
        ]);
    }

    public function promotions()
    {
        return view('dms.promotions', [
            'rewards' => $this->data->rewards(),
            'raffles' => $this->data->raffles(),
        ]);
    }

    public function users(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only admin users can manage DMS users.');

        $query = User::query()->latest();

        if ($request->get('q')) {
            $search = $request->get('q');
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%')
                    ->orWhere('territory', 'like', '%' . $search . '%');
            });
        }

        $roleCounts = User::all()->groupBy(function ($user) {
            return $user->roleKey();
        })->map(function ($users) {
            return $users->count();
        });

        return view('dms.users', [
            'users' => $query->paginate(25),
            'q' => $request->get('q'),
            'roleCounts' => $roleCounts,
        ]);
    }

    public function storeUser(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only admin users can create DMS users.');

        $this->normalizeUserRequest($request);

        $validator = Validator::make($request->all(), $this->userRules('store'));

        if ($validator->fails()) {
            return redirect()->route('dms.users')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $this->profileData($request);
        $data['password'] = Hash::make($request->password);
        $data['user_reference'] = $this->nextUserReference($request->role);
        $data['avatar_path'] = $this->storeUpload($request, 'avatar', 'avatars');
        $data['attachment_path'] = $this->storeUpload($request, 'attachment', 'attachments');

        $user = User::create($data);
        $this->syncAwardedAreas($user, $request);

        return redirect()->route('dms.users')
            ->with('success', 'User account created successfully.');
    }

    public function editUser(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only admin users can edit DMS users.');

        return view('dms.users-edit', [
            'user' => $user,
            'awardedAreas' => $this->awardedAreas($user),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only admin users can update DMS users.');

        $this->normalizeUserRequest($request);

        $validator = Validator::make($request->all(), $this->userRules('update', $user));

        if ($validator->fails()) {
            return redirect()->route('dms.users.edit', $user)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $this->profileData($request);

        if (! $user->user_reference) {
            $data['user_reference'] = $this->nextUserReference($request->role);
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($avatarPath = $this->storeUpload($request, 'avatar', 'avatars')) {
            $data['avatar_path'] = $avatarPath;
        }

        if ($attachmentPath = $this->storeUpload($request, 'attachment', 'attachments')) {
            $data['attachment_path'] = $attachmentPath;
        }

        $user->update($data);
        $this->syncAwardedAreas($user, $request);

        return redirect()->route('dms.users')
            ->with('success', 'User account updated successfully.');
    }

    private function userRules($mode, User $user = null)
    {
        $emailRule = 'required|string|email|max:255|unique:users,email';

        if ($user) {
            $emailRule .= ',' . $user->id;
        }

        return [
            'first_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => $emailRule,
            'mobile_number' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:120',
            'facebook' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:120',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'attachment' => 'nullable|file|max:4096',
            'role' => 'required|string|in:' . implode(',', array_keys(User::roles())),
            'project_tag' => 'nullable|array',
            'project_tag.*' => 'nullable|string|in:Project Rise,Project Genesis,Regular',
            'territory' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
            'street_address' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:120',
            'barangay' => 'nullable|string|max:120',
            'zip_code' => 'nullable|string|max:20',
            'delivery_same_as_address' => 'nullable|boolean',
            'delivery_address' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'complete_address' => 'nullable|string|max:1000',
            'warehouse' => 'nullable|string|in:lubao,guinobatan',
            'designation' => 'nullable|string|max:120',
            'employee_number' => 'nullable|string|max:120',
            'department' => 'nullable|string|max:120',
            'joining_date' => 'nullable|array',
            'joining_date.*' => 'nullable|date',
            'area_name_rise' => 'nullable|array',
            'area_name_rise.*' => 'nullable|string|max:255',
            'area_name_genesis' => 'nullable|array',
            'area_name_genesis.*' => 'nullable|string|max:255',
            'password' => ($mode === 'store' ? 'required' : 'nullable') . '|string|min:6|confirmed',
        ];
    }

    private function normalizeUserRequest(Request $request)
    {
        $role = $request->role;
        $roleAliases = [
            'Admin' => User::ROLE_SUPER_ADMIN,
            'Super Admin' => User::ROLE_SUPER_ADMIN,
            'Provincial Distributor' => User::ROLE_PROVINCIAL_DISTRIBUTOR,
            'Area Distributor' => User::ROLE_AREA_DISTRIBUTOR,
            'Mega Dealer' => User::ROLE_MEGA_DEALER,
            'Dealer' => User::ROLE_DEALER,
        ];

        $projectTag = $request->get('project_tag');
        if (! is_array($projectTag)) {
            $projectTag = $projectTag ? [$projectTag] : [];
        }
        if (! $projectTag && is_array($request->get('type'))) {
            $projectTag = $request->get('type');
        }
        $projectTag = array_values(array_unique(array_filter($projectTag, function ($tag) {
            return in_array($tag, ['Project Rise', 'Project Genesis', 'Regular']);
        })));

        $request->merge([
            'role' => $roleAliases[$role] ?? $role,
            'email' => $request->email ?: $request->email_address,
            'mobile_number' => $request->mobile_number ?: $request->contact_number,
            'mother_name' => $request->mother_name ?: $request->mothers_name,
            'project_tag' => $projectTag,
            'region' => $request->region ?: $request->location_region,
            'province' => $request->province ?: $request->location_province,
            'city' => $request->city ?: $request->location_city,
            'barangay' => $request->barangay ?: $request->location_barangay,
            'zip_code' => $request->zip_code ?: $request->zipcode,
            'complete_address' => $request->complete_address ?: $request->address,
            'delivery_same_as_address' => $request->has('delivery_same_as_address') || $request->has('same_as_delivery_address'),
        ]);
    }

    private function profileData(Request $request)
    {
        $name = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        $completeAddress = $request->complete_address ?: collect([
            $request->street_address,
            $request->barangay,
            $request->city,
            $request->province,
            $request->region,
            $request->zip_code,
        ])->filter()->implode(', ');

        return [
            'name' => $name,
            'email' => $request->email,
            'role' => $request->role,
            'territory' => $request->territory,
            'status' => $request->status,
            'project_tag' => $request->project_tag ? json_encode(array_values($request->project_tag)) : null,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'mobile_number' => $request->mobile_number,
            'birthdate' => $request->birthdate,
            'age' => $request->age,
            'facebook' => $request->facebook,
            'mother_name' => $request->mother_name,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'withholding_tax' => $request->has('withholding_tax'),
            'street_address' => $request->street_address,
            'region' => $request->region,
            'province' => $request->province,
            'city' => $request->city,
            'barangay' => $request->barangay,
            'zip_code' => $request->zip_code,
            'delivery_same_as_address' => $request->has('delivery_same_as_address'),
            'delivery_address' => $request->delivery_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'complete_address' => $completeAddress,
            'warehouse' => $request->warehouse,
            'designation' => $request->designation,
            'employee_number' => $request->employee_number,
            'department' => $request->department,
        ];
    }

    private function awardedAreas(User $user)
    {
        if (! Schema::hasTable('user_awarded_areas')) {
            return collect();
        }

        return DB::table('user_awarded_areas')
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();
    }

    private function syncAwardedAreas(User $user, Request $request)
    {
        if (! Schema::hasTable('user_awarded_areas')) {
            return;
        }

        DB::table('user_awarded_areas')->where('user_id', $user->id)->delete();

        $joiningDates = (array) $request->get('joining_date', []);
        $riseAreas = (array) $request->get('area_name_rise', []);
        $genesisAreas = (array) $request->get('area_name_genesis', []);
        $rows = max(count($joiningDates), count($riseAreas), count($genesisAreas));

        for ($index = 0; $index < $rows; $index++) {
            $joiningDate = $joiningDates[$index] ?? null;
            $riseArea = trim((string) ($riseAreas[$index] ?? ''));
            $genesisArea = trim((string) ($genesisAreas[$index] ?? ''));

            if (! $joiningDate && ! $riseArea && ! $genesisArea) {
                continue;
            }

            DB::table('user_awarded_areas')->insert([
                'user_id' => $user->id,
                'joining_date' => $joiningDate ?: null,
                'area_name_rise' => $riseArea ?: null,
                'area_name_genesis' => $genesisArea ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function nextUserReference($role)
    {
        $prefix = User::roleReferencePrefix($role);
        $year = date('Y');
        $like = $prefix . '-' . $year . '-%';
        $last = User::where('user_reference', 'like', $like)
            ->orderBy('user_reference', 'desc')
            ->value('user_reference');

        $number = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . '-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    private function storeUpload(Request $request, $field, $folder)
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $destination = public_path('uploads/users/' . $folder);

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $filename = date('YmdHis') . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return 'uploads/users/' . $folder . '/' . $filename;
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only admin users can delete DMS users.');

        if ($request->user()->id === $user->id) {
            return redirect()->route('dms.users')
                ->withErrors(['delete' => 'You cannot delete your own signed-in account.']);
        }

        $user->delete();

        return redirect()->route('dms.users')
            ->with('success', 'User account deleted successfully.');
    }

    public function reports()
    {
        return view('dms.reports', [
            'dashboard' => $this->data->build(),
            'connections' => $this->data->connectionStatusesPublic(),
        ]);
    }

    public function alerts()
    {
        $dashboard = $this->data->build();

        return view('dms.alerts', [
            'alerts' => $dashboard['alerts'],
            'inventory' => $dashboard['inventory'],
        ]);
    }

    public function settings()
    {
        return view('dms.settings', [
            'connections' => $this->data->connectionStatusesPublic(),
        ]);
    }
}
