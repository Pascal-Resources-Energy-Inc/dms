@extends('layouts.header')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css" rel="stylesheet">
<style>
    .cci-page { --navy:#163a5f; --blue:#2166b1; --sky:#eaf4ff; --green:#0e7a55; --line:#d8e4f0; --muted:#64748b; padding:18px 12px 36px; }
    .cci-hero,.cci-panel,.cci-kpi { background:#fff; border:1px solid var(--line); border-radius:16px; box-shadow:0 12px 28px rgba(15,42,70,.07); }
    .cci-hero { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:24px; margin-bottom:16px; background:linear-gradient(130deg,#fff 0%,#f2f8ff 100%); }
    .cci-eyebrow { color:var(--blue); font-size:11px; font-weight:900; letter-spacing:.11em; text-transform:uppercase; }
    .cci-hero h3 { color:var(--navy); font-size:25px; font-weight:900; margin:4px 0 7px; }
    .cci-hero p { color:var(--muted); margin:0; }
    .cci-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .cci-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .cci-kpi { display:flex; gap:12px; align-items:center; padding:15px; }
    .cci-kpi-icon { width:43px; height:43px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:var(--sky); color:var(--blue); font-size:21px; }
    .cci-kpi.incentive .cci-kpi-icon { background:#e5f6ed; color:var(--green); }
    .cci-kpi small,.cci-kpi strong { display:block; } .cci-kpi small { color:var(--muted); font-size:10px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; } .cci-kpi strong { color:var(--navy); font-size:22px; font-weight:900; }
    .cci-panel { padding:16px; margin-bottom:16px; }
    .cci-panel .form-label { color:#36516d; font-size:12px; font-weight:800; }
    .cci-rate-note { display:flex; align-items:center; height:38px; color:var(--green); font-size:13px; font-weight:800; }
    .cci-report { padding:0; overflow:hidden; }
    .cci-meta { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; padding:12px 16px; border-bottom:1px solid var(--line); color:var(--muted); font-size:12px; font-weight:700; }
    .cci-table-wrap { overflow:auto; max-height:70vh; }
    .cci-table { width:100%; min-width:1350px; border-collapse:separate; border-spacing:0; font-size:13px; }
    .cci-table th,.cci-table td { border-right:1px solid var(--line); border-bottom:1px solid var(--line); padding:9px 10px; white-space:nowrap; }
    .cci-table th { position:sticky; top:0; z-index:2; color:#fff; background:var(--navy); text-align:center; font-size:11px; letter-spacing:.03em; }
    .cci-table th:first-child,.cci-table td:first-child { border-left:1px solid var(--line); }
    .cci-table tbody tr:hover td { background:#f4f9ff; } .cci-table .cci-total td { color:#0d543e; background:#e9f7ef; font-weight:900; }
    .cci-number { text-align:right; } .cci-center { font-weight:800; color:var(--navy); }
    .cci-transaction { border:0; padding:0; color:var(--blue); background:transparent; font:inherit; font-weight:800; cursor:pointer; text-decoration:underline; text-decoration-color:#b7d4f0; text-underline-offset:3px; }
    .cci-transaction:hover { color:var(--navy); }
    .cci-empty { padding:36px !important; text-align:center; color:var(--muted); }
    .cci-modal .modal-content { overflow:hidden; border:0; border-radius:16px; } .cci-modal .modal-header { color:#fff; background:var(--navy); border:0; } .cci-transaction-state { padding:32px; color:var(--muted); text-align:center; }
    @media (max-width:991px) { .cci-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } .cci-hero { align-items:flex-start; flex-direction:column; } }
    @media (max-width:575px) { .cci-kpis { grid-template-columns:1fr; } }
    @media print { .sidebar,.topbar,.cci-actions,.cci-filters { display:none!important; } .cci-page { padding:0; } .cci-hero,.cci-panel { border:0; box-shadow:none; } .cci-table-wrap { max-height:none; overflow:visible; } .cci-table th { position:static; } }
</style>
@endsection

@section('content')
<div class="container-fluid cci-page">
    <section class="cci-hero">
        <div>
            <div class="cci-eyebrow">Reports / Incentives</div>
            <h3>Center Chief Incentive Report</h3>
            <p>Eligible 330g refill transactions from Project Rise CRM, computed at a fixed ₱2.00 per refill.</p>
        </div>
        <div class="cci-actions">
            <a class="btn btn-outline-primary" href="{{ route('center-chief-incentives.export', request()->query()) }}"><i class="ti ti-download"></i> Export CSV</a>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer"></i> Print</button>
        </div>
    </section>

    <section class="cci-kpis">
        <div class="cci-kpi"><span class="cci-kpi-icon"><i class="ti ti-building-community"></i></span><div><small>Eligible Centers</small><strong>{{ number_format(count($rows)) }}</strong></div></div>
        <div class="cci-kpi"><span class="cci-kpi-icon"><i class="ti ti-gas-station"></i></span><div><small>330g Refills</small><strong>{{ number_format($grandTotalRefills) }}</strong></div></div>
        <div class="cci-kpi incentive"><span class="cci-kpi-icon"><i class="ti ti-cash"></i></span><div><small>Total Incentive</small><strong>₱{{ number_format($grandIncentive, 2) }}</strong></div></div>
        <div class="cci-kpi"><span class="cci-kpi-icon"><i class="ti ti-calculator"></i></span><div><small>Rate Per Refill</small><strong>₱{{ number_format($rate, 2) }}</strong></div></div>
    </section>

    <section class="cci-panel cci-filters">
        <form method="GET" action="{{ route('center-chief-incentives') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-4"><label class="form-label">Report Year</label><input type="number" name="year" class="form-control" value="{{ $year }}" min="2000" max="2100"></div>
            <div class="col-lg-5 col-md-8"><label class="form-label">Center</label><select class="form-control" name="center"><option value="">All Centers</option>@foreach($centers as $center)<option value="{{ $center }}" {{ $selectedCenter === $center ? 'selected' : '' }}>{{ $center }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-4"><label class="form-label">Incentive Rate</label><div class="cci-rate-note"><i class="ti ti-lock me-1"></i> Fixed at ₱2.00</div></div>
            <div class="col-lg-2 col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i> Apply Filters</button></div>
        </form>
    </section>

    <section class="cci-panel cci-report">
        <div class="cci-meta"><span>Year: {{ $year }}</span><span><i class="ti ti-database"></i> Source: Project Rise CRM transactions and Center Chief assignments</span><span><i class="ti ti-circle-check"></i> Only 330g refill line items; cancelled, voided, and deleted records are excluded</span></div>
        <div class="cci-table-wrap"><table class="cci-table"><thead><tr><th>Center</th><th>Center Chief</th>@foreach($months as $month)<th>{{ $month }}</th>@endforeach<th>Total 330g Refills</th><th>Rate</th><th>Center Chief Incentive</th></tr></thead><tbody>
            @forelse($rows as $row)<tr><td class="cci-center">{{ $row['center'] }}</td><td class="fw-bold">{{ $row['center_chief'] }}</td>@foreach(array_keys($months) as $month)<td class="cci-number">@if($row['monthly_refills'][$month])<button type="button" class="cci-transaction js-cci-transactions" data-center="{{ $row['center'] }}" data-center-chief="{{ $row['center_chief'] }}" data-center-chief-id="{{ $row['center_chief_id'] }}" data-month="{{ $month }}" data-month-name="{{ $months[$month] }}" title="View {{ $row['center_chief'] }}'s transactions">{{ number_format($row['monthly_refills'][$month]) }}</button>@else 0 @endif</td>@endforeach<td class="cci-number fw-bold">{{ number_format($row['total_refills']) }}</td><td class="cci-number">₱{{ number_format($rate, 2) }}</td><td class="cci-number fw-bold text-success">₱{{ number_format($row['incentive'], 2) }}</td></tr>
            @empty <tr><td colspan="17" class="cci-empty"><i class="ti ti-inbox fs-4 d-block mb-2"></i>No 330g refill transactions assigned to a Center Chief were found for this filter.</td></tr>@endforelse
            <tr class="cci-total"><td>Grand Total</td><td></td>@foreach(array_keys($months) as $month)<td class="cci-number">{{ number_format($grandMonthlyRefills[$month]) }}</td>@endforeach<td class="cci-number">{{ number_format($grandTotalRefills) }}</td><td class="cci-number">₱{{ number_format($rate, 2) }}</td><td class="cci-number">₱{{ number_format($grandIncentive, 2) }}</td></tr>
        </tbody></table></div>
    </section>
</div>

<div class="modal fade cci-modal" id="centerChiefTransactionsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><div class="cci-eyebrow text-white-50">Eligible 330g Refill Transactions</div><h5 class="modal-title mb-0" id="centerChiefTransactionsTitle">Transactions</h5></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-0"><div id="centerChiefTransactionsState" class="cci-transaction-state">Loading transactions…</div><div id="centerChiefTransactionsTableWrap" class="d-none table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Date</th><th>Client Name</th><th>Item</th><th>Reference</th><th class="pe-3 text-end">Quantity</th></tr></thead><tbody id="centerChiefTransactionsBody"></tbody></table></div></div></div></div></div>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = new bootstrap.Modal(document.getElementById('centerChiefTransactionsModal'));
    var title = document.getElementById('centerChiefTransactionsTitle'), state = document.getElementById('centerChiefTransactionsState'), tableWrap = document.getElementById('centerChiefTransactionsTableWrap'), body = document.getElementById('centerChiefTransactionsBody');
    var endpoint = '{{ route('center-chief-incentives.transactions') }}';
    document.querySelectorAll('.js-cci-transactions').forEach(function (button) { button.addEventListener('click', function () {
        title.textContent = button.dataset.centerChief + ' — ' + button.dataset.center + ' / ' + button.dataset.monthName + ' {{ $year }}'; state.textContent = 'Loading transactions…'; state.classList.remove('d-none'); tableWrap.classList.add('d-none'); body.innerHTML = ''; modal.show();
        var params = new URLSearchParams({center: button.dataset.center, center_chief_id: button.dataset.centerChiefId, month: button.dataset.month, year: '{{ $year }}'});
        fetch(endpoint + '?' + params.toString(), {headers:{'Accept':'application/json'}}).then(function(response){ if(!response.ok) throw new Error(); return response.json(); }).then(function(data) {
            if (!data.transactions || !data.transactions.length) { state.textContent = 'No eligible 330g refill transactions were found for this center and month.'; return; }
            data.transactions.forEach(function(transaction) { var row = document.createElement('tr'); [transaction.date, transaction.client, transaction.item, transaction.reference, transaction.quantity].forEach(function(value, index) { var cell = document.createElement('td'); cell.textContent = value; if(index === 0) cell.className = 'ps-3 text-nowrap'; if(index === 1) cell.className = 'fw-semibold'; if(index === 4) cell.className = 'pe-3 text-end fw-semibold'; row.appendChild(cell); }); body.appendChild(row); }); state.classList.add('d-none'); tableWrap.classList.remove('d-none');
        }).catch(function(){ state.textContent = 'Unable to load transactions. Please try again.'; });
    }); });
});
</script>
@endsection
