<div id="new_users" class="modal fade modal-select2" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myModalLabel">
                    New Users
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method='POST' action='{{url('new-user')}}' id="newUserForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="hidden_latitude">
                <input type="hidden" name="longitude" id="hidden_longitude">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Role Type&nbsp;<span class="text-danger">*</span></label>
                            <select id="roleFilter2" name="role" class="form-control select2 shadow-sm mb-2" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Provincial Distributor">Provincial Distributor</option>
                                <option value="Area Distributor">Area Distributor</option>
                                <option value="Mega Dealer">Mega Dealer</option>
                            </select>
                            <label class="form-label fw-semibold business-fields project-tag-fields">Project Tag</label>
                            <div class="border rounded p-2 bg-light business-fields project-tag-fields">
                                <label class="project-card">
                                    <input type="checkbox" name="type[]" value="Project Rise">
                                    <span><i class="bi bi-graph-up text-success"></i> Project Rise</span>
                                </label>
                                <label class="project-card">
                                    <input type="checkbox" name="type[]" value="Project Genesis">
                                    <span><i class="bi bi-lightning text-primary"></i> Project Genesis</span>
                                </label>
                                <label class="project-card">
                                    <input type="checkbox" name="type[]" value="Regular">
                                    <span><i class="bi bi-person-badge text-warning"></i> Regular</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="avatar-wrapper mx-auto mb-2">
                                <img id="avatar"
                                    src="{{ asset('design/assets/images/profile/user-1.png') }}"
                                    onerror="this.src='{{ asset('design/assets/images/profile/user-1.png') }}'"
                                    alt="Avatar Preview">
                            </div>

                            <label for="inputImage" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-upload"></i> Upload Image
                            </label>
                            <input type="file" 
                                accept="image/*" 
                                name="avatar" 
                                id="inputImage" 
                                hidden 
                                onchange="uploadImage(this)">
                            
                            <small class="d-block text-muted mt-1">
                                JPG, PNG (Max: 2MB)
                            </small><br>
                            <div class="input-group shadow-sm business-fields mb-2">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-upc-scan"></i>
                                </span>
                                <input 
                                    type="text"
                                    class="form-control"
                                    id="store_code"
                                    name="store_code"
                                    placeholder="Auto Generated Partner Code"
                                    readonly
                                    required
                                >
                            </div>
                            <small class="text-muted business-fields">
                                This code is automatically generated based on role and project tag.
                            </small>
                        </div>
                        {{-- <div class="col-md-4 mb-3">
                            <label class="form-label">Role Type&nbsp;<span class="text-danger">*</span></label>
                            <select id="roleFilter2" name="role" class="form-control select2" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Provincial Distributor">Provincial Distributor</option>
                                <option value="Area Distributor">Area Distributor</option>
                                <option value="Mega Dealer">Mega Dealer</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 business-fields">
                            <label class="form-label">User Type&nbsp;<span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-control select2" required>
                                <option value="">Select Role</option>
                                <option value="Project Rise">For Project Rise</option>
                                <option value="Project Genesis">For Project Genesis</option>
                                <option value="Both">Both</option>
                                <option value="Ordinary">Ordinary</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 business-fields">
                            <label class="form-label">
                                Partner Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="store_code" name="store_code" placeholder="Auto Generated Partner Code" readonly required>
                        </div> --}}
                        <!-- Avatar Upload -->
                        <div class="fs-6 fw-bold col-md-12 mb-3">
                            <i class="bi bi-person"></i> Personal Information
                        </div>
                        {{-- <div class="col-md-12 mb-2">
                            <label class="form-label" for="name">Full Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="name" name="name" placeholder="Enter Full Name" required/>
                        </div> --}}
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="name">First Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="first_name" name="first_name" placeholder="Enter First Name" data-uppercase required/>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="middle_name">Middle Name</label>
                            <input type="text" class="form-control required" id="middle_name" name="middle_name" placeholder="Enter Middle Name" data-uppercase>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="last_name">Last Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="last_name" name="last_name" placeholder="Enter Last Name" data-uppercase required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="email_address">Email Address&nbsp;<span class="text-danger">*</span></label>
                            <input type="email" class="form-control required" id="email_address" name="email_address" placeholder="Enter Email Address" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="contact_number">Mobile Number&nbsp;<span class="text-danger" id="contactRequiredMark">*</span></label>
                            <div class="mobile-verify-shell" id="mobileVerifyShell">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="09xxxxxxxxx" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, ''); toggleContactRequired();">
                                    <button type="button" class="btn btn-outline-primary" id="sendMobileOtpBtn">
                                        Send OTP
                                    </button>
                                </div>
                                <div class="mobile-otp-panel" id="mobileOtpPanel" hidden>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control otp-code-input" id="mobile_otp" placeholder="6-digit OTP" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                                        <button type="button" class="btn btn-primary" id="verifyMobileOtpBtn">Verify</button>
                                    </div>
                                    <div class="mobile-otp-help">
                                        <span id="mobileOtpStatus">Enter the code sent by Semaphore SMS.</span>
                                        <button type="button" class="btn btn-link p-0 mobile-resend-link" id="resendMobileOtpBtn" disabled>Resend</button>
                                    </div>
                                </div>
                                <div class="mobile-verify-status" id="mobileVerifyStatus" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 non-admin-personal-fields">
                            <label class="form-label" for="birthdate">Birthdate&nbsp;<span class="text-danger">*</span></label>
                            <input type="date" class="form-control required" id="birthdate" name='birthdate' placeholder="Enter Birthdate" required/>
                        </div>
                        <div class="col-md-2 mb-3 non-admin-personal-fields">
                            <label class="form-label" for="age">Age</label>
                            <input type="number" class="form-control" id="age" name="age" placeholder="Age" readonly>
                        </div>
                        <div class="col-md-6 mb-3 non-admin-personal-fields">
                            <label class="form-label" for="facebook">Facebook&nbsp;<span class="text-danger" id="facebookRequiredMark">*</span></label>
                            <input type="text" class="form-control" id="facebook" name="facebook" placeholder="Enter Facebook" oninput="toggleContactRequired()" data-uppercase>
                        </div>
                        <div class="col-md-12 mb-2 non-admin-personal-fields">
                            <label class="form-label" for="name">Mother's Full Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="mothers_name" name="mothers_name" placeholder="Enter Mother's Name" data-uppercase required/>
                            <div class="invalid-feedback" id="duplicateUserFeedback">
                                User with same First Name, Last Name, and Mother's Name already exists.
                            </div>
                        </div>
                        <div class="fs-6 fw-bold col-md-12 mb-3">
                            <i class="bi bi-building-fill"></i> Business Information
                        </div>
                        <div class="col-md-6 mb-3 business-fields">
                            <label class="form-label" for="business_name">Business Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="business_name" name="business_name" placeholder="Enter Business Name" data-uppercase required>
                        </div>
                        <div class="col-md-6 mb-3 business-fields">
                            <label class="form-label" for="business_type">Business Type&nbsp;<span class="text-danger">*</span></label>
                            {{-- <input type="text" class="form-control required" id="business_type" name="business_type" placeholder="Enter Business Type" required/> --}}
                            <select id="business_type" name="business_type" class="form-control select2" data-placeholder="Select Business Type" required>
                                <option value="">Select Business Type</option>
                                <option value="Sari Sari Store">Sari Sari Store</option>
                                <option value="Mini Mart">Mini Mart</option>
                                <option value="Retail Shop">Retail Shop</option>
                                <option value="Wholesale">Wholesale</option>
                                <option value="Grocery">Grocery</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 business-fields">
                            <label class="form-label" for="tin">TIN</label>
                            <input
                                type="text"
                                class="form-control"
                                id="tin"
                                name="tin"
                                value="{{ old('tin') }}"
                                placeholder="Enter TIN (Optional)"
                                maxlength="50"
                            >
                            <small class="form-text text-muted">Optional. Tax identification number if applicable.</small>
                        </div>
                        <div class="col-md-6 mb-3 business-fields">
                            <label class="form-label" for="store_picture">Store Picture</label>
                            <input
                                type="file"
                                class="form-control"
                                id="store_picture"
                                name="store_picture"
                                accept="image/*"
                            >
                            <small class="form-text text-muted">Optional. JPG or PNG, max 2MB.</small>
                        </div>
                        <div class="col-md-6 mb-3 attachment-field">
                            <label class="form-label" for="attachment">Upload Attachment&nbsp;<span class="text-danger">*</span></label>
                            <input type="file" class="form-control required" id="attachment" name="attachment" required> 
                        </div>
                        <div class="col-md-6 mb-3 business-fields">
                            <label class="form-label d-block">Withholding Tax</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="withholding_tax" name="withholding_tax" value="1">
                                <label class="form-check-label" for="withholding_tax">
                                    Enabled
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 location-fields">
                            <div class="location-panel">
                                <div class="location-panel-header">
                                    <div>
                                        <div class="fs-6 fw-bold">
                                            <i class="bi bi-geo-alt me-1"></i> Location Details
                                        </div>
                                        <small class="text-muted">Complete the address and the pin will update automatically.</small>
                                    </div>
                                    <span class="location-status" id="locationMapStatus">Waiting for address</span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <div class="location-input-grid">
                                            <div>
                                                <label class="form-label">Street Name, Building, House No. <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="street_address" id="street_address" value="{{ old('street_address') }}" placeholder="e.g., 1868 Kapalaran St" data-uppercase required>
                                            </div>
                                            <div>
                                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="location_region" name="location_region" data-placeholder="Select Region" required>
                                                    <option value="">-- Select Region --</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="location_province" name="location_province" data-placeholder="Select Province" required disabled>
                                                    <option value="">-- Select Region First --</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="location_city" name="location_city" data-placeholder="Select City/Municipality" required disabled>
                                                    <option value="">-- Select Province First --</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Barangay <span class="text-danger">*</span></label>
                                                <select class="form-control select2"
                                                        name="location_barangay"
                                                        id="location_barangay"
                                                        data-placeholder="Select Barangay"
                                                        required
                                                        disabled>
                                                    <option value="">-- Select City First --</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Zip Code&nbsp;<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="location_zipcode" name="zipcode" readonly required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="location-map-shell">
                                            <div class="location-map-toolbar">
                                                <div>
                                                    <strong>Exact Pin Location</strong>
                                                    <small>Drag the pin or click the map to adjust.</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshLocationMapBtn">
                                                    <i class="bi bi-arrow-clockwise"></i> Locate
                                                </button>
                                            </div>
                                            <div id="location_map"></div>
                                            <div class="location-coordinate-bar">
                                                <span><strong>Lat:</strong> <span id="display_lat">--</span></span>
                                                <span><strong>Lng:</strong> <span id="display_lng">--</span></span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label">Complete Address Preview</label>
                                            <textarea class="form-control location-preview" id="full_address_preview" rows="2" readonly></textarea>
                                            <input type="hidden" name="address" id="location_hidden" data-uppercase>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 admin-fields" style="display:none;">
                            <div class="warehouse-panel">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <div>
                                        <div class="fs-6 fw-bold">
                                            <i class="bi bi-box-seam me-1"></i> Admin Warehouse
                                        </div>
                                        <small class="text-muted">Choose which warehouse this admin account will manage.</small>
                                    </div>
                                </div>
                                <label class="form-label d-block">
                                    Warehouse <span class="text-muted">(Optional)</span>
                                </label>
                                <div class="warehouse-options">
                                    <label class="warehouse-card" for="warehouse_lubao">
                                        <input type="radio" name="warehouse" id="warehouse_lubao" value="lubao">
                                        <span class="warehouse-content">
                                            <span class="warehouse-icon"><i class="bi bi-building"></i></span>
                                            <span>
                                                <strong>Lubao</strong>
                                                <small>Pampanga warehouse</small>
                                            </span>
                                        </span>
                                    </label>

                                    <label class="warehouse-card" for="warehouse_guinobatan">
                                        <input type="radio" name="warehouse" id="warehouse_guinobatan" value="guinobatan">
                                        <span class="warehouse-content">
                                            <span class="warehouse-icon"><i class="bi bi-building"></i></span>
                                            <span>
                                                <strong>Guinobatan</strong>
                                                <small>Albay warehouse</small>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 admin-fields" style="display:none;">
                            <div class="warehouse-panel">
                                <div class="fs-6 fw-bold mb-3">
                                    <i class="bi bi-person-workspace me-1"></i> Employment Information
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="designation">Designation&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control admin-required" id="designation" name="designation" placeholder="Enter Designation" data-uppercase>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="employee_number">Employee Number&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control admin-required" id="employee_number" name="employee_number" placeholder="Enter Employee Number" data-uppercase>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="department">Department&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control admin-required" id="department" name="department" placeholder="Enter Department" data-uppercase>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 distributor-delivery-fields" style="display:none;">
                            <div class="delivery-panel">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <div class="fs-6 fw-bold">
                                            <i class="bi bi-truck me-1"></i> Distributor Delivery Details
                                        </div>
                                        <small class="text-muted">Use a different delivery destination for Area or Provincial Distributor accounts.</small>
                                    </div>
                                    <div class="form-check same-address-check">
                                        <input class="form-check-input" type="checkbox" name="same_as_delivery_address" id="same_as_delivery_address" value="1">
                                        <label class="form-check-label" for="same_as_delivery_address">
                                            Same as address
                                        </label>
                                    </div>
                                </div>
                                <label class="form-label" for="delivery_address">
                                    Delivery Address <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control distributor-delivery-required delivery-address-box" id="delivery_address" name="delivery_address" rows="3" placeholder="Enter delivery address" data-uppercase></textarea>
                            </div>
                        </div>
                        {{-- <div class="col-md-6 mb-2 business-fields">
                            <label class="form-label" for="joining_date">Joining Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control required" id="joining_date" name="joining_date" placeholder="Enter Joining Date" required/>
                        </div>
                        <div class="col-md-6 mb-2 project-area-field">
                            <label class="form-label">Awarded Area&nbsp;<span class="text-danger">*</span></label>
                            <select class="form-control area_name select2"
                                    id="area_name"
                                    name="area_name[]"
                                    data-placeholder="Select Awarded Area"
                                    multiple
                                    required>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}">{{ $center->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <!-- Dynamic Area Section -->
                        <div id="dynamic-area-wrapper" class="col-md-12 business-fields" style="display:none;">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body" style="padding: 10px 10px">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="bi bi-map me-2"></i>Awarded Areas
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="addProjectRow">
                                            <i class="bi bi-plus-lg"></i> Add Row
                                        </button>
                                    </div>
                                    <div id="projectRows"></div>
                                </div>
                            </div>
                        </div>
                        <template id="project-row-template">
                            <div class="row align-items-end project-row mb-3 border rounded-3 p-3 bg-white">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        Joining Date&nbsp;<span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="joining_date[]" class="form-control" required>
                                </div>
                                <div class="col-md-8 project-area">
                                    <label class="form-label fw-semibold">
                                        Awarded Area&nbsp;<span class="text-danger">*</span>
                                    </label>
                                    <select name="area_name[]" class="form-control area_name shadow-sm select2" data-placeholder="Select Area" required>
                                        <option value=""></option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->name }}">
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect"data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-info-subtle text-info  waves-effect" id="newUserSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #location_map {
        height: 420px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid #d6e1ec;
        background: #eef3f7;
        position: relative;
        z-index: 1;
    }
    .avatar-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .project-card {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: 0.25s ease;
        background: #fff;
    }

    .project-card:hover {
        border-color: #0d6efd;
        background: #f5f9ff;
        transform: translateY(-1px);
    }

    .project-card input {
        margin-top: 5px;
        transform: scale(1.2);
    }

    .warehouse-panel {
        border: 1px solid #d7e3f1;
        border-radius: 8px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 16px;
        margin-top: 12px;
        box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
    }

    .warehouse-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .warehouse-card {
        cursor: pointer;
        margin: 0;
    }

    .warehouse-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .warehouse-content {
        min-height: 78px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #d8dee6;
        border-radius: 8px;
        background: #fff;
        padding: 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .warehouse-card:hover .warehouse-content {
        border-color: #5b9bd5;
        background: #f5faff;
    }

    .warehouse-card input:checked + .warehouse-content {
        border-color: #0d6efd;
        background: #eef6ff;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .warehouse-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e7f1ff;
        color: #0d6efd;
        flex: 0 0 auto;
    }

    .warehouse-content strong,
    .warehouse-content small {
        display: block;
        line-height: 1.25;
    }

    .warehouse-content small {
        color: #6c757d;
        margin-top: 2px;
    }

    .delivery-panel {
        border: 1px solid #d7e3f1;
        border-radius: 8px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 16px;
        margin-top: 12px;
        box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
    }

    .same-address-check {
        min-height: 34px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        background: #fff;
    }

    .delivery-address-box {
        min-height: 92px;
        resize: vertical;
    }

    .delivery-address-box[readonly] {
        background: #eef2f6;
        cursor: not-allowed;
    }

    .location-panel {
        border: 1px solid #d7e3f1;
        border-radius: 8px;
        background: #ffffff;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
    }

    .location-panel-header,
    .location-map-toolbar,
    .location-coordinate-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .location-panel-header {
        border-bottom: 1px solid #e5edf5;
        padding-bottom: 12px;
        margin-bottom: 14px;
    }

    .location-status {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        background: #eef2f7;
        color: #52606d;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .location-status.is-locating {
        background: #fff7e6;
        color: #9a6500;
    }

    .location-status.is-ready {
        background: #eaf7ef;
        color: #16794c;
    }

    .location-status.is-warning {
        background: #fff0f0;
        color: #b42318;
    }

    .location-input-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .location-map-shell {
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        background: #f8fafc;
        padding: 10px;
        position: relative;
    }

    .location-map-toolbar {
        min-height: 40px;
        margin-bottom: 8px;
    }

    .location-map-toolbar strong,
    .location-map-toolbar small {
        display: block;
        line-height: 1.25;
    }

    .location-map-toolbar small {
        color: #6c757d;
        margin-top: 2px;
    }

    .location-coordinate-bar {
        flex-wrap: wrap;
        border: 1px solid #e3e9f1;
        border-radius: 8px;
        background: #fff;
        color: #495057;
        font-size: 12px;
        margin-top: 8px;
        padding: 8px 10px;
    }

    .location-coordinate-bar span {
        white-space: nowrap;
    }

    .location-preview {
        background: #f8fafc;
        border-color: #d8e3ee;
        resize: vertical;
    }

    .select2-container {
        width: 100% !important;
    }

    .mobile-verify-shell {
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        background: #fbfcfe;
        padding: 8px;
    }

    .mobile-verify-shell .input-group-text {
        background: #f1f6fb;
        color: #0d6efd;
    }

    .mobile-otp-panel {
        margin-top: 8px;
        border-top: 1px dashed #d4dde8;
        padding-top: 8px;
    }

    .otp-code-input {
        max-width: 150px;
        font-weight: 700;
        letter-spacing: 0;
        text-align: center;
    }

    .mobile-otp-help,
    .mobile-verify-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 6px;
        font-size: 12px;
        color: #6c757d;
    }

    .mobile-verify-status.is-success {
        color: #198754;
        font-weight: 700;
    }

    .mobile-verify-status.is-error {
        color: #dc3545;
        font-weight: 700;
    }

    .mobile-resend-link {
        font-size: 12px;
        text-decoration: none;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .warehouse-options {
            grid-template-columns: 1fr;
        }

        .mobile-verify-shell .input-group {
            display: grid;
            grid-template-columns: 42px 1fr;
        }

        .mobile-verify-shell .input-group .btn {
            grid-column: 1 / -1;
            border-radius: 6px;
            margin-top: 8px;
        }

        .mobile-otp-panel .d-flex,
        .mobile-otp-help {
            align-items: stretch !important;
            flex-direction: column;
        }

        .otp-code-input {
            max-width: none;
        }

        .location-panel-header,
        .location-map-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        #location_map {
            height: 340px;
        }
    }

