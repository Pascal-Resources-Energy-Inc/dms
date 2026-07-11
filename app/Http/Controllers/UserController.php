<?php

namespace App\Http\Controllers;
use App\User;
use App\Dealer;
use App\Client;
use App\Stove;
use App\Area;
use App\Center;
use App\TransactionDetail;
use App\AreaDistributor;
use App\AreaAd;
use App\Services\SemaphoreSmsService;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use OwenIt\Auditing\Facades\Auditor;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $stoves = Stove::whereNull('client_id')->get(['id', 'serial_number']);
        $areas = Area::get();
        $centers = Center::orderBy('name')->get();
        $users = User::with([
                'dealer:id,user_id,address,status',
                'client:id,user_id,address,status',
                'ad'
            ])
            ->select('id', 'name', 'email', 'role', 'address', 'can_add', 'can_edit')
            ->latest()
            ->paginate(20); // ✅ IMPORTANT

        return view('users.index', compact('stoves', 'users', 'areas', 'centers'));
    }

    public function store(Request $request)
    {
       $isAdmin = $request->role === 'Admin';
       $isSedp = $request->role === 'SEDP';
       $isAdminLike = $isAdmin || $isSedp;
       $needsDeliveryAddress = in_array($request->role, ['Area Distributor', 'Provincial Distributor'], true);
       $normalizedContactNumber = $this->normalizeMobileNumber($request->contact_number);

       if ($isAdminLike && $request->has('same_as_address')) {
            $request->merge([
                'delivery_address' => $request->address,
            ]);
       }

       if ($needsDeliveryAddress && $request->has('same_as_delivery_address')) {
            $request->merge([
                'delivery_address' => $request->address,
            ]);
       }

       if ($needsDeliveryAddress && trim((string) $request->delivery_address) === '') {
            return back()
                ->withErrors(['delivery_address' => 'Delivery address is required.'])
                ->withInput();
       }

       $request->validate([
            'warehouse' => 'nullable|in:lubao,guinobatan',
            'sedp_center' => 'required_if:role,SEDP|array',
            'sedp_center.*' => 'string|max:255',
            'delivery_address' => 'nullable|string|max:1000',
            'designation' => 'required_if:role,Admin|required_if:role,SEDP|nullable|string|max:255',
            'employee_number' => 'required_if:role,Admin|nullable|string|max:255',
            'department' => 'required_if:role,Admin|nullable|string|max:255',
            'contact_number' => 'nullable|regex:/^09[0-9]{9}$/',
            'type' => 'nullable|array',
            'type.*' => 'in:Project Rise,Project Genesis,Regular',
            // 'tin' => 'nullable|string|max:50',
            // 'store_picture' => 'nullable|image|max:2048',
        ]);

       if (!$isAdminLike && trim((string) $request->contact_number) === '' && trim((string) $request->facebook) === '') {
            return back()
                ->withErrors(['contact_number' => 'Mobile Number or Facebook is required.'])
                ->withInput();
       }

       if ($normalizedContactNumber && !$this->isVerifiedMobileNumber($request, $normalizedContactNumber)) {
            return back()
                ->withErrors(['contact_number' => 'Please verify the mobile number by OTP before submitting.'])
                ->withInput();
       }

       $duplicate = false;

       if (!$isAdminLike) {
            $duplicate = User::where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where('mothers_name', $request->mothers_name)
                ->exists();
       }

        if ($duplicate) {

            return back()->withErrors([
                'duplicate' => 'User already exists.'
            ]);

        }

        $projectTags = collect((array) $request->input('type', []))
            ->map(function ($type) {
                return trim((string) $type);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();


        $imagePath = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/area_distributor'), $filename);
            $imagePath = 'uploads/area_distributor/' . $filename;
        }

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));
        
        $user = new User();
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->name = $fullName;
        $user->email = $request->email_address;
        $user->address = $isAdminLike ? null : $request->address;
        $user->warehouse = $isAdmin ? $request->warehouse : null;
        if (Schema::hasColumn('users', 'territory')) {
            $user->territory = $isSedp ? implode(', ', (array) $request->input('sedp_center', [])) : null;
        }
        $user->delivery_address = $isAdminLike
            ? ($request->has('same_as_address') ? $request->address : $request->delivery_address)
            : null;
        $user->role = $request->role;

        if (Schema::hasColumn('users', 'type')) {
            $user->type = json_encode($projectTags);
        }

        if (Schema::hasColumn('users', 'project_tag')) {
            $user->project_tag = implode(', ', $projectTags);
        }

        $user->birthdate = $isAdminLike ? null : $request->birthdate;
        $user->age = $isAdminLike ? null : $request->age;
        $user->mothers_name = $isAdminLike ? null : $request->mothers_name;
        $user->designation = $isAdminLike ? $request->designation : null;
        $user->employee_number = $isAdmin ? $request->employee_number : null;
        $user->department = $isAdmin ? $request->department : null;
        $user->password = bcrypt('12345678');

        if ($imagePath) {
            $user->avatar = $imagePath;
        }

        $user->save();

        if ($isAdminLike) {
            session()->forget('mobile_otp');

            return redirect()
                ->route('users')
                ->with('success', $request->role . ' successfully created');
        }

        $latestAd = AreaDistributor::orderBy('id', 'desc')->first();

        $number = ($latestAd && $latestAd->ad_reference)
            ? intval(substr($latestAd->ad_reference, 4)) + 1
            : 1;

        $ad_reference = 'PRP' . str_pad($number, 5, '0', STR_PAD_LEFT);


        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/attachments'), $filename);
            $attachmentPath = 'uploads/attachments/' . $filename;
        }

        $storePicturePath = null;
        if ($request->hasFile('store_picture')) {
            $file = $request->file('store_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/store_pictures'), $filename);
            $storePicturePath = 'uploads/store_pictures/' . $filename;
        }

        $areaDistributor = new AreaDistributor();
        $areaDistributor->user_id = $user->id;
        $areaDistributor->ad_reference = $ad_reference;
        $areaDistributor->name = $fullName;
        $areaDistributor->store_code = $request->store_code;
        $areaDistributor->email_address = $request->email_address;
        $areaDistributor->contact_number = $normalizedContactNumber ?: $request->contact_number;
        $areaDistributor->facebook = $request->facebook;
        $areaDistributor->address = $request->address;
        $areaDistributor->delivery_address = $needsDeliveryAddress
            ? $request->delivery_address
            : null;

        $areaDistributor->street_address = $request->street_address;
        $areaDistributor->location_region = $request->location_region;
        $areaDistributor->location_province = $request->location_province;
        $areaDistributor->location_city = $request->location_city;
        $areaDistributor->location_barangay = $request->location_barangay;
        $areaDistributor->zipcode = $request->zipcode;

        $areaDistributor->business_name = $request->business_name;
        $areaDistributor->business_type = $request->business_type;
        if (Schema::hasColumn('area_distributors', 'tin')) {
            $areaDistributor->tin = trim((string) $request->tin) ?: null;
        }
        $areaDistributor->withholding_tax = $request->has('withholding_tax') ? 1 : 0;

        $areaDistributor->latitude = $request->latitude;
        $areaDistributor->longitude = $request->longitude;

        if ($attachmentPath) {
            $areaDistributor->attachment = $attachmentPath;
        }

        if ($storePicturePath && Schema::hasColumn('area_distributors', 'store_picture')) {
            $areaDistributor->store_picture = $storePicturePath;
        }

        if ($imagePath) {
            $areaDistributor->avatar = $imagePath;
        }

        $areaDistributor->status = "Active";
        $areaDistributor->save();


        $joiningDates = $request->input('joining_date', []);
        $awardedAreas = $request->input('area_name', []);
        $maxRows = max(count($joiningDates), count($awardedAreas));

        for ($i = 0; $i < $maxRows; $i++) {
            $areaName = $awardedAreas[$i] ?? null;
            $date = $joiningDates[$i] ?? null;

            if ($areaName && !empty($projectTags)) {
                foreach ($projectTags as $projectTag) {
                    AreaAd::create([
                        'ad_id' => $areaDistributor->id,
                        'ad_user_id' => $user->id,
                        'area_name' => $areaName,
                        'project_type' => $projectTag,
                        'joining_date' => $date,
                        'user_role' => $request->role,
                    ]);
                }
            } elseif ($areaName) {
                AreaAd::create([
                    'ad_id' => $areaDistributor->id,
                    'ad_user_id' => $user->id,
                    'area_name' => $areaName,
                    'project_type' => null,
                    'joining_date' => $date,
                    'user_role' => $request->role,
                ]);
            }
        }

        session()->forget('mobile_otp');

        return redirect()
            ->route('users')
            ->with('success', 'Successfully encoded');
    }

    public function sendMobileOtp(Request $request, SemaphoreSmsService $sms)
    {
        $validator = Validator::make($request->all(), [
            'contact_number' => 'required|regex:/^09[0-9]{9}$/',
        ], [
            'contact_number.regex' => 'Enter a valid Philippine mobile number starting with 09.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('contact_number'),
            ], 422);
        }

        $mobileNumber = $this->normalizeMobileNumber($request->contact_number);
        $lastSentAt = session('mobile_otp.last_sent_at');

        if ($lastSentAt && time() - (int) $lastSentAt < 60) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before sending another OTP.',
                'retry_after' => 60 - (time() - (int) $lastSentAt),
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $result = $sms->sendOtp($this->formatSemaphoreMobileNumber($mobileNumber), $otp);

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        session([
            'mobile_otp' => [
                'number' => $mobileNumber,
                'hash' => hash('sha256', $otp),
                'expires_at' => time() + 300,
                'attempts' => 0,
                'last_sent_at' => time(),
                'verified' => false,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent. Please check the mobile number.',
            'expires_in' => 300,
        ]);
    }

    public function verifyMobileOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_number' => 'required|regex:/^09[0-9]{9}$/',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $otpState = session('mobile_otp');
        $mobileNumber = $this->normalizeMobileNumber($request->contact_number);

        if (!$otpState || !isset($otpState['number'], $otpState['hash'], $otpState['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please request a new OTP.',
            ], 422);
        }

        if ($otpState['number'] !== $mobileNumber) {
            return response()->json([
                'success' => false,
                'message' => 'This OTP was sent to a different mobile number.',
            ], 422);
        }

        if (time() > (int) $otpState['expires_at']) {
            session()->forget('mobile_otp');

            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new code.',
            ], 422);
        }

        $attempts = (int) array_get($otpState, 'attempts', 0) + 1;

        if (!hash_equals($otpState['hash'], hash('sha256', $request->otp))) {
            $otpState['attempts'] = $attempts;
            session(['mobile_otp' => $otpState]);

            if ($attempts >= 5) {
                session()->forget('mobile_otp');
                return response()->json([
                    'success' => false,
                    'message' => 'Too many incorrect attempts. Please request a new OTP.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Incorrect OTP. Please check the code and try again.',
            ], 422);
        }

        $otpState['verified'] = true;
        $otpState['verified_at'] = time();
        session(['mobile_otp' => $otpState]);

        return response()->json([
            'success' => true,
            'message' => 'Mobile number verified.',
        ]);
    }

    protected function normalizeMobileNumber($mobileNumber)
    {
        $digits = preg_replace('/\D+/', '', (string) $mobileNumber);

        if (preg_match('/^09[0-9]{9}$/', $digits)) {
            return $digits;
        }

        if (preg_match('/^639[0-9]{9}$/', $digits)) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

    protected function formatSemaphoreMobileNumber($mobileNumber)
    {
        $mobileNumber = $this->normalizeMobileNumber($mobileNumber);

        if (preg_match('/^09[0-9]{9}$/', $mobileNumber)) {
            return '63' . substr($mobileNumber, 1);
        }

        return $mobileNumber;
    }

    protected function isVerifiedMobileNumber(Request $request, $mobileNumber)
    {
        $otpState = session('mobile_otp');

        return $otpState
            && array_get($otpState, 'verified') === true
            && array_get($otpState, 'number') === $mobileNumber
            && time() <= (int) array_get($otpState, 'expires_at', 0);
    }

    // public function store(Request $request)
    // {
    //     $latestAd = AreaDistributor::orderBy('id', 'desc')->first();

    //     $number = ($latestAd && $latestAd->ad_reference)
    //         ? intval(substr($latestAd->ad_reference, 4)) + 1
    //         : 1;

    //     $ad_reference = 'PRAD' . str_pad($number, 5, '0', STR_PAD_LEFT);

    //     $imagePath = null;

    //     if ($request->hasFile('avatar')) {
    //         $file = $request->file('avatar');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('uploads/area_distributor'), $filename);
    //         $imagePath = 'uploads/area_distributor/' . $filename;
    //     }

    //     // ✅ Attachment fix
    //     $attachmentPath = null;
    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('uploads/attachments'), $filename);
    //         $attachmentPath = 'uploads/attachments/' . $filename;
    //     }

    //     $fullAddress = $request->address;

    //     $user = new User;
    //     $user->name = $request->name;
    //     $user->email = $request->email_address;
    //     $user->address = $fullAddress;
    //     $user->role = $request->role;
    //     $user->type = $request->type;
    //     $user->birthdate = $request->birthdate;
    //     $user->password = bcrypt('12345678');

    //     if ($imagePath) {
    //         $user->avatar = $imagePath;
    //     }

    //     $user->save();

    //     $areaDistributor = new AreaDistributor;
    //     $areaDistributor->user_id = $user->id;
    //     $areaDistributor->ad_reference = $ad_reference;
    //     $areaDistributor->name = $request->name;
    //     $areaDistributor->store_code = $request->store_code;
    //     $areaDistributor->email_address = $request->email_address;
    //     $areaDistributor->contact_number = $request->contact_number;
    //     $areaDistributor->facebook = $request->facebook;
    //     $areaDistributor->address = $fullAddress;

    //     // ✅ ZIPCODE SAVED PROPERLY
    //     $areaDistributor->location_region = $request->location_region;

    //     $areaDistributor->business_name = $request->business_name;
    //     $areaDistributor->business_type = $request->business_type;
    //     $areaDistributor->joining_date = $request->joining_date;
    //     $areaDistributor->latitude = $request->latitude;
    //     $areaDistributor->longitude = $request->longitude;

    //     if ($attachmentPath) {
    //         $areaDistributor->attachment = $attachmentPath;
    //     }

    //     if ($imagePath) {
    //         $areaDistributor->avatar = $imagePath;
    //     }

    //     $areaDistributor->status = "Active";
    //     $areaDistributor->save();

    //     $areas = $request->input('area_name', []);

    //     foreach ($areas as $area) {
    //         AreaAd::create([
    //             'ad_id' => $areaDistributor->id,
    //             'ad_user_id' => $user->id,
    //             'area_name' => $area,
    //             'user_role' => $request->role,
    //             'joining_date' => $request->joining_date
    //         ]);
    //     }

    //     return redirect()->route('users')->with('success', 'Successfully encoded');
    // }

    public function view(){

        if(auth()->user()->role == "Dealer")
        {
            $profile = Dealer::where('user_id',auth()->user()->id)->first();
             $transactions = TransactionDetail::where('dealer_id',$profile->user_id)->get();
        }
        else
        {
            $profile = Client::where('user_id',auth()->user()->id)->first();
            $transactions = TransactionDetail::where('client_id',$profile->id)->get();
        }
       
       return view('view_profile',
            array(
                'profile' => $profile,  
                'transactions' => $transactions,
            )
        );
    }

    public function generatePartnerCode(Request $request)
    {
        $role = $request->role;
        $year = date('Y');

        // Role Prefix Mapping
        $prefixMap = [
            'Provincial Distributor' => 'PD',
            'Area Distributor' => 'AD',
            'Mega Dealer' => 'MD',
        ];

        if (!isset($prefixMap[$role])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role selected.'
            ]);
        }

        $prefix = $prefixMap[$role];

        // Get latest code for this role
        $latestUser = AreaDistributor::where('store_code', 'like', $prefix . '-' . $year . '-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;

        if ($latestUser) {
            $lastCode = $latestUser->store_code;

            // Example: PD-2026-0007
            $explode = explode('-', $lastCode);

            if (isset($explode[2])) {
                $nextNumber = intval($explode[2]) + 1;
            }
        }

        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $generatedCode = $prefix . '-' . $year . '-' . $formattedNumber;

        return response()->json([
            'success' => true,
            'code' => $generatedCode
        ]);
    }

    public function checkDuplicate(Request $request)
    {
        $exists = User::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('mothers_name', $request->mothers_name)
            ->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function checkMothersName(Request $request)
    {
        $mothersName = trim($request->input('mothers_name', ''));
        $firstName = trim($request->input('first_name', ''));
        $middleName = trim($request->input('middle_name', ''));
        $lastName = trim($request->input('last_name', ''));

        if ($firstName !== '' && $lastName !== '') {
            $exists = User::whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower($firstName)])
                ->whereRaw("LOWER(TRIM(COALESCE(middle_name, ''))) = ?", [mb_strtolower($middleName)])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower($lastName)])
                ->exists();

            if ($exists) {
                return response()->json([
                    'exists' => true
                ]);
            }
        }

        if ($mothersName === '') {
            return response()->json([
                'exists' => false
            ]);
        }

        $exists = User::whereRaw(
            'LOWER(TRIM(mothers_name)) = ?',
            [mb_strtolower($mothersName)]
        )->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function getZipCode(Request $request)
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

    public function show($id)
    {
        try {
            $user = User::with([
                'dealer:id,user_id,address,status',
                'client:id,user_id,address,status'
            ])->findOrFail($id);

            return response()->json([
                'id' => $user->id,
                'name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'role' => $user->role ?? '',
                'can_edit' => $user->can_edit,
                'can_add' => $user->can_add,
                'can_delete' => $user->can_delete,

                'can_edit_rewards' => $user->can_edit_rewards,
                'can_add_rewards' => $user->can_add_rewards,
                'can_delete_rewards' => $user->can_delete_rewards,
                'can_access_transactions' => $user->can_access_transactions,
                'can_access_distributors' => $user->can_access_distributors,
                'can_access_dealers' => $user->can_access_dealers,
                'can_access_customers' => $user->can_access_customers,
                'can_access_purchase_orders' => $user->can_access_purchase_orders,
                'can_access_inventory' => $user->can_access_inventory,
                'can_access_reports' => $user->can_access_reports,
                'can_access_settings' => $user->can_access_settings,
                'access_permissions' => $this->decodeAccessPermissions($user->access_permissions ?? null),
                'address' => optional($user->dealer)->address
                    ?? optional($user->client)->address
                    ?? $user->address
                    ?? '',
                'status' => optional($user->dealer)->status
                    ?? optional($user->client)->status
                    ?? ''
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $request->id,
            ]);

            $user = User::findOrFail($request->id);

            DB::beginTransaction();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            // OPTIONAL: update related tables
            if ($user->role === 'Dealer' && $user->dealer) {
                $user->dealer->address = $request->address ?? $user->dealer->address;
                $user->dealer->save();
            }

            if ($user->role === 'Client' && $user->client) {
                $user->client->address = $request->address ?? $user->client->address;
                $user->client->save();
            }

            if ($user->role === 'Admin') {
                $user->address = $request->address ?? $user->address;
                $user->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Update failed'
            ], 500);
        }
    }

    public function updateAccess(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'can_edit' => 'nullable|in:on,off',
            'can_add' => 'nullable|in:on,off',
            'can_delete' => 'nullable|in:on,off',
            'can_edit_rewards' => 'nullable|in:on,off',
            'can_add_rewards' => 'nullable|in:on,off',
            'can_delete_rewards' => 'nullable|in:on,off',
            'can_access_transactions' => 'nullable|in:on,off',
            'can_access_distributors' => 'nullable|in:on,off',
            'can_access_dealers' => 'nullable|in:on,off',
            'can_access_customers' => 'nullable|in:on,off',
            'can_access_purchase_orders' => 'nullable|in:on,off',
            'can_access_inventory' => 'nullable|in:on,off',
            'can_access_reports' => 'nullable|in:on,off',
            'can_access_settings' => 'nullable|in:on,off',
            'access_permissions' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->id);

        if (!in_array($user->role, ['Admin', 'SEDP'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Access can only be assigned to Admin or SEDP users.'
            ], 422);
        }

        $permissions = $this->sanitizeAccessPermissions(
            json_decode($request->input('access_permissions', '{}'), true) ?: []
        );

        $hasPermission = function ($module, $submodule = null, $action = null) use ($permissions) {
            if (!isset($permissions[$module]) || !is_array($permissions[$module])) {
                return false;
            }

            foreach ($permissions[$module] as $key => $actions) {
                if ($submodule !== null && $key !== $submodule) {
                    continue;
                }

                if (!is_array($actions)) {
                    continue;
                }

                if ($action === null && !empty($actions)) {
                    return true;
                }

                if ($action !== null && in_array($action, $actions, true)) {
                    return true;
                }
            }

            return false;
        };

        $user->can_edit = $hasPermission('users', 'accounts', 'edit') ? 'on' : null;
        $user->can_add = $hasPermission('users', 'accounts', 'add') ? 'on' : null;
        $user->can_delete = $hasPermission('users', 'accounts', 'delete') ? 'on' : null;

        $user->can_edit_rewards = $hasPermission('settings', 'rewards', 'edit') ? 'on' : null;
        $user->can_add_rewards = $hasPermission('settings', 'rewards', 'add') ? 'on' : null;
        $user->can_delete_rewards = $hasPermission('settings', 'rewards', 'delete') ? 'on' : null;
        $user->can_access_transactions = $hasPermission('transactions') ? 'on' : null;
        $user->can_access_distributors = $hasPermission('distributors') ? 'on' : null;
        $user->can_access_dealers = $hasPermission('dealers') ? 'on' : null;
        $user->can_access_customers = $hasPermission('customers') ? 'on' : null;
        $user->can_access_purchase_orders = $hasPermission('purchase_orders') ? 'on' : null;
        $user->can_access_inventory = $hasPermission('inventory') ? 'on' : null;
        $user->can_access_reports = $hasPermission('reports') ? 'on' : null;
        $user->can_access_settings = $hasPermission('settings') ? 'on' : null;
        $user->access_permissions = json_encode($permissions);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User access updated successfully.'
        ]);
    }

    private function decodeAccessPermissions($permissions)
    {
        if (!$permissions) {
            return [];
        }

        $decoded = json_decode($permissions, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeAccessPermissions(array $permissions)
    {
        $allowed = [
            'users' => ['accounts'],
            'distributors' => ['records'],
            'dealers' => ['records'],
            'customers' => ['records'],
            'transactions' => ['sales'],
            'purchase_orders' => ['adpo'],
            'inventory' => ['stock'],
            'settings' => ['items', 'rewards', 'campaigns'],
            'reports' => ['sales', 'operations', 'sedp'],
        ];
        $allowedActions = ['view', 'add', 'edit', 'delete'];
        $clean = [];

        foreach ($allowed as $module => $submodules) {
            if (!isset($permissions[$module]) || !is_array($permissions[$module])) {
                continue;
            }

            foreach ($submodules as $submodule) {
                if (!isset($permissions[$module][$submodule]) || !is_array($permissions[$module][$submodule])) {
                    continue;
                }

                $actions = array_values(array_intersect($allowedActions, $permissions[$module][$submodule]));

                if (!empty($actions)) {
                    $clean[$module][$submodule] = $actions;
                }
            }
        }

        return $clean;
    }

    public function datatable(Request $request)
    {
        $query = User::with([
            'dealer:id,user_id,address,status',
            'client:id,user_id,address,status',
            'ad'
        ])->select('users.*');

        if ($request->role) {
            $query->where('role', $request->role);
        }

        return \Yajra\DataTables\Facades\DataTables::of($query)

            ->addColumn('name', function ($user) {
                $name = $user->name
                    ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

                return strtoupper($name);
            })

            ->addColumn('email', function ($user) {
                return strtoupper($user->email ?? '');
            })

            ->addColumn('role', function ($user) {
                $role = strtoupper($user->role ?? 'N/A');
                $roleClass = 'is-muted';

                if (in_array($user->role, ['Admin', 'SEDP'], true)) {
                    $roleClass = 'is-admin';
                } elseif ($user->role === 'Client') {
                    $roleClass = 'is-client';
                } elseif ($user->role === 'Dealer') {
                    $roleClass = 'is-dealer';
                } elseif (in_array($user->role, ['Area Distributor', 'Provincial Distributor', 'Mega Dealer'], true)) {
                    $roleClass = 'is-distributor';
                }

                return '<span class="user-pill '.$roleClass.'">'.$role.'</span>';
            })

            // ->addColumn('address', function ($user) {
            //     return optional($user->dealer)->address
            //         ?? optional($user->client)->address
            //         ?? $user->address
            //         ?? 'N/A';
            //     return strtoupper($address);
            // })
            ->addColumn('address', function ($user) {

                $address = optional($user->dealer)->address
                    ?? optional($user->client)->address
                    ?? $user->address
                    ?? 'N/A';

                return strtoupper($address);
            })

            ->addColumn('status', function ($user) {
                $status = optional($user->dealer)->status
                    ?? optional($user->client)->status
                    ?? optional($user->ad)->status
                    ?? 'N/A';

                $statusText = strtoupper($status);
                $statusClass = strcasecmp($status, 'Active') === 0
                    ? 'is-active'
                    : (strcasecmp($status, 'Inactive') === 0 ? 'is-inactive' : 'is-muted');

                return '<span class="user-pill '.$statusClass.'">'.$statusText.'</span>';
            })

            ->addColumn('actions', function ($user) {
                $currentUser = auth()->user();
                $currentUserIsAdminLike = $currentUser && in_array($currentUser->role, ['Admin', 'SEDP'], true);
                $canEdit = $currentUserIsAdminLike && $currentUser->can_edit === 'on';
                $canAdd = $currentUserIsAdminLike && $currentUser->can_add === 'on';

                $status = optional($user->dealer)->status
                    ?? optional($user->client)->status
                    ?? optional($user->ad)->status
                    ?? 'N/A';

                $buttons = [];

                if ($status !== 'Inactive') {
                    if ($user->role === 'Dealer' && $user->dealer) {
                        $buttons[] = '<a href="'.url('view-dealer/'.$user->dealer->id).'" class="btn-custom btn-view-custom" title="View Details"><i class="fas fa-eye"></i></a>';
                    } elseif ($user->role === 'Client' && $user->client) {
                        $buttons[] = '<a href="'.url('view-client/'.$user->client->id).'" class="btn-custom btn-view-custom" title="View Details"><i class="fas fa-eye"></i></a>';
                    } elseif (in_array($user->role, ['Area Distributor', 'Provincial Distributor', 'Mega Dealer'], true) && $user->ad) {
                        $buttons[] = '<a href="'.url('view-ad/'.$user->ad->id).'" class="btn-custom btn-view-custom" title="View Details"><i class="fas fa-eye"></i></a>';
                    }
                }

                if ($canEdit) {
                    $buttons[] = '<button type="button" class="btn-custom btn-edit-custom btn-edit-user" data-id="'.$user->id.'" title="Edit User"><i class="fas fa-edit"></i></button>';
                }

                if (in_array($user->role, ['Admin', 'SEDP'], true) && ($canEdit || $canAdd)) {
                    $buttons[] = '<button type="button" class="btn-custom btn-access-custom btn-access-user" data-id="'.$user->id.'" title="Access Control"><i class="fas fa-key"></i></button>';
                }

                return '<div class="action-buttons">'.implode('', $buttons).'</div>';
            })

            ->filter(function ($query) use ($request) {

                if ($request->has('search') && $request->search['value']) {

                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {

                        $q->whereRaw(
                            "CONCAT(users.first_name, ' ', users.last_name) LIKE ?",
                            ["%{$search}%"]
                        )
                        ->orWhere('users.name', 'LIKE', "%{$search}%")
                        ->orWhere('users.email', 'LIKE', "%{$search}%")
                        ->orWhere('users.role', 'LIKE', "%{$search}%")
                        ->orWhere('users.address', 'LIKE', "%{$search}%");

                        $q->orWhereHas('dealer', function ($q2) use ($search) {
                            $q2->where('address', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%");
                        });

                        $q->orWhereHas('client', function ($q2) use ($search) {
                            $q2->where('address', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%");
                        });

                    });
                }
            })

            ->rawColumns(['status', 'role', 'actions'])
            ->make(true);
    }
}
