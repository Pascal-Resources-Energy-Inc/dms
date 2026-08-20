@extends('layouts.header')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css" rel="stylesheet">
<style>
    .isl-page {
        --isl-teal: #0f766e;
        --isl-cyan: #1598c6;
        --isl-navy: #183b56;
        --isl-border: #e2e8f0;
        --isl-muted: #64748b;
        padding: 18px 12px 32px;
        color: #172033;
    }

    .isl-hero,
    .isl-card,
    .isl-kpi {
        background: #fff;
        border: 1px solid var(--isl-border);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
    }

    .isl-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 24px;
        margin-bottom: 16px;
        overflow: hidden;
        background:
            radial-gradient(circle at 92% 15%, rgba(21, 152, 198, .16), transparent 28%),
            linear-gradient(135deg, #fff 0%, #eefbff 100%);
        border-left: 5px solid var(--isl-cyan);
    }

    .isl-hero::after {
        position: absolute;
        right: -45px;
        bottom: -60px;
        width: 180px;
        height: 180px;
        content: "";
        border: 28px solid rgba(15, 118, 110, .05);
        border-radius: 50%;
        pointer-events: none;
    }

    .isl-hero-copy,
    .isl-actions {
        position: relative;
        z-index: 1;
    }

    .isl-eyebrow {
        color: #1384aa;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .isl-hero h3 {
        margin: 4px 0 5px;
        color: #102a43;
        font-size: clamp(21px, 2vw, 26px);
        font-weight: 800;
    }

    .isl-hero p,
    .isl-report-head p {
        margin: 0;
        color: #6b7c93;
        font-size: 13px;
    }

    .isl-actions,
    .isl-report-tools,
    .isl-legend {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .isl-actions .btn,
    .isl-filter-card .btn,
    .isl-report-tools .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
    }

    .isl-date {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 40px;
        padding: 8px 12px;
        color: #075d80;
        background: #e7f7fc;
        border: 1px solid #d0eef8;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
    }

    .isl-kpi {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 92px;
        padding: 16px;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .isl-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .09);
    }

    .isl-kpi-icon {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        border-radius: 13px;
        font-size: 24px;
    }

    .isl-kpi-icon.is-distributor { color: #0e7490; background: #cffafe; }
    .isl-kpi-icon.is-product { color: #4338ca; background: #e0e7ff; }
    .isl-kpi-icon.is-stock { color: #15803d; background: #dcfce7; }
    .isl-kpi-icon.is-alert { color: #c2410c; background: #ffedd5; }

    .isl-kpi small {
        display: block;
        margin-bottom: 3px;
        color: #728197;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .isl-kpi strong {
        display: block;
        color: #172033;
        font-size: clamp(19px, 2vw, 23px);
        line-height: 1.1;
    }

    .isl-filter-card {
        padding: 18px;
        margin-bottom: 16px;
    }

    .isl-filter-card .form-label {
        margin-bottom: 6px;
        color: #46556a;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .isl-filter-card .form-control,
    .isl-filter-card .form-select,
    .isl-table-search {
        min-height: 42px;
        border-color: #dce5ef;
        border-radius: 9px;
        font-size: 13px;
    }

    .isl-filter-card .form-control:focus,
    .isl-filter-card .form-select:focus,
    .isl-table-search:focus {
        border-color: #67b8d5;
        box-shadow: 0 0 0 3px rgba(21, 152, 198, .12);
    }

    .isl-formula {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 9px 12px;
        color: #5c6b7e;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 9px;
        font-size: 12px;
    }

    .isl-report-card {
        overflow: hidden;
    }

    .isl-report-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 20px;
        border-bottom: 1px solid #e7edf4;
    }

    .isl-report-head h5 {
        margin: 0 0 3px;
        color: #172033;
        font-size: 16px;
        font-weight: 800;
    }

    .isl-legend {
        gap: 13px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .isl-legend span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .isl-legend i {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .isl-legend .is-good { background: #22c55e; }
    .isl-legend .is-low { background: #f59e0b; }
    .isl-legend .is-zero { background: #cbd5e1; }

    .isl-report-tools {
        padding: 12px 16px;
        background: #fbfdff;
        border-bottom: 1px solid #e7edf4;
    }

    .isl-search-wrap {
        position: relative;
        width: min(320px, 100%);
    }

    .isl-search-wrap i {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .isl-table-search {
        width: 100%;
        padding-left: 35px;
        background: #fff;
    }

    .isl-visible-count {
        margin-left: auto;
        color: var(--isl-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .isl-scroll-hint {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .isl-table-wrap {
        max-height: calc(100vh - 270px);
        overflow: auto;
        scrollbar-color: #b8c4d1 #f3f6f9;
        scrollbar-width: thin;
        overscroll-behavior: contain;
    }

    .isl-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .isl-table th,
    .isl-table td {
        padding: 10px 9px;
        background: #fff;
        border-right: 1px solid #e8edf3;
        border-bottom: 1px solid #e8edf3;
        vertical-align: middle;
    }

    .isl-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        color: #fff;
        background: #506579;
        text-align: center;
        font-weight: 800;
    }

    .isl-table tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .isl-table tbody tr:hover td {
        background: #eef9fc;
    }

    .isl-meta {
        text-align: left !important;
    }

    .isl-col-region { left: 0; width: 75px; min-width: 75px; }
    .isl-col-id { left: 75px; width: 175px; min-width: 175px; }
    .isl-col-business { left: 250px; width: 210px; min-width: 210px; }
    .isl-col-type { left: 460px; width: 140px; min-width: 140px; }

    .isl-table thead .isl-meta {
        z-index: 9;
        background: #3d5368;
    }

    .isl-table tbody .isl-meta {
        position: sticky;
        z-index: 3;
    }

    .isl-table tbody tr:nth-child(odd) .isl-meta { background: #fff; }
    .isl-table tbody tr:nth-child(even) .isl-meta { background: #fbfdff; }
    .isl-table tbody tr:hover .isl-meta { background: #eef9fc; }

    .isl-col-type {
        box-shadow: 7px 0 14px rgba(15, 23, 42, .06);
    }

    .isl-col-id strong,
    .isl-col-business strong {
        display: block;
        overflow: hidden;
        color: #24364b;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .isl-col-id small,
    .isl-col-business small {
        display: block;
        overflow: hidden;
        margin-top: 3px;
        color: #8290a3;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .isl-account-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .isl-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        background: #22c55e;
        border-radius: 50%;
    }

    .isl-dot.is-inactive {
        background: #f59e0b;
    }

    .isl-region {
        display: inline-grid;
        place-items: center;
        min-width: 38px;
        padding: 5px 7px;
        color: #075d80;
        background: #e5f6fc;
        border-radius: 7px;
        font-weight: 900;
    }

    .isl-product-column {
        width: 82px;
        min-width: 82px;
        height: 150px;
        padding: 8px 4px !important;
        background: #60778c !important;
        vertical-align: bottom !important;
    }

    .isl-product-head {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-direction: column;
        gap: 5px;
        height: 132px;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
    }

    .isl-product-head span {
        color: #d8f5ff;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .4px;
    }

    .isl-product-head strong {
        display: block;
        overflow: hidden;
        max-height: 112px;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-overflow: ellipsis;
    }

    .isl-qty {
        min-width: 82px;
        text-align: center;
        font-variant-numeric: tabular-nums;
        font-weight: 800;
    }

    .isl-qty.is-good { color: #166534; background: #f0fdf4 !important; }
    .isl-qty.is-low { color: #9a5b06; background: #fffbeb !important; }
    .isl-qty.is-zero { color: #94a3b8; background: #f8fafc !important; }

    .isl-total-column {
        position: sticky !important;
        right: 0;
        z-index: 4 !important;
        width: 90px;
        min-width: 90px;
        color: #fff !important;
        background: var(--isl-teal) !important;
        text-align: center !important;
        box-shadow: -4px 0 12px rgba(15, 23, 42, .08);
    }

    .isl-table tbody .isl-total-column {
        color: #0f5f5a !important;
        background: #eafaf7 !important;
    }

    .isl-empty {
        height: 220px;
        color: #7b8a9d;
        text-align: center;
    }

    .isl-empty i,
    .isl-empty strong,
    .isl-empty span {
        display: block;
    }

    .isl-empty i { margin-bottom: 8px; font-size: 34px; }
    .isl-empty strong { color: #42526a; font-size: 15px; }
    .isl-empty span { margin-top: 4px; }

    .isl-row-hidden {
        display: none;
    }

    .isl-ledger-card { margin-top: 16px; }
    .isl-ledger-sheet-title { display: flex; align-items: stretch; overflow: hidden; border-bottom: 1px solid #e7edf4; }
    .isl-ledger-sheet-title strong { padding: 12px 16px; color: #172033; font-size: 15px; }
    .isl-ledger-sku { min-width: 210px; padding: 12px 16px; color: #172033; background: #fff500; font-size: 12px; font-weight: 900; text-align: center; }
    .isl-ledger-wrap { max-height: 520px; overflow: auto; overscroll-behavior: contain; }
    .isl-ledger-table { width: 100%; min-width: 760px; border-collapse: separate; border-spacing: 0; font-size: 12px; }
    .isl-ledger-table th, .isl-ledger-table td { padding: 12px 14px; border-bottom: 1px solid #e8edf3; vertical-align: middle; }
    .isl-ledger-table th { position: sticky; top: 0; z-index: 2; color: #fff; background: #506579; font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .isl-ledger-table tbody tr:hover td { background: #eef9fc; }
    .isl-ledger-type { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 999px; color: #075d80; background: #e5f6fc; font-size: 10px; font-weight: 900; }
    .isl-ledger-type.is-out { color: #9a3412; background: #ffedd5; }
    .isl-ledger-type.is-transfer { color: #5b21b6; background: #ede9fe; }
    .isl-ledger-qty { color: #166534; font-weight: 900; font-variant-numeric: tabular-nums; }
    .isl-ledger-qty.is-out { color: #b42318; }
    .isl-ledger-balance { color: #0f5f5a; font-size: 13px; font-weight: 900; font-variant-numeric: tabular-nums; }
    .isl-ledger-remarks { min-width: 280px; color: #667085; line-height: 1.45; }

    @media (max-width: 1199.98px) {
        .isl-visible-count {
            width: 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 991.98px) {
        .isl-hero,
        .isl-report-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .isl-table tbody .isl-meta,
        .isl-table thead .isl-meta {
            position: static;
        }

        .isl-col-type {
            box-shadow: none;
        }

        .isl-table-wrap {
            max-height: 65vh;
        }
    }

    @media (max-width: 575.98px) {
        .isl-page { padding-inline: 4px; }
        .isl-hero { padding: 18px; }
        .isl-actions { width: 100%; }
        .isl-actions .btn { flex: 1 1 auto; }
        .isl-date { width: 100%; justify-content: center; }
        .isl-report-tools { align-items: stretch; flex-direction: column; }
        .isl-search-wrap { width: 100%; }
        .isl-scroll-hint { display: none; }
        .isl-ledger-sheet-title { display: block; }
        .isl-ledger-sheet-title strong, .isl-ledger-sku { display: block; width: 100%; }
    }

    @media print {
        .sidebar,
        .topbar,
        .footer,
        .isl-actions,
        .isl-filter-card,
        .isl-report-tools {
            display: none !important;
        }

        .main-content,
        .content-area {
            position: static !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .isl-page { padding: 0; }
        .isl-hero { padding: 8px 10px; margin-bottom: 8px; }
        .isl-hero::after, .isl-eyebrow { display: none; }
        .isl-hero h3 { font-size: 16px; }
        .isl-kpi { min-height: 55px; padding: 8px; box-shadow: none; }
        .isl-kpi-icon { width: 30px; height: 30px; font-size: 15px; }
        .isl-kpi strong { font-size: 13px; }
        .isl-hero, .isl-card, .isl-kpi { box-shadow: none; }
        .isl-report-head { padding: 8px 10px; }
        .isl-table-wrap { max-height: none; overflow: visible; }
        .isl-table { width: 100%; font-size: 7px; }
        .isl-table th, .isl-table td { padding: 3px; }
        .isl-table thead th,
        .isl-table tbody .isl-meta,
        .isl-total-column {
            position: static !important;
        }
        .isl-product-column { width: 38px; min-width: 38px; height: 95px; }
        .isl-product-head { height: 82px; }
        .isl-row-hidden { display: table-row; }
        .isl-client-empty { display: none !important; }

        @page {
            size: landscape;
            margin: 8mm;
        }
    }
</style>
@endsection

@section('content')
@php
    $columnCount = 5 + $products->count();
    $hasFilters = collect(['as_of', 'region', 'status', 'availability', 'distributor', 'product', 'low_stock'])
        ->contains(fn ($filter) => request()->filled($filter));
@endphp

<div class="container-fluid isl-page">
    <section class="isl-hero">
        <div class="isl-hero-copy">
            <div class="isl-eyebrow">Inventory Intelligence</div>
            <h3>Stock Inventory Sheet</h3>
            <p>Consolidated available stock for Area Distributors by product as of {{ $asOf->format('F d, Y') }}.</p>
        </div>
        <div class="isl-actions">
            <span class="isl-date">
                <i class="ti ti-calendar-stats" aria-hidden="true"></i>
                {{ $asOf->format('M d, Y') }}
            </span>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="ti ti-printer" aria-hidden="true"></i>
                Print
            </button>
            <a href="{{ route('stock-inventory.export', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-spreadsheet" aria-hidden="true"></i>
                Export Excel
            </a>
        </div>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <article class="isl-kpi">
                <span class="isl-kpi-icon is-distributor"><i class="ti ti-building-store" aria-hidden="true"></i></span>
                <div>
                    <small>Distributors</small>
                    <strong>{{ number_format($summary->distributors) }}</strong>
                </div>
            </article>
        </div>
        <div class="col-xl-3 col-md-6">
            <article class="isl-kpi">
                <span class="isl-kpi-icon is-product"><i class="ti ti-packages" aria-hidden="true"></i></span>
                <div>
                    <small>Products</small>
                    <strong>{{ number_format($summary->products) }}</strong>
                </div>
            </article>
        </div>
        <div class="col-xl-3 col-md-6">
            <article class="isl-kpi">
                <span class="isl-kpi-icon is-stock"><i class="ti ti-stack-2" aria-hidden="true"></i></span>
                <div>
                    <small>Total Available Stock</small>
                    <strong>{{ number_format($summary->total_stock) }}</strong>
                </div>
            </article>
        </div>
        <div class="col-xl-3 col-md-6">
            <article class="isl-kpi">
                <span class="isl-kpi-icon is-alert"><i class="ti ti-alert-triangle" aria-hidden="true"></i></span>
                <div>
                    <small>Low / No-stock Cells</small>
                    <strong>{{ number_format($summary->low_stock_cells + $summary->out_of_stock_cells) }}</strong>
                </div>
            </article>
        </div>
    </div>

    <section class="isl-card isl-filter-card" aria-labelledby="inventoryFilters">
        <form method="GET" action="{{ route('stock-inventory') }}" class="row g-3 align-items-end">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <h5 id="inventoryFilters" class="mb-0 fw-bold fs-6">Report Filters</h5>
                    @if($hasFilters)
                        <span class="badge rounded-pill text-bg-primary">Filters applied</span>
                    @endif
                </div>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islAsOf">As of date</label>
                <input id="islAsOf" type="date" name="as_of" class="form-control" value="{{ request('as_of', $asOf->format('Y-m-d')) }}">
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islRegion">Region</label>
                <select id="islRegion" name="region" class="form-select">
                    <option value="">All regions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}" {{ request('region') === $region ? 'selected' : '' }}>{{ $region }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islStatus">Account status</label>
                <select id="islStatus" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islAvailability">Stock status</label>
                <select id="islAvailability" name="availability" class="form-select">
                    <option value="">All stock levels</option>
                    <option value="in_stock" {{ request('availability') === 'in_stock' ? 'selected' : '' }}>Healthy stock</option>
                    <option value="low_stock" {{ request('availability') === 'low_stock' ? 'selected' : '' }}>Low stock</option>
                    <option value="out_of_stock" {{ request('availability') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islDistributor">Distributor</label>
                <input id="islDistributor" type="search" name="distributor" class="form-control" value="{{ request('distributor') }}" placeholder="ID or business name">
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islProduct">Product / SKU</label>
                <select id="islProduct" name="product" class="form-select">
                    <option value="">All products</option>
                    @foreach($productOptions as $productOption)
                        <option value="{{ $productOption->sku ?: $productOption->product_name }}" {{ request('product') === ($productOption->sku ?: $productOption->product_name) ? 'selected' : '' }}>
                            {{ $productOption->sku ? $productOption->sku . ' — ' : '' }}{{ $productOption->product_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="form-label" for="islThreshold">Low-stock threshold</label>
                <input id="islThreshold" type="number" min="1" max="9999" name="low_stock" class="form-control" value="{{ $lowStockThreshold }}">
            </div>
            <div class="col-xl-4 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="ti ti-filter" aria-hidden="true"></i>
                    Apply filters
                </button>
                <a href="{{ route('stock-inventory') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-refresh" aria-hidden="true"></i>
                    Reset
                </a>
            </div>
            <div class="col-xl-6">
                <div class="isl-formula">
                    <i class="ti ti-calculator" aria-hidden="true"></i>
                    Available = completed purchase orders + inventory IN &minus; inventory OUT &minus; completed dealer/guest orders
                </div>
            </div>
        </form>
    </section>

    <section class="isl-card isl-report-card" aria-labelledby="inventoryMatrix">
        <header class="isl-report-head">
            <div>
                <h5 id="inventoryMatrix">Area Distributor Stock Matrix</h5>
                <p>{{ $rows->count() }} distributor{{ $rows->count() === 1 ? '' : 's' }} across {{ $products->count() }} product{{ $products->count() === 1 ? '' : 's' }}</p>
            </div>
            <div class="isl-legend" aria-label="Stock level legend">
                <span><i class="is-good"></i> Healthy</span>
                <span><i class="is-low"></i> Low (1–{{ number_format($lowStockThreshold) }})</span>
                <span><i class="is-zero"></i> No stock</span>
            </div>
        </header>

        <div class="isl-report-tools">
            <div class="isl-search-wrap">
                <i class="ti ti-search" aria-hidden="true"></i>
                <input id="islTableSearch" type="search" class="form-control isl-table-search" placeholder="Search visible distributors…" autocomplete="off">
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="islJumpTotal">
                <i class="ti ti-arrow-bar-to-right" aria-hidden="true"></i>
                Jump to totals
            </button>
            <span class="isl-scroll-hint"><i class="ti ti-arrows-horizontal"></i> Scroll horizontally to review every product</span>
            <span class="isl-visible-count" id="islVisibleCount">{{ $rows->count() }} visible row{{ $rows->count() === 1 ? '' : 's' }}</span>
        </div>

        <div class="isl-table-wrap" id="islTableWrap" tabindex="0" aria-label="Scrollable inventory stock level table">
            <table class="isl-table" id="islTable">
                <thead>
                    <tr>
                        <th class="isl-meta isl-col-region" scope="col">Region</th>
                        <th class="isl-meta isl-col-id" scope="col">Authorized Distributor ID</th>
                        <th class="isl-meta isl-col-business" scope="col">IL Business Name</th>
                        <th class="isl-meta isl-col-type" scope="col">Customer Type</th>
                        @foreach($products as $product)
                            <th class="isl-product-column" scope="col" title="{{ $product->product_name }}">
                                <div class="isl-product-head">
                                    <span>{{ $product->sku ?: 'NO SKU' }}</span>
                                    <strong>{{ $product->product_name }}</strong>
                                </div>
                            </th>
                        @endforeach
                        <th class="isl-total-column" scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr data-isl-row data-search="{{ strtolower(implode(' ', [
                            $row->region,
                            $row->region_name,
                            $row->distributor_id,
                            $row->business_name,
                            $row->customer_type,
                            $row->status,
                            $row->territories->implode(' '),
                        ])) }}">
                            <td class="isl-meta isl-col-region">
                                <span class="isl-region" title="{{ $row->region_name }}">{{ $row->region }}</span>
                            </td>
                            <td class="isl-meta isl-col-id">
                                <strong title="{{ $row->distributor_id }}">{{ $row->distributor_id }}</strong>
                                <small title="{{ $row->territories->implode(', ') }}">
                                    {{ $row->territories->isNotEmpty() ? $row->territories->implode(', ') : 'No territory' }}
                                </small>
                            </td>
                            <td class="isl-meta isl-col-business">
                                <strong title="{{ $row->business_name }}">{{ $row->business_name ?: 'Unnamed business' }}</strong>
                                <small class="isl-account-status">
                                    <span class="isl-dot {{ strtolower((string) $row->status) === 'active' ? '' : 'is-inactive' }}"></span>
                                    {{ $row->status }}
                                </small>
                            </td>
                            <td class="isl-meta isl-col-type">{{ $row->customer_type }}</td>
                            @foreach($products as $product)
                                @php
                                    $qty = (float) $row->stock->get($product->key, 0);
                                    $stockClass = $qty <= 0 ? 'is-zero' : ($qty <= $lowStockThreshold ? 'is-low' : 'is-good');
                                @endphp
                                <td class="isl-qty {{ $stockClass }}" title="{{ $product->product_name }}: {{ number_format($qty) }}">
                                    {{ number_format($qty) }}
                                </td>
                            @endforeach
                            <td class="isl-total-column"><strong>{{ number_format($row->total_stock) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" class="isl-empty">
                                <i class="ti ti-package-off" aria-hidden="true"></i>
                                <strong>No inventory records found</strong>
                                <span>Try changing the filters or report date.</span>
                            </td>
                        </tr>
                    @endforelse
                    @if($rows->isNotEmpty())
                        <tr id="islClientEmpty" class="isl-row-hidden isl-client-empty">
                            <td colspan="{{ $columnCount }}" class="isl-empty">
                                <i class="ti ti-search-off" aria-hidden="true"></i>
                                <strong>No matching distributor</strong>
                                <span>Try another table search.</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <section class="isl-card isl-ledger-card" aria-labelledby="movementLedgerTitle">
        <div class="isl-ledger-sheet-title">
            <strong id="movementLedgerTitle">AD Stock Inventory Sheet</strong>
            <span class="isl-ledger-sku">Per SKU: {{ $ledgerSkuLabel }}</span>
        </div>
        <header class="isl-report-head">
            <div>
                <h5>Stock Movement Ledger</h5>
                <p>Select a Product / SKU above to review that product’s running balance, like a stock card.</p>
            </div>
            <span class="isl-visible-count m-0">{{ number_format($movementLedger->count()) }} movement{{ $movementLedger->count() === 1 ? '' : 's' }}</span>
        </header>
        <div class="isl-ledger-wrap" tabindex="0" aria-label="Scrollable stock movement ledger">
            <table class="isl-ledger-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference #</th>
                        <th>Movement Type</th>
                        <th class="text-end">Qty In</th>
                        <th class="text-end">Qty Out</th>
                        <th class="text-end">Balance</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!request()->filled('product'))
                        <tr>
                            <td colspan="7" class="isl-empty">
                                <i class="ti ti-package-search" aria-hidden="true"></i>
                                <strong>Select a product or SKU</strong>
                                <span>Choose one from the Product / SKU filter to open its stock inventory sheet.</span>
                            </td>
                        </tr>
                    @else
                    @forelse($movementLedger as $movement)
                        @php
                            $movementClass = strtolower($movement->direction);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($movement->date)->format('M d, Y') }}</td>
                            <td>{{ $movement->reference_no }}</td>
                            <td><span class="isl-ledger-type {{ $movementClass === 'out' ? 'is-out' : ($movementClass === 'transfer' ? 'is-transfer' : '') }}">{{ $movement->movement_type }}</span></td>
                            <td class="text-end isl-ledger-qty">{{ $movement->qty_in > 0 ? number_format($movement->qty_in) : '—' }}</td>
                            <td class="text-end isl-ledger-qty is-out">{{ $movement->qty_out > 0 ? number_format($movement->qty_out) : '—' }}</td>
                            <td class="text-end isl-ledger-balance">{{ number_format($movement->balance) }}</td>
                            <td class="isl-ledger-remarks">{{ $movement->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="isl-empty">
                                <i class="ti ti-notes-off" aria-hidden="true"></i>
                                <strong>No stock movements found</strong>
                                <span>Inventory IN, OUT, and transfer entries will appear here.</span>
                            </td>
                        </tr>
                    @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('islTableSearch');
        const tableWrap = document.getElementById('islTableWrap');
        const totalHeader = document.querySelector('.isl-total-column');
        const jumpButton = document.getElementById('islJumpTotal');
        const visibleCount = document.getElementById('islVisibleCount');
        const clientEmpty = document.getElementById('islClientEmpty');
        const rows = Array.from(document.querySelectorAll('[data-isl-row]'));

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let count = 0;

                rows.forEach(function (row) {
                    const matches = !query || row.dataset.search.includes(query);
                    row.classList.toggle('isl-row-hidden', !matches);
                    if (matches) count++;
                });

                if (visibleCount) {
                    visibleCount.textContent = count + ' visible row' + (count === 1 ? '' : 's');
                }

                if (clientEmpty) {
                    clientEmpty.classList.toggle('isl-row-hidden', count !== 0);
                }
            });
        }

        if (jumpButton && tableWrap && totalHeader) {
            jumpButton.addEventListener('click', function () {
                tableWrap.scrollTo({
                    left: tableWrap.scrollWidth,
                    behavior: 'smooth'
                });
            });
        }
    });
</script>
@endsection
