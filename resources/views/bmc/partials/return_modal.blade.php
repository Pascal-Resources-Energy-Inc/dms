@php
    $returnRetry = (string) old('return_accountability_id') === (string) $accountability['id'];
    $returnModeHints = [
        'Drop off' => 'e.g., HO, Plant, Warehouse',
        'Courier' => 'e.g., SPX4321, Tracking No. 4123',
        'Officer Name' => 'e.g., Designation - Name',
        'Field location' => 'e.g., Field location',
    ];
    $returnModes = ['Drop off' => 'Drop off', 'Courier' => 'Courier', 'Officer Name' => 'Thru Officer', 'Field location' => 'Thru Plant/Warehouse'];
    $selectedMode = $returnRetry ? old('return_mode') : null;
@endphp
<div class="modal fade bmc-return-modal" id="requestBmcReturn{{ $accountability['id'] }}" tabindex="-1" aria-labelledby="requestBmcReturnTitle{{ $accountability['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content border-0" method="POST" enctype="multipart/form-data" action="{{ route('bmc.return', $accountability['id']) }}" data-bmc-return-form>
            @csrf
            <input type="hidden" name="return_accountability_id" value="{{ $accountability['id'] }}">
            <input type="hidden" name="bmc_request_id" value="{{ $borrowing['id'] ?? $accountability['marketing_collateral_borrowing_id'] ?? '' }}">
            <div class="modal-header rounded-top" style="background: #3993d1; color: #fff;">
                <div><h5 class="modal-title text-white" id="requestBmcReturnTitle{{ $accountability['id'] }}">Return items</h5><small>{{ $accountability['accountability_ref'] ?? '-' }}</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body">
                @if($returnRetry && $errors->any())
                    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <p class="text-muted mb-3">Select the exact serial number(s) of item you are returning now.</p>
                @foreach($accountability['items'] ?? [] as $item)
                    @php
                        $serialRecords = collect($item['serials'] ?? []);
                        $returnedNumbers = collect($accountability['return_history'] ?? [])->where('bmc_accountability_item_id', $item['id'])->flatMap(function ($entry) {
                            $raw = $entry['serial_numbers'] ?? '';
                            $decoded = json_decode($raw, true);
                            return is_array($decoded) && isset($decoded['numbers']) && is_array($decoded['numbers']) ? $decoded['numbers'] : array_filter(array_map('trim', preg_split('/[,\r\n]+/', $raw)));
                        });
                        $rawSerials = $item['serial_number'] ?? '';
                        $decodedSerials = json_decode($rawSerials, true);
                        $legacySerials = is_array($decodedSerials) && isset($decodedSerials['numbers']) && is_array($decodedSerials['numbers']) ? $decodedSerials['numbers'] : preg_split('/[,\r\n]+/', $rawSerials);
                        $availableSerials = $serialRecords->isNotEmpty() ? $serialRecords->where('status', 'Available')->values() : collect($legacySerials)->map(function ($serial) { return trim($serial); })->filter()->diff($returnedNumbers)->values();
                        $oldIds = collect($returnRetry ? old('return_serial_ids.'.$item['id'], []) : [])->map(function ($id) { return (string) $id; });
                        $oldNumbers = collect($returnRetry ? old('return_serial_numbers.'.$item['id'], []) : []);
                    @endphp
                    @if((int) ($item['released_quantity'] ?? 0) > (int) ($item['returned_quantity'] ?? 0) && $availableSerials->isNotEmpty())
                        <section class="bmc-return-serial-group mb-3" data-bmc-return-group>
                            <div class="d-flex justify-content-between align-items-center mb-2"><strong>{{ $item['item_name'] ?? 'Item' }}</strong><span class="badge bg-light text-dark border" data-bmc-selected-count>0 selected</span></div>
                            @foreach($availableSerials as $serial)
                                @php
                                    $serialNumber = $serialRecords->isNotEmpty() ? ($serial['serial_number'] ?? '') : $serial;
                                    $serialId = $serialRecords->isNotEmpty() ? $serial['id'] : null;
                                    $serialName = $serialRecords->isNotEmpty() ? 'return_serial_ids['.$item['id'].'][]' : 'return_serial_numbers['.$item['id'].'][]';
                                    $serialSelected = $serialRecords->isNotEmpty() ? $oldIds->contains((string) $serialId) : $oldNumbers->contains($serialNumber);
                                @endphp
                                <label class="bmc-return-serial-option mb-2">
                                    <input type="checkbox" name="{{ $serialName }}" value="{{ $serialId ?? $serialNumber }}" data-bmc-return-serial @if($serialSelected) checked @endif>
                                    <span>{{ $serialNumber }}</span>
                                </label>
                            @endforeach
                        </section>
                    @endif
                @endforeach

                <div class="card border mb-3">
                    <div class="card-header bg-light py-2"><h6 class="mb-0 fw-bold">Mode of Return</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5 mb-3 mb-md-0">
                                <label class="fw-bold small text-uppercase" for="bmcReturnMode{{ $accountability['id'] }}">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="bmcReturnMode{{ $accountability['id'] }}" name="return_mode" data-bmc-return-mode required>
                                    <option value="">-- Select Type --</option>
                                    @foreach($returnModes as $value => $label)
                                        <option value="{{ $value }}" data-detail-placeholder="{{ $returnModeHints[$value] }}" @if($selectedMode === $value) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="fw-bold small text-uppercase" for="bmcReturnDetails{{ $accountability['id'] }}">Details <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="bmcReturnDetails{{ $accountability['id'] }}" name="return_mode_details" maxlength="1900" placeholder="{{ $returnModeHints[$selectedMode] ?? '' }}" value="{{ $returnRetry ? old('return_mode_details') : '' }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="fw-bold" for="bmcReturnRemarks{{ $accountability['id'] }}">Return Remarks <span class="text-danger">*</span></label>
                    <textarea id="bmcReturnRemarks{{ $accountability['id'] }}" name="return_remarks" class="form-control" rows="4" maxlength="2000" placeholder="e.g., item condition" required>{{ $returnRetry ? old('return_remarks') : '' }}</textarea>
                </div>
                <div class="form-group">
                    <label class="fw-bold" for="bmcReturnAttachment{{ $accountability['id'] }}">Proof of Attachment <span class="text-danger">*</span></label>
                    <input type="file" id="bmcReturnAttachment{{ $accountability['id'] }}" name="attachment[]" class="form-control" accept="image/jpeg,image/png" multiple required>
                    <small class="form-text text-muted">Upload up to 3 JPG or PNG photos, 5 MB each.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Return</button>
            </div>
        </form>
    </div>
