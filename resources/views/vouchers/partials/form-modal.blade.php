@php
    $isEdit = (bool) $voucher;
    $selectedDistributor = old('name', $voucher->name ?? '');
    $selectedAreaNames = old('area_names', $voucher->area_names ?? []);
    $selectedAreaNames = is_array($selectedAreaNames) ? array_values($selectedAreaNames) : [];
    $discountType = old('discount_type', $voucher->discount_type ?? 'fixed');
    $isActive = old('is_active', $voucher->is_active ?? true);
@endphp

<div class="modal fade voucher-form-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}_label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-select2">
        <div class="modal-content voucher-modal-content">
            <form action="{{ $action }}" method="POST" class="voucher-form" novalidate>
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header voucher-modal-header">
                    <div class="voucher-modal-heading">
                        <span class="voucher-modal-icon"><i class="bi bi-ticket-perforated"></i></span>
                        <div>
                            <div class="voucher-modal-kicker">Voucher Management</div>
                            <h5 class="modal-title" id="{{ $modalId }}_label">{{ $title }}</h5>
                            <p>Set the distributor coverage, discount rules, limits, and validity period.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body voucher-modal-body">
                    <div class="voucher-form-section">
                        <div class="voucher-section-head">
                            <span><i class="bi bi-person-badge"></i></span>
                            <div>
                                <h6>Voucher Assignment</h6>
                                <p>Identify the voucher and choose where it can be used.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label" for="{{ $modalId }}_code">Voucher Code <span class="text-danger">*</span></label>
                                <div class="voucher-input-icon">
                                    <i class="bi bi-upc-scan"></i>
                                    <input type="text" id="{{ $modalId }}_code" name="code" class="form-control text-uppercase" value="{{ old('code', $voucher->code ?? '') }}" placeholder="ADREBATE100" maxlength="100" autocomplete="off" required>
                                </div>
                                <small class="voucher-help">Use a short, memorable, unique code.</small>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label" for="{{ $modalId }}_distributor">Distributor <span class="text-danger">*</span></label>
                                <select id="{{ $modalId }}_distributor" name="name" class="form-select select2 voucher-distributor-select" data-placeholder="Search distributor by code or name" data-minimum-results-for-search="0" data-dropdown-css-class="voucher-select2-dropdown" data-area-target="{{ $modalId }}_areas" data-areas-url="{{ route('vouchers.distributor-areas') }}" required>
                                    <option value=""></option>
                                    @foreach($areaDistributors as $areaDistributor)
                                        @php
                                            $storeCode = optional($areaDistributor->ad)->store_code;
                                            $distributorValue = $storeCode ?: $areaDistributor->name;
                                            $areaCount = optional(optional($areaDistributor)->ad)->areas ? $areaDistributor->ad->areas->count() : 0;
                                        @endphp
                                        <option value="{{ $distributorValue }}"
                                            data-distributor-id="{{ $areaDistributor->id }}"
                                            data-store-code="{{ $storeCode ?: 'NO CODE' }}"
                                            data-name="{{ $areaDistributor->name }}"
                                            data-role="{{ $areaDistributor->role }}"
                                            data-email="{{ $areaDistributor->email }}"
                                            data-area-count="{{ $areaCount }}"
                                            @if(in_array($selectedDistributor, [$storeCode, $areaDistributor->name], true)) selected @endif>
                                            {{ $storeCode ? $storeCode . ' - ' : '' }}{{ $areaDistributor->name }}
                                        </option>
                                    @endforeach 
                                </select>
                                <small class="voucher-help">Areas are loaded from the selected distributor.</small>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label" for="{{ $modalId }}_areas">Authorized Areas <span class="text-danger voucher-area-required d-none">*</span></label>
                                <div class="voucher-area-picker" id="{{ $modalId }}_areas" data-selected="{{ json_encode($selectedAreaNames) }}">
                                    <div class="voucher-area-toolbar">
                                        <span class="voucher-area-count badge rounded-pill bg-light text-dark">0 selected</span>
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Area selection controls">
                                            <button type="button" class="btn btn-outline-secondary voucher-area-select-all" disabled>Select All</button>
                                            <button type="button" class="btn btn-outline-secondary voucher-area-clear" disabled>Clear</button>
                                        </div>
                                    </div>
                                    <div class="voucher-area-options is-disabled">
                                        <div class="voucher-area-placeholder">
                                            <i class="bi bi-geo-alt"></i>
                                            <span>Select a distributor first</span>
                                        </div>
                                    </div>
                                </div>
                                <small class="voucher-help voucher-area-help"><i class="bi bi-info-circle"></i> Select a distributor to view assigned areas.</small>
                            </div>
                            <div class="col-12">
                                <div class="voucher-description-field">
                                    <div class="voucher-description-icon"><i class="bi bi-card-text"></i></div>
                                    <div class="flex-grow-1">
                                        <label class="form-label" for="{{ $modalId }}_description">Description</label>
                                        <textarea id="{{ $modalId }}_description" name="description" class="form-control border-0 p-0" data-uppercase rows="2" maxlength="1000" placeholder="Add internal notes or a short description">{{ old('description', $voucher->description ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="voucher-form-section">
                        <div class="voucher-section-head">
                            <span><i class="bi bi-percent"></i></span>
                            <div>
                                <h6>Discount Rules</h6>
                                <p>Control the discount amount and minimum eligible order.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_discount_type">Discount Type <span class="text-danger">*</span></label>
                                <select id="{{ $modalId }}_discount_type" name="discount_type" class="form-select voucher-discount-type" required>
                                    <option value="fixed" @if($discountType === 'fixed') selected @endif>Fixed Amount</option>
                                    <option value="percent" @if($discountType === 'percent') selected @endif>Percentage</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_discount_value">Discount Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text voucher-discount-prefix">{{ $discountType === 'percent' ? '%' : 'PHP' }}</span>
                                    <input type="number" id="{{ $modalId }}_discount_value" name="discount_value" class="form-control voucher-discount-value" min="0.01" @if($discountType === 'percent') max="100" @endif step="0.01" value="{{ old('discount_value', $voucher->discount_value ?? '') }}" placeholder="0.00" required>
                                </div>
                                <small class="voucher-help voucher-discount-help">Enter the fixed rebate amount.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_minimum">Minimum Order</label>
                                <div class="input-group">
                                    <span class="input-group-text">PHP</span>
                                    <input type="number" id="{{ $modalId }}_minimum" name="minimum_order_amount" class="form-control" min="0" step="0.01" value="{{ old('minimum_order_amount', $voucher->minimum_order_amount ?? 0) }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="voucher-form-section">
                        <div class="voucher-section-head">
                            <span><i class="bi bi-calendar3"></i></span>
                            <div>
                                <h6>Limits and Availability</h6>
                                <p>Define usage capacity, validity dates, and availability status.</p>
                            </div>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_usage_limit">Usage Limit</label>
                                <input type="number" id="{{ $modalId }}_usage_limit" name="usage_limit" class="form-control" min="1" value="{{ old('usage_limit', $voucher->usage_limit ?? '') }}" placeholder="Leave blank for unlimited">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_starts_at">Start Date</label>
                                <input type="date" id="{{ $modalId }}_starts_at" name="starts_at" class="form-control voucher-start-date" value="{{ old('starts_at', $voucher && $voucher->starts_at ? $voucher->starts_at->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $modalId }}_expires_at">Expiry Date</label>
                                <input type="date" id="{{ $modalId }}_expires_at" name="expires_at" class="form-control voucher-expiry-date" value="{{ old('expires_at', $voucher && $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '') }}">
                                <small class="voucher-help voucher-date-help">No expiry date means the voucher remains available.</small>
                            </div>
                            <div class="col-12">
                                <label class="voucher-status-card" for="{{ $modalId }}_active">
                                    <span class="voucher-status-card-icon"><i class="bi bi-power"></i></span>
                                    <span class="voucher-status-card-copy">
                                        <strong>Voucher is active</strong>
                                        <small>Active vouchers can be applied when all other rules are satisfied.</small>
                                    </span>
                                    <span class="form-check form-switch m-0">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="{{ $modalId }}_active" @if($isActive) checked @endif>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer voucher-modal-footer">
                    <span class="voucher-required-note"><span>*</span> Required fields</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light voucher-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger voucher-submit-btn">
                            <i class="bi {{ $isEdit ? 'bi-check2-circle' : 'bi-plus-circle' }}"></i>
                            {{ $isEdit ? 'Save Changes' : 'Create Voucher' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
