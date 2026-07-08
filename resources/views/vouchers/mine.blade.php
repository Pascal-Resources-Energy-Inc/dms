@extends('layouts.header')

@section('css')
<style>
    .my-voucher-page { --red: #b91c1c; --text: #101828; --muted: #667085; display: grid; gap: 16px; padding-top: 18px; }
    .my-voucher-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 22px; background: linear-gradient(135deg, #fff, #fff7f7); border: 1px solid #f1e4e4; border-radius: 14px; box-shadow: 0 10px 28px rgba(15, 23, 42, .05); }
    .my-voucher-kicker { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 5px; color: var(--red); font-size: 11px; font-weight: 900; text-transform: uppercase; }
    .my-voucher-title { margin: 0; color: var(--text); font-size: 25px; font-weight: 900; }
    .my-voucher-copy { margin: 4px 0 0; color: var(--muted); font-size: 12px; }
    .my-voucher-back { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 8px 13px; color: #475467; font-size: 11px; font-weight: 800; background: #fff; border: 1px solid #dfe4ea; border-radius: 8px; }
    .my-voucher-back:hover { color: var(--red); background: #fff7f7; border-color: #fecaca; }
    .my-voucher-summary { display: grid; grid-template-columns: repeat(3, minmax(170px, 1fr)); gap: 12px; }
    .my-voucher-stat { display: flex; align-items: center; gap: 12px; padding: 16px; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; box-shadow: 0 8px 22px rgba(15, 23, 42, .045); }
    .my-voucher-stat i { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; color: #475467; font-size: 20px; background: #f2f4f7; border-radius: 10px; }
    .my-voucher-stat.used i { color: #15803d; background: #dcfce7; }
    .my-voucher-stat.unused i { color: #1d4ed8; background: #dbeafe; }
    .my-voucher-stat small { display: block; color: var(--muted); font-size: 10px; font-weight: 900; text-transform: uppercase; }
    .my-voucher-stat strong { display: block; margin-top: 3px; color: var(--text); font-size: 23px; line-height: 1; }
    .my-voucher-panel { overflow: hidden; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .my-voucher-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 17px; background: #f8fafc; border-bottom: 1px solid #edf0f5; }
    .my-voucher-tabs { display: flex; flex-wrap: wrap; gap: 6px; }
    .my-voucher-tab { display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; color: #667085; font-size: 10px; font-weight: 900; background: #fff; border: 1px solid #dfe4ea; border-radius: 999px; }
    .my-voucher-tab.active { color: #fff; background: var(--red); border-color: var(--red); }
    .my-voucher-search { display: flex; gap: 7px; }
    .my-voucher-search .form-control { min-width: 230px; min-height: 37px; font-size: 11px; border-color: #dfe4ea; }
    .my-voucher-search .btn { font-size: 10px; font-weight: 800; }
    .my-voucher-table { min-width: 950px; margin: 0; }
    .my-voucher-table th { padding: 12px 14px; color: #667085; font-size: 9px; font-weight: 900; text-transform: uppercase; white-space: nowrap; background: #fff; border-bottom: 1px solid #edf0f5; }
    .my-voucher-table td { padding: 14px; color: #344054; font-size: 11px; vertical-align: middle; border-color: #f0f2f5; }
    .my-voucher-code { color: var(--red); font-weight: 900; letter-spacing: .04em; }
    .my-voucher-description { display: block; max-width: 250px; margin-top: 3px; color: #98a2b3; font-size: 10px; }
    .my-voucher-discount { color: #0f766e; font-weight: 900; }
    .my-voucher-areas { display: flex; flex-wrap: wrap; gap: 4px; max-width: 280px; }
    .my-voucher-area { padding: 4px 6px; color: #1d4ed8; font-size: 9px; font-weight: 800; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 5px; }
    .my-voucher-status { display: inline-flex; padding: 5px 8px; font-size: 9px; font-weight: 900; border-radius: 999px; }
    .my-voucher-status.active { color: #166534; background: #dcfce7; }
    .my-voucher-status.scheduled { color: #075985; background: #e0f2fe; }
    .my-voucher-status.expired, .my-voucher-status.inactive, .my-voucher-status.used-up { color: #991b1b; background: #fee2e2; }
    .my-voucher-empty { padding: 55px 16px; text-align: center; color: var(--muted); }
    .my-voucher-empty i { display: block; margin-bottom: 8px; color: #d0d5dd; font-size: 38px; }
    @media (max-width: 768px) {
        .my-voucher-head, .my-voucher-toolbar { align-items: stretch; flex-direction: column; }
        .my-voucher-summary { grid-template-columns: 1fr; }
        .my-voucher-search .form-control { min-width: 0; flex: 1; }
    }
</style>
@endsection

@section('content')
<section class="my-voucher-page">
    <div class="my-voucher-head">
        <div>
            <div class="my-voucher-kicker"><i class="ti ti-ticket"></i> {{ $storeCode ?: 'AD Account' }}</div>
            <h3 class="my-voucher-title">My Vouchers</h3>
            <p class="my-voucher-copy">Review vouchers assigned to your account and monitor their usage.</p>
        </div>
        <a href="{{ route('ad-dashboard') }}" class="my-voucher-back"><i class="ti ti-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="my-voucher-summary">
        <div class="my-voucher-stat"><i class="ti ti-ticket"></i><div><small>Total Vouchers</small><strong>{{ number_format($totalCount) }}</strong></div></div>
        <div class="my-voucher-stat used"><i class="ti ti-circle-check"></i><div><small>Used Vouchers</small><strong>{{ number_format($usedCount) }}</strong></div></div>
        <div class="my-voucher-stat unused"><i class="ti ti-ticket-off"></i><div><small>Unused Vouchers</small><strong>{{ number_format($unusedCount) }}</strong></div></div>
    </div>

    <div class="my-voucher-panel">
        <div class="my-voucher-toolbar">
            <div class="my-voucher-tabs">
                <a href="{{ route('vouchers.mine') }}" class="my-voucher-tab {{ $filter === 'all' ? 'active' : '' }}">All <span>{{ number_format($totalCount) }}</span></a>
                <a href="{{ route('vouchers.mine', ['status' => 'used']) }}" class="my-voucher-tab {{ $filter === 'used' ? 'active' : '' }}">Used <span>{{ number_format($usedCount) }}</span></a>
                <a href="{{ route('vouchers.mine', ['status' => 'unused']) }}" class="my-voucher-tab {{ $filter === 'unused' ? 'active' : '' }}">Unused <span>{{ number_format($unusedCount) }}</span></a>
            </div>
            <form method="GET" action="{{ route('vouchers.mine') }}" class="my-voucher-search">
                @if($filter !== 'all')<input type="hidden" name="status" value="{{ $filter }}">@endif
                <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search code or description">
                <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i> Search</button>
            </form>
        </div>

        @if($vouchers->count())
            <div class="table-responsive">
                <table class="table my-voucher-table">
                    <thead><tr><th>Voucher</th><th>Discount</th><th>Areas</th><th>Usage</th><th>Validity</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($vouchers as $voucher)
                        @php $status = $voucher->statusLabel(); @endphp
                        <tr>
                            <td><span class="my-voucher-code">{{ $voucher->code }}</span>@if($voucher->description)<span class="my-voucher-description">{{ $voucher->description }}</span>@endif</td>
                            <td><span class="my-voucher-discount">{{ $voucher->discount_type === 'percent' ? number_format($voucher->discount_value, 2) . '%' : 'PHP ' . number_format($voucher->discount_value, 2) }}</span><span class="my-voucher-description">Minimum: PHP {{ number_format($voucher->minimum_order_amount, 2) }}</span></td>
                            <td><div class="my-voucher-areas">@forelse($voucher->areaNames() as $area)<span class="my-voucher-area">{{ $area }}</span>@empty<span class="text-muted">All assigned areas</span>@endforelse</div></td>
                            <td><strong>{{ number_format($voucher->used_count) }}</strong> / {{ $voucher->usage_limit ? number_format($voucher->usage_limit) : 'Unlimited' }}</td>
                            <td>{{ $voucher->starts_at ? $voucher->starts_at->format('M d, Y') : 'Available now' }}<span class="my-voucher-description">to {{ $voucher->expires_at ? $voucher->expires_at->format('M d, Y') : 'No expiry' }}</span></td>
                            <td><span class="my-voucher-status {{ strtolower(str_replace(' ', '-', $status)) }}">{{ $status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="my-voucher-empty"><i class="ti ti-ticket-off"></i><strong>No {{ $filter === 'all' ? '' : $filter }} vouchers found</strong><div>Try another tab or adjust your search.</div></div>
        @endif
    </div>
</section>
@endsection
