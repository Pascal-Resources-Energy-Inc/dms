@extends('layouts.header')
<link rel="icon" type="image/png" href="{{asset('images/logo_nya.png')}}">
@section('css')
<style>
    .dealer-page {
        padding-top: 18px;
        padding-bottom: 28px;
    }

    .dealer-head,
    .dealer-stat,
    .dealer-table-card {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .06);
    }

    .dealer-head {
        padding: 20px 22px;
        margin-bottom: 16px;
        border-left: 5px solid #2563eb;
    }

    .dealer-eyebrow {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .dealer-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
    }

    .dealer-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        border-radius: 8px;
        font-weight: 700;
    }

    .dealer-stat {
        min-height: 102px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dealer-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 24px;
    }

    .dealer-stat-icon.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .dealer-stat-icon.is-total {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .dealer-stat-icon.is-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .dealer-stat-value {
        color: #111827;
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
    }

    .dealer-stat-label {
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .dealer-table-card .card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #edf0f5;
    }

    .dealer-table-card .card-body {
        padding: 0;
    }

    .dealer-tabs {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .dealer-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        padding: 7px 13px;
        border: 0;
        border-radius: 7px;
        color: #64748b;
        background: transparent;
        font-size: 12px;
        font-weight: 800;
        transition: color .18s ease, background-color .18s ease, box-shadow .18s ease;
    }

    .dealer-tab:hover {
        color: #1d4ed8;
        background: #fff;
    }

    .dealer-tab.active {
        color: #1d4ed8;
        background: #fff;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .08);
    }

    .dealer-tab[data-dealer-tab="Regular"].active {
        color: #047857;
    }

    .dealer-tab[data-dealer-tab="admin_crms"].active {
        color: #1d4ed8;
    }

    .dealer-tab[data-dealer-tab="admin_crms2"].active {
        color: #7c3aed;
    }

    .dealer-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 23px;
        height: 23px;
        padding: 0 7px;
        border-radius: 999px;
        color: inherit;
        background: #e2e8f0;
        font-size: 11px;
    }

    .dealer-tab.active .dealer-tab-count {
        background: #dbeafe;
    }

    .dealer-tab[data-dealer-tab="Regular"].active .dealer-tab-count {
        background: #d1fae5;
    }

    .dealer-tab[data-dealer-tab="admin_crms2"].active .dealer-tab-count {
        background: #ede9fe;
    }

    .dealer-table {
        margin-bottom: 0 !important;
    }

    .dealer-table thead th {
        vertical-align: middle !important;
        background: #f3f6fa;
        border-color: #e5e7eb;
        color: #4b5563;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .dealer-table td {
        border-color: #edf0f5;
        vertical-align: middle;
        color: #374151;
        font-size: 13px;
    }

    .dealer-table tbody tr:hover {
        background: #f8fafc;
    }

    .dealer-link {
        color: #111827;
        font-weight: 800;
        text-decoration: none;
    }

    .dealer-link:hover {
        color: #2563eb;
        text-decoration: none;
    }

    .dealer-ref {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        color: #1d4ed8;
        background: #dbeafe;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .dealer-action-btn {
        width: 34px;
        height: 34px;
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        background: #fff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .dealer-action-btn:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .dealer-info-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
        margin-bottom: 16px;
    }

    .dealer-info-person {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .dealer-info-avatar {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 20px;
        font-weight: 900;
    }

    .dealer-info-title {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.2;
    }

    .dealer-info-subtitle {
        margin-top: 5px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .dealer-info-badges {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .dealer-info-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .dealer-info-badge.is-source {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .dealer-info-badge.is-status-active {
        color: #166534;
        background: #dcfce7;
    }

    .dealer-info-badge.is-status-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .dealer-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .dealer-info-item {
        border: 1px solid #edf0f5;
        border-radius: 8px;
        background: #f8fafc;
        padding: 11px 12px;
    }

    .dealer-info-item span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .dealer-info-item strong {
        display: block;
        margin-top: 4px;
        color: #111827;
        font-size: 13px;
        word-break: break-word;
    }

    .dealer-info-item.is-wide {
        grid-column: 1 / -1;
    }

    .dealer-metric {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .dealer-metric.is-stock {
        color: #166534;
        background: #dcfce7;
    }

    .dealer-metric.is-sold {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .dealer-metric.is-remaining {
        color: #0f766e;
        background: #ccfbf1;
    }

    .dealer-metric.is-negative {
        color: #991b1b;
        background: #fee2e2;
    }

    .dealer-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 76px;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .dealer-status.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .dealer-status.is-inactive {
        color: #991b1b;
        background: #fee2e2;
    }

    .dealer-muted {
        color: #64748b;
        max-width: 260px;
        white-space: normal;
    }

    .dataTables_wrapper {
        padding: 14px;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 8px;
    }

    .dataTables_length select {
        width: 64px !important;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border: 1px solid #dbe2ea;
        border-radius: 7px;
        min-height: 36px;
        padding: 4px 8px;
    }

    table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .dealer-empty-state {
        padding: 48px 18px;
        text-align: center;
        color: #64748b;
    }

    .dealer-empty-state i {
        display: block;
        margin-bottom: 10px;
        color: #94a3b8;
        font-size: 38px;
    }

    .dealer-empty-state strong {
        display: block;
        color: #111827;
        font-size: 15px;
    }

    @media (max-width: 575px) {
        .dealer-head {
            padding: 16px;
        }

        .dealer-actions,
        .dealer-actions .btn {
            width: 100%;
        }

        .dealer-stat-value {
            font-size: 26px;
        }

        .dealer-info-grid {
            grid-template-columns: 1fr;
        }

        .dealer-info-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .dealer-info-badges {
            justify-content: flex-start;
        }
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection
@section('content')
@php
    $dealerPageTitle = $dealerPageTitle ?? 'Dealers';
    $dealerSingularTitle = $dealerSingularTitle ?? 'Dealer';
    $isAdminDealerPage = auth()->user()->role == 'Admin' && Route::currentRouteName() !== 'mds';
    $adminCrmDealers = $adminCrmDealers ?? collect();
    $adminCrm2Dealers = $adminCrm2Dealers ?? collect();
    $adminRegularDealers = $adminRegularDealers ?? collect();

    if ($isAdminDealerPage) {
        $dealerTabs = collect([
            ['key' => 'Regular', 'label' => 'Regular', 'icon' => 'ti ti-building-store', 'count' => $adminRegularDealers->count()],
            ['key' => 'admin_crms', 'label' => 'Project Rise', 'icon' => 'ti ti-database', 'count' => $adminCrmDealers->count()],
            ['key' => 'admin_crms2', 'label' => 'Project Genesis', 'icon' => 'ti ti-database-export', 'count' => $adminCrm2Dealers->count()],
        ]);
    } else {
        $projectRiseDealerCount = $dealers->filter(function ($dealer) {
            return ($dealer->source ?? null) === 'admin_crms';
        })->count();
        $projectGenesisDealerCount = $dealers->filter(function ($dealer) {
            return ($dealer->source ?? null) === 'admin_crms2';
        })->count();
        $regularDealerCount = $dealers->filter(function ($dealer) {
            return empty($dealer->is_remote) && strcasecmp((string) $dealer->dealer_type, 'Regular') === 0;
        })->count();
        $localProjectDealerCount = $dealers->filter(function ($dealer) {
            return empty($dealer->is_remote) && strcasecmp((string) ($dealer->dealer_type ?: 'Project'), 'Regular') !== 0;
        })->count();
        $dealerTabs = collect([
            ['key' => 'admin_crms', 'label' => 'Project Rise', 'icon' => 'ti ti-database', 'count' => $projectRiseDealerCount],
            ['key' => 'admin_crms2', 'label' => 'Project Genesis', 'icon' => 'ti ti-database-export', 'count' => $projectGenesisDealerCount],
            ['key' => 'Regular', 'label' => 'Regular', 'icon' => 'ti ti-building-store', 'count' => $regularDealerCount],
            ['key' => 'Project', 'label' => 'Local Project', 'icon' => 'ti ti-building-community', 'count' => $localProjectDealerCount],
        ])->filter(function ($tab) {
            return $tab['count'] > 0 || in_array($tab['key'], ['admin_crms', 'admin_crms2', 'Regular']);
        })->values();
    }

    $initialDealerTab = $dealerTabs->firstWhere('count', '>', 0) ?: $dealerTabs->first();
    $initialDealerKey = $initialDealerTab['key'] ?? 'Project';
    $initialDealerLabel = $initialDealerTab['label'] ?? 'Project';
    $initialDealerCount = $initialDealerTab['count'] ?? 0;
@endphp
<section class="dealer-page">
    <div class="dealer-head">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="dealer-eyebrow">Partner Network</div>
                <h4 class="mb-1 mt-2">{{ $dealerPageTitle }}</h4>
                <div class="text-muted">Monitor dealer status, territory, stock, and sales performance.</div>
            </div>
            <div class="dealer-actions">
                @if(auth()->user()->role == 'Admin' && Route::currentRouteName() !== 'mds')
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#new_dealer">
                        <i class="ti ti-plus"></i>
                        Add {{ $dealerSingularTitle }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="dealer-stat">
                <div class="dealer-stat-icon is-total">
                    <i class="ti ti-users"></i>
                </div>
                <div>
                    <div class="dealer-stat-value">{{ number_format($dealers->count()) }}</div>
                    <div class="dealer-stat-label">Total {{ $dealerPageTitle }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="dealer-stat">
                <div class="dealer-stat-icon is-active">
                    <i class="ti ti-user-check"></i>
                </div>
                <div>
                    <div class="dealer-stat-value">{{ number_format($activeDealers) }}</div>
                    <div class="dealer-stat-label">Active {{ $dealerPageTitle }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="dealer-stat">
                <div class="dealer-stat-icon is-inactive">
                    <i class="ti ti-user-x"></i>
                </div>
                <div>
                    <div class="dealer-stat-value">{{ number_format($inactiveDealers) }}</div>
                    <div class="dealer-stat-label">Inactive {{ $dealerPageTitle }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-xl-12 d-flex align-items-stretch">
            <div class="card dealer-table-card w-100">
                <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h5 class="mb-0" id="dealerTableTitle">{{ $initialDealerLabel }} Dealers</h5>
                        <div class="small text-muted" id="dealerTableCount">{{ $initialDealerCount }} record{{ $initialDealerCount == 1 ? '' : 's' }} listed</div>
                    </div>
                    <div class="dealer-tabs mt-3 mt-lg-0" role="tablist" aria-label="Dealer type">
                        @foreach($dealerTabs as $tab)
                            <button type="button"
                                class="dealer-tab {{ $initialDealerKey === $tab['key'] ? 'active' : '' }}"
                                data-dealer-tab="{{ $tab['key'] }}"
                                data-dealer-tab-label="{{ $tab['label'] }}"
                                data-count="{{ $tab['count'] }}"
                                role="tab"
                                aria-selected="{{ $initialDealerKey === $tab['key'] ? 'true' : 'false' }}">
                                <i class="{{ $tab['icon'] }}"></i>
                                {{ $tab['label'] }}
                                <span class="dealer-tab-count">{{ number_format($tab['count']) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="card-body">
                    <div id="dealerEmptyState" class="dealer-empty-state" style="display: {{ $dealers->count() ? 'none' : 'block' }};">
                        <i class="ti ti-users-off"></i>
                        <strong>No dealers found</strong>
                        <span>Add a dealer to start building this list.</span>
                    </div>
                    <div class="table-responsive" id="dealerTableWrap" style="display: {{ $dealers->count() ? 'block' : 'none' }};">
                        @if(auth()->user()->role == 'Admin')
                            <table class="table dealer-table transaction-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>{{ $dealerSingularTitle }} Reference</th>
                                        <th>{{ $dealerSingularTitle }} Name</th>
                                        <th>Store Name</th>
                                        <th>Store Type</th>
                                        <th>Number</th>
                                        <th>Qty Stock</th>
                                        <th>Qty Sold</th>
                                        <th>Address</th>
                                        <th>Sales Territory</th>
                                        <th>Status</th>
                                        <th>View</th>
                                    </tr>
                                </thead>
                                <tbody id="adBody">
                                    @foreach($dealers as $dealer)
                                    @php
                                        $dealerType = strcasecmp((string) $dealer->dealer_type, 'Regular') === 0 ? 'Regular' : 'Project';
                                        $dealerTabKey = $isAdminDealerPage ? ($dealer->source ?? 'admin_crms') : $dealerType;
                                        $adminDealerViewUrl = $isAdminDealerPage
                                            ? ($dealerTabKey === 'Regular'
                                                ? url('view-dealer/' . $dealer->id)
                                                : route('admin.crm.dealer.view', ['source' => $dealerTabKey, 'id' => $dealer->id]))
                                            : null;
                                    @endphp
                                    <tr data-dealer-tab-key="{{ $dealerTabKey }}" data-dealer-type="{{ $dealerType }}">
                                        <td scope="col"><span class="dealer-ref">{{  strtoupper($dealer->dealer_reference) }}</span></td>
                                        <td scope="col">
                                            @if($isAdminDealerPage)
                                                <a href="{{ $adminDealerViewUrl }}" class="dealer-link">{{ strtoupper($dealer->name)}} </a>
                                            @else
                                                <a href='view-dealer/{{$dealer->id}}' class="dealer-link">{{ strtoupper($dealer->name)}} </a>
                                            @endif
                                        </td>
                                        <td scope="col">{{ strtoupper($dealer->store_name ?? '-')}}</td>
                                        <td scope="col">{{ strtoupper($dealer->store_type ?? '-')}}</td>
                                        <td scope="col">{{ strtoupper($dealer->number)}}</td>
                                        <td scope="col"><span class="dealer-metric is-stock">{{ number_format(($dealer->orders)->sum('qty')) }}</span></td>
                                        <td scope="col"><span class="dealer-metric is-sold">{{ number_format(($dealer->sales)->sum('qty')) }}</span></td>
                                        <td scope="col"><div class="dealer-muted">{{ strtoupper($dealer->address ?? '-') }}</div></td>
                                        <td scope="col"><div class="dealer-muted">{{ strtoupper($dealer->area ?? '-') }}</div></td>
                                        <td>
                                            @if($dealer->status == 'Active')
                                                <span class="dealer-status is-active">Active</span>
                                            @else 
                                                <span class="dealer-status is-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $dealerName = $dealer->name ?: '-';
                                                $dealerInfo = [
                                                    'source' => $dealer->source_label ?: 'Local',
                                                    'reference' => $dealer->dealer_reference ?: '-',
                                                    'name' => $dealerName,
                                                    'initials' => collect(explode(' ', $dealerName))->filter()->map(function ($part) { return strtoupper(substr($part, 0, 1)); })->take(2)->implode(''),
                                                    'store_name' => $dealer->store_name ?: '-',
                                                    'store_type' => $dealer->store_type ?: '-',
                                                    'number' => $dealer->number ?: '-',
                                                    'address' => $dealer->address ?: '-',
                                                    'area' => $dealer->area ?: '-',
                                                    'status' => $dealer->status ?: '-',
                                                ];
                                            @endphp
                                            @if($isAdminDealerPage)
                                                <a href="{{ $adminDealerViewUrl }}" class="dealer-action-btn" title="View dealer">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @else
                                                <button type="button"
                                                    class="dealer-action-btn js-view-dealer"
                                                    title="View dealer"
                                                    data-dealer="{{ e(json_encode($dealerInfo)) }}">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table> 
                        @elseif(auth()->user()->role == 'Area Distributor')
                            <table class="table dealer-table transaction-table text-center" style="width:100%">
                                <thead>
                                    <tr>
                                        <th rowspan="2">{{ $dealerSingularTitle }} Ref</th>
                                        <th rowspan="2">{{ $dealerSingularTitle }} Name</th>
                                        <th rowspan="2">Store</th>
                                        <th rowspan="2">Type</th>
                                        <th rowspan="2">Number</th>

                                        @foreach($items as $item)
                                            <th colspan="3">{{ $item->product_name }}</th>
                                        @endforeach

                                        <th rowspan="2">Address</th>
                                        <th rowspan="2">Sales Territory</th>
                                        <th rowspan="2">Status</th>
                                        <th rowspan="2">View</th>
                                    </tr>
                                    <tr>
                                        @foreach($items as $item)
                                            <th class="text-success">Stock</th>
                                            <th class="text-primary">Sold</th>
                                            <th class="text-info">Remaining</th> 
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody id="adBody">
                                    @foreach($dealers as $dealer)
                                    @php
                                        $dealerType = strcasecmp((string) $dealer->dealer_type, 'Regular') === 0 ? 'Regular' : 'Project';
                                        $dealerTabKey = !empty($dealer->is_remote) ? ($dealer->source ?? 'admin_crms') : $dealerType;
                                        $dealerName = $dealer->name ?: '-';
                                        $dealerInfo = [
                                            'source' => $dealer->source_label ?: 'Local',
                                            'reference' => $dealer->dealer_reference ?: '-',
                                            'name' => $dealerName,
                                            'initials' => collect(explode(' ', $dealerName))->filter()->map(function ($part) { return strtoupper(substr($part, 0, 1)); })->take(2)->implode(''),
                                            'store_name' => $dealer->store_name ?: '-',
                                            'store_type' => $dealer->store_type ?: '-',
                                            'number' => $dealer->number ?: '-',
                                            'address' => $dealer->address ?: '-',
                                            'area' => $dealer->area ?: '-',
                                            'status' => $dealer->status ?: '-',
                                        ];
                                    @endphp
                                    <tr data-dealer-tab-key="{{ $dealerTabKey }}" data-dealer-type="{{ $dealerType }}">
                                        <td><span class="dealer-ref">{{ $dealer->dealer_reference }}</span></td>

                                        <td>
                                            @if(!empty($dealer->is_remote))
                                                <button type="button"
                                                    class="dealer-link btn btn-link p-0 js-view-dealer"
                                                    data-dealer="{{ e(json_encode($dealerInfo)) }}">
                                                    {{ $dealer->name }}
                                                </button>
                                            @else
                                                <a href="view-dealer/{{$dealer->id}}" class="dealer-link">
                                                    {{ $dealer->name }}
                                                </a>
                                            @endif
                                        </td>

                                        <td>{{ strtoupper($dealer->store_name ?? '-') }}</td>
                                        <td>{{ strtoupper($dealer->store_type ?? '-') }}</td>
                                        <td>{{ $dealer->number }}</td>

                                        @foreach($items as $item)
                                            @php
                                                $stock = optional($dealer->orders->firstWhere('item', $item->product_name))->total_qty ?? 0;
                                                $sold  = optional($dealer->sales->firstWhere('item', $item->product_name))->total_qty ?? 0;
                                                $remaining = $stock - $sold;
                                            @endphp

                                            <!-- STOCK -->
                                            <td>
                                                <span class="dealer-metric is-stock">{{ number_format($stock) }}</span>
                                            </td>

                                            <!-- SOLD -->
                                            <td>
                                                <span class="dealer-metric is-sold">{{ number_format($sold) }}</span>
                                            </td>

                                            <!-- REMAINING -->
                                            <td>
                                                <span class="dealer-metric {{ $remaining < 0 ? 'is-negative' : 'is-remaining' }}">{{ number_format($remaining) }}</span>
                                            </td>
                                        @endforeach

                                        <td><div class="dealer-muted">{{ strtoupper($dealer->address ?? '-') }}</div></td>
                                        <td><div class="dealer-muted">{{ strtoupper($dealer->area ?? '-') }}</div></td>

                                        <td>
                                            @if($dealer->status == 'Active')
                                                <span class="dealer-status is-active">Active</span>
                                            @else 
                                                <span class="dealer-status is-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="dealer-action-btn js-view-dealer"
                                                title="View dealer"
                                                data-dealer="{{ e(json_encode($dealerInfo)) }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="dealerInfoModal" tabindex="-1" aria-labelledby="dealerInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="dealerInfoModalLabel">Dealer Information</h5>
                    <div class="small text-muted">Complete dealer profile and CRM source details</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="dealer-info-hero">
                    <div class="dealer-info-person">
                        <div class="dealer-info-avatar" id="dealerInfoInitials">DL</div>
                        <div>
                            <h4 class="dealer-info-title" id="dealerInfoName">-</h4>
                            <div class="dealer-info-subtitle">
                                <span id="dealerInfoStoreName">-</span>
                                <span class="mx-1">/</span>
                                <span id="dealerInfoReference">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="dealer-info-badges">
                        <span class="dealer-info-badge is-source" id="dealerInfoSource">Source</span>
                        <span class="dealer-info-badge" id="dealerInfoStatus">Status</span>
                    </div>
                </div>

                <div class="dealer-info-grid">
                    <div class="dealer-info-item"><span>Store Type</span><strong id="dealerInfoStoreType">-</strong></div>
                    <div class="dealer-info-item"><span>Contact Number</span><strong id="dealerInfoNumber">-</strong></div>
                    <div class="dealer-info-item"><span>Sales Territory</span><strong id="dealerInfoArea">-</strong></div>
                    <div class="dealer-info-item"><span>CRM Source</span><strong id="dealerInfoSourceDetail">-</strong></div>
                    <div class="dealer-info-item is-wide"><span>Address</span><strong id="dealerInfoAddress">-</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@include('new_dealer')
@section('javascript')
<script>
    $(document).ready(async function () {
        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.src = src;
                script.async = false;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        async function ensureDataTables() {
            if ($.fn && $.fn.DataTable) {
                return true;
            }

            const sources = [
                "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                "https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"
            ];

            for (let i = 0; i < sources.length; i++) {
                try {
                    await loadScript(sources[i]);

                    if ($.fn && $.fn.DataTable) {
                        break;
                    }
                } catch (error) {
                    console.warn('Unable to load DataTables from:', sources[i]);
                }
            }

            if (!$.fn || !$.fn.DataTable) {
                return false;
            }

            try {
                await loadScript("{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}");
            } catch (error) {
                console.warn('DataTables Bootstrap integration did not load. Continuing with core DataTables.');
            }

            return true;
        }

        let activeDealerType = $('.dealer-tab.active').data('dealer-tab') || 'Project';
        const $dealerTable = $('.transaction-table');
        const $dealerTableBody = $dealerTable.find('tbody');
        const hasDealerRows = $dealerTableBody.find('tr').length > 0;
        const allDealerRows = $dealerTableBody.find('tr').detach().toArray();

        function updateDealerHeader(label, count) {
            $('#dealerTableTitle').text(label + ' Dealers');
            $('#dealerTableCount').text(
                count.toLocaleString() + ' record' + (count === 1 ? '' : 's') + ' listed'
            );
        }

        function setDealerEmptyState(label, count) {
            const hasRows = count > 0;

            $('#dealerTableWrap').toggle(hasRows);
            $('#dealerEmptyState')
                .toggle(!hasRows)
                .find('strong')
                .text(hasRows ? 'No dealers found' : 'No ' + label.toLowerCase() + ' dealers found');
            $('#dealerEmptyState span').text(
                hasRows ? '' : 'Switch tabs or add a dealer to populate this list.'
            );
        }

        function activateDealerTab($tab) {
            activeDealerType = $tab.data('dealer-tab');
            const label = $tab.data('dealer-tab-label') || activeDealerType;
            const count = Number($tab.data('count') || 0);

            $('.dealer-tab')
                .removeClass('active')
                .attr('aria-selected', 'false');
            $tab
                .addClass('active')
                .attr('aria-selected', 'true');

            updateDealerHeader(label, count);
            setDealerEmptyState(label, count);

            return count;
        }

        function renderDealerRowsForActiveTab() {
            const matchingRows = allDealerRows.filter(function (row) {
                return row.getAttribute('data-dealer-tab-key') === activeDealerType;
            });

            $dealerTableBody.empty().append(matchingRows);
        }

        function showDealerInfo(dealer) {
            const status = dealer.status || '-';
            const isActive = String(status).toLowerCase() === 'active';

            $('#dealerInfoSource').text(dealer.source || 'Local');
            $('#dealerInfoSourceDetail').text(dealer.source || 'Local');
            $('#dealerInfoInitials').text(dealer.initials || 'DL');
            $('#dealerInfoReference').text(dealer.reference || '-');
            $('#dealerInfoName').text(dealer.name || '-');
            $('#dealerInfoStoreName').text(dealer.store_name || '-');
            $('#dealerInfoStoreType').text(dealer.store_type || '-');
            $('#dealerInfoNumber').text(dealer.number || '-');
            $('#dealerInfoStatus')
                .text(status)
                .removeClass('is-status-active is-status-inactive')
                .addClass(isActive ? 'is-status-active' : 'is-status-inactive');
            $('#dealerInfoArea').text(dealer.area || '-');
            $('#dealerInfoAddress').text(dealer.address || '-');

            const modalElement = document.getElementById('dealerInfoModal');

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            } else {
                $('#dealerInfoModal').modal('show');
            }
        }

        $(document).on('click', '.js-view-dealer', function () {
            let dealer = {};

            try {
                dealer = JSON.parse($(this).attr('data-dealer') || '{}');
            } catch (error) {
                dealer = {};
            }

            showDealerInfo(dealer);
        });

        if (!hasDealerRows) {
            $('.dealer-tab').on('click', function () {
                activateDealerTab($(this));
            });

            if (typeof initSelect2 === 'function') {
                initSelect2();
            }
            return;
        }

        if (!await ensureDataTables()) {
            console.error('DataTables failed to load.');
            $('.dealer-tab').on('click', function () {
                activateDealerTab($(this));
            });

            if (typeof initSelect2 === 'function') {
                initSelect2();
            }
            return;
        }

        renderDealerRowsForActiveTab();

        const dealerTable = $dealerTable.DataTable({
            pageLength: 10,
            autoWidth: false,
            language: {
                search: 'Search dealers:',
                lengthMenu: 'Show _MENU_ records',
                emptyTable: 'No dealers found.'
            }
        });

        setDealerEmptyState(activeDealerType, Number($('.dealer-tab.active').data('count') || 0));

        $('.dealer-tab').on('click', function () {
            activateDealerTab($(this));
            dealerTable.clear();
            dealerTable.rows.add($(allDealerRows).filter(function () {
                return this.getAttribute('data-dealer-tab-key') === activeDealerType;
            })).search('').page('first').draw();
        });

        if (typeof initSelect2 === 'function') {
            initSelect2();
        }
    });

    
    $('#new_dealer').on('shown.bs.modal', function () {
        if (!map) {
            initMap();
        } else {
            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        }
    });
</script>
@endsection
