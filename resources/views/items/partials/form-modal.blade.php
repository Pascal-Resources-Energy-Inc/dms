@php
    $isEdit = filled($item);
    $restoreInput = old('_item_form') === $modalId;
    $formValue = function ($key, $default = null) use ($restoreInput) {
        return $restoreInput ? old($key, $default) : $default;
    };
    $itemNameValue = $formValue('item', $item->item ?? '');
    $isStoveKit = strpos(strtolower(trim((string) $itemNameValue)), 'gaz lite stove kit') !== false;
    $stoveKitColors = \App\Item::STOVE_KIT_COLORS;
    $storedStoveKitAvailability = $isEdit && is_array($item->stove_kit_color_availability)
        ? $item->stove_kit_color_availability
        : collect($stoveKitColors)->mapWithKeys(fn($label, $color) => [$color => true])->all();
    $selectedStoveKitAvailability = $restoreInput ? old('stove_kit_color_availability', []) : $storedStoveKitAvailability;
    $imagePath = $isEdit && $item->item_image && file_exists(public_path('uploads/products/' . $item->item_image))
        ? asset('uploads/products/' . $item->item_image) : null;
@endphp

<div class="modal fade item-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ $action }}" class="modal-content" enctype="multipart/form-data">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <input type="hidden" name="_item_form" value="{{ $modalId }}">
            @if(!empty($redirectTo))<input type="hidden" name="redirect_to" value="{{ $redirectTo }}">@endif
            <div class="modal-header">
                <div><div class="items-eyebrow">Catalog item</div><h2 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h2><div class="text-muted small mt-1">Item details, pricing, and availability.</div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($restoreInput && $errors->any())
                    <div class="alert alert-danger small" role="alert">
                        <strong>Please check the following:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <div class="item-form-grid">
                    <div class="item-form-span item-image-field">
                        <span class="item-preview">
                            <img class="js-item-image-preview {{ $imagePath ? '' : 'd-none' }}" @if($imagePath) src="{{ $imagePath }}" @endif alt="Item image preview">
                            <i class="bi bi-image fs-4 js-item-image-placeholder {{ $imagePath ? 'd-none' : '' }}" aria-hidden="true"></i>
                        </span>
                        <div>
                            <label class="form-label" for="{{ $modalId }}_image">Item image</label>
                            <input id="{{ $modalId }}_image" type="file" name="item_image" class="form-control js-item-image" accept="image/jpeg,image/png,image/gif,image/webp" aria-describedby="{{ $modalId }}_image_help">
                            <div id="{{ $modalId }}_image_help" class="text-muted small mt-1">JPG, PNG, GIF, or WEBP. Maximum 2 MB.</div>
                        </div>
                    </div>
                    <div class="item-form-span">
                        <label class="form-label" for="{{ $modalId }}_name">Item name <span class="text-danger">*</span></label>
                        <input id="{{ $modalId }}_name" type="text" name="item" class="form-control js-item-name" value="{{ $itemNameValue }}" maxlength="255" required>
                    </div>
                    <div class="stove-kit-colors js-stove-kit-colors {{ $isStoveKit ? '' : 'd-none' }}">
                        <div class="item-section-heading"><h3>Stove kit colors</h3><p>Select colors available in AD purchase orders.</p></div>
                        <div class="stove-kit-color-grid">
                            @foreach($stoveKitColors as $colorValue => $colorLabel)
                                <div class="stove-kit-color-toggle">
                                    <label class="stove-kit-color-name" for="{{ $modalId }}_stove_color_{{ $colorValue }}">{{ $colorLabel }}</label>
                                    <div class="form-check form-switch m-0"><input type="checkbox" name="stove_kit_color_availability[{{ $colorValue }}]" value="1" class="form-check-input js-stove-kit-color-input" id="{{ $modalId }}_stove_color_{{ $colorValue }}" @if((bool) data_get($selectedStoveKitAvailability, $colorValue, false)) checked @endif @if(!$isStoveKit) disabled @endif></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="item-form-span">
                        <label class="form-label" for="{{ $modalId }}_description">Description</label>
                        <textarea id="{{ $modalId }}_description" name="item_description" class="form-control" rows="2">{{ $formValue('item_description', $item->item_description ?? '') }}</textarea>
                    </div>

                    <div class="item-form-span item-pricing-section">
                        <div class="item-section-heading"><h3>Pricing</h3><p>Set each price tier in Philippine pesos (PHP).</p></div>
                        <div class="item-pricing-regions">
                            @foreach(['standard' => 'Standard prices', 'vismin' => 'VisMin prices'] as $priceRegion => $regionLabel)
                                @php $isVismin = $priceRegion === 'vismin'; @endphp
                                <fieldset class="item-price-region {{ $isVismin ? 'item-price-region-vismin' : '' }}">
                                    <legend>{{ $regionLabel }}</legend>
                                    <p class="item-region-help" id="{{ $modalId }}_{{ $priceRegion }}_help">{{ $isVismin ? 'Visayas & Mindanao · Optional' : 'Default pricing · Required' }}</p>
                                    <div class="item-price-fields">
                                        @foreach(\App\Item::PRICE_TIERS as $standardField => $priceLabel)
                                            @php
                                                $priceField = ($isVismin ? 'vismin_' : '') . $standardField;
                                                $priceError = $restoreInput && $errors->has($priceField);
                                            @endphp
                                            <div>
                                                <label class="form-label" for="{{ $modalId }}_{{ $priceField }}">{{ $priceLabel }} price @if(!$isVismin)<span class="text-danger">*</span>@endif</label>
                                                <div class="item-money-input">
                                                    <span aria-hidden="true">₱</span>
                                                    <input id="{{ $modalId }}_{{ $priceField }}" type="number" name="{{ $priceField }}" class="form-control{{ $priceError ? ' is-invalid' : '' }}" value="{{ $formValue($priceField, $item->{$priceField} ?? ($isVismin ? '' : 0)) }}" min="0" step="0.01" inputmode="decimal" @if($isVismin) max="9999999999.99" placeholder="Not set" @else required @endif aria-describedby="{{ $modalId }}_{{ $priceRegion }}_help{{ $priceError ? ' ' . $modalId . '_' . $priceField . '_error' : '' }}" @if($priceError) aria-invalid="true" @endif>
                                                </div>
                                                @if($priceError)<div id="{{ $modalId }}_{{ $priceField }}_error" class="invalid-feedback d-block">{{ $errors->first($priceField) }}</div>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                        <p class="item-pricing-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Leave a VisMin price blank to use its standard price. Enter 0 only for a free item.</p>
                    </div>

                    <div>
                        <label class="form-label" for="{{ $modalId }}_status">Status <span class="text-danger">*</span></label>
                        <select id="{{ $modalId }}_status" name="status" class="form-select" required>
                            <option value="Activate" @if($formValue('status', $item->status ?? 'Activate') === 'Activate') selected @endif>Active</option>
                            <option value="Deactivate" @if($formValue('status', $item->status ?? '') === 'Deactivate') selected @endif>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="{{ $modalId }}_type">Item type <span class="text-danger">*</span></label>
                        <select id="{{ $modalId }}_type" name="item_type" class="form-select" required>
                            <option value="product" @if($formValue('item_type', $item->item_type ?? 'product') === 'product') selected @endif>Product</option>
                            <option value="bundle" @if($formValue('item_type', $item->item_type ?? '') === 'bundle') selected @endif>Bundle</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="{{ $modalId }}_dealer_points">Dealer points</label>
                        <input id="{{ $modalId }}_dealer_points" type="number" name="dealer_points" class="form-control" value="{{ $formValue('dealer_points', $item->dealer_points ?? 0) }}" min="0" step="1" inputmode="numeric">
                    </div>
                    <div>
                        <label class="form-label" for="{{ $modalId }}_customer_points">Customer points</label>
                        <input id="{{ $modalId }}_customer_points" type="number" name="customer_points" class="form-control" value="{{ $formValue('customer_points', $item->customer_points ?? 0) }}" min="0" step="1" inputmode="numeric">
                    </div>
                    <div class="item-form-span">
                        @if($showForAd ?? true)
                            <div class="form-check form-switch"><input type="checkbox" name="for_ad" value="1" class="form-check-input" id="{{ $modalId }}ForAd" @if($restoreInput ? old('for_ad', false) : ($item->for_ad ?? false)) checked @endif><label class="form-check-label fw-bold" for="{{ $modalId }}ForAd">For AD purchase orders</label></div>
                            <div class="text-muted small mt-1">Show this item in the AD-only product section.</div>
                        @else
                            <input type="hidden" name="for_ad" value="0">
                            <div class="text-muted small">This product or bundle will be available in the price matrix.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2" aria-hidden="true"></i> {{ $isEdit ? 'Save changes' : 'Create item' }}</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById(@json($modalId));
    if (!modal) return;
    const itemName = modal.querySelector('.js-item-name');
    const colorPanel = modal.querySelector('.js-stove-kit-colors');
    const colorInputs = modal.querySelectorAll('.js-stove-kit-color-input');
    function syncStoveKitColors() {
        const isStoveKit = itemName.value.toLowerCase().indexOf('gaz lite stove kit') !== -1;
        colorPanel.classList.toggle('d-none', !isStoveKit);
        colorInputs.forEach(function (input) { input.disabled = !isStoveKit; });
    }
    itemName.addEventListener('input', syncStoveKitColors);
    syncStoveKitColors();
    const imageInput = modal.querySelector('.js-item-image');
    const preview = modal.querySelector('.js-item-image-preview');
    const savedImage = preview.getAttribute('src');
    let previewUrl;
    imageInput.addEventListener('change', function () {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = imageInput.files[0];
        if (file && /^image\//.test(file.type)) {
            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.classList.remove('d-none');
            modal.querySelector('.js-item-image-placeholder').classList.add('d-none');
        } else {
            if (savedImage) preview.src = savedImage;
            else preview.removeAttribute('src');
            preview.classList.toggle('d-none', !savedImage);
            modal.querySelector('.js-item-image-placeholder').classList.toggle('d-none', !!savedImage);
        }
    });
    @if($restoreInput && $errors->any())
        if (window.bootstrap) {
            modal.addEventListener('shown.bs.modal', function () {
                const invalid = modal.querySelector('.is-invalid');
                if (invalid) { invalid.focus(); invalid.scrollIntoView({ block: 'center' }); }
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    @endif
});
</script>
