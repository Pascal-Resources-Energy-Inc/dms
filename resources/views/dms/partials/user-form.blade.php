@php
    $formUser = $formUser ?? null;
    $passwordRequired = $passwordRequired ?? false;
    $submitText = $submitText ?? 'Save User';
    $showSubmit = $showSubmit ?? true;
    $avatar = old('avatar_path', $formUser && $formUser->avatar_path ? $formUser->avatar_path : null);
    $selectedProjectTag = old('project_tag');
    if ($selectedProjectTag === null) {
        $decodedProjectTags = $formUser && $formUser->project_tag ? json_decode($formUser->project_tag, true) : null;
        $selectedProjectTag = is_array($decodedProjectTags)
            ? $decodedProjectTags
            : array_filter([$formUser ? $formUser->project_tag : null]);
    }
    $awardedAreas = $awardedAreas ?? collect();
    $joiningDates = old('joining_date', $awardedAreas->pluck('joining_date')->all());
    $riseAreas = old('area_name_rise', $awardedAreas->pluck('area_name_rise')->all());
    $genesisAreas = old('area_name_genesis', $awardedAreas->pluck('area_name_genesis')->all());
    $areaRows = max(1, count((array) $joiningDates), count((array) $riseAreas), count((array) $genesisAreas));
    $selectedRegion = old('region', $formUser ? $formUser->region : '');
    $regions = [
        'Region I (Ilocos Region)' => '0100000000',
        'Region II (Cagayan Valley)' => '0200000000',
        'Region III (Central Luzon)' => '0300000000',
        'Region IV-A (CALABARZON)' => '0400000000',
        'MIMAROPA Region' => '1700000000',
        'Region V (Bicol Region)' => '0500000000',
        'Region VI (Western Visayas)' => '0600000000',
        'Region VII (Central Visayas)' => '0700000000',
        'Region VIII (Eastern Visayas)' => '0800000000',
        'Region IX (Zamboanga Peninsula)' => '0900000000',
        'Region X (Northern Mindanao)' => '1000000000',
        'Region XI (Davao Region)' => '1100000000',
        'Region XII (SOCCSKSARGEN)' => '1200000000',
        'National Capital Region (NCR)' => '1300000000',
        'Cordillera Administrative Region (CAR)' => '1400000000',
        'Region XIII (Caraga)' => '1600000000',
        'Bangsamoro Autonomous Region In Muslim Mindanao (BARMM)' => '1900000000',
    ];
@endphp

