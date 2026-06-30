@extends('layouts.header')

@section('css')
<link rel="icon" type="image/png" href="{{ asset('images/logo_nya.png') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<style>
    .ad-page {
        --red: #b91c1c;
        --red-dark: #991b1b;
        --text: #101828;
        --muted: #667085;
        --border: #e4e7ec;
        display: grid;
        gap: 16px;
        padding-top: 18px;
    }
    .ad-page-head, .ad-filter-head, .ad-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .ad-page-head { padding: 20px 22px; background: linear-gradient(135deg, #fff, #fff7f7); border: 1px solid #f1e4e4; border-radius: 14px; box-shadow: 0 10px 28px rgba(15, 23, 42, .05); }
    .ad-kicker { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px; color: var(--red); font-size: 12px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
    .ad-title { margin: 0; color: var(--text); font-size: 26px; font-weight: 900; letter-spacing: -.02em; }
    .ad-copy { margin: 5px 0 0; color: var(--muted); font-size: 12px; }
    .ad-primary-action { min-height: 40px; display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; font-size: 12px; font-weight: 900; background: linear-gradient(135deg, #dc2626, #991b1b); border: 0; border-radius: 9px; box-shadow: 0 7px 16px rgba(185, 28, 28, .2); transition: .18s ease; }
    .ad-primary-action:hover { transform: translateY(-1px); box-shadow: 0 9px 20px rgba(185, 28, 28, .26); }
    .ad-stats { display: grid; grid-template-columns: repeat(4, minmax(170px, 1fr)); gap: 12px; }
    .ad-stat { position: relative; display: flex; align-items: center; gap: 13px; min-height: 94px; padding: 17px; overflow: hidden; background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 8px 22px rgba(15, 23, 42, .045); transition: .18s ease; }
    .ad-stat:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15, 23, 42, .08); }
    .ad-stat::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 4px; background: #475467; }
    .ad-stat-icon { width: 46px; height: 46px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #475467; font-size: 21px; background: #f2f4f7; border-radius: 11px; }
    .ad-stat-label { display: block; color: var(--muted); font-size: 12px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .ad-stat-value { display: block; margin-top: 4px; color: var(--text); font-size: 26px; font-weight: 900; line-height: 1; }
    .ad-stat-note { display: block; margin-top: 5px; color: #98a2b3; font-size: 12px; }
    .ad-stat.is-active::before { background: #16a34a; }
    .ad-stat.is-active .ad-stat-icon { color: #15803d; background: #dcfce7; }
    .ad-stat.is-inactive::before { background: #dc2626; }
    .ad-stat.is-inactive .ad-stat-icon { color: #b91c1c; background: #fee2e2; }
    .ad-stat.is-area::before { background: #2563eb; }
    .ad-stat.is-area .ad-stat-icon { color: #1d4ed8; background: #dbeafe; }
    .ad-filter, .ad-panel { overflow: hidden; background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .ad-filter-head { padding: 14px 17px; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .ad-filter-heading { display: flex; align-items: center; gap: 10px; }
    .ad-filter-heading > span { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; color: var(--red); background: #fef2f2; border-radius: 9px; }
    .ad-filter-heading strong { display: block; color: var(--text); font-size: 13px; font-weight: 900; }
    .ad-filter-heading small { display: block; margin-top: 2px; color: #98a2b3; font-size: 12px; }
    .ad-filter-form { display: grid; grid-template-columns: minmax(230px, 1.35fr) repeat(3, minmax(145px, .8fr)) minmax(180px, 1fr) auto auto; align-items: end; gap: 9px; padding: 14px 17px; }
    .ad-filter-field { display: grid; gap: 5px; }
    .ad-filter-label { color: var(--muted); font-size: 12px; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }
    .ad-search { position: relative; }
    .ad-search i { position: absolute; z-index: 2; left: 12px; top: 50%; color: #98a2b3; transform: translateY(-50%); }
    .ad-search .form-control { padding-left: 36px; }
    .ad-filter .form-control, .ad-filter .form-select { min-height: 40px; font-size: 12px; background: #fff; border-color: #dfe4ea; border-radius: 8px; }
    .ad-filter .form-control:focus, .ad-filter .form-select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .1); }
    .ad-filter-btn, .ad-clear-btn { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 13px; font-size: 12px; font-weight: 900; border-radius: 8px; white-space: nowrap; }
    .ad-filter-btn { color: #fff; background: #5BC2E7 !important; border: 1px solid #5BC2E7 !important; }
    .ad-filter-btn:hover { color: #fff; background: var(--red-dark); }
    .ad-clear-btn { color: var(--muted); background: #fff; border: 1px solid #dfe4ea; }
    .ad-clear-btn:hover { color: var(--red); background: #fff7f7; border-color: #fecaca; }
    .ad-filter-chips { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 17px 13px; }
    .ad-filter-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; color: var(--red-dark); font-size: 12px; font-weight: 800; background: #fef2f2; border: 1px solid #fecaca; border-radius: 999px; }
    .ad-panel-head { padding: 16px 18px; background: linear-gradient(135deg, #fff, #fbfcfe); border-bottom: 1px solid #edf0f5; }
    .ad-panel-head h5 { margin: 0; color: var(--text); font-size: 15px; font-weight: 900; }
    .ad-panel-head p { margin: 3px 0 0; color: var(--muted); font-size: 12px; }
    .ad-result-badge { display: inline-flex; align-items: center; gap: 5px; padding: 7px 11px; color: #344054; font-size: 12px; font-weight: 800; background: #f2f4f7; border: 1px solid var(--border); border-radius: 999px; }
    .ad-table-wrap { max-height: 68vh; overflow: auto; }
    .ad-table { min-width: 1160px; margin: 0; }
    .ad-table thead th { position: sticky; z-index: 2; top: 0; padding: 12px 13px; color: var(--muted); font-size: 12px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-bottom: 1px solid #e8ecf2; }
    .ad-table tbody td { padding: 13px; color: #344054; font-size: 12px; vertical-align: middle; border-color: #f0f2f5; }
    .ad-table tbody tr:hover { background: #fffafa; }
    /* .ad-code { display: inline-flex; padding: 5px 8px; color: var(--red); font-size: 12px; font-weight: 900; white-space: nowrap; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; } */
    .ad-person { display: flex; align-items: center; gap: 9px; min-width: 180px; }
    .ad-avatar { width: 36px; height: 36px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 900; background: linear-gradient(135deg, #5BC2E7, #18a0d1); border-radius: 9px; box-shadow: 0 5px 12px rgba(185, 28, 28, .18); }
    .ad-person a { color: #1d2939; font-weight: 900; text-decoration: none; }
    .ad-person a:hover { color: var(--red); }
    .ad-person small { display: block; margin-top: 2px; color: #98a2b3; font-size: 12px; }
    .ad-muted { display: block; max-width: 210px; color: var(--muted); line-height: 1.45; }
    .ad-area-cell { min-width: 280px; }
    .ad-area-list { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
    .ad-area-badge { display: inline-flex; align-items: center; gap: 4px; padding: 5px 7px; color: #1d4ed8; font-size: 12px; font-weight: 800; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; }
    .ad-area-more { color: #475467; background: #f2f4f7; border-color: var(--border); }
    .ad-no-area { display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; color: #98a2b3; font-size: 12px; }
    .ad-manage-btn { padding: 5px 9px; font-size: 12px; font-weight: 800; border-radius: 7px; }
    .ad-status { display: inline-flex; align-items: center; gap: 5px; padding: 6px 9px; font-size: 12px; font-weight: 900; border-radius: 999px; white-space: nowrap; }
    .ad-status.active { color: #166534; background: #dcfce7; }
    .ad-status.inactive { color: #991b1b; background: #fee2e2; }
    .ad-actions { display: flex; gap: 5px; }
    .ad-action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border-radius: 7px; }
    .ad-empty { padding: 58px 16px; text-align: center; color: var(--muted); }
    .ad-empty strong { display: block; margin-bottom: 4px; color: #344054; }
    .ad-empty i { display: block; margin-bottom: 8px; color: #d0d5dd; font-size: 36px; }
    .ad-pagination { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 13px 16px; border-top: 1px solid #edf0f5; }
    .ad-pagination-summary { color: var(--muted); font-size: 12px; font-weight: 800; }
    .ad-pagination .pagination { gap: 3px; margin: 0; }
    .ad-pagination .page-link { min-width: 31px; color: #475467; font-size: 12px; text-align: center; border-color: var(--border); border-radius: 6px !important; }
    .ad-pagination .page-item.active .page-link { color: #fff; background: #5BC2E7; border-color: #5BC2E7; }
    .dataTables_wrapper { padding: 12px 14px 14px; }
    .dataTables_length label, .dataTables_info { color: var(--muted); font-size: 12px; }
    .dataTables_length select { width: 62px !important; }
    .dataTables_paginate .pagination { gap: 3px; }
    .dataTables_paginate .page-link { min-width: 31px; color: #475467; font-size: 12px; text-align: center; border-color: var(--border); border-radius: 6px !important; }
    /* .dataTables_paginate .page-item.active .page-link { color: #fff; background: var(--red); border-color: var(--red); } */
    @media (max-width: 1200px) {
        .ad-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ad-filter-form { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .ad-page-head, .ad-filter-head, .ad-panel-head { align-items: stretch; flex-direction: column; }
        .ad-primary-action { justify-content: center; }
        .ad-filter-form { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .ad-stats, .ad-filter-form { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $indexRoute = $indexRoute ?? 'ads';
    $distributorTitle = $distributorTitle ?? 'Area Distributors';
    $distributorSingular = $distributorSingular ?? 'Area Distributor';
    $distributorCopy = $distributorCopy ?? 'Monitor distributor status, business information, and awarded territories.';
    $showCreateButton = $showCreateButton ?? true;
    $filteredAwardedAreas = $ads->sum(function ($ad) { return $ad->areas->count(); });
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('region') || request()->filled('project_type') || request()->filled('area');
@endphp

<section class="ad-page">
    {{-- <div class="ad-page-head">
        <div>
            <div class="ad-kicker"><i class="ti ti-map-pin"></i> Partner Network</div>
            <h3 class="ad-title">{{ $distributorTitle }}</h3>
            <p class="ad-copy">{{ $distributorCopy }}</p>
        </div>
        @if($showCreateButton)
            <button class="btn btn-danger ad-primary-action" type="button" data-bs-toggle="modal" data-bs-target="#new_area_distributor"><i class="ti ti-plus"></i> Add {{ $distributorSingular }}</button>
        @endif
    </div> --}}

    <div class="ad-stats">
        @foreach([
            ['class' => '', 'icon' => 'ti-users', 'label' => 'Total Distributors', 'value' => $totalAds, 'note' => 'Registered ' . $distributorSingular . ' partners'],
            ['class' => 'is-active', 'icon' => 'ti-user-check', 'label' => 'Active', 'value' => $activeAds, 'note' => 'Currently operational'],
            ['class' => 'is-inactive', 'icon' => 'ti-user-x', 'label' => 'Inactive', 'value' => $inactiveAds, 'note' => 'Needs review'],
            ['class' => 'is-area', 'icon' => 'ti-map-2', 'label' => 'Total Awarded Areas', 'value' => $totalAwardedAreas, 'note' => 'Territory assignments'],
        ] as $stat)
            <div class="ad-stat {{ $stat['class'] }}">
                <span class="ad-stat-icon"><i class="ti {{ $stat['icon'] }}"></i></span>
                <div><span class="ad-stat-label">{{ $stat['label'] }}</span><strong class="ad-stat-value">{{ number_format($stat['value']) }}</strong><span class="ad-stat-note">{{ $stat['note'] }}</span></div>
            </div>
        @endforeach
    </div>

    <div class="ad-filter">
        <div class="ad-filter-head">
            <div class="ad-filter-heading"><span><i class="ti ti-adjustments-horizontal"></i></span><div><strong>Filter Distributors</strong><small>Narrow results by partner, status, region, project, or area.</small></div></div>
            @if($hasFilters)<a href="{{ route($indexRoute) }}" class="ad-clear-btn"><i class="ti ti-filter-off"></i> Clear Filters</a>@endif
        </div>
        <form method="GET" action="{{ route($indexRoute) }}" class="ad-filter-form">
            <div class="ad-filter-field"><label class="ad-filter-label" for="adSearch">Search</label><div class="ad-search"><i class="ti ti-search"></i><input type="search" id="adSearch" name="search" class="form-control" value="{{ request('search') }}" placeholder="Code, name, business, contact, or area"></div></div>
            <div class="ad-filter-field"><label class="ad-filter-label" for="adStatus">Status</label><select id="adStatus" name="status" class="form-select"><option value="">All statuses</option><option value="Active" @if(request('status') === 'Active') selected @endif>Active</option><option value="Inactive" @if(request('status') === 'Inactive') selected @endif>Inactive</option></select></div>
            <div class="ad-filter-field"><label class="ad-filter-label" for="adRegion">Region</label><select id="adRegion" name="region" class="form-select"><option value="">All regions</option>@foreach($regions as $region)<option value="{{ $region }}" @if(request('region') === $region) selected @endif>{{ strtoupper($region) }}</option>@endforeach</select></div>
            <div class="ad-filter-field"><label class="ad-filter-label" for="adProject">Project Type</label><select id="adProject" name="project_type" class="form-select"><option value="">All projects</option>@foreach($projectTypes as $projectType)<option value="{{ $projectType }}" @if(request('project_type') === $projectType) selected @endif>{{ strtoupper($projectType) }}</option>@endforeach</select></div>
            <div class="ad-filter-field"><label class="ad-filter-label" for="adArea">Awarded Area</label><input type="search" id="adArea" name="area" class="form-control" value="{{ request('area') }}" placeholder="Search area name"></div>
            <button type="submit" class="ad-filter-btn"><i class="ti ti-filter"></i> Apply</button>
            <a href="{{ route($indexRoute) }}" class="ad-clear-btn"><i class="ti ti-refresh"></i> Reset</a>
        </form>
        @if($hasFilters)
            <div class="ad-filter-chips">@foreach(['search' => 'Search', 'status' => 'Status', 'region' => 'Region', 'project_type' => 'Project', 'area' => 'Area'] as $key => $label)@if(request()->filled($key))<span class="ad-filter-chip"><i class="ti ti-tag"></i>{{ $label }}: {{ request($key) }}</span>@endif @endforeach</div>
        @endif
    </div>

    <div class="ad-panel">
        <div class="ad-panel-head"><div><h5>{{ $distributorTitle }} Directory</h5><p>Showing {{ number_format($ads->firstItem() ?? 0) }}-{{ number_format($ads->lastItem() ?? 0) }} of {{ number_format($ads->total()) }} distributor(s), with {{ number_format($filteredAwardedAreas) }} awarded area(s) on this page.</p></div><span class="ad-result-badge"><i class="ti ti-list"></i> {{ number_format($ads->total()) }} Results</span></div>
        @if($ads->count())
            <div class="ad-table-wrap">
                <table class="table ad-table align-middle" id="example">
                    <thead><tr><th>Actions</th><th>Partner Code</th><th>Distributor</th><th>Contact</th><th>Business</th><th>Region</th><th>Awarded Areas</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($ads as $ad)
                        <tr>
                            <td><div class="ad-actions"><a href="{{ route('ad.view', $ad->id) }}" class="btn btn-outline-info ad-action-btn" title="View"><i class="ti ti-eye"></i></a><button type="button" class="btn btn-outline-warning ad-action-btn" data-bs-toggle="modal" data-bs-target="#edit_area_distributor-{{ $ad->id }}" title="Edit"><i class="ti ti-edit"></i></button></div></td>
                            <td><span class="ad-area-badge">{{ strtoupper($ad->store_code ?: '-') }}</span></td>
                            <td><div class="ad-person"><span class="ad-avatar">{{ strtoupper(substr($ad->name ?: 'A', 0, 1)) }}</span><div><a href="{{ route('ad.view', $ad->id) }}">{{ strtoupper($ad->name ?: '-') }}</a><small>{{ strtoupper($ad->business_type ?: 'AREA DISTRIBUTOR') }}</small></div></div></td>
                            <td><span class="ad-muted"><i class="ti ti-phone"></i> {{ strtoupper($ad->contact_number ?: '-') }}</span></td>
                            <td><strong>{{ strtoupper($ad->business_name ?: '-') }}</strong><span class="ad-muted">{{ strtoupper($ad->address ?: '-') }}</span></td>
                            <td><span class="ad-muted"><i class="ti ti-map-pin"></i> {{ strtoupper($ad->location_region ?: '-') }}</span></td>
                            <td class="ad-area-cell">
                                @if($ad->areas->count())
                                    {{-- <div class="ad-area-list">@foreach($ad->areas->take(4) as $area)<span class="ad-area-badge"><i class="ti ti-map-pin"></i>{{ strtoupper(($area->project_type ? $area->project_type . ': ' : '') . $area->area_name) }}</span>@endforeach @if($ad->areas->count() > 4)<span class="ad-area-badge ad-area-more">+{{ $ad->areas->count() - 4 }} more</span>@endif</div> --}}
                                    <div class="ad-area-list">@foreach($ad->areas->take(4) as $area)<span class="ad-area-badge"><i class="ti ti-map-pin"></i>{{ strtoupper($area->area_name) }}</span>@endforeach @if($ad->areas->count() > 4)<span class="ad-area-badge ad-area-more">+{{ $ad->areas->count() - 4 }} more</span>@endif</div>
                                @else
                                    <span class="ad-no-area"><i class="ti ti-map-off"></i>No awarded areas</span>
                                @endif
                                <button type="button" class="btn btn-outline-primary ad-manage-btn" data-bs-toggle="modal" data-bs-target="#manageAreaModal-{{ $ad->id }}"><i class="ti ti-map-plus"></i> Manage Areas</button>
                            </td>
                            <td><span class="ad-status {{ strtolower($ad->status ?: 'inactive') }}"><i class="ti ti-circle-filled"></i>{{ $ad->status ?: 'Inactive' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @foreach($ads as $ad)
                @include('area_distributor.manage_areas')
                @include('area_distributor.edit')
            @endforeach
            @if($ads->hasPages())
                <div class="ad-pagination">
                    <div class="ad-pagination-summary">
                        Showing {{ number_format($ads->firstItem() ?? 0) }} to {{ number_format($ads->lastItem() ?? 0) }} of {{ number_format($ads->total()) }} results
                    </div>
                    {{ $ads->links() }}
                </div>
            @endif
        @else
            <div class="ad-empty"><i class="ti ti-user-search"></i><strong>No area distributors found</strong><div>Adjust the filters or clear them to view all records.</div></div>
        @endif
    </div>
</section>
@endsection

@if($showCreateButton)
    @include('area_distributor.create')
@endif

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function () {
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Success', text: @json(session('success')), confirmButtonText: 'OK' });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonText: 'OK' });
        @endif
    });
</script>
@endsection
