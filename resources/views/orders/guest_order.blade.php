@extends('layouts.app')

@section('content')
<style>
    :root {
        --gl-red: #c1121f;
        --gl-green: #15803d;
        --gl-ink: #1f2937;
        --gl-muted: #667085;
        --gl-line: #e4e7ec;
        --gl-soft: #f8fafc;
    }

    body { background: #f3f6fa; }
    .guest-page { min-height: 100vh; color: var(--gl-ink); font-family: Inter, Nunito, system-ui, sans-serif; }
    .guest-nav { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px clamp(14px, 4vw, 42px); background: #fff; border-bottom: 1px solid var(--gl-line); }
    .guest-brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .guest-logo { width: 44px; height: 44px; object-fit: contain; border: 1px solid var(--gl-line); border-radius: 8px; }
    .guest-brand strong { display: block; font-size: 16px; line-height: 1.1; }
    .guest-brand span { color: var(--gl-muted); font-size: 12px; }
    .guest-login { display: inline-flex; align-items: center; gap: 7px; min-height: 38px; padding: 8px 12px; color: var(--gl-red); font-size: 12px; font-weight: 800; text-decoration: none; background: #fff7f7; border: 1px solid #fecaca; border-radius: 8px; }

    .guest-wrap { width: min(1180px, calc(100% - 28px)); margin: 0 auto; padding: 24px 0 42px; }
    .guest-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
    .guest-head h1 { margin: 0; font-size: clamp(26px, 4vw, 42px); font-weight: 900; letter-spacing: 0; line-height: 1.05; }
    .guest-head p { max-width: 560px; margin: 9px 0 0; color: var(--gl-muted); font-size: 14px; line-height: 1.55; }
    .guest-pill { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 10px; padding: 6px 10px; color: #991b1b; font-size: 11px; font-weight: 900; background: #fee2e2; border: 1px solid #fecaca; border-radius: 999px; }

    .guest-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
    .guest-panel { background: #fff; border: 1px solid var(--gl-line); border-radius: 8px; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .guest-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--gl-line); }
    .guest-panel-head h2 { margin: 0; font-size: 16px; font-weight: 900; }
    .guest-panel-head span { color: var(--gl-muted); font-size: 12px; }
    .guest-panel-body { padding: 18px; }

    .guest-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .guest-panel .form-control, .guest-panel .form-select { min-height: 42px; border-color: #d0d5dd; border-radius: 8px; }
    .guest-panel .form-control:focus, .guest-panel .form-select:focus { border-color: var(--gl-red); box-shadow: 0 0 0 3px rgba(193, 18, 31, .1); }
    .guest-readonly-field { min-height: 42px; display: flex; align-items: center; padding: 10px 14px; border: 1px solid #d0d5dd; border-radius: 8px; background: #f8fafc; color: #0f172a; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }

    .guest-payments { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .guest-radio { position: relative; display: flex; align-items: center; justify-content: center; min-height: 42px; padding: 8px; color: #475467; font-size: 12px; font-weight: 800; cursor: pointer; background: var(--gl-soft); border: 1px solid var(--gl-line); border-radius: 8px; }
    .guest-radio input { position: absolute; opacity: 0; }
    .guest-radio:has(input:checked) { color: #fff; background: var(--gl-red); border-color: var(--gl-red); }

    .product-search { position: relative; margin-bottom: 12px; }
    .product-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--gl-muted); font-size: 14px; pointer-events: none; }
    .product-search input { width: 100%; min-height: 42px; padding: 9px 42px 9px 38px; border: 1px solid #d0d5dd; border-radius: 8px; outline: 0; }
    .product-search input:focus { border-color: var(--gl-red); box-shadow: 0 0 0 3px rgba(193, 18, 31, .1); }
    .product-search-clear { position: absolute; right: 7px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; display: none; align-items: center; justify-content: center; color: var(--gl-muted); background: transparent; border: 0; border-radius: 8px; }
    .product-search-clear:hover { color: var(--gl-red); background: #fff1f2; }
    .product-list { display: grid; gap: 10px; }
    .product-row { display: grid; grid-template-columns: 74px minmax(0, 1fr) 112px; gap: 12px; align-items: center; padding: 12px; background: #fff; border: 1px solid var(--gl-line); border-radius: 8px; transition: border-color .16s ease, box-shadow .16s ease; }
    .product-row.is-hidden { display: none; }
    .product-row.is-selected { border-color: var(--gl-red); box-shadow: 0 10px 24px rgba(193, 18, 31, .08); }
    .product-img { width: 74px; height: 74px; object-fit: contain; background: var(--gl-soft); border: 1px solid #edf0f5; border-radius: 8px; }
    .product-name { margin-bottom: 4px; font-size: 14px; font-weight: 900; line-height: 1.25; }
    .product-desc { color: var(--gl-muted); font-size: 12px; line-height: 1.35; }
    .product-price { margin-top: 6px; color: var(--gl-red); font-size: 13px; font-weight: 900; }
    .product-qty label { display: block; margin-bottom: 5px; color: var(--gl-muted); font-size: 10px; font-weight: 900; text-align: center; text-transform: uppercase; }
    .product-qty input { width: 100%; min-height: 40px; text-align: center; border: 1px solid #d0d5dd; border-radius: 8px; }

    .summary-card { position: sticky; top: 86px; }
    .summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .summary-box { padding: 12px; background: var(--gl-soft); border: 1px solid var(--gl-line); border-radius: 8px; }
    .summary-box span { display: block; color: var(--gl-muted); font-size: 10px; font-weight: 900; text-transform: uppercase; }
    .summary-box strong { display: block; margin-top: 3px; font-size: 15px; }
    .cart-list { display: grid; gap: 8px; max-height: 260px; overflow-y: auto; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--gl-line); }
    .cart-empty { padding: 14px; color: var(--gl-muted); font-size: 12px; font-weight: 700; text-align: center; background: var(--gl-soft); border-radius: 8px; }
    .cart-line { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 10px; border: 1px solid #edf0f5; border-radius: 8px; }
    .cart-line strong { display: block; font-size: 12px; line-height: 1.25; }
    .cart-line small { color: var(--gl-muted); font-size: 11px; }
    .cart-line span { color: var(--gl-red); font-size: 12px; font-weight: 900; white-space: nowrap; }
    .guest-submit { width: 100%; min-height: 48px; margin-top: 14px; color: #fff; font-size: 14px; font-weight: 900; background: var(--gl-green); border: 0; border-radius: 8px; box-shadow: 0 12px 24px rgba(21, 128, 61, .2); }
    .guest-submit:disabled { cursor: not-allowed; opacity: .65; box-shadow: none; }
    .guest-empty { padding: 18px; text-align: center; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; }
    .product-search-empty { display: none; margin-top: 10px; padding: 14px; color: var(--gl-muted); font-size: 12px; font-weight: 700; text-align: center; background: var(--gl-soft); border: 1px dashed #d0d5dd; border-radius: 8px; }

    @media (max-width: 992px) {
        .guest-layout { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }

    @media (max-width: 640px) {
        .guest-wrap { width: min(100% - 20px, 1180px); padding-top: 16px; }
        .guest-brand span, .guest-login span, .guest-head aside { display: none; }
        .guest-head { display: block; }
        .product-row { grid-template-columns: 58px minmax(0, 1fr); }
        .product-img { width: 58px; height: 58px; }
        .product-qty { grid-column: 1 / -1; }
        .summary-grid, .guest-payments { grid-template-columns: 1fr; }
    }
</style>

<div class="guest-page">
    @php
        $isLoyaltyOrder = isset($prefillClient) && $prefillClient;
        $orderPageLabel = $isLoyaltyOrder ? 'Order' : 'Guest Order';
    @endphp
    <nav class="guest-nav">
        <div class="guest-brand">
            <img src="{{ asset('images/logo_nya.png') }}" class="guest-logo" alt="Gaz Lite">
            <div>
                <strong>Gaz Lite {{ $orderPageLabel }}</strong>
                <span>Order multiple products in one request</span>
            </div>
        </div>
        <a href="{{ route('login') }}" class="guest-login">
            <i class="bi bi-person-lock"></i>
            <span>Staff Login</span>
        </a>
    </nav>

    <main class="guest-wrap">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(isset($prefillClient) && $prefillClient)
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-person-check-fill"></i>
                <span>Loyalty card verified for <strong>{{ $prefillClient->name }}</strong>. Continue the order below.</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                Please check the highlighted fields and try again.
            </div>
        @endif

        <form action="{{ route('guest-order.store') }}" method="POST" id="guestOrderForm">
            @csrf
            @if($isLoyaltyOrder)
                <input type="hidden" name="loyalty_client_id" value="{{ $prefillClient->id }}">
            @endif

            <div class="guest-head">
                <div>
                    <div class="guest-pill"><i class="bi bi-bag-check-fill"></i> No account required</div>
                    <h1>{{ $orderPageLabel }}</h1>
                    <p>Enter customer details, add quantities beside each product, then submit one order request with one reference number.</p>
                </div>
                <aside class="text-end">
                    <div class="text-muted small fw-bold">Available Products</div>
                    <div class="fs-4 fw-black fw-bold">{{ $products->count() }}</div>
                </aside>
            </div>

            <div class="guest-layout">
                <div class="d-grid gap-3">
                    <section class="guest-panel">
                        <div class="guest-panel-head">
                            <div>
                                <h2>Customer Details</h2>
                                <span>Customer contact information</span>
                            </div>
                            <i class="bi bi-person-lines-fill text-danger fs-4"></i>
                        </div>
                        <div class="guest-panel-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="guest-label" for="guestName">Full Name</label>
                                    <input type="text" name="guest_name" id="guestName" class="form-control {{ $errors->has('guest_name') ? 'is-invalid' : '' }}" value="{{ old('guest_name', optional($prefillClient)->name) }}">
                                    @if($errors->has('guest_name'))<div class="invalid-feedback">{{ $errors->first('guest_name') }}</div>@endif
                                </div>
                                <div class="col-md-6">
                                    <label class="guest-label" for="guestPhone">Phone Number</label>
                                    <input type="text" name="guest_phone" id="guestPhone" class="form-control {{ $errors->has('guest_phone') ? 'is-invalid' : '' }}" value="{{ old('guest_phone', optional($prefillClient)->number) }}">
                                    @if($errors->has('guest_phone'))<div class="invalid-feedback">{{ $errors->first('guest_phone') }}</div>@endif
                                </div>
                                <div class="col-md-6">
                                    <label class="guest-label" for="guestEmail">Email Address</label>
                                    <input type="email" name="guest_email" id="guestEmail" class="form-control {{ $errors->has('guest_email') ? 'is-invalid' : '' }}" value="{{ old('guest_email', optional($prefillClient)->email_address) }}">
                                    @if($errors->has('guest_email'))<div class="invalid-feedback">{{ $errors->first('guest_email') }}</div>@endif
                                </div>
                                <div class="col-md-6">
                                    <label class="guest-label">Payment Method</label>
                                    <div class="guest-payments">
                                        <label class="guest-radio"><input type="radio" name="payment_method" value="cash" @if(old('payment_method', 'cash') === 'cash') checked @endif>Cash</label>
                                        <label class="guest-radio"><input type="radio" name="payment_method" value="gcash" @if(old('payment_method') === 'gcash') checked @endif>GCash</label>
                                        <label class="guest-radio"><input type="radio" name="payment_method" value="bank_transfer" @if(old('payment_method') === 'bank_transfer') checked @endif>Bank</label>
                                    </div>
                                    @if($errors->has('payment_method'))<div class="text-danger small mt-1">{{ $errors->first('payment_method') }}</div>@endif
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $defaultTerritory = old('guest_authorized_territory', $authorizedTerritories->count() === 1 ? $authorizedTerritories->first() : '');
                                        $hasSingleTerritory = $authorizedTerritories->count() === 1;
                                    @endphp
                                    <label class="guest-label" for="guestAuthorizedTerritory">Authorized Territory</label>
                                    @if($hasSingleTerritory)
                                        <div class="guest-readonly-field{{ $errors->has('guest_authorized_territory') ? ' is-invalid' : '' }}">{{ strtoupper($defaultTerritory) }}</div>
                                        <input type="hidden" name="guest_authorized_territory" value="{{ $defaultTerritory }}">
                                        <div class="form-text text-muted">Your assigned territory has been pre-filled.</div>
                                        @if($errors->has('guest_authorized_territory'))<div class="invalid-feedback d-block">{{ $errors->first('guest_authorized_territory') }}</div>@endif
                                    @else
                                        <select name="guest_authorized_territory" id="guestAuthorizedTerritory" class="form-select {{ $errors->has('guest_authorized_territory') ? 'is-invalid' : '' }}">
                                            <option value="">Select territory</option>
                                            @if($defaultTerritory && !$authorizedTerritories->contains($defaultTerritory))
                                                <option value="{{ $defaultTerritory }}" selected>{{ strtoupper($defaultTerritory) }}</option>
                                            @endif
                                            @foreach($authorizedTerritories as $territory)
                                                <option value="{{ $territory }}" @if($defaultTerritory === $territory) selected @endif>{{ strtoupper($territory) }}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('guest_authorized_territory'))<div class="invalid-feedback">{{ $errors->first('guest_authorized_territory') }}</div>@endif
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="guest-label" for="guestNotes">Notes</label>
                                    <textarea name="guest_notes" id="guestNotes" class="form-control {{ $errors->has('guest_notes') ? 'is-invalid' : '' }}" rows="2" placeholder="Landmark, preferred time, or other request">{{ old('guest_notes') }}</textarea>
                                    @if($errors->has('guest_notes'))<div class="invalid-feedback">{{ $errors->first('guest_notes') }}</div>@endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="guest-panel">
                        <div class="guest-panel-head">
                            <div>
                                <h2>Products</h2>
                                <span>Set quantity to add product</span>
                            </div>
                            <i class="bi bi-grid-3x3-gap-fill text-danger fs-4"></i>
                        </div>
                        <div class="guest-panel-body">
                            @if($products->isEmpty())
                                <div class="guest-empty">
                                    <strong>No active products available.</strong>
                                    <div class="text-muted small mt-1">Please check again later.</div>
                                </div>
                            @else
                                <div class="product-search">
                                    <i class="bi bi-search"></i>
                                    <input type="search" id="productSearch" placeholder="Search products..." autocomplete="off">
                                    <button type="button" class="product-search-clear" id="productSearchClear" aria-label="Clear product search">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="product-list">
                                    @foreach($products as $product)
                                        @php
                                            $price = $product->client_price ?? $product->srp_price ?? $product->price ?? 0;
                                            $oldQty = old('products.' . $product->id . '.qty', 0);
                                            $image = $product->product_image && file_exists(public_path('uploads/products/' . $product->product_image))
                                                ? asset('uploads/products/' . $product->product_image)
                                                : asset('design/assets/images/products/empty-shopping-bag.gif');
                                        @endphp
                                        <div class="product-row" data-product-row data-search-text="{{ strtolower($product->product_name . ' ' . $product->description) }}">
                                            <img src="{{ $image }}" class="product-img" alt="{{ $product->product_name }}">
                                            <div>
                                                <div class="product-name">{{ $product->product_name }}</div>
                                                <div class="product-desc">{{ \Illuminate\Support\Str::limit($product->description, 70) }}</div>
                                                <div class="product-price">PHP {{ number_format($price, 2) }}</div>
                                            </div>
                                            <div class="product-qty">
                                                <label for="productQty{{ $product->id }}">Qty</label>
                                                <input type="number"
                                                    id="productQty{{ $product->id }}"
                                                    name="products[{{ $product->id }}][qty]"
                                                    value="{{ $oldQty }}"
                                                    min="0"
                                                    step="1"
                                                    data-product-qty
                                                    data-name="{{ $product->product_name }}"
                                                    data-price="{{ $price }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="product-search-empty" id="productSearchEmpty">
                                    No products match your search.
                                </div>
                                @if($errors->has('products'))<div class="text-danger small mt-2">{{ $errors->first('products') }}</div>@endif
                            @endif
                        </div>
                    </section>
                </div>

                <aside class="guest-panel summary-card">
                    <div class="guest-panel-head">
                        <div>
                            <h2>Summary</h2>
                            <span id="summaryQty">0 pcs</span>
                        </div>
                        <i class="bi bi-receipt-cutoff text-danger fs-4"></i>
                    </div>
                    <div class="guest-panel-body">
                        <div class="summary-grid">
                            <div class="summary-box"><span>Items</span><strong id="summaryItems">0</strong></div>
                            <div class="summary-box"><span>Total</span><strong id="summaryTotal">PHP 0.00</strong></div>
                            <div class="summary-box"><span>Subtotal</span><strong id="summarySubtotal">PHP 0.00</strong></div>
                        </div>

                        <div class="cart-list" id="summaryCart">
                            <div class="cart-empty">Enter quantity beside a product.</div>
                        </div>

                        <button type="submit" class="guest-submit" id="guestSubmit" disabled>
                            <i class="bi bi-send-fill me-1"></i> Submit Order
                        </button>
                    </div>
                </aside>
            </div>
        </form>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('guestOrderForm');
    const submitButton = document.getElementById('guestSubmit');
    const summaryItems = document.getElementById('summaryItems');
    const summaryQty = document.getElementById('summaryQty');
    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryCart = document.getElementById('summaryCart');
    const productSearch = document.getElementById('productSearch');
    const productSearchClear = document.getElementById('productSearchClear');
    const productSearchEmpty = document.getElementById('productSearchEmpty');
    const productRows = document.querySelectorAll('[data-product-row]');

    function money(value) {
        return 'PHP ' + Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function calculate() {
        let subtotal = 0;
        let itemCount = 0;
        let totalQty = 0;
        let rows = '';

        productRows.forEach(function(row) {
            const qtyInput = row.querySelector('[data-product-qty]');
            let qty = parseInt(qtyInput.value || 0, 10);
            const price = parseFloat(qtyInput.dataset.price || 0);

            if (qty < 0 || Number.isNaN(qty)) {
                qty = 0;
            }

            qtyInput.value = qty;
            row.classList.toggle('is-selected', qty > 0);

            if (qty <= 0) {
                return;
            }

            const lineTotal = price * qty;
            subtotal += lineTotal;
            itemCount += 1;
            totalQty += qty;

            rows += '<div class="cart-line"><div><strong>' + escapeHtml(qtyInput.dataset.name) + '</strong><small>' + qty + ' x ' + money(price) + '</small></div><span>' + money(lineTotal) + '</span></div>';
        });

        summaryItems.textContent = itemCount;
        summaryQty.textContent = totalQty + (totalQty === 1 ? ' pc' : ' pcs');
        summarySubtotal.textContent = money(subtotal);
        summaryTotal.textContent = money(subtotal);
        summaryCart.innerHTML = rows || '<div class="cart-empty">Enter quantity beside a product.</div>';
        submitButton.disabled = itemCount === 0;
    }

    function filterProducts() {
        if (!productSearch) {
            return;
        }

        const query = productSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        productRows.forEach(function(row) {
            const isMatch = !query || (row.dataset.searchText || '').indexOf(query) !== -1;
            row.classList.toggle('is-hidden', !isMatch);

            if (isMatch) {
                visibleCount += 1;
            }
        });

        if (productSearchClear) {
            productSearchClear.style.display = query ? 'inline-flex' : 'none';
        }

        if (productSearchEmpty) {
            productSearchEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    productRows.forEach(function(row) {
        row.querySelector('[data-product-qty]').addEventListener('input', calculate);
        row.querySelector('[data-product-qty]').addEventListener('change', calculate);
    });

    if (productSearch) {
        productSearch.addEventListener('input', filterProducts);
    }

    if (productSearchClear) {
        productSearchClear.addEventListener('click', function() {
            productSearch.value = '';
            productSearch.focus();
            filterProducts();
        });
    }

    form.addEventListener('submit', function(event) {
        if (submitButton.disabled) {
            event.preventDefault();
            summaryCart.innerHTML = '<div class="cart-empty text-danger">Please add quantity to at least one product.</div>';
        }
    });

    calculate();
    filterProducts();
});
</script>
@endsection
