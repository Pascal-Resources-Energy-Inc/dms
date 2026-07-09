@extends('layouts.header')

@section('css')
<style>
    .charges-page { display: grid; gap: 16px; }
    .charges-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; }
    .charges-kicker { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 7px; color: #0f766e; font-size: 11px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
    .charges-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .charges-copy { margin: 4px 0 0; max-width: 760px; color: #667085; font-size: 13px; }
    .charges-add-btn { min-height: 40px; padding: 9px 14px; font-size: 12px; font-weight: 900; border-radius: 8px; }
    .charges-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .charges-stat { position: relative; display: grid; grid-template-columns: 42px minmax(0, 1fr); align-items: center; gap: 12px; min-height: 82px; padding: 15px; overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); }
    .charges-stat::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; background: #0f766e; }
    .charges-stat-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; color: #0f766e; background: #ccfbf1; border-radius: 8px; font-size: 19px; }
    .charges-stat-label { display: block; color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .charges-stat-value { display: block; margin-top: 3px; color: #101828; font-size: 23px; font-weight: 900; line-height: 1; }
    .charges-stat.is-active::before { background: #16a34a; }
    .charges-stat.is-active .charges-stat-icon { color: #16a34a; background: #dcfce7; }
    .charges-stat.is-fixed::before { background: #2563eb; }
    .charges-stat.is-fixed .charges-stat-icon { color: #2563eb; background: #dbeafe; }
    .charges-stat.is-percent::before { background: #c2410c; }
    .charges-stat.is-percent .charges-stat-icon { color: #c2410c; background: #ffedd5; }
    .charges-stat.is-discount::before { background: #be123c; }
    .charges-stat.is-discount .charges-stat-icon { color: #be123c; background: #ffe4e6; }
    .charges-filter { overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .charges-filter-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 16px; background: #fcfcfd; border-bottom: 1px solid #edf0f5; }
    .charges-filter-title { display: flex; align-items: center; gap: 10px; }
    .charges-filter-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; color: #0f766e; background: #ccfbf1; border-radius: 8px; }
    .charges-filter-title strong { display: block; color: #101828; font-size: 14px; font-weight: 900; }
    .charges-result-count { color: #667085; font-size: 11px; }
    .charges-filter-body { padding: 14px 16px; }
    .charges-tools { display: grid; grid-template-columns: minmax(240px, 1fr) 210px 150px 150px auto auto; align-items: end; gap: 10px; }
    .charges-field { display: grid; gap: 5px; }
    .charges-label { color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .charges-search { position: relative; }
    .charges-search > i { position: absolute; left: 12px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; }
    .charges-search .form-control { padding-left: 37px; }
    .charges-filter .form-control, .charges-filter .form-select { min-height: 40px; border-color: #dfe4ea; border-radius: 8px; }
    .charges-filter .form-control:focus, .charges-filter .form-select:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .11); }
    .charges-filter-submit, .charges-filter-reset { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 13px; font-size: 11px; font-weight: 900; border-radius: 8px; white-space: nowrap; }
    .charges-panel { overflow: hidden; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .charges-table { margin: 0; }
    .charges-table th { padding: 13px 15px; color: #667085; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #edf0f5; white-space: nowrap; }
    .charges-table td { padding: 15px; border-color: #f1f3f6; vertical-align: middle; }
    .charge-name { color: #101828; font-size: 14px; font-weight: 900; }
    .charge-code { display: inline-flex; align-items: center; gap: 5px; margin-top: 4px; color: #667085; font-size: 11px; font-weight: 800; }
    .charge-desc { max-width: 360px; color: #667085; font-size: 12px; line-height: 1.35; }
    .charge-ad { color: #101828; font-size: 13px; font-weight: 900; white-space: nowrap; }
    .charge-ad-meta { color: #667085; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .charge-amount { color: #0f766e; font-size: 16px; font-weight: 900; white-space: nowrap; }
    .charge-amount.is-discount { color: #be123c; }
    .charge-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: 11px; font-weight: 900; white-space: nowrap; }
    .charge-pill.fixed { color: #1d4ed8; background: #dbeafe; }
    .charge-pill.percentage { color: #9a3412; background: #ffedd5; }
    .charge-pill.discount { color: #be123c; background: #ffe4e6; }
    .charge-pill.active { color: #166534; background: #dcfce7; }
    .charge-pill.inactive { color: #991b1b; background: #fee2e2; }
    .charge-applies { color: #344054; font-size: 12px; font-weight: 800; white-space: nowrap; }
    .charge-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .charge-icon-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    .charges-empty { padding: 52px 16px; text-align: center; color: #667085; }
    .charges-empty i { display: block; margin-bottom: 8px; color: #d0d5dd; font-size: 38px; }
    .charge-modal .modal-content { overflow: hidden; border: 0; border-radius: 12px; box-shadow: 0 24px 70px rgba(15, 23, 42, .2); }
    .charge-modal .modal-header { padding: 20px 22px; background: #f8fafc; border-bottom: 1px solid #e8ecf2; }
    .charge-modal-title { display: flex; align-items: center; gap: 12px; }
    .charge-modal-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; color: #fff; background: #0f766e; border-radius: 10px; font-size: 20px; }
    .charge-modal .modal-title { color: #101828; font-size: 18px; font-weight: 900; }
    .charge-modal .modal-body { display: grid; gap: 14px; padding: 20px 22px; }
    .charge-modal .form-label { margin-bottom: 6px; color: #344054; font-size: 12px; font-weight: 800; }
    .charge-modal .form-control, .charge-modal .form-select, .charge-modal .input-group-text { min-height: 42px; border-color: #dfe4ea; }
    .charge-modal .form-control:focus, .charge-modal .form-select:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .11); }
    .charge-status-card { display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e4e7ec; border-radius: 8px; }
    .charge-status-card i { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; color: #16a34a; background: #dcfce7; border-radius: 8px; }
    .charge-status-card strong { display: block; color: #1d2939; font-size: 13px; }
    .charge-status-card small { color: #667085; font-size: 11px; }
    @media (max-width: 992px) {
        .charges-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .charges-tools { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
        .charges-head, .charges-filter-head { align-items: stretch; flex-direction: column; }
        .charges-tools, .charges-stats { grid-template-columns: 1fr; }
        .charges-panel { overflow-x: auto; }
        .charges-table { min-width: 1040px; }
        .charges-add-btn { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="charges-page">
    <div class="charges-head">
        <div>
            <div class="charges-kicker"><i class="bi bi-receipt-cutoff"></i> Partners</div>
            <h4 class="charges-title">Charges and Discount</h4>
            <p class="charges-copy">Manage additional fees that can be applied to orders, delivery, dealers, customers, or AD purchase orders.</p>
        </div>
        <button class="btn btn-danger charges-add-btn" type="button" data-bs-toggle="modal" data-bs-target="#chargeCreateModal">
            <i class="bi bi-plus-lg"></i> Add Charge
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

    <div class="charges-stats">
        <div class="charges-stat">
            <span class="charges-stat-icon"><i class="bi bi-receipt"></i></span>
            <span>
                <span class="charges-stat-label">Total Charges</span>
                <span class="charges-stat-value">{{ number_format($summary['total']) }}</span>
            </span>
        </div>
        <div class="charges-stat is-active">
            <span class="charges-stat-icon"><i class="bi bi-check2-circle"></i></span>
            <span>
                <span class="charges-stat-label">Active</span>
                <span class="charges-stat-value">{{ number_format($summary['active']) }}</span>
            </span>
        </div>
        <div class="charges-stat is-fixed">
            <span class="charges-stat-icon"><i class="bi bi-cash-stack"></i></span>
            <span>
                <span class="charges-stat-label">Fixed Amount</span>
                <span class="charges-stat-value">{{ number_format($summary['fixed']) }}</span>
            </span>
        </div>
        <div class="charges-stat is-percent">
            <span class="charges-stat-icon"><i class="bi bi-percent"></i></span>
            <span>
                <span class="charges-stat-label">Percentage</span>
                <span class="charges-stat-value">{{ number_format($summary['percentage']) }}</span>
            </span>
        </div>
        <div class="charges-stat is-discount">
            <span class="charges-stat-icon"><i class="bi bi-tags"></i></span>
            <span>
                <span class="charges-stat-label">Discount</span>
                <span class="charges-stat-value">{{ number_format($summary['discount']) }}</span>
            </span>
        </div>
    </div>

    <div class="charges-filter">
        <div class="charges-filter-head">
            <div class="charges-filter-title">
                <span class="charges-filter-icon"><i class="bi bi-funnel"></i></span>
                <div>
                    <strong>Find Charges</strong>
                    <div class="charges-result-count">{{ number_format($charges->count()) }} result(s) shown</div>
                </div>
            </div>
        </div>
        <div class="charges-filter-body">
            <form action="{{ route('charges') }}" method="GET" class="charges-tools">
                <div class="charges-field">
                    <label class="charges-label" for="chargeSearch">Search</label>
                    <div class="charges-search">
                        <i class="bi bi-search"></i>
                        <input type="text" id="chargeSearch" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, code, or description">
                    </div>
                </div>
                @if(auth()->user()->role === 'Admin')
                    <div class="charges-field">
                        <label class="charges-label" for="chargeAdUser">AD</label>
                        <select id="chargeAdUser" name="ad_user_id" class="form-select">
                            <option value="">All ADs</option>
                            @foreach($adUsers as $adUser)
                                <option value="{{ $adUser->id }}" @if((string) request('ad_user_id') === (string) $adUser->id) selected @endif>
                                    {{ optional($adUser->ad)->business_name ?: $adUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="charges-field">
                    <label class="charges-label" for="chargeStatus">Status</label>
                    <select id="chargeStatus" name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" @if(request('status') === 'active') selected @endif>Active</option>
                        <option value="inactive" @if(request('status') === 'inactive') selected @endif>Inactive</option>
                    </select>
                </div>
                <div class="charges-field">
                    <label class="charges-label" for="chargeType">Type</label>
                    <select id="chargeType" name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="fixed" @if(request('type') === 'fixed') selected @endif>Fixed Amount</option>
                        <option value="percentage" @if(request('type') === 'percentage') selected @endif>Percentage</option>
                        <option value="discount" @if(request('type') === 'discount') selected @endif>Discount</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary charges-filter-submit"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('charges') }}" class="btn btn-light charges-filter-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </form>
        </div>
    </div>

    <div class="charges-panel">
        <table class="table charges-table align-middle">
            <thead>
                <tr>
                    <th>Charge</th>
                    <th>AD</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Applies To</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($charges as $charge)
                    <tr>
                        <td>
                            <div class="charge-name">{{ $charge->name }}</div>
                            <div class="charge-code"><i class="bi bi-upc-scan"></i>{{ $charge->code }}</div>
                        </td>
                        <td>
                            <div class="charge-ad">{{ optional(optional($charge->adUser)->ad)->business_name ?: optional($charge->adUser)->name ?: 'No AD assigned' }}</div>
                            <div class="charge-ad-meta">{{ optional(optional($charge->adUser)->ad)->store_code ?: optional($charge->adUser)->role ?: 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="charge-desc">{{ $charge->description ?: 'No description provided.' }}</div>
                        </td>
                        <td><span class="charge-amount {{ $charge->charge_type === 'discount' ? 'is-discount' : '' }}">{{ $charge->formattedAmount() }}</span></td>
                        <td>
                            <span class="charge-pill {{ $charge->charge_type }}">
                                <i class="bi {{ $charge->charge_type === 'percentage' ? 'bi-percent' : ($charge->charge_type === 'discount' ? 'bi-tags' : 'bi-cash') }}"></i>
                                {{ $charge->typeLabel() }}
                            </span>
                        </td>
                        <td><span class="charge-applies">{{ $charge->appliesToLabel() }}</span></td>
                        <td>
                            <span class="charge-pill {{ $charge->is_active ? 'active' : 'inactive' }}">
                                <i class="bi {{ $charge->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                {{ $charge->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="charge-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary charge-icon-btn" data-bs-toggle="modal" data-bs-target="#chargeEditModal{{ $charge->id }}" title="Edit charge">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('charges.destroy', $charge->id) }}" method="POST" onsubmit="return confirm('Delete this other charge?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger charge-icon-btn" type="submit" title="Delete charge">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="charges-empty">
                                <i class="bi bi-receipt"></i>
                                No other charges found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('other_charges.partials.form-modal', [
    'modalId' => 'chargeCreateModal',
    'modalTitle' => 'Add Charges and Discount',
    'method' => 'POST',
    'action' => route('charges.store'),
    'charge' => null,
    'adUsers' => $adUsers,
    'submitLabel' => 'Save Charge',
])

@foreach($charges as $charge)
    @include('other_charges.partials.form-modal', [
        'modalId' => 'chargeEditModal' . $charge->id,
        'modalTitle' => 'Edit Other Charge',
        'method' => 'PUT',
        'action' => route('charges.update', $charge->id),
        'charge' => $charge,
        'adUsers' => $adUsers,
        'submitLabel' => 'Update Charge',
    ])
@endforeach
@endsection