</div>
<style>
    .bmc-return-serial-group { padding: 14px; border: 1px solid #dee2e6; border-radius: 8px; }
    .bmc-return-serial-option { display: flex; align-items: center; min-height: 42px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 7px; background: #fff; cursor: pointer; }
    .bmc-return-serial-option:hover { border-color: #59c2e5; background: #f7fcfe; }
    .bmc-return-serial-option input { margin-right: 10px; }
    .bmc-return-serial-option span { overflow-wrap: anywhere; font-weight: 600; }
    .bmc-return-modal input.form-control,
    .bmc-return-modal textarea.form-control,
    .bmc-return-modal select.form-control { color: #1d2939; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('requestBmcReturn{{ $accountability['id'] }}');
        if (!modal) return;
        var form = modal.querySelector('[data-bmc-return-form]');
        function updateCounts() {
            modal.querySelectorAll('[data-bmc-return-group]').forEach(function (group) {
                group.querySelector('[data-bmc-selected-count]').textContent = group.querySelectorAll('[data-bmc-return-serial]:checked').length + ' selected';
            });
        }
        modal.addEventListener('change', function (event) {
            if (event.target.matches('[data-bmc-return-serial]')) updateCounts();
            if (event.target.matches('[data-bmc-return-mode]')) form.elements.namedItem('return_mode_details').placeholder = event.target.options[event.target.selectedIndex].dataset.detailPlaceholder || '';
        });
        form.addEventListener('submit', function (event) {
            if (!form.querySelector('[data-bmc-return-serial]:checked')) {
                event.preventDefault();
                Swal.fire('Serial number required', 'Select at least one serial number to return.', 'warning');
                return;
            }
            var photos = form.querySelector('input[type="file"]').files;
            if (photos.length > 3 || Array.from(photos).some(function (photo) { return photo.size > 5 * 1024 * 1024; })) {
                event.preventDefault();
                Swal.fire('Check proof photos', 'Upload up to 3 JPG or PNG photos, 5 MB each.', 'warning');
                return;
            }
            form.querySelector('button[type="submit"]').disabled = true;
        });
        updateCounts();
        @if($returnRetry && $errors->any())
            bootstrap.Modal.getOrCreateInstance(modal).show();
        @endif
    });
</script>
