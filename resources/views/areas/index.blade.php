@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
<style>
    .areas-page { display: grid; gap: 16px; }
    .areas-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; }
    .areas-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .areas-copy { margin: 4px 0 0; color: #667085; font-size: 13px; }
    .areas-panel { overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .areas-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .areas-table { margin: 0; }
    .areas-table th { padding: 12px 14px; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #edf0f5; white-space: nowrap; }
    .areas-table td { padding: 14px; border-color: #f1f3f6; vertical-align: middle; }
    .area-name { color: #101828; font-weight: 900; }
    .area-location { display: flex; flex-wrap: wrap; gap: 6px; }
    .coverage-summary { display: grid; gap: 5px; }
    .coverage-summary-row { display: flex; flex-wrap: wrap; gap: 5px; padding-bottom: 5px; border-bottom: 1px dashed #eaecf0; }
    .coverage-summary-row:last-child { padding-bottom: 0; border-bottom: 0; }
    .location-chip { display: inline-flex; align-items: center; gap: 4px; padding: 4px 7px; color: #344054; font-size: 11px; font-weight: 700; background: #f8fafc; border: 1px solid #eaecf0; border-radius: 5px; }
    .location-chip i { color: #98a2b3; }
    .area-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; border-radius: 999px; background: #e0f2fe; color: #075985; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .area-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .area-icon-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    .areas-mobile-list { display: none; }
    .mobile-area-search { position: relative; margin-bottom: 10px; }
    .mobile-area-search i { position: absolute; top: 50%; left: 13px; color: #98a2b3; transform: translateY(-50%); }
    .mobile-area-search input { min-height: 42px; padding-left: 36px; border-color: #d0d5dd; border-radius: 8px; }
    .mobile-area-card { padding: 14px; background: #fff; border: 1px solid #e4e7ec; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 24, 40, .05); }
    .mobile-area-card + .mobile-area-card { margin-top: 10px; }
    .mobile-area-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .mobile-area-card-title { margin: 0; color: #101828; font-size: 15px; font-weight: 900; }
    .mobile-area-card-meta { margin-top: 3px; color: #667085; font-size: 11px; }
    .mobile-coverage-label { margin-bottom: 7px; color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }
    .mobile-empty-state { padding: 24px 14px; color: #667085; text-align: center; background: #fff; border: 1px dashed #d0d5dd; border-radius: 10px; }
    .areas-panel .dataTables_wrapper { padding: 14px; }
    .areas-panel .dataTables_filter input,
    .areas-panel .dataTables_length select { border: 1px solid #d0d5dd; border-radius: 6px; }
    .areas-panel .dataTables_filter input { min-height: 34px; padding: 5px 9px; }
    .areas-panel .dataTables_length select { min-height: 32px; padding: 4px 26px 4px 8px; }
    .areas-panel .dataTables_info { color: #667085; font-size: 13px; }
    .areas-panel .pagination { margin: 0; }
    .areas-table tbody tr { transition: background-color .16s ease, box-shadow .16s ease; }
    .areas-table tbody tr:hover { background: #fcfcfd; }
    .areas-table td[data-label="Coverage"] { min-width: 360px; }
    .coverage-summary-row { max-width: 100%; }
    .areas-panel .dataTables_filter label, .areas-panel .dataTables_length label { color: #475467; font-size: 13px; font-weight: 700; }
    .areas-panel .dataTables_paginate .paginate_button { border-radius: 6px !important; }
    .area-modal .modal-dialog { max-width: min(860px, calc(100vw - 3rem)); }
    .area-modal .modal-dialog.modal-dialog-scrollable { height: min(780px, calc(100dvh - 3.5rem)); }
    .area-modal .modal-content { display: flex; flex-direction: column; height: 100%; max-height: 100%; overflow: hidden; border: 0; border-radius: 12px; box-shadow: 0 24px 70px rgba(15, 23, 42, .2); }
    .area-modal .modal-content > form { display: flex; flex: 1 1 auto; flex-direction: column; min-height: 0; overflow: hidden; }
    .area-modal .modal-header { padding: 20px 22px; background: #f8fafc; border-bottom: 1px solid #e8ecf2; }
    .area-modal-title { display: flex; align-items: center; gap: 12px; }
    .area-modal-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 42px; color: #fff; background: #b42318; border-radius: 10px; font-size: 20px; }
    .area-modal .modal-title { color: #101828; font-size: 18px; font-weight: 900; }
    .area-modal .modal-body { flex: 1 1 0; min-height: 0; padding: 20px 22px; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; background: #fff; }
    .area-modal .modal-footer { flex: 0 0 auto; gap: 8px; padding: 14px 22px 20px; background: #fff; border-top: 1px solid #e8ecf2; }
    .area-modal .form-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .area-modal .form-control { min-height: 44px; border-color: #dfe4ea; border-radius: 8px; }
    .area-modal .form-control:focus { border-color: #b42318; box-shadow: 0 0 0 3px rgba(180, 35, 24, .11); }
    .coverage-intro { padding: 12px 14px; margin-bottom: 16px; color: #475467; font-size: 13px; background: #f8fafc; border: 1px solid #e4e7ec; border-radius: 8px; }
    .coverage-entry { position: relative; padding: 14px; margin-bottom: 10px; background: #fff; border: 1px solid #e4e7ec; border-radius: 9px; box-shadow: 0 2px 6px rgba(16, 24, 40, .03); }
    .coverage-entry-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; color: #344054; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
    .coverage-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .coverage-grid .coverage-wide { grid-column: 1 / -1; }
    .coverage-grid .form-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .remove-coverage { padding: 2px 6px; color: #b42318; font-size: 12px; font-weight: 800; background: transparent; border: 0; }
    @media (max-width: 768px) { .area-modal .modal-dialog { max-width: none; } .area-modal .modal-dialog.modal-dialog-scrollable { height: calc(100dvh - 24px); margin: 12px auto; } .area-modal .modal-header, .area-modal .modal-body { padding: 16px; } .area-modal .modal-footer { padding: 12px 16px 16px; } }
    @media (max-width: 575px) { .coverage-grid { grid-template-columns: 1fr; } .coverage-grid .coverage-wide { grid-column: auto; } .area-modal .modal-dialog.modal-dialog-scrollable { height: calc(100dvh - 16px); margin: 8px auto; } .area-modal .modal-footer { display: grid; grid-template-columns: 1fr 1fr; } .area-modal .modal-footer .btn { width: 100%; min-height: 42px; margin: 0; } .area-modal-title { align-items: flex-start; gap: 9px; } .area-modal-icon { width: 36px; height: 36px; flex-basis: 36px; font-size: 17px; } .area-modal .modal-title { font-size: 16px; } .area-modal .modal-header .text-muted { font-size: 11px; line-height: 1.35; } }
    @media (max-width: 991px) {
        .areas-panel { overflow-x: auto; }
        .areas-table { min-width: 850px; }
        .areas-panel .dataTables_wrapper { min-width: 850px; }
    }
    @media (max-width: 768px) {
        .areas-head, .areas-panel-head { align-items: stretch; flex-direction: column; }
        .areas-head > .btn { width: 100%; min-height: 42px; }
        .areas-panel { overflow: visible; border: 0; background: transparent; box-shadow: none; }
        .areas-panel-head { padding: 0 0 10px; background: transparent; border: 0; }
        .areas-panel .dataTables_wrapper { padding: 0; }
        .areas-panel .dataTables_wrapper { display: none; }
        .areas-mobile-list { display: block; }
        .areas-panel .dataTables_wrapper { min-width: 0; }
        .areas-panel .dataTables_wrapper > .row { display: flex; flex-direction: column; gap: 8px; margin: 0; }
        .areas-panel .dataTables_wrapper > .row > [class*="col-"] { width: 100%; max-width: 100%; padding: 0; }
        .areas-panel .dataTables_filter label, .areas-panel .dataTables_length label { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 0; }
        .areas-panel .dataTables_filter input { width: min(100%, 250px); margin-left: 0; }
        .area-icon-btn { width: 38px; height: 38px; }
    }
    @media (max-width: 420px) {
        .areas-title { font-size: 21px; }
        .areas-copy { font-size: 12px; }
        .mobile-area-card { padding: 12px; }
        .coverage-summary-row { justify-content: flex-start; }
    }
</style>
@endsection

@section('content')
<div class="areas-page">
    <div class="areas-head">
        <div>
            <h4 class="areas-title">Areas</h4>
            <p class="areas-copy">Manage service coverage by region, province, city or municipality, and barangay.</p>
        </div>
        <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#areaCreateModal">
            <i class="bi bi-plus-lg"></i> Add Area
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-0">
            <strong>Please check the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0">{{ session('error') }}</div>
    @endif

    <div class="areas-panel">
        <div class="areas-panel-head">
            <div>
                <div class="fw-bold text-dark">Area List</div>
                <div class="text-muted small">{{ number_format($areas->count()) }} area(s) found</div>
            </div>
        </div>

        <table id="areasTable" class="table areas-table align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Area Name</th>
                    <th>Geographic Coverage</th>
                    <th>Assigned Distributors</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                    <tr>
                        <td data-label="Area name">
                            <div class="area-name">{{ $area->name }}</div>
                        </td>
                        <td data-label="Coverage">
                            <div class="coverage-summary">
                                @forelse($area->geographicCoverages as $coverage)
                                    <div class="coverage-summary-row">
                                        @foreach(['region' => 'Region', 'province' => 'Province', 'city_municipality' => 'City/Municipality', 'barangay' => 'Barangay'] as $field => $label)
                                            <span class="location-chip" title="{{ $label }}"><i class="bi bi-geo-alt"></i>{{ $coverage->{$field} }}</span>
                                        @endforeach
                                    </div>
                                @empty
                                    <span class="text-muted small">Not set</span>
                                @endforelse
                            </div>
                        </td>
                        <td data-label="Distributors">
                            <span class="area-badge">
                                <i class="bi bi-geo-alt"></i>
                                {{ number_format($area->assigned_distributors_count ?? 0) }} assigned
                            </span>
                        </td>
                        <td data-label="Created" class="text-muted small">
                            {{ $area->created_at ? $area->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td data-label="Actions">
                            <div class="area-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary area-icon-btn" data-bs-toggle="modal" data-bs-target="#areaEditModal{{ $area->id }}" title="Edit area">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('Delete this area?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger area-icon-btn" type="submit" title="Delete area">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div id="areasMobileList" class="areas-mobile-list">
            <div class="mobile-area-search">
                <i class="bi bi-search"></i>
                <input id="mobileAreaSearch" type="search" class="form-control" placeholder="Search areas or locations">
            </div>
            <div id="mobileAreaCards">
                @forelse($areas as $area)
                    @php
                        $coverageSearch = $area->geographicCoverages->map(function ($coverage) {
                            return implode(' ', [$coverage->region, $coverage->province, $coverage->city_municipality, $coverage->barangay]);
                        })->implode(' ');
                    @endphp
                    <article class="mobile-area-card" data-search="{{ strtolower($area->name . ' ' . $coverageSearch) }}">
                        <div class="mobile-area-card-head">
                            <div>
                                <h5 class="mobile-area-card-title">{{ $area->name }}</h5>
                                <div class="mobile-area-card-meta">{{ number_format($area->assigned_distributors_count ?? 0) }} distributor(s) assigned · {{ $area->created_at ? $area->created_at->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="area-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary area-icon-btn" data-bs-toggle="modal" data-bs-target="#areaEditModal{{ $area->id }}" aria-label="Edit {{ $area->name }}"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('Delete this area?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger area-icon-btn" type="submit" aria-label="Delete {{ $area->name }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="mobile-coverage-label">Geographic coverage · {{ $area->geographicCoverages->count() }} location(s)</div>
                        <div class="coverage-summary">
                            @forelse($area->geographicCoverages as $coverage)
                                <div class="coverage-summary-row">
                                    <span class="location-chip"><i class="bi bi-geo-alt"></i>{{ $coverage->region }}</span>
                                    <span class="location-chip">{{ $coverage->province }}</span>
                                    <span class="location-chip">{{ $coverage->city_municipality }}</span>
                                    <span class="location-chip">{{ $coverage->barangay }}</span>
                                </div>
                            @empty
                                <span class="text-muted small">No coverage location set</span>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="mobile-empty-state">No areas found. Add an area to get started.</div>
                @endforelse
            </div>
            <div id="mobileAreaNoResults" class="mobile-empty-state d-none">No areas match your search.</div>
        </div>
    </div>
</div>

<div class="modal fade area-modal" id="areaCreateModal" tabindex="-1" aria-labelledby="areaCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('areas.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <div class="area-modal-title">
                        <span class="area-modal-icon"><i class="bi bi-map"></i></span>
                        <div>
                            <h5 class="modal-title mb-1" id="areaCreateModalLabel">Add Area</h5>
                            <div class="text-muted small">Define the area name and geographic locations it covers.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="coverage-wide mb-3"><label class="form-label">Area Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" placeholder="e.g. Davao City North" required></div>
                    <div class="coverage-intro"><i class="bi bi-info-circle me-1"></i>Add every geographic location covered by this area. You can add as many Region → Province → City/Municipality → Barangay combinations as needed.</div>
                    <div id="coverageEntries" class="coverage-entries" data-prefix="coverages">
                        @foreach(old('coverages', [['region' => '', 'province' => '', 'city_municipality' => '', 'barangay' => '']]) as $index => $entry)
                            <div class="coverage-entry">
                                <div class="coverage-entry-title"><span>Coverage area <span class="coverage-number">{{ $index + 1 }}</span></span><button type="button" class="remove-coverage"><i class="bi bi-trash"></i> Remove</button></div>
                                <div class="coverage-grid">
                                    <div><label class="form-label">Region <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][region]" class="form-control area-region" data-selected="{{ $entry['region'] ?? '' }}" required><option value="">Loading regions...</option></select></div>
                                    <div><label class="form-label">Province <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][province]" class="form-control area-province" data-selected="{{ $entry['province'] ?? '' }}" required disabled><option value="">Select region first</option></select></div>
                                    <div><label class="form-label">City / Municipality <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][city_municipality]" class="form-control area-city" data-selected="{{ $entry['city_municipality'] ?? '' }}" required disabled><option value="">Select province first</option></select></div>
                                    <div><label class="form-label">Barangay <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][barangay]" class="form-control area-barangay" data-selected="{{ $entry['barangay'] ?? '' }}" required disabled><option value="">Select city first</option></select></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm add-coverage" data-target="#coverageEntries"><i class="bi bi-plus-lg"></i> Add coverage location</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($areas as $area)
    <div class="modal fade area-modal" id="areaEditModal{{ $area->id }}" tabindex="-1" aria-labelledby="areaEditModal{{ $area->id }}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form action="{{ route('areas.update', $area->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div class="area-modal-title">
                            <span class="area-modal-icon"><i class="bi bi-map"></i></span>
                            <div>
                                <h5 class="modal-title mb-1" id="areaEditModal{{ $area->id }}Label">Edit Area</h5>
                                <div class="text-muted small">Update the area name and geographic locations it covers.</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="coverage-wide mb-3"><label class="form-label" for="areaEditName{{ $area->id }}">Area Name <span class="text-danger">*</span></label><input type="text" id="areaEditName{{ $area->id }}" name="name" class="form-control" value="{{ old('name', $area->name) }}" maxlength="255" required></div>
                        <div class="coverage-intro"><i class="bi bi-layers me-1"></i>Manage all geographic locations served by this area.</div>
                        <div id="editCoverageEntries{{ $area->id }}" class="coverage-entries" data-prefix="coverages">
                            @foreach($area->geographicCoverages as $index => $coverage)
                                <div class="coverage-entry">
                                    <div class="coverage-entry-title"><span>Coverage location <span class="coverage-number">{{ $index + 1 }}</span></span><button type="button" class="remove-coverage"><i class="bi bi-trash"></i> Remove</button></div>
                                    <div class="coverage-grid">
                                        <div><label class="form-label">Region <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][region]" class="form-control area-region" data-selected="{{ $coverage->region }}" required><option value="">Loading regions...</option></select></div>
                                        <div><label class="form-label">Province <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][province]" class="form-control area-province" data-selected="{{ $coverage->province }}" required disabled><option value="">Select region first</option></select></div>
                                        <div><label class="form-label">City / Municipality <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][city_municipality]" class="form-control area-city" data-selected="{{ $coverage->city_municipality }}" required disabled><option value="">Select province first</option></select></div>
                                        <div><label class="form-label">Barangay <span class="text-danger">*</span></label><select name="coverages[{{ $index }}][barangay]" class="form-control area-barangay" data-selected="{{ $coverage->barangay }}" required disabled><option value="">Select city first</option></select></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm add-coverage" data-target="#editCoverageEntries{{ $area->id }}"><i class="bi bi-plus-lg"></i> Add coverage location</button>
                        <small class="text-muted d-block mt-3">Renaming an area also updates matching distributor and dealer assignments.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('javascript')
<script>
    $(document).ready(async function () {
        function coverageEntry(index, prefix) {
            return '<div class="coverage-entry">'
                + '<div class="coverage-entry-title"><span>Coverage location <span class="coverage-number"></span></span><button type="button" class="remove-coverage"><i class="bi bi-trash"></i> Remove</button></div>'
                + '<div class="coverage-grid">'
                + '<div><label class="form-label">Region <span class="text-danger">*</span></label><select name="' + prefix + '[' + index + '][region]" class="form-control area-region" required><option value="">Loading regions...</option></select></div>'
                + '<div><label class="form-label">Province <span class="text-danger">*</span></label><select name="' + prefix + '[' + index + '][province]" class="form-control area-province" required disabled><option value="">Select region first</option></select></div>'
                + '<div><label class="form-label">City / Municipality <span class="text-danger">*</span></label><select name="' + prefix + '[' + index + '][city_municipality]" class="form-control area-city" required disabled><option value="">Select province first</option></select></div>'
                + '<div><label class="form-label">Barangay <span class="text-danger">*</span></label><select name="' + prefix + '[' + index + '][barangay]" class="form-control area-barangay" required disabled><option value="">Select city first</option></select></div>'
                + '</div></div>';
        }

        function numberCoverageEntries($container) {
            var prefix = $container.data('prefix');
            $container.find('.coverage-entry').each(function (index) {
                $(this).find('.coverage-number').text(index + 1);
                $(this).find('select').each(function () {
                    this.name = this.name.replace(/^[^\[]+\[\d+\]/, prefix + '[' + index + ']');
                });
            });
        }

        function normaliseLocation(value) {
            return String(value || '').replace(/\s+/g, ' ').trim().replace(/^(city|municipality)\s+of\s+/i, '').toLowerCase();
        }

        function isNcr(region) {
            region = String(region || '').toLowerCase();
            return region.indexOf('ncr') !== -1 || region.indexOf('national capital') !== -1;
        }

        function setOptions($select, placeholder, items) {
            $select.empty().append($('<option>', { value: '', text: placeholder }));
            (items || []).forEach(function (item) {
                $select.append($('<option>', { value: item.name, text: item.name }));
            });
        }

        function selectSavedValue($select, value) {
            if (!value) return false;
            var expected = normaliseLocation(value);
            var match = $select.find('option').filter(function () {
                return normaliseLocation(this.value) === expected || normaliseLocation($(this).text()) === expected;
            }).first();
            if (!match.length) {
                $select.append($('<option>', { value: value, text: value }));
                match = $select.find('option:last');
            }
            $select.val(match.val());
            return true;
        }

        function resetAfterRegion($entry) {
            setOptions($entry.find('.area-province').prop('disabled', true), 'Select region first');
            setOptions($entry.find('.area-city').prop('disabled', true), 'Select province first');
            setOptions($entry.find('.area-barangay').prop('disabled', true), 'Select city first');
        }

        function loadBarangays($entry, restore) {
            var $barangay = $entry.find('.area-barangay').prop('disabled', true);
            var city = $entry.find('.area-city').val();
            setOptions($barangay, city ? 'Loading barangays...' : 'Select city first');
            if (!city) return;
            $.get('/api/cities/' + encodeURIComponent(city) + '/barangays')
                .done(function (data) {
                    setOptions($barangay, 'Select barangay', data);
                    $barangay.prop('disabled', false);
                    if (restore) selectSavedValue($barangay, $barangay.attr('data-selected'));
                })
                .fail(function () { setOptions($barangay, 'Unable to load barangays'); });
        }

        function loadCities($entry, restore, ncr) {
            var $city = $entry.find('.area-city').prop('disabled', true);
            setOptions($city, 'Loading cities/municipalities...');
            setOptions($entry.find('.area-barangay').prop('disabled', true), 'Select city first');
            var source = ncr
                ? '/api/regions/' + encodeURIComponent($entry.find('.area-region').val()) + '/cities-municipalities'
                : '/api/provinces/' + encodeURIComponent($entry.find('.area-province').val()) + '/cities';
            $.get(source)
                .done(function (data) {
                    setOptions($city, 'Select city/municipality', data);
                    $city.prop('disabled', false);
                    if (restore && selectSavedValue($city, $city.attr('data-selected'))) loadBarangays($entry, true);
                })
                .fail(function () { setOptions($city, 'Unable to load cities/municipalities'); });
        }

        function loadProvinces($entry, restore) {
            var region = $entry.find('.area-region').val();
            resetAfterRegion($entry);
            if (!region) return;
            if (isNcr(region)) {
                var $province = $entry.find('.area-province');
                setOptions($province, 'Select province', [{ name: 'Metro Manila' }]);
                $province.val('Metro Manila').prop('disabled', false);
                loadCities($entry, restore, true);
                return;
            }
            var $province = $entry.find('.area-province');
            setOptions($province, 'Loading provinces...');
            $.get('/api/regions/' + encodeURIComponent(region) + '/provinces')
                .done(function (data) {
                    setOptions($province, 'Select province', data);
                    $province.prop('disabled', false);
                    if (restore && selectSavedValue($province, $province.attr('data-selected'))) loadCities($entry, true, false);
                })
                .fail(function () { setOptions($province, 'Unable to load provinces'); });
        }

        function initialiseCoverageEntry($entry) {
            var $region = $entry.find('.area-region');
            $.get('/api/regions')
                .done(function (data) {
                    setOptions($region, 'Select region', data);
                    if (selectSavedValue($region, $region.attr('data-selected'))) loadProvinces($entry, true);
                })
                .fail(function () { setOptions($region, 'Unable to load regions'); });
        }

        $('.area-modal').on('change', '.area-region', function () {
            loadProvinces($(this).closest('.coverage-entry, .modal[id^="areaEditModal"]'), false);
        }).on('change', '.area-province', function () {
            var $entry = $(this).closest('.coverage-entry, .modal[id^="areaEditModal"]');
            if (!isNcr($entry.find('.area-region').val())) loadCities($entry, false, false);
        }).on('change', '.area-city', function () {
            loadBarangays($(this).closest('.coverage-entry, .modal[id^="areaEditModal"]'), false);
        });

        $('.modal[id^="areaEditModal"]').on('show.bs.modal', function () {
            var $modal = $(this);
            if (!$modal.data('locationLoaded')) {
                $modal.data('locationLoaded', true);
                var $container = $modal.find('.coverage-entries');
                if (!$container.find('.coverage-entry').length) {
                    $container.append(coverageEntry(0, $container.data('prefix')));
                }
                $modal.find('.coverage-entry').each(function () { initialiseCoverageEntry($(this)); });
            }
        });
        $('#coverageEntries .coverage-entry').each(function () { initialiseCoverageEntry($(this)); });

        $('.add-coverage').on('click', function () {
            var $container = $($(this).data('target'));
            $container.append(coverageEntry($container.find('.coverage-entry').length, $container.data('prefix')));
            numberCoverageEntries($container);
            var $entry = $container.find('.coverage-entry:last');
            initialiseCoverageEntry($entry);
            $entry.find('.area-region').trigger('focus');
        });

        $('.area-modal').on('click', '.remove-coverage', function () {
            var $container = $(this).closest('.coverage-entries');
            var entries = $container.find('.coverage-entry');
            if (entries.length === 1) {
                entries.find('select').val('');
                return;
            }
            $(this).closest('.coverage-entry').remove();
            numberCoverageEntries($container);
        });

        @if($errors->any())
            new bootstrap.Modal(document.getElementById('areaCreateModal')).show();
        @endif

        $('#mobileAreaSearch').on('input', function () {
            var query = String($(this).val() || '').toLowerCase().trim();
            var visible = 0;
            $('#mobileAreaCards .mobile-area-card').each(function () {
                var matches = !query || String($(this).data('search') || '').indexOf(query) !== -1;
                $(this).toggle(matches);
                if (matches) visible++;
            });
            $('#mobileAreaNoResults').toggleClass('d-none', visible !== 0 || !query);
        });

        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var script = document.createElement('script');
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

            var dataTableSources = [
                "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                "https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"
            ];

            for (var i = 0; i < dataTableSources.length; i++) {
                try {
                    await loadScript(dataTableSources[i]);

                    if ($.fn && $.fn.DataTable) {
                        break;
                    }
                } catch (error) {
                    console.warn('Unable to load DataTables from:', dataTableSources[i]);
                }
            }

            if (!$.fn || !$.fn.DataTable) {
                return false;
            }

            try {
                await loadScript("{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}");
            } catch (error) {
                console.warn('DataTables Bootstrap styling script did not load. Continuing with core DataTables.');
            }

            return true;
        }

        if (!await ensureDataTables()) {
            console.error('DataTables failed to load from local assets and CDN fallback.');
            return;
        }

        if ($.fn.DataTable.isDataTable('#areasTable')) {
            $('#areasTable').DataTable().destroy();
        }

        $('#areasTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 4, orderable: false, searchable: false }
            ],
            language: {
                emptyTable: 'No areas found. Add an area to get started.',
                search: 'Search:',
                lengthMenu: 'Show _MENU_ areas',
                info: 'Showing _START_ to _END_ of _TOTAL_ areas',
                infoEmpty: 'Showing 0 areas',
                zeroRecords: 'No matching areas found'
            }
        });
    });
</script>
@endsection
