<style>
    #editDealerModal .edit-dealer-type-picker {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    #editDealerModal .edit-dealer-type-option {
        position: relative;
        margin: 0;
        cursor: pointer;
    }

    #editDealerModal .edit-dealer-type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    #editDealerModal .edit-dealer-type-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 88px;
        padding: 15px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        transition: .18s ease;
    }

    #editDealerModal .edit-dealer-type-option:hover .edit-dealer-type-card {
        border-color: #93c5fd;
        transform: translateY(-1px);
    }

    #editDealerModal .edit-dealer-type-option input:checked + .edit-dealer-type-card {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .12);
    }

    #editDealerModal .edit-dealer-type-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        border-radius: 10px;
        color: #1d4ed8;
        background: #dbeafe;
        font-size: 21px;
    }

    #editDealerModal .is-regular .edit-dealer-type-icon {
        color: #047857;
        background: #d1fae5;
    }

    #editDealerModal .edit-dealer-type-content {
        min-width: 0;
        flex: 1;
    }

    #editDealerModal .edit-dealer-type-title {
        display: block;
        color: #111827;
        font-size: 14px;
        font-weight: 800;
    }

    #editDealerModal .edit-dealer-type-copy {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
    }

    #editDealerModal .edit-dealer-type-check {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 22px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        color: transparent;
        background: #fff;
    }

    #editDealerModal .edit-dealer-type-option input:checked + .edit-dealer-type-card .edit-dealer-type-check {
        color: #fff;
        border-color: #2563eb;
        background: #2563eb;
    }

    @media (max-width: 575px) {
        #editDealerModal .edit-dealer-type-picker {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="modal fade modal-select2" id="editDealerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Dealer Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDealerForm" method="POST" action="{{ route('dealer.update', $dealer->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="hidden_latitude" value="{{ $dealer->latitude }}">
                <input type="hidden" name="longitude" id="hidden_longitude" value="{{ $dealer->longitude }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-person"></i> Personal Information</div>
                        {{-- <div class="col-md-12 mb-2">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Full Name" value="{{ old('name', $dealer->name) }}" required>
                        </div> --}}
                        <div class="col-md-4 mb-2">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Enter First Name" value="{{ old('first_name', $dealer->user->first_name) }}" data-uppercase required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Enter Middle Name" value="{{ old('middle_name', $dealer->user->middle_name) }}" data-uppercase required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name" value="{{ old('last_name', $dealer->user->last_name) }}" data-uppercase required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Email Address</label>
                            <input type="email" name="email_address" class="form-control" placeholder="Enter Email Address" value="{{ old('email_address', $dealer->email_address) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Contact Number</label>
                            {{-- <input type="text" name="number" class="form-control" value="{{ $dealer->number }}"> --}}
                            <input type="text" class="form-control" id="number" name="number" placeholder="Enter Contact Number" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="{{ old('number', $dealer->number) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Facebook</label>
                            <input type="text" name="facebook" placeholder="Enter Facebook" class="form-control" value="{{ old('facebook', $dealer->facebook) }}" data-uppercase>
                        </div>
                        <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-building-fill"></i> Business Information</div>
                        @php
                            $currentDealerType = old('dealer_type', $dealer->dealer_type ?: 'Project');
                        @endphp
                        @if(auth()->user()->role === 'Admin')
                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Dealer Type <span class="text-danger">*</span></label>
                                <div class="edit-dealer-type-picker">
                                    <label class="edit-dealer-type-option">
                                        <input type="radio" name="dealer_type" value="Project"
                                            {{ $currentDealerType === 'Project' ? 'checked' : '' }} required>
                                        <span class="edit-dealer-type-card">
                                            <span class="edit-dealer-type-icon"><i class="bi bi-building-gear"></i></span>
                                            <span class="edit-dealer-type-content">
                                                <span class="edit-dealer-type-title">Project Dealer</span>
                                                <span class="edit-dealer-type-copy">Requires an SPO and Center assignment.</span>
                                            </span>
                                            <span class="edit-dealer-type-check"><i class="bi bi-check"></i></span>
                                        </span>
                                    </label>
                                    <label class="edit-dealer-type-option is-regular">
                                        <input type="radio" name="dealer_type" value="Regular"
                                            {{ $currentDealerType === 'Regular' ? 'checked' : '' }} required>
                                        <span class="edit-dealer-type-card">
                                            <span class="edit-dealer-type-icon"><i class="bi bi-shop"></i></span>
                                            <span class="edit-dealer-type-content">
                                                <span class="edit-dealer-type-title">Regular Dealer</span>
                                                <span class="edit-dealer-type-copy">Does not require an SPO or Center.</span>
                                            </span>
                                            <span class="edit-dealer-type-check"><i class="bi bi-check"></i></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="dealer_type" value="{{ $currentDealerType }}">
                        @endif
                        <div class="col-md-6 mb-2">
                            <label>Store Name</label>
                            <input type="text" name="store_name" class="form-control" placeholder="Enter Store Name" value="{{ old('store_name', $dealer->store_name) }}" data-uppercase>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Store Type</label>
                            <input type="text" name="store_type" class="form-control" placeholder="Enter Store Type" value="{{ old('store_type', $dealer->store_type) }}" data-uppercase>
                        </div>
                        <div class="col-md-6 mb-2" id="editDealerSpoWrap">
                            <label>SPO</label>
                            <input type="text" id="editDealerSpo" name="spo" class="form-control" placeholder="Enter SPO" value="{{ old('spo', $dealer->spo) }}" data-uppercase>
                        </div>
                        <div class="col-md-6 mb-2" id="editDealerCenterWrap">
                            <label class="form-label" for="center">Center</label>
                            <select class="form-control select2" id="editDealerCenter" name="center" required>
                                <option value="">Select Center</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}"
                                        {{ old('center', $dealer->center) == $center->name ? 'selected' : '' }}>
                                        {{ $center->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="center">Sales Territory</label>
                            {{-- <select class="form-control select2" id="area" name="area" required>
                                <option value="">Select Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->name }}"
                                        {{ (isset($dealer) && $dealer->area == $area->name) ? 'selected' : '' }}>
                                        {{ $area->name }} <br>👤{{ $area->areaAd->distributor->name ?? 'No User' }}
                                    </option>
                                @endforeach
                            </select> --}}
                            <select class="form-select select2 select2-area" id="area" name="area" required data-placeholder="Select Area" data-select2-theme="bootstrap-5">
                                <option></option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->name }}"
                                        data-user="{{ $area->areaAd->distributor->name ?? 'No User' }}"
                                        {{ (isset($dealer) && $dealer->area == $area->name) ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-geo-alt"></i> Location Details</div>
                        <div class="col-md-6 mb-2">
                            <label>Street Name, Building, House No.</label>
                            <input type="text" class="form-control" name="street_address" id="street_address" value="{{ old('street_address', $dealer->street_address) }}" data-uppercase placeholder="e.g., 1868 Kapalaran St">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Region</label>
                            <select class="form-control select2" id="location_region" name="location_region">
                                <option value="">-- Select Region --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Province</label>
                            <select class="form-control select2" id="location_province" name="location_province" disabled>
                                <option value="">-- Select Region First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>City/Municipality</label>
                            <select class="form-control select2" id="location_city" name="location_city" disabled>
                                <option value="">-- Select Province First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Barangay</label>
                            <select class="form-control select2" name="location_barangay" id="location_barangay" disabled>
                                <option value="">-- Select City First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Postal Code</label>
                            <input type="text" class="form-control" name="postal_code" id="postal_code" value="{{ old('postal_code', $dealer->postal_code) }}" placeholder="e.g., 1868">
                        </div>
                        <input type="hidden" id="original_address" value="{{ $dealer->address }}">
                        <div class="col-md-12 mb-2">
                            <label>Existing Address</label>
                            <input type="text" class="form-control" value="{{ $dealer->address }}" readonly>
                        </div>
                        <div class="col-md-12 mt-3 mb-2" style="position: relative;">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-map"></i> Pin Location (Drag the marker)
                            </label>

                            <div id="map" style="
                                height: 300px;
                                border-radius: 10px;
                                overflow: hidden;
                                border: 1px solid #ddd;
                            "></div>
                            <div id="map-coords" style="
                                position:absolute;
                                top: 40px;
                                right: 20px;
                                background:#000;
                                color:#fff;
                                padding:4px 8px;
                                font-size:10px;
                                z-index: 999;
                                border-radius:6px;
                                opacity:0.8;
                            ">
                                Lat: --, Lng: --
                            </div>
                            <div class="text-muted small mt-2">
                                📍 Drag or click the map to set exact location
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label>Complete Address Preview</label>
                            {{-- <input type="text" name="address" class="form-control" value="{{ $dealer->address }}" readonly> --}}
                            <input type="text" id="complete_address" name="address" class="form-control" value="{{ $dealer->address }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    $(document).ready(function() {
        const $editDealerType = $('#editDealerModal input[name="dealer_type"]');
        const $editDealerSpoWrap = $('#editDealerSpoWrap');
        const $editDealerCenterWrap = $('#editDealerCenterWrap');
        const $editDealerSpo = $('#editDealerSpo');
        const $editDealerCenter = $('#editDealerCenter');

        function updateEditDealerTypeFields() {
            const selectedType = $editDealerType.filter(':checked').val() || $editDealerType.val() || 'Project';
            const isRegular = String(selectedType).toLowerCase() === 'regular';

            $editDealerSpoWrap.toggleClass('d-none', isRegular);
            $editDealerCenterWrap.toggleClass('d-none', isRegular);
            $editDealerSpo.prop('disabled', isRegular).prop('required', !isRegular);
            $editDealerCenter.prop('disabled', isRegular).prop('required', !isRegular);

            if (isRegular) {
                $editDealerSpo.val('');
                $editDealerCenter.val('').trigger('change');
            }
        }

        $editDealerType.on('change', updateEditDealerTypeFields);
        updateEditDealerTypeFields();

        // LOAD REGIONS
        // $.get('/api/regions')
        // .done(function(data) {
        //     let options = '<option value="">-- Select Region --</option>';
        //     data.forEach(function(item) {
        //         options += `<option value="${item.name}">${item.name}</option>`;
        //     });
        //     $('#location_region').html(options);

        //     generateFullAddress(); // ✅ add this
        // })
        // .fail(function() {
        //     alert('Failed to load regions');
        // });
        // ✅ ALWAYS set initial address FIRST
        const originalAddress = $('#original_address').val();
        $('#complete_address').val(originalAddress);
        const savedLocation = {
            region: @json(old('location_region', $dealer->location_region)),
            province: @json(old('location_province', $dealer->location_province)),
            city: @json(old('location_city', $dealer->location_city)),
            barangay: @json(old('location_barangay', $dealer->location_barangay)),
            postalCode: @json(old('postal_code', $dealer->postal_code))
        };

        function normalizeLocation(value) {
            return String(value || '')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/^(city|municipality)\s+of\s+/i, '')
                .toLowerCase();
        }

        function setSavedValue($select, value) {
            if (!value) return false;

            const saved = normalizeLocation(value);
            let matchedValue = null;

            $select.find('option').each(function() {
                const optionValue = normalizeLocation(this.value);
                const optionText = normalizeLocation($(this).text());

                if (optionValue === saved || optionText === saved) {
                    matchedValue = this.value;
                    return false;
                }
            });

            if (matchedValue) {
                $select.val(matchedValue);
            } else {
                $select.append(new Option(value, value, true, true));
            }

            $select.trigger('change.select2');

            return true;
        }

        function isNcrRegion(regionName) {
            regionName = String(regionName || '').toLowerCase();

            return regionName.includes('ncr') || regionName.includes('national capital');
        }

        // LOAD REGIONS
        $.get('/api/regions')
        .done(function(data) {

            let options = '<option value="">-- Select Region --</option>';
            data.forEach(function(item) {
                options += `<option value="${item.name}">${item.name}</option>`;
            });

            $('#location_region').html(options);

            // ✅ AFTER regions load → allow editing logic
            if (setSavedValue($('#location_region'), savedLocation.region)) {
                $('#location_region').trigger('change');
            } else {
                isInitialLoad = false;
            }

        })
        .fail(function() {
            alert('Failed to load regions');
        });
            
        // REGION CHANGE
        $('#location_region').on('change', function() {
            let regionID = $(this).val();

            $('#location_province').prop('disabled', true).html('<option>Loading...</option>');
            $('#location_city').prop('disabled', true).html('<option>-- Select Province First --</option>');
            $('#location_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');

            if (!regionID) {
                isInitialLoad = false;
                return;
            }

            if (isNcrRegion(regionID)) {
                $('#location_province')
                    .html('<option value="Metro Manila" selected>Metro Manila</option>')
                    .prop('disabled', false);

                $.get('/api/regions/' + encodeURIComponent(regionID) + '/cities-municipalities')
                    .done(function(data) {
                        let options = '<option value="">-- Select City/Municipality --</option>';
                        data.forEach(function(item) {
                            const selected = isInitialLoad && normalizeLocation(item.name) === normalizeLocation(savedLocation.city) ? 'selected' : '';
                            options += `<option value="${item.name}" ${selected}>${item.name}</option>`;
                        });

                        $('#location_city').html(options).prop('disabled', false);

                        if (isInitialLoad && savedLocation.city) {
                            setSavedValue($('#location_city'), savedLocation.city);
                            $('#location_city').trigger('change');
                        } else {
                            isInitialLoad = false;
                            generateFullAddress();
                        }
                    })
                    .fail(function() {
                        isInitialLoad = false;
                        alert('Failed to load cities');
                    });

                return;
            }

            $.get('/api/regions/' + encodeURIComponent(regionID) + '/provinces')
                .done(function(data) {
                    let options = '<option value="">-- Select Province --</option>';
                    data.forEach(function(item) {
                        const selected = isInitialLoad && normalizeLocation(item.name) === normalizeLocation(savedLocation.province) ? 'selected' : '';
                        options += `<option value="${item.name}" ${selected}>${item.name}</option>`;
                    });
                    $('#location_province').html(options).prop('disabled', false);

                    if (isInitialLoad && savedLocation.province) {
                        setSavedValue($('#location_province'), savedLocation.province);
                        $('#location_province').trigger('change');
                        return;
                    }

                    isInitialLoad = false;

                    generateFullAddress(); // ✅ add this
                })
                .fail(function() {
                    isInitialLoad = false;
                    alert('Failed to load provinces');
                });
        });

        // PROVINCE CHANGE
        $('#location_province').on('change', function() {
            let provinceID = $(this).val();

            $('#location_city').prop('disabled', true).html('<option>Loading...</option>');
            $('#location_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');

            if (!provinceID || provinceID === 'Metro Manila') return;

            $.get('/api/provinces/' + encodeURIComponent(provinceID) + '/cities')
            .done(function(data) {
                let options = '<option value="">-- Select City/Municipality --</option>';
                data.forEach(function(item) {
                    const selected = isInitialLoad && normalizeLocation(item.name) === normalizeLocation(savedLocation.city) ? 'selected' : '';
                    options += `<option value="${item.name}" ${selected}>${item.name}</option>`;
                });
                $('#location_city').html(options).prop('disabled', false);

                if (isInitialLoad && savedLocation.city) {
                    setSavedValue($('#location_city'), savedLocation.city);
                    $('#location_city').trigger('change');
                    return;
                }

                isInitialLoad = false;

                generateFullAddress(); // ✅ add this
            })
            .fail(function() {
                alert('Failed to load cities');
            });
        });

        // City change
        $('#location_city').change(function() {
            let cityID = $(this).val();
            $('#location_barangay').prop('disabled', true).html('<option>Loading...</option>');

            if (!isInitialLoad) {
                $('#postal_code').val('');
            }

            if(cityID) {
                $.get(`/api/cities/${cityID}/barangays`, function(data){
                    let options = '<option value="">-- Select Barangay --</option>';
                    data.forEach(function(item){
                        const selected = isInitialLoad && normalizeLocation(item.name) === normalizeLocation(savedLocation.barangay) ? 'selected' : '';
                        options += `<option value="${item.name}" data-postal="${item.zip_code || ''}" ${selected}>${item.name}</option>`;
                    });
                    $('#location_barangay').html(options).prop('disabled', false);

                    if (isInitialLoad && savedLocation.barangay) {
                        setSavedValue($('#location_barangay'), savedLocation.barangay);
                        $('#location_barangay').trigger('change.select2');
                    }

                    if (isInitialLoad && savedLocation.postalCode) {
                        $('#postal_code').val(savedLocation.postalCode);
                    }

                    isInitialLoad = false;
                    generateFullAddress();
                });
            } else {
                isInitialLoad = false;
            }
        });

        $('#location_barangay').on('change', async function () {

            let selected = $(this).find(':selected');
            let postal = selected.data('postal') || '';

            // ✅ 1. Use dataset zip if exists
            if (postal) {
                $('#postal_code').val(postal);
                generateFullAddress();
                return;
            }

            // ✅ 2. Build full address
            let fullTextAddress = [
                $('#street_address').val(),
                selected.text(),
                $('#location_city option:selected').text(),
                $('#location_province option:selected').text(),
                'Philippines'
            ].filter(Boolean).join(', ');

            try {
                // ✅ 3. Forward geocode (get coordinates)
                let geo = await $.get('https://nominatim.openstreetmap.org/search', {
                    q: fullTextAddress,
                    format: 'json',
                    limit: 1
                });

                if (!geo.length) {
                    fallbackZip();
                    return;
                }

                let lat = geo[0].lat;
                let lon = geo[0].lon;

                // ✅ Save coords (important for backend use later)
                $('#hidden_latitude').val(lat);
                $('#hidden_longitude').val(lon);

                // ✅ 4. Reverse lookup via Laravel
                let zipRes = await $.get('/get-zipcode1', {
                    latitude: lat,
                    longitude: lon
                });

                let zip = zipRes.zipcode;

                if (!zip) {
                    fallbackZip();
                } else {
                    $('#postal_code').val(zip);
                }

            } catch (e) {
                console.error(e);
                fallbackZip();
            }

            generateFullAddress();
            geocodeAddressToMap(); // 🔥 sync map
        });

        $('#location_region, #location_province, #location_city, #location_barangay')
        .on('change', function () {

            if (!isInitialLoad) {
                isUserEditingAddress = true;
            }

            generateFullAddress();
        });

        $('#street_address, #postal_code')
        .on('keyup change', function () {

            isUserEditingAddress = true;
            generateFullAddress();
        });
        // // TRIGGER ON CHANGE
        // $('#location_region, #location_province, #location_city, #location_barangay')
        //   .on('change', generateFullAddress);

        // // TRIGGER ON INPUT
        // $('#postal_code, #street_address')
        //   .on('keyup change', generateFullAddress);
    });  
