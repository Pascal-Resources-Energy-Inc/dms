@php
    $isEdit = filled($item);
    $itemNameValue = old('item', $item->item ?? '');
    $isStoveKit = strpos(strtolower(trim((string) $itemNameValue)), 'gaz lite stove kit') !== false;
    $stoveKitColors = \App\Item::STOVE_KIT_COLORS;
    $storedStoveKitAvailability = $isEdit && is_array($item->stove_kit_color_availability)
        ? $item->stove_kit_color_availability
        : collect($stoveKitColors)->mapWithKeys(fn($label, $color) => [$color => true])->all();
    $selectedStoveKitAvailability = old('stove_kit_color_availability', $storedStoveKitAvailability);
    $imagePath = $isEdit && $item->item_image && file_exists(public_path('uploads/products/' . $item->item_image))
        ? asset('uploads/products/' . $item->item_image)
        : null;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ $action }}" class="modal-content" enctype="multipart/form-data">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            @if(!empty($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                    <div class="text-muted small">Fill out the item details below.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="item-form-grid">
                    <div class="item-form-span d-flex align-items-center gap-3">
                        <span class="item-preview">
                            @if($imagePath)
                                <img src="{{ $imagePath }}" alt="{{ $item->item }}">
                            @else
                                <i class="bi bi-image fs-4"></i>
                            @endif
                        </span>
                        <div class="flex-grow-1">
                            <label class="form-label">Item Image</label>
                            <input type="file" name="item_image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <div class="text-muted small mt-1">JPG, PNG, GIF, or WEBP. Max 2MB.</div>
                        </div>
                    </div>

                    <div class="item-form-span">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item" class="form-control js-item-name" value="{{ $itemNameValue }}" required>
                    </div>

                    <div class="stove-kit-colors js-stove-kit-colors {{ $isStoveKit ? '' : 'd-none' }}">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                            <div>
                                <div class="fw-bold text-dark">Stove Kit Color Availability</div>
                                <div class="text-muted small">Turn off colors that should not appear in AD purchase orders.</div>
                            </div>
                        </div>
                        <div class="stove-kit-color-grid">
                            @foreach($stoveKitColors as $colorValue => $colorLabel)
                                <div class="stove-kit-color-toggle">
                                    <span class="stove-kit-color-name">{{ $colorLabel }}</span>
                                    <div class="form-check form-switch m-0">
                                        <input type="checkbox"
                                            name="stove_kit_color_availability[{{ $colorValue }}]"
                                            value="1"
                                            class="form-check-input js-stove-kit-color-input"
                                            id="{{ $modalId }}_stove_color_{{ $colorValue }}"
                                            @if((bool) data_get($selectedStoveKitAvailability, $colorValue, false)) checked @endif
                                            @if(!$isStoveKit) disabled @endif>
                                        <label class="visually-hidden" for="{{ $modalId }}_stove_color_{{ $colorValue }}">{{ $colorLabel }} available</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="item-form-span">
                        <label class="form-label">Description</label>
                        <textarea name="item_description" class="form-control" rows="3">{{ old('item_description', $item->item_description ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="form-label">SRP Price <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $item->price ?? 0) }}" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label class="form-label">Dealer Price <span class="text-danger">*</span></label>
                        <input type="number" name="dealer_price" class="form-control" value="{{ old('dealer_price', $item->dealer_price ?? 0) }}" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label class="form-label">Mega Dealer Price <span class="text-danger">*</span></label>
                        <input type="number" name="md_price" class="form-control" value="{{ old('md_price', $item->md_price ?? 0) }}" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label class="form-label">Distributor Price <span class="text-danger">*</span></label>
                        <input type="number" name="dprice" class="form-control" value="{{ old('dprice', $item->dprice ?? 0) }}" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="Activate" @if(old('status', $item->status ?? 'Activate') === 'Activate') selected @endif>Activate</option>
                            <option value="Deactivate" @if(old('status', $item->status ?? '') === 'Deactivate') selected @endif>Deactivate</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select" required>
                            <option value="product" @if(old('item_type', $item->item_type ?? 'product') === 'product') selected @endif>Product</option>
                            <option value="bundle" @if(old('item_type', $item->item_type ?? '') === 'bundle') selected @endif>Bundle</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Dealer Points</label>
                        <input type="number" name="dealer_points" class="form-control" value="{{ old('dealer_points', $item->dealer_points ?? 0) }}" min="0" step="1">
                    </div>

                    <div>
                        <label class="form-label">Customer Points</label>
                        <input type="number" name="customer_points" class="form-control" value="{{ old('customer_points', $item->customer_points ?? 0) }}" min="0" step="1">
                    </div>

                    <div class="item-form-span">
                        @if($showForAd ?? true)
                            <div class="form-check form-switch">
                                <input type="checkbox" name="for_ad" value="1" class="form-check-input" id="{{ $modalId }}ForAd" @if(old('for_ad', $item->for_ad ?? false)) checked @endif>
                                <label class="form-check-label fw-bold" for="{{ $modalId }}ForAd">For AD purchase orders</label>
                            </div>
                            <div class="text-muted small">Items marked for AD appear in the AD-only product section.</div>
                        @else
                            <input type="hidden" name="for_ad" value="0">
                            <div class="text-muted small">This product or bundle will be available in the price matrix.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bi bi-check2-circle"></i> {{ $isEdit ? 'Save Changes' : 'Create Item' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById(@json($modalId));
    if (!modal) {
        return;
    }

    const itemName = modal.querySelector('.js-item-name');
    const colorPanel = modal.querySelector('.js-stove-kit-colors');
    const colorInputs = modal.querySelectorAll('.js-stove-kit-color-input');

    function syncStoveKitColors() {
        const isStoveKit = itemName && itemName.value.toLowerCase().indexOf('gaz lite stove kit') !== -1;
        colorPanel.classList.toggle('d-none', !isStoveKit);
        colorInputs.forEach(function (input) {
            input.disabled = !isStoveKit;
        });
    }

    if (itemName && colorPanel) {
        itemName.addEventListener('input', syncStoveKitColors);
        syncStoveKitColors();
    }
});
</script>
