<div id="new_area_distributor" class="modal fade modal-select2" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myModalLabel">
                    New {{ $distributorSingular ?? 'Area Distributor' }}
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($storeRoute ?? 'new-ad') }}" onsubmit="show()" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="hidden_latitude">
                <input type="hidden" name="longitude" id="hidden_longitude">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Role Type&nbsp;<span class="text-danger">*</span></label>
                            <select id="roleFilter" class="form-control select2" required disabled>
                                <option value="{{ $distributorSingular ?? 'Area Distributor' }}" selected>{{ $distributorSingular ?? 'Area Distributor' }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Partner Code&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="store_code" name="store_code" placeholder="Enter Store Code" required>
                        </div>
                        <!-- Avatar Upload -->
                        <div class="col-md-4 text-center">
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
                            </small>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="name">Full Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="name" name="name" placeholder="Enter Full Name" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="email_address"> Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control required" id="email_address" name="email_address" placeholder="Enter Email Address" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="contact_number">Contact Number&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Enter Contact Number" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required >
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="facebook">Facebook<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="facebook" name='facebook' placeholder="Enter Facebook" required/>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Street Name, Building, House No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="street_address" id="street_address" value="{{ old('street_address') }}" placeholder="e.g., 1868 Kapalaran St" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Region <span class="text-danger">*</span></label>
                            <select class="form-control" id="location_region" name="location_region" required onclick="event.stopPropagation();">
                                <option value="">-- Select Region --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-control" id="location_province" name="location_province" required onclick="event.stopPropagation();" disabled>
                                <option value="">-- Select Region First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                            <select class="form-control" id="location_city" name="location_city" required onclick="event.stopPropagation();" disabled>
                                <option value="">-- Select Province First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Barangay <span class="text-danger">*</span></label>
                            <select class="form-control" 
                                    name="location_barangay" 
                                    id="location_barangay" 
                                    required 
                                    onclick="event.stopPropagation();" 
                                    disabled>
                                <option value="">-- Select City First --</option>
                            </select>
                            <small class="form-text text-muted">Select barangay from the list</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="business_name">Business Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="business_name" name="business_name" placeholder="Enter Business Name" required/>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="business_type">Business Type<span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="business_type" name="business_type" placeholder="Enter Business Type" required/>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Service Vehicle <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 border rounded-3 px-3 py-2 bg-light">
                                <div class="form-check mb-0"><input class="form-check-input" type="radio" name="has_service_vehicle" id="new_ad_service_vehicle_yes" value="1" required><label class="form-check-label" for="new_ad_service_vehicle_yes"><i class="bi bi-truck me-1"></i>Yes</label></div>
                                <div class="form-check mb-0"><input class="form-check-input" type="radio" name="has_service_vehicle" id="new_ad_service_vehicle_no" value="0" required><label class="form-check-label" for="new_ad_service_vehicle_no">No</label></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="new_ad_business_years_operation">Years in Business Operation <span class="text-danger">*</span></label>
                            <div class="input-group"><input type="number" class="form-control" id="new_ad_business_years_operation" name="business_years_operation" min="0" max="200" step="1" placeholder="e.g., 5" required><span class="input-group-text">year(s)</span></div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="new_ad_contract_attachments">Contract Attachments</label>
                            <input type="file" class="form-control" id="new_ad_contract_attachments" name="contract_attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Select up to 10 PDF, Word, JPG, or PNG files. Maximum 10MB each.</small>
                        </div>
                        {{-- <div class="col-md-6 mb-2">
                            <label class="form-label">Area<span class="text-danger">*</span></label>
                            <select class="form-control" id="area" name="area" required>
                                <option value="">-- Select Area --</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}">{{ $center->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Area <span class="text-danger">*</span></label>
                            <select class="form-control area_name" 
                                    id="area_name" 
                                    name="area_name[]" 
                                    multiple 
                                    required>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}">{{ $center->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <div id="location_map" style="height: 400px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <strong>Current Pin Location:</strong><br>
                                    Latitude: <span id="display_lat">--</span>, Longitude: <span id="display_lng">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Complete Address Preview</label>
                                <textarea class="form-control bg-light" id="full_address_preview" rows="2" readonly></textarea>
                                <input type="hidden" name="address" id="location_hidden">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect"data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-info-subtle text-info  waves-effect">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #location_map {
        height: 400px;
        width: 100%;
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
        
        let map, marker;
        let currentLat = 14.6507, currentLng = 121.0494;
        let currentRegionName = '';
        let currentRegionCode = '';
        let currentProvinceName = '';
        let currentCityName = '';
        let geocodeCache = {};
        let geocodeTimeout = null;

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

        function updateCoordinates(lat, lng) {
            currentLat = lat;
            currentLng = lng;
            document.getElementById('display_lat').textContent = lat.toFixed(6);
            document.getElementById('display_lng').textContent = lng.toFixed(6);
            document.getElementById('hidden_latitude').value = lat.toFixed(6);
            document.getElementById('hidden_longitude').value = lng.toFixed(6);
            updateFullAddress();
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

        function updateFullAddress() {
            const street = document.getElementById('street_address').value.trim();
            const barangay = getSelectedText('location_barangay');
            const city = getSelectedText('location_city');
            const province = getSelectedText('location_province');
            const region = getSelectedText('location_region');

            let parts = [];

            if (street) parts.push(street);
            if (barangay) parts.push(barangay);
            if (city) parts.push(city);

            // ✅ NCR fix (safe check)
            const isNCR =
                region &&
                (region.toLowerCase().includes('ncr') ||
                region.toLowerCase().includes('national capital'));

            if (isNCR) {
                parts.push('Metro Manila');
            } else if (province) {
                parts.push(province);
            }

            if (region) parts.push(region);

            const fullAddress = parts.join(', ');

            document.getElementById('full_address_preview').value = fullAddress;
            document.getElementById('location_hidden').value = fullAddress;
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
                            <strong>Locating barangay...</strong>
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

        async function geocodeAddress(barangay, city, province, region) {
            const cacheKey = `${barangay}|${city}|${province}`;
            
            if (geocodeCache[cacheKey]) {
                const cached = geocodeCache[cacheKey];
                map.setView([cached.lat, cached.lng], 16);
                marker.setLatLng([cached.lat, cached.lng]);
                updateCoordinates(cached.lat, cached.lng);
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
                        barangay: barangay,
                        city: city,
                        province: province
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
                } else {
                    console.log('Barangay not found, using city coordinates');
                    updateMapForCity(city);
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                updateMapForCity(city);
            } finally {
                hideMapLoading();
            }
        }

        async function loadRegions() {
            try {
                const response = await fetch(`${BASE_URL}/regions`);
                const regions = await response.json();
                
                const regionSelect = document.getElementById('location_region');
                regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                
                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.code;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
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
        const regionCode = this.value;
        currentRegionCode = regionCode;
        currentRegionName = this.options[this.selectedIndex]?.text || '';

        const provinceSelect = document.getElementById('location_province');
        const citySelect = document.getElementById('location_city');
        const barangaySelect = document.getElementById('location_barangay');

        citySelect.innerHTML = '<option value="">-- Select City First --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (regionCode) {

            if (isNCR(regionCode, currentRegionName)) {

                provinceSelect.innerHTML = '<option value="NCR" selected>Metro Manila</option>';
                provinceSelect.disabled = true;
                currentProvinceName = 'Metro Manila';

                updateFullAddress();

                await loadNCRCities(regionCode);

            } else {

                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                provinceSelect.disabled = false;

                try {
                    provinceSelect.innerHTML = '<option value="">Loading...</option>';
                    const response = await fetch(`${BASE_URL}/regions/${regionCode}/provinces`);
                    const provinces = await response.json();

                    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';

                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });

                } catch (error) {
                    console.error('Error loading provinces:', error);
                    provinceSelect.innerHTML = '<option value="">-- Error loading --</option>';
                }
            }

        } else {
            provinceSelect.innerHTML = '<option value="">-- Select Region First --</option>';
            provinceSelect.disabled = true;
        }

        updateFullAddress();
    });

    async function loadNCRCities(regionCode) {
        const citySelect = document.getElementById('location_city');
        
        try {
            citySelect.innerHTML = '<option value="">Loading...</option>';
            
            const response = await fetch(`${BASE_URL}/regions/${regionCode}/cities-municipalities`);
            const cities = await response.json();
            
            cities.sort((a, b) => a.name.localeCompare(b.name));
            
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            citySelect.disabled = false;
            
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.name;
                citySelect.appendChild(option);
            });
            
        } catch (error) {
            console.error('Error loading NCR cities:', error);
            citySelect.innerHTML = '<option value="">-- Error loading --</option>';
            alert('Failed to load cities.');
        }
    }

    document.getElementById('location_province').addEventListener('change', async function() {
        const provinceCode = this.value;
        currentProvinceName = this.options[this.selectedIndex]?.text || '';
        
        const citySelect = document.getElementById('location_city');
        const barangaySelect = document.getElementById('location_barangay');

        citySelect.innerHTML = '<option value="">-- Select City --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select City First --</option>';
        barangaySelect.disabled = true;

        if (provinceCode && provinceCode !== 'NCR') {
            try {
                citySelect.innerHTML = '<option value="">Loading...</option>';
                
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
                    option.value = city.code;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading cities:', error);
                citySelect.innerHTML = '<option value="">-- Error loading --</option>';
                alert('Failed to load cities. Please try again.');
            }
        } else {
            citySelect.disabled = true;
        }
        updateFullAddress();
    });

    document.getElementById('location_city').addEventListener('change', async function() {
        const cityCode = this.value;
        currentCityName = this.options[this.selectedIndex]?.text || '';
        
        const barangaySelect = document.getElementById('location_barangay');

        barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';

        if (cityCode) {
            try {
                barangaySelect.innerHTML = '<option value="">Loading...</option>';
                barangaySelect.disabled = false;
                
                const response = await fetch(`${BASE_URL}/cities-municipalities/${cityCode}/barangays`);
                const barangays = await response.json();
                
                barangays.sort((a, b) => a.name.localeCompare(b.name));
                
                barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
                
                updateMapForCity(currentCityName);
                
            } catch (error) {
                console.error('Error loading barangays:', error);
                barangaySelect.innerHTML = '<option value="">-- Error loading --</option>';
                alert('Failed to load barangays. Please try again.');
            }
        } else {
            barangaySelect.disabled = true;
        }
        updateFullAddress();
    });

    document.getElementById('location_barangay').addEventListener('change', function() {
        const barangayName = this.options[this.selectedIndex]?.text || '';
        
        if (barangayName && barangayName !== '-- Select Barangay --') {
            if (geocodeTimeout) {
                clearTimeout(geocodeTimeout);
            }
            
            geocodeTimeout = setTimeout(() => {
                geocodeAddress(barangayName, currentCityName, currentProvinceName, currentRegionName);
                if (currentLat && currentLng) {
                //   fetchZipCode(currentLat, currentLng);
                }
            }, 300);
        }
        
        updateFullAddress();
    });

    document.getElementById('street_address').addEventListener('input', updateFullAddress);

    function updateMapForCity(city) {
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

    $('#new_area_distributor').on('shown.bs.modal', function () {
        loadRegions();

        setTimeout(() => {
            if (!map) {
                initMap();
            } else {
                map.invalidateSize();
            }
        }, 300); // delay is important
    });

});
</script>