</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function uploadImage(input) {
        const file = input.files[0];

        if (!file) return;

        // ✅ Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload a valid image file.');
            input.value = '';
            return;
        }

        // ✅ Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image must be less than 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            document.getElementById('avatar').src = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const BASE_URL = 'https://psgc.cloud/api';
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');
        const newUserForm = document.getElementById('newUserForm');
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const mothersNameInput = document.getElementById('mothers_name');
        const submitButton = newUserForm ? newUserForm.querySelector('button[type="submit"]') : null;
        const sameAsDeliveryAddressInput = document.getElementById('same_as_delivery_address');
        const deliveryAddressInput = document.getElementById('delivery_address');
        const contactInput = document.getElementById('contact_number');
        const sendMobileOtpBtn = document.getElementById('sendMobileOtpBtn');
        const resendMobileOtpBtn = document.getElementById('resendMobileOtpBtn');
        const verifyMobileOtpBtn = document.getElementById('verifyMobileOtpBtn');
        const mobileOtpInput = document.getElementById('mobile_otp');
        const mobileOtpPanel = document.getElementById('mobileOtpPanel');
        const mobileOtpStatus = document.getElementById('mobileOtpStatus');
        const mobileVerifyStatus = document.getElementById('mobileVerifyStatus');
        const locationMapStatus = document.getElementById('locationMapStatus');
        const refreshLocationMapBtn = document.getElementById('refreshLocationMapBtn');
        
        let map, marker;
        let currentLat = 14.6507, currentLng = 121.0494;
        let currentRegionName = '';
        let currentRegionCode = '';
        let currentProvinceName = '';
        let currentCityName = '';
        let geocodeCache = {};
        let geocodeTimeout = null;
        let lastGeocodedAddressKey = '';
        // let mothersNameTimeout = null;
        // let hasDuplicateMothersName = false;
        let duplicateTimeout = null;
        let hasDuplicateUser = false;
        let mobileOtpVerified = false;
        let verifiedMobileNumber = '';
        let resendTimer = null;

        function initModalSelect2(parent = document) {
            if (!window.jQuery || !$.fn || !$.fn.select2) {
                return;
            }

            const $modal = $('#new_users');
            const $parent = $(parent);
            const $selects = $parent.is('select.select2')
                ? $parent
                : $parent.find('select.select2');

            $selects.each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change.select2');
                    return;
                }

                $select.select2({
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : $(document.body),
                    placeholder: $select.data('placeholder') || 'Select Option',
                    allowClear: true,
                    theme: $select.data('select2-theme') || 'bootstrap-5'
                });
            });
        }

        initModalSelect2(document.getElementById('new_users'));

        function setLocationStatus(message, state) {
            if (!locationMapStatus) return;

            locationMapStatus.textContent = message || '';
            locationMapStatus.classList.remove('is-locating', 'is-ready', 'is-warning');

            if (state) {
                locationMapStatus.classList.add(state);
            }
        }

        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }

        function getMobileNumber() {
            return contactInput ? contactInput.value.replace(/[^0-9]/g, '') : '';
        }

        function isValidMobileNumber(number) {
            return /^09[0-9]{9}$/.test(number);
        }

        function mobileNeedsVerification() {
            return getMobileNumber() !== '';
        }

        function isMobileVerificationReady() {
            const number = getMobileNumber();
            return !number || (mobileOtpVerified && verifiedMobileNumber === number);
        }

        function updateSubmitState() {
            if (!submitButton) return;

            submitButton.disabled = hasDuplicateUser || !isMobileVerificationReady();
        }

        function setMobileStatus(message, type) {
            if (!mobileVerifyStatus) return;

            mobileVerifyStatus.textContent = message || '';
            mobileVerifyStatus.classList.remove('is-success', 'is-error');

            if (type) {
                mobileVerifyStatus.classList.add(type === 'success' ? 'is-success' : 'is-error');
            }
        }

        function setMobileOtpStatus(message) {
            if (mobileOtpStatus) {
                mobileOtpStatus.textContent = message || '';
            }
        }

        function setOtpLoading(isLoading, button, loadingText) {
            if (!button) return;

            if (isLoading) {
                button.dataset.originalText = button.textContent;
                button.textContent = loadingText;
                button.disabled = true;
            } else {
                button.textContent = button.dataset.originalText || button.textContent;
                button.disabled = false;
            }
        }

        function startResendCountdown(seconds) {
            let remaining = seconds || 60;

            clearInterval(resendTimer);

            if (!resendMobileOtpBtn) return;

            resendMobileOtpBtn.disabled = true;
            resendMobileOtpBtn.textContent = 'Resend in ' + remaining + 's';

            resendTimer = setInterval(function () {
                remaining -= 1;

                if (remaining <= 0) {
                    clearInterval(resendTimer);
                    resendMobileOtpBtn.disabled = false;
                    resendMobileOtpBtn.textContent = 'Resend';
                    return;
                }

                resendMobileOtpBtn.textContent = 'Resend in ' + remaining + 's';
            }, 1000);
        }

        function resetMobileVerification(message) {
            mobileOtpVerified = false;
            verifiedMobileNumber = '';

            if (mobileOtpInput) {
                mobileOtpInput.value = '';
            }

            if (mobileNeedsVerification()) {
                setMobileStatus(message || 'Send OTP to verify this mobile number.', 'error');
            } else {
                setMobileStatus('', null);
                if (mobileOtpPanel) {
                    mobileOtpPanel.hidden = true;
                }
            }

            updateSubmitState();
        }

        function handleOtpResponse(response) {
            return response.json().then(function (data) {
                if (!response.ok || data.success !== true) {
                    throw data;
                }

                return data;
            });
        }

        function sendMobileOtp() {
            const number = getMobileNumber();

            if (!isValidMobileNumber(number)) {
                setMobileStatus('Enter a valid 11-digit mobile number starting with 09.', 'error');
                if (contactInput) contactInput.focus();
                return;
            }

            resetMobileVerification('');
            setOtpLoading(true, sendMobileOtpBtn, 'Sending...');
            setOtpLoading(true, resendMobileOtpBtn, 'Sending...');

            fetch("{{ route('users.mobile-otp.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ contact_number: number })
            })
            .then(handleOtpResponse)
            .then(function (data) {
                if (mobileOtpPanel) {
                    mobileOtpPanel.hidden = false;
                }

                setMobileStatus(data.message || 'OTP sent successfully.', 'success');
                setMobileOtpStatus('Code expires in 5 minutes.');
                startResendCountdown(60);

                if (mobileOtpInput) {
                    mobileOtpInput.focus();
                }
            })
            .catch(function (error) {
                const message = error && error.message ? error.message : 'Unable to send OTP. Please try again.';
                setMobileStatus(message, 'error');

                if (error && error.retry_after) {
                    startResendCountdown(error.retry_after);
                }
            })
            .finally(function () {
                setOtpLoading(false, sendMobileOtpBtn, 'Sending...');

                if (!resendMobileOtpBtn || resendMobileOtpBtn.textContent.indexOf('Resend in') === -1) {
                    setOtpLoading(false, resendMobileOtpBtn, 'Sending...');
                }

                updateSubmitState();
            });
        }

        function verifyMobileOtp() {
            const number = getMobileNumber();
            const otp = mobileOtpInput ? mobileOtpInput.value.replace(/[^0-9]/g, '') : '';

            if (!isValidMobileNumber(number)) {
                setMobileStatus('Enter a valid mobile number before verifying.', 'error');
                return;
            }

            if (!/^[0-9]{6}$/.test(otp)) {
                setMobileStatus('Enter the 6-digit OTP.', 'error');
                if (mobileOtpInput) mobileOtpInput.focus();
                return;
            }

            setOtpLoading(true, verifyMobileOtpBtn, 'Checking...');

            fetch("{{ route('users.mobile-otp.verify') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    contact_number: number,
                    otp: otp
                })
            })
            .then(handleOtpResponse)
            .then(function (data) {
                mobileOtpVerified = true;
                verifiedMobileNumber = number;
                setMobileStatus(data.message || 'Mobile number verified.', 'success');
                setMobileOtpStatus('Verified. You can submit the form.');
                clearInterval(resendTimer);

                if (resendMobileOtpBtn) {
                    resendMobileOtpBtn.disabled = true;
                    resendMobileOtpBtn.textContent = 'Verified';
                }

                if (mobileOtpInput) {
                    mobileOtpInput.setAttribute('readonly', 'readonly');
                }
            })
            .catch(function (error) {
                const message = error && error.message ? error.message : 'OTP verification failed.';
                mobileOtpVerified = false;
                verifiedMobileNumber = '';
                setMobileStatus(message, 'error');
            })
            .finally(function () {
                setOtpLoading(false, verifyMobileOtpBtn, 'Checking...');
                updateSubmitState();
            });
        }

        window.toggleContactRequired = function () {
            const facebookInput = document.getElementById('facebook');
            const contactRequiredMark = document.getElementById('contactRequiredMark');
            const facebookRequiredMark = document.getElementById('facebookRequiredMark');

            if (!contactInput || !facebookInput) return;

            const hasContact = contactInput.value.trim() !== '';
            const hasFacebook = facebookInput.value.trim() !== '';

            contactInput.required = !hasFacebook;
            facebookInput.required = !hasContact;

            if (contactRequiredMark) {
                contactRequiredMark.style.display = contactInput.required ? 'inline' : 'none';
            }

            if (facebookRequiredMark) {
                facebookRequiredMark.style.display = facebookInput.required ? 'inline' : 'none';
            }

            updateSubmitState();
        };

        function calculateAge(birthdate) {
            if (!birthdate) return '';

            const today = new Date();
            const birthDate = new Date(birthdate);

            if (birthDate > today) return '';

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDifference = today.getMonth() - birthDate.getMonth();

            if (
                monthDifference < 0 ||
                (monthDifference === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            return age;
        }

        if (birthdateInput && ageInput) {
            birthdateInput.max = new Date().toISOString().split('T')[0];

            birthdateInput.addEventListener('change', function () {
                ageInput.value = calculateAge(this.value);
            });
        }

        function setDuplicateValidation(isDuplicate) {

            hasDuplicateUser = isDuplicate;

            [firstNameInput, lastNameInput, mothersNameInput].forEach(input => {
                input.classList.toggle('is-invalid', isDuplicate);
            });

            if (submitButton) {
                updateSubmitState();
            }
        }

        function checkDuplicateUser() {

            const first_name = firstNameInput.value.trim();
            const last_name = lastNameInput.value.trim();
            const mothers_name = mothersNameInput.value.trim();

            if (!first_name || !last_name || !mothers_name) {
                setDuplicateValidation(false);
                return;
            }

            fetch("{{ route('check.user.duplicate') }}", {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    first_name,
                    last_name,
                    mothers_name
                })

            })
            .then(response => response.json())
            .then(data => {

                setDuplicateValidation(data.exists === true);

            })
            .catch(error => {

                console.error('Duplicate check error:', error);

            });
        }
        [firstNameInput, lastNameInput, mothersNameInput].forEach(input => {

            input.addEventListener('input', function () {

                clearTimeout(duplicateTimeout);

                duplicateTimeout = setTimeout(() => {

                    checkDuplicateUser();

                }, 500);

            });

        });
        if (newUserForm) {

            newUserForm.addEventListener('submit', function (event) {

                if (hasDuplicateUser) {

                    event.preventDefault();

                    firstNameInput.focus();

                    return;
                }

                if (!isMobileVerificationReady()) {
                    event.preventDefault();
                    setMobileStatus('Please verify the mobile number before submitting.', 'error');

                    if (contactInput) {
                        contactInput.focus();
                    }

                    return;
                }

                syncDeliveryAddress();

                if (typeof show === 'function') {
                    show();
                }

            });

        }

        if (contactInput) {
            contactInput.addEventListener('input', function () {
                const number = getMobileNumber();

                if (mobileOtpInput) {
                    mobileOtpInput.removeAttribute('readonly');
                }

                if (!number) {
                    resetMobileVerification('');
                } else if (verifiedMobileNumber !== number) {
                    resetMobileVerification('Send OTP to verify this mobile number.');
                } else {
                    updateSubmitState();
                }
            });
        }

        if (mobileOtpInput) {
            mobileOtpInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        }

        if (sendMobileOtpBtn) {
            sendMobileOtpBtn.addEventListener('click', sendMobileOtp);
        }

        if (resendMobileOtpBtn) {
            resendMobileOtpBtn.addEventListener('click', sendMobileOtp);
        }

        if (verifyMobileOtpBtn) {
            verifyMobileOtpBtn.addEventListener('click', verifyMobileOtp);
        }

        updateSubmitState();

        // function setMothersNameValidation(isDuplicate) {
        //     hasDuplicateMothersName = isDuplicate;

        //     if (!mothersNameInput) return;

        //     mothersNameInput.classList.toggle('is-invalid', isDuplicate);

        //     if (submitButton) {
        //         submitButton.disabled = isDuplicate;
        //     }
        // }

        // function checkMothersName() {
        //     if (!mothersNameInput) return;

        //     const mothersName = mothersNameInput.value.trim();

        //     if (!mothersName) {
        //         setMothersNameValidation(false);
        //         return;
        //     }

        //     fetch("{{ route('check.mothers.name') }}", {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        //             'Accept': 'application/json'
        //         },
        //         body: JSON.stringify({
        //             mothers_name: mothersName
        //         })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         setMothersNameValidation(data.exists === true);
        //     })
        //     .catch(error => {
        //         console.error('Mother name check error:', error);
        //     });
        // }

        // if (mothersNameInput) {
        //     mothersNameInput.addEventListener('input', function () {
        //         clearTimeout(mothersNameTimeout);
        //         mothersNameTimeout = setTimeout(checkMothersName, 400);
        //     });
        // }

        // if (newUserForm) {
        //     newUserForm.addEventListener('submit', function (event) {
        //         if (hasDuplicateMothersName) {
        //             event.preventDefault();
        //             mothersNameInput.focus();
        //             return;
        //         }

        //         if (typeof show === 'function') {
        //             show();
        //         }
        //     });
        // }

        toggleContactRequired();

        function initMap() {
            map = L.map('location_map', {
                center: [currentLat, currentLng],
                zoom: 13
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            marker = L.marker([currentLat, currentLng], {
                draggable: true
            }).addTo(map);

            // 🔥 Fix rendering issue
            setTimeout(() => {
                map.invalidateSize();
            }, 200);

            updateCoordinates(currentLat, currentLng);

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });
        }

        // function updateCoordinates(lat, lng) {
        //     currentLat = lat;
        //     currentLng = lng;
        //     document.getElementById('display_lat').textContent = lat.toFixed(6);
        //     document.getElementById('display_lng').textContent = lng.toFixed(6);
        //     document.getElementById('hidden_latitude').value = lat.toFixed(6);
        //     document.getElementById('hidden_longitude').value = lng.toFixed(6);
        //     updateFullAddress();
        //     fetchZipCode(lat, lng);
        // }

        function updateCoordinates(lat, lng) {
            currentLat = lat;
            currentLng = lng;

            document.getElementById('display_lat').textContent = lat.toFixed(6);
            document.getElementById('display_lng').textContent = lng.toFixed(6);

            document.getElementById('hidden_latitude').value = lat;
            document.getElementById('hidden_longitude').value = lng;

            updateZipCode(lat, lng); // ✅ ONLY SOURCE OF ZIP UPDATE
        }

        function getSelectedText(selectId) {
            const el = document.getElementById(selectId);

            if (!el || el.selectedIndex === -1) return '';

            const text = el.options[el.selectedIndex].text.trim();

            // 🚫 ignore placeholders
            if (
                text.includes('Select') ||
                text.includes('Loading') ||
                text.includes('Error')
            ) {
                return '';
            }

            return text;
        }

        function getSelectedCode(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];

            return selectedOption ? (selectedOption.getAttribute('data-code') || selectElement.value) : '';
        }

        function refreshSelect2Element(selectElement) {
            if (!selectElement) {
                return;
            }

            const $select = $(selectElement);

            if (!$select.hasClass('select2')) {
                return;
            }

            initModalSelect2(selectElement);

            $select.trigger('change.select2');
        }

        function syncDeliveryAddress() {
            if (!sameAsDeliveryAddressInput || !deliveryAddressInput || !sameAsDeliveryAddressInput.checked) {
                return;
            }

            deliveryAddressInput.value = document.getElementById('location_hidden').value;
        }

        function updateFullAddress() {
            const street = document.getElementById('street_address').value.trim();
            const barangay = getSelectedText('location_barangay');
            const city = getSelectedText('location_city');
            const province = getSelectedText('location_province');
            const region = getSelectedText('location_region');
            const zipcode = document.getElementById('location_zipcode').value.trim(); // ✅ ADD THIS

            let parts = [];

            if (street) parts.push(street);
            if (barangay) parts.push(barangay);
            if (city) parts.push(city);

            // NCR logic
            const isNCR =
                region &&
                (region.toLowerCase().includes('ncr') ||
                region.toLowerCase().includes('national capital'));

            if (isNCR) {
                parts.push('Metro Manila');
            } else if (province) {
                parts.push(province);
            }

            // ✅ ADD ZIP CODE HERE (after province)
            if (zipcode) {
                parts.push(zipcode);
            }

            if (region) parts.push(region);

            const fullAddress = parts.join(', ');

            document.getElementById('full_address_preview').value = fullAddress;
            document.getElementById('location_hidden').value = fullAddress;
            syncDeliveryAddress();
        }

        if (sameAsDeliveryAddressInput && deliveryAddressInput) {
            sameAsDeliveryAddressInput.addEventListener('change', function () {
                deliveryAddressInput.readOnly = this.checked;
                syncDeliveryAddress();

                if (!this.checked) {
                    deliveryAddressInput.focus();
                }
            });
        }

        function showMapLoading() {
            const mapContainer = document.getElementById('location_map');
            let loadingDiv = document.getElementById('map-loading-overlay');
            
            if (!loadingDiv) {
                loadingDiv = document.createElement('div');
                loadingDiv.id = 'map-loading-overlay';
                loadingDiv.innerHTML = `
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                                background: rgba(255,255,255,0.95); padding: 20px; border-radius: 8px; 
                                box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1000; text-align: center;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div class="mt-2" style="font-size: 14px; color: #495057;">
                            <strong>Locating address...</strong>
                        </div>
                    </div>
                `;
                loadingDiv.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 999;';
                mapContainer.appendChild(loadingDiv);
            }
            loadingDiv.style.display = 'block';
        }

        function hideMapLoading() {
            const loadingDiv = document.getElementById('map-loading-overlay');
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }

        function getLocationParts() {
            return {
                street: document.getElementById('street_address').value.trim(),
                barangay: getSelectedText('location_barangay'),
                city: getSelectedText('location_city'),
                province: getSelectedText('location_province'),
                region: getSelectedText('location_region'),
                fullAddress: document.getElementById('location_hidden').value.trim()
            };
        }

        function hasCompleteLocationDetails(parts) {
            const provinceIsReady = parts.province || (
                parts.region &&
                (
                    parts.region.toLowerCase().includes('ncr') ||
                    parts.region.toLowerCase().includes('national capital')
                )
            );

            return Boolean(parts.street && parts.barangay && parts.city && provinceIsReady);
        }

        function scheduleLocationGeocode(force) {
            updateFullAddress();
            const parts = getLocationParts();

            if (!hasCompleteLocationDetails(parts)) {
                lastGeocodedAddressKey = '';
                setLocationStatus('Waiting for complete address', '');
                return;
            }

            const addressKey = [
                parts.street,
                parts.barangay,
                parts.city,
                parts.province,
                parts.region
            ].join('|').toLowerCase();

            if (!force && addressKey === lastGeocodedAddressKey) {
                return;
            }

            clearTimeout(geocodeTimeout);
            setLocationStatus('Locating address...', 'is-locating');

            geocodeTimeout = setTimeout(function () {
                lastGeocodedAddressKey = addressKey;
                geocodeAddress(parts);
            }, force ? 0 : 650);
        }

        async function geocodeAddress(parts) {
            if (!map || !marker) {
                lastGeocodedAddressKey = '';
                setLocationStatus('Map is loading...', 'is-locating');
                return;
            }

            const street = parts.street || '';
            const barangay = parts.barangay || '';
            const city = parts.city || '';
            const province = parts.province || '';
            const region = parts.region || '';
            const fullAddress = parts.fullAddress || '';
            const cacheKey = `${street}|${barangay}|${city}|${province}|${region}`;
            
            if (geocodeCache[cacheKey]) {
                const cached = geocodeCache[cacheKey];
                map.setView([cached.lat, cached.lng], 16);
                marker.setLatLng([cached.lat, cached.lng]);
                updateCoordinates(cached.lat, cached.lng);
                setLocationStatus('Pin updated', 'is-ready');
                return;
            }

            showMapLoading();
            
            try {
                const geocodeUrl = "{{ route('geocode.location') }}";
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    throw new Error('CSRF token missing');
                }

                const response = await fetch(geocodeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        street: street,
                        barangay: barangay,
                        city: city,
                        province: province,
                        region: region,
                        full_address: fullAddress
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const lat = parseFloat(data.lat);
                    const lng = parseFloat(data.lng);
                    
                    geocodeCache[cacheKey] = { lat, lng };
                    
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                    setLocationStatus('Pin updated', 'is-ready');
                } else {
                    console.log('Barangay not found, using city coordinates');
                    updateMapForCity(city);
                    setLocationStatus('Using city estimate', 'is-warning');
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                updateMapForCity(city);
                setLocationStatus('Using city estimate', 'is-warning');
            } finally {
                hideMapLoading();
            }
        }

        async function loadRegions() {
            try {
                const regionSelect = document.getElementById('location_region');

                if (regionSelect && regionSelect.options.length > 1) {
                    refreshSelect2Element(regionSelect);
                    return;
                }

                const response = await fetch(`${BASE_URL}/regions`);
                const regions = await response.json();
                
                regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                
                // regions.forEach(region => {
                //     const option = document.createElement('option');
                //     option.value = region.code;
                //     option.textContent = region.name;
                //     regionSelect.appendChild(option);
                // });

                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.name; // save region name instead of code
                    option.setAttribute('data-code', region.code); // keep code for API usage
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
                refreshSelect2Element(regionSelect);
            } catch (error) {
                console.error('Error loading regions:', error);
                alert('Failed to load regions. Please refresh the page.');
            }
        }

        function isNCR(regionCode, regionName) {
        return regionCode.startsWith('13') || 
                regionName.toLowerCase().includes('ncr') ||
                regionName.toLowerCase().includes('national capital');
        }


        document.getElementById('location_region').addEventListener('change', async function() {
            const regionCode = getSelectedCode(this);
            currentRegionCode = regionCode;
            currentRegionName = this.options[this.selectedIndex]?.text || '';

            const provinceSelect = document.getElementById('location_province');
            const citySelect = document.getElementById('location_city');
            const barangaySelect = document.getElementById('location_barangay');

            citySelect.innerHTML = '<option value="">-- Select City First --</option>';
            barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
            citySelect.disabled = true;
            barangaySelect.disabled = true;
            refreshSelect2Element(citySelect);
            refreshSelect2Element(barangaySelect);

            if (regionCode) {

                if (isNCR(regionCode, currentRegionName)) {

                    provinceSelect.innerHTML = '<option value="NCR" selected>Metro Manila</option>';
                    provinceSelect.disabled = true;
                    currentProvinceName = 'Metro Manila';
                    refreshSelect2Element(provinceSelect);

                    scheduleLocationGeocode(false);

                    await loadNCRCities(regionCode);

                } else {

                    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                    provinceSelect.disabled = false;
                    refreshSelect2Element(provinceSelect);

                    try {
                        provinceSelect.innerHTML = '<option value="">Loading...</option>';
                        refreshSelect2Element(provinceSelect);

                        const response = await fetch(`${BASE_URL}/regions/${regionCode}/provinces`);
                        const provinces = await response.json();

                        provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';

                        provinces.forEach(province => {
                            const option = document.createElement('option');
                            // option.value = province.code;
                            option.value = province.name; 
                            option.setAttribute('data-code', province.code);
                            option.textContent = province.name;
                            provinceSelect.appendChild(option);
                        });
                        refreshSelect2Element(provinceSelect);

                    } catch (error) {
                        console.error('Error loading provinces:', error);
                        provinceSelect.innerHTML = '<option value="">-- Error loading --</option>';
                        refreshSelect2Element(provinceSelect);
                    }
                }

            } else {
                provinceSelect.innerHTML = '<option value="">-- Select Region First --</option>';
                provinceSelect.disabled = true;
                refreshSelect2Element(provinceSelect);
            }

            scheduleLocationGeocode(false);
        });

        async function loadNCRCities(regionCode) {
            const citySelect = document.getElementById('location_city');
            
            try {
                citySelect.innerHTML = '<option value="">Loading...</option>';
                refreshSelect2Element(citySelect);
                
                const response = await fetch(`${BASE_URL}/regions/${regionCode}/cities-municipalities`);
                const cities = await response.json();
                
                cities.sort((a, b) => a.name.localeCompare(b.name));
                
                citySelect.innerHTML = '<option value="">-- Select City --</option>';
                citySelect.disabled = false;
                
                cities.forEach(city => {
                    const option = document.createElement('option');
                    // option.value = city.code;
                    option.value = city.name; 
                    option.setAttribute('data-code', city.code);
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                refreshSelect2Element(citySelect);
                
            } catch (error) {
                console.error('Error loading NCR cities:', error);
                citySelect.innerHTML = '<option value="">-- Error loading --</option>';
                refreshSelect2Element(citySelect);
                alert('Failed to load cities.');
            }
        }

        document.getElementById('location_province').addEventListener('change', async function() {
            const provinceCode = getSelectedCode(this);
            currentProvinceName = this.options[this.selectedIndex]?.text || '';
            
            const citySelect = document.getElementById('location_city');
            const barangaySelect = document.getElementById('location_barangay');

            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
            barangaySelect.disabled = true;
            refreshSelect2Element(citySelect);
            refreshSelect2Element(barangaySelect);

            if (provinceCode && provinceCode !== 'NCR') {
                try {
                    citySelect.innerHTML = '<option value="">Loading...</option>';
                    refreshSelect2Element(citySelect);
                    
                    const [citiesResponse, municipalitiesResponse] = await Promise.all([
                        fetch(`${BASE_URL}/provinces/${provinceCode}/cities`),
                        fetch(`${BASE_URL}/provinces/${provinceCode}/municipalities`)
                    ]);
                    
                    const cities = await citiesResponse.json();
                    const municipalities = await municipalitiesResponse.json();
                    
                    const allCities = [...cities, ...municipalities].sort((a, b) => 
                        a.name.localeCompare(b.name)
                    );
                    
                    citySelect.innerHTML = '<option value="">-- Select City --</option>';
                    citySelect.disabled = false;
                    
                    allCities.forEach(city => {
                        const option = document.createElement('option');
                        // option.value = city.code;
                        option.value = city.name; 
                        option.setAttribute('data-code', city.code);
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                    refreshSelect2Element(citySelect);
                } catch (error) {
                    console.error('Error loading cities:', error);
                    citySelect.innerHTML = '<option value="">-- Error loading --</option>';
                    refreshSelect2Element(citySelect);
                    alert('Failed to load cities. Please try again.');
                }
            } else {
                citySelect.disabled = true;
                refreshSelect2Element(citySelect);
            }
            scheduleLocationGeocode(false);
        });

        document.getElementById('location_city').addEventListener('change', async function() {
            const cityCode = getSelectedCode(this);
            currentCityName = this.options[this.selectedIndex]?.text || '';
            
            const barangaySelect = document.getElementById('location_barangay');

            barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
            refreshSelect2Element(barangaySelect);

            if (cityCode) {
                try {
                    barangaySelect.innerHTML = '<option value="">Loading...</option>';
                    barangaySelect.disabled = false;
                    refreshSelect2Element(barangaySelect);
                    
                    const response = await fetch(`${BASE_URL}/cities-municipalities/${cityCode}/barangays`);
                    const barangays = await response.json();
                    
                    barangays.sort((a, b) => a.name.localeCompare(b.name));
                    
                    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                    
                    barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        // option.value = barangay.code;
                        option.value = barangay.name; 
                        option.setAttribute('data-code', barangay.code);
                        option.textContent = barangay.name;
                        barangaySelect.appendChild(option);
                    });
                    refreshSelect2Element(barangaySelect);
                    
                    updateMapForCity(currentCityName);
                    
                } catch (error) {
                    console.error('Error loading barangays:', error);
                    barangaySelect.innerHTML = '<option value="">-- Error loading --</option>';
                    refreshSelect2Element(barangaySelect);
                    alert('Failed to load barangays. Please try again.');
                }
            } else {
                barangaySelect.disabled = true;
                refreshSelect2Element(barangaySelect);
            }
            scheduleLocationGeocode(false);
        });

        // async function fetchZipCode(lat, lng) {
        //     const zipInput = document.getElementById('location_zipcode');

        //     if (!lat || !lng) {
        //         zipInput.value = '';
        //         return;
        //     }

        //     try {
        //         const response = await fetch("{{ route('get.zipcode') }}", {
        //             method: "POST",
        //             headers: {
        //                 "Content-Type": "application/json",
        //                 "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        //             },
        //             body: JSON.stringify({
        //                 latitude: lat,
        //                 longitude: lng
        //             })
        //         });

        //         const data = await response.json();

        //         console.log("ZIP RESPONSE:", data);

        //         zipInput.value = data.zipcode ?? '';

        //     } catch (error) {
        //         console.error('ZIP error:', error);
        //         zipInput.value = '';
        //     }
        // }
        let zipTimeout = null;

        function updateZipCode(lat, lng) {
            if (!lat || !lng) return;

            clearTimeout(zipTimeout);

            zipTimeout = setTimeout(() => {
                fetch("{{ route('get.zipcode') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('location_zipcode').value = data.zipcode ?? '';
                    updateFullAddress();
                })
                .catch(err => {
                    console.error("Zip error:", err);
                    document.getElementById('location_zipcode').value = '';
                });

            }, 300);
        }
        
        document.getElementById('location_barangay').addEventListener('change', function () {
            scheduleLocationGeocode(false);
        });

        document.getElementById('street_address').addEventListener('input', function () {
            scheduleLocationGeocode(false);
        });

        if (refreshLocationMapBtn) {
            refreshLocationMapBtn.addEventListener('click', function () {
                scheduleLocationGeocode(true);
            });
        }

        function updateMapForCity(city) {
            if (!map || !marker) {
                return;
            }

            const cityCoordinates = {
                'Manila': [14.5995, 120.9842],
                'Quezon City': [14.6760, 121.0437],
                'Makati': [14.5547, 121.0244],
                'Pasig': [14.5764, 121.0851],
                'Taguig': [14.5176, 121.0509],
                'Caloocan': [14.6507, 120.9820],
                'Pasay': [14.5378, 121.0014],
                'Mandaluyong': [14.5794, 121.0359],
                'San Juan': [14.6019, 121.0355],
                'Marikina': [14.6507, 121.1029],
                'Valenzuela': [14.6938, 120.9830],
                'Las Piñas': [14.4454, 120.9830],
                'Parañaque': [14.4793, 121.0198],
                'Muntinlupa': [14.4083, 121.0416],
                'Malabon': [14.6625, 120.9570],
                'Navotas': [14.6674, 120.9402],
                'Pateros': [14.5437, 121.0685],
                
                'Angeles City': [15.1450, 120.5887],
                'Olongapo': [14.8294, 120.2828],
                'San Fernando': [15.0285, 120.6898],
                'Mabalacat': [15.2167, 120.5714],
                'Tarlac City': [15.4754, 120.5964],
                'Balanga': [14.6760, 120.5368],
                
                'Antipolo': [14.5860, 121.1756],
                'Tagaytay': [14.1090, 120.9610],
                'Bacoor': [14.4590, 120.9390],
                'Calamba': [14.2118, 121.1653],
                'Santa Rosa': [14.3123, 121.1114],
                'Batangas City': [13.7565, 121.0583],
                'Lipa': [13.9411, 121.1624],
                'Lucena': [13.9372, 121.6175],
                
                'Cebu City': [10.3157, 123.8854],
                'Mandaue City': [10.3237, 123.9223],
                'Lapu-Lapu City': [10.3103, 123.9494],
                'Bacolod': [10.6560, 122.9500],
                'Iloilo City': [10.7202, 122.5621],
                'Tacloban': [11.2443, 125.0038],
                'Dumaguete': [9.3068, 123.3054],
                
                'Davao City': [7.1907, 125.4553],
                'Cagayan de Oro': [8.4542, 124.6319],
                'Zamboanga City': [6.9214, 122.0790],
                'General Santos': [6.1164, 125.1716],
                'Butuan': [8.9475, 125.5406],
                'Iligan': [8.2280, 124.2452],
                'Cotabato City': [7.2231, 124.2452],
                
                'Baguio': [16.4023, 120.5960],
                'Dagupan': [16.0433, 120.3333],
                'Laoag': [18.1984, 120.5931],
                'Vigan': [17.5747, 120.3869],
                'Santiago': [16.6879, 121.5468],
                'Tuguegarao': [17.6132, 121.7270]
            };
            
            if (cityCoordinates[city]) {
                const coords = cityCoordinates[city];
                map.setView(coords, 14);
                marker.setLatLng(coords);
                updateCoordinates(coords[0], coords[1]);
            }
        }

        $('#new_users').on('shown.bs.modal', function () {
            initModalSelect2(this);
            loadRegions();

            setTimeout(() => {
                if (!map) {
                    initMap();
                } else {
                    map.invalidateSize();
                }
                scheduleLocationGeocode(false);
            }, 300); // delay is important
        });

    });
</script>
{{-- <script>
document.addEventListener("DOMContentLoaded", function () {

    // ADD NEW ROW
    document.addEventListener("click", function (e) {
        if (e.target.closest(".add-area")) {

            let wrapper = document.getElementById("area-wrapper");
            let firstRow = document.querySelector(".area-group");
            let newRow = firstRow.cloneNode(true);

            // Clear input values
            newRow.querySelectorAll("input").forEach(input => {
                input.value = "";
            });

            newRow.querySelectorAll("select").forEach(select => {
                Array.from(select.options).forEach(option => {
                    option.selected = false;
                });
            });

            wrapper.appendChild(newRow);
        }
    });

    // REMOVE ROW
    document.addEventListener("click", function (e) {
        if (e.target.closest(".remove-area")) {

            let rows = document.querySelectorAll(".area-group");

            if (rows.length > 1) {
                e.target.closest(".area-group").remove();
            } else {
                alert("At least one row is required.");
            }
        }
    });

});
</script> --}}

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK'
        }).then(() => {
            location.reload(); // ✅ refresh page
        });
    });
</script>
@endif
