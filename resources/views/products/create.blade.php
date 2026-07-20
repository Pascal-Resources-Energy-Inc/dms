@extends('layouts.header')

@section('css')
<style>
    .pricing-page { width: min(100%, 1480px); margin: 0 auto; }
    .pricing-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
    .pricing-title { margin: 0; color: #111827; font-size: clamp(20px, 2.4vw, 24px); font-weight: 800; }
    .pricing-subtitle { margin: 4px 0 0; color: #64748b; font-size: 13px; }
    .pricing-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
    .pricing-search { width: 300px; max-width: 100%; min-height: 40px; }
    .summary-strip { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 10px; margin-bottom: 14px; }
    .summary-tile { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
    .summary-label { margin-bottom: 5px; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .summary-value { color: #0f172a; font-size: 18px; font-weight: 800; }
    .price-legend { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .legend-chip { display: inline-flex; align-items: center; gap: 7px; background: #fff; border: 1px solid #e5e7eb; border-radius: 999px; padding: 7px 11px; color: #475569; font-size: 12px; font-weight: 800; }
    .role-dot { width: 9px; height: 9px; border-radius: 999px; display: inline-block; }
    .role-dot.srp { background: #f59e0b; }
    .role-dot.mega { background: #0f766e; }
    .role-dot.dealer { background: #2563eb; }
    .role-dot.client { background: #dc2626; }
    .pricing-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; overscroll-behavior-inline: contain; scrollbar-color: #94a3b8 transparent; }
    .pricing-table { width: 100%; min-width: 1120px; margin: 0; border-collapse: separate; border-spacing: 0; }
    .pricing-table th { position: sticky; top: 0; z-index: 10; background: #f8fafc; border-bottom: 1px solid #e5e7eb; color: #475569; font-size: 11px; font-weight: 800; letter-spacing: .04em; padding: 12px; text-transform: uppercase; white-space: nowrap; }
    .pricing-table td { border-bottom: 1px solid #eef2f7; padding: 12px; vertical-align: middle; }
    .pricing-table tr:last-child td { border-bottom: 0; }
    .product-cell { display: flex; align-items: center; gap: 12px; min-width: 280px; }
    .product-thumb { width: 62px; height: 62px; display: flex; align-items: center; justify-content: center; flex: 0 0 62px; overflow: hidden; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; }
    .product-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .product-empty { color: #94a3b8; font-size: 11px; text-align: center; }
    .product-name { margin: 0; color: #111827; font-size: 14px; font-weight: 800; }
    .product-description { margin: 3px 0 0; color: #64748b; font-size: 12px; line-height: 1.35; }
    .role-label { display: inline-flex; align-items: center; gap: 6px; }
    .price-input { min-width: 145px; }
    .price-input .input-group-text { background: #f8fafc; border-color: #dbe3ee; color: #64748b; font-size: 12px; font-weight: 800; }
    .price-input input, .sku-input { border-color: #dbe3ee; font-size: 13px; }
    .srp-cell { background: #fffbeb; }
    .srp-cell .input-group-text { background: #fff7ed; color: #92400e; }
    .srp-cell input[readonly] { background: #fffaf0; color: #78350f; font-weight: 800; }
    .srp-note, .price-note { min-height: 16px; margin-top: 5px; font-size: 11px; }
    .srp-note { color: #92400e; font-weight: 700; }
    .price-note { color: #64748b; }
    .price-note.good { color: #047857; }
    .price-note.warn { color: #b45309; }
    .status-chip { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .status-chip.pending { background: #fff7ed; color: #c2410c; }
    .active-toggle { min-width: 90px; }
    .row-hidden { display: none; }
    .save-bar { position: sticky; bottom: 0; z-index: 20; display: flex; justify-content: flex-end; padding: 14px 0; background: linear-gradient(to top, #d8e9f0 70%, transparent); }
    @media (max-width: 1200px) {
        .pricing-header { align-items: flex-start; flex-direction: column; }
        .pricing-actions, .pricing-search { width: 100%; }
        .pricing-actions { justify-content: flex-start; }
    }
    @media (max-width: 992px) {
        .summary-strip { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
    }
    @media (max-width: 768px) {
        .pricing-header { gap: 12px; }
        .pricing-actions { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; }
        .pricing-search { width: 100%; }
        .pricing-actions .btn { min-height: 40px; white-space: nowrap; }
        .price-legend { gap: 6px; }
        .legend-chip { padding: 6px 9px; font-size: 11px; }
        .pricing-table-wrap { overflow: visible; border: 0; background: transparent; }
        .pricing-table, .pricing-table tbody, .pricing-table tr, .pricing-table td { display: block; width: 100%; min-width: 0; }
        .pricing-table thead { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .pricing-table tbody { display: grid; gap: 12px; }
        .pricing-table .product-row { overflow: hidden; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 14px rgba(15, 23, 42, .05); }
        .pricing-table .product-row.row-hidden { display: none; }
        .pricing-table td { padding: 11px 14px; border-bottom: 1px solid #eef2f7; }
        .pricing-table td:first-child { padding: 14px; background: #f8fafc; }
        .pricing-table td:last-child { border-bottom: 0; }
        .pricing-table td[data-label]::before { content: attr(data-label); display: block; margin-bottom: 7px; color: #64748b; font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .product-cell { min-width: 0; gap: 10px; }
        .product-thumb { width: 52px; height: 52px; flex-basis: 52px; }
        .product-name { font-size: 14px; }
        .product-description { display: -webkit-box; overflow: hidden; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .price-input { width: 100%; min-width: 0; }
        .sku-input { min-height: 40px; }
        .active-toggle { min-width: 0; }
        .save-bar { position: static; padding: 16px 0 6px; background: transparent; }
        .save-bar .btn { width: 100%; min-height: 44px; }
    }
    @media (max-width: 576px) {
        .summary-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .summary-tile { padding: 10px; }
        .pricing-actions { grid-template-columns: 1fr; }
        .pricing-actions .btn { width: 100%; }
    }
</style>
@endsection

@section('content')
@php
    $configuredCount = $productItems->filter(function ($product) {
        return $product->status === 'Activate';
    })->count();
@endphp

<form action="{{ route('products.storeBulk') }}" method="POST" class="pricing-page">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="pricing-header">
        <div>
            <h4 class="pricing-title">Product Price Matrix</h4>
            <p class="pricing-subtitle">Show the item SRP and manage selling prices for Mega Dealer, Dealer, and Client.</p>
        </div>
        <div class="pricing-actions">
            <input type="search" id="productSearch" class="form-control pricing-search" placeholder="Search product or SKU" aria-label="Search product or SKU">
            <button class="btn btn-success px-4" type="submit">
                <i class="bi bi-check2-circle"></i> Save
            </button>
        </div>
    </div>
    
    <div class="summary-strip">
        <div class="summary-tile">
            <div class="summary-label">Items</div>
            <div class="summary-value">{{ $items->count() }}</div>
        </div>
        <div class="summary-tile">
            <div class="summary-label">Active</div>
            <div class="summary-value" id="configuredCount">{{ $configuredCount }}</div>
        </div>
    </div>

    <div class="price-legend">
        <span class="legend-chip"><span class="role-dot srp"></span>SRP comes from the master item</span>
        <span class="legend-chip"><span class="role-dot mega"></span>Mega Dealer price</span>
        <span class="legend-chip"><span class="role-dot dealer"></span>Dealer price</span>
        <span class="legend-chip"><span class="role-dot client"></span>Client price</span>
    </div>

    <div class="pricing-table-wrap">
        <table class="pricing-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Active</th>
                    <th><span class="role-label"><span class="role-dot srp"></span>Cost</span></th>
                    <th><span class="role-label"><span class="role-dot mega"></span>Mega Dealer</span></th>
                    <th><span class="role-label"><span class="role-dot dealer"></span>Dealer</span></th>
                    <th><span class="role-label"><span class="role-dot client"></span>SRP Price</span></th>
                    <th>SKU</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    @php
                        $product = $productItems[$item->id] ?? null;
                        $srpPrice = $item->dprice ?? null;
                        $megaDealerPrice = $item->md_price ?? null;
                        $dealerPrice = $item->dealer_price ?? null;
                        $clientPrice = $item->price ?? null;
                        $rowDealerPrice = $dealerPrice ?? $srpPrice;
                        $rowClientPrice = $clientPrice ?? $srpPrice;
                        $sku = $product->sku ?? 'AD' . auth()->id() . '-ITEM' . $item->id;
                        $hasPrice = collect([$megaDealerPrice, $dealerPrice, $clientPrice])->filter(function ($price) {
                            return $price !== null && $price !== '' && (float) $price > 0;
                        })->isNotEmpty();
                        $isActive = $product ? $product->status === 'Activate' : false;
                    @endphp
                    <tr class="product-row">
                        <td data-label="Item">
                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                            <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item->item }}">
                            <input type="hidden" name="items[{{ $index }}][description]" value="{{ $item->item_description }}">
                            <input type="hidden" name="items[{{ $index }}][image]" value="{{ $item->item_image }}">
                            <input type="hidden" class="product-search-sku" name="items[{{ $index }}][sku]" value="{{ $sku }}">

                            <div class="product-cell">
                                <div class="product-thumb">
                                    @if($item->item_image && file_exists(public_path('uploads/products/' . $item->item_image)))
                                        <img src="{{ asset('uploads/products/'.$item->item_image) }}" alt="{{ $item->item }}">
                                    @else
                                        <span class="product-empty">No Image</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="product-name product-search-name">{{ $item->item }}</p>
                                    <p class="product-description">{{ $item->item_description }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Availability">
                            <div class="form-check form-switch active-toggle">
                                <input type="checkbox" class="form-check-input js-selected" id="itemSelected{{ $item->id }}" name="items[{{ $index }}][selected]" value="1" @if($isActive) checked @endif>
                                <label class="form-check-label" for="itemSelected{{ $item->id }}">Active</label>
                            </div>
                        </td>
                        <td class="srp-cell" data-label="Cost">
                            <div class="input-group price-input">
                                <span class="input-group-text">PHP</span>
                                <input type="number" class="form-control js-srp" name="items[{{ $index }}][dprice]" value="{{ $srpPrice }}" min="0" step="0.01" placeholder="0.00" readonly>
                            </div>
                            <div class="srp-note">Reference only</div>
                        </td>
                        <td data-label="Mega Dealer price">
                            <div class="input-group price-input">
                                <span class="input-group-text">PHP</span>
                                {{-- <input type="number" class="form-control js-role-price" name="items[{{ $index }}][mega_dealer_price]" value="{{ $megaDealerPrice }}" min="0" step="0.01" placeholder="0.00"> --}}
                                <input type="number" class="form-control js-role-price" name="items[{{ $index }}][mega_dealer_price]" value="{{ $megaDealerPrice }}" min="0" step="0.01" placeholder="0.00" readonly>
                            </div>
                            <div class="price-note"></div>
                        </td>
                        <td data-label="Dealer price">
                            <div class="input-group price-input">
                                <span class="input-group-text">PHP</span>
                                {{-- <input type="number" class="form-control js-role-price" name="items[{{ $index }}][dealer_price]" value="{{ $dealerPrice }}" min="0" step="0.01" placeholder="0.00"> --}}
                                <input type="number" class="form-control js-role-price" name="items[{{ $index }}][dealer_price]" value="{{ $rowDealerPrice }}" min="0" step="0.01" placeholder="0.00" readonly>
                            </div>
                            <div class="price-note"></div>
                        </td>
                        <td data-label="SRP price">
                            <div class="input-group price-input">
                                <span class="input-group-text">PHP</span>
                                {{-- <input type="number" class="form-control js-role-price js-client-price" name="items[{{ $index }}][client_price]" value="{{ $clientPrice }}" min="0" step="0.01" placeholder="0.00"> --}}
                                <input type="number" class="form-control js-role-price js-client-price" name="items[{{ $index }}][client_price]" value="{{ $rowClientPrice }}" min="0" step="0.01" placeholder="0.00" readonly>
                            </div>
                            <div class="price-note"></div>
                            <input type="hidden" name="items[{{ $index }}][price]" value="{{ $rowClientPrice }}">
                        </td>
                        <td data-label="SKU">
                            <input type="text" class="form-control sku-input product-search-sku" name="items[{{ $index }}][sku]" value="{{ $product->sku ?? '' }}" placeholder="SKU">
                            <div class="srp-note">Stock Keeping Unit</div>
                        </td>
                        <td class="status-cell" data-label="Status">
                            @if($isActive)
                                <span class="status-chip">Active</span>
                            @else
                                <span class="status-chip pending">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="save-bar">
        <button class="btn btn-success px-4" type="submit">
            <i class="bi bi-check2-circle"></i> Save
        </button>
    </div>
</form>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('productSearch');
    const rows = document.querySelectorAll('.product-row');

    function updateLegacyPrice(row) {
        const clientInput = row.querySelector('.js-client-price');
        const legacyInput = row.querySelector('input[name$="[price]"]');
        if (clientInput && legacyInput) {
            legacyInput.value = clientInput.value;
        }
    }

    function rowHasPrice(row) {
        return Array.from(row.querySelectorAll('.js-role-price')).some(function(input) {
            return parseFloat(input.value || 0) > 0;
        });
    }

    function rowIsSelected(row) {
        const selected = row.querySelector('.js-selected');
        return selected ? selected.checked : false;
    }

    function updateStatus(row) {
        const statusCell = row.querySelector('.status-cell');
        if (!statusCell) return;

        if (rowIsSelected(row)) {
            statusCell.innerHTML = '<span class="status-chip">Active</span>';
        } else {
            statusCell.innerHTML = '<span class="status-chip pending">Inactive</span>';
        }
    }

    function updateConfiguredCount() {
        const count = Array.from(rows).filter(rowIsSelected).length;
        const target = document.getElementById('configuredCount');
        if (target) {
            target.textContent = count;
        }
    }

    function updatePriceNotes(row) {
        const srp = parseFloat(row.querySelector('.js-srp').value || 0);

        row.querySelectorAll('.js-role-price').forEach(function(input) {
            const note = input.closest('td').querySelector('.price-note');
            const price = parseFloat(input.value || 0);

            note.classList.remove('good', 'warn');

            if (!srp || !price) {
                note.textContent = '';
                return;
            }

            const diff = srp - price;
            const percent = Math.abs((diff / srp) * 100).toFixed(1);

            if (diff >= 0) {
                note.textContent = percent + '% below SRP';
                note.classList.add('good');
            } else {
                note.textContent = percent + '% above SRP';
                note.classList.add('warn');
            }
        });

        updateLegacyPrice(row);
        updateStatus(row);
        updateConfiguredCount();
    }

    rows.forEach(function(row) {
        row.querySelectorAll('.js-srp, .js-role-price').forEach(function(input) {
            input.addEventListener('input', function() {
                updatePriceNotes(row);
            });
        });

        const selected = row.querySelector('.js-selected');
        if (selected) {
            selected.addEventListener('change', function() {
                updateStatus(row);
                updateConfiguredCount();
            });
        }

        updatePriceNotes(row);
    });

    if (search) {
        search.addEventListener('input', function() {
            const term = search.value.trim().toLowerCase();

            rows.forEach(function(row) {
                const name = row.querySelector('.product-search-name').textContent.toLowerCase();
                const skuInput = row.querySelector('.product-search-sku');
                const sku = skuInput ? skuInput.value.toLowerCase() : '';

                row.classList.toggle('row-hidden', term && !name.includes(term) && !sku.includes(term));
            });
        });
    }
});
</script>
@endsection
