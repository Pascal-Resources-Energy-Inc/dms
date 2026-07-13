@extends('layouts.header')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css" rel="stylesheet">
<style>
    .sir-page {
        --sir-blue: #2f6fa4;
        --sir-blue-dark: #284f99;
        --sir-ink: #17324d;
        --sir-border: #dbe4ef;
        --sir-muted: #64748b;
        --sir-grid: #273449;
        --sir-center: #ddebf7;
        --sir-total: #e2f0d9;
        --sir-soft: #f8fafc;
        padding: 18px 12px 32px;
        background: #f6f8fb;
    }

    .sir-hero,
    .sir-card,
    .sir-kpi {
        background: #fff;
        border: 1px solid var(--sir-border);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .sir-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        margin-bottom: 14px;
        border-left: 5px solid var(--sir-blue);
    }

    .sir-eyebrow {
        color: var(--sir-blue);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .sir-hero h3 {
        color: var(--sir-ink);
        font-size: 23px;
        font-weight: 900;
        margin: 3px 0;
    }

    .sir-hero p {
        color: var(--sir-muted);
        margin: 0;
        font-size: 13px;
    }

    .sir-actions,
    .sir-kpis {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .sir-actions .btn,
    .sir-card .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        font-weight: 800;
        min-height: 38px;
    }

    .sir-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .sir-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
    }

    .sir-kpi-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #eef5ff;
        color: var(--sir-blue);
        font-size: 20px;
    }

    .sir-kpi small,
    .sir-kpi strong {
        display: block;
    }

    .sir-kpi small {
        color: var(--sir-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sir-kpi strong {
        color: var(--sir-ink);
        font-size: 21px;
        font-weight: 900;
    }

    .sir-card {
        padding: 14px;
        margin-bottom: 14px;
    }

    .sir-card .form-label {
        color: #334e68;
        font-size: 12px;
        font-weight: 900;
    }

    .sir-card .form-control {
        border-color: #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        min-height: 38px;
    }

    .sir-card .form-control:focus {
        border-color: var(--sir-blue);
        box-shadow: 0 0 0 .18rem rgba(47, 111, 164, .14);
    }

    .sir-filter-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
    }

    .sir-filter-actions .btn-outline-secondary {
        width: 42px;
        padding-left: 0;
        padding-right: 0;
    }

    .sir-report-card {
        padding: 0;
        overflow: hidden;
    }

    .sir-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--sir-border);
        background: var(--sir-soft);
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        flex-wrap: wrap;
    }

    .sir-territory-note {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--sir-blue-dark);
    }

    .sir-table-wrap {
        overflow: auto;
        max-height: 74vh;
        background: #fff;
    }

    .sir-table {
        width: 100%;
        min-width: 1510px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        table-layout: fixed;
    }

    .sir-table th,
    .sir-table td {
        border-right: 1px solid var(--sir-grid);
        border-bottom: 1px solid var(--sir-grid);
        padding: 4px 7px;
        height: 28px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .sir-table thead th {
        color: #000;
        background: #fff;
        font-weight: 800;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .sir-table th:first-child,
    .sir-table td:first-child {
        border-left: 1px solid var(--sir-grid);
    }

    .sir-table thead tr:first-child th {
        border-top: 1px solid var(--sir-grid);
    }

    .sir-col-center {
        width: 100px;
    }

    .sir-col-location {
        width: 205px;
    }

    .sir-col-designation {
        width: 170px;
    }

    .sir-col-name {
        width: 150px;
    }

    .sir-col-month {
        width: 74px;
    }

    .sir-summary-title {
        background: var(--sir-blue-dark) !important;
        color: #fff !important;
        text-align: center !important;
        height: 42px;
    }

    .sir-month-title {
        font-size: 14px;
    }

    .sir-center-row td {
        background: var(--sir-center);
        color: #f00;
        font-weight: 700;
    }

    .sir-person-row td {
        color: #f00;
        background: #fff;
    }

    .sir-total-row td {
        background: var(--sir-total);
        color: #f00;
        font-weight: 800;
    }

    .sir-grand-signups td {
        background: var(--sir-center);
        color: #000;
        font-weight: 800;
    }

    .sir-grand-amounts td {
        background: var(--sir-total);
        color: #000;
        font-weight: 800;
    }

    .sir-number {
        text-align: right;
    }

    .sir-signup-link {
        width: 100%;
        min-width: 32px;
        padding: 0;
        border: 0;
        background: transparent;
        color: inherit;
        font: inherit;
        font-weight: 900;
        line-height: 20px;
        text-align: right;
        cursor: pointer;
        text-decoration: underline;
        text-decoration-color: rgba(47, 111, 164, .35);
        text-underline-offset: 3px;
    }

    .sir-signup-link:hover,
    .sir-signup-link:focus {
        color: var(--sir-blue-dark);
        outline: none;
        text-decoration-color: currentColor;
    }

    .sir-client-modal .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .2);
    }

    .sir-client-modal .modal-header {
        background: var(--sir-blue-dark);
        color: #fff;
        border: 0;
    }

    /* .sir-client-modal .btn-close {
        filter: invert(1) grayscale(1) brightness(3);
    } */

    .sir-client-modal .table {
        margin-bottom: 0;
    }

    .sir-client-modal .table th {
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
    }

    .sir-client-state {
        padding: 30px 16px;
        color: var(--sir-muted);
        text-align: center;
    }

    .sir-center-empty {
        padding: 30px;
        text-align: center;
        color: var(--sir-muted);
    }

    .sir-label {
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sir-location {
        color: #334155;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 767.98px) {
        .sir-hero,
        .sir-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .sir-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .sir-kpis {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .sidebar,
        .topbar,
        .sir-actions,
        .sir-filters {
            display: none !important;
        }

        .sir-page {
            padding: 0;
        }

        .sir-hero,
        .sir-card {
            border: 0;
            box-shadow: none;
        }

        .sir-table-wrap {
            overflow: visible;
            max-height: none;
        }

        .sir-table thead th {
            position: static;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid sir-page">
    <div class="sir-hero">
        <div>
            <div class="sir-eyebrow">Reports</div>
            <h3>Sign Up Incentives Report</h3>
            <p>Monthly sign up counts and computed incentives per center, designation, and SEDP account.</p>
        </div>
        <div class="sir-actions">
            <a class="btn btn-outline-primary" href="{{ route('signup-incentives.export', request()->query()) }}">
                <i class="ti ti-download"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="ti ti-printer"></i> Print
            </button>
        </div>
    </div>

    <div class="sir-kpis">
        <div class="sir-kpi">
            <span class="sir-kpi-icon"><i class="ti ti-building-store"></i></span>
            <div><small>Centers</small><strong>{{ count($rows) }}</strong></div>
        </div>
        <div class="sir-kpi">
            <span class="sir-kpi-icon"><i class="ti ti-user-plus"></i></span>
            <div><small>Total Sign Ups</small><strong>{{ number_format($grandTotalSignups) }}</strong></div>
        </div>
        <div class="sir-kpi">
            <span class="sir-kpi-icon"><i class="ti ti-cash"></i></span>
            <div><small>Total Incentives</small><strong>{{ number_format($grandTotalAmount, 2) }}</strong></div>
        </div>
    </div>

    <div class="sir-card sir-filters">
        <form method="GET" action="{{ route('signup-incentives') }}" class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Year</label>
                <input type="number" class="form-control" name="year" value="{{ $year }}" min="2000" max="2100">
            </div>
            <div class="col-lg-3 col-md-8">
                <label class="form-label">{{ $isSedpTerritoryView ? 'My Territory Centers' : 'Center' }}</label>
                <select class="form-control" name="center">
                    <option value="">{{ $isSedpTerritoryView ? 'All My Centers' : 'All Centers' }}</option>
                    @foreach($centers as $center)
                        <option value="{{ $center }}" {{ $selectedCenter === $center ? 'selected' : '' }}>{{ $center }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-12">
                <div class="sir-filter-actions">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i> Apply</button>
                    <a href="{{ route('signup-incentives') }}" class="btn btn-outline-secondary" title="Reset filters"><i class="ti ti-refresh"></i></a>
                </div>
            </div>
        </form>
    </div>

    <div class="sir-card sir-report-card">
        {{-- <div class="sir-meta">
            <span>Year: {{ $year }}</span>
            <span>Source: admin_crms.clients</span>
            @if($isSedpTerritoryView)
                <span class="sir-territory-note"><i class="ti ti-map-pin"></i> Limited to {{ count($territoryCenters) }} territory center{{ count($territoryCenters) === 1 ? '' : 's' }}</span>
            @else
                <span>All centers</span>
            @endif
        </div> --}}
        <div class="sir-table-wrap">
            <table class="sir-table">
                <colgroup>
                    <col class="sir-col-center">
                    <col class="sir-col-designation">
                    <col class="sir-col-name">
                    @foreach($months as $month)
                        <col class="sir-col-month">
                    @endforeach
                    <col class="sir-col-month">
                </colgroup>
                <thead>
                    <tr>
                        <th class="sir-summary-title" colspan="3">Sign Up Incentive Summary</th>
                        <th class="sir-month-title" colspan="13"># of Sign Ups Per Month</th>
                    </tr>
                    <tr>
                        <th>Center</th>
                        <th>Designation</th>
                        <th>Name</th>
                        @foreach($months as $month)
                            <th>{{ $month }}</th>
                        @endforeach
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="sir-center-row">
                            <td rowspan="{{ count($row['people']) + 1 }}">{{ $row['center'] }}</td>
                            <td></td>
                            <td></td>
                            @foreach(array_keys($months) as $month)
                                <td class="sir-number">
                                    @if($row['signups'][$month])
                                        <button type="button" class="sir-signup-link js-signup-clients"
                                            data-center="{{ $row['center'] }}"
                                            data-month="{{ $month }}"
                                            data-month-name="{{ $months[$month] }}"
                                            title="View {{ number_format($row['signups'][$month]) }} client{{ $row['signups'][$month] === 1 ? '' : 's' }}">
                                            {{ number_format($row['signups'][$month]) }}
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                            <td class="sir-number">{{ number_format($row['total_signups']) }}</td>
                        </tr>

                        @forelse($row['people'] as $person)
                            <tr class="sir-person-row">
                                <td>{{ $person['designation'] }}</td>
                                <td>{{ $person['name'] }}</td>
                                @foreach(array_keys($months) as $month)
                                    <td class="sir-number">{{ $person['amounts'][$month] ? number_format($person['amounts'][$month], 2) : '-' }}</td>
                                @endforeach
                                <td class="sir-number">{{ number_format($person['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr class="sir-person-row">
                                <td colspan="2" class="sir-label">No SEDP CDW/CDW2/SPOM assigned to this center</td>
                                @foreach(array_keys($months) as $month)
                                    <td class="sir-number">-</td>
                                @endforeach
                                <td class="sir-number">-</td>
                            </tr>
                        @endforelse

                        <tr class="sir-total-row">
                            <td class="sir-label" colspan="2">Total Incentive Earned</td>
                            <td></td>
                            @foreach(array_keys($months) as $month)
                                <td class="sir-number">{{ $row['monthly_total_amounts'][$month] ? number_format($row['monthly_total_amounts'][$month], 2) : '-' }}</td>
                            @endforeach
                            <td class="sir-number">{{ number_format($row['total_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="sir-center-empty">
                                {{ $isSedpTerritoryView ? 'No sign up incentive records found for your assigned territory.' : 'No sign up incentive records found for this filter.' }}
                            </td>
                        </tr>
                    @endforelse

                    <tr class="sir-grand-signups">
                        <td></td>
                        <td class="sir-label">Grand Total Sign Up</td>
                        <td></td>
                        @foreach(array_keys($months) as $month)
                            <td class="sir-number">{{ number_format($grandSignups[$month]) }}</td>
                        @endforeach
                        <td class="sir-number">{{ number_format($grandTotalSignups) }}</td>
                    </tr>
                    <tr class="sir-grand-amounts">
                        <td></td>
                        <td class="sir-label">Grand Total Php</td>
                        <td></td>
                        @foreach(array_keys($months) as $month)
                            <td class="sir-number">{{ $grandAmounts[$month] ? number_format($grandAmounts[$month], 2) : '-' }}</td>
                        @endforeach
                        <td class="sir-number">{{ number_format($grandTotalAmount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade sir-client-modal" id="signupClientsModal" tabindex="-1" aria-labelledby="signupClientsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="sir-eyebrow text-white-50">Client Sign Ups</div>
                    <h5 class="modal-title mb-0" id="signupClientsModalTitle">Clients</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="signupClientsState" class="sir-client-state">Loading clients…</div>
                <div id="signupClientsTableWrap" class="d-none table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr><th class="ps-3">Client</th><th>Contact</th><th class="pe-3">Signed Up</th></tr>
                        </thead>
                        <tbody id="signupClientsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalElement = document.getElementById('signupClientsModal');
    var modal = new bootstrap.Modal(modalElement);
    var title = document.getElementById('signupClientsModalTitle');
    var state = document.getElementById('signupClientsState');
    var tableWrap = document.getElementById('signupClientsTableWrap');
    var body = document.getElementById('signupClientsBody');
    var endpoint = '{{ route('signup-incentives.clients') }}';

    document.querySelectorAll('.js-signup-clients').forEach(function (button) {
        button.addEventListener('click', function () {
            var center = button.dataset.center;
            var month = button.dataset.month;
            var monthName = button.dataset.monthName;

            title.textContent = center + ' — ' + monthName + ' {{ $year }}';
            state.textContent = 'Loading clients…';
            state.classList.remove('d-none');
            tableWrap.classList.add('d-none');
            body.innerHTML = '';
            modal.show();

            var params = new URLSearchParams({ center: center, month: month, year: '{{ $year }}' });

            fetch(endpoint + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Unable to load clients.');
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (!data.clients || !data.clients.length) {
                        state.textContent = 'No clients were found for this center and month.';
                        return;
                    }

                    data.clients.forEach(function (client) {
                        var row = document.createElement('tr');
                        [client.name, client.contact, client.created_at].forEach(function (value, index) {
                            var cell = document.createElement('td');
                            cell.textContent = value;
                            if (index === 0) cell.className = 'ps-3 fw-semibold';
                            if (index === 2) cell.className = 'pe-3 text-nowrap';
                            row.appendChild(cell);
                        });
                        body.appendChild(row);
                    });

                    state.classList.add('d-none');
                    tableWrap.classList.remove('d-none');
                })
                .catch(function () {
                    state.textContent = 'Unable to load clients. Please try again.';
                });
        });
    });
});
</script>
@endsection
