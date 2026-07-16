@extends('layouts.header')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css" rel="stylesheet">
<style>
    .rpi-page {
        --rpi-blue: #4472c4;
        --rpi-ink: #17324d;
        --rpi-border: #dbe4ef;
        --rpi-muted: #64748b;
        --rpi-grid: #111827;
        --rpi-center: #ddebf7;
        --rpi-alert: #ff1f1f;
        --rpi-alert-bg: #f55a5a;
        --rpi-total: #e2f0d9;
        padding: 18px 12px 32px;
    }

    .rpi-hero,
    .rpi-card,
    .rpi-kpi {
        background: #fff;
        border: 1px solid var(--rpi-border);
        border-radius: 10px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    }

    .rpi-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        margin-bottom: 14px;
        border-left: 5px solid var(--rpi-blue);
    }

    .rpi-eyebrow {
        color: var(--rpi-blue);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .rpi-hero h3 {
        color: var(--rpi-ink);
        font-size: 24px;
        font-weight: 900;
        margin: 3px 0;
    }

    .rpi-hero p {
        color: var(--rpi-muted);
        margin: 0;
        font-size: 13px;
    }

    .rpi-actions,
    .rpi-kpis {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rpi-actions .btn,
    .rpi-card .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 8px;
        font-weight: 800;
    }

    .rpi-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 14px;
    }

    .rpi-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
    }

    .rpi-kpi-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e7f1ff;
        color: #0d6efd;
        font-size: 20px;
    }

    .rpi-kpi small,
    .rpi-kpi strong {
        display: block;
    }

    .rpi-kpi small {
        color: var(--rpi-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .rpi-kpi strong {
        color: var(--rpi-ink);
        font-size: 22px;
        font-weight: 900;
    }

    .rpi-card {
        padding: 14px;
        margin-bottom: 14px;
    }

    .rpi-card .form-label {
        color: #334e68;
        font-size: 12px;
        font-weight: 900;
    }

    .rpi-report-card {
        padding: 0;
        overflow: hidden;
    }

    .rpi-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--rpi-border);
        color: var(--rpi-muted);
        font-size: 12px;
        font-weight: 800;
        flex-wrap: wrap;
    }

    .rpi-table-wrap {
        overflow: auto;
        max-height: 72vh;
        background: #fff;
    }

    .rpi-table {
        width: 100%;
        min-width: 1380px;
        border-collapse: collapse;
        font-size: 13px;
    }

    .rpi-table th,
    .rpi-table td {
        border: 1px solid var(--rpi-grid);
        padding: 4px 6px;
        height: 26px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .rpi-table th {
        background: #fff;
        color: #000;
        font-weight: 800;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .rpi-summary-title {
        background: var(--rpi-blue) !important;
        color: #fff !important;
        height: 42px;
    }

    .rpi-center-row td {
        background: var(--rpi-center);
        color: #000;
        font-weight: 700;
    }

    .rpi-member-row td {
        font-weight: 600;
    }

    .rpi-summary-row td {
        background: var(--rpi-total);
        color: #000;
        font-weight: 800;
    }

    .rpi-table td.rpi-low-repeat {
        background: var(--rpi-alert-bg) !important;
        color: #fff !important;
        font-weight: 900;
    }

    .rpi-transaction-link {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: inherit;
        font: inherit;
        font-weight: 900;
        text-align: right;
        cursor: pointer;
        text-decoration: underline;
        text-decoration-color: rgba(47, 111, 164, .35);
        text-underline-offset: 3px;
    }

    .rpi-low-repeat .rpi-transaction-link {
        color: #fff;
        text-decoration-color: rgba(255, 255, 255, .65);
    }

    .rpi-transaction-link:hover,
    .rpi-transaction-link:focus {
        outline: none;
        color: var(--rpi-blue-dark);
        text-decoration-color: currentColor;
    }

    .rpi-transaction-modal .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .2);
    }

    .rpi-transaction-modal .modal-header {
        border: 0;
        background: var(--rpi-blue-dark);
        color: #fff;
    }

    /* .rpi-transaction-modal .btn-close {
        filter: invert(1) grayscale(1) brightness(3);
    } */

    .rpi-transaction-state {
        padding: 30px 16px;
        color: var(--rpi-muted);
        text-align: center;
    }

    .rpi-number {
        text-align: right;
    }

    .rpi-label {
        text-align: left;
    }

    .rpi-empty {
        padding: 30px;
        text-align: center;
        color: var(--rpi-muted);
    }

    .rpi-legend {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .rpi-legend-swatch {
        width: 14px;
        height: 14px;
        display: inline-block;
        border: 1px solid var(--rpi-grid);
        background: var(--rpi-alert-bg);
    }

    @media (max-width: 767.98px) {
        .rpi-hero,
        .rpi-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .rpi-kpis {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .sidebar,
        .topbar,
        .rpi-actions,
        .rpi-filters {
            display: none !important;
        }

        .rpi-page {
            padding: 0;
        }

        .rpi-hero,
        .rpi-card {
            border: 0;
            box-shadow: none;
        }

        .rpi-table-wrap {
            overflow: visible;
            max-height: none;
        }

        .rpi-table th {
            position: static;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid rpi-page" style="margin-top: 5.5em;">
    <div class="rpi-hero">
        <div>
            <div class="rpi-eyebrow">Reports</div>
            <h3>Repeat Purchase Incentives Report</h3>
            <p>Monthly repeat/refill counts by center, location, and member from Project Rise CRM transactions.</p>
        </div>
        <div class="rpi-actions">
            <a class="btn btn-outline-primary" href="{{ route('repeat-purchase-incentives.export', request()->query()) }}">
                <i class="ti ti-download"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="ti ti-printer"></i> Print
            </button>
        </div>
    </div>

    <div class="rpi-kpis">
        <div class="rpi-kpi">
            <span class="rpi-kpi-icon"><i class="ti ti-building-store"></i></span>
            <div><small>Centers</small><strong>{{ count($rows) }}</strong></div>
        </div>
        <div class="rpi-kpi">
            <span class="rpi-kpi-icon"><i class="ti ti-repeat"></i></span>
            <div><small>Total Refill</small><strong>{{ number_format($grandTotalRefills) }}</strong></div>
        </div>
        <div class="rpi-kpi">
            <span class="rpi-kpi-icon"><i class="ti ti-cash"></i></span>
            <div><small>Computed Incentive</small><strong>{{ number_format($grandComputedIncentive, 2) }}</strong></div>
        </div>
        <div class="rpi-kpi">
            <span class="rpi-kpi-icon"><i class="ti ti-alert-triangle"></i></span>
            <div><small>Low Repeat Cells</small><strong>{{ number_format($lowRepeatMonths) }}</strong></div>
        </div>
    </div>

    <div class="rpi-card rpi-filters">
        <form method="GET" action="{{ route('repeat-purchase-incentives') }}" class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Year</label>
                <input type="number" class="form-control" name="year" value="{{ $year }}" min="2000" max="2100">
            </div>
            <div class="col-lg-4 col-md-8">
                <label class="form-label">Center</label>
                <select class="form-control" name="center">
                    <option value="">All Centers</option>
                    @foreach($centers as $center)
                        <option value="{{ $center }}" {{ $selectedCenter === $center ? 'selected' : '' }}>{{ $center }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Incentive Rate</label>
                <input type="number" class="form-control" name="rate" value="{{ $rate }}" min="0" step="0.01">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Low Repeat Threshold</label>
                <input type="number" class="form-control" name="low_repeat_threshold" value="{{ $lowRepeatThreshold }}" min="0" step="1">
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i> Apply</button>
            </div>
        </form>
    </div>

    <div class="rpi-card rpi-report-card">
        <div class="rpi-meta">
            <span>Year: {{ $year }}</span>
            <span>Source: admin_crms.clients + transaction_details</span>
            <span><i class="ti ti-user-check"></i> Active clients only</span>
            <span class="rpi-legend"><span class="rpi-legend-swatch"></span> Repeat purchase <= {{ rtrim(rtrim(number_format($lowRepeatThreshold, 2), '0'), '.') }}</span>
        </div>
        <div class="rpi-table-wrap">
            <table class="rpi-table">
                <thead>
                    <tr>
                        <th class="rpi-summary-title" colspan="3">Repeat Purchase Incentive Summary</th>
                        <th class="rpi-summary-title" colspan="13"># of Repeat Purchases Per Month</th>
                    </tr>
                    <tr>
                        <th>Center</th>
                        {{-- <th>Location</th> --}}
                        <th>Members</th>
                        @foreach($months as $month)
                            <th>{{ $month }}</th>
                        @endforeach
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @forelse($row['members'] as $member)
                            <tr class="{{ $loop->first ? 'rpi-center-row' : 'rpi-member-row' }}">
                                <td>{{ $loop->first ? $row['center'] : '' }}</td>
                                {{-- <td>{{ $loop->first ? ($row['location'] ?: '-') : '' }}</td> --}}
                                <td class="rpi-label">{{ $member['name'] }}</td>
                                @foreach(array_keys($months) as $month)
                                    @php
                                        $repeatCount = $member['monthly_refills'][$month];
                                    @endphp
                                    <td class="rpi-number {{ $repeatCount <= $lowRepeatThreshold ? 'rpi-low-repeat' : '' }}">
                                        @if($repeatCount)
                                            <button type="button" class="rpi-transaction-link js-repeat-transactions"
                                                data-client-id="{{ $member['client_id'] }}"
                                                data-center="{{ $row['center'] }}"
                                                data-member="{{ $member['name'] }}"
                                                data-month="{{ $month }}"
                                                data-month-name="{{ $months[$month] }}"
                                                title="View {{ number_format($repeatCount) }} transaction{{ $repeatCount == 1 ? '' : 's' }}">
                                                {{ number_format($repeatCount) }}
                                            </button>
                                        @else
                                            0
                                        @endif
                                    </td>
                                @endforeach
                                <td class="rpi-number">{{ number_format($member['total_refills']) }}</td>
                            </tr>
                        @empty
                            <tr class="rpi-center-row">
                                <td>{{ $row['center'] }}</td>
                                {{-- <td>{{ $row['location'] ?: '-' }}</td> --}}
                                <td class="rpi-label">No repeat purchases found</td>
                                @foreach(array_keys($months) as $month)
                                    <td class="rpi-number">0</td>
                                @endforeach
                                <td class="rpi-number">0</td>
                            </tr>
                        @endforelse

                        <tr class="rpi-summary-row">
                            <td></td>
                            {{-- <td></td> --}}
                            <td class="rpi-label">ave</td>
                            @foreach(array_keys($months) as $month)
                                <td class="rpi-number">{{ number_format($row['monthly_average_refills'][$month], 2) }}</td>
                            @endforeach
                            <td class="rpi-number">{{ number_format($row['average_refills'], 2) }}</td>
                        </tr>
                        <tr class="rpi-summary-row">
                            <td></td>
                            {{-- <td></td> --}}
                            <td class="rpi-label">total refill</td>
                            @foreach(array_keys($months) as $month)
                                <td class="rpi-number">{{ number_format($row['monthly_total_refills'][$month]) }}</td>
                            @endforeach
                            <td class="rpi-number">{{ number_format($row['total_refills']) }}</td>
                        </tr>
                        <tr class="rpi-summary-row">
                            <td></td>
                            {{-- <td></td> --}}
                            <td class="rpi-label">computed</td>
                            @foreach(array_keys($months) as $month)
                                <td class="rpi-number">{{ number_format($row['monthly_computed_incentives'][$month], 2) }}</td>
                            @endforeach
                            <td class="rpi-number">{{ number_format($row['computed_incentive'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="rpi-empty">No repeat purchase records found for this filter.</td>
                        </tr>
                    @endforelse

                    <tr class="rpi-summary-row">
                        <td></td>
                        {{-- <td></td> --}}
                        <td class="rpi-label">Grand Total Refill</td>
                        @foreach(array_keys($months) as $month)
                            <td class="rpi-number">{{ number_format($grandMonthlyRefills[$month]) }}</td>
                        @endforeach
                        <td class="rpi-number">{{ number_format($grandTotalRefills) }}</td>
                    </tr>
                    <tr class="rpi-summary-row">
                        <td></td>
                        {{-- <td></td> --}}
                        <td class="rpi-label">Grand Computed Incentive</td>
                        @foreach(array_keys($months) as $month)
                            <td class="rpi-number">{{ number_format($grandMonthlyIncentives[$month], 2) }}</td>
                        @endforeach
                        <td class="rpi-number">{{ number_format($grandComputedIncentive, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade rpi-transaction-modal" id="repeatTransactionsModal" tabindex="-1" aria-labelledby="repeatTransactionsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="rpi-eyebrow text-white-50">Repeat Purchase Transactions</div>
                    <h5 class="modal-title mb-0" id="repeatTransactionsModalTitle">Transactions</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="repeatTransactionsState" class="rpi-transaction-state">Loading transactions…</div>
                <div id="repeatTransactionsTableWrap" class="d-none table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Date</th><th>Item</th><th>Reference</th><th class="pe-3 text-end">Quantity</th></tr>
                        </thead>
                        <tbody id="repeatTransactionsBody"></tbody>
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
            var modalElement = document.getElementById('repeatTransactionsModal');
            var modal = new bootstrap.Modal(modalElement);
            var title = document.getElementById('repeatTransactionsModalTitle');
            var state = document.getElementById('repeatTransactionsState');
            var tableWrap = document.getElementById('repeatTransactionsTableWrap');
            var body = document.getElementById('repeatTransactionsBody');
            var endpoint = '{{ route('repeat-purchase-incentives.transactions') }}';

            document.querySelectorAll('.js-repeat-transactions').forEach(function (button) {
                button.addEventListener('click', function () {
                    var clientId = button.dataset.clientId;
                    var center = button.dataset.center;
                    var member = button.dataset.member;
                    var month = button.dataset.month;
                    var monthName = button.dataset.monthName;

                    title.textContent = member + ' — ' + monthName + ' {{ $year }}';
                    state.textContent = 'Loading transactions…';
                    state.classList.remove('d-none');
                    tableWrap.classList.add('d-none');
                    body.innerHTML = '';
                    modal.show();
                
                    var params = new URLSearchParams({ client_id: clientId, center: center, month: month, year: '{{ $year }}' });

                    fetch(endpoint + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(function (response) {
                            if (!response.ok) throw new Error('Unable to load transactions.');
                            return response.json();
                        })
                        .then(function (data) {
                            if (!data.transactions || !data.transactions.length) {
                                state.textContent = 'No transactions were found for this member and month.';
                                return;
                            }

                            data.transactions.forEach(function (transaction) {
                                var row = document.createElement('tr');
                                [transaction.date, transaction.item, transaction.reference, transaction.quantity].forEach(function (value, index) {
                                    var cell = document.createElement('td');
                                    cell.textContent = value;
                                    if (index === 0) cell.className = 'ps-3 text-nowrap';
                                    if (index === 3) cell.className = 'pe-3 text-end fw-semibold';
                                    row.appendChild(cell);
                                });
                                body.appendChild(row);
                            });

                            state.classList.add('d-none');
                            tableWrap.classList.remove('d-none');
                        })
                        .catch(function () {
                            state.textContent = 'Unable to load transactions. Please try again.';
                        });
                });
            });
        });
    </script>
@endsection
