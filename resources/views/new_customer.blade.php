{{-- <div id="new_customer" class="modal fade" tabindex="-1" aria-labelledby="bs-example-modal-md" aria-hidden="true">
  <div class="modal-dialog modal-lg"> --}}
<div id="new_customer" class="modal fade modal-select2 {{ !empty($isDistributorCustomerPage) ? 'distributor-customer-modal' : '' }}" tabindex="-1" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h4 class="modal-title" id="myModalLabel">New Customer</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method='POST' action='{{url('new-customer')}}' enctype="multipart/form-data" class="validation-wizard wizard-circle">
        @csrf
        <input type="hidden" name="latitude" id="hidden_latitude">
        <input type="hidden" name="longitude" id="hidden_longitude">
        <div class="modal-body">
          <input style="display:none" value="Active" name="status" id="status">
          <div class="row">
            <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-person"></i> Personal Information</div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label" for="stoveId">Serial Number</label>
                <select id="stoveId" class="w-100 form-control {{ !empty($isDistributorCustomerPage) ? 'select2' : 'js-example-basic-single renz chosen-select' }}" name='serial_number'>
                  <option value="">Search Serial Number</option>
                  @foreach($stoves as $stove)
                    <option value="{{$stove->id}}" {{ old('serial_number') == $stove->id ? 'selected' : '' }}>{{$stove->serial_number}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            {{-- <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label" for="wfirstName2">Full Name&nbsp;<span class="text-danger">*</span></label>
                <input type="text" class="form-control required" id="wfirstName2" name="name" placeholder="Enter Full Name" required/>
              </div>
            </div> --}}
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label" for="first_name">First Name&nbsp;<span class="text-danger">*</span></label>
                <input type="text" class="form-control required" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="Enter First Name" data-uppercase required/>
              </div>
            </div>
             <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label" for="middle_name">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Enter Middle Name" data-uppercase>
              </div>
            </div>
             <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label" for="last_name">Last Name&nbsp;<span class="text-danger">*</span></label>
                <input type="text" class="form-control required" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Enter Last Name" data-uppercase required/>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label" for="wemailAddress2">Email Address&nbsp;<span class="text-danger">*</span></label>
                <input type="email" class="form-control required" id="wemailAddress2" name="email_address" value="{{ old('email_address') }}" placeholder="Enter Email Address" required/>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label" for="wphoneNumber2">Mobile Number&nbsp;<span class="text-danger">*</span></label>
                <input type="text" class="form-control required" id="wphoneNumber2" maxlength="11" pattern="09[0-9]{9}" name="phone_number" value="{{ old('phone_number') }}" placeholder="09xxxxxxxxx" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label" for="facebook2">Facebook&nbsp;<span class="text-danger">*</span></label>
                <input type="tel" class="form-control required" id="facebook2" name='facebook' placeholder="Enter Facebook" data-uppercase required/>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label" for="birthdate">Birthdate&nbsp;<span class="text-danger">*</span></label>
                <input type="date" class="form-control required" id="birthdate" name="birthdate" placeholder="Enter Birthdate" required/>
              </div>
            </div>
            <div class="col-md-2">
              <div class="mb-3">
                <label class="form-label" for="age">Age</label>
                <input type="number" class="form-control" id="age" name="age" placeholder="Age" readonly>
              </div>
            </div>
            <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-geo-alt"></i> Location Details</div>
            @if(!empty($isDistributorCustomerPage))
            <div class="col-md-12 mb-3">
              <label class="form-label" for="newCustomerArea">Awarded area <span class="text-danger">*</span></label>
              <select id="newCustomerArea" name="area" class="form-control select2" required data-placeholder="Select awarded area">
                <option value="">Select awarded area</option>
                @foreach($customerAreaNames as $areaName)
                  <option value="{{ $areaName }}" {{ old('area') === $areaName ? 'selected' : '' }}>{{ $areaName }}</option>
                @endforeach
              </select>
              <small class="text-muted">This customer will belong to the selected area.</small>
            </div>
            @endif
            <div class="col-md-6">
              <div class="mb-3">
                <label>Street Name, Building, House No. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="street_address" id="street_address" value="{{ old('street_address') }}" placeholder="e.g., 1868 Kapalaran St" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label>Region <span class="text-danger">*</span></label>
                <select class="form-control select2" id="location_region" name="location_region" required onclick="event.stopPropagation();">
                  <option value="">-- Select Region --</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label>Province <span class="text-danger">*</span></label>
                <select class="form-control select2" id="location_province" name="location_province" required onclick="event.stopPropagation();" disabled>
                  <option value="">-- Select Region First --</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label>City/Municipality <span class="text-danger">*</span></label>
                <select class="form-control select2" id="location_city" name="location_city" required onclick="event.stopPropagation();" disabled>
                  <option value="">-- Select Province First --</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label>Barangay <span class="text-danger">*</span></label>
                <select class="form-control select2" name="location_barangay" id="location_barangay" required onclick="event.stopPropagation();" disabled>
                  <option value="">-- Select City First --</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label>Postal Code</span></label>
                <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="e.g., 1868">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">SPO</label>
                <input type="text" class="form-control required" name="spo" placeholder="Enter SPO" data-uppercase>
              </div>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="center">Center @if(empty($isDistributorCustomerPage))<span class="text-danger">*</span>@else <small class="text-muted">(optional)</small>@endif</label>
              <select class="form-control select2" id="center" name="center" {{ empty($isDistributorCustomerPage) ? 'required' : '' }} data-placeholder="Select Center">
                <option value="">Select Center</option>
                @foreach($centers ?? [] as $center)
                  <option value="{{ $center->name }}">{{ $center->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-12 mb-2 mt-3 position-relative">
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
            <div class="mt-2 small text-muted">
              Drag the pin to your exact store location
            </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="wlocation2">Complete Address Preview</label>
            <textarea class="form-control required" id="complete_address" name="address" placeholder="Auto-generated address" readonly>
            </textarea>
          </div>
          {{-- <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label" for="wlocation2"> Address <span class="text-danger">*</span>
                </label>
                <textarea class="form-control required" name='address' required></textarea>
              </div>
            </div>
          </div> --}}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn bg-danger-subtle text-danger  waves-effect"
            data-bs-dismiss="modal">
            Close
          </button>
          <button type="submit" class="btn bg-info-subtle text-info  waves-effect">
            Submit
          </button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
<!-- /.modal-dialog -->
</div>
@push('page-scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Then your custom script -->
<script>
$(document).ready(function() {
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

    const $birthdate = $('#birthdate');
    const $age = $('#age');

    $birthdate.attr('max', new Date().toISOString().split('T')[0]);
    $birthdate.on('change input', function() {
        $age.val(calculateAge(this.value));
    });

    // LOAD REGIONS
    $.get('/api/regions')
        .done(function(data) {
            let options = '<option value="">-- Select Region --</option>';
            data.forEach(function(item) {
                options += `<option value="${item.name}">${item.name}</option>`;
            });
            $('#location_region').html(options);
            generateFullAddress();
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

        if (!regionID) return;

        $.get('/api/regions/' + regionID + '/provinces')
            .done(function(data) {
                let options = '<option value="">-- Select Province --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                $('#location_province').html(options).prop('disabled', false);
                generateFullAddress();
            })
            .fail(function() {
                alert('Failed to load provinces');
            });
    });


    // PROVINCE CHANGE
    $('#location_province').on('change', function() {
        let provinceID = $(this).val();

        $('#location_city').prop('disabled', true).html('<option>Loading...</option>');
        $('#location_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');

        if (!provinceID) return;

        $.get('/api/provinces/' + provinceID + '/cities')
            .done(function(data) {
                let options = '<option value="">-- Select City/Municipality --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                $('#location_city').html(options).prop('disabled', false);
                generateFullAddress();
            })
            .fail(function() {
                alert('Failed to load cities');
            });
    });


    // City change
    $('#location_city').change(function() {
        let cityID = $(this).val();
        $('#location_barangay').prop('disabled', true).html('<option>Loading...</option>');
        $('#postal_code').val('');
        updatePostalCodeFromLocation('city');

        if(cityID) {
            $.get(`/api/cities/${cityID}/barangays`, function(data){
                let options = '<option value="">-- Select Barangay --</option>';
                data.forEach(function(item){
                    options += `<option value="${item.name}" data-postal="${item.zip_code || ''}">${item.name}</option>`;
                });
                $('#location_barangay').html(options).prop('disabled', false);
                generateFullAddress();
            });
        }
    });

    // Barangay change → populate postal code
    $('#location_barangay').change(function() {
        let postal = $(this).find(':selected').data('postal') || '';
        if (postal) {
            $('#postal_code').val(postal);
            generateFullAddress();
            return;
        }

        updatePostalCodeFromLocation('barangay');
    });

    let geoTimeout = null;
    let postalTimeout = null;

    function selectedText(selector) {
        const text = $(`${selector} option:selected`).text();
        return (!text || text.includes('Select') || text.includes('Loading')) ? '' : text.trim();
    }

    function fallbackZipByCity() {
        const city = selectedText('#location_city') || $('#location_city').val();
        const normalizedCity = (city || '').toLowerCase();

        const zipMap = {
            'antipolo': '1870',
            'bacarra': '2916',
            'cainta': '1900',
            'marikina': '1800',
            'pasig': '1600',
            'taytay': '1920'
        };

        return zipMap[normalizedCity] || '';
    }

    function updatePostalCodeFromLocation(source) {
        clearTimeout(postalTimeout);

        postalTimeout = setTimeout(async function() {
            const barangay = selectedText('#location_barangay');
            const city = selectedText('#location_city');
            const province = selectedText('#location_province');
            const region = selectedText('#location_region');

            if (!city) {
                $('#postal_code').val('');
                generateFullAddress();
                return;
            }

            const addressParts = source === 'barangay' && barangay
                ? [barangay, city, province, region, 'Philippines']
                : [city, province, region, 'Philippines'];

            try {
                const geo = await $.get('https://nominatim.openstreetmap.org/search', {
                    q: addressParts.filter(Boolean).join(', '),
                    format: 'json',
                    limit: 1
                });

                if (geo.length) {
                    const lat = geo[0].lat;
                    const lon = geo[0].lon;

                    $('#hidden_latitude').val(lat);
                    $('#hidden_longitude').val(lon);

                    const zipRes = await $.get('/get-zipcode1', {
                        latitude: lat,
                        longitude: lon
                    });

                    if (zipRes.zipcode) {
                        $('#postal_code').val(zipRes.zipcode);
                        generateFullAddress();
                        return;
                    }
                }
            } catch (error) {
                console.error('Postal code lookup error:', error);
            }

            $('#postal_code').val(fallbackZipByCity());
            generateFullAddress();
        }, 300);
    }

    function triggerMapUpdate() {
        clearTimeout(geoTimeout);
        geoTimeout = setTimeout(function() {
            geocodeAddressToMap();
        }, 600);
    }

    function generateFullAddress() {
        const clean = val => (!val || val.includes('Select') || val.includes('Loading')) ? '' : val.trim();

        let parts = [
            clean($('#street_address').val()),
            clean($('#location_barangay option:selected').text()),
            clean($('#location_city option:selected').text()),
            clean($('#location_province option:selected').text()),
            clean($('#location_region option:selected').text()),
            clean($('#postal_code').val()),
            'Philippines'
        ].filter(Boolean);

        $('#complete_address').val(parts.join(', '));
        triggerMapUpdate();
    }

    $('#location_region, #location_province, #location_city, #location_barangay')
        .on('change', generateFullAddress);

    $('#street_address, #postal_code')
        .on('keyup change', generateFullAddress);

});
</script>

<script>
  let map, marker;

  function initMap() {
    const defaultLat = 14.5995;
    const defaultLng = 120.9842;

    map = L.map('map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    updateLatLng(defaultLat, defaultLng);

    marker.on('dragend', function () {
        const position = marker.getLatLng();
        updateLatLng(position.lat, position.lng);
        reverseGeocode(position.lat, position.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateLatLng(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
  }

  function updateLatLng(lat, lng) {
    $('#hidden_latitude').val(lat);
    $('#hidden_longitude').val(lng);
    $('#map-coords').text(`Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`);
  }

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
    if (!address || typeof map === 'undefined' || !map || typeof map.flyTo !== 'function') {
        return;
    }

    $('#map').css('opacity', '0.6');

    try {
      const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`);
      const data = await res.json();

      if (data.length > 0) {
          const lat = parseFloat(data[0].lat);
          const lon = parseFloat(data[0].lon);

          map.flyTo([lat, lon], 16, {
              animate: true,
              duration: 1.2
          });

          marker.setLatLng([lat, lon]);
          updateLatLng(lat, lon);
      }
    } catch (err) {
        console.error('Geocode error:', err);
    }

    $('#map').css('opacity', '1');
  }
</script>
@endpush
