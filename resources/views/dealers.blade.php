@extends('layouts.header')

@section('content')
    <style>
        .dealer-page { max-width: 1600px; margin: 0 auto; padding: 4px 0 24px; }
        .dealer-head { margin-bottom: 22px; padding: 26px 28px; color: #fff; background: linear-gradient(120deg, #0b3f73 0%, #0f6ba8 58%, #48a6c6 100%); border-radius: 18px; box-shadow: 0 16px 34px rgba(14, 74, 125, .2); }
        .dealer-head h4 { color: #fff; font-size: clamp(1.35rem, 2.5vw, 1.8rem); font-weight: 800; letter-spacing: -.02em; }
        .dealer-head .text-muted { color: rgba(255,255,255,.82) !important; }
        .dealer-eyebrow { color: #bdefff; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .dealer-actions .btn { min-height: 44px; padding: .6rem 1rem; border: 0; border-radius: 10px; font-weight: 800; box-shadow: 0 8px 18px rgba(0,0,0,.16); }
        .dealer-stat { height: 100%; display: flex; align-items: center; gap: 14px; padding: 18px; background: #fff; border: 1px solid #e7edf4; border-radius: 14px; box-shadow: 0 8px 20px rgba(15, 23, 42, .05); }
        .dealer-stat-icon { width: 46px; height: 46px; display: grid; place-items: center; flex: 0 0 46px; border-radius: 12px; font-size: 22px; }
        .dealer-stat-icon.is-total { color: #1667aa; background: #e8f4ff; }
        .dealer-stat-icon.is-active { color: #15803d; background: #e9f9ef; }
        .dealer-stat-icon.is-inactive { color: #b45309; background: #fff5df; }
        .dealer-stat-value { color: #102a43; font-size: 1.45rem; font-weight: 800; line-height: 1.15; }
        .dealer-stat-label { color: #64748b; font-size: .82rem; font-weight: 700; margin-top: 3px; }
        .dealer-table-card { overflow: hidden; border: 1px solid #e5edf5; border-radius: 14px; box-shadow: 0 10px 28px rgba(15, 23, 42, .06); }
        .dealer-table-card .card-header { gap: 16px; padding: 18px 22px; border-bottom: 1px solid #e8eef5; }
        .dealer-tabs { display: flex; gap: 7px; flex-wrap: wrap; }
        .dealer-tab { display: inline-flex; align-items: center; gap: 7px; min-height: 38px; padding: 7px 10px; color: #52657a; background: #f7fafc; border: 1px solid #e2eaf2; border-radius: 9px; font-size: .78rem; font-weight: 800; white-space: nowrap; transition: .18s ease; }
        .dealer-tab:hover { color: #0f5f9e; background: #edf7ff; border-color: #a9d7f5; }
        .dealer-tab.active { color: #fff; background: #0f6ba8; border-color: #0f6ba8; box-shadow: 0 7px 14px rgba(15, 107, 168, .2); }
        .dealer-tab-count { min-width: 19px; padding: 1px 5px; color: inherit; background: rgba(255,255,255,.22); border-radius: 999px; font-size: .7rem; text-align: center; }
        .dealer-tab:not(.active) .dealer-tab-count { color: #0f6ba8; background: #dff1fc; }
        .dealer-table-card .card-body { padding: 18px 22px; }
        .dealer-table thead th { padding: 12px 14px; color: #5e7288; background: #f7fafc; border-bottom: 1px solid #e5edf5; font-size: .69rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
        .dealer-table tbody td { padding: 14px; color: #334155; border-color: #edf2f7; font-size: .82rem; vertical-align: middle; }
        .dealer-table tbody tr { transition: background .15s ease; }
        .dealer-table tbody tr:hover { background: #f5fbff; }
        .dealer-ref { display: inline-block; color: #0f5f9e; font-size: .72rem; font-weight: 800; letter-spacing: .04em; }
        .dealer-link { color: #143f62; font-weight: 800; text-decoration: none; }
        .dealer-link:hover { color: #0f75b7; text-decoration: underline; }
        .dealer-muted { max-width: 180px; overflow: hidden; color: #64748b; text-overflow: ellipsis; white-space: nowrap; }
        .dealer-metric, .dealer-status { display: inline-flex; align-items: center; justify-content: center; min-height: 26px; padding: 3px 9px; border-radius: 999px; font-size: .73rem; font-weight: 800; }
        .dealer-metric.is-stock { color: #155e75; background: #e6f7fb; }
        .dealer-metric.is-sold { color: #5b3a9a; background: #f1ebff; }
        .dealer-status.is-active { color: #16713a; background: #e7f8ec; }
        .dealer-status.is-inactive { color: #a14b10; background: #fff1dd; }
        .dealer-action-btn { width: 34px; height: 34px; display: inline-grid; place-items: center; color: #0f6ba8; background: #e7f5ff; border-radius: 9px; text-decoration: none; }
        .dealer-action-btn:hover { color: #fff; background: #0f6ba8; text-decoration: none; }
        .dealer-empty-state { padding: 54px 20px; color: #64748b; text-align: center; }
        .dealer-empty-state i { display: block; margin-bottom: 10px; color: #9eb4c6; font-size: 38px; }
        .dealer-empty-state strong, .dealer-empty-state span { display: block; }
        .dealer-empty-state strong { color: #334155; margin-bottom: 4px; }
        .dealer-info-hero { display: flex; justify-content: space-between; gap: 16px; padding: 18px; background: #f4faff; border-radius: 12px; }
        .dealer-info-person { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .dealer-info-avatar { width: 46px; height: 46px; display: grid; place-items: center; flex: 0 0 46px; color: #fff; background: #0f6ba8; border-radius: 50%; font-weight: 800; }
        .dealer-info-title { margin: 0 0 3px; color: #163b59; font-size: 1rem; font-weight: 800; }
        .dealer-info-subtitle { color: #64748b; font-size: .78rem; overflow-wrap: anywhere; }
        .dealer-info-badges { display: flex; align-content: flex-start; flex-wrap: wrap; gap: 6px; }
        .dealer-info-badge { height: fit-content; padding: 4px 8px; color: #52657a; background: #e8eef4; border-radius: 999px; font-size: .72rem; font-weight: 800; }
        .dealer-info-badge.is-source { color: #0d5c94; background: #dff1fc; }
        .dealer-info-badge.is-status-active { color: #16713a; background: #e7f8ec; }
        .dealer-info-badge.is-status-inactive { color: #a14b10; background: #fff1dd; }
        .dealer-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .dealer-info-item { min-width: 0; padding: 12px; background: #f8fafc; border: 1px solid #edf2f7; border-radius: 9px; }
        .dealer-info-item.is-wide { grid-column: 1 / -1; }
        .dealer-info-item span { display: block; margin-bottom: 3px; color: #718096; font-size: .69rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .dealer-info-item strong { color: #334155; font-size: .84rem; overflow-wrap: anywhere; }
        @media (max-width: 767.98px) {
            .dealer-head { padding: 20px; border-radius: 14px; }
            .dealer-actions, .dealer-actions .btn { width: 100%; }
            .dealer-tabs { flex-wrap: nowrap; margin-right: -22px; padding-right: 22px; overflow-x: auto; scrollbar-width: none; }
            .dealer-tabs::-webkit-scrollbar { display: none; }
            .dealer-table-card .card-header, .dealer-table-card .card-body { padding: 16px; }
            .dealer-table thead { display: none; }
            .dealer-table, .dealer-table tbody, .dealer-table tr, .dealer-table td { display: block; width: 100% !important; }
            .dealer-table tbody tr { margin-bottom: 12px; padding: 5px 14px; background: #fff; border: 1px solid #e4edf5; border-radius: 11px; box-shadow: 0 4px 12px rgba(15, 23, 42, .04); }
            .dealer-table tbody tr:hover { background: #fff; }
            .dealer-table tbody td { display: flex; align-items: center; justify-content: space-between; gap: 14px; min-height: 38px; padding: 9px 0; border-bottom: 1px solid #edf2f7; text-align: right; }
            .dealer-table tbody td:last-child { border-bottom: 0; }
            .dealer-table tbody td::before { flex: 0 0 40%; color: #718096; font-size: .67rem; font-weight: 800; letter-spacing: .04em; text-align: left; text-transform: uppercase; content: attr(data-label); }
            .dealer-muted { max-width: 55%; white-space: normal; }
            .dealer-action-btn { margin-left: auto; }
            .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_length { float: none; margin: 0 0 12px; text-align: left; }
            .dataTables_wrapper .dataTables_filter input { width: 100%; margin: 6px 0 0; }
            .dataTables_wrapper .dataTables_paginate { float: none; margin-top: 12px; text-align: center; }
            .dealer-info-hero { align-items: flex-start; flex-direction: column; }
            .dealer-info-grid { grid-template-columns: 1fr; }
            .dealer-info-item.is-wide { grid-column: auto; }
        }
        @media (max-width: 420px) { .dealer-head { padding: 18px 16px; } .dealer-table-card .card-header, .dealer-table-card .card-body { padding: 14px; } .dealer-table tbody tr { padding: 5px 12px; } }
    </style>
    @php
        $dealerPageTitle = $dealerPageTitle ?? 'Dealers';
        $dealerSingularTitle = $dealerSingularTitle ?? 'Dealer';
        $isAdminDealerPage = auth()->user()->role == 'Admin' && Route::currentRouteName() !== 'mds';
        $adminCrmDealers = $adminCrmDealers ?? collect();
        $adminCrm2Dealers = $adminCrm2Dealers ?? collect();
        $adminRegularDealers = $adminRegularDealers ?? collect();
        $canCreateDealer = $canCreateDealer ?? (
            auth()->user()->role == 'Admin'
            || (auth()->user()->role == 'Area Distributor' && Route::currentRouteName() !== 'mds')
        );

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
                ['key' => 'Regular', 'label' => 'Regular', 'icon' => 'ti ti-building-store', 'count' => $regularDealerCount],
                ['key' => 'admin_crms', 'label' => 'Project Rise', 'icon' => 'ti ti-database', 'count' => $projectRiseDealerCount],
                ['key' => 'admin_crms2', 'label' => 'Project Genesis', 'icon' => 'ti ti-database-export', 'count' => $projectGenesisDealerCount],
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
                    @if($canCreateDealer)
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
                                        $dealerTabKey = !empty($dealer->is_remote)
                                            ? ($dealer->source ?? 'admin_crms')
                                            : ($isAdminDealerPage ? ($dealer->source ?? 'Regular') : $dealerType);
                                        $stockQty = $dealer->stock_qty ?? $dealer->orders->sum('qty') ?? $dealer->orders->sum('total_qty') ?? 0;
                                        $soldQty = $dealer->sold_qty ?? $dealer->sales->sum('qty') ?? $dealer->sales->sum('total_qty') ?? 0;
                                        $adminDealerViewUrl = $isAdminDealerPage
                                            ? ($dealerTabKey === 'Regular'
                                                ? url('view-dealer/' . $dealer->id)
                                                : route('admin.crm.dealer.view', ['source' => $dealerTabKey, 'id' => $dealer->id]))
                                            : null;
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
                                        <td data-label="Reference" scope="col"><span class="dealer-ref">{{ strtoupper($dealer->dealer_reference) }}</span></td>
                                        <td data-label="Dealer name" scope="col">
                                            @if($isAdminDealerPage)
                                                <a href="{{ $adminDealerViewUrl }}" class="dealer-link">{{ strtoupper($dealer->name) }}</a>
                                            @elseif(!empty($dealer->is_remote))
                                                <button type="button"
                                                    class="dealer-link btn btn-link p-0 js-view-dealer"
                                                    data-dealer="{{ e(json_encode($dealerInfo)) }}">
                                                    {{ strtoupper($dealer->name) }}
                                                </button>
                                            @else
                                                <a href="view-dealer/{{$dealer->id}}" class="dealer-link">{{ strtoupper($dealer->name) }}</a>
                                            @endif
                                        </td>
                                        <td data-label="Store name" scope="col">{{ strtoupper($dealer->store_name ?? '-') }}</td>
                                        <td data-label="Store type" scope="col">{{ strtoupper($dealer->store_type ?? '-') }}</td>
                                        <td data-label="Contact number" scope="col">{{ strtoupper($dealer->number ?? '-') }}</td>
                                        <td data-label="Stock quantity" scope="col"><span class="dealer-metric is-stock">{{ number_format($stockQty) }}</span></td>
                                        <td data-label="Quantity sold" scope="col"><span class="dealer-metric is-sold">{{ number_format($soldQty) }}</span></td>
                                        <td data-label="Address" scope="col"><div class="dealer-muted">{{ strtoupper($dealer->address ?? '-') }}</div></td>
                                        <td data-label="Sales territory" scope="col"><div class="dealer-muted">{{ strtoupper($dealer->area ?? '-') }}</div></td>
                                        <td data-label="Status">
                                            @if($dealer->status == 'Active')
                                                <span class="dealer-status is-active">Active</span>
                                            @else
                                                <span class="dealer-status is-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td data-label="View">
                                            @if($isAdminDealerPage)
                                                <a href="{{ $adminDealerViewUrl }}" class="dealer-action-btn" title="View dealer">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @else
                                                {{-- <button type="button"
                                                    class="dealer-action-btn js-view-dealer"
                                                    title="View dealer"
                                                    data-dealer="{{ e(json_encode($dealerInfo)) }}">
                                                    <i class="ti ti-eye"></i>
                                                </button> --}}
                                                <a href="view-dealer/{{$dealer->id}}" class="dealer-link dealer-action-btn"><i class="ti ti-eye"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