<div class="user-form dms-user-form">
    <div class="form-hero">
        <div class="form-hero-main">
            <span class="form-kicker">{{ $formUser ? 'Edit DMS Account' : 'New DMS Account' }}</span>
            <h3>{{ $formUser ? ($formUser->name ?: 'User Profile') : 'Create User Profile' }}</h3>
            <p>Complete the account, business, delivery, and map details in one guided form.</p>
        </div>
        <div class="form-hero-side">
            <img class="avatar-preview js-avatar-preview" src="{{ $avatar ? asset($avatar) : asset('images/gazlite.png') }}" alt="Avatar preview">
            <div>
                <strong>{{ old('user_reference', $formUser ? $formUser->user_reference : '') ?: 'Pending Code' }}</strong>
                <small>Partner reference</small>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading">
            <span class="section-icon">1</span>
            <div>
                <strong>Account Type</strong>
                <small>Role access, project tag, avatar, and generated partner code.</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-lg-4">
                <label class="font-weight-bold">Role Type <span class="text-danger">*</span></label>
                <select class="form-control js-select2 js-role" name="role" data-placeholder="Select role type" required>
                    @foreach (\App\User::roles() as $role => $label)
                        <option value="{{ $role }}" {{ old('role', $formUser ? $formUser->roleKey() : \App\User::ROLE_PROVINCIAL_DISTRIBUTOR) === $role ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-lg-4">
                <label class="font-weight-bold">Project Tag</label>
                <div class="project-card-grid">
                    @foreach (['Project Rise', 'Project Genesis', 'Regular'] as $tag)
                        <label class="project-card">
                            <input class="js-project-tag" type="checkbox" name="project_tag[]" value="{{ $tag }}" {{ in_array($tag, (array) $selectedProjectTag) ? 'checked' : '' }}>
                            <span>{{ $tag }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group col-lg-4">
                <label class="font-weight-bold">Partner Code</label>
                <input class="form-control js-reference" value="{{ old('user_reference', $formUser ? $formUser->user_reference : '') }}" placeholder="Auto-generated after save" readonly>
                <small class="form-text text-muted">Generated from role, year, and sequence.</small>
            </div>
        </div>

        <div class="form-row align-items-center">
            <div class="form-group col-lg-8">
                <label class="font-weight-bold">Avatar</label>
                <input class="form-control-file js-avatar-input" type="file" name="avatar" accept="image/jpeg,image/png">
                <small class="form-text text-muted">JPG or PNG, max 2MB.</small>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading">
            <span class="section-icon">2</span>
            <div>
                <strong>Personal Information</strong>
                <small>Login credentials are stored in the users table.</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="font-weight-bold">First Name <span class="text-danger">*</span></label>
                <input class="form-control text-uppercase" name="first_name" value="{{ old('first_name', $formUser ? $formUser->first_name : '') }}" placeholder="Enter First Name" required>
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Middle Name</label>
                <input class="form-control text-uppercase" name="middle_name" value="{{ old('middle_name', $formUser ? $formUser->middle_name : '') }}" placeholder="Enter Middle Name">
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Last Name <span class="text-danger">*</span></label>
                <input class="form-control text-uppercase" name="last_name" value="{{ old('last_name', $formUser ? $formUser->last_name : '') }}" placeholder="Enter Last Name" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="font-weight-bold">Email Address <span class="text-danger">*</span></label>
                <input class="form-control" type="email" name="email" value="{{ old('email', $formUser ? $formUser->email : '') }}" placeholder="Enter Email Address" required>
            </div>
            <div class="form-group col-md-6">
                <label class="font-weight-bold">Mobile Number</label>
                <input class="form-control" name="mobile_number" value="{{ old('mobile_number', $formUser ? $formUser->mobile_number : '') }}" placeholder="09xxxxxxxxx" maxlength="20">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Birthdate</label>
                <input class="form-control js-birthdate" type="date" name="birthdate" value="{{ old('birthdate', $formUser && $formUser->birthdate ? $formUser->birthdate->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group col-md-2">
                <label class="font-weight-bold">Age</label>
                <input class="form-control js-age" type="number" name="age" value="{{ old('age', $formUser ? $formUser->age : '') }}" readonly>
            </div>
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Facebook</label>
                <input class="form-control" name="facebook" value="{{ old('facebook', $formUser ? $formUser->facebook : '') }}" placeholder="Enter Facebook">
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Mother's Full Name</label>
                <input class="form-control text-uppercase" name="mother_name" value="{{ old('mother_name', $formUser ? $formUser->mother_name : '') }}" placeholder="Enter Mother's Name">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="font-weight-bold">Password {{ $passwordRequired ? '*' : '' }}</label>
                <input class="form-control" type="password" name="password" placeholder="{{ $passwordRequired ? 'Enter password' : 'Leave blank to keep current password' }}" {{ $passwordRequired ? 'required' : '' }}>
            </div>
            <div class="form-group col-md-6">
                <label class="font-weight-bold">Confirm Password {{ $passwordRequired ? '*' : '' }}</label>
                <input class="form-control" type="password" name="password_confirmation" {{ $passwordRequired ? 'required' : '' }}>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading">
            <span class="section-icon">3</span>
            <div>
                <strong>Business Information</strong>
                <small>Business profile, attachment, tax setup, territory, and status.</small>
            </div>
        </div>

        <div class="admin-fields mb-3">
            <div class="warehouse-panel">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div>
                        <strong>Admin Warehouse</strong>
                        <small class="d-block text-muted">Choose which warehouse this admin account will manage.</small>
                    </div>
                </div>
                <div class="warehouse-options">
                    @foreach (['lubao' => 'Lubao', 'guinobatan' => 'Guinobatan'] as $warehouseKey => $warehouseLabel)
                        <label class="warehouse-card">
                            <input type="radio" name="warehouse" value="{{ $warehouseKey }}" {{ old('warehouse', $formUser ? $formUser->warehouse : '') === $warehouseKey ? 'checked' : '' }}>
                            <span class="warehouse-content">
                                <span class="warehouse-icon">W</span>
                                <span>
                                    <strong>{{ $warehouseLabel }}</strong>
                                    <small>{{ $warehouseKey === 'lubao' ? 'Pampanga warehouse' : 'Albay warehouse' }}</small>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="admin-fields mb-3">
            <div class="warehouse-panel">
                <strong class="d-block mb-3">Employment Information</strong>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Designation</label>
                        <input class="form-control text-uppercase" name="designation" value="{{ old('designation', $formUser ? $formUser->designation : '') }}" placeholder="Enter Designation">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Employee Number</label>
                        <input class="form-control text-uppercase" name="employee_number" value="{{ old('employee_number', $formUser ? $formUser->employee_number : '') }}" placeholder="Enter Employee Number">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Department</label>
                        <input class="form-control text-uppercase" name="department" value="{{ old('department', $formUser ? $formUser->department : '') }}" placeholder="Enter Department">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Business Name</label>
                <input class="form-control text-uppercase" name="business_name" value="{{ old('business_name', $formUser ? $formUser->business_name : '') }}" placeholder="Enter Business Name">
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Business Type</label>
                <select class="form-control js-select2" name="business_type" data-placeholder="Select business type">
                    <option value="">Select Business Type</option>
                    @foreach (['Sari Sari Store', 'Mini Mart', 'Retail Shop', 'Wholesale', 'Grocery'] as $type)
                        <option value="{{ $type }}" {{ old('business_type', $formUser ? $formUser->business_type : '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Territory</label>
                <input class="form-control" name="territory" value="{{ old('territory', $formUser ? $formUser->territory : '') }}" placeholder="Example: Legazpi City 1">
            </div>
        </div>

        <div class="form-row align-items-end">
            <div class="form-group col-md-5">
                <label class="font-weight-bold">Upload Attachment</label>
                <input class="form-control-file" type="file" name="attachment">
                @if ($formUser && $formUser->attachment_path)
                    <small class="form-text text-muted">Current file: {{ basename($formUser->attachment_path) }}</small>
                @endif
            </div>
            <div class="form-group col-md-3">
                <label class="font-weight-bold">Status</label>
                <select class="form-control js-select2" name="status" data-placeholder="Select status" required>
                    <option value="active" {{ old('status', $formUser ? $formUser->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $formUser ? $formUser->status : '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <div class="custom-control custom-switch mt-4">
                    <input class="custom-control-input" type="checkbox" id="withholding_tax_{{ $formUser ? $formUser->id : 'new' }}" name="withholding_tax" value="1" {{ old('withholding_tax', $formUser && $formUser->withholding_tax ? '1' : '') ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="withholding_tax_{{ $formUser ? $formUser->id : 'new' }}">Withholding Tax Enabled</label>
                </div>
            </div>
        </div>

        <div class="warehouse-panel">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <strong>Awarded Areas Per Project</strong>
                    <small class="d-block text-muted">Add joining dates and assigned project areas.</small>
                </div>
                <button class="btn btn-sm btn-primary js-add-area-row" type="button">Add Row</button>
            </div>
            <div class="js-area-rows">
                @for ($index = 0; $index < $areaRows; $index++)
                    <div class="form-row align-items-end project-row">
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Joining Date</label>
                            <input class="form-control" type="date" name="joining_date[]" value="{{ $joiningDates[$index] ?? '' }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Awarded Area (Project Rise)</label>
                            <input class="form-control" name="area_name_rise[]" value="{{ $riseAreas[$index] ?? '' }}" placeholder="Enter Area">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Awarded Area (Project Genesis)</label>
                            <input class="form-control" name="area_name_genesis[]" value="{{ $genesisAreas[$index] ?? '' }}" placeholder="Enter Area">
                        </div>
                        <div class="form-group col-md-1">
                            <button class="btn btn-outline-danger btn-block js-remove-area-row" type="button">&times;</button>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading">
            <span class="section-icon">4</span>
            <div>
                <strong>Location Details</strong>
                <small>Address and delivery destination for distributor accounts.</small>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Street Name, Building, House No.</label>
            <input class="form-control js-address-part" name="street_address" value="{{ old('street_address', $formUser ? $formUser->street_address : '') }}" placeholder="e.g., 1868 Kapalaran St">
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Region</label>
                <select class="form-control js-select2 js-address-part js-region" name="region" data-current="{{ $selectedRegion }}" data-placeholder="Select region">
                    <option value="">-- Select Region --</option>
                    @foreach ($regions as $regionName => $regionCode)
                        <option value="{{ $regionName }}" data-code="{{ $regionCode }}" {{ $selectedRegion === $regionName ? 'selected' : '' }}>{{ $regionName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Province</label>
                <select class="form-control js-select2 js-address-part js-province" name="province" data-current="{{ old('province', $formUser ? $formUser->province : '') }}" data-placeholder="Select or type province" disabled>
                    <option value="">-- Select Region First --</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">City/Municipality</label>
                <select class="form-control js-select2 js-address-part js-city" name="city" data-current="{{ old('city', $formUser ? $formUser->city : '') }}" data-placeholder="Select or type city" disabled>
                    <option value="">-- Select Province First --</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-8">
                <label class="font-weight-bold">Barangay</label>
                <select class="form-control js-select2 js-address-part js-barangay" name="barangay" data-current="{{ old('barangay', $formUser ? $formUser->barangay : '') }}" data-placeholder="Select or type barangay" disabled>
                    <option value="">-- Select City First --</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Zip Code</label>
                <input class="form-control js-address-part js-zip-code" name="zip_code" value="{{ old('zip_code', $formUser ? $formUser->zip_code : '') }}" placeholder="Auto generated" readonly>
            </div>
        </div>

        <div class="delivery-panel">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <strong>Distributor Delivery Details</strong>
                    <small class="d-block text-muted">Use a different delivery destination for Area or Provincial Distributor accounts.</small>
                </div>
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input js-same-address" type="checkbox" id="same_address_{{ $formUser ? $formUser->id : 'new' }}" name="delivery_same_as_address" value="1" {{ old('delivery_same_as_address', $formUser ? $formUser->delivery_same_as_address : true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="same_address_{{ $formUser ? $formUser->id : 'new' }}">Same as address</label>
                </div>
            </div>
            <label class="font-weight-bold">Delivery Address</label>
            <textarea class="form-control js-delivery-address" name="delivery_address" rows="3" placeholder="Enter delivery address">{{ old('delivery_address', $formUser ? $formUser->delivery_address : '') }}</textarea>
        </div>

        <div class="map-panel mt-3">
            <div class="d-flex justify-content-between flex-wrap">
                <div>
                    <strong>Pin Exact Location</strong>
                    <small class="d-block text-muted">Search from the address, use your current location, or drag the map pin.</small>
                </div>
                <div class="btn-group btn-group-sm mt-2 mt-sm-0" role="group">
                    <button class="btn btn-outline-primary js-locate-address" type="button">Locate Address</button>
                    <button class="btn btn-outline-primary js-use-my-location" type="button">Use My Location</button>
                    <button class="btn btn-outline-secondary js-default-pin" type="button">Default Pin</button>
                </div>
            </div>
            <div class="alert alert-info py-2 px-3 mt-3 mb-0 js-map-status" style="display:none;"></div>
            <div class="leaflet-map js-location-map mt-3"></div>
            <div class="pin-readout mt-2">
                Current Pin Location:
                <span>Latitude: <span class="js-pin-lat">{{ old('latitude', $formUser ? $formUser->latitude : '13.1391000') }}</span></span>,
                <span>Longitude: <span class="js-pin-lng">{{ old('longitude', $formUser ? $formUser->longitude : '123.7438000') }}</span></span>
            </div>
            <div class="form-row mt-3">
                <div class="form-group col-md-6">
                    <label class="font-weight-bold">Latitude</label>
                    <input class="form-control js-latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $formUser ? $formUser->latitude : '13.1391000') }}">
                </div>
                <div class="form-group col-md-6">
                    <label class="font-weight-bold">Longitude</label>
                    <input class="form-control js-longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $formUser ? $formUser->longitude : '123.7438000') }}">
                </div>
            </div>
            <label class="font-weight-bold">Complete Address Preview</label>
            <textarea class="form-control bg-light js-complete-address" name="complete_address" rows="2" readonly>{{ old('complete_address', $formUser ? $formUser->complete_address : '') }}</textarea>
        </div>
    </div>

    @if ($showSubmit)
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-lg px-4" type="submit">{{ $submitText }}</button>
        </div>
    @endif
</div>
