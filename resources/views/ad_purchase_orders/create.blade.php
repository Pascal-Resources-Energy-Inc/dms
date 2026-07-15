@extends('layouts.header')

@section('css')
<style>
    .adpo-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .adpo-sheet { background: #fff; border: 1px solid #e7eaf0; border-radius: 8px; overflow: hidden; box-shadow: 0 14px 36px rgba(15, 23, 42, .06); }
    .adpo-brand { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 24px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .adpo-logo { height: 56px; width: auto; object-fit: contain; }
    .adpo-title { margin: 10px 0 2px; color: #101828; font-size: 24px; font-weight: 800; }
    .adpo-subtitle { margin: 0; color: #667085; font-size: 13px; }
    .adpo-badge { display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 999px; background: #fff1f2; color: #be123c; font-size: 12px; font-weight: 800; }
    .adpo-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; align-items: start; }
    .adpo-main { min-width: 0; border-right: 1px solid #edf0f5; }
    .adpo-side { grid-column: 2; width: 100%; position: sticky; top: 84px; align-self: start; padding: 20px; }
    .adpo-section { padding: 22px 24px; border-bottom: 1px solid #edf0f5; }
    .adpo-section:last-child { border-bottom: 0; }
    .adpo-section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 16px; }
    .adpo-section-title { margin: 0; color: #101828; font-size: 15px; font-weight: 800; }
    .adpo-section-copy { margin: 3px 0 0; color: #667085; font-size: 12px; line-height: 1.45; }
    .adpo-alert { display: flex; gap: 10px; align-items: flex-start; border: 1px solid #fed7aa; border-radius: 8px; padding: 12px 14px; background: #fff7ed; color: #9a3412; font-size: 13px; }
    .adpo-info-panel { border: 1px solid #e7eaf0; border-radius: 8px; padding: 16px; background: linear-gradient(180deg, #fcfcfd 0%, #fff 100%); }
    .adpo-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .adpo-field-span { grid-column: 1 / -1; }
    .adpo-info-panel .form-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .territory-select-wrap { position: relative; }
    .territory-select-wrap i { position: absolute; left: 12px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; z-index: 1; }
    .territory-select-wrap .form-select { padding-left: 36px; min-height: 42px; font-weight: 700; color: #101828; }
    .adpo-option { position: relative; display: flex; gap: 10px; padding: 13px; border: 1px solid #d9dee8; border-radius: 8px; background: #fff; cursor: pointer; transition: .16s ease; }
    .adpo-option:hover { border-color: #b8c0cc; background: #fafbfc; }
    .adpo-option:has(input:checked) { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, .10); }
    .adpo-option input { margin-top: 4px; flex: 0 0 auto; }
    .adpo-option strong { display: block; color: #111827; font-size: 13px; line-height: 1.3; }
    .adpo-option small { color: #667085; line-height: 1.35; }
    .adpo-bank-panel { margin-top: 12px; padding: 14px; background: linear-gradient(135deg, #f8fafc, #fff); border: 1px solid #dfe4ea; border-radius: 10px; }
    .adpo-bank-panel-head { display: flex; align-items: center; gap: 9px; margin-bottom: 10px; color: #344054; font-size: 12px; font-weight: 800; }
    .adpo-bank-panel-head i { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; color: #1d4ed8; background: #dbeafe; border-radius: 8px; }
    .adpo-product-toolbar { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .adpo-product-tools { display: grid; grid-template-columns: auto minmax(220px, 1fr) 170px; gap: 8px; max-width: 680px; width: 100%; }
    .favorite-filter-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 34px; padding: 0 11px; border: 1px solid #d9dee8; border-radius: 999px; background: #fff; color: #475467; font-size: 12px; font-weight: 800; white-space: nowrap; }
    .favorite-filter-btn:hover { border-color: #cbd5e1; background: #f8fafc; color: #101828; }
    .favorite-filter-btn.is-active { border-color: #dc2626; background: #fef2f2; color: #b91c1c; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, .08); }
    .favorite-filter-btn .count { min-width: 22px; padding: 2px 7px; border-radius: 999px; background: #eef2f7; color: #475467; font-size: 11px; }
    .favorite-filter-btn.is-active .count { background: #fee2e2; color: #991b1b; }
    .product-search { position: relative; }
    .product-search i { position: absolute; left: 11px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; }
    .product-search .form-control { padding-left: 34px; }
    .product-grid { display: block; }
    .product-list { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .product-list + .ad-product-collapse { margin-top: 16px; }
    .ad-product-collapse { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #fff; }
    .ad-product-toggle { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border: 0; background: #f8fafc; color: #101828; text-align: left; }
    .ad-product-toggle strong { display: block; font-size: 14px; }
    .ad-product-toggle small { display: block; color: #667085; font-size: 12px; margin-top: 2px; }
    .ad-product-toggle i { transition: transform .16s ease; }
    .ad-product-toggle[aria-expanded="true"] i { transform: rotate(180deg); }
    .ad-product-body { padding: 16px; border-top: 1px solid #eef2f7; }
    .product-card { position: relative; display: flex; flex-direction: column; min-height: 340px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; background: #fff; transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease; }
    .product-card:hover { border-color: #cbd5e1; box-shadow: 0 12px 26px rgba(15, 23, 42, .07); transform: translateY(-1px); }
    .product-card.is-selected { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, .10), 0 12px 26px rgba(15, 23, 42, .07); }
    .product-card.is-hidden { display: none; }
    .product-favorite-btn { position: absolute; top: 15px; left: 15px; z-index: 3; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 999px; background: rgba(255, 255, 255, .94); color: #98a2b3; box-shadow: 0 6px 14px rgba(15, 23, 42, .08); transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease; }
    .product-favorite-btn:hover { border-color: #fecaca; background: #fff; color: #dc2626; transform: translateY(-1px); }
    .product-favorite-btn.is-favorite { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
    .product-favorite-btn.is-saving { opacity: .65; pointer-events: none; }
    .product-card.is-favorite { border-color: #fecaca; }
    .product-card.is-favorite::after { content: "Favorite"; position: absolute; top: 55px; left: 12px; z-index: 2; padding: 4px 8px; border-radius: 999px; background: #fff1f2; color: #be123c; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .product-check { position: absolute; top: 19px; left: 60px; z-index: 2; width: 20px; height: 20px; cursor: pointer; }
    .product-selected-badge { position: absolute; top: 12px; right: 12px; z-index: 2; display: none; align-items: center; gap: 5px; padding: 5px 9px; border-radius: 999px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; }
    .product-card.is-selected .product-selected-badge { display: inline-flex; }
    .product-image { position: relative; height: 150px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; background: #f8fafc; border: 1px solid #eef2f7; border-radius: 8px; overflow: hidden; }
    .product-image img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform .16s ease; }
    .product-image.is-bundle { padding: 10px; background: linear-gradient(135deg, #f8fafc 0%, #ecfdf5 100%); border-color: #bbf7d0; }
    .product-image.is-bundle::before { content: ""; position: absolute; inset: 12px; border-radius: 8px; background: repeating-linear-gradient(135deg, rgba(22, 163, 74, .08) 0 8px, rgba(255, 255, 255, .42) 8px 16px); pointer-events: none; }
    .bundle-store-image-frame { position: relative; z-index: 1; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 8px; overflow: hidden; background: #fff; box-shadow: 0 8px 18px rgba(15, 23, 42, .10); }
    .bundle-store-image-frame img { width: 100%; height: 100%; max-width: none; max-height: none; object-fit: contain; display: block; }
    .bundle-store-badge { position: absolute; right: 12px; bottom: 12px; z-index: 2; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 999px; background: #15803d; color: #fff; font-size: 15px; box-shadow: 0 6px 14px rgba(21, 128, 61, .25); }
    .product-card:hover .product-image img { transform: scale(1.03); }
    .product-body { display: flex; flex: 1; flex-direction: column; }
    .product-name { margin: 0; color: #101828; font-size: 14px; font-weight: 800; line-height: 1.35; }
    .product-desc { margin: 6px 0 12px; color: #667085; font-size: 12px; line-height: 1.45; }
    .product-meta { display: flex; align-items: end; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f3f6; }
    .product-price-label { display: block; margin-bottom: 2px; color: #98a2b3; font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .product-price { color: #0f766e; font-size: 15px; font-weight: 800; white-space: nowrap; }
    .qty-group { min-width: 92px; }
    .qty-control { max-width: 96px; min-height: 34px; text-align: center; font-weight: 800; }
    .color-variant-panel { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f3f6; }
    .size-variant-panel { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f3f6; }
    .product-card:not(.is-selected) .color-variant-panel,
    .product-card:not(.is-selected) .size-variant-panel { display: none; }
    .color-variant-title { margin: 0 0 8px; color: #344054; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .size-variant-title { margin: 0 0 8px; color: #344054; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .color-variant-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .size-variant-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .color-variant { display: grid; grid-template-columns: 18px minmax(0, 1fr) 58px; align-items: center; gap: 7px; padding: 8px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fcfcfd; }
    .size-variant { display: grid; grid-template-columns: minmax(0, 1fr) 58px; align-items: center; gap: 7px; padding: 8px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fcfcfd; }
    .size-variant.is-other { grid-column: 1 / -1; grid-template-columns: minmax(90px, 1fr) 58px; }
    .size-other-field { grid-column: 1 / -1; }
    .color-dot { width: 14px; height: 14px; border-radius: 50%; border: 1px solid #cbd5e1; }
    .color-dot.white { background: #fff; }
    .color-dot.choco { background: #6f4e37; }
    .color-dot.blue { background: #2563eb; }
    .color-dot.green { background: #16a34a; }
    .color-dot.yellow { background: #f59e0b; }
    .color-dot.red { background: #ef4444; }
    .color-variant label { margin: 0; color: #475467; font-size: 12px; font-weight: 700; }
    .size-variant label { margin: 0; color: #475467; font-size: 12px; font-weight: 700; }
    .color-qty { min-height: 30px; padding: 4px 6px; text-align: center; font-weight: 800; }
    .size-qty { min-height: 30px; padding: 4px 6px; text-align: center; font-weight: 800; }
    .product-empty-search { display: none; grid-column: 1 / -1; margin: 0; padding: 24px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; color: #667085; text-align: center; }
    .product-empty-search.is-visible { display: block; }
    .summary-card { width: 100%; border: 1px solid #e6e9ef; border-radius: 8px; padding: 16px; background: #fff; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .summary-title { margin: 0 0 14px; color: #101828; font-size: 16px; font-weight: 800; }
    .summary-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 9px 0; color: #475467; font-size: 13px; border-bottom: 1px solid #f1f3f6; }
    .summary-row strong { color: #101828; }
    .summary-total { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 14px; color: #101828; font-size: 18px; font-weight: 800; }
    .adpo-help { margin-top: 12px; color: #667085; font-size: 12px; line-height: 1.55; }
    .empty-products { grid-column: 1 / -1; margin: 0; }
    .adpo-loading { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(255, 255, 255, .82); backdrop-filter: blur(2px); }
    .adpo-loading.is-visible { display: flex; }
    .adpo-loading-box { width: min(100%, 340px); padding: 24px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; box-shadow: 0 18px 48px rgba(15, 23, 42, .14); text-align: center; }
    .adpo-loading-spinner { width: 42px; height: 42px; margin: 0 auto 14px; border: 4px solid #fee2e2; border-top-color: #dc2626; border-radius: 50%; animation: adpoSpin .8s linear infinite; }
    .adpo-loading-title { margin: 0; color: #101828; font-size: 16px; font-weight: 800; }
    .adpo-loading-copy { margin: 6px 0 0; color: #667085; font-size: 13px; line-height: 1.45; }
    @keyframes adpoSpin { to { transform: rotate(360deg); } }
    @media (max-width: 1200px) {
        .adpo-layout { grid-template-columns: 1fr; }
        .adpo-main { border-right: 0; }
        .adpo-side { grid-column: 1; position: static; border-top: 1px solid #edf0f5; }
        .product-list { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .adpo-info-grid { grid-template-columns: 1fr; }
        .product-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .adpo-brand, .adpo-product-toolbar, .adpo-topbar, .adpo-section-head { align-items: stretch; flex-direction: column; }
        .adpo-product-tools { grid-template-columns: 1fr; max-width: none; }
    }
    @media (max-width: 480px) { .product-list { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@php
    $territoryOptions = $ad && $ad->areas
        ? $ad->areas->map(function ($area) {
            return [
                'value' => $area->area_name,
                'label' => trim(($area->project_type ? $area->project_type . ': ' : '') . $area->area_name),
            ];
        })->filter(fn($area) => $area['value'])->unique('value')->values()
        : collect();
    $selectedTerritory = old(
        'authorized_territory',
        $territoryOptions->count() === 1 ? $territoryOptions->first()['value'] : ''
    );
    $stoveKitColors = \App\Item::STOVE_KIT_COLORS;
    $uniformSizes = [
        'extra_small' => 'Extra Small',
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'extra_large' => 'Extra Large',
    ];
    $favoriteProductIds = collect($favoriteProductIds ?? [])->map(fn($id) => (int) $id)->values();
    $favoriteProductCount = $favoriteProductIds->count();
    $userVouchers = collect($userVouchers ?? []);
    $territoryVouchers = $selectedTerritory === ''
        ? collect()
        : $userVouchers->filter(fn($voucher) => $voucher->hasArea($selectedTerritory))->values();
    $defaultVoucherCode = old('voucher_code', $territoryVouchers->count() === 1 ? $territoryVouchers->first()->code : '');
    $useRebateVoucher = old('use_rebate_voucher', $territoryVouchers->isNotEmpty() ? 'yes' : 'no');
    $showPickupLubao = $showPickupLubao ?? true;
    $selectedShippingType = old('shipping_type', 'delivered');

    if ($selectedShippingType === 'pickup') {
        $selectedShippingType = $showPickupLubao ? 'pickup_lubao' : 'pickup_guinobatan';
    }

    if ($selectedShippingType === 'pickup_lubao' && !$showPickupLubao) {
        $selectedShippingType = 'delivered';
    }
@endphp

<form action="{{ route('ad-purchase-orders.store') }}" method="POST" id="adpoForm">
    @csrf
    <div class="adpo-topbar">
        <a href="{{ route('ad-purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <button type="submit" class="btn btn-danger px-4"><i class="bi bi-send"></i>&nbsp;Submit Purchase Order</button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="adpo-sheet">
        <div class="adpo-brand">
            <div>
                {{-- <img src="{{ asset('images/logo_mo.png') }}" class="adpo-logo" alt="Gaz Lite"> --}}
                <h4 class="adpo-title">Distributor Purchase Order</h4>
                <p class="adpo-subtitle">Build your ADPO from available active products.</p>
            </div>
            <div class="adpo-badge">
                <i class="bi bi-clipboard-check"></i> DPO
            </div>
        </div>

        <div class="adpo-layout">
            <div class="adpo-main">
                <div class="adpo-section">
                    {{-- <div class="adpo-alert mb-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>Available stock is limited. Please review product quantities carefully before submitting.</div>
                    </div> --}}
                    <div class="adpo-section-head">
                        <div>
                            <h6 class="adpo-section-title">Submitter Information</h6>
                            <p class="adpo-section-copy">These details will appear on the purchase order record.</p>
                        </div>
                    </div>
                    <div class="adpo-info-panel">
                        <div class="adpo-info-grid">
                            <div>
                                <label class="form-label">Business Name</label>
                                <input class="form-control" value="{{ old('business_name', optional($ad)->business_name ?: optional($ad)->name ?: auth()->user()->name) }}" readonly>
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email_address" class="form-control" value="{{ old('email_address', optional($ad)->email_address ?: auth()->user()->email) }}" placeholder="name@example.com" readonly>
                            </div>
                            <div>
                                <label class="form-label">Phone Number</label>
                                {{-- <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', optional($ad)->contact_number) }}" placeholder="09XX XXX XXXX"> --}}
                                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="09XX XXX XXXX" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="{{ old('phone_number', optional($ad)->contact_number) }}">
                            </div>
                            <div>
                                <label class="form-label">Business Type</label>
                                <input type="text" name="business_type" class="form-control" value="{{ old('business_type', optional($ad)->business_type) }}" placeholder="Example: Retailer" readonly>
                            </div>
                            {{-- <div class="col-md-6">
                                <label class="form-label">Uniform Shirt Size</label>
                                <input type="text" name="uniform_size" class="form-control" value="{{ old('uniform_size') }}" placeholder="Example: Large">
                            </div> --}}
                            <div class="adpo-field-span">
                                <label class="form-label">Delivery Address</label>
                                <input class="form-control" name="delivery_address" value="{{ old('delivery_address', optional($ad)->delivery_address) }}" data-uppercase readonly>
                            </div>
                            <div class="adpo-field-span">
                                <label class="form-label">Authorized Territory <span class="text-danger">*</span></label>
                                <div class="territory-select-wrap">
                                    <i class="bi bi-geo-alt"></i>
                                    <select name="authorized_territory" class="form-select" {{ $territoryOptions->isEmpty() ? 'disabled' : 'required' }}>
                                        @if($territoryOptions->isEmpty())
                                            <option value="">No territory assigned</option>
                                        @else
                                            @if($territoryOptions->count() > 1)
                                                <option value="">Select authorized territory</option>
                                            @endif
                                            @foreach($territoryOptions as $territory)
                                                <option value="{{ $territory['value'] }}" {{ $selectedTerritory === $territory['value'] ? 'selected' : '' }}>
                                                    {{ $territory['label'] }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <small class="text-muted d-block mt-1">The selected territory will be recorded on this purchase order.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adpo-section">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="adpo-section-head">
                                <div>
                                    <h6 class="adpo-section-title">Shipping</h6>
                                    <p class="adpo-section-copy">Choose how this purchase order will be fulfilled.</p>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="adpo-option">
                                        <input type="radio" name="shipping_type" value="delivered" required @if($selectedShippingType === 'delivered') checked @endif>
                                        <span><strong>Delivered</strong><small>Ship to authorized area</small></span>
                                    </label>
                                </div>
                                @if($showPickupLubao)
                                    <div class="col-sm-6">
                                        <label class="adpo-option">
                                            <input type="radio" name="shipping_type" value="pickup_lubao" required @if($selectedShippingType === 'pickup_lubao') checked @endif>
                                            <span><strong>Pick Up</strong><small>Pick Up in Lubao Plant - Type &quot;PICKUP&quot; in coupon for 2% discount on LPG Refill</small></span>
                                        </label>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <label class="adpo-option">
                                        <input type="radio" name="shipping_type" value="pickup_guinobatan" required @if($selectedShippingType === 'pickup_guinobatan') checked @endif>
                                        <span><strong>Pick Up</strong><small>Pick Up in Guinobatan Warehouse</small></span>
                                    </label>
                                </div>
                            </div>
                            {{-- <label class="form-label mt-3">Delivery Fee</label>
                            <input type="number" name="delivery_fee" id="deliveryFee" class="form-control" min="0" step="0.01" value="{{ old('delivery_fee', 0) }}"> --}}
                        </div>
                        <div class="col-lg-6">
                            <div class="adpo-section-head">
                                <div>
                                    <h6 class="adpo-section-title">I would like to utilize my rebate voucher for this transaction</h6>
                                    <p class="adpo-section-copy">Choose Yes to enter the voucher code for this order.</p>
                                </div>
                            </div>
                            {{-- <input type="hidden" name="payment_method" id="paymentMethod" value="{{ $useRebateVoucher === 'yes' ? 'voucher' : 'cash' }}"> --}}
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="adpo-option">
                                        <input type="radio" name="use_rebate_voucher" value="yes" required @if($useRebateVoucher === 'yes') checked @endif>
                                        <span><strong>Yes</strong><small>I will use a rebate voucher.</small></span>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="adpo-option">
                                        <input type="radio" name="use_rebate_voucher" value="no" required @if($useRebateVoucher === 'no') checked @endif>
                                        <span><strong>No</strong><small>Continue without a voucher.</small></span>
                                    </label>
                                </div>
                            </div>
                            <div id="voucherCodeWrap" class="mt-3 {{ $useRebateVoucher === 'yes' ? '' : 'd-none' }}">
                                <label class="form-label">Voucher Code</label>
                                <select name="voucher_code" id="voucherCode" class="form-select" data-default-voucher-code="{{ $defaultVoucherCode }}" @if($useRebateVoucher === 'yes') required @else disabled @endif>
                                    <option value="">Select voucher code</option>
                                    @foreach($userVouchers as $voucher)
                                        <option value="{{ $voucher->code }}" @if($defaultVoucherCode === $voucher->code) selected @endif>
                                            {{ $voucher->code }} - {{ $voucher->discount_type === 'percent' ? number_format($voucher->discount_value, 2) . '%' : 'PHP ' . number_format($voucher->discount_value, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="rebate_amount" id="rebateAmount" value="{{ $useRebateVoucher === 'yes' ? old('rebate_amount', 0) : 0 }}">
                                <small class="text-muted d-block mt-1" id="voucherFeedback">Select an authorized territory to see its active vouchers.</small>
                            </div>
                            {{-- <div class="row g-2">
                                @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer', 'credit' => 'Credit'] as $value => $label)
                                    <div class="col-sm-6">
                                        <label class="adpo-option">
                                            <input type="radio" name="payment_method" value="{{ $value }}" required @if(old('payment_method') === $value) checked @endif>
                                            <span><strong>{{ $label }}</strong></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div> --}}
                        </div>
                        <div class="col-lg-6">
                            <div class="adpo-section-head">
                                <div>
                                    <h6 class="adpo-section-title">Payment Method</h6>
                                    <p class="adpo-section-copy">Select your preferred payment option for this order.</p>
                                </div>
                            </div>
                            <div class="row g-2">
                                {{-- @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer', 'credit' => 'Credit']as $value => $label) --}}
                                @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                                    <div class="col-sm-6">
                                        <label class="adpo-option">
                                            <input type="radio" name="payment_method" value="{{ $value }}" @if(old('payment_method') === $value) checked @endif>
                                            <span><strong>{{ $label }}</strong></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @php
                                $selectedBank = old('bank_name');
                                $commonBanks = [
                                    'BDO Unibank',
                                    'Bank of the Philippine Islands (BPI)',
                                    'Metrobank',
                                    'Land Bank of the Philippines',
                                    'Philippine National Bank (PNB)',
                                    'Security Bank',
                                    'UnionBank of the Philippines',
                                    'RCBC',
                                    'China Bank',
                                    'EastWest Bank',
                                    'Maya Bank',
                                    'SeaBank Philippines',
                                ];
                            @endphp
                            <div id="bankTransferPanel" class="adpo-bank-panel {{ old('payment_method') === 'bank_transfer' ? '' : 'd-none' }}">
                                <div class="adpo-bank-panel-head"><i class="ti ti-building-bank"></i> Bank Transfer Details</div>
                                <label class="form-label" for="bankName">Receiving Bank <span class="text-danger">*</span></label>
                                <select name="bank_name" id="bankName" class="form-select" @if(old('payment_method') === 'bank_transfer') required @else disabled @endif>
                                    <option value="">Select bank</option>
                                    @foreach($commonBanks as $bank)
                                        <option value="{{ $bank }}" @if($selectedBank === $bank) selected @endif>{{ $bank }}</option>
                                    @endforeach
                                    <option value="Other Bank" @if($selectedBank === 'Other Bank') selected @endif>Other Bank</option>
                                </select>
                                <div id="otherBankWrap" class="mt-2 {{ $selectedBank === 'Other Bank' ? '' : 'd-none' }}">
                                    <label class="form-label" for="otherBankName">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="other_bank_name" id="otherBankName" class="form-control" value="{{ old('other_bank_name') }}" maxlength="255" placeholder="Enter bank name" @if($selectedBank === 'Other Bank') required @else disabled @endif>
                                </div>
                                <small class="text-muted d-block mt-2">Select the bank that will receive this payment.</small>
                            </div>
                            {{-- <label class="form-label mt-3">Delivery Fee</label>
                            <input type="number" name="delivery_fee" id="deliveryFee" class="form-control" min="0" step="0.01" value="{{ old('delivery_fee', 0) }}"> --}}
                        </div>
                    </div>
                    <div class="adpo-help">
                        <b>CEO-ADC-FOR-005-000 | Area Distributor Purchase Order</b><br>
                        This communication, all its pages, contain confidential and/or legally privileged information meant solely for the intended recipient. If you are not the intended recipient and have come into possession of this communication: (1) by email or online platform please delete it and all its attachments and inform the sender by email of the erroneous transmission, (2) by post please mail this communication resealed to the sender. If you are not the intended recipient of this communication DO NOT disclose its contents, copy, distribute it, nor use the information contained in it. Your violation of these terms may result in severe civil and/or criminal liability.
                    </div>
                </div>

                <div class="adpo-section">
                    <div class="adpo-product-toolbar">
                        <div>
                            <h6 class="adpo-section-title mb-1">Products</h6>
                            <p class="adpo-section-copy mb-0"><span id="selectedCount">0</span> selected item(s) from {{ $products->count() }} available product(s)</p>
                        </div>
                        <div class="adpo-product-tools">
                            <button type="button" class="favorite-filter-btn" id="favoriteFilter">
                                <i class="bi bi-star-fill"></i>
                                Favorites
                                <span class="count" id="favoriteCount">{{ number_format($favoriteProductCount) }}</span>
                            </button>
                            <div class="product-search">
                                <i class="bi bi-search"></i>
                                <input type="search" id="productSearch" class="form-control form-control-sm" placeholder="Search product">
                            </div>
                            <select id="productSort" class="form-select form-select-sm">
                                <option value="name">Sort by name</option>
                                <option value="price-low">Price low to high</option>
                                <option value="price-high">Price high to low</option>
                            </select>
                        </div>
                    </div>

                    @php
                        $regularProducts = $products->filter(fn($product) => (int) ($product->for_ad ?? 0) !== 1);
                        // dd($regularProducts);
                        $adProducts = $products->filter(fn($product) => (int) ($product->for_ad ?? 0) === 1);
                        $hasSelectedAdProduct = $adProducts->contains(function ($product) {
                            $oldQty = old('products.' . $product->id . '.qty', 0);
                            $oldColorQty = collect(old('products.' . $product->id . '.colors', []))->sum(fn($qty) => (int) $qty);
                            $oldSizeQty = collect(old('products.' . $product->id . '.sizes', []))->sum(fn($qty) => (int) $qty);
                            return old('products.' . $product->id . '.selected') || (int) $oldQty > 0 || $oldColorQty > 0 || $oldSizeQty > 0;
                        });
                    @endphp

                    <div class="product-grid" id="productGrid">
                        <div class="product-list" data-product-list>
                        @forelse($regularProducts as $product)
                            @php
                                $unitPrice = ($product->dprice !== null && $product->dprice !== '') ? (float) $product->dprice : 0;
                                $oldQty = old('products.' . $product->id . '.qty', 0);
                                $oldSelected = old('products.' . $product->id . '.selected') || (int) $oldQty > 0;
                                $productName = $product->item ?: $product->product_name;
                                $productDescription = $product->item_description ?: $product->description;
                                $isBundle = ($product->item_type ?? 'product') === 'bundle';
                                $isStoveKit = strpos(strtolower(trim((string) $productName)), 'gaz lite stove kit') !== false;
                                $isUniform = strpos(strtolower(trim((string) $productName)), 'gaz lite authorized retail partner uniform') !== false;
                                $isLpgRefill = strpos(strtolower(trim((string) $productName)), 'lpg refill') !== false;
                                $availableStoveKitColors = $isStoveKit ? $product->availableStoveKitColors() : [];
                                $oldColors = old('products.' . $product->id . '.colors', []);
                                $oldColorQty = collect($oldColors)->sum(fn($qty) => (int) $qty);
                                $oldSizes = old('products.' . $product->id . '.sizes', []);
                                $oldSizeQty = collect($oldSizes)->sum(fn($qty) => (int) $qty);
                                $oldQty = ($isStoveKit && $oldColorQty > 0) || ($isUniform && $oldSizeQty > 0)
                                    ? ($isStoveKit ? $oldColorQty : $oldSizeQty)
                                    : $oldQty;
                                $oldSelected = old('products.' . $product->id . '.selected') || (int) $oldQty > 0;
                                $isFavorite = $favoriteProductIds->contains((int) $product->id);
                                $imagePath = $product->item_image && file_exists(public_path('uploads/products/' . $product->item_image))
                                    ? asset('uploads/products/' . $product->item_image)
                                    : asset('design/assets/images/products/empty-shopping-bag.gif');
                            @endphp
                            <div class="product-card {{ $oldSelected ? 'is-selected' : '' }} {{ $isFavorite ? 'is-favorite' : '' }}" data-product-id="{{ $product->id }}" data-name="{{ strtolower((string) $productName) }}" data-price="{{ $unitPrice }}" data-has-colors="{{ $isStoveKit ? '1' : '0' }}" data-has-sizes="{{ $isUniform ? '1' : '0' }}" data-is-lpg-refill="{{ $isLpgRefill ? '1' : '0' }}" data-is-favorite="{{ $isFavorite ? '1' : '0' }}">
                                <button type="button" class="product-favorite-btn js-product-favorite {{ $isFavorite ? 'is-favorite' : '' }}" data-url="{{ route('ad-purchase-orders.products.favorite', $product->id) }}" title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}" aria-label="{{ $isFavorite ? 'Remove ' . $productName . ' from favorites' : 'Add ' . $productName . ' to favorites' }}">
                                    <i class="bi {{ $isFavorite ? 'bi-star-fill' : 'bi-star' }}"></i>
                                </button>
                                <input type="checkbox" class="form-check-input product-check js-product-check" name="products[{{ $product->id }}][selected]" value="1" @if($oldSelected) checked @endif aria-label="Select {{ $productName }}">
                                <span class="product-selected-badge"><i class="bi bi-check2"></i> Selected</span>
                                <div class="{{ $isBundle ? 'product-image is-bundle' : 'product-image' }}">
                                    @if($isBundle)
                                        <div class="bundle-store-image-frame">
                                            <img src="{{ $imagePath }}" alt="{{ $productName }}">
                                        </div>
                                        <span class="bundle-store-badge"><i class="bi bi-boxes"></i></span>
                                    @else
                                        <img src="{{ $imagePath }}" alt="{{ $productName }}">
                                    @endif
                                </div>
                                <div class="product-body">
                                    <p class="product-name">{{ $productName }}</p>
                                    <p class="product-desc">{{ \Illuminate\Support\Str::limit($productDescription, 100) }}</p>
                                    <div class="product-meta">
                                        <div>
                                            <span class="product-price-label">Unit Price</span>
                                            <div class="product-price">PHP {{ number_format($unitPrice, 2) }}</div>
                                        </div>
                                        <div class="qty-group">
                                            <label class="form-label mb-1 small text-muted">Qty</label>
                                            <input type="number" name="products[{{ $product->id }}][qty]" class="form-control form-control-sm qty-control js-product-qty" value="{{ $oldQty }}" min="0" data-price="{{ $unitPrice }}" aria-label="Quantity for {{ $productName }}" @if($isStoveKit || $isUniform) readonly @endif>
                                        </div>
                                    </div>
                                    @if($isStoveKit)
                                        <div class="color-variant-panel">
                                            <p class="color-variant-title">Color Quantity</p>
                                            @if(!empty($availableStoveKitColors))
                                                <div class="color-variant-grid">
                                                    @foreach($availableStoveKitColors as $colorValue => $colorLabel)
                                                        <div class="color-variant">
                                                            <span class="color-dot {{ $colorValue }}"></span>
                                                            <label for="product_{{ $product->id }}_{{ $colorValue }}">{{ $colorLabel }}</label>
                                                            <input type="number" id="product_{{ $product->id }}_{{ $colorValue }}" name="products[{{ $product->id }}][colors][{{ $colorValue }}]" class="form-control form-control-sm color-qty js-color-qty" value="{{ old('products.' . $product->id . '.colors.' . $colorValue, 0) }}" min="0" aria-label="{{ $colorLabel }} quantity for {{ $productName }}">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-muted small">No stove kit colors are available.</div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($isUniform)
                                        <div class="size-variant-panel">
                                            <p class="size-variant-title">Size Quantity</p>
                                            <div class="size-variant-grid">
                                                @foreach($uniformSizes as $sizeValue => $sizeLabel)
                                                    <div class="size-variant">
                                                        <label for="product_{{ $product->id }}_{{ $sizeValue }}">{{ $sizeLabel }}</label>
                                                        <input type="number" id="product_{{ $product->id }}_{{ $sizeValue }}" name="products[{{ $product->id }}][sizes][{{ $sizeValue }}]" class="form-control form-control-sm size-qty js-size-qty" value="{{ old('products.' . $product->id . '.sizes.' . $sizeValue, 0) }}" min="0" aria-label="{{ $sizeLabel }} quantity for {{ $productName }}">
                                                    </div>
                                                @endforeach
                                                <div class="size-variant is-other">
                                                    <label for="product_{{ $product->id }}_other">Other</label>
                                                    <input type="number" id="product_{{ $product->id }}_other" name="products[{{ $product->id }}][sizes][other]" class="form-control form-control-sm size-qty js-size-qty js-other-size-qty" value="{{ old('products.' . $product->id . '.sizes.other', 0) }}" min="0" aria-label="Other size quantity for {{ $productName }}">
                                                    <input type="text" name="products[{{ $product->id }}][other_size]" class="form-control form-control-sm size-other-field js-other-size-label d-none" value="{{ old('products.' . $product->id . '.other_size') }}" placeholder="Enter other size" aria-label="Other size for {{ $productName }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            @if($adProducts->isEmpty())
                            <div class="alert alert-info empty-products">
                                <i class="bi bi-info-circle"></i> No active products available for purchase order.
                            </div>
                            @endif
                        @endforelse
                        </div>

                        @if($adProducts->isNotEmpty())
                            <div class="ad-product-collapse">
                                <button class="ad-product-toggle"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#adProductCollapse"
                                        aria-expanded="{{ $hasSelectedAdProduct ? 'true' : 'false' }}"
                                        aria-controls="adProductCollapse">
                                    <span>
                                        <strong>Marketing Collateral Products</strong>
                                        <small>{{ $adProducts->count() }} product(s) tagged for Marketing Collateral</small>
                                    </span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="collapse {{ $hasSelectedAdProduct ? 'show' : '' }}" id="adProductCollapse">
                                    <div class="ad-product-body">
                                        <div class="product-list" data-product-list>
                                            @foreach($adProducts as $product)
                                                @php
                                                    $unitPrice = ($product->price !== null && $product->price !== '') ? (float) $product->price : 0;
                                                    $oldQty = old('products.' . $product->id . '.qty', 0);
                                                    $oldSelected = old('products.' . $product->id . '.selected') || (int) $oldQty > 0;
                                                    $productName = $product->item ?: $product->product_name;
                                                    $productDescription = $product->item_description ?: $product->description;
                                                    $isBundle = ($product->item_type ?? 'product') === 'bundle';
                                                    $isStoveKit = strpos(strtolower(trim((string) $productName)), 'gaz lite stove kit') !== false;
                                                    $isUniform = strpos(strtolower(trim((string) $productName)), 'gaz lite authorized retail partner uniform') !== false;
                                                    $isLpgRefill = strpos(strtolower(trim((string) $productName)), 'lpg refill') !== false;
                                                    $availableStoveKitColors = $isStoveKit ? $product->availableStoveKitColors() : [];
                                                    $oldColors = old('products.' . $product->id . '.colors', []);
                                                    $oldColorQty = collect($oldColors)->sum(fn($qty) => (int) $qty);
                                                    $oldSizes = old('products.' . $product->id . '.sizes', []);
                                                    $oldSizeQty = collect($oldSizes)->sum(fn($qty) => (int) $qty);
                                                    $oldQty = ($isStoveKit && $oldColorQty > 0) || ($isUniform && $oldSizeQty > 0)
                                                        ? ($isStoveKit ? $oldColorQty : $oldSizeQty)
                                                        : $oldQty;
                                                    $oldSelected = old('products.' . $product->id . '.selected') || (int) $oldQty > 0;
                                                    $isFavorite = $favoriteProductIds->contains((int) $product->id);
                                                    $imagePath = $product->item_image && file_exists(public_path('uploads/products/' . $product->item_image))
                                                        ? asset('uploads/products/' . $product->item_image)
                                                        : asset('design/assets/images/products/empty-shopping-bag.gif');
                                                @endphp
                                                <div class="product-card {{ $oldSelected ? 'is-selected' : '' }} {{ $isFavorite ? 'is-favorite' : '' }}" data-product-id="{{ $product->id }}" data-name="{{ strtolower((string) $productName) }}" data-price="{{ $unitPrice }}" data-has-colors="{{ $isStoveKit ? '1' : '0' }}" data-has-sizes="{{ $isUniform ? '1' : '0' }}" data-is-lpg-refill="{{ $isLpgRefill ? '1' : '0' }}" data-is-favorite="{{ $isFavorite ? '1' : '0' }}">
                                                    <button type="button" class="product-favorite-btn js-product-favorite {{ $isFavorite ? 'is-favorite' : '' }}" data-url="{{ route('ad-purchase-orders.products.favorite', $product->id) }}" title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}" aria-label="{{ $isFavorite ? 'Remove ' . $productName . ' from favorites' : 'Add ' . $productName . ' to favorites' }}">
                                                        <i class="bi {{ $isFavorite ? 'bi-star-fill' : 'bi-star' }}"></i>
                                                    </button>
                                                    <input type="checkbox" class="form-check-input product-check js-product-check" name="products[{{ $product->id }}][selected]" value="1" @if($oldSelected) checked @endif aria-label="Select {{ $productName }}">
                                                    <span class="product-selected-badge"><i class="bi bi-check2"></i> Selected</span>
                                                    <div class="{{ $isBundle ? 'product-image is-bundle' : 'product-image' }}">
                                                        @if($isBundle)
                                                            <div class="bundle-store-image-frame">
                                                                <img src="{{ $imagePath }}" alt="{{ $productName }}">
                                                            </div>
                                                            <span class="bundle-store-badge"><i class="bi bi-boxes"></i></span>
                                                        @else
                                                            <img src="{{ $imagePath }}" alt="{{ $productName }}">
                                                        @endif
                                                    </div>
                                                    <div class="product-body">
                                                        <p class="product-name">{{ $productName }}</p>
                                                        <p class="product-desc">{{ \Illuminate\Support\Str::limit($productDescription, 100) }}</p>
                                                        <div class="product-meta">
                                                            <div>
                                                                <span class="product-price-label">Unit Price</span>
                                                                <div class="product-price">PHP {{ number_format($unitPrice, 2) }}</div>
                                                            </div>
                                                            <div class="qty-group">
                                                                <label class="form-label mb-1 small text-muted">Qty</label>
                                                                <input type="number" name="products[{{ $product->id }}][qty]" class="form-control form-control-sm qty-control js-product-qty" value="{{ $oldQty }}" min="0" data-price="{{ $unitPrice }}" aria-label="Quantity for {{ $productName }}" @if($isStoveKit || $isUniform) readonly @endif>
                                                            </div>
                                                        </div>
                                                        @if($isStoveKit)
                                                            <div class="color-variant-panel">
                                                                <p class="color-variant-title">Color Quantity</p>
                                                                @if(!empty($availableStoveKitColors))
                                                                    <div class="color-variant-grid">
                                                                        @foreach($availableStoveKitColors as $colorValue => $colorLabel)
                                                                            <div class="color-variant">
                                                                                <span class="color-dot {{ $colorValue }}"></span>
                                                                                <label for="product_{{ $product->id }}_{{ $colorValue }}">{{ $colorLabel }}</label>
                                                                                <input type="number" id="product_{{ $product->id }}_{{ $colorValue }}" name="products[{{ $product->id }}][colors][{{ $colorValue }}]" class="form-control form-control-sm color-qty js-color-qty" value="{{ old('products.' . $product->id . '.colors.' . $colorValue, 0) }}" min="0" aria-label="{{ $colorLabel }} quantity for {{ $productName }}">
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="text-muted small">No stove kit colors are available.</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if($isUniform)
                                                            <div class="size-variant-panel">
                                                                <p class="size-variant-title">Size Quantity</p>
                                                                <div class="size-variant-grid">
                                                                    @foreach($uniformSizes as $sizeValue => $sizeLabel)
                                                                        <div class="size-variant">
                                                                            <label for="product_{{ $product->id }}_{{ $sizeValue }}">{{ $sizeLabel }}</label>
                                                                            <input type="number" id="product_{{ $product->id }}_{{ $sizeValue }}" name="products[{{ $product->id }}][sizes][{{ $sizeValue }}]" class="form-control form-control-sm size-qty js-size-qty" value="{{ old('products.' . $product->id . '.sizes.' . $sizeValue, 0) }}" min="0" aria-label="{{ $sizeLabel }} quantity for {{ $productName }}">
                                                                        </div>
                                                                    @endforeach
                                                                    <div class="size-variant is-other">
                                                                        <label for="product_{{ $product->id }}_other">Other</label>
                                                                        <input type="number" id="product_{{ $product->id }}_other" name="products[{{ $product->id }}][sizes][other]" class="form-control form-control-sm size-qty js-size-qty js-other-size-qty" value="{{ old('products.' . $product->id . '.sizes.other', 0) }}" min="0" aria-label="Other size quantity for {{ $productName }}">
                                                                        <input type="text" name="products[{{ $product->id }}][other_size]" class="form-control form-control-sm size-other-field js-other-size-label d-none" value="{{ old('products.' . $product->id . '.other_size') }}" placeholder="Enter other size" aria-label="Other size for {{ $productName }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="product-empty-search" id="productEmptySearch">
                            <i class="bi bi-search"></i>
                            <div class="fw-bold mt-2">No products found</div>
                            <div>Try a different search term or clear the search box.</div>
                        </div>
                    </div>
                </div>

                <div class="adpo-section">
                    <div class="adpo-section-head">
                        <div>
                            <h6 class="adpo-section-title">Remarks</h6>
                            <p class="adpo-section-copy">Optional delivery notes or internal instructions.</p>
                        </div>
                    </div>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Optional instructions or delivery notes">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <aside class="adpo-side">
                <div class="summary-card">
                    <h6 class="summary-title">Order Summary</h6>
                    <div class="summary-row">
                        <span>Selected Products</span>
                        <strong><span id="summarySelected">0</span></strong>
                    </div>
                    <div class="summary-row">
                        <span>Total Quantity</span>
                        <strong><span id="summaryQty">0</span></strong>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>PHP <span id="subtotal">0.00</span></strong>
                    </div>
                    <div class="summary-row {{ $useRebateVoucher === 'yes' ? '' : 'd-none' }}" id="rebateSummaryRow">
                        <span>Rebate Voucher</span>
                        <strong>- PHP <span id="rebateTotal">0.00</span></strong>
                    </div>
                    <div class="summary-row d-none" id="pickupDiscountSummaryRow">
                        <span>Pick Up Lubao Discount</span>
                        <strong>- PHP <span id="pickupDiscountTotal">0.00</span></strong>
                    </div>
                    @if(optional($ad)->withholding_tax)
                        <div class="summary-row" id="withholdingSummaryRow">
                            <span>Less: EWT</span>
                            <strong>- PHP <span id="withholdingTotal">0.00</span></strong>
                        </div>
                    @endif
                    {{-- <div class="summary-row">
                        <span>Delivery Fee</span>
                        <strong>PHP <span id="deliveryTotal">0.00</span></strong>
                    </div> --}}
                    <div class="summary-total">
                        <span>Total</span>
                        <span>PHP <span id="grandTotal">0.00</span></span>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 mt-3">
                        <i class="bi bi-send"></i> Submit Purchase Order
                    </button>
                </div>
            </aside>
        </div>
    </div>
</form>

<div class="adpo-loading" id="adpoLoading" aria-live="polite" aria-hidden="true">
    <div class="adpo-loading-box">
        <div class="adpo-loading-spinner"></div>
        <p class="adpo-loading-title">Submitting purchase order</p>
        <p class="adpo-loading-copy">Please wait while we save your ADPO and notify the warehouse.</p>
    </div>
</div>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adpoForm');
    const loading = document.getElementById('adpoLoading');
    const grid = document.getElementById('productGrid');
    const search = document.getElementById('productSearch');
    const sort = document.getElementById('productSort');
    const favoriteFilter = document.getElementById('favoriteFilter');
    const favoriteCount = document.getElementById('favoriteCount');
    const productEmptySearch = document.getElementById('productEmptySearch');
    const adProductCollapse = document.getElementById('adProductCollapse');
    const deliveryFee = document.getElementById('deliveryFee');
    const voucherCodeWrap = document.getElementById('voucherCodeWrap');
    const voucherCode = document.getElementById('voucherCode');
    const rebateAmount = document.getElementById('rebateAmount');
    const voucherFeedback = document.getElementById('voucherFeedback');
    const territorySelect = document.querySelector('[name="authorized_territory"]');
    const paymentMethod = document.getElementById('paymentMethod');
    const bankTransferPanel = document.getElementById('bankTransferPanel');
    const bankName = document.getElementById('bankName');
    const otherBankWrap = document.getElementById('otherBankWrap');
    const otherBankName = document.getElementById('otherBankName');
    const selectedCount = document.getElementById('selectedCount');
    const summarySelected = document.getElementById('summarySelected');
    const summaryQty = document.getElementById('summaryQty');
    const subtotalEl = document.getElementById('subtotal');
    const deliveryTotalEl = document.getElementById('deliveryTotal');
    const rebateSummaryRow = document.getElementById('rebateSummaryRow');
    const rebateTotalEl = document.getElementById('rebateTotal');
    const pickupDiscountSummaryRow = document.getElementById('pickupDiscountSummaryRow');
    const pickupDiscountTotalEl = document.getElementById('pickupDiscountTotal');
    const withholdingTotalEl = document.getElementById('withholdingTotal');
    const grandTotalEl = document.getElementById('grandTotal');
    const hasWithholdingTax = @json((bool) optional($ad)->withholding_tax);
    const availableVouchersUrl = @json(route('vouchers.available-for-territory'));
    let currentVoucherSubtotal = 0;
    let voucherCheckTimer = null;
    let lastVoucherKey = '';
    let voucherChoiceTouched = false;
    let showFavoritesOnly = false;

    function money(value) {
        return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectedShippingType() {
        const selected = document.querySelector('input[name="shipping_type"]:checked');
        return selected ? selected.value : 'delivered';
    }

    function syncBankTransferFields() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        const isBankTransfer = selectedPayment && selectedPayment.value === 'bank_transfer';
        const isOtherBank = isBankTransfer && bankName.value === 'Other Bank';

        bankTransferPanel.classList.toggle('d-none', !isBankTransfer);
        bankName.disabled = !isBankTransfer;
        bankName.required = isBankTransfer;
        otherBankWrap.classList.toggle('d-none', !isOtherBank);
        otherBankName.disabled = !isOtherBank;
        otherBankName.required = isOtherBank;

        if (!isBankTransfer) {
            bankName.value = '';
            otherBankName.value = '';
        } else if (!isOtherBank) {
            otherBankName.value = '';
        }
    }

    function syncDeliveryField() {
        if (!deliveryFee) {
            return;
        }

        const isPickup = selectedShippingType().indexOf('pickup') === 0;
        deliveryFee.disabled = isPickup;
        if (isPickup) {
            deliveryFee.dataset.previousValue = deliveryFee.dataset.previousValue || deliveryFee.value;
            deliveryFee.value = 0;
        } else if (deliveryFee.dataset.previousValue && Number(deliveryFee.value || 0) === 0) {
            deliveryFee.value = deliveryFee.dataset.previousValue;
            deliveryFee.dataset.previousValue = '';
        }
    }

    function selectedVoucherOption() {
        const selected = document.querySelector('input[name="use_rebate_voucher"]:checked');
        return selected ? selected.value : 'no';
    }

    function syncTerritoryVouchers() {
        const selectedArea = territorySelect ? territorySelect.value.trim() : '';
        const currentCode = voucherCode.value;

        voucherCode.innerHTML = '<option value="">Select voucher code</option>';
        rebateAmount.value = 0;
        lastVoucherKey = '';

        if (!selectedArea) {
            if (voucherFeedback) {
                voucherFeedback.textContent = 'Select an authorized territory to view available vouchers.';
                voucherFeedback.className = 'text-muted d-block mt-1';
            }
            return Promise.resolve(0);
        }

        if (voucherFeedback) {
            voucherFeedback.textContent = 'Loading vouchers for the selected territory...';
            voucherFeedback.className = 'text-muted d-block mt-1';
        }

        return fetch(availableVouchersUrl + '?area_name=' + encodeURIComponent(selectedArea), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) throw data;
                return data;
            });
        })
        .then(function (data) {
            const vouchers = data.vouchers || [];
            vouchers.forEach(function (voucher) {
                const option = document.createElement('option');
                option.value = voucher.code;
                option.textContent = voucher.label;
                voucherCode.appendChild(option);
            });

            const currentExists = vouchers.some(function (voucher) { return voucher.code === currentCode; });
            voucherCode.value = currentExists ? currentCode : (vouchers.length === 1 ? vouchers[0].code : '');

            if (voucherFeedback) {
                voucherFeedback.textContent = vouchers.length
                    ? vouchers.length + ' active voucher(s) available for this territory.'
                    : 'No active voucher is assigned to this territory.';
                voucherFeedback.className = vouchers.length
                    ? 'text-success d-block mt-1'
                    : 'text-warning d-block mt-1';
            }

            return vouchers.length;
        })
        .catch(function (data) {
            if (voucherFeedback) {
                voucherFeedback.textContent = data.message || 'Unable to load vouchers.';
                voucherFeedback.className = 'text-danger d-block mt-1';
            }
            return 0;
        });
    }

    function syncVoucherForTerritory() {
        lastVoucherKey = '';
        syncTerritoryVouchers().then(function (availableCount) {
            if (!voucherChoiceTouched) {
                const automaticChoice = document.querySelector(
                    'input[name="use_rebate_voucher"][value="' + (availableCount > 0 ? 'yes' : 'no') + '"]'
                );
                if (automaticChoice) {
                    automaticChoice.checked = true;
                }
            }

            syncVoucherField();
        });
    }

    function syncVoucherField() {
        const useVoucher = selectedVoucherOption() === 'yes';

        voucherCodeWrap.classList.toggle('d-none', !useVoucher);
        voucherCode.disabled = !useVoucher;
        voucherCode.required = useVoucher;
        if (paymentMethod) {
            paymentMethod.value = useVoucher ? 'voucher' : 'cash';
        }

        if (!useVoucher) {
            voucherCode.value = '';
            rebateAmount.value = 0;
            rebateTotalEl.textContent = money(0);
            rebateSummaryRow.classList.add('d-none');
            if (voucherFeedback) {
                voucherFeedback.textContent = 'The voucher discount will be calculated automatically.';
                voucherFeedback.className = 'text-muted d-block mt-1';
            }
        } else {
            if (!voucherCode.value && voucherCode.dataset.defaultVoucherCode) {
                const defaultOption = Array.prototype.find.call(voucherCode.options, function (option) {
                    return option.value === voucherCode.dataset.defaultVoucherCode && !option.disabled;
                });
                voucherCode.value = defaultOption ? defaultOption.value : '';
            }
            rebateSummaryRow.classList.toggle('d-none', parseFloat(rebateAmount.value || '0') <= 0);
        }

        scheduleVoucherCheck();
        recalc();
    }

    function scheduleVoucherCheck() {
        clearTimeout(voucherCheckTimer);
        voucherCheckTimer = setTimeout(checkVoucherCode, 350);
    }

    function checkVoucherCode() {
        if (selectedVoucherOption() !== 'yes' || !voucherCode.value.trim()) {
            rebateAmount.value = 0;
            lastVoucherKey = '';
            recalc();
            return;
        }

        const selectedArea = territorySelect ? territorySelect.value : '';
        const key = voucherCode.value.trim().toUpperCase() + '|' + currentVoucherSubtotal.toFixed(2) + '|' + selectedArea;
        if (key === lastVoucherKey) {
            return;
        }
        lastVoucherKey = key;

        if (voucherFeedback) {
            voucherFeedback.textContent = 'Checking voucher...';
            voucherFeedback.className = 'text-muted d-block mt-1';
        }

        fetch("{{ route('vouchers.check') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                code: voucherCode.value,
                subtotal: currentVoucherSubtotal,
                area_name: selectedArea
            })
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        })
        .then(function (data) {
            rebateAmount.value = data.discount || 0;
            if (voucherFeedback) {
                voucherFeedback.textContent = data.message + ' Discount: PHP ' + money(data.discount || 0);
                voucherFeedback.className = 'text-success d-block mt-1';
            }
            recalc();
        })
        .catch(function (data) {
            rebateAmount.value = 0;
            if (voucherFeedback) {
                voucherFeedback.textContent = data.message || 'Voucher could not be applied.';
                voucherFeedback.className = 'text-danger d-block mt-1';
            }
            recalc();
        });
    }

    function syncColorQuantity(card) {
        if (!card || card.dataset.hasColors !== '1') {
            return;
        }

        const qtyInput = card.querySelector('.js-product-qty');
        const checkbox = card.querySelector('.js-product-check');
        let colorTotal = 0;

        card.querySelectorAll('.js-color-qty').forEach(function (input) {
            colorTotal += parseInt(input.value || '0', 10);
        });

        qtyInput.value = colorTotal;
        checkbox.checked = colorTotal > 0 || checkbox.checked;
    }

    function syncOtherSizeField(card) {
        if (!card || card.dataset.hasSizes !== '1') {
            return;
        }

        const otherQty = card.querySelector('.js-other-size-qty');
        const otherLabel = card.querySelector('.js-other-size-label');

        if (!otherQty || !otherLabel) {
            return;
        }

        const showOther = parseInt(otherQty.value || '0', 10) > 0;
        otherLabel.classList.toggle('d-none', !showOther);
        otherLabel.disabled = !showOther;
        otherLabel.required = showOther;

        if (!showOther) {
            otherLabel.value = '';
        }
    }

    function syncSizeQuantity(card) {
        if (!card || card.dataset.hasSizes !== '1') {
            return;
        }

        const qtyInput = card.querySelector('.js-product-qty');
        const checkbox = card.querySelector('.js-product-check');
        let sizeTotal = 0;

        card.querySelectorAll('.js-size-qty').forEach(function (input) {
            sizeTotal += parseInt(input.value || '0', 10);
        });

        qtyInput.value = sizeTotal;
        checkbox.checked = sizeTotal > 0 || checkbox.checked;
        syncOtherSizeField(card);
    }

    function recalc() {
        let subtotal = 0;
        let selected = 0;
        let totalQty = 0;
        let lpgRefillSubtotal = 0;

        document.querySelectorAll('.product-card').forEach(function (card) {
            syncColorQuantity(card);
            syncSizeQuantity(card);

            const checkbox = card.querySelector('.js-product-check');
            const qtyInput = card.querySelector('.js-product-qty');
            const qty = parseInt(qtyInput.value || '0', 10);
            const price = parseFloat(qtyInput.dataset.price || '0');
            const active = checkbox.checked;

            card.classList.toggle('is-selected', active);

            if (active && qty > 0) {
                const lineTotal = qty * price;
                selected += 1;
                totalQty += qty;
                subtotal += lineTotal;

                if (card.dataset.isLpgRefill === '1') {
                    lpgRefillSubtotal += lineTotal;
                }
            }
        });

        const useVoucher = selectedVoucherOption() === 'yes';
        const rebate = useVoucher ? Math.min(parseFloat(rebateAmount.value || '0'), subtotal) : 0;
        const pickupDiscount = selectedShippingType() === 'pickup_lubao' ? lpgRefillSubtotal * 0.02 : 0;
        const taxableTotal = Math.max(0, subtotal - rebate - pickupDiscount);
        const ewtBase = taxableTotal / 1.12;
        const withholdingTax = hasWithholdingTax ? ewtBase * 0.01 : 0;
        const grandTotal = Math.max(0, taxableTotal - withholdingTax);
        currentVoucherSubtotal = subtotal;

        selectedCount.textContent = selected;
        summarySelected.textContent = selected;
        summaryQty.textContent = totalQty;
        subtotalEl.textContent = money(subtotal);
        // deliveryTotalEl.textContent = money(delivery);
        rebateTotalEl.textContent = money(rebate);
        rebateSummaryRow.classList.toggle('d-none', !useVoucher || rebate <= 0);
        pickupDiscountTotalEl.textContent = money(pickupDiscount);
        pickupDiscountSummaryRow.classList.toggle('d-none', pickupDiscount <= 0);
        if (withholdingTotalEl) {
            withholdingTotalEl.textContent = money(withholdingTax);
        }
        grandTotalEl.textContent = money(grandTotal);

        if (useVoucher && voucherCode.value.trim()) {
            scheduleVoucherCheck();
        }
    }

    function filterProducts() {
        const term = search.value.toLowerCase().trim();
        let visibleCount = 0;
        let visibleAdCount = 0;

        document.querySelectorAll('.product-card').forEach(function (card) {
            const matchesSearch = card.dataset.name.indexOf(term) !== -1;
            const matchesFavorite = !showFavoritesOnly || card.dataset.isFavorite === '1';
            const isVisible = matchesSearch && matchesFavorite;
            card.classList.toggle('is-hidden', !isVisible);

            if (isVisible) {
                visibleCount += 1;
                if (card.closest('#adProductCollapse')) {
                    visibleAdCount += 1;
                }
            }
        });

        productEmptySearch.classList.toggle('is-visible', (term !== '' || showFavoritesOnly) && visibleCount === 0);

        if (term !== '' && visibleAdCount > 0 && adProductCollapse && window.bootstrap) {
            bootstrap.Collapse.getOrCreateInstance(adProductCollapse, { toggle: false }).show();
        }
    }

    function updateFavoriteCount() {
        const count = document.querySelectorAll('.product-card[data-is-favorite="1"]').length;
        if (favoriteCount) {
            favoriteCount.textContent = count.toLocaleString('en-US');
        }
    }

    function setProductFavorite(button, favorited) {
        const card = button.closest('.product-card');
        const icon = button.querySelector('i');
        card.dataset.isFavorite = favorited ? '1' : '0';
        card.classList.toggle('is-favorite', favorited);
        button.classList.toggle('is-favorite', favorited);
        button.title = favorited ? 'Remove from favorites' : 'Add to favorites';
        if (icon) {
            icon.className = favorited ? 'bi bi-star-fill' : 'bi bi-star';
        }
        updateFavoriteCount();
        filterProducts();
    }

    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('js-color-qty')) {
            const card = event.target.closest('.product-card');
            syncColorQuantity(card);
            recalc();
        }

        if (event.target.classList.contains('js-size-qty')) {
            const card = event.target.closest('.product-card');
            syncSizeQuantity(card);
            recalc();
        }

        if (event.target.classList.contains('js-product-qty')) {
            const card = event.target.closest('.product-card');
            if (card.dataset.hasColors === '1' || card.dataset.hasSizes === '1') {
                return;
            }
            const checkbox = card.querySelector('.js-product-check');
            checkbox.checked = parseInt(event.target.value || '0', 10) > 0;
            recalc();
        }

        if (deliveryFee && event.target === deliveryFee) {
            deliveryFee.dataset.previousValue = deliveryFee.value;
            recalc();
        }

        if (event.target === rebateAmount) {
            recalc();
        }

        if (event.target === search) {
            filterProducts();
        }

        if (event.target === voucherCode) {
            scheduleVoucherCheck();
        }

    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('js-product-check')) {
            const card = event.target.closest('.product-card');
            const qty = card.querySelector('.js-product-qty');
            card.classList.toggle('is-selected', event.target.checked);

            if (card.dataset.hasColors === '1' || card.dataset.hasSizes === '1') {
                if (!event.target.checked) {
                    card.querySelectorAll('.js-color-qty').forEach(function (input) {
                        input.value = 0;
                    });
                    card.querySelectorAll('.js-size-qty').forEach(function (input) {
                        input.value = 0;
                    });
                    card.querySelectorAll('.js-other-size-label').forEach(function (input) {
                        input.value = '';
                    });
                    qty.value = 0;
                } else if (parseInt(qty.value || '0', 10) === 0) {
                    const firstVariantQty = card.querySelector('.js-color-qty, .js-size-qty');
                    if (firstVariantQty) {
                        firstVariantQty.focus();
                    }
                }
                syncColorQuantity(card);
                syncSizeQuantity(card);
                recalc();
                return;
            }
            if (event.target.checked && parseInt(qty.value || '0', 10) === 0) {
                qty.value = 1;
            }
            recalc();
        }

        if (event.target.name === 'shipping_type') {
            syncDeliveryField();
            recalc();
        }

        if (event.target.name === 'use_rebate_voucher') {
            voucherChoiceTouched = true;
            syncVoucherField();
        }

        if (event.target.name === 'authorized_territory') {
            syncVoucherForTerritory();
        }

        if (event.target.name === 'payment_method' || event.target === bankName) {
            syncBankTransferFields();
        }

        if (event.target === voucherCode) {
            scheduleVoucherCheck();
        }

        if (event.target === sort) {
            grid.querySelectorAll('[data-product-list]').forEach(function (list) {
                const cards = Array.prototype.slice.call(list.querySelectorAll('.product-card'));
                cards.sort(function (a, b) {
                    if (sort.value === 'price-low') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    if (sort.value === 'price-high') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    return a.dataset.name.localeCompare(b.dataset.name);
                });
                cards.forEach(function (card) { list.appendChild(card); });
            });
        }
    });

    document.addEventListener('click', function (event) {
        const favoriteButton = event.target.closest('.js-product-favorite');
        if (!favoriteButton) {
            return;
        }

        favoriteButton.classList.add('is-saving');

        fetch(favoriteButton.dataset.url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        })
        .then(function (data) {
            setProductFavorite(favoriteButton, !!data.favorited);
        })
        .catch(function () {
            Swal.fire({
                icon: 'error',
                title: 'Favorite not saved',
                text: 'Please try again.',
                confirmButtonText: 'OK'
            });
        })
        .finally(function () {
            favoriteButton.classList.remove('is-saving');
        });
    });

    if (favoriteFilter) {
        favoriteFilter.addEventListener('click', function () {
            showFavoritesOnly = !showFavoritesOnly;
            favoriteFilter.classList.toggle('is-active', showFavoritesOnly);
            filterProducts();
        });
    }

    if (form && loading) {
        form.addEventListener('submit', function () {
            if (form.dataset.submitting === '1') {
                return;
            }

            form.dataset.submitting = '1';
            loading.classList.add('is-visible');
            loading.setAttribute('aria-hidden', 'false');

            form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...';
            });
        });
    }

    syncDeliveryField();
    syncVoucherForTerritory();
    syncBankTransferFields();
    updateFavoriteCount();
    filterProducts();
    recalc();
});
</script>
@endsection
