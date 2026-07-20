@extends('layouts.header')

@section('css')
<style>
    .customer-aging-page { max-width: 1540px; margin: 5.5em auto 28px; padding: 0 12px; color: #172033; }
    .customer-aging-hero { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 16px; padding: 23px 25px; color: #fff; background: linear-gradient(120deg, #102a43, #176b87 58%, #0e7490); border-radius: 14px; box-shadow: 0 14px 32px rgba(15, 23, 42, .16); }
    .customer-aging-eyebrow { color: #a5f3fc; font-size: 10px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
    .customer-aging-hero h3 { margin: 4px 0; font-size: 25px; font-weight: 900; }
    .customer-aging-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 13px; }
    .customer-aging-asof { min-width: 150px; padding: 9px 12px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 9px; }
    .customer-aging-asof span, .customer-aging-asof strong { display: block; }
    .customer-aging-asof span { color: #a5f3fc; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .customer-aging-asof strong { margin-top: 3px; font-size: 13px; }
    .customer-aging-kpi { display: flex; align-items: center; gap: 12px; height: 100%; min-height: 92px; padding: 14px; background: #fff; border: 1px solid #e3eaf0; border-radius: 12px; box-shadow: 0 5px 16px rgba(15,23,42,.04); }
    .customer-aging-kpi i { display: grid; width: 39px; height: 39px; place-items: center; color: #0369a1; background: #e0f2fe; border-radius: 9px; font-size: 19px; }
    .customer-aging-kpi.warning i { color: #b45309; background: #fef3c7; }.customer-aging-kpi.danger i { color: #b91c1c; background: #fee2e2; }.customer-aging-kpi.muted i { color: #475569; background: #e2e8f0; }
    .customer-aging-kpi span { display: block; color: #64748b; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }.customer-aging-kpi strong { display: block; margin-top: 3px; font-size: 23px; font-weight: 900; }
    .customer-aging-card { margin-top: 16px; overflow: hidden; background: #fff; border: 1px solid #e3eaf0; border-radius: 12px; box-shadow: 0 7px 21px rgba(15,23,42,.045); }.customer-aging-card-body { padding: 16px; }
    .customer-aging-card label { margin-bottom: 5px; color: #64748b; font-size: 10px; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }.customer-aging-card .form-control, .customer-aging-card .form-select { min-height: 38px; border-color: #d7e0e9; font-size: 12px; }
    .customer-aging-bucket { height: 100%; padding: 14px 16px; background: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 11px; }.customer-aging-bucket.watch { background: #fffbeb; border-color: #fde68a; }.customer-aging-bucket.critical { background: #fef2f2; border-color: #fecaca; }.customer-aging-bucket.none { background: #f8fafc; border-color: #cbd5e1; }
    .customer-aging-bucket span, .customer-aging-bucket small { display: block; color: #64748b; font-size: 10px; font-weight: 900; text-transform: uppercase; }.customer-aging-bucket strong { display: block; margin: 4px 0; font-size: 24px; font-weight: 900; }
    .customer-aging-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid #edf1f5; }.customer-aging-head h5 { margin: 0; font-size: 15px; font-weight: 900; }.customer-aging-head p { margin: 3px 0 0; color: #64748b; font-size: 11px; }
    .customer-aging-table th { padding: 12px 14px; color: #64748b; background: #f8fafc; border-color: #edf1f5; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }.customer-aging-table td { padding: 13px 14px; color: #334155; border-color: #eef2f6; font-size: 12px; vertical-align: middle; }.customer-aging-table td small { display: block; margin-top: 3px; color: #94a3b8; font-size: 10px; }
    .customer-aging-days { display: inline-flex; align-items: center; padding: 5px 8px; color: #15803d; background: #dcfce7; border-radius: 999px; font-size: 11px; font-weight: 900; }.customer-aging-days.watch { color: #b45309; background: #fef3c7; }.customer-aging-days.critical { color: #b91c1c; background: #fee2e2; }.customer-aging-days.none { color: #475569; background: #e2e8f0; }
    @media (max-width: 767px) { .customer-aging-hero { align-items: flex-start; flex-direction: column; padding: 20px; }.customer-aging-asof { width: 100%; }.customer-aging-head { align-items: flex-start; flex-direction: column; } }
    @media print { .sidebar, .topbar, .customer-aging-hero .btn, .customer-aging-card:first-of-type { display: none !important; }.customer-aging-page { margin: 0; }.customer-aging-hero { padding: 0 0 12px; color: #172033; background: none; box-shadow: none; }.customer-aging-hero p { color: #64748b; }.customer-aging-asof { display: none; } }
</style>
@endsection

@section('content')
<div class="container-fluid customer-aging-page">
    <section class="customer-aging-hero">
        <div>
            <div class="customer-aging-eyebrow">Client relationship report</div>
            <h3>Customer Aging Report</h3>
            <p>Days since each active client’s most recent transaction.</p>
        </div>
        <div class="customer-aging-asof"><span>Report as of</span><strong>{{ $asOf->format('M d, Y') }}</strong></div>
    </section>

    <section class="row g-3">
        <div class="col-xl col-md-6"><div class="customer-aging-kpi"><i class="ti ti-users"></i><div><span>Active clients</span><strong>{{ number_format($summary->total) }}</strong></div></div></div>
        <div class="col-xl col-md-6"><div class="customer-aging-kpi"><i class="ti ti-receipt"></i><div><span>With transaction</span><strong>{{ number_format($summary->transacted) }}</strong></div></div></div>
        <div class="col-xl col-md-6"><div class="customer-aging-kpi muted"><i class="ti ti-user-off"></i><div><span>No transaction</span><strong>{{ number_format($summary->noTransaction) }}</strong></div></div></div>
        <div class="col-xl col-md-6"><div class="customer-aging-kpi warning"><i class="ti ti-calendar-time"></i><div><span>Average interval</span><strong>{{ number_format($summary->averageDays, 1) }} <small>days</small></strong></div></div></div>
        <div class="col-xl col-md-6"><div class="customer-aging-kpi danger"><i class="ti ti-bell-ringing"></i><div><span>Needs follow-up</span><strong>{{ number_format($summary->followUp) }}</strong></div></div></div>
    </section>

    <section class="customer-aging-card"><div class="customer-aging-card-body"><form method="GET" action="{{ route('aging-report-customer') }}" class="row g-2 align-items-end">
        <div class="col-lg-2 col-md-4"><label for="as_of">As of</label><input id="as_of" type="date" name="as_of" class="form-control" value="{{ $asOf->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"></div>
        <div class="col-lg-3 col-md-4"><label for="center">Center</label><select id="center" name="center" class="form-select"><option value="">All accessible centers</option>@foreach($centerOptions as $center)<option value="{{ $center }}" {{ $selectedCenter === $center ? 'selected' : '' }}>{{ $center }}</option>@endforeach</select></div>
        <div class="col-lg-2 col-md-4"><label for="bucket">Aging range</label><select id="bucket" name="bucket" class="form-select"><option value="">All ranges</option>@foreach($bucketTotals as $bucket => $count)<option value="{{ $bucket }}" {{ $selectedBucket === $bucket ? 'selected' : '' }}>{{ $bucket }} days</option>@endforeach</select></div>
        <div class="col-lg-3 col-md-6"><label for="search">Find client</label><input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Name, center, or contact"></div>
        <div class="col-lg-2 col-md-6 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="ti ti-filter"></i> Apply</button><a class="btn btn-outline-secondary" href="{{ route('aging-report-customer') }}" title="Reset"><i class="ti ti-refresh"></i></a></div>
    </form></div></section>

    <section class="row g-3 mt-0">@foreach($bucketTotals as $bucket => $count)<div class="col-xl col-md-4"><div class="customer-aging-bucket {{ $bucket === '91+' ? 'critical' : ($bucket === '61-90' ? 'watch' : ($bucket === 'No transaction' ? 'none' : '')) }}"><span>{{ $bucket }}{{ $bucket === 'No transaction' ? '' : ' days' }}</span><strong>{{ number_format($count) }}</strong><small>active clients</small></div></div>@endforeach</section>

    <section class="customer-aging-card">
        <div class="customer-aging-head"><div><h5>Client Transaction Intervals</h5><p>{{ number_format($rows->count()) }} active client{{ $rows->count() === 1 ? '' : 's' }} matching the selected filters.</p></div><button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="ti ti-printer"></i> Print</button></div>
        <div class="table-responsive"><table class="table customer-aging-table mb-0"><thead><tr><th>Client</th><th>Center</th><th>Contact</th><th>Last transaction</th><th class="text-end">Days inactive</th></tr></thead><tbody>
            @forelse($rows as $row)<tr><td class="fw-semibold">{{ $row->name }}</td><td>{{ $row->center }}</td><td>{{ $row->contact }}</td><td>@if($row->last_transaction_at){{ $row->last_transaction_at->format('M d, Y') }}<small>{{ $row->last_transaction_at->format('h:i A') }}</small>@else<span class="text-muted">No recorded transaction</span>@endif</td><td class="text-end"><span class="customer-aging-days {{ $row->bucket === '91+' ? 'critical' : ($row->bucket === '61-90' ? 'watch' : ($row->bucket === 'No transaction' ? 'none' : '')) }}">{{ $row->age_days === null ? 'No transaction' : number_format($row->age_days) . ' days' }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">No active clients found for the selected filters.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
