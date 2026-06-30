@extends('layouts.header')

@section('css')
<style>
    .voucher-page { display: grid; gap: 16px; }
    .voucher-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; }
    .voucher-kicker { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #20a2d1; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    .voucher-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .voucher-copy { margin: 4px 0 0; color: #667085; font-size: 13px; }
    .voucher-actions-top { display: flex; align-items: center; gap: 8px; }
    .voucher-stats { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 12px; }
    .voucher-stat { position: relative; display: grid; grid-template-columns: 44px minmax(0, 1fr); align-items: center; gap: 12px; min-height: 86px; padding: 16px; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); overflow: hidden; }
    .voucher-stat::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; background: #5BC2E7; }
    .voucher-stat-icon { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #e2f7ff; color: #5BC2E7; font-size: 20px; }
    .voucher-stat-label { display: block; color: #667085; font-size: 11px; font-weight: 800; line-height: 1.2; text-transform: uppercase; }
    .voucher-stat-value { display: block; margin-top: 4px; color: #101828; font-size: 24px; font-weight: 900; line-height: 1; }
    .voucher-stat-note { display: block; margin-top: 5px; color: #98a2b3; font-size: 11px; font-weight: 700; }
    .voucher-stat.is-active::before { background: #16a34a; }
    .voucher-stat.is-active .voucher-stat-icon { background: #dcfce7; color: #16a34a; }
    .voucher-stat.is-warning::before { background: #f59e0b; }
    .voucher-stat.is-warning .voucher-stat-icon { background: #fef3c7; color: #d97706; }
    .voucher-stat.is-usage::before { background: #0f766e; }
    .voucher-stat.is-usage .voucher-stat-icon { background: #ccfbf1; color: #0f766e; }
    .voucher-filter { overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .voucher-filter-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 18px; background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); border-bottom: 1px solid #edf0f5; }
    .voucher-filter-title { display: flex; align-items: center; gap: 11px; }
    .voucher-filter-icon { width: 38px; height: 38px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #5BC2E7; background: #ebfbff; border-radius: 10px; }
    .voucher-filter-title strong { display: block; color: #101828; font-size: 14px; font-weight: 900; }
    .voucher-result-count { display: inline-flex; align-items: center; gap: 5px; margin-top: 2px; color: #667085; font-size: 11px; }
    /* .voucher-result-count b { color: #b91c1c; } */
    .voucher-data-actions { display: flex; align-items: center; gap: 7px; }
    .voucher-data-actions .btn { min-height: 36px; padding: 7px 11px; font-size: 11px; font-weight: 800; border-radius: 8px; }
    .voucher-filter-body { padding: 14px 18px; }
    .voucher-tools { display: grid; grid-template-columns: minmax(260px, 1fr) 190px auto auto; align-items: end; gap: 10px; width: 100%; }
    .voucher-filter-field { display: grid; gap: 5px; }
    .voucher-filter-label { color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .voucher-search { position: relative; }
    .voucher-search > i { position: absolute; z-index: 2; left: 12px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; }
    .voucher-search .form-control { min-height: 40px; padding-left: 37px; border-color: #dfe4ea; border-radius: 8px; }
    .voucher-filter .form-select { min-height: 40px; border-color: #dfe4ea; border-radius: 8px; }
    .voucher-filter .form-control:focus, .voucher-filter .form-select:focus { border-color: #5BC2E7; box-shadow: 0 0 0 3px rgba(91, 194, 231, .1); }
    .voucher-filter-submit { min-height: 40px; padding: 8px 16px; color: #fff; font-size: 11px; font-weight: 900; background: #5BC2E7; border-color: #5BC2E7; border-radius: 8px; }
    .voucher-filter-submit:hover { color: #fff; background: #47bde8; border-color: #47bde8; }
    .voucher-filter-reset { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 13px; color: #667085; font-size: 11px; font-weight: 800; background: #fff; border: 1px solid #dfe4ea; border-radius: 8px; }
    .voucher-filter-reset:hover { color: #5BC2E7; background: #e2f7ff; border-color: #5BC2E7; }
    .voucher-active-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e4e7ec; }
    .voucher-filter-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; color: #991b1b; font-size: 10px; font-weight: 800; background: #fef2f2; border: 1px solid #fecaca; border-radius: 999px; }
    .voucher-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .voucher-table { margin: 0; }
    .voucher-table th { padding: 13px 16px; color: #667085; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .voucher-table td { padding: 16px; border-color: #f1f3f6; }
    .voucher-code { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 5px; color: #111827; font-weight: 900; letter-spacing: .04em; }
    .voucher-code i { color: #dc2626; }
    .voucher-name { color: #344054; font-size: 13px; font-weight: 800; }
    .voucher-meta { color: #667085; font-size: 12px; }
    .voucher-discount { color: #0f766e; font-size: 15px; font-weight: 900; white-space: nowrap; }
    .voucher-usage { min-width: 120px; }
    .voucher-progress { height: 6px; margin-top: 7px; overflow: hidden; background: #eef2f7; border-radius: 999px; }
    .voucher-progress span { display: block; height: 100%; background: #dc2626; border-radius: inherit; }
    .voucher-status { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .voucher-status.active { background: #dcfce7; color: #166534; }
    .voucher-status.scheduled { background: #e0f2fe; color: #075985; }
    .voucher-status.expired, .voucher-status.inactive, .voucher-status.used-up { background: #fee2e2; color: #991b1b; }
    .voucher-status.minimum-not-met { background: #fef3c7; color: #92400e; }
    .voucher-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .voucher-icon-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    .voucher-empty { padding: 52px 16px; text-align: center; color: #667085; }
    .voucher-empty i { color: #d0d5dd; }
    .voucher-modal-content { overflow: hidden; border: 0; border-radius: 14px; box-shadow: 0 24px 70px rgba(15, 23, 42, .2); }
    .voucher-modal-header { align-items: flex-start; padding: 22px 24px; background: linear-gradient(135deg, #fff 0%, #fff7f7 100%); border-bottom: 1px solid #f1e4e4; }
    .voucher-modal-heading { display: flex; align-items: center; gap: 14px; }
    .voucher-modal-icon { width: 46px; height: 46px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; font-size: 21px; background: linear-gradient(135deg, #dc2626, #991b1b); border-radius: 12px; box-shadow: 0 8px 18px rgba(185, 28, 28, .22); }
    .voucher-modal-kicker { margin-bottom: 2px; color: #b91c1c; font-size: 10px; font-weight: 900; letter-spacing: .09em; text-transform: uppercase; }
    .voucher-modal-header .modal-title { color: #101828; font-size: 20px; font-weight: 900; }
    .voucher-modal-header p { margin: 3px 0 0; color: #667085; font-size: 12px; }
    .voucher-modal-body { display: grid; gap: 14px; padding: 20px 24px 24px; background: #f8fafc; }
    .voucher-form-section { padding: 18px; background: #fff; border: 1px solid #e8ecf2; border-radius: 12px; box-shadow: 0 5px 15px rgba(15, 23, 42, .035); }
    .voucher-section-head { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #eef1f5; }
    .voucher-section-head > span { width: 34px; height: 34px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #b91c1c; background: #fef2f2; border-radius: 9px; }
    .voucher-section-head h6 { margin: 0; color: #1d2939; font-size: 14px; font-weight: 900; }
    .voucher-section-head p { margin: 2px 0 0; color: #98a2b3; font-size: 11px; }
    .voucher-form .form-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .voucher-form .form-control, .voucher-form .form-select, .voucher-form .input-group-text { min-height: 42px; border-color: #dfe4ea; }
    .voucher-form .form-control:focus, .voucher-form .form-select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .11); }
    .voucher-form .input-group-text { color: #667085; font-size: 12px; font-weight: 800; background: #f8fafc; }
    .voucher-input-icon { position: relative; }
    .voucher-input-icon > i { position: absolute; z-index: 2; left: 13px; top: 50%; color: #98a2b3; transform: translateY(-50%); }
    .voucher-input-icon .form-control { padding-left: 38px; font-weight: 800; letter-spacing: .04em; }
    .voucher-help { display: block; margin-top: 5px; color: #98a2b3; font-size: 10px; line-height: 1.35; }
    .voucher-area-help { display: flex; align-items: center; gap: 5px; min-height: 15px; }
    .voucher-area-help.is-loading { color: #475467; }
    .voucher-area-help.is-success { color: #15803d; }
    .voucher-area-help.is-error { color: #b91c1c; }
    .voucher-area-help .spinner-border { width: 11px; height: 11px; border-width: 1.5px; }
    .voucher-area-picker { overflow: hidden; background: #fff; border: 1px solid #dfe4ea; border-radius: 10px; transition: border-color .18s ease, box-shadow .18s ease; }
    .voucher-area-picker:focus-within { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .11); }
    .voucher-area-picker.is-invalid { border-color: #dc3545; }
    .voucher-area-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .voucher-area-count { padding: 6px 9px; font-size: 10px; border: 1px solid #e4e7ec; }
    .voucher-area-toolbar .btn { padding: 3px 8px; font-size: 10px; font-weight: 800; }
    .voucher-area-options { display: grid; gap: 6px; max-height: 172px; padding: 9px; overflow-y: auto; }
    .voucher-area-options.is-disabled { min-height: 90px; place-items: center; background: #f9fafb; }
    .voucher-area-placeholder { display: grid; justify-items: center; gap: 5px; color: #98a2b3; font-size: 11px; }
    .voucher-area-placeholder i { font-size: 21px; }
    .voucher-area-option { position: relative; display: flex; align-items: center; gap: 9px; min-height: 38px; margin: 0; padding: 8px 10px; cursor: pointer; background: #fff; border: 1px solid #e4e7ec; border-radius: 8px; transition: .16s ease; }
    .voucher-area-option:hover { background: #fff7f7; border-color: #fecaca; }
    .voucher-area-option:has(.form-check-input:checked) { color: #991b1b; background: #fef2f2; border-color: #fca5a5; }
    .voucher-area-option .form-check-input { flex: 0 0 auto; margin: 0; cursor: pointer; }
    .voucher-area-option .form-check-input:checked { background-color: #dc2626; border-color: #dc2626; }
    .voucher-area-option span { font-size: 11px; font-weight: 700; line-height: 1.3; margin-left: 25px; }
    .voucher-description-field { display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e4e7ec; border-radius: 10px; }
    .voucher-description-field:focus-within { background: #fff; border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .11); }
    .voucher-description-icon { width: 34px; height: 34px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #b91c1c; background: #fee2e2; border-radius: 8px; }
    .voucher-description-field textarea { min-height: 50px; resize: vertical; background: transparent; box-shadow: none !important; }
    .voucher-status-card { display: flex; align-items: center; gap: 12px; padding: 13px 15px; cursor: pointer; background: #f8fafc; border: 1px solid #e4e7ec; border-radius: 10px; transition: .18s ease; }
    .voucher-status-card:hover { border-color: #fecaca; background: #fffafa; }
    .voucher-status-card-icon { width: 36px; height: 36px; display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; color: #16a34a; background: #dcfce7; border-radius: 9px; }
    .voucher-status-card-copy { display: grid; flex: 1; gap: 2px; }
    .voucher-status-card-copy strong { color: #1d2939; font-size: 13px; }
    .voucher-status-card-copy small { color: #667085; font-size: 10px; }
    .voucher-status-card .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; }
    .voucher-status-card .form-check-input:checked { background-color: #16a34a; border-color: #16a34a; }
    .voucher-modal-footer { justify-content: space-between; padding: 15px 24px; background: #fff; border-top: 1px solid #e8ecf2; }
    .voucher-required-note { color: #98a2b3; font-size: 11px; }
    .voucher-required-note span { color: #dc2626; font-weight: 900; }
    .voucher-cancel-btn, .voucher-submit-btn { min-height: 40px; padding-right: 18px; padding-left: 18px; font-weight: 800; }
    .voucher-form-modal .select2-container { width: 100% !important; }
    .voucher-form-modal .select2-container--bootstrap-5 .select2-selection {
        min-height: 42px;
        border-color: #dfe4ea;
        border-radius: .375rem;
        font-size: 14px;
    }
    .voucher-form-modal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        line-height: 40px;
        color: #1f2937;
    }
    .voucher-form-modal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: #98a2b3;
    }
    .voucher-form-modal .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .voucher-form-modal .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .11);
    }
    .voucher-form-modal .select2-container--bootstrap-5 .select2-selection--multiple {
        padding-top: 3px;
        padding-bottom: 3px;
    }
    .voucher-form-modal .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fecaca;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }
    .voucher-form-modal .select2-container--bootstrap-5.select2-container--disabled .select2-selection {
        background: #f2f4f7;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 2000;
        border-color: #dfe4ea;
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background: #ef4444;
        color: #fff;
    }
    @media (max-width: 768px) {
        .voucher-head { align-items: stretch; flex-direction: column; }
        .voucher-actions-top { justify-content: stretch; }
        .voucher-actions-top .btn { flex: 1; }
        .voucher-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .voucher-filter-head { align-items: stretch; flex-direction: column; }
        .voucher-data-actions { display: grid; grid-template-columns: repeat(3, 1fr); }
        .voucher-tools { grid-template-columns: 1fr 1fr; width: 100%; }
        .voucher-panel { overflow-x: auto; }
        .voucher-table { min-width: 900px; }
        .voucher-modal-header, .voucher-modal-body, .voucher-modal-footer { padding-right: 16px; padding-left: 16px; }
        .voucher-modal-footer { align-items: stretch; flex-direction: column; }
        .voucher-modal-footer > div { display: grid !important; grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .voucher-stats { grid-template-columns: 1fr; }
        .voucher-data-actions, .voucher-tools { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $totalVouchers = $vouchers->count();
    $activeVouchers = $vouchers->filter(function ($voucher) {
        return $voucher->statusLabel() === 'Active';
    })->count();
    $expiredVouchers = $vouchers->filter(function ($voucher) {
        return in_array($voucher->statusLabel(), ['Expired', 'Used Up']);
    })->count();
    
    $totalUsage = $vouchers->sum('used_count');
@endphp

<div class="voucher-page">
    <div class="voucher-head">
        <div>
            <div class="voucher-kicker"><i class="bi bi-ticket-perforated"></i> Voucher Management</div>
            <h4 class="voucher-title">Vouchers</h4>
            <p class="voucher-copy">Create, monitor, and control rebate voucher codes for purchase orders.</p>
        </div>
        <div class="voucher-actions-top">
            {{-- <a href="{{ route('vouchers.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a> --}}
            {{-- <a href="{{ route('vouchers') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a> --}}
            {{-- <a href="{{ route('vouchers.import-template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download"></i> Template
            </a>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#voucherUploadModal">
                <i class="bi bi-upload"></i> Upload
            </button> --}}
            <button class="btn btn-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#voucherCreateModal">
                <i class="bi bi-plus-lg"></i> Add Voucher
            </button>
        </div>
    </div>

    <div class="voucher-stats">
        <div class="voucher-stat">
            <span class="voucher-stat-icon"><i class="bi bi-collection"></i></span>
            <div>
                <span class="voucher-stat-label">Total Vouchers</span>
                <strong class="voucher-stat-value">{{ number_format($totalVouchers) }}</strong>
                <span class="voucher-stat-note">Created records</span>
            </div>
        </div>
        <div class="voucher-stat is-active">
            <span class="voucher-stat-icon"><i class="bi bi-check2-circle"></i></span>
            <div>
                <span class="voucher-stat-label">Active</span>
                <strong class="voucher-stat-value">{{ number_format($activeVouchers) }}</strong>
                <span class="voucher-stat-note">Ready to apply</span>
            </div>
        </div>
        <div class="voucher-stat is-warning">
            <span class="voucher-stat-icon"><i class="bi bi-hourglass-split"></i></span>
            <div>
                <span class="voucher-stat-label">Expired/Used</span>
                <strong class="voucher-stat-value">{{ number_format($expiredVouchers) }}</strong>
                <span class="voucher-stat-note">Needs review</span>
            </div>
        </div>
        <div class="voucher-stat is-usage">
            <span class="voucher-stat-icon"><i class="bi bi-bag-check"></i></span>
            <div>
                <span class="voucher-stat-label">Total Uses</span>
                <strong class="voucher-stat-value">{{ number_format($totalUsage) }}</strong>
                <span class="voucher-stat-note">Redeemed orders</span>
            </div>
        </div>
    </div>

    <div class="voucher-filter">
        <div class="voucher-filter-head">
            <div class="voucher-filter-title">
                <span class="voucher-filter-icon"><i class="bi bi-ticket-detailed"></i></span>
                <div>
                    <strong>Voucher Directory</strong>
                    <span class="voucher-result-count">
                        <i class="bi bi-list-check"></i>
                        Showing <b>{{ number_format($vouchers->count()) }}</b> result(s)
                    </span>
                </div>
            </div>
            <div class="voucher-data-actions">
                <a href="{{ route('vouchers.export', request()->query()) }}" class="btn btn-outline-success" title="Export the current filtered results">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </a>
                <a href="{{ route('vouchers.import-template') }}" class="btn btn-outline-secondary" title="Download the voucher import template">
                    <i class="bi bi-file-earmark-arrow-down"></i> Template
                </a>
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#voucherUploadModal" title="Upload voucher records">
                    <i class="bi bi-cloud-arrow-up"></i> Upload
                </button>
            </div>
        </div>
        <div class="voucher-filter-body">
            <form method="GET" action="{{ route('vouchers') }}" class="voucher-tools">
                <div class="voucher-filter-field">
                    <label class="voucher-filter-label" for="voucherSearch">Search Vouchers</label>
                    <div class="voucher-search">
                        <i class="bi bi-search"></i>
                        <input type="search" id="voucherSearch" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search voucher code or distributor name">
                    </div>
                </div>
                <div class="voucher-filter-field">
                    <label class="voucher-filter-label" for="voucherStatus">Voucher Status</label>
                    <select id="voucherStatus" name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" @if(request('status') === 'active') selected @endif>Active</option>
                        <option value="inactive" @if(request('status') === 'inactive') selected @endif>Inactive</option>
                        <option value="expired" @if(request('status') === 'expired') selected @endif>Expired</option>
                    </select>
                </div>
                <button class="btn voucher-filter-submit" type="submit">
                    <i class="bi bi-funnel-fill"></i> Apply Filters
                </button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('vouchers') }}" class="voucher-filter-reset">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @else
                    <button type="button" class="voucher-filter-reset" disabled>
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                @endif
            </form>

            @if(request()->filled('search') || request()->filled('status'))
                <div class="voucher-active-filters">
                    @if(request()->filled('search'))
                        <span class="voucher-filter-chip"><i class="bi bi-search"></i> Search: {{ request('search') }}</span>
                    @endif
                    @if(request()->filled('status'))
                        <span class="voucher-filter-chip"><i class="bi bi-circle-fill"></i> Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-0">
            <strong>Please check the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="voucher-panel">
        @if($vouchers->count())
            <table class="table voucher-table align-middle">
                <thead>
                    <tr>
                        <th>Voucher</th>
                        <th>Description</th>
                        <th>Areas</th>
                        <th>Discount</th>
                        <th>Minimum Order</th>
                        <th>Usage</th>
                        <th>Validity</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vouchers as $voucher)
                        @php
                            $status = $voucher->statusLabel();
                            $statusClass = strtolower(str_replace(' ', '-', $status));
                            $usageLimit = (int) ($voucher->usage_limit ?: 0);
                            $usagePercent = $usageLimit > 0 ? min(100, ((int) $voucher->used_count / $usageLimit) * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="voucher-code"><i class="bi bi-ticket-perforated"></i>{{ $voucher->code }}</div>
                                <div class="voucher-name">{{ strtoupper($voucher->name ?? 'NO DISTRIBUTOR') }}</div>
                                {{-- @if($voucher->description)
                                    <div class="voucher-meta">{{ \Illuminate\Support\Str::limit(strtoupper($voucher->description), 90) }}</div>
                                @endif --}}
                            </td>
                            <td>{{ strtoupper($voucher->description ?? 'NO DESCRIPTION') }}</td>
                            <td>{{ implode(', ', $voucher->area_names) }}</td>
                            <td>
                                <div class="voucher-discount">
                                    @if($voucher->discount_type === 'percent')
                                        {{ number_format($voucher->discount_value, 2) }}%
                                    @else
                                        PHP {{ number_format($voucher->discount_value, 2) }}
                                    @endif
                                </div>
                                <div class="voucher-meta">{{ strtoupper($voucher->discount_type === 'percent' ? 'Percentage discount' : 'Fixed rebate') }}</div>
                            </td>
                            <td>PHP {{ number_format($voucher->minimum_order_amount, 2) }}</td>
                            <td>
                                <div class="voucher-usage">
                                    {{-- <div>{{ number_format($voucher->used_count) }} / {{ $voucher->usage_limit ? number_format($voucher->usage_limit) : '<i class="bi bi-infinity"></i>' }}</div> --}}
                                    <div>
                                        {{ number_format($voucher->used_count) }} /
                                        
                                        @if($voucher->usage_limit)
                                            {{ number_format($voucher->usage_limit) }}
                                        @else
                                            <i class="bi bi-infinity"></i>
                                        @endif
                                    </div>
                                    @if($usageLimit > 0)
                                        <div class="voucher-progress"><span style="width: {{ $usagePercent }}%"></span></div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>{{ $voucher->starts_at ? $voucher->starts_at->format('M d, Y') : 'Now' }}</div>
                                <div class="voucher-meta">to {{ $voucher->expires_at ? $voucher->expires_at->format('M d, Y') : 'No expiry' }}</div>
                            </td>
                            <td><span class="voucher-status {{ $statusClass }}"><i class="bi bi-circle-fill"></i>{{ $status }}</span></td>
                            <td>
                                <div class="voucher-actions">
                                    <a href="{{ route('vouchers.ad-orders', $voucher->id) }}" class="btn btn-sm btn-outline-secondary voucher-icon-btn" title="View AD voucher usage">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary voucher-icon-btn" data-bs-toggle="modal" data-bs-target="#voucherEditModal{{ $voucher->id }}" title="Edit voucher">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST" onsubmit="return confirm('Delete this voucher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger voucher-icon-btn" type="submit" title="Delete voucher"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        @else
            <div class="voucher-empty">
                <i class="bi bi-ticket-perforated fs-1 d-block mb-2"></i>
                <div class="fw-bold text-dark">No vouchers found</div>
                <div>Create a new voucher or adjust your filters.</div>
            </div>
        @endif
    </div>

    @foreach($vouchers as $voucher)
        @include('vouchers.partials.form-modal', ['voucher' => $voucher, 'modalId' => 'voucherEditModal' . $voucher->id, 'title' => 'Edit Voucher', 'action' => route('vouchers.update', $voucher->id), 'method' => 'PUT'])
    @endforeach
</div>

@include('vouchers.partials.form-modal', ['voucher' => null, 'modalId' => 'voucherCreateModal', 'title' => 'Add Voucher', 'action' => route('vouchers.store'), 'method' => 'POST'])

<div class="modal fade" id="voucherUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 8px; overflow: hidden;">
            <form action="{{ route('vouchers.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #edf0f5;">
                    <div>
                        <h5 class="modal-title fw-bold">Upload Vouchers</h5>
                        <div class="text-muted small">Import voucher records from Excel or CSV.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Voucher file <span class="text-danger">*</span></label>
                    <input type="file" name="voucher_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="voucher-meta mt-2">
                        Required headings: code, name, discount_type, discount_value. Optional headings: description, minimum_order_amount, usage_limit, starts_at, expires_at, is_active.
                    </div>
                    <div class="voucher-meta mt-1">
                        Existing voucher codes will be skipped.
                    </div>
                    <a href="{{ route('vouchers.import-template') }}" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-upload"></i> Upload Vouchers
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function parseJson(value, fallback) {
            try {
                return value ? JSON.parse(value) : fallback;
            } catch (error) {
                return fallback;
            }
        }

        function initVoucherSelect2(modal) {
            if (!window.jQuery || !jQuery.fn.select2) return;

            jQuery(modal).find('select.select2').each(function () {
                var $select = jQuery(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    dropdownParent: jQuery(modal),
                    placeholder: $select.data('placeholder') || 'Select option',
                    allowClear: !$select.prop('multiple'),
                    theme: 'bootstrap-5',
                    selectionCssClass: 'form-select',
                    dropdownCssClass: 'voucher-select2-dropdown'
                });
            });
        }

        function setAreaStatus(help, state, message) {
            if (!help) return;

            help.className = 'voucher-help voucher-area-help is-' + state;
            help.innerHTML = state === 'loading'
                ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' + message
                : '<i class="bi ' + (state === 'success' ? 'bi-check-circle' : (state === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle')) + '"></i> ' + message;
        }

        function checkedAreaValues(areaPicker) {
            return Array.prototype.slice.call(areaPicker.querySelectorAll('input[name="area_names[]"]:checked')).map(function (checkbox) {
                return checkbox.value;
            });
        }

        function updateAreaCount(areaPicker) {
            var count = checkedAreaValues(areaPicker).length;
            var badge = areaPicker.querySelector('.voucher-area-count');
            badge.textContent = count + ' selected';
            badge.classList.toggle('bg-danger', count > 0);
            badge.classList.toggle('text-white', count > 0);
            badge.classList.toggle('bg-light', count === 0);
            badge.classList.toggle('text-dark', count === 0);
            areaPicker.classList.remove('is-invalid');
        }

        function updateAreaPicker(areaPicker, areas, selected) {
            var options = areaPicker.querySelector('.voucher-area-options');
            var selectAll = areaPicker.querySelector('.voucher-area-select-all');
            var clear = areaPicker.querySelector('.voucher-area-clear');

            options.innerHTML = '';
            options.classList.toggle('is-disabled', areas.length === 0);
            selectAll.disabled = areas.length === 0;
            clear.disabled = areas.length === 0;
            areaPicker.dataset.hasAreas = areas.length ? '1' : '0';
            areaPicker.dataset.selected = '[]';

            if (!areas.length) {
                options.innerHTML = '<div class="voucher-area-placeholder"><i class="bi bi-geo-alt"></i><span>No areas available</span></div>';
            } else {
                areas.forEach(function (areaName, index) {
                    var label = document.createElement('label');
                    var checkbox = document.createElement('input');
                    var text = document.createElement('span');

                    label.className = 'voucher-area-option';
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.name = 'area_names[]';
                    checkbox.value = areaName;
                    checkbox.id = areaPicker.id + '_option_' + index;
                    checkbox.checked = selected.indexOf(areaName) !== -1;
                    text.textContent = areaName;

                    label.appendChild(checkbox);
                    label.appendChild(text);
                    options.appendChild(label);
                });
            }

            var requiredMark = areaPicker.closest('[class*="col-"]').querySelector('.voucher-area-required');
            if (requiredMark) requiredMark.classList.toggle('d-none', areas.length === 0);
            updateAreaCount(areaPicker);
        }

        function syncVoucherAreas(distributorSelect, keepSelected) {
            var areaPicker = document.getElementById(distributorSelect.dataset.areaTarget);
            if (!areaPicker) return;

            var option = distributorSelect.options[distributorSelect.selectedIndex];
            var distributorId = option ? option.dataset.distributorId : '';
            var currentSelected = checkedAreaValues(areaPicker);
            var storedSelected = parseJson(areaPicker.dataset.selected, []);
            var selected = keepSelected ? (storedSelected.length ? storedSelected : currentSelected) : [];
            var help = areaPicker.closest('[class*="col-"]').querySelector('.voucher-area-help');

            if (!distributorId) {
                areaPicker.dataset.loadedDistributorId = '';
                updateAreaPicker(areaPicker, [], []);
                setAreaStatus(help, 'info', 'Select a distributor to view assigned areas.');
                return Promise.resolve();
            }

            if (keepSelected && areaPicker.dataset.loadedDistributorId === distributorId && areaPicker.dataset.hasAreas === '1') {
                setAreaStatus(help, 'success', areaPicker.querySelectorAll('input[name="area_names[]"]').length + ' assigned area(s) available.');
                return Promise.resolve();
            }

            var requestId = String(Date.now()) + Math.random();
            areaPicker.dataset.requestId = requestId;
            updateAreaPicker(areaPicker, [], []);
            setAreaStatus(help, 'loading', 'Loading assigned areas...');

            var url = distributorSelect.dataset.areasUrl + '?distributor_id=' + encodeURIComponent(distributorId);

            return fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (response) {
                    if (!response.ok) throw new Error('Unable to load distributor areas.');
                    return response.json();
                })
                .then(function (data) {
                    if (areaPicker.dataset.requestId !== requestId) return;

                    var areas = Array.isArray(data.areas) ? data.areas : [];
                    updateAreaPicker(areaPicker, areas, selected);
                    areaPicker.dataset.loadedDistributorId = distributorId;

                    if (areas.length) {
                        setAreaStatus(help, 'success', areas.length + ' assigned area(s) available. Select one or more.');
                    } else {
                        setAreaStatus(help, 'info', 'This distributor has no assigned areas.');
                    }
                })
                .catch(function () {
                    if (areaPicker.dataset.requestId !== requestId) return;

                    updateAreaPicker(areaPicker, [], []);
                    setAreaStatus(help, 'error', 'Areas could not be loaded. Please try again.');
                });
        }

        function syncDiscountFields(form) {
            var type = form.querySelector('.voucher-discount-type');
            var value = form.querySelector('.voucher-discount-value');
            var prefix = form.querySelector('.voucher-discount-prefix');
            var help = form.querySelector('.voucher-discount-help');
            var isPercent = type.value === 'percent';

            prefix.textContent = isPercent ? '%' : 'PHP';
            help.textContent = isPercent ? 'Enter a percentage from 0.01 to 100.' : 'Enter the fixed rebate amount.';

            if (isPercent) {
                value.max = '100';
                if (parseFloat(value.value || '0') > 100) value.value = '100';
            } else {
                value.removeAttribute('max');
            }
        }

        function syncDates(form) {
            var start = form.querySelector('.voucher-start-date');
            var expiry = form.querySelector('.voucher-expiry-date');

            expiry.min = start.value || '';
            if (start.value && expiry.value && expiry.value < start.value) {
                expiry.value = start.value;
            }
        }

        document.querySelectorAll('.voucher-form-modal').forEach(function (modal) {
            var form = modal.querySelector('.voucher-form');
            var distributor = form.querySelector('.voucher-distributor-select');
            var areaPicker = document.getElementById(distributor.dataset.areaTarget);
            var discountType = form.querySelector('.voucher-discount-type');
            var startDate = form.querySelector('.voucher-start-date');

            syncDiscountFields(form);
            syncDates(form);

            var handleDistributorChange = function () {
                areaPicker.dataset.selected = '[]';
                areaPicker.dataset.loadedDistributorId = '';
                syncVoucherAreas(distributor, false);
            };

            if (window.jQuery) {
                jQuery(distributor).off('change.voucherAreas').on('change.voucherAreas', handleDistributorChange);
            } else {
                distributor.addEventListener('change', handleDistributorChange);
            }

            discountType.addEventListener('change', function () {
                syncDiscountFields(form);
            });

            startDate.addEventListener('change', function () {
                syncDates(form);
            });

            areaPicker.addEventListener('change', function (event) {
                if (event.target.matches('input[name="area_names[]"]')) {
                    updateAreaCount(areaPicker);
                }
            });

            areaPicker.querySelector('.voucher-area-select-all').addEventListener('click', function () {
                areaPicker.querySelectorAll('input[name="area_names[]"]').forEach(function (checkbox) {
                    checkbox.checked = true;
                });
                updateAreaCount(areaPicker);
            });

            areaPicker.querySelector('.voucher-area-clear').addEventListener('click', function () {
                areaPicker.querySelectorAll('input[name="area_names[]"]').forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                updateAreaCount(areaPicker);
            });

            modal.addEventListener('shown.bs.modal', function () {
                initVoucherSelect2(modal);
                syncVoucherAreas(distributor, true);
            });

            form.addEventListener('submit', function (event) {
                var missingArea = areaPicker.dataset.hasAreas === '1' && checkedAreaValues(areaPicker).length === 0;
                areaPicker.classList.toggle('is-invalid', missingArea);

                if (missingArea) {
                    setAreaStatus(
                        areaPicker.closest('[class*="col-"]').querySelector('.voucher-area-help'),
                        'error',
                        'Select at least one authorized area.'
                    );
                }

                if (!form.checkValidity() || missingArea) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                }
            });
        });
    });
</script>
@endsection
