@php
    $chargeTypes = [
        'fixed' => 'Fixed Amount',
        'percentage' => 'Percentage',
        'discount' => 'Discount',
    ];
    $appliesToOptions = [
        'dealer' => 'Dealer',
        'customer' => 'Customer',
        'ad_purchase_order' => 'AD Purchase Order',
    ];
    $selectedType = old('charge_type', optional($charge)->charge_type ?? 'fixed');
    $selectedAppliesTo = old('applies_to', optional($charge)->applies_to ?? 'order');
    $selectedAdUserId = old('ad_user_id', optional($charge)->ad_user_id ?? optional(auth()->user())->id);
    $isActive = old('is_active', optional($charge)->is_active ?? true);
    $displayAmount = old('amount', optional($charge)->charge_type === 'discount'
        ? abs((float) optional($charge)->amount)
        : optional($charge)->amount);
@endphp

<div class="modal fade charge-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif
                <div class="modal-header">
                    <div class="charge-modal-title">
                        <span class="charge-modal-icon"><i class="bi bi-receipt-cutoff"></i></span>
                        <div>
                            <h5 class="modal-title" id="{{ $modalId }}Label">{{ $modalTitle }}</h5>
                            <div class="text-muted small">Set the charge name, amount, scope, and availability.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="{{ $modalId }}Name">Charge Name <span class="text-danger">*</span></label>
                            <input type="text" id="{{ $modalId }}Name" name="name" class="form-control" value="{{ old('name', optional($charge)->name) }}" maxlength="255" placeholder="Example: Handling Fee" required data-uppercase>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="{{ $modalId }}Code">Code <span class="text-danger">*</span></label>
                            <input type="text" id="{{ $modalId }}Code" name="code" class="form-control text-uppercase" value="{{ old('code', optional($charge)->code) }}" maxlength="60" placeholder="HANDLING" required data-uppercase>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="{{ $modalId }}AdUser">Area Distributor <span class="text-danger">*</span></label>
                            <select id="{{ $modalId }}AdUser" name="ad_user_id" class="form-select" required @if(auth()->user()->role !== 'Admin') disabled @endif>
                                <option value="">Select AD</option>
                                @foreach($adUsers as $adUser)
                                    <option value="{{ $adUser->id }}" @if((string) $selectedAdUserId === (string) $adUser->id) selected @endif>
                                        {{ optional($adUser->ad)->business_name ?: $adUser->name }}{{ optional($adUser->ad)->store_code ? ' - ' . optional($adUser->ad)->store_code : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->role !== 'Admin')
                                <input type="hidden" name="ad_user_id" value="{{ auth()->id() }}">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="{{ $modalId }}Type">Charge Type <span class="text-danger">*</span></label>
                            <select id="{{ $modalId }}Type" name="charge_type" class="form-select" required data-uppercase>
                                @foreach($chargeTypes as $value => $label)
                                    <option value="{{ $value }}" @if($selectedType === $value) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="{{ $modalId }}Amount">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">PHP / %</span>
                                <input type="number" id="{{ $modalId }}Amount" name="amount" class="form-control" value="{{ $displayAmount }}" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="{{ $modalId }}AppliesTo">Applies To <span class="text-danger">*</span></label>
                            <select id="{{ $modalId }}AppliesTo" name="applies_to" class="form-select" required>
                                @foreach($appliesToOptions as $value => $label)
                                    <option value="{{ $value }}" @if($selectedAppliesTo === $value) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="{{ $modalId }}Description">Description</label>
                            <textarea id="{{ $modalId }}Description" name="description" class="form-control" rows="3" maxlength="1000" placeholder="Short note about when this charge is used" data-uppercase>{{ old('description', optional($charge)->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="charge-status-card mb-0" for="{{ $modalId }}Active">
                                <i class="bi bi-toggle-on"></i>
                                <span class="flex-grow-1">
                                    <strong>Active Charge</strong>
                                    <small>Only active charges should be available for transactions.</small>
                                </span>
                                <input class="form-check-input m-0" type="checkbox" id="{{ $modalId }}Active" name="is_active" value="1" @if($isActive) checked @endif>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
