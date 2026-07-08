<div id="new_dealer" class="modal fade modal-select2" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h4 class="modal-title" id="myModalLabel">New Dealer</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newDealerForm" method='POST' action='{{url('new-dealer')}}' data-duplicate-url="{{ route('check.dealer.duplicate') }}" onsubmit='show()' enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="latitude" id="hidden_latitude">
        <input type="hidden" name="longitude" id="hidden_longitude">
        <div class="modal-body">
          <div class="row">
            <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-person"></i> Personal Information</div>
            {{-- <div class="col-md-12 mb-2">
              <label class="form-label" for="wfirstName2">Full Name &nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required" id="wfirstName2" name="name" placeholder="Enter Full Name" required/>
            </div> --}}
            <div class="col-md-4 mb-2">
              <label class="form-label" for="first_name">First Name &nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required {{ $errors->has('dealer_duplicate') ? 'is-invalid' : '' }}" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="Enter First Name" data-uppercase required/>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label" for="middle_name">Middle Name</label>
              <input type="text" class="form-control required" id="middle_name" name="middle_name" placeholder="Enter Middle Name" data-uppercase>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label" for="last_name">Last Name &nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required {{ $errors->has('dealer_duplicate') ? 'is-invalid' : '' }}" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Enter Last Name" data-uppercase required/>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="wemailAddress2">Email Address&nbsp;<span class="text-danger">*</span></label>
              <input type="email" class="form-control required" id="wemailAddress2" name="email_address" placeholder="Enter Email Address" required/>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="wphoneNumber2">Mobile Number&nbsp;<span class="text-danger">*</span></label>
              {{-- <input type="number" class="form-control required" id="wphoneNumber2" name="phone_number" placeholder="Enter Phone Number" step="0.01"> --}}
              <input type="text" class="form-control" id="number" name="number" placeholder="09xxxxxxxxx" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="facebook2">Facebook&nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required" id="facebook2" name='facebook' placeholder="Enter Facebook" data-uppercase required/>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label" for="birthdate">Birthdate&nbsp;<span class="text-danger">*</span></label>
              <input type="date" class="form-control required" id="birthdate" name='birthdate' placeholder="Enter Birthdate" required/>
            </div>
            <div class="col-md-2 mb-3">
              <label class="form-label" for="age">Age</label>
              <input type="number" class="form-control" id="age" name="age" placeholder="Age" readonly>
            </div>
            <div class="col-md-12 mb-2">
              <label class="form-label" for="name">Mother's Full Name&nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required {{ $errors->has('dealer_duplicate') ? 'is-invalid' : '' }}" id="mothers_name" name="mothers_name" value="{{ old('mothers_name') }}" placeholder="Enter Mother's Name" data-uppercase required/>
              <div class="invalid-feedback {{ $errors->has('dealer_duplicate') ? 'd-block' : '' }}" id="duplicateDealerFeedback">
                  {{ $errors->first('dealer_duplicate') ?: "Dealer with same First Name, Last Name, and Mother's Name already exists." }}
              </div>
            </div>
            <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-building-fill"></i> Business Information</div>
            <input type="hidden" name="dealer_type" value="Regular">
            <div class="col-md-6 mb-2">
              <label class="form-label" for="store_name">Store Name &nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required" name='store_name' id="store_name" placeholder="Enter Store Name" data-uppercase>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="store_type">Store Type &nbsp;<span class="text-danger">*</span></label>
              {{-- <input type="text" class="form-control required" name='store_type' id="store_type" placeholder="Enter Store Type" /> --}}
              <select name="store_type" class="form-control select2" data-placeholder="Select Business Type" data-select2-theme="bootstrap-5" required>
                <option value="">Select Store Type</option>
                <option value="Sari Sari Store">Sari Sari Store</option>
                <option value="Mini Mart">Mini Mart</option>
                <option value="Retail Shop">Retail Shop</option>
                <option value="Wholesale">Wholesale</option>
                <option value="Grocery">Grocery</option>
              </select>
            </div>
            <div class="col-md-6 mb-2" id="dealerSpoWrap">
              <label class="form-label" for="spo">SPO&nbsp;<span class="text-danger">*</span></label>
              <input type="text" class="form-control required" id="spo" name="spo" value="{{ old('spo') }}" placeholder="Enter SPO" data-uppercase required/>
            </div>
            <div class="col-md-6 mb-2" id="dealerCenterWrap">
              <label class="form-label" for="center">Center&nbsp;<span class="text-danger">*</span></label>
              <select class="form-control select2" id="center" name="center" required data-placeholder="Select Center" data-select2-theme="bootstrap-5">
                <option value="">Select Center</option>
                @foreach($centers ?? [] as $center)
                    <option value="{{ $center->name }}" {{ old('center') === $center->name ? 'selected' : '' }}>{{ $center->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label" for="center">Sales Territory&nbsp;<span class="text-danger">*</span></label>
              {{-- <select class="form-control select2" id="area" name="area" required data-placeholder="Select Area">
                <option value="">Select Area</option>
                @foreach($areas ?? [] as $area)
                  <option value="{{ $area->name }}">{{ $area->name }}</option>
                @endforeach
              </select> --}}

              <select class="form-select select2 select2-area" id="area" name="area" required data-placeholder="Select Area" data-select2-theme="bootstrap-5">
                <option value="">Select Area</option>
                @foreach($areas ?? [] as $area)
                  <option value="{{ $area->name }}"
                      data-user="{{ $area->areaAd->distributor->name ?? 'No User' }}">
                      {{ $area->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-geo-alt"></i> Location Details</div>
            <div class="col-md-6 mb-2">
              <label>Street Name, Building, House No. <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="street_address" id="street_address" value="{{ old('street_address') }}" placeholder="e.g., 1868 Kapalaran St" data-uppercase required>
            </div>
            <div class="col-md-6 mb-2">
              <label>Region <span class="text-danger">*</span></label>
              <select class="form-control select2" id="location_region" name="location_region" required data-placeholder="Select Region" data-select2-theme="bootstrap-5" onclick="event.stopPropagation();">
                <option value="">-- Select Region --</option>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label>Province <span class="text-danger">*</span></label>
              <select class="form-control select2" id="location_province" name="location_province" required data-placeholder="Select Province" data-select2-theme="bootstrap-5" onclick="event.stopPropagation();" disabled>
                <option value="">-- Select Region First --</option>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label>City/Municipality <span class="text-danger">*</span></label>
              <select class="form-control select2" id="location_city" name="location_city" required data-placeholder="Select City/Municipality" data-select2-theme="bootstrap-5" onclick="event.stopPropagation();" disabled>
                <option value="">-- Select Province First --</option>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label>Barangay <span class="text-danger">*</span></label>
              <select class="form-control select2" name="location_barangay" id="location_barangay" required data-placeholder="Select Barangay" data-select2-theme="bootstrap-5" onclick="event.stopPropagation();" disabled>
                <option value="">-- Select City First --</option>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label>Postal Code</label>
              <input type="text" class="form-control" name="postal_code" id="postal_code" placeholder="e.g., 1868">
            </div>
            <div class="col-md-12 mb-2 mt-3">
              <label class="form-label fw-semibold">
                <i class="bi bi-map"></i> Pin Location (Drag the marker)
              </label>

              <div class="position-relative">
                <div id="map" style="
                    height: 300px;
                    border-radius: 10px;
                    overflow: hidden;
                    border: 1px solid #ddd;
                "></div>
                <div id="map-coords" style="
                    position:absolute;
                    top: 12px;
                    right: 12px;
                    background:#000;
                    color:#fff;
                    padding:4px 8px;
                    font-size:10px;
                    z-index: 1000;
                    border-radius:6px;
                    opacity:0.8;
                    pointer-events:none;
                ">
                    Lat: --, Lng: --
                </div>
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

<!-- jQuery FIRST -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Then Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

    const $dealerForm = $('#newDealerForm');
    const $dealerType = $('#new_dealer input[name="dealer_type"]');
    const $dealerSpoWrap = $('#dealerSpoWrap');
    const $dealerCenterWrap = $('#dealerCenterWrap');
    const $spo = $('#new_dealer #spo');
    const $center = $('#new_dealer #center');
    const $firstName = $('#new_dealer #first_name');
    const $lastName = $('#new_dealer #last_name');
    const $mothersName = $('#new_dealer #mothers_name');
    const $duplicateDealerFeedback = $('#duplicateDealerFeedback');
    const $submitButton = $dealerForm.find('button[type="submit"]');
    const duplicateDealerMessage = "Dealer with same First Name, Last Name, and Mother's Name already exists.";

    let duplicateDealerTimeout = null;
    let duplicateDealerRequestId = 0;

    function updateDealerTypeFields() {
      const selectedDealerType = $dealerType.filter(':checked').val() || $dealerType.val() || 'Regular';
      const isRegular = String(selectedDealerType).toLowerCase() === 'regular';

      $dealerSpoWrap.toggleClass('d-none', isRegular);
      $dealerCenterWrap.toggleClass('d-none', isRegular);
      $spo.prop('disabled', isRegular).prop('required', !isRegular);
      $center.prop('disabled', isRegular).prop('required', !isRegular);

      if (isRegular) {
        $spo.val('');
        $center.val('').trigger('change');
      }
    }

    $dealerType.on('change', updateDealerTypeFields);
    updateDealerTypeFields();

    function setDuplicateDealerValidation(isDuplicate, message = duplicateDealerMessage) {
      $firstName.add($lastName).add($mothersName).toggleClass('is-invalid', isDuplicate);

      if (isDuplicate) {
        $duplicateDealerFeedback.text(message).addClass('d-block').show();
        $submitButton.prop('disabled', true).attr('title', message);
        return;
      }

      $duplicateDealerFeedback.removeClass('d-block').hide();
      $submitButton.prop('disabled', false).removeAttr('title');
    }

    function checkDuplicateDealer() {
      const first_name = $firstName.val().trim();
      const last_name = $lastName.val().trim();
      const mothers_name = $mothersName.val().trim();

      if (!first_name || !last_name || !mothers_name) {
        setDuplicateDealerValidation(false);
        return;
      }

      const requestId = ++duplicateDealerRequestId;

      fetch($dealerForm.data('duplicate-url'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': $dealerForm.find('input[name="_token"]').val(),
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
        if (requestId !== duplicateDealerRequestId) return;

        setDuplicateDealerValidation(data.exists === true, data.message || duplicateDealerMessage);
      })
      .catch(error => {
        console.error('Dealer duplicate check error:', error);
        setDuplicateDealerValidation(false);
      });
    }

    $firstName.add($lastName).add($mothersName).on('input change', function() {
      clearTimeout(duplicateDealerTimeout);
      duplicateDealerTimeout = setTimeout(checkDuplicateDealer, 400);
    });

    if ($firstName.val() && $lastName.val() && $mothersName.val()) {
      checkDuplicateDealer();
    }

    function refreshNewDealerSelect2(selector) {
      const $select = $(selector);

      if ($select.hasClass('select2-hidden-accessible')) {
        $select.trigger('change.select2');
      }
    }

    function selectedLocationText(selector) {
      const text = $(`${selector} option:selected`).text();

      if (!text || text.includes('Select') || text.includes('Loading') || text === 'Not applicable') {
        return '';
      }

      return text.trim();
    }

    function optionsFromPsgc(data, placeholder) {
      let options = `<option value="">${placeholder}</option>`;

      (data || []).forEach(function(item) {
        options += `<option value="${item.name}">${item.name}</option>`;
      });

      return options;
    }

    function loadRegionCities(regionID) {
      resetMappedAddressCache();
      $('#location_city').prop('disabled', true).html('<option>Loading...</option>');
      refreshNewDealerSelect2('#location_city');

      return $.get('/api/regions/' + encodeURIComponent(regionID) + '/cities-municipalities')
        .done(function(data) {
          $('#location_city')
            .html(optionsFromPsgc(data, '-- Select City/Municipality --'))
            .prop('disabled', false);
          refreshNewDealerSelect2('#location_city');
          generateFullAddress();
        })
        .fail(function() {
          alert('Failed to load cities/municipalities');
        });
    }

    @if($errors->has('dealer_duplicate'))
      setTimeout(function() {
        const modal = document.getElementById('new_dealer');

        if (modal && window.bootstrap && bootstrap.Modal) {
          bootstrap.Modal.getOrCreateInstance(modal).show();
        }
      }, 300);
    @endif

    // LOAD REGIONS
    $.get('/api/regions')
      .done(function(data) {
          $('#location_region').html(optionsFromPsgc(data, '-- Select Region --'));
          refreshNewDealerSelect2('#location_region');

          generateFullAddress(); // ✅ add this
      })
      .fail(function() {
        alert('Failed to load regions');
      });

    // REGION CHANGE
    $('#location_region').on('change', function() {
      resetMappedAddressCache();
      let regionID = $(this).val();

      $('#location_province').prop('disabled', true).html('<option>Loading...</option>');
      $('#location_city').prop('disabled', true).html('<option>-- Select Province or Region First --</option>');
      $('#location_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');
      refreshNewDealerSelect2('#location_province');
      refreshNewDealerSelect2('#location_city');
      refreshNewDealerSelect2('#location_barangay');

      if (!regionID) return;

      $.get('/api/regions/' + encodeURIComponent(regionID) + '/provinces')
          .done(function(data) {
              if (!data || data.length === 0) {
                  $('#location_province')
                      .html('<option value="">Not applicable</option>')
                      .prop('disabled', true);
                  refreshNewDealerSelect2('#location_province');
                  loadRegionCities(regionID);
                  return;
              }

              $('#location_province')
                  .html(optionsFromPsgc(data, '-- Select Province --'))
                  .prop('disabled', false);
              refreshNewDealerSelect2('#location_province');

              generateFullAddress(); // ✅ add this
          })
          .fail(function() {
              $('#location_province')
                  .html('<option value="">Not applicable</option>')
                  .prop('disabled', true);
              refreshNewDealerSelect2('#location_province');
              loadRegionCities(regionID);
          });
    });

    // PROVINCE CHANGE
    $('#location_province').on('change', function() {
        resetMappedAddressCache();
        let provinceID = $(this).val();

        $('#location_city').prop('disabled', true).html('<option>Loading...</option>');
        $('#location_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');
        refreshNewDealerSelect2('#location_city');
        refreshNewDealerSelect2('#location_barangay');

        if (!provinceID) return;

        $.get('/api/provinces/' + encodeURIComponent(provinceID) + '/cities')
          .done(function(data) {
              $('#location_city')
                  .html(optionsFromPsgc(data, '-- Select City/Municipality --'))
                  .prop('disabled', false);
              refreshNewDealerSelect2('#location_city');

              generateFullAddress(); // ✅ add this
          })
          .fail(function() {
              alert('Failed to load cities');
          });
    });

    // City change
    $('#location_city').change(function() {
        resetMappedAddressCache();
        let cityID = $(this).val();
        $('#location_barangay').prop('disabled', true).html('<option>Loading...</option>');
        $('#postal_code').val('');
        updatePostalCodeFromLocation('city');
        refreshNewDealerSelect2('#location_barangay');

        if(cityID) {
            $.get(`/api/cities/${encodeURIComponent(cityID)}/barangays`, function(data){
                let options = '<option value="">-- Select Barangay --</option>';
                data.forEach(function(item){
                    options += `<option value="${item.name}" data-postal="${item.zip_code || ''}">${item.name}</option>`;
                });
                $('#location_barangay').html(options).prop('disabled', false);
                refreshNewDealerSelect2('#location_barangay');
                generateFullAddress();
            });
        }
    });

    $('#location_barangay').on('change', async function () {

      let selected = $(this).find(':selected');
      let postal = selected.data('postal') || '';

      // ✅ 1. Use dataset zip if exists
      if (postal) {
          $('#postal_code').val(postal);
          resetMappedAddressCache();
          generateFullAddress();
          return;
      }

      // ✅ 2. Build full address
      resetMappedAddressCache();
      updatePostalCodeFromLocation('barangay');
      return;

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

      resetMappedAddressCache();
      generateFullAddress();
    });

    let postalTimeout = null;

    function fallbackZipByCity() {
      const city = selectedLocationText('#location_city') || $('#location_city').val();
      const normalizedCity = (city || '')
        .toLowerCase()
        .replace(/^city of\s+/, '')
        .replace(/\s+city$/, '')
        .trim();

      const zipMap = {
          'bacacay': '4509',
          'caloocan': '1400',
          'camalig': '4502',
          'daraga': '4501',
          'guinobatan': '4503',
          'jovellar': '4515',
          'las pinas': '1740',
          'legazpi': '4500',
          'libon': '4507',
          'ligao': '4504',
          'makati': '1200',
          'malabon': '1470',
          'malilipot': '4510',
          'malinao': '4512',
          'mandaluyong': '1550',
          'manila': '1000',
          'manito': '4514',
          'muntinlupa': '1770',
          'navotas': '1485',
          'oas': '4505',
          'paranaque': '1700',
          'pasay': '1300',
          'pateros': '1620',
          'pio duran': '4516',
          'polangui': '4506',
          'quezon': '1100',
          'rapu-rapu': '4517',
          'san juan': '1500',
          'santo domingo': '4508',
          'tabaco': '4511',
          'taguig': '1630',
          'tiwi': '4513',
          'valenzuela': '1440',
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
        const barangay = selectedLocationText('#location_barangay');
        const city = selectedLocationText('#location_city');
        const province = selectedLocationText('#location_province');
        const region = selectedLocationText('#location_region');

        if (!city) {
          $('#postal_code').val('');
          generateFullAddress();
          return;
        }

        const lookupAttempts = [
          source === 'barangay' && barangay
            ? [barangay, city, province, region, 'Philippines']
            : null,
          [city, province, region, 'Philippines']
        ].filter(Boolean);

        for (let i = 0; i < lookupAttempts.length; i++) {
          try {
            const geo = await $.get('https://nominatim.openstreetmap.org/search', {
                q: lookupAttempts[i].filter(Boolean).join(', '),
                format: 'json',
                limit: 1
            });

            if (!geo.length) {
              continue;
            }

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
          } catch (error) {
            console.error('Postal code lookup error:', error);
          }
        }

        $('#postal_code').val(fallbackZipByCity());
        generateFullAddress();
      }, 300);
    }

    function fallbackZip() {
      $('#postal_code').val(fallbackZipByCity());
    }

    let geoTimeout = null;

    function hasEnoughAddressForMap() {
      return Boolean(
        $('#street_address').val().trim() &&
        $('#location_region').val() &&
        $('#location_city').val() &&
        $('#location_barangay').val()
      );
    }

    function resetMappedAddressCache() {
      if (typeof lastMappedAddress !== 'undefined') {
        lastMappedAddress = '';
      }
    }

    function scheduleLiveMapUpdate() {
      resetMappedAddressCache();
      generateFullAddress();
    }

    function triggerMapUpdate() {
      clearTimeout(geoTimeout);

      if (!$('#new_dealer').hasClass('show') || !hasEnoughAddressForMap()) {
        return;
      }

      geoTimeout = setTimeout(() => {
          geocodeAddressToMap();
      }, 600); // wait 600ms after last change
    }

    function generateFullAddress() {

      const clean = val => {
        if (!val) return '';

        val = val.trim();

        return val.includes('Select') || val.includes('Loading') || val === 'Not applicable'
          ? ''
          : val;
      };

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

      $('#complete_address').val(fullAddress);

      // 🔥 AUTO UPDATE MAP HERE
      triggerMapUpdate();
    }

    $('#location_region, #location_province, #location_city, #location_barangay')
    .on('change select2:select select2:clear', scheduleLiveMapUpdate);

    $('#street_address, #postal_code')
    .on('input keyup change', scheduleLiveMapUpdate);

    // // TRIGGER ON CHANGE
    // $('#location_region, #location_province, #location_city, #location_barangay')
    //   .on('change', generateFullAddress);

    // // TRIGGER ON INPUT
    // $('#postal_code, #street_address')
    //   .on('keyup change', generateFullAddress);
  });

  function formatArea(state) {
    if (!state.id) return state.text;

    const user = $(state.element).data('user') || 'No User';

    return `
        <div>
            <strong>${state.text}</strong><br>
            <small style="color:#888;">${user}</small>
        </div>
    `;
  }

  function ensureDealerSelect2Ready(callback) {
    if ($.fn.select2) {
      callback();
      return;
    }

    if (window.dealerSelect2Loading) {
      setTimeout(function () {
        ensureDealerSelect2Ready(callback);
      }, 50);
      return;
    }

    window.dealerSelect2Loading = true;

    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
    script.onload = function () {
      window.dealerSelect2Loading = false;
      callback();
    };
    document.head.appendChild(script);
  }

  function initNewDealerSelect2(parent) {
    ensureDealerSelect2Ready(function () {
      const $parent = $(parent);
      const $selects = $parent.is('select.select2')
        ? $parent
        : $parent.find('select.select2');

      $selects.each(function () {
        const $select = $(this);
        const isSalesTerritory = $select.is('select.select2-area[name="area"]');

        if ($select.hasClass('select2-hidden-accessible')) {
          return;
        }

        $select.select2({
          width: '100%',
          dropdownParent: $('#new_dealer'),
          placeholder: $select.data('placeholder') || 'Select Option',
          allowClear: true,
          theme: $select.data('select2-theme') || 'bootstrap-5',
          templateResult: isSalesTerritory ? formatArea : undefined,
          templateSelection: isSalesTerritory ? formatArea : undefined,
          escapeMarkup: markup => markup
        });
      });
    });
  }

  $(document).on('shown.bs.modal', '#new_dealer', function () {
    if (typeof window.initSelect2 === 'function') {
      window.initSelect2(this);
    }

    initNewDealerSelect2(this);
  });

  $(document).ready(function () {
    initNewDealerSelect2('#new_dealer');
  });

  /*
   * Select2 CDN used by these fields if the global layout script has not loaded yet:
   * https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js
   */
</script>

<script>
  let map, marker;
  let geocodeRequestId = 0;
  let lastMappedAddress = '';
  const defaultLat = 14.5995;
  const defaultLng = 120.9842;

  function initMap() {

    const mapElement = document.getElementById('map');

    if (!mapElement || typeof L === 'undefined') {
      return;
    }

    if (!$('#new_dealer').hasClass('show')) {
      return;
    }

    if (map) {
      setTimeout(function () {
        map.invalidateSize();
      }, 150);
      return;
    }

    map = L.map(mapElement, {
      scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    // Set initial hidden values
    updateLatLng(defaultLat, defaultLng);

    marker.on('drag', function () {
      const position = marker.getLatLng();
      updateLatLng(position.lat, position.lng);
    });

    // Drag event
    marker.on('dragend', function () {
      const position = marker.getLatLng();
      updateLatLng(position.lat, position.lng);
      reverseGeocode(position.lat, position.lng);
    });

    // Click map to move pin
    map.on('click', function (e) {
      marker.setLatLng(e.latlng);
      updateLatLng(e.latlng.lat, e.latlng.lng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    setTimeout(function () {
      map.invalidateSize();
    }, 250);
  }

  // Update hidden inputs
  function updateLatLng(lat, lng) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);

    if (Number.isNaN(lat) || Number.isNaN(lng)) {
      return;
    }

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

  // async function geocodeAddressToMap() {
  //   const address = $('#complete_address').val();
  //   if (!address) return;

  //   // UI Loading effect
  //   $('#map').css('opacity', '0.6');

  //   try {
  //     const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`);
  //     const data = await res.json();

  //     if (data.length > 0) {
  //         const lat = parseFloat(data[0].lat);
  //         const lon = parseFloat(data[0].lon);

  //         // Smooth move
  //         map.flyTo([lat, lon], 16, {
  //             animate: true,
  //             duration: 1.2
  //         });

  //         marker.setLatLng([lat, lon]);

  //         updateLatLng(lat, lon);
  //     } else {
  //         console.warn("No location found for:", address);
  //     }

  //   } catch (err) {
  //       console.error('Geocode error:', err);
  //   }

  //   $('#map').css('opacity', '1');
  // }
  async function geocodeAddressToMapLegacy() {
  const address = ($('#complete_address').val() || '').trim();
  if (!address || address === lastMappedAddress || !$('#new_dealer').hasClass('show')) return;

  // ✅ SAFETY CHECK (FIXES YOUR ERROR)
  if (!map) {
      initMap();
  }

  if (!map || !marker || typeof map.flyTo !== 'function') {
      console.warn('Map not initialized yet');
      return;
  }

  const requestId = ++geocodeRequestId;
  $('#map').css('opacity', '0.6');

  try {
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&countrycodes=ph`);
    const data = await res.json();

    if (requestId !== geocodeRequestId) {
        return;
    }

    if (data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);

        if (!Number.isNaN(lat) && !Number.isNaN(lon)) {
            map.flyTo([lat, lon], 16, {
                animate: true,
                duration: 0.8
            });

            marker.setLatLng([lat, lon]);
            updateLatLng(lat, lon);
            lastMappedAddress = address;
        }
    }

    } catch (err) {
        console.error('Geocode error:', err);
    }

    if (requestId === geocodeRequestId) {
        $('#map').css('opacity', '1');
    }
  }

  function selectedLocationText(selector) {
    const value = $(selector).val();
    const text = $(selector + ' option:selected').text();

    if (!value || !text || text.includes('Select') || text.includes('Loading') || text === 'Not applicable') {
      return '';
    }

    return text.trim();
  }

  function dealerMapSearchQueries() {
    const street = ($('#street_address').val() || '').trim();
    const barangay = selectedLocationText('#location_barangay');
    const city = selectedLocationText('#location_city');
    const province = selectedLocationText('#location_province');
    const region = selectedLocationText('#location_region');
    const postal = ($('#postal_code').val() || '').trim();
    const completeAddress = ($('#complete_address').val() || '').trim();
    const queryParts = [
      [completeAddress],
      [street, barangay, city, province, region, postal, 'Philippines'],
      [barangay, city, province, region, 'Philippines'],
      [city, province, region, 'Philippines'],
      [city, region, 'Philippines']
    ];
    const seen = {};

    return queryParts
      .map(parts => parts.filter(Boolean).join(', '))
      .filter(function (query) {
        if (!query || seen[query]) {
          return false;
        }

        seen[query] = true;
        return true;
      });
  }

  async function geocodeAddressToMap() {
    const address = ($('#complete_address').val() || '').trim();

    if (!address || address === lastMappedAddress || !$('#new_dealer').hasClass('show')) {
      return;
    }

    if (!map) {
      initMap();
    }

    if (!map || !marker || typeof map.flyTo !== 'function') {
      console.warn('Map not initialized yet');
      return;
    }

    const requestId = ++geocodeRequestId;
    $('#map').css('opacity', '0.6');

    try {
      const queries = dealerMapSearchQueries();

      for (let i = 0; i < queries.length; i++) {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(queries[i])}&limit=1&countrycodes=ph`);
        const data = await res.json();

        if (requestId !== geocodeRequestId) {
          return;
        }

        if (!data.length) {
          continue;
        }

        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);

        if (Number.isNaN(lat) || Number.isNaN(lon)) {
          continue;
        }

        map.flyTo([lat, lon], 16, {
          animate: true,
          duration: 0.8
        });

        marker.setLatLng([lat, lon]);
        updateLatLng(lat, lon);
        lastMappedAddress = address;
        return;
      }

      console.warn('No map location found for:', address);
    } catch (err) {
      console.error('Geocode error:', err);
    } finally {
      if (requestId === geocodeRequestId) {
        $('#map').css('opacity', '1');
      }
    }
  }

  $('#new_dealer')
    .off('shown.bs.modal.pinLocation')
    .on('shown.bs.modal.pinLocation', function () {
      initMap();

      setTimeout(function () {
        if (!map) {
          return;
        }

        map.invalidateSize();

        const lat = parseFloat($('#hidden_latitude').val()) || defaultLat;
        const lng = parseFloat($('#hidden_longitude').val()) || defaultLng;

        map.setView([lat, lng], map.getZoom() || 13);

        if (marker) {
          marker.setLatLng([lat, lng]);
        }

        updateLatLng(lat, lng);
      }, 250);

      setTimeout(function () {
        if (!map) {
          return;
        }

        map.invalidateSize();

        if ($('#street_address').val().trim() && $('#location_region').val() && $('#location_city').val() && $('#location_barangay').val()) {
          geocodeAddressToMap();
        }
      }, 550);
    });
</script>
