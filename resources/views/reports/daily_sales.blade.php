@extends('layouts.header')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1 text-dark">Daily Sales Report</h2>
                <p class="text-muted mb-0">Sales Transaction Monitoring Dashboard</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ route('reports.daily.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-success shadow-sm">
                    <i class="ti ti-file-export me-1"></i>
                    Export Excel
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from" class="form-control form-control-lg" value="{{ $from }}">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to" class="form-control form-control-lg" value="{{ $to }}">
                        </div>
                        <div class="col-lg-4">
                            <button class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="ti ti-filter me-1"></i>
                                Filter Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
            $tabs = collect($reportTabs ?? [[
                'key' => 'regular',
                'label' => 'Regular',
                'reports' => $reports,
                'items' => $items,
                'grand_total' => $grandTotal,
                'transaction_count' => $reports->flatten(1)->count(),
                'dealer_count' => $reports->flatten(1)->pluck('dealer_id')->filter()->unique()->count(),
                'products_sold' => $reports->flatten(1)->sum('qty'),
            ]]);
        @endphp

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden report-workspace">
            <div class="card-header bg-white border-0 p-0">
                <div class="report-workspace-head">
                    <div>
                        <h5 class="fw-bold mb-1">Sales Transaction Table</h5>
                        <small class="text-muted">Switch source tabs to review regular and project sales in one report.</small>
                    </div>
                    <ul class="nav nav-pills report-tabs" id="dailySalesTabs" role="tablist">
                        @foreach($tabs as $tab)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="tab-{{ $tab['key'] }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-{{ $tab['key'] }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="pane-{{ $tab['key'] }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <span>{{ $tab['label'] }}</span>
                                    <strong>{{ number_format($tab['transaction_count']) }}</strong>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="dailySalesTabsContent">
                    @foreach($tabs as $tab)
                        @php
                            $tabReports = collect($tab['reports']);
                            $tabItems = collect($tab['items']);
                            $columnCount = ($tabItems->count() * 2) + 10;
                        @endphp

                        <div
                            class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="pane-{{ $tab['key'] }}"
                            role="tabpanel"
                            aria-labelledby="tab-{{ $tab['key'] }}">

                            <div class="report-summary-grid">
                                <div class="report-summary-item">
                                    <span class="report-summary-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></span>
                                    <div>
                                        <small>Total Sales</small>
                                        <strong class="text-success">&#8369;{{ number_format($tab['grand_total'], 2) }}</strong>
                                    </div>
                                </div>
                                <div class="report-summary-item">
                                    <span class="report-summary-icon bg-primary-subtle text-primary"><i class="ti ti-receipt-2"></i></span>
                                    <div>
                                        <small>Total Transactions</small>
                                        <strong>{{ number_format($tab['transaction_count']) }}</strong>
                                    </div>
                                </div>
                                <div class="report-summary-item">
                                    <span class="report-summary-icon bg-warning-subtle text-warning"><i class="ti ti-users"></i></span>
                                    <div>
                                        <small>Dealer Transaction</small>
                                        <strong>{{ number_format($tab['dealer_count']) }}</strong>
                                    </div>
                                </div>
                                <div class="report-summary-item">
                                    <span class="report-summary-icon bg-info-subtle text-info"><i class="ti ti-package"></i></span>
                                    <div>
                                        <small>Products Sold</small>
                                        <strong class="text-info">{{ number_format($tab['products_sold']) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="report-table-toolbar">
                                <div>
                                    <div class="fw-bold">{{ $tab['label'] }} Sales</div>
                                    <div class="small text-muted">Detailed sales monitoring and remittance summary</div>
                                </div>
                                <div class="report-table-filters">
                                    <input type="text" class="form-control table-search" placeholder="Search dealer, order # ...">
                                    <select class="form-select payment-filter">
                                        <option value="">All Payments</option>
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive custom-table-wrapper">
                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-header">
                                        <tr>
                                            <th rowspan="2">Date</th>
                                            <th rowspan="2">Dealer</th>
                                            <th rowspan="2">Order #</th>
                                            @foreach($tabItems as $item)
                                                <th colspan="2" class="text-center">
                                                    {{ $item->sku ?: 'SKU' }}
                                                    <br>
                                                    <small class="fw-normal">{{ $item->product_name }}</small>
                                                </th>
                                            @endforeach
                                            <th rowspan="2" class="text-center" style="vertical-align: middle">Delivery Fee</th>
                                            <th rowspan="2" class="text-center" style="vertical-align: middle">Other Charges</th>
                                            <th rowspan="2" class="text-center" style="vertical-align: middle">Total Amount</th>
                                            <th colspan="4" class="text-center">Payment Methods</th>
                                        </tr>
                                        <tr>
                                            @foreach($tabItems as $item)
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Amount</th>
                                            @endforeach
                                            <th>CASH</th>
                                            <th>GCASH</th>
                                            <th>BANK</th>
                                            <th>CREDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tabReports as $date => $transactions)
                                            @php
                                                $dailySubtotal = 0;
                                                $dailyDeliveryFeeTotal = 0;
                                                $dailyOtherChargeTotal = 0;
                                                $paymentTotals = [
                                                    'cash' => 0,
                                                    'gcash' => 0,
                                                    'bank_transfer' => 0,
                                                    'credit' => 0,
                                                ];
                                                $productSubtotals = [];

                                                foreach($tabItems as $item) {
                                                    $productSubtotals[$item->product_name] = [
                                                        'qty' => 0,
                                                        'amount' => 0,
                                                    ];
                                                }
                                            @endphp

                                            <tr class="date-group-row">
                                                <td colspan="{{ $columnCount }}">
                                                    <i class="ti ti-calendar-event me-2"></i>
                                                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                                </td>
                                            </tr>

                                            @foreach($transactions as $r)
                                                @php
                                                    $lineSubtotal = (float) $r->qty * (float) $r->price;
                                                    $deliveryFee = (float) ($r->delivery_fee ?? 0);
                                                    $otherChargeTotal = $otherCharges->sum(function ($charge) use ($lineSubtotal, $r) {
                                                        if ($charge->applies_to === 'delivery' && $r->delivery_type !== 'delivery') {
                                                            return 0;
                                                        }

                                                        return $charge->charge_type === 'percentage'
                                                            ? $lineSubtotal * ((float) $charge->amount / 100)
                                                            : (float) $charge->amount;
                                                    });
                                                    $lineTotal = $lineSubtotal + $deliveryFee + $otherChargeTotal;
                                                    $paymentMethod = strtolower((string) ($r->payment_method ?? ''));

                                                    $dailySubtotal += $lineTotal;
                                                    $dailyDeliveryFeeTotal += $deliveryFee;
                                                    $dailyOtherChargeTotal += $otherChargeTotal;

                                                    if(isset($productSubtotals[$r->item])) {
                                                        $productSubtotals[$r->item]['qty'] += $r->qty;
                                                        $productSubtotals[$r->item]['amount'] += $lineSubtotal;
                                                    }

                                                    if(isset($paymentTotals[$paymentMethod])) {
                                                        $paymentTotals[$paymentMethod] += $lineTotal;
                                                    }
                                                @endphp

                                                <tr class="data-row" data-payment="{{ $paymentMethod }}">
                                                    <td>{{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}</td>
                                                    <td class="fw-semibold">{{ optional($r->dealer)->name ?? '-' }}</td>
                                                    <td>{{ $r->transaction_id }}</td>
                                                    @foreach($tabItems as $item)
                                                        <td class="text-center">{{ $r->item == $item->product_name ? number_format($r->qty) : 0 }}</td>
                                                        <td class="text-end">
                                                            @if($r->item == $item->product_name)
                                                                &#8369;{{ number_format($lineSubtotal, 2) }}
                                                            @else
                                                                &#8369;0.00
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-end">{!! $deliveryFee > 0 ? '&#8369;'.number_format($deliveryFee, 2) : '-' !!}</td>
                                                    <td class="text-end">{!! $otherChargeTotal > 0 ? '&#8369;'.number_format($otherChargeTotal, 2) : '-' !!}</td>
                                                    <td class="fw-bold text-success text-end">&#8369;{{ number_format($lineTotal, 2) }}</td>
                                                    <td class="text-end">{!! $paymentMethod == 'cash' ? '&#8369;'.number_format($lineTotal, 2) : '-' !!}</td>
                                                    <td class="text-end">{!! $paymentMethod == 'gcash' ? '&#8369;'.number_format($lineTotal, 2) : '-' !!}</td>
                                                    <td class="text-end">{!! $paymentMethod == 'bank_transfer' ? '&#8369;'.number_format($lineTotal, 2) : '-' !!}</td>
                                                    <td class="text-end">{!! $paymentMethod == 'credit' ? '&#8369;'.number_format($lineTotal, 2) : '-' !!}</td>
                                                </tr>
                                            @endforeach

                                            <tr class="subtotal-row">
                                                <td colspan="3" class="text-end fw-bold">DAILY SUBTOTAL</td>
                                                @foreach($tabItems as $item)
                                                    <td class="text-center fw-bold text-primary">{{ $productSubtotals[$item->product_name]['qty'] }}</td>
                                                    <td class="text-end fw-bold text-success">&#8369;{{ number_format($productSubtotals[$item->product_name]['amount'], 2) }}</td>
                                                @endforeach
                                                <td class="fw-bold text-end text-success">&#8369;{{ number_format($dailyDeliveryFeeTotal, 2) }}</td>
                                                <td class="fw-bold text-end text-success">&#8369;{{ number_format($dailyOtherChargeTotal, 2) }}</td>
                                                <td class="fw-bold text-end text-success">&#8369;{{ number_format($dailySubtotal, 2) }}</td>
                                                <td class="fw-bold text-end text-primary">&#8369;{{ number_format($paymentTotals['cash'], 2) }}</td>
                                                <td class="fw-bold text-end text-primary">&#8369;{{ number_format($paymentTotals['gcash'], 2) }}</td>
                                                <td class="fw-bold text-end text-primary">&#8369;{{ number_format($paymentTotals['bank_transfer'], 2) }}</td>
                                                <td class="fw-bold text-end text-primary">&#8369;{{ number_format($paymentTotals['credit'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $columnCount }}" class="text-center py-5 text-muted">
                                                    <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                                                    No report found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .report-workspace {
        border: 1px solid #edf1f7;
    }

    .report-workspace-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #edf1f7;
        background: #fff;
    }

    .report-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .report-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #dbe3ea;
        border-radius: 8px;
        color: #334155;
        background: #f8fafc;
        min-height: 40px;
        padding: 8px 12px;
        font-weight: 800;
        transition: background .16s ease, border-color .16s ease, color .16s ease;
    }

    .report-tabs .nav-link strong {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        min-height: 24px;
        padding: 2px 8px;
        border-radius: 999px;
        color: #475569;
        background: #fff;
        border: 1px solid #e5e7eb;
        font-size: 12px;
    }

    .report-tabs .nav-link.active {
        color: #fff;
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 8px 18px rgba(13, 110, 253, .18);
    }

    .report-tabs .nav-link.active strong {
        color: #0d6efd;
        border-color: rgba(255, 255, 255, .5);
    }

    .report-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        border-bottom: 1px solid #edf1f7;
        background: #fbfdff;
    }

    .report-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 92px;
        padding: 16px 18px;
        border-right: 1px solid #edf1f7;
    }

    .report-summary-item:last-child {
        border-right: 0;
    }

    .report-summary-item small {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .report-summary-item strong {
        display: block;
        color: #111827;
        font-size: 21px;
        line-height: 1.15;
        white-space: nowrap;
    }

    .report-summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        font-size: 18px;
    }

    .report-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        min-height: 74px;
        padding: 14px 18px;
        border-bottom: 1px solid #edf1f7;
        background: #fff;
    }

    .report-table-filters {
        display: grid;
        grid-template-columns: minmax(220px, 320px) 180px;
        gap: 10px;
        align-items: center;
    }

    .custom-table-wrapper {
        max-height: 750px;
        overflow: auto;
    }

    .table-header th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #2563eb !important;
        color: #fff !important;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 13px;
    }

    .table td {
        white-space: nowrap;
        font-size: 13px;
        vertical-align: middle;
    }

    .date-group-row td {
        background: #dff4ff !important;
        color: #055160;
        font-weight: 700;
        font-size: 14px;
    }

    .subtotal-row td {
        background: #f8f9fa !important;
        border-top: 2px solid #dee2e6;
    }

    .table tbody tr:hover {
        background: #f9fcff;
    }

    .table-search,
    .payment-filter {
        height: 45px;
        border-radius: 12px;
        border: 1px solid #dbe3ea;
        box-shadow: none;
    }

    .table-search:focus,
    .payment-filter:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, .15);
    }

    @media (max-width: 1199px) {
        .report-workspace-head,
        .report-table-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .report-tabs {
            justify-content: flex-start;
        }

        .report-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-summary-item:nth-child(2) {
            border-right: 0;
        }

        .report-summary-item:nth-child(-n+2) {
            border-bottom: 1px solid #edf1f7;
        }
    }

    @media (max-width: 575px) {
        .report-workspace-head {
            padding: 14px;
        }

        .report-tabs .nav-link {
            width: 100%;
            justify-content: space-between;
        }

        .report-tabs,
        .report-tabs .nav-item {
            width: 100%;
        }

        .report-summary-grid,
        .report-table-filters {
            grid-template-columns: 1fr;
        }

        .report-summary-item {
            border-right: 0;
            border-bottom: 1px solid #edf1f7;
        }

        .report-summary-item:last-child {
            border-bottom: 0;
        }
    }
</style>

@section('javascript')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tab-pane').forEach(function (pane) {
            const searchInput = pane.querySelector('.table-search');
            const paymentFilter = pane.querySelector('.payment-filter');

            function filterTable() {
                const keyword = searchInput ? searchInput.value.toLowerCase() : '';
                const payment = paymentFilter ? paymentFilter.value.toLowerCase() : '';
                const rows = pane.querySelectorAll('.data-row');

                rows.forEach(function (row) {
                    const text = row.innerText.toLowerCase();
                    const rowPayment = row.dataset.payment || '';
                    const matchKeyword = text.includes(keyword);
                    const matchPayment = payment === '' || rowPayment === payment;

                    row.style.display = matchKeyword && matchPayment ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('keyup', filterTable);
            }

            if (paymentFilter) {
                paymentFilter.addEventListener('change', filterTable);
            }
        });
    });
</script>
@endsection
