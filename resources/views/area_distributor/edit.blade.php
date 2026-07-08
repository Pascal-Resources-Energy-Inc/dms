<div id="edit_area_distributor-{{ $ad->id }}" class="modal fade modal-select2 ad-edit-modal" tabindex="-1" aria-labelledby="editAreaDistributorTitle-{{ $ad->id }}" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <div class="ad-modal-heading">
                    <span class="ad-modal-icon"><i class="ti ti-building-store"></i></span>
                    <div>
                        <h4 class="modal-title" id="editAreaDistributorTitle-{{ $ad->id }}">Edit Partner Information</h4>
                        <small>{{ $ad->store_code }} - {{ $ad->business_name ?: 'Partner profile' }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ url('edit-ads/'.$ad->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="latitude" id="hidden_latitude_{{ $ad->id }}" value="{{ $ad->latitude }}">
                <input type="hidden" name="longitude" id="hidden_longitude_{{ $ad->id }}" value="{{ $ad->longitude }}">
                <div class="modal-body">
                    <div class="row">
                        @php
                            $selectedTypes = is_array($ad->userAds->type)
                                ? $ad->userAds->type
                                : json_decode($ad->userAds->type, true);
                            $selectedTypes = is_array($selectedTypes) ? $selectedTypes : [];
                            $awardedAreaOptions = collect($areas ?? []);

                            if ($awardedAreaOptions->isEmpty()) {
                                $awardedAreaOptions = collect($centers ?? []);
                            }

                            $existingAwardedAreaNames = $ad->areas->pluck('area_name');
                            $awardedAreaOptions = $awardedAreaOptions
                                ->pluck('name')
                                ->merge($existingAwardedAreaNames)
                                ->filter()
                                ->map(function ($name) {
                                    return trim((string) $name);
                                })
                                ->unique()
                                ->sort()
                                ->values();

                            $deletedAwardedAreas = $ad->relationLoaded('trashedAreas')
                                ? $ad->trashedAreas->whereIn('project_type', ['Project Rise', 'Project Genesis'])
                                : collect();
                        @endphp

                        <div class="col-md-6 mb-3">
                            <div class="ad-section-card h-100">
                                <div class="ad-section-title"><i class="ti ti-id"></i><span>Partner Tags</span></div>
                                <label class="form-label" for="store_code_{{ $ad->id }}">Store Code</label>
                                <input type="text" class="form-control mb-3" id="store_code_{{ $ad->id }}" name="store_code" placeholder="Enter Store Code" value="{{ $ad->store_code }}" readonly>
                                <label class="form-label fw-semibold">Project Tag</label>
                                <div class="ad-project-list">
                                    <label class="project-card">
                                        <input type="checkbox"
                                            class="project-type"
                                            name="type[]"
                                            value="Project Rise"
                                            {{ in_array('Project Rise', $selectedTypes ?? []) ? 'checked' : '' }}>
                                        <span><i class="bi bi-graph-up text-success"></i> Project Rise</span>
                                    </label>
                                    <label class="project-card">
                                        <input type="checkbox"
                                            class="project-type"
                                            name="type[]"
                                            value="Project Genesis"
                                            {{ in_array('Project Genesis', $selectedTypes ?? []) ? 'checked' : '' }}>
                                        <span><i class="bi bi-lightning text-primary"></i> Project Genesis</span>
                                    </label>
                                    <label class="project-card">
                                        <input type="checkbox"
                                            class="project-type"
                                            name="type[]"
                                            value="Regular"
                                            {{ in_array('Regular', $selectedTypes ?? []) ? 'checked' : '' }}>
                                        <span><i class="bi bi-person-badge text-warning"></i> Regular</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 text-center">
                            <div class="ad-section-card ad-profile-card h-100">
                                <div class="avatar-wrapper mx-auto mb-2">
                                    <img id="avatar-{{ $ad->id }}" 
                                        src="{{ $ad->avatar ? asset($ad->avatar) : asset('design/assets/images/profile/user-1.png') }}"
                                        alt="Partner photo">
                                </div>
                                <label for="inputImage-{{ $ad->id }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-upload"></i> Upload Image
                                </label>

                                <input type="file" 
                                    name="avatar"
                                    id="inputImage-{{ $ad->id }}"
                                    hidden
                                    accept="image/*"
                                    onchange="uploadAdImage(this, {{ $ad->id }})">

                                <small class="d-block text-muted mt-1">
                                    JPG, PNG (Max: 2MB)
                                </small>
                            </div>
                        </div>
                        {{-- <div class="col-md-12 mb-2">
                            <label class="form-label" for="name">Full Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="name" name="name" placeholder="Enter Full Name" value="{{ $ad->name }}" required/>
                        </div> --}}
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="first_name_{{ $ad->id }}">First Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="first_name_{{ $ad->id }}" name="first_name" placeholder="Enter First Name" data-uppercase value="{{ $ad->userAds->first_name }}" required/>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="middle_name_{{ $ad->id }}">Middle Name</label>
                            <input type="text" class="form-control required" id="middle_name_{{ $ad->id }}" name="middle_name" placeholder="Enter Middle Name" data-uppercase value="{{ $ad->userAds->middle_name }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" for="last_name_{{ $ad->id }}">Last Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="last_name_{{ $ad->id }}" name="last_name" placeholder="Enter Last Name" data-uppercase value="{{ $ad->userAds->last_name }}" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="email_address_{{ $ad->id }}">Email Address&nbsp;<span class="text-danger">*</span></label>
                            <input type="email" class="form-control required" id="email_address_{{ $ad->id }}" name="email_address" placeholder="Enter Email Address" value="{{ $ad->email_address }}" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="contact_number_{{ $ad->id }}">Contact Number&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="contact_number_{{ $ad->id }}" name="contact_number" placeholder="Enter Contact Number" value="{{ $ad->contact_number }}" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="birthdate_{{ $ad->id }}">Birthdate&nbsp;<span class="text-danger">*</span></label>
                            <input type="date" class="form-control required" id="birthdate_{{ $ad->id }}" name='birthdate' placeholder="Enter Birthdate" value="{{ $ad->userAds->birthdate }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="facebook_{{ $ad->id }}">Facebook</label>
                            <input type="text" class="form-control required" id="facebook_{{ $ad->id }}" name='facebook' placeholder="Enter Facebook" data-uppercase value="{{ $ad->facebook }}"/>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="ad-location-panel">
                                <div class="ad-location-title">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>Location Details</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label">Street Name, Building, House No.</label>
                                        <input type="text" class="form-control" name="street_address" id="street_address_{{ $ad->id }}" value="{{ old('street_address', $ad->street_address ?? '') }}" placeholder="e.g., 1868 Kapalaran St" data-uppercase>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Region <span class="text-danger">*</span></label>
                                        <select class="form-select ad-location-select" id="location_region_{{ $ad->id }}" name="location_region" required onclick="event.stopPropagation();">
                                            <option value="">-- Select Region --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Province <span class="text-danger">*</span></label>
                                        <select class="form-select ad-location-select" id="location_province_{{ $ad->id }}" name="location_province" required onclick="event.stopPropagation();" disabled>
                                            <option value="">-- Select Region First --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                                        <select class="form-select ad-location-select" id="location_city_{{ $ad->id }}" name="location_city" required onclick="event.stopPropagation();" disabled>
                                            <option value="">-- Select Province First --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Barangay <span class="text-danger">*</span></label>
                                        <select class="form-select ad-location-select"
                                                name="location_barangay"
                                                id="location_barangay_{{ $ad->id }}"
                                                required
                                                onclick="event.stopPropagation();"
                                                disabled>
                                            <option value="">-- Select City First --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Zip Code&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="location_zipcode_{{ $ad->id }}" name="zipcode" value="{{ old('zipcode', $ad->zipcode ?? '') }}" readonly required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-3">
                            @php
                                $savedDeliveryAddress = old('delivery_address', $ad->delivery_address ?? '');
                                $sameAsAddress = trim($savedDeliveryAddress) !== '' && trim($savedDeliveryAddress) === trim($ad->address ?? '');
                            @endphp
                            <div class="ad-delivery-panel">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div class="ad-delivery-title">
                                        <i class="bi bi-truck"></i>
                                        <div>
                                            <span>Delivery Address</span>
                                            <small>Use a separate destination when delivery differs from the partner address.</small>
                                        </div>
                                    </div>
                                    <div class="form-check ad-same-address-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="same_as_delivery_address"
                                               id="same_as_delivery_address_{{ $ad->id }}"
                                               value="1"
                                               {{ $sameAsAddress ? 'checked' : '' }}>
                                        <label class="form-check-label" for="same_as_delivery_address_{{ $ad->id }}">
                                            Same as address
                                        </label>
                                    </div>
                                </div>
                                <textarea class="form-control ad-delivery-address-box"
                                          id="delivery_address_{{ $ad->id }}"
                                          name="delivery_address"
                                          rows="3"
                                          placeholder="Enter delivery address"
                                          data-uppercase>{{ $sameAsAddress ? ($ad->address ?? '') : $savedDeliveryAddress }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="business_name_{{ $ad->id }}">Business Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="business_name_{{ $ad->id }}" name="business_name" placeholder="Enter Business Name" data-uppercase value="{{ $ad->business_name }}" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="business_type_{{ $ad->id }}">Business Type&nbsp;<span class="text-danger">*</span></label>
                            {{-- <input type="text" class="form-control required" id="business_type" name="business_type" placeholder="Enter Business Type" value="{{ $ad->business_type }}" required/> --}}
                            <select id="business_type_{{ $ad->id }}" name="business_type" class="form-select" data-placeholder="Select Business Type" required>
                                <option value="">Select Business Type</option>
                                <option value="Sari Sari Store" @if($ad->business_type == 'Sari Sari Store') selected @endif>Sari Sari Store</option>
                                <option value="Mini Mart" @if($ad->business_type == 'Mini Mart') selected @endif>Mini Mart</option>
                                <option value="Retail Shop" @if($ad->business_type == 'Retail Shop') selected @endif>Retail Shop</option>
                                <option value="Wholesale" @if($ad->business_type == 'Wholesale') selected @endif>Wholesale</option>
                                <option value="Grocery" @if($ad->business_type == 'Grocery') selected @endif>Grocery</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tin_{{ $ad->id }}">TIN</label>
                            <input
                                type="text"
                                class="form-control"
                                id="tin_{{ $ad->id }}"
                                name="tin"
                                placeholder="Enter TIN (Optional)"
                                value="{{ old('tin', $ad->tin) }}"
                                maxlength="50"
                            >
                            <small class="text-muted">Optional. Tax identification number if applicable.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="store_picture_{{ $ad->id }}">Store Picture</label>
                            <div class="ad-store-picture-editor">
                                <div class="ad-store-picture-preview">
                                    @if($ad->store_picture)
                                        <img src="{{ asset($ad->store_picture) }}" alt="{{ $ad->business_name ?: 'Store' }} picture">
                                    @else
                                        <div class="ad-store-picture-empty">
                                            <i class="ti ti-building-store"></i>
                                            <span>No store picture</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ad-store-picture-actions">
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="store_picture_{{ $ad->id }}"
                                        name="store_picture"
                                        accept="image/*"
                                    >
                                    <small class="text-muted d-block mt-1">Optional. Upload JPG or PNG up to 2MB.</small>
                                    @if($ad->store_picture)
                                        <a href="{{ asset($ad->store_picture) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-image"></i> View current store picture
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="attachment-{{ $ad->id }}">Attachment</label>
                            <input type="file" class="form-control" id="attachment-{{ $ad->id }}" name="attachment">
                            @if($ad->attachment)
                                <a href="{{ asset($ad->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-paperclip"></i> View current attachment
                                </a>
                            @endif
                        </div>
                         <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Withholding Tax</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="withholding_tax" value="1" {{ $ad->withholding_tax ? 'checked' : '' }}>
                                <label class="form-check-label">Enabled</label>
                            </div>
                        </div>
                        {{-- <div class="col-md-6 mb-2 business-fields">
                            <label class="form-label" for="joining_date">Joining Date<span class="text-danger">*</span></label>
                            <input type="date" class="form-control required" id="joining_date" name="joining_date" placeholder="Enter Joining Date" value="{{ $ad->joining_date }}">
                        </div> --}}
                        <div class="col-md-6">
                            <label for="status_{{ $ad->id }}" class="form-label">Status</label>
                            <select id="status_{{ $ad->id }}" name="status" class="form-select">
                                <option value="Active" @if($ad->status == 'Active') selected @endif>Active</option>
                                <option value="Inactive" @if($ad->status == 'Inactive') selected @endif>Inactive</option>
                            </select>
                        </div>
                        <input type="hidden" name="sync_project_areas" value="1">
                        <div id="dynamic-area-wrapper-{{ $ad->id }}" class="col-md-12 business-fields ad-awarded-area-wrapper">
                            <div class="ad-awarded-area-card">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div class="ad-awarded-area-title">
                                        <i class="ti ti-map-pin"></i>
                                        <div>
                                            <span>Awarded Areas Per Project</span>
                                            <small>Select Project Rise or Project Genesis above to assign areas.</small>
                                        </div>
                                    </div>
                                    <button type="button"
                                            class="btn btn-sm btn-primary ad-add-project-row"
                                            data-ad-id="{{ $ad->id }}">
                                        <i class="ti ti-plus"></i> Add Row
                                    </button>
                                </div>
                                <div id="projectRows-{{ $ad->id }}" class="ad-project-rows">
                                    @foreach($ad->areas->whereIn('project_type', ['Project Rise', 'Project Genesis'])->values() as $areaIndex => $area)
                                        <div class="ad-project-row" data-project-row>
                                            <input type="hidden" name="rows[{{ $areaIndex }}][id]" value="{{ $area->id }}">
                                            <div class="row align-items-end">
                                                <div class="col-md-3 mb-2">
                                                    <label class="form-label">Project Type <span class="text-danger">*</span></label>
                                                    <select class="form-select ad-row-project-type" name="rows[{{ $areaIndex }}][project_type]" required>
                                                        <option value="">Select Project</option>
                                                        <option value="Project Rise" {{ $area->project_type === 'Project Rise' ? 'selected' : '' }}>Project Rise</option>
                                                        <option value="Project Genesis" {{ $area->project_type === 'Project Genesis' ? 'selected' : '' }}>Project Genesis</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="form-label">Awarded Area <span class="text-danger">*</span></label>
                                                    <select class="form-select ad-row-area-name" name="rows[{{ $areaIndex }}][area_name]" required>
                                                        <option value="">Select Area</option>
                                                        @foreach($awardedAreaOptions as $areaOption)
                                                            <option value="{{ $areaOption }}" {{ $area->area_name === $areaOption ? 'selected' : '' }}>{{ $areaOption }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($awardedAreaOptions->isEmpty())
                                                        <small class="text-danger d-block mt-1">No awarded area options found.</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label class="form-label">Joining Date</label>
                                                    <input type="date" class="form-control" name="rows[{{ $areaIndex }}][joining_date]" value="{{ $area->joining_date }}">
                                                </div>
                                                <div class="col-md-1 mb-2">
                                                    <button type="button" class="btn btn-outline-danger ad-remove-project-row" title="Remove area">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="projectRowsEmpty-{{ $ad->id }}" class="ad-project-empty">
                                    <i class="ti ti-map-pin-off"></i>
                                    <span>No awarded area rows yet.</span>
                                </div>
                                @if($deletedAwardedAreas->isNotEmpty())
                                    <div class="ad-deleted-area-log">
                                        <div class="ad-deleted-area-title">
                                            <i class="ti ti-history"></i>
                                            <span>Deleted Awarded Areas</span>
                                        </div>
                                        @foreach($deletedAwardedAreas as $deletedArea)
                                            <div class="ad-deleted-area-item">
                                                <span>{{ $deletedArea->project_type }} - {{ $deletedArea->area_name }}</span>
                                                <small>Deleted: {{ optional($deletedArea->deleted_at)->format('M d, Y h:i A') ?? 'No timestamp' }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- <template id="project-row-template">
                            <div class="row align-items-end project-row mb-3 border rounded-3 p-3 bg-white">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Joining Date</label>
                                    <input type="date" name="joining_date[]" class="form-control" required>
                                </div>
                                <div class="col-md-4 project-rise-area" style="display:none;">
                                    <label class="form-label fw-semibold">Project Rise Area</label>
                                    <select class="form-control area_name select2" name="area_name_rise[]">
                                        @foreach($centers as $center)
                                            <option value="{{ $center->name }}">{{ $center->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 project-genesis-area" style="display:none;">
                                    <label class="form-label fw-semibold">Project Genesis Area</label>
                                    <select class="form-control area_name select2" name="area_name_genesis[]">
                                        @foreach($centers as $center)
                                            <option value="{{ $center->name }}">{{ $center->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-danger remove-row mt-4">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template> --}}
                        {{-- <template id="project-row-template">

                            <div class="row align-items-end project-row mb-3 border rounded-3 p-3 bg-white">

                                <div class="col-md-3">
                                    <label>Joining Date</label>

                                    <input
                                        type="date"
                                        class="form-control joining-date"
                                    >
                                </div>

                                <div class="col-md-4 project-rise-area d-none">

                                    <label>Project Rise Area</label>

                                    <select class="form-control select2 rise-select">
                                        <option value="">Select Area</option>

                                        @foreach($areas as $area)
                                            <option value="{{ $area->name }}">
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-4 project-genesis-area d-none">

                                    <label>Project Genesis Area</label>

                                    <select class="form-control select2 genesis-select">
                                        <option value="">Select Area</option>

                                        @foreach($centers as $center)
                                            <option value="{{ $center->name }}">
                                                {{ $center->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-1 text-center">
                                    <button
                                        type="button"
                                        class="btn btn-danger remove-row"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                            </div>

                        </template> --}}

                        {{-- <div class="col-md-12 mb-2">
                            <label class="form-label">Awarded Area&nbsp;<span class="text-danger">*</span></label>
                            @php
                                $selectedAreas = $ad->areas->pluck('area_name')->toArray();
                            @endphp

                            <select class="form-control area_name" name="area_name[]" multiple required>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}"
                                        {{ in_array($center->name, $selectedAreas) ? 'selected' : '' }}>
                                        {{ $center->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="col-md-12">
                            <div class="form-group">
                            <label>Pin Exact Location</span></label>
                            <div class="alert alert-warning d-flex align-items-start" role="alert">
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16" style="min-width: 24px; margin-right: 10px;">
                                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                </svg>
                                <div>
                                    <strong>Place an accurate pin</strong><br>
                                    <small>We will deliver to your map location. Please check if it is correct, else click the map to adjust the pin location.</small>
                                </div>
                            </div>
                            <div id="location_map_{{ $ad->id }}" class="location-map" style="height: 400px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <strong>Current Pin Location:</strong><br>
                                    Latitude: <span id="display_lat_{{ $ad->id }}">--</span>, Longitude: <span id="display_lng_{{ $ad->id }}">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>Complete Address Preview</label>
                            <textarea class="form-control bg-light" id="full_address_preview_{{ $ad->id }}" rows="2" readonly>{{ $ad->address }}</textarea>
                            <input type="hidden" name="address" id="location_hidden_{{ $ad->id }}" value="{{ $ad->address }}" data-uppercase>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light waves-effect" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary waves-effect"><i class="ti ti-device-floppy me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .location-map {
        height: 400px;
        width: 100%;
    }
    #edit_area_distributor-{{ $ad->id }} .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
    }
    #edit_area_distributor-{{ $ad->id }} .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 22px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-modal-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-modal-heading h4 {
        color: #111827;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.2;
        margin: 0;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-modal-heading small {
        color: #6b7280;
        display: block;
        margin-top: 3px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-modal-icon {
        align-items: center;
        background: #eaf4ff;
        border-radius: 10px;
        color: #0d6efd;
        display: inline-flex;
        flex: 0 0 42px;
        font-size: 22px;
        height: 42px;
        justify-content: center;
        width: 42px;
    }
    #edit_area_distributor-{{ $ad->id }} .modal-body {
        background: #f6f8fb;
        max-height: calc(100vh - 180px);
        overflow-y: auto;
        padding: 20px 22px;
    }
    #edit_area_distributor-{{ $ad->id }} .modal-body > .row {
        row-gap: 10px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-section-card,
    #edit_area_distributor-{{ $ad->id }} .ad-location-panel {
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        background: #ffffff;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }
    #edit_area_distributor-{{ $ad->id }} .ad-section-title {
        align-items: center;
        color: #1f2937;
        display: flex;
        font-weight: 700;
        gap: 8px;
        margin-bottom: 12px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-location-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-editor {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 12px;
        align-items: stretch;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-preview {
        min-height: 112px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f8fafc;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-preview img {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 112px;
        object-fit: cover;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-empty {
        display: grid;
        min-height: 112px;
        place-items: center;
        gap: 4px;
        color: #94a3b8;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-empty i {
        font-size: 28px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-store-picture-actions {
        min-width: 0;
        align-self: center;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-project-list {
        display: grid;
        gap: 8px;
    }
    #edit_area_distributor-{{ $ad->id }} .project-card {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        gap: 9px;
        margin: 0;
        padding: 10px 12px;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    #edit_area_distributor-{{ $ad->id }} .project-card:hover {
        background: #ffffff;
        border-color: #b6d4fe;
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.08);
    }
    #edit_area_distributor-{{ $ad->id }} .project-card input {
        flex: 0 0 auto;
    }
    #edit_area_distributor-{{ $ad->id }} .project-card span {
        align-items: center;
        display: flex;
        font-weight: 600;
        gap: 8px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-wrapper {
        display: none;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        padding: 16px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-title {
        align-items: flex-start;
        display: flex;
        gap: 10px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-title > i {
        align-items: center;
        background: #eaf4ff;
        border-radius: 8px;
        color: #0d6efd;
        display: inline-flex;
        flex: 0 0 36px;
        height: 36px;
        justify-content: center;
        width: 36px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-title span {
        color: #1f2937;
        display: block;
        font-weight: 700;
        line-height: 1.2;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-awarded-area-title small {
        color: #6b7280;
        display: block;
        line-height: 1.35;
        margin-top: 2px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-project-row {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 10px;
        padding: 12px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-remove-project-row {
        align-items: center;
        display: inline-flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-project-empty {
        align-items: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #6b7280;
        display: none;
        gap: 8px;
        justify-content: center;
        min-height: 64px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-log {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 8px;
        margin-top: 14px;
        padding: 12px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-title {
        align-items: center;
        color: #9a3412;
        display: flex;
        font-weight: 700;
        gap: 8px;
        margin-bottom: 8px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-item {
        align-items: center;
        border-top: 1px solid #fed7aa;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        padding: 8px 0;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-item:first-of-type {
        border-top: 0;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-item span {
        color: #7c2d12;
        font-weight: 600;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-deleted-area-item small {
        color: #9a3412;
        white-space: nowrap;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-location-select:disabled {
        background-color: #eef2f6;
        color: #6b7280;
        cursor: not-allowed;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-panel {
        border: 1px solid #d7e3f1;
        border-radius: 8px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        padding: 16px;
        box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-title {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-weight: 700;
        color: #1f2937;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-title i {
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
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-title span,
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-title small {
        display: block;
        line-height: 1.25;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-title small {
        color: #6b7280;
        font-weight: 400;
        margin-top: 2px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-same-address-check {
        min-height: 34px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        background: #fff;
        padding: 6px 10px;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-address-box {
        min-height: 92px;
        resize: vertical;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-delivery-address-box[readonly] {
        background: #eef2f6;
        cursor: not-allowed;
    }
    #edit_area_distributor-{{ $ad->id }} .form-label {
        font-weight: 600;
        color: #374151;
    }
    #edit_area_distributor-{{ $ad->id }} .ad-profile-card {
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    #edit_area_distributor-{{ $ad->id }} .avatar-wrapper {
        width: 116px;
        height: 116px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.16);
    }

    #edit_area_distributor-{{ $ad->id }} .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Project Box */
    .project-box {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    #edit_area_distributor-{{ $ad->id }} .location-map {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55);
    }
    #edit_area_distributor-{{ $ad->id }} .modal-footer {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 14px 22px;
    }
    @media (max-width: 767.98px) {
        #edit_area_distributor-{{ $ad->id }} .ad-store-picture-editor {
            grid-template-columns: 1fr;
        }
        #edit_area_distributor-{{ $ad->id }} .modal-header,
        #edit_area_distributor-{{ $ad->id }} .modal-body,
        #edit_area_distributor-{{ $ad->id }} .modal-footer {
            padding-left: 14px;
            padding-right: 14px;
        }
        #edit_area_distributor-{{ $ad->id }} .ad-modal-heading h4 {
            font-size: 16px;
        }
    }
</style>
@php
    static $adEditAssetsLoaded = false;
@endphp
@if(!$adEditAssetsLoaded)
@php
    $adEditAssetsLoaded = true;
@endphp
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function uploadAdImage(input, id) {
        const file = input.files[0];

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            Swal.fire('Error', 'Please upload a valid image file.', 'error');
            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Image must be less than 2MB.', 'error');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatar-' + id).src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>
@endif

<script>
    (function () {
        const adId = @json($ad->id);
        const modal = document.getElementById(`edit_area_distributor-${adId}`);

        if (!modal) return;

        const BASE_URL = 'https://psgc.cloud/api';
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const areaOptions = @json($awardedAreaOptions);
        const saved = {
            region: @json($ad->location_region ?? ''),
            province: @json($ad->location_province ?? ''),
            city: @json($ad->location_city ?? ''),
            barangay: @json($ad->location_barangay ?? ''),
            zipcode: @json($ad->zipcode ?? ''),
            address: @json($ad->address ?? ''),
            deliveryAddress: @json($ad->delivery_address ?? ''),
            lat: Number(@json($ad->latitude ?? null)) || 14.6507,
            lng: Number(@json($ad->longitude ?? null)) || 121.0494,
        };

        const fields = {
            street: document.getElementById(`street_address_${adId}`),
            region: document.getElementById(`location_region_${adId}`),
            province: document.getElementById(`location_province_${adId}`),
            city: document.getElementById(`location_city_${adId}`),
            barangay: document.getElementById(`location_barangay_${adId}`),
            zipcode: document.getElementById(`location_zipcode_${adId}`),
            map: document.getElementById(`location_map_${adId}`),
            latText: document.getElementById(`display_lat_${adId}`),
            lngText: document.getElementById(`display_lng_${adId}`),
            latInput: document.getElementById(`hidden_latitude_${adId}`),
            lngInput: document.getElementById(`hidden_longitude_${adId}`),
            addressPreview: document.getElementById(`full_address_preview_${adId}`),
            addressInput: document.getElementById(`location_hidden_${adId}`),
            deliveryAddress: document.getElementById(`delivery_address_${adId}`),
            sameAsDeliveryAddress: document.getElementById(`same_as_delivery_address_${adId}`),
        };

        if (!fields.street || !fields.region || !fields.province || !fields.city || !fields.barangay || !fields.zipcode || !fields.addressPreview || !fields.addressInput) {
            return;
        }

        let initialized = false;
        let map = null;
        let marker = null;
        let currentLat = saved.lat;
        let currentLng = saved.lng;
        let geocodeTimeout = null;
        let zipTimeout = null;
        let areaRowIndex = Date.now();

        function normalize(value) {
            return String(value || '').trim().toLowerCase();
        }

        function selectedOption(select) {
            return select && select.options ? select.options[select.selectedIndex] : null;
        }

        function selectedText(select) {
            const option = selectedOption(select);
            const text = option && option.text ? option.text.trim() : '';

            if (!text || text.includes('Select') || text.includes('Loading') || text.includes('Error')) {
                return '';
            }

            return text;
        }

        function selectedCode(select) {
            const option = selectedOption(select);
            return (option && option.dataset && option.dataset.code) || (option && option.value) || '';
        }

        function setOptions(select, placeholder, items, selectedValue = '') {
            const selected = normalize(selectedValue);
            let matchedValue = '';

            select.innerHTML = `<option value="">${placeholder}</option>`;

            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.name;
                option.textContent = item.name;

                if (item.code) {
                    option.dataset.code = item.code;
                }

                if (selected && (normalize(item.name) === selected || normalize(item.code) === selected)) {
                    matchedValue = item.name;
                }

                select.appendChild(option);
            });

            select.value = matchedValue;
            select.disabled = false;
        }

        function resetSelect(select, placeholder, disabled = true) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.value = '';
            select.disabled = disabled;
        }

        function escapeHtml(value) {
            return String(value === null || typeof value === 'undefined' ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function selectedProjectTags() {
            return Array.from(modal.querySelectorAll('.project-type:checked'))
                .map(input => input.value)
                .filter(value => ['Project Rise', 'Project Genesis'].includes(value));
        }

        function setRowInputsDisabled(row, disabled) {
            row.querySelectorAll('input, select').forEach(input => {
                input.disabled = disabled;
            });
        }

        function refreshRowProjectOptions(row, projects) {
            const select = row.querySelector('.ad-row-project-type');
            if (!select) return;

            const current = select.value;
            select.innerHTML = '<option value="">Select Project</option>';

            if (current && !projects.includes(current)) {
                const currentOption = document.createElement('option');
                currentOption.value = current;
                currentOption.textContent = current;
                select.appendChild(currentOption);
            }

            projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project;
                option.textContent = project;
                select.appendChild(option);
            });

            select.value = current || (projects[0] || '');
        }

        function syncProjectAreaVisibility(autoAddRow = false) {
            const projects = selectedProjectTags();
            const wrapper = document.getElementById(`dynamic-area-wrapper-${adId}`);
            const empty = document.getElementById(`projectRowsEmpty-${adId}`);
            const rows = Array.from(modal.querySelectorAll('[data-project-row]'));

            if (!wrapper) return;

            const shouldShow = projects.length > 0;
            wrapper.style.display = shouldShow ? 'block' : 'none';

            let visibleRows = 0;

            rows.forEach(row => {
                refreshRowProjectOptions(row, projects);

                const projectSelect = row.querySelector('.ad-row-project-type');
                const project = projectSelect ? projectSelect.value : '';
                const rowVisible = shouldShow && projects.includes(project);

                row.style.display = rowVisible ? 'block' : 'none';
                setRowInputsDisabled(row, !rowVisible);

                if (rowVisible) {
                    visibleRows++;
                }
            });

            if (empty) {
                empty.style.display = shouldShow && visibleRows === 0 ? 'flex' : 'none';
            }

            if (shouldShow) {
                initializeDynamicSelects(wrapper);
            }

            if (autoAddRow && shouldShow && visibleRows === 0) {
                addProjectAreaRow();
            }
        }

        function initializeDynamicSelects(scope) {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(scope).find('select.select2').each(function () {
                    const $select = window.jQuery(this);

                    if ($select.hasClass('select2-hidden-accessible')) return;

                    $select.select2({
                        width: '100%',
                        dropdownParent: $select.closest('.modal'),
                        placeholder: $select.data('placeholder') || 'Select Option',
                        allowClear: true,
                    });
                });
            }
        }

        function addProjectAreaRow() {
            const projects = selectedProjectTags();
            const container = document.getElementById(`projectRows-${adId}`);

            if (!container || projects.length === 0) return;

            const index = areaRowIndex++;
            const projectOptions = projects
                .map(project => `<option value="${escapeHtml(project)}">${escapeHtml(project)}</option>`)
                .join('');
            const areaOptionsHtml = areaOptions
                .map(name => `<option value="${escapeHtml(name)}">${escapeHtml(name)}</option>`)
                .join('');

            const template = document.createElement('div');
            template.className = 'ad-project-row';
            template.dataset.projectRow = 'true';
            template.innerHTML = `
                <input type="hidden" name="rows[${index}][id]" value="">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Project Type <span class="text-danger">*</span></label>
                        <select class="form-select ad-row-project-type" name="rows[${index}][project_type]" required>
                            <option value="">Select Project</option>
                            ${projectOptions}
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="form-label">Awarded Area <span class="text-danger">*</span></label>
                        <select class="form-select ad-row-area-name" name="rows[${index}][area_name]" required>
                            <option value="">Select Area</option>
                            ${areaOptionsHtml}
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Joining Date</label>
                        <input type="date" class="form-control" name="rows[${index}][joining_date]">
                    </div>
                    <div class="col-md-1 mb-2">
                        <button type="button" class="btn btn-outline-danger ad-remove-project-row" title="Remove area">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            template.querySelector('.ad-row-project-type').value = projects[0];
            container.appendChild(template);
            initializeDynamicSelects(template);
            syncProjectAreaVisibility();
        }

        function isNCR(regionCode, regionName) {
            return String(regionCode || '').startsWith('13') ||
                normalize(regionName).includes('ncr') ||
                normalize(regionName).includes('national capital');
        }

        function syncDeliveryAddress() {
            if (!fields.sameAsDeliveryAddress || !fields.sameAsDeliveryAddress.checked || !fields.deliveryAddress) return;

            fields.deliveryAddress.value = (fields.addressInput && fields.addressInput.value) || (fields.addressPreview && fields.addressPreview.value) || '';
        }

        function updateFullAddress() {
            const street = fields.street && fields.street.value ? fields.street.value.trim() : '';
            const barangay = selectedText(fields.barangay);
            const city = selectedText(fields.city);
            const province = selectedText(fields.province);
            const region = selectedText(fields.region);
            const zipcode = fields.zipcode && fields.zipcode.value ? fields.zipcode.value.trim() : '';

            const parts = [];

            if (street) parts.push(street);
            if (barangay) parts.push(barangay);
            if (city) parts.push(city);

            if (isNCR(selectedCode(fields.region), region)) {
                parts.push('Metro Manila');
            } else if (province) {
                parts.push(province);
            }

            if (zipcode) parts.push(zipcode);
            if (region) parts.push(region);

            const fullAddress = parts.length ? parts.join(', ') : saved.address;

            fields.addressPreview.value = fullAddress;
            fields.addressInput.value = fullAddress;
            syncDeliveryAddress();
        }

        function updateCoordinates(lat, lng, fetchZip = true) {
            currentLat = Number(lat);
            currentLng = Number(lng);

            if (fields.latText) fields.latText.textContent = currentLat.toFixed(6);
            if (fields.lngText) fields.lngText.textContent = currentLng.toFixed(6);
            if (fields.latInput) fields.latInput.value = currentLat;
            if (fields.lngInput) fields.lngInput.value = currentLng;

            if (fetchZip) {
                updateZipCode(currentLat, currentLng);
            }
        }

        function updateZipCode(lat, lng) {
            if (!csrfToken || !lat || !lng) return;

            clearTimeout(zipTimeout);
            zipTimeout = setTimeout(() => {
                fetch("{{ route('get.zipcode') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        fields.zipcode.value = data.zipcode || fields.zipcode.value || saved.zipcode || '';
                        updateFullAddress();
                    })
                    .catch(error => console.error('Zip code lookup error:', error));
            }, 300);
        }

        function initMap() {
            if (!fields.map || !window.L) return;

            if (map) {
                setTimeout(() => map.invalidateSize(), 200);
                return;
            }

            map = L.map(fields.map).setView([currentLat, currentLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            marker = L.marker([currentLat, currentLng], {
                draggable: true,
            }).addTo(map);

            updateCoordinates(currentLat, currentLng, false);

            marker.on('dragend', function () {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            map.on('click', function (event) {
                marker.setLatLng(event.latlng);
                updateCoordinates(event.latlng.lat, event.latlng.lng);
            });

            setTimeout(() => map.invalidateSize(), 200);
        }

        async function loadRegions(selectedValue = '') {
            try {
                const response = await fetch(`${BASE_URL}/regions`);
                const regions = await response.json();

                setOptions(fields.region, '-- Select Region --', regions, selectedValue);
            } catch (error) {
                console.error('Error loading regions:', error);
                resetSelect(fields.region, '-- Error loading --', false);
            }
        }

        async function loadNCRCities(selectedValue = '') {
            const regionCode = selectedCode(fields.region);

            if (!regionCode) {
                resetSelect(fields.city, '-- Select Region First --');
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/regions/${regionCode}/cities-municipalities`);
                const cities = await response.json();

                cities.sort((a, b) => a.name.localeCompare(b.name));
                setOptions(fields.city, '-- Select City --', cities, selectedValue);
            } catch (error) {
                console.error('Error loading NCR cities:', error);
                resetSelect(fields.city, '-- Error loading --', false);
            }
        }

        async function loadProvinces(selectedValue = '') {
            const regionCode = selectedCode(fields.region);
            const regionName = selectedText(fields.region);

            resetSelect(fields.city, '-- Select Province First --');
            resetSelect(fields.barangay, '-- Select City First --');

            if (!regionCode) {
                resetSelect(fields.province, '-- Select Region First --');
                return;
            }

            if (isNCR(regionCode, regionName)) {
                setOptions(fields.province, '-- Select Province --', [
                    { name: 'Metro Manila', code: 'NCR' },
                ], selectedValue || 'Metro Manila');

                await loadNCRCities(saved.city);
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/regions/${regionCode}/provinces`);
                const provinces = await response.json();

                setOptions(fields.province, '-- Select Province --', provinces, selectedValue);
            } catch (error) {
                console.error('Error loading provinces:', error);
                resetSelect(fields.province, '-- Error loading --', false);
            }
        }

        async function loadCities(selectedValue = '') {
            const provinceCode = selectedCode(fields.province);

            resetSelect(fields.barangay, '-- Select City First --');

            if (!provinceCode) {
                resetSelect(fields.city, '-- Select Province First --');
                return;
            }

            if (provinceCode === 'NCR') {
                await loadNCRCities(selectedValue);
                return;
            }

            try {
                const [citiesResponse, municipalitiesResponse] = await Promise.all([
                    fetch(`${BASE_URL}/provinces/${provinceCode}/cities`),
                    fetch(`${BASE_URL}/provinces/${provinceCode}/municipalities`),
                ]);

                const cities = await citiesResponse.json();
                const municipalities = await municipalitiesResponse.json();
                const allCities = [...cities, ...municipalities].sort((a, b) => a.name.localeCompare(b.name));

                setOptions(fields.city, '-- Select City --', allCities, selectedValue);
            } catch (error) {
                console.error('Error loading cities:', error);
                resetSelect(fields.city, '-- Error loading --', false);
            }
        }

        async function loadBarangays(selectedValue = '') {
            const cityCode = selectedCode(fields.city);

            if (!cityCode) {
                resetSelect(fields.barangay, '-- Select City First --');
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/cities-municipalities/${cityCode}/barangays`);
                const barangays = await response.json();

                barangays.sort((a, b) => a.name.localeCompare(b.name));
                setOptions(fields.barangay, '-- Select Barangay --', barangays, selectedValue);
            } catch (error) {
                console.error('Error loading barangays:', error);
                resetSelect(fields.barangay, '-- Error loading --', false);
            }
        }

        async function geocodeSelectedBarangay() {
            const barangay = selectedText(fields.barangay);
            const city = selectedText(fields.city);
            const province = selectedText(fields.province);

            if (!barangay || !city || !province || !csrfToken) return;

            try {
                const response = await fetch("{{ route('geocode.location') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        barangay,
                        city,
                        province,
                    }),
                });

                const data = await response.json();

                if (data.success && map && marker) {
                    const lat = Number(data.lat);
                    const lng = Number(data.lng);

                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                }
            } catch (error) {
                console.error('Geocoding error:', error);
            }
        }

        async function initializeLocationFields() {
            if (initialized) return;

            initialized = true;

            fields.zipcode.value = saved.zipcode || fields.zipcode.value || '';
            fields.addressPreview.value = saved.address || '';
            fields.addressInput.value = saved.address || '';

            if (fields.deliveryAddress && fields.sameAsDeliveryAddress) {
                const deliveryMatchesAddress = normalize(fields.deliveryAddress.value) !== '' &&
                    normalize(fields.deliveryAddress.value) === normalize(saved.address);

                fields.sameAsDeliveryAddress.checked = fields.sameAsDeliveryAddress.checked || deliveryMatchesAddress;
                fields.deliveryAddress.readOnly = fields.sameAsDeliveryAddress.checked;

                if (fields.sameAsDeliveryAddress.checked) {
                    syncDeliveryAddress();
                } else if (!fields.deliveryAddress.value && saved.deliveryAddress) {
                    fields.deliveryAddress.value = saved.deliveryAddress;
                }
            }

            await loadRegions(saved.region);
            await loadProvinces(saved.province);

            if (selectedCode(fields.province) !== 'NCR') {
                await loadCities(saved.city);
            }

            await loadBarangays(saved.barangay);

            updateFullAddress();
        }

        if (fields.region) {
            fields.region.addEventListener('change', async function () {
                fields.zipcode.value = '';
                await loadProvinces();
                updateFullAddress();
            });
        }

        if (fields.province) {
            fields.province.addEventListener('change', async function () {
                fields.zipcode.value = '';
                await loadCities();
                updateFullAddress();
            });
        }

        if (fields.city) {
            fields.city.addEventListener('change', async function () {
                fields.zipcode.value = '';
                await loadBarangays();
                updateFullAddress();
            });
        }

        if (fields.barangay) {
            fields.barangay.addEventListener('change', function () {
                clearTimeout(geocodeTimeout);
                geocodeTimeout = setTimeout(geocodeSelectedBarangay, 300);
                updateFullAddress();
            });
        }

        if (fields.street) {
            fields.street.addEventListener('input', updateFullAddress);
        }

        modal.querySelectorAll('.project-type').forEach(input => {
            input.addEventListener('change', function () {
                syncProjectAreaVisibility(true);
            });
        });

        const addProjectRowButton = modal.querySelector('.ad-add-project-row');

        if (addProjectRowButton) {
            addProjectRowButton.addEventListener('click', addProjectAreaRow);
        }

        modal.addEventListener('change', function (event) {
            if (event.target.classList.contains('ad-row-project-type')) {
                syncProjectAreaVisibility();
            }
        });

        modal.addEventListener('click', function (event) {
            const button = event.target.closest('.ad-remove-project-row');

            if (!button) return;

            const row = button.closest('[data-project-row]');

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(row).find('select.select2').each(function () {
                    const $select = window.jQuery(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                });
            }

            if (row) {
                row.remove();
            }
            syncProjectAreaVisibility();
        });

        if (fields.sameAsDeliveryAddress) {
            fields.sameAsDeliveryAddress.addEventListener('change', function () {
                fields.deliveryAddress.readOnly = this.checked;
                syncDeliveryAddress();

                if (!this.checked) {
                    fields.deliveryAddress.focus();
                }
            });
        }

        modal.addEventListener('shown.bs.modal', async function () {
            await initializeLocationFields();
            initMap();
            syncProjectAreaVisibility(true);
            initializeDynamicSelects(modal);
        });

        const form = modal.querySelector('form');

        if (form) {
            form.addEventListener('submit', function () {
                syncProjectAreaVisibility();
                updateFullAddress();
                syncDeliveryAddress();
            });
        }
    })();
</script>
