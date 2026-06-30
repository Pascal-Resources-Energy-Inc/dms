<div class="modal fade manage-area-modal" id="manageAreaModal-{{ $ad->id }}"  tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form id="areaForm-{{ $ad->id }}" data-ad="{{ $ad->id }}" method="POST" action="{{ url('ad/'.$ad->id.'/areas/update') }}">
            @csrf

            <div class="modal-content manage-area-shell border-0 shadow-lg">

                <div class="modal-header manage-area-header">
                    <div class="manage-area-title-wrap">
                        <span class="manage-area-icon">
                            <i class="ti ti-map-pin"></i>
                        </span>
                        <div>
                            <h4 class="modal-title manage-area-title">
                                Manage Awarded Areas
                            </h4>

                            <small class="manage-area-subtitle">
                                {{ strtoupper($ad->name) }}
                            </small>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body manage-area-body">

                    <div class="manage-area-toolbar">
                        <div>
                            <h5 class="manage-area-section-title">Awarded Area(s)</h5>

                            <small class="manage-area-help">
                                Add or update assigned areas
                            </small>
                        </div>

                        <button type="button"
                                class="btn btn-primary manage-area-add-btn"
                                data-ad="{{ $ad->id }}" onclick="addAreaRow({{ $ad->id }})">

                            <i class="ti ti-plus"></i>
                            Add Area
                        </button>
                    </div>

                    <div class="manage-area-rows" id="areaRows-{{ $ad->id }}">
                        @php
                            $areaOptionNames = $areas->pluck('name')->map(function ($name) {
                                return trim((string) $name);
                            });
                        @endphp

                        @forelse($ad->areas as $index => $area)
                            @php
                                $areaName = trim((string) $area->area_name);
                            @endphp

                            <div class="card manage-area-row border-0 shadow-sm area-row">

                                <div class="card-body manage-area-row-body">

                                    <input type="hidden"
                                           name="rows[{{ $index }}][id]"
                                           value="{{ $area->id }}">

                                    <div class="row g-3 align-items-end">

                                        <div class="col-md-7 mb-3">
                                            <label class="form-label manage-area-label">Area Name</label>
                                            <select class="form-select select2 select2-area"
                                                    data-select2-theme="bootstrap-5"
                                                    data-placeholder="Select Area"
                                                    name="rows[{{ $index }}][area_name]"
                                                    required>

                                                <option value="">
                                                    Select Area
                                                </option>

                                                @php
                                                    $selectedAreaName = trim((string) $area->area_name);
                                                @endphp

                                                @if($selectedAreaName !== '' && ! $areaOptionNames->contains($selectedAreaName))
                                                    <option value="{{ $selectedAreaName }}" selected>
                                                        {{ $selectedAreaName }}
                                                    </option>
                                                @endif

                                                @foreach($areas as $a)

                                                    @php
                                                        $optionAreaName = trim((string) $a->name);
                                                    @endphp

                                                    <option value="{{ $optionAreaName }}"
                                                        {{ strtolower($selectedAreaName) == strtolower($optionAreaName) ? 'selected' : '' }}>

                                                        {{ $optionAreaName }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <label class="form-label manage-area-label">Joining Date</label>

                                            <input type="date"
                                                   class="form-control"
                                                   name="rows[{{ $index }}][joining_date]"
                                                   value="{{ !empty($area->joining_date) ? \Carbon\Carbon::parse($area->joining_date)->format('Y-m-d') : '' }}">

                                        </div>

                                        <div class="col-md-1 mb-3 d-flex align-items-end">

                                            <button type="button"
                                                    class="btn btn-outline-danger manage-area-remove-btn remove-row"
                                                    title="Remove area">

                                                <i class="ti ti-trash"></i>

                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="empty-area manage-area-empty">

                                <i class="ti ti-map-pin-off"></i>

                                <strong>No awarded areas yet</strong>

                                <p>Click Add Area to assign the first territory.</p>

                            </div>

                        @endforelse

                    </div>

                </div>

                <div class="modal-footer manage-area-footer">

                    <button type="button"
                            class="btn btn-light border manage-area-cancel-btn"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-success px-4 manage-area-save-btn">

                        <i class="ti ti-device-floppy me-1"></i>
                        Save Areas

                    </button>

                </div>

            </div>

        </form>
    </div>
</div>

<?php if (empty($GLOBALS['manageAreaScriptRendered'])): ?>
<?php $GLOBALS['manageAreaScriptRendered'] = true; ?>
<script>
    window.manageAreaOptions = @json($areas->pluck('name')->values());

    (function () {

        function initAreaModalManager() {
            if (typeof window.jQuery === 'undefined') {
                return setTimeout(initAreaModalManager, 50);
            }

            var $ = window.jQuery;

            // prevent double loading in Laravel (VERY IMPORTANT)
            if (window.AreaModalManager) return;

            window.AreaModalManager = true;

            function ensureSelect2Ready(callback) {
                if ($.fn.select2) {
                    callback();
                    return;
                }

                if (window.manageAreaSelect2Loading) {
                    setTimeout(function () {
                        ensureSelect2Ready(callback);
                    }, 50);
                    return;
                }

                window.manageAreaSelect2Loading = true;

                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                script.onload = function () {
                    window.manageAreaSelect2Loading = false;
                    callback();
                };
                document.head.appendChild(script);
            }

            function initAreaSelect2(scope) {
                ensureSelect2Ready(function () {
                    var $scope = $(scope);
                    var $selects = $scope.is('select.select2-area')
                        ? $scope
                        : $scope.find('select.select2-area');

                    $selects.each(function () {
                        var $select = $(this);

                        if ($select.hasClass('select2-hidden-accessible')) {
                            return;
                        }

                        $select.select2({
                            width: '100%',
                            dropdownParent: $select.closest('.modal'),
                            placeholder: $select.data('placeholder') || 'Select Area',
                            allowClear: true,
                            theme: $select.data('select2-theme') || 'bootstrap-5'
                        });
                    });
                });
            }

            function escapeHtml(value) {
                return String(value === null || typeof value === 'undefined' ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            window.addAreaRow = function (adId) {

                let container = $('#areaRows-' + adId);

                container.find('.empty-area').remove();

                let index = Date.now(); // still unique but numeric-safe
                let areaOptions = (window.manageAreaOptions || [])
                    .map(function (name) {
                        let safeName = escapeHtml(name);
                        return `<option value="${safeName}">${safeName}</option>`;
                    })
                    .join('');

                let html = `
                    <div class="card manage-area-row border-0 shadow-sm area-row">
                        <div class="card-body manage-area-row-body">
                            <div class="row g-3 align-items-end">

                                <input type="hidden" name="rows[${index}][id]" value="">

                                <div class="col-md-7 mb-3">
                                    <label class="form-label manage-area-label">Area Name</label>
                                    <select class="form-select select2 select2-area" data-select2-theme="bootstrap-5" data-placeholder="Select Area"
                                            name="rows[${index}][area_name]" required>
                                        <option value="">Select Area</option>
                                        ${areaOptions}
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label manage-area-label">Joining Date</label>
                                    <input type="date"
                                        class="form-control"
                                        name="rows[${index}][joining_date]">
                                </div>

                                <div class="col-md-1 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger manage-area-remove-btn remove-row" title="Remove area">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                `;

                let row = $(html);
                container.append(row);

                initAreaSelect2(row);
            };

            /**
             * Remove Row
             */
            $(document).on('click', '.remove-row', function () {
                let row = $(this).closest('.area-row');
                let modal = row.closest('.modal');
                let container = modal.find('[id^="areaRows-"]');

                // destroy select2 only in the removed row
                row.find('select.select2').each(function () {
                    if ($.fn.select2 && $(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                row.remove();

                if (container.find('.area-row').length === 0) {
                    container.html(`
                        <div class="empty-area manage-area-empty">
                            <i class="ti ti-map-pin-off"></i>
                            <strong>No awarded areas yet</strong>
                            <p>Click Add Area to assign the first territory.</p>
                        </div>
                    `);
                }
            });

            $(document).on('shown.bs.modal', '.manage-area-modal', function () {
                initAreaSelect2(this);
            });

            $(document).ready(function () {
                initAreaSelect2(document);
            });

            $(document).on('submit', 'form[id^="areaForm-"]', function (e) {

                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let btn = form.find('button[type="submit"]');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: form.serialize(), // THIS IS NOW SAFE

                    success: function (res) {

                        if (res.status) {

                            alert(res.message);

                            let modalEl = document.getElementById(
                                'manageAreaModal-' + form.data('ad')
                            );

                            let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            if (modal) modal.hide();

                            window.location.reload();
                        } else {
                            alert(res.message || 'Unable to update areas');
                        }
                    },

                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Error occurred');
                    },

                    complete: function () {
                        btn.prop('disabled', false)
                            .html('<i class="ti ti-device-floppy me-1"></i> Save Areas');
                    }
                });

            });
        }

        window.addEventListener('load', initAreaModalManager);
    })();
</script>
<?php endif; ?>

<?php if (empty($GLOBALS['manageAreaStyleRendered'])): ?>
<?php $GLOBALS['manageAreaStyleRendered'] = true; ?>
<style>

    .manage-area-modal {
        --manage-blue: #0d6efd;
        --manage-blue-soft: #eef5ff;
        --manage-text: #172033;
        --manage-muted: #667085;
        --manage-border: #e6eaf0;
    }

    .manage-area-shell {
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .manage-area-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, #f8fbff, #ffffff);
        border-bottom: 1px solid var(--manage-border);
    }

    .manage-area-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .manage-area-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: var(--manage-blue);
        font-size: 22px;
        background: var(--manage-blue-soft);
        border: 1px solid #d8e8ff;
        border-radius: 10px;
    }

    .manage-area-title {
        margin: 0;
        color: var(--manage-text);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    .manage-area-subtitle {
        display: block;
        margin-top: 3px;
        color: var(--manage-muted);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .manage-area-body {
        padding: 18px 22px;
        background: #f6f8fb;
    }

    .manage-area-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        padding: 14px;
        background: #fff;
        border: 1px solid var(--manage-border);
        border-radius: 10px;
    }

    .manage-area-section-title {
        margin: 0;
        color: var(--manage-text);
        font-size: 15px;
        font-weight: 800;
    }

    .manage-area-help {
        display: block;
        margin-top: 2px;
        color: var(--manage-muted);
        font-size: 12px;
    }

    .manage-area-add-btn,
    .manage-area-save-btn,
    .manage-area-cancel-btn {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 800;
        border-radius: 8px;
        white-space: nowrap;
    }

    .manage-area-rows {
        display: grid;
        gap: 12px;
    }

    .manage-area-row {
        margin-bottom: 0 !important;
        border: 1px solid var(--manage-border) !important;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .05) !important;
    }

    .manage-area-row-body {
        padding: 14px;
    }

    .manage-area-label {
        margin-bottom: 6px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .manage-area-row .form-control,
    .manage-area-row .form-select {
        min-height: 42px;
        font-size: 13px;
        border-color: #d9e1ea;
        border-radius: 8px;
    }

    .manage-area-row .form-control:focus,
    .manage-area-row .form-select:focus {
        border-color: var(--manage-blue);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
    }

    .manage-area-remove-btn {
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 8px;
    }

    .manage-area-empty {
        padding: 42px 16px;
        color: var(--manage-muted);
        text-align: center;
        background: #fff;
        border: 1px dashed #cfd8e3;
        border-radius: 10px;
    }

    .manage-area-empty i {
        display: block;
        margin-bottom: 9px;
        color: #a9b4c2;
        font-size: 38px;
    }

    .manage-area-empty strong {
        display: block;
        color: #344054;
        font-size: 14px;
    }

    .manage-area-empty p {
        margin: 4px 0 0;
        font-size: 13px;
    }

    .manage-area-footer {
        padding: 14px 22px;
        background: #fff;
        border-top: 1px solid var(--manage-border);
    }

    .manage-area-modal .select2-container {
        width: 100% !important;
    }

    .manage-area-modal .select2-container--bootstrap-5 .select2-selection,
    .manage-area-modal .select2-container--default .select2-selection--single {
        min-height: 42px;
        display: flex;
        align-items: center;
        border-color: #d9e1ea;
        border-radius: 8px;
    }

    .manage-area-modal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
    .manage-area-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 12px;
        color: #344054;
        font-size: 13px;
        line-height: 40px;
    }

    .manage-area-modal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow,
    .manage-area-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }

    .manage-area-modal .select2-container--open .select2-selection {
        border-color: var(--manage-blue);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
    }

    .select2-container--open {
        z-index: 999999 !important;
    }

    .select2-dropdown {
        border-color: #d9e1ea;
        border-radius: 8px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .14);
        z-index: 999999 !important;
    }

    .select2-search--dropdown {
        padding: 8px;
    }

    .select2-search--dropdown .select2-search__field {
        min-height: 36px;
        border-color: #d9e1ea !important;
        border-radius: 7px;
        outline: none;
    }

    .select2-results__option {
        font-size: 13px;
        padding: 8px 10px;
    }

    .select2-results__option--highlighted {
        background: var(--manage-blue) !important;
    }

    @media (max-width: 767px) {
        .manage-area-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .manage-area-add-btn {
            width: 100%;
        }

        .manage-area-remove-btn {
            width: 100%;
        }
    }

</style>
<?php endif; ?>
