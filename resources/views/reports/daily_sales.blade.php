@extends('layouts.header')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1 text-dark">
                    Daily Sales Report
                </h2>

                <p class="text-muted mb-0">
                    Sales Transaction Monitoring Dashboard
                </p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ route('reports.daily.export', ['from' => $from, 'to' => $to ]) }}"
                class="btn btn-success shadow-sm">
                    <i class="ti ti-file-export me-1"></i>
                    Export Excel
                </a>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">
                                From Date
                            </label>
                            <input type="date" name="from" class="form-control form-control-lg" value="{{ $from }}">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">
                                To Date
                            </label>
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

        {{-- SUMMARY CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-success-subtle text-success">
                                <i class="bi bi-cash-stack fs-6"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block">
                                    Total Sales
                                </small>
                                <h3 class="fw-bold mb-0 text-success">
                                    ₱{{ number_format($grandTotal, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-primary-subtle text-primary">
                                <i class="ti ti-receipt-2 fs-6"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block">
                                    Total Transactions
                                </small>
                                <h3 class="fw-bold mb-0">
                                    {{ $reports->flatten()->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-warning-subtle text-warning">
                                <i class="ti ti-users fs-6"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block">
                                    Dealer Transaction
                                </small>
                                <h3 class="fw-bold mb-0">
                                    {{ $reports->flatten()->pluck('dealer_id')->unique()->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-info-subtle text-info">
                                <i class="ti ti-package fs-6"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block">
                                    Products Sold
                                </small>
                                <h3 class="fw-bold mb-0 text-info">
                                    {{ number_format($reports->flatten()->sum('qty')) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">
                            Sales Transaction Table
                        </h5>
                        <small class="text-muted">
                            Detailed sales monitoring and remittance summary
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                    <div class="row p-3 border-bottom justify-content-end align-items-center g-2">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <input type="text" id="tableSearch" class="form-control" placeholder="Search dealer, order # ...">
                    </div>
                    <div class="col-lg-2">
                        <select id="paymentFilter" class="form-select">
                            <option value="">All Payment Methods</option>
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
                                @foreach($items as $item)
                                    <th colspan="2" class="text-center">
                                        {{ $item->sku }}
                                        <br>
                                        <small class="fw-normal">
                                            {{ $item->product_name }}
                                        </small>
                                    </th>
                                @endforeach
                                <th rowspan="2" class="text-center" style="vertical-align: middle">Delivery Fee</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle">Other Charges</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle">Total Amount</th>
                                <th colspan="4" class="text-center">
                                    Payment Methods
                                </th>
                            </tr>
                            <tr>
                                @foreach($items as $item)
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
                            @forelse($reports as $date => $transactions)
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

                                    foreach($items as $item){
                                        $productSubtotals[$item->product_name] = [
                                            'qty' => 0,
                                            'amount' => 0
                                        ];
                                    }
                                @endphp

                                {{-- DATE GROUP --}}
                                <tr class="date-group-row">
                                    <td colspan="{{ (count($items) * 2) + 10 }}">
                                        <i class="ti ti-calendar-event me-2"></i>
                                        {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                    </td>
                                </tr>

                                @foreach($transactions as $r)
                                    @php
                                        $lineSubtotal = $r->qty * $r->price;
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

                                        $dailySubtotal += $lineTotal;
                                        $dailyDeliveryFeeTotal += $deliveryFee;
                                        $dailyOtherChargeTotal += $otherChargeTotal;

                                        if(isset($productSubtotals[$r->item])){
                                            $productSubtotals[$r->item]['qty'] += $r->qty;
                                            $productSubtotals[$r->item]['amount'] += $lineSubtotal;

                                        }

                                        if(isset($paymentTotals[$r->payment_method])){
                                            $paymentTotals[$r->payment_method] += $lineTotal;
                                        }
                                    @endphp

                                   <tr class="data-row" data-payment="{{ strtolower($r->payment_method) }}">
                                        <td>
                                            {{ \Carbon\Carbon::parse($r->date)->format('M d, Y') }}
                                        </td>
                                        <td class="fw-semibold">
                                            {{ optional($r->dealer)->name ?? '-' }}
                                        </td>
                                        <td>
                                            {{ $r->transaction_id }}
                                        </td>
                                        @foreach($items as $item)
                                            <td class="text-center">
                                                {{ $r->item == $item->product_name ? number_format($r->qty) : 0 }}
                                            </td>
                                            <td class="text-end">
                                                {{ $r->item == $item->product_name
                                                    ? '₱'.number_format($lineSubtotal,2)
                                                    : '₱0.00' }}
                                            </td>
                                        @endforeach
                                        <td class="text-end">
                                            {{ $deliveryFee > 0 ? '₱'.number_format($deliveryFee, 2) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $otherChargeTotal > 0 ? '₱'.number_format($otherChargeTotal, 2) : '-' }}
                                        </td>
                                        <td class="fw-bold text-success text-end">
                                            ₱{{ number_format($lineTotal, 2) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $r->payment_method == 'cash'
                                                ? '₱'.number_format($lineTotal,2)
                                                : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $r->payment_method == 'gcash'
                                                ? '₱'.number_format($lineTotal,2)
                                                : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $r->payment_method == 'bank_transfer'
                                                ? '₱'.number_format($lineTotal,2)
                                                : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $r->payment_method == 'credit'
                                                ? '₱'.number_format($lineTotal,2)
                                                : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                                {{-- SUBTOTAL --}}
                                <tr class="subtotal-row">
                                    <td colspan="3" class="text-end fw-bold">
                                        DAILY SUBTOTAL
                                    </td>
                                    @foreach($items as $item)
                                        <td class="text-center fw-bold text-primary">
                                            {{ $productSubtotals[$item->product_name]['qty'] }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            ₱{{ number_format($productSubtotals[$item->product_name]['amount'], 2) }}
                                        </td>
                                    @endforeach
                                    <td class="fw-bold text-end text-success">
                                        ₱{{ number_format($dailyDeliveryFeeTotal, 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-success">
                                        ₱{{ number_format($dailyOtherChargeTotal, 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-success">
                                        ₱{{ number_format($dailySubtotal, 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-primary">
                                        ₱{{ number_format($paymentTotals['cash'], 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-primary">
                                        ₱{{ number_format($paymentTotals['gcash'], 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-primary">
                                        ₱{{ number_format($paymentTotals['bank_transfer'], 2) }}
                                    </td>
                                    <td class="fw-bold text-end text-primary">
                                        ₱{{ number_format($paymentTotals['credit'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ (count($items) * 2) + 10 }}"
                                        class="text-center py-5 text-muted">
                                        <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                                        No report found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .stats-card {
        transition:0.3s;
    }

    .stats-card:hover {
        transform:translateY(-4px);
    }

    .stats-icon {
        width:60px;
        height:60px;
        border-radius:16px;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .custom-table-wrapper {
        max-height:750px;
        overflow:auto;
    }

    .table-header th {
        position:sticky;
        top:0;
        z-index:10;
        background: #5BC2E7 !important;
        color:#fff !important;
        vertical-align:middle;
        white-space:nowrap;
        font-size:13px;
    }

    .table td {
        white-space:nowrap;
        font-size:13px;
        vertical-align:middle;
    }

    .date-group-row td {
        background:#dff4ff !important;
        color:#055160;
        font-weight:700;
        font-size:14px;
    }

    .subtotal-row td {
        background:#f8f9fa !important;
        border-top:2px solid #dee2e6;
    }

    .table tbody tr:hover {
        background:#f9fcff;
    }
    #tableSearch,
    #paymentFilter{
        height:45px;
        border-radius:12px;
        border:1px solid #dbe3ea;
        box-shadow:none;
    }

    #tableSearch:focus,
    #paymentFilter:focus{
        border-color:#0d6efd;
        box-shadow:0 0 0 0.15rem rgba(13,110,253,.15);
    }
</style>

@section('javascript')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('tableSearch');
        const paymentFilter = document.getElementById('paymentFilter');
        function filterTable() {
            const keyword = searchInput.value.toLowerCase();
            const payment = paymentFilter
                ? paymentFilter.value.toLowerCase()
                : '';
            const rows = document.querySelectorAll('.data-row');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const rowPayment = row.dataset.payment;
                const matchKeyword = text.includes(keyword);
                const matchPayment =
                    payment === '' || rowPayment === payment;
                row.style.display =
                    (matchKeyword && matchPayment)
                        ? ''
                        : 'none';
            });
        }
        // SEARCH
        if(searchInput){
            searchInput.addEventListener('keyup', filterTable);
        }
        // PAYMENT FILTER
        if(paymentFilter){
            paymentFilter.addEventListener('change', filterTable);
        }
    });

</script>
@endsection
