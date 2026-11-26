<div class="modal fade modal-select2 {{ !empty($isDistributorCustomerPage) ? 'distributor-customer-modal' : '' }}" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('customer.update', $customer->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="edit_customer_latitude" value="{{ $customer->latitude }}">
                <input type="hidden" name="longitude" id="edit_customer_longitude" value="{{ $customer->longitude }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="fs-6 fw-bold col-md-12 mb-3"><i class="bi bi-person"></i> Personal Information</div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Serial Number</label>
                            <select class="form-control select2" name="serial_number">
                                <option value="">Select Serial Number</option>
                                @foreach($stoves as $stove)
                                    <option value="{{ $stove->id }}" {{ (int) $customer->serial_number === (int) $stove->id ? 'selected' : '' }}>
                                        {{ $stove->serial_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $customer->user->first_name ?? '') }}" data-uppercase required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $customer->user->middle_name ?? '') }}" data-uppercase>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $customer->user->last_name ?? '') }}" data-uppercase required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Email Address</label>
                            <input type="email" name="email_address" class="form-control" value="{{ old('email_address', $customer->email_address) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Contact Number</label>
                            <input type="text" name="number" class="form-control" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" value="{{ old('number', $customer->number) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Facebook</label>
                            <input type="text" name="facebook" class="form-control" value="{{ old('facebook', $customer->facebook) }}" data-uppercase>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Birthdate</label>
                            <input type="date" class="form-control" id="edit_customer_birthdate" name="birthdate" value="{{ old('birthdate', $customer->user->birthdate ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Age</label>
                            <input type="number" class="form-control" id="edit_customer_age" name="age" value="{{ old('age', $customer->user->age ?? '') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>SPO</label>
                            <input type="text" name="spo" class="form-control" value="{{ old('spo', $customer->spo) }}" data-uppercase>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Center</label>
                            <select class="form-control select2" name="center">
                                <option value="">Select Center</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->name }}" {{ $customer->center === $center->name ? 'selected' : '' }}>
                                        {{ $center->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="Active" {{ $customer->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $customer->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="fs-6 fw-bold col-md-12 mt-2 mb-3"><i class="bi bi-geo-alt"></i> Location Details</div>
                        @if(!empty($isDistributorCustomerPage))
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="editCustomerArea">Awarded area <span class="text-danger">*</span></label>
                            <select id="editCustomerArea" name="area" class="form-control select2" required>
                                <option value="">Select awarded area</option>
                                @foreach($customerAreaNames as $areaName)
                                    <option value="{{ $areaName }}" {{ strcasecmp(trim(old('area', $customer->area)), $areaName) === 0 ? 'selected' : '' }}>{{ $areaName }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 mb-2">
                            <label>Street Name, Building, House No.</label>
                            <input type="text" class="form-control" name="street_address" id="edit_customer_street_address" value="{{ old('street_address', $customer->street_address) }}" data-uppercase>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Region</label>
                            <select class="form-control select2" id="edit_customer_region" name="location_region">
                                <option value="">-- Select Region --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Province</label>
                            <select class="form-control select2" id="edit_customer_province" name="location_province" disabled>
                                <option value="">-- Select Region First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>City/Municipality</label>
                            <select class="form-control select2" id="edit_customer_city" name="location_city" disabled>
                                <option value="">-- Select Province First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Barangay</label>
                            <select class="form-control select2" id="edit_customer_barangay" name="location_barangay" disabled>
                                <option value="">-- Select City First --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Postal Code</label>
                            <input type="text" class="form-control" name="postal_code" id="edit_customer_postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label>Complete Address Preview</label>
                            <input type="text" id="edit_customer_address" name="address" class="form-control" value="{{ old('address', $customer->address) }}" readonly>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedLocation = {
        region: @json(old('location_region', $customer->location_region)),
        province: @json(old('location_province', $customer->location_province)),
        city: @json(old('location_city', $customer->location_city)),
        barangay: @json(old('location_barangay', $customer->location_barangay)),
    };
    const originalAddress = @json(old('address', $customer->address));
    let isEditingAddress = false;

    function calculateAge(birthdate) {
        if (!birthdate) return '';
        const today = new Date();
        const birthDate = new Date(birthdate);
        if (birthDate > today) return '';
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDifference = today.getMonth() - birthDate.getMonth();
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

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
            if (normalizeLocation(this.value) === saved || normalizeLocation($(this).text()) === saved) {
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

    function cleanLocation(value) {
        return (!value || value.includes('Select') || value.includes('Loading')) ? '' : value.trim();
    }

    function selectedText(selector) {
        const text = $(`${selector} option:selected`).text();
        return cleanLocation(text);
    }

    function generateAddress() {
        if (!isEditingAddress && originalAddress) {
            $('#edit_customer_address').val(originalAddress);
            return;
        }

        const parts = [
            cleanLocation($('#edit_customer_street_address').val()),
            selectedText('#edit_customer_barangay'),
            selectedText('#edit_customer_city'),
            selectedText('#edit_customer_province'),
            selectedText('#edit_customer_region'),
            cleanLocation($('#edit_customer_postal_code').val()),
            'Philippines'
        ].filter(Boolean);

        $('#edit_customer_address').val(parts.join(', '));
    }

    $('#edit_customer_birthdate').attr('max', new Date().toISOString().split('T')[0]);
    $('#edit_customer_birthdate').on('change input', function() {
        $('#edit_customer_age').val(calculateAge(this.value));
    });

    $.get('/api/regions')
        .done(function(data) {
            let options = '<option value="">-- Select Region --</option>';
            data.forEach(function(item) {
                options += `<option value="${item.name}">${item.name}</option>`;
            });
            $('#edit_customer_region').html(options);

            if (setSavedValue($('#edit_customer_region'), savedLocation.region)) {
                $('#edit_customer_region').trigger('change');
            }
        })
        .fail(function() {
            alert('Failed to load regions');
        });

    $('#edit_customer_region').on('change', function() {
        const region = $(this).val();
        $('#edit_customer_province').prop('disabled', true).html('<option>Loading...</option>');
        $('#edit_customer_city').prop('disabled', true).html('<option>-- Select Province First --</option>');
        $('#edit_customer_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');

        if (!region) return;

        if (isNcrRegion(region)) {
            $('#edit_customer_province').html('<option value="Metro Manila" selected>Metro Manila</option>').prop('disabled', false);
            $.get('/api/regions/' + encodeURIComponent(region) + '/cities-municipalities')
                .done(function(data) {
                    let options = '<option value="">-- Select City/Municipality --</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.name}">${item.name}</option>`;
                    });
                    $('#edit_customer_city').html(options).prop('disabled', false);
                    if (setSavedValue($('#edit_customer_city'), savedLocation.city)) {
                        $('#edit_customer_city').trigger('change');
                    }
                    generateAddress();
                });
            return;
        }

        $.get('/api/regions/' + encodeURIComponent(region) + '/provinces')
            .done(function(data) {
                let options = '<option value="">-- Select Province --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                $('#edit_customer_province').html(options).prop('disabled', false);
                if (setSavedValue($('#edit_customer_province'), savedLocation.province)) {
                    $('#edit_customer_province').trigger('change');
                }
                generateAddress();
            });
    });

    $('#edit_customer_province').on('change', function() {
        const province = $(this).val();
        $('#edit_customer_city').prop('disabled', true).html('<option>Loading...</option>');
        $('#edit_customer_barangay').prop('disabled', true).html('<option>-- Select City First --</option>');

        if (!province || province === 'Metro Manila') return;

        $.get('/api/provinces/' + encodeURIComponent(province) + '/cities')
            .done(function(data) {
                let options = '<option value="">-- Select City/Municipality --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                $('#edit_customer_city').html(options).prop('disabled', false);
                if (setSavedValue($('#edit_customer_city'), savedLocation.city)) {
                    $('#edit_customer_city').trigger('change');
                }
                generateAddress();
            });
    });

    $('#edit_customer_city').on('change', function() {
        const city = $(this).val();
        $('#edit_customer_barangay').prop('disabled', true).html('<option>Loading...</option>');

        if (!city) return;

        $.get('/api/cities/' + encodeURIComponent(city) + '/barangays')
            .done(function(data) {
                let options = '<option value="">-- Select Barangay --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.name}" data-postal="${item.zip_code || ''}">${item.name}</option>`;
                });
                $('#edit_customer_barangay').html(options).prop('disabled', false);
                setSavedValue($('#edit_customer_barangay'), savedLocation.barangay);
                generateAddress();
            });
    });

    $('#edit_customer_barangay').on('change', function() {
        const postal = $(this).find(':selected').data('postal') || '';
        if (postal) {
            $('#edit_customer_postal_code').val(postal);
        }
        generateAddress();
    });

    $('#edit_customer_street_address, #edit_customer_postal_code, #edit_customer_region, #edit_customer_province, #edit_customer_city, #edit_customer_barangay')
        .on('mousedown keydown', function() {
            isEditingAddress = true;
        })
        .on('keyup change', generateAddress);
});
</script>