</script>
<script>
    let map, marker;
    let isUserEditingAddress = false;
    let isInitialLoad = true;

    function fallbackZip() {
        let city = $('#location_city').val();

        // Minimal example mapping (you can expand)
        const zipMap = {
            'Taytay': '1920',
            'Cainta': '1900',
            'Antipolo': '1870',
            'Pasig': '1600',
            'Marikina': '1800',
            'Bacarra': '2916'
        };

        $('#postal_code').val(zipMap[city] || '');
    }

    let geoTimeout;

    function triggerMapUpdate() {
        clearTimeout(geoTimeout);

        geoTimeout = setTimeout(() => {
            geocodeAddressToMap();
        }, 700);
    }

    // function generateFullAddress() {

    //     const clean = val => (!val || val.includes('Select')) ? '' : val.trim();

    //     let parts = [
    //         clean($('#street_address').val()),
    //         clean($('#location_barangay option:selected').text()),
    //         clean($('#location_city option:selected').text()),
    //         clean($('#location_province option:selected').text()),
    //         clean($('#location_region option:selected').text()),
    //         clean($('#postal_code').val()),
    //         'Philippines'
    //     ].filter(Boolean);

    //     let fullAddress = parts.join(', ');

    //     $('#complete_address').val(fullAddress);

    //     // 🔥 AUTO UPDATE MAP HERE
    //     triggerMapUpdate();
    // }

    function generateFullAddress() {

        const original = $('#original_address').val();

        const clean = val => (!val || val.includes('Select')) ? '' : val.trim();

        let parts = [
            clean($('#street_address').val()),
            clean($('#location_barangay option:selected').text()),
            clean($('#location_city option:selected').text()),
            clean($('#location_province option:selected').text()),
            clean($('#location_region option:selected').text()),
            clean($('#postal_code').val()),
            'Philippines'
        ].filter(Boolean);

        let fullAddress = parts.join(', ');

        // ✅ KEY LOGIC (DO NOT TOUCH ORIGINAL UNLESS USER EDITS)
        if (!isUserEditingAddress) {
            $('#complete_address').val(original);
            return;
        }

        // ✅ If user editing but incomplete → don't override yet
        if (fullAddress.length < 10) {
            return;
        }

        // ✅ Apply new address
        $('#complete_address').val(fullAddress);

        triggerMapUpdate();
    }

    function formatArea(option) {
        if (!option.id) return option.text;

        let user = $(option.element).data('user') || 'No User';

        return $(`
            <div style="display:flex; flex-direction:column;">
                <span style="font-weight:600;">${option.text}</span>
                <small style="color:#f94040;">
                    👤 ${user}
                </small>
            </div>
        `);
    }

    $(document).on('shown.bs.modal', '#editDealerModal', function () {
        if (typeof window.initSelect2 === 'function') {
            window.initSelect2(this);
            return;
        }

        if ($.fn.select2) {
            $(this).find('select.select2-area').select2({
                width: '100%',
                dropdownParent: $(this),
                placeholder: 'Select Area',
                allowClear: true,
                theme: 'bootstrap-5',
                templateResult: formatArea,
                templateSelection: formatArea,
                escapeMarkup: markup => markup
            });
        }
    });

    function initMap() {

        let lat = parseFloat("{{ $dealer->latitude ?? 14.5995 }}");
        let lng = parseFloat("{{ $dealer->longitude ?? 120.9842 }}");

        map = L.map('map').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        updateLatLng(lat, lng);

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            updateLatLng(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateLatLng(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        // 🧠 FIX: map rendering issue inside modal
        setTimeout(() => {
            map.invalidateSize();
        }, 400);
    }

    // Update hidden inputs
    function updateLatLng(lat, lng) {
        $('#hidden_latitude').val(lat);
        $('#hidden_longitude').val(lng);

        $('#map-coords').text(`Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`);
    }

    // Reverse geocode → fill address
    async function reverseGeocode(lat, lng) {
        try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);

        const data = await res.json();

        if (data && data.display_name) {
            $('#complete_address').val(data.display_name);
        }

        } catch (err) {
            console.error('Reverse geocode error:', err);
        }
    }

    async function geocodeAddressToMap() {
        const address = $('#complete_address').val();
        if (!address) return;

        $('#map').css('opacity', '0.5');

        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`);
            const data = await res.json();

            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);

                map.flyTo([lat, lng], 16, {
                    animate: true,
                    duration: 1.2
                });

                marker.setLatLng([lat, lng]);
                updateLatLng(lat, lng);
            }

        } catch (err) {
            console.error(err);
        }

        $('#map').css('opacity', '1');
    }
</script>
