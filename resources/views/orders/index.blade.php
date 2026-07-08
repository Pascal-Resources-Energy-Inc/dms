@extends('layouts.header')
<style>
    .content-area:has(.welcome-client) {
        margin-top: 90px !important;
    }
    
    .transaction-table th {
        text-align: center;
    }
    .btn-view {
        width: 100px;
        font-size: 14px;
    }
    .dashboard-stats {
        display: flex;
        justify-content: space-around;
    }
    .dashboard-stats div {
        text-align: center;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 30%;
    }
    .welcome {
        margin-top: auto !important;
    }
    .card-header {
        font-size: 1.25rem;
        font-weight: bold;
    }
    .card-body {
        padding: 20px;
    }
    .filter-container {
        margin-bottom: 20px;
    }
    .card {
      border-radius: 15px !important;
    }
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 1rem;
            max-width: 100%;
        }

        .chosen-container {
            width: 100% !important;
        }

        .chosen-drop {
            max-height: 200px;
            overflow-y: auto;
        }

    }

    .search-name-responsive{
        width: 180px !important;
    }

    @media (max-width: 576px) {
        .search-name-responsive{
            font-size: 12px !important;
            width: 170px !important;
            height: 43px !important; 
        }
    }

.chosen-container .chosen-single {
  height: calc(2.25rem + 2px);
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  box-shadow: none;
}

.chosen-container-active.chosen-with-drop .chosen-single {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.chosen-container .chosen-drop {
  border: 1px solid #ced4da;
  border-top: none;
  border-radius: 0 0 0.25rem 0.25rem;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
}

.chosen-container .chosen-results {
  max-height: 200px;
  overflow-y: auto;
}

.chosen-container .chosen-search input {
  height: calc(1.5em + 0.75rem + 2px);
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
}

.dataTables_length {
  float: left;
  margin-top: 15px;
  margin-bottom: 5px;
}

.dataTables_filter {
  float: right;
  margin-top: 15px;
  margin-bottom: 5px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:focus {
    box-shadow: none;
    outline: none;
}

.export-btn-custom {
    width: 130px;
    height: 38px;
    font-size: 14px;
    padding: 6px 12px;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 0 !important;
}

table.dataTable {
    margin-top: 5px !important;
}

.card-body > .dataTables_wrapper {
    margin-bottom: 0 !important;
}

.dataTables_wrapper .row:first-child {
    margin-bottom: 0 !important;
}

.order-summary-grid {
    row-gap: 12px;
}

.order-summary-card {
    position: relative;
    min-height: 116px;
    border: 1px solid #e6e9ef;
    border-radius: 8px !important;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .055);
    overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease;
}

.order-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .09);
}

.order-summary-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 4px;
    background: var(--summary-color);
}

.order-summary-card .card-body {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 116px;
    padding: 18px !important;
}

.order-summary-card.is-sales {
    --summary-color: #0f766e;
    --summary-soft: #ccfbf1;
}

.order-summary-card.is-orders {
    --summary-color: #1d4ed8;
    --summary-soft: #dbeafe;
}

.order-summary-card.is-qty {
    --summary-color: #b45309;
    --summary-soft: #fef3c7;
}

.order-summary-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 46px;
    height: 46px;
    color: var(--summary-color);
    background: var(--summary-soft);
    border-radius: 8px;
    font-size: 23px;
}

.order-summary-label {
    display: block;
    color: #667085;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    line-height: 1.2;
    text-transform: uppercase;
}

.order-summary-value {
    display: block;
    margin-top: 6px;
    color: #101828;
    font-size: clamp(19px, 2vw, 24px);
    font-weight: 900;
    line-height: 1.1;
    overflow-wrap: anywhere;
}

.order-summary-note {
    display: block;
    margin-top: 6px;
    color: #98a2b3;
    font-size: 11px;
    font-weight: 700;
}

.order-stock-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.25rem 0.55rem;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.order-stock-pill.is-ok {
    color: #146c43;
    background: #d1e7dd;
}

.order-stock-pill.is-low {
    color: #8a5a00;
    background: #fff3cd;
}

.order-stock-pill.is-out {
    color: #b02a37;
    background: #f8d7da;
}

.order-inventory-card {
    margin-top: 8px;
    border: 1px solid #e4e9f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    min-width: 260px;
}

.order-inventory-card.is-out {
    border-color: #f0a5a5;
    background: #fff7f7;
}

.order-inventory-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(62px, 1fr));
}

.order-inventory-grid > div {
    padding: 8px 10px;
    border-right: 1px solid #edf1f5;
}

.order-inventory-grid > div:last-child {
    border-right: 0;
}

.order-inventory-label {
    display: block;
    color: #6c757d;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.order-inventory-value {
    display: block;
    color: #212529;
    font-size: 13px;
    font-weight: 700;
    margin-top: 4px;
    white-space: nowrap;
}

.order-inventory-value.is-good {
    color: #146c43;
}

.order-inventory-value.is-out {
    color: #b02a37;
}

.order-source-shell {
    border: 1px solid #e6eaf0;
    border-radius: 8px;
    background: #f8fafc;
    padding: 12px;
}

.order-source-tabs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 10px;
    border-bottom: 0;
}

.order-source-tabs .nav-link {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid #dfe5ec;
    border-radius: 8px;
    color: #344054;
    background: #fff;
    padding: 12px 14px;
    text-align: left;
}

.order-source-tabs .nav-link.active {
    border-color: #1f7a4d;
    color: #14532d;
    background: #edf8f1;
    box-shadow: 0 8px 18px rgba(20, 83, 45, 0.08);
}

.order-source-tab-main {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.order-source-tab-icon {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #eef2f7;
    color: #475467;
    flex: 0 0 auto;
}

.order-source-tabs .nav-link.active .order-source-tab-icon {
    background: #d1e7dd;
    color: #146c43;
}

.order-source-tab-title {
    display: block;
    font-weight: 800;
    line-height: 1.1;
}

.order-source-tab-subtitle {
    display: block;
    color: #667085;
    font-size: 11px;
    font-weight: 700;
    margin-top: 3px;
    text-transform: uppercase;
}

.order-source-count {
    border-radius: 999px;
    background: #eef2f7;
    color: #344054;
    font-weight: 800;
    padding: 5px 9px;
    flex: 0 0 auto;
}

.order-source-tabs .nav-link.active .order-source-count {
    background: #146c43;
    color: #fff;
}

.order-tab-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
    margin: 14px 0;
}

.order-tab-metric {
    border: 1px solid #e6eaf0;
    border-radius: 8px;
    background: #fff;
    padding: 12px 14px;
}

.order-tab-metric small {
    display: block;
    color: #667085;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.order-tab-metric strong {
    display: block;
    color: #1d2939;
    font-size: 18px;
    margin-top: 4px;
}

.remote-order-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid #d0d5dd;
    border-radius: 999px;
    background: #f9fafb;
    color: #475467;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

@media (max-width: 576px) {
    .order-source-shell {
        padding: 8px;
    }

    .order-source-tabs {
        grid-template-columns: 1fr;
    }

    .order-summary-card .card-body {
        min-height: 98px;
    }
}


</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
<div class="welcome @if(auth()->user()->role === 'Dealer') welcome-client @endif">
    <div class="row">
        <div class="col-12">
            <div class="row mb-0 order-summary-grid">
                <div class="col-sm-6 col-xl-4">
                    <div class="card order-summary-card is-sales w-100">
                        <div class="card-body">
                            <span class="order-summary-icon">
                                <i class="ti ti-currency-peso"></i>
                            </span>
                            <div>
                                <span class="order-summary-label">Total Sales</span>
                                <strong class="order-summary-value">
                                    PHP {{ number_format($orders->sum(function($transaction) {
                                        return ($transaction->price * $transaction->qty) + ($transaction->delivery_fee ?? 0);
                                    }), 2) }}
                                </strong>
                                <span class="order-summary-note">Includes delivery fees</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card order-summary-card is-orders w-100">
                        <div class="card-body">
                            <span class="order-summary-icon">
                                <i class="ti ti-shopping-cart"></i>
                            </span>
                            <div>
                                <span class="order-summary-label">Orders</span>
                                <strong class="order-summary-value">
                                    {{ number_format($orders->count()) }}
                                </strong>
                                <span class="order-summary-note">Total order records</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card order-summary-card is-qty w-100">
                        <div class="card-body">
                            <span class="order-summary-icon">
                                <i class="ti ti-packages"></i>
                            </span>
                            <div>
                                <span class="order-summary-label">Qty Sold</span>
                                <strong class="order-summary-value">
                                    {{ number_format($orders->sum('qty'), 2) }}
                                </strong>
                                <span class="order-summary-note">Total products ordered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card w-100">  
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-0">Orders</h5>
                            {{-- <small class="text-muted">Create and manage dealer purchase orders.</small> --}}
                        </div>
                        <a href="{{ route('orders.export') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
                        </a>
                        {{-- <a href="{{ route('orders.purchase-order') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> New Purchase Order
                        </a> --}}
                    </div>
                    @php
                        $orderTabs = collect($orderTabs ?? [[
                            'key' => 'regular',
                            'label' => 'Regular',
                            'database' => 'dms_prei',
                            'icon' => 'bi bi-building',
                            'orders' => $orders ?? collect(),
                        ]]);
                    @endphp

                    <div class="order-source-shell mb-3">
                        <ul class="nav order-source-tabs" id="orderSourceTabs" role="tablist">
                            @foreach($orderTabs as $tabIndex => $orderTab)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $tabIndex === 0 ? 'active' : '' }}"
                                        id="orders-{{ $orderTab['key'] }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#orders-{{ $orderTab['key'] }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="orders-{{ $orderTab['key'] }}"
                                        aria-selected="{{ $tabIndex === 0 ? 'true' : 'false' }}">
                                        <span class="order-source-tab-main">
                                            <span class="order-source-tab-icon"><i class="{{ $orderTab['icon'] ?? 'bi bi-database' }}"></i></span>
                                            <span>
                                                <span class="order-source-tab-title">{{ $orderTab['label'] }}</span>
                                                {{-- <span class="order-source-tab-subtitle">{{ $orderTab['database'] ?? '-' }}</span> --}}
                                            </span>
                                        </span>
                                        <span class="order-source-count">{{ collect($orderTab['orders'])->count() }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="tab-content" id="orderSourceTabContent">
                        @foreach($orderTabs as $tabIndex => $orderTab)
                            @php
                                $tabOrders = collect($orderTab['orders']);
                                $tabSales = $tabOrders->sum(function ($order) {
                                    return ((float) $order->price * (float) $order->qty) + (float) ($order->delivery_fee ?? 0);
                                });
                                $tabPending = $tabOrders->filter(function ($order) {
                                    return strcasecmp((string) $order->status, 'Pending') === 0;
                                })->count();
                            @endphp
                            <div class="tab-pane fade {{ $tabIndex === 0 ? 'show active' : '' }}"
                                id="orders-{{ $orderTab['key'] }}"
                                role="tabpanel"
                                aria-labelledby="orders-{{ $orderTab['key'] }}-tab">
                                <div class="order-tab-summary">
                                    <div class="order-tab-metric">
                                        <small>Total Sales</small>
                                        <strong>{{ number_format($tabSales, 2) }}</strong>
                                    </div>
                                    <div class="order-tab-metric">
                                        <small>Orders</small>
                                        <strong>{{ number_format($tabOrders->count()) }}</strong>
                                    </div>
                                    <div class="order-tab-metric">
                                        <small>Quantity</small>
                                        <strong>{{ number_format($tabOrders->sum('qty'), 2) }}</strong>
                                    </div>
                                    <div class="order-tab-metric">
                                        <small>Pending</small>
                                        <strong>{{ number_format($tabPending) }}</strong>
                                    </div>
                                </div>
                                <div class="table-responsive">
                        <table class="table table-bordered table-striped transaction-table orders-data-table" id="ordersTable-{{ $orderTab['key'] }}" style="width:100%">
                            <thead>
                                <tr>
                                    @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                        <th scope="col" style="width: 50px; text-align: center;">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <input type="checkbox" class="select-all" title="Select All" style="cursor: pointer;">
                                            </div>
                                        </th>
                                    @endif
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Awarded Area</th>
                                    <th scope="col">Points</th>
                                    <th scope="col">Item</th>
                                    <th scope="col">Payment Method</th>
                                    <th scope="col">Delivery Type</th>
                                    <th scope="col">Delivery Fee</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                    @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                        <th scope="col" style="width: 80px; text-align: center;">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="transactionBody-{{ $orderTab['key'] }}">
                                @foreach($tabOrders as $order)
                                    <tr id="transaction-row-{{ $orderTab['key'] }}-{{$order->id}}">
                                        @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                            <td style="text-align: center;">
                                                @if(empty($order->is_remote))
                                                    <input type="checkbox" class="checkbox-item" data-id="{{$order->id}}" style="cursor: pointer;">
                                                @else
                                                    <input type="checkbox" disabled title="Remote CRM order" style="cursor: not-allowed;">
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $order->transaction_id }}</td>
                                        <td>{{ strtoupper(date('M d, Y', strtotime($order->date))) }}</td>
                                        <td class="qty-cell">{{ number_format($order->qty, 2) }}</td>
                                        <td class="amount-cell">{{ number_format(($order->qty * $order->price) + ($order->delivery_fee ?? 0), 2) }}</td>
                                        <td>
                                            @if(($order->is_guest ?? false) || $order->guest_name)
                                                <div class="fw-semibold">{{ strtoupper($order->guest_name ?: 'GUEST CUSTOMER') }}</div>
                                                <small class="text-muted d-block">{{ $order->guest_phone }}</small>
                                                @if($order->guest_email)
                                                    <small class="text-muted d-block">{{ $order->guest_email }}</small>
                                                @endif
                                            @else
                                                {{ strtoupper($order->dealer->name ?? '') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(($order->is_guest ?? false) || $order->guest_name)
                                                <span class="badge rounded-pill bg-light text-dark border">{{ strtoupper($order->guest_authorized_territory ?: 'GUEST') }}</span>
                                            @else
                                                {{ strtoupper($order->adDealer->area ?? '') }}
                                            @endif
                                        </td>
                                        <td class="dealer-points-cell"><span class='text-success'>{{ $order->points_dealer }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ strtoupper($order->item) }}</div>
                                            @if(auth()->user()->role !== 'Admin')
                                                @php
                                                    $rowProduct = null;

                                                    if(isset($order->product_id) && $order->product_id) {
                                                        $rowProduct = $products->firstWhere('id', (int) $order->product_id);
                                                    }

                                                    if(!$rowProduct) {
                                                        $rowProduct = $products->first(function($product) use ($order) {
                                                            return strtolower(trim($product->product_name)) === strtolower(trim($order->item));
                                                        });
                                                    }

                                                    $rowArea = $order->dealer->area ?? '';
                                                    $rowInventoryStats = ($rowProduct && $rowArea)
                                                        ? collect($inventoryStatsByAreaProduct->get($rowArea, []))->get($rowProduct->id)
                                                        : null;
                                                    $rowHasInventoryContext = $rowProduct && $rowArea;
                                                    $rowStock = $rowHasInventoryContext
                                                        ? (float) ($rowInventoryStats['available'] ?? 0)
                                                        : null;
                                                    $rowStockAfterMovement = $rowHasInventoryContext
                                                        ? (float) ($rowInventoryStats['stock_after_movement'] ?? 0)
                                                        : 0;
                                                    $rowSalesOrders = $rowHasInventoryContext
                                                        ? (float) ($rowInventoryStats['sales_orders'] ?? 0)
                                                        : 0;
                                                    $rowInventoryStatus = $rowStock !== null && $rowStock <= 0 ? 'No stock' : 'Good';
                                                @endphp
                                                @if($rowStock !== null)
                                                    @if($rowStock <= 0)
                                                        <span class="order-stock-pill is-out mt-1">
                                                            <i class="bi bi-exclamation-triangle-fill"></i> OUT OF STOCK
                                                        </span>
                                                    @elseif($rowStock <= 5)
                                                        <span class="order-stock-pill is-low mt-1">
                                                            <i class="bi bi-exclamation-circle-fill"></i> {{ number_format($rowStock) }} LEFT
                                                        </span>
                                                    @else
                                                        <span class="order-stock-pill is-ok mt-1">
                                                            <i class="bi bi-check-circle-fill"></i> {{ number_format($rowStock) }} IN STOCK
                                                        </span>
                                                    @endif
                                                    {{-- <div class="order-inventory-card {{ $rowStock <= 0 ? 'is-out' : '' }}">
                                                        <div class="order-inventory-grid">
                                                            <div>
                                                                <span class="order-inventory-label">Stock</span>
                                                                <span class="order-inventory-value">{{ number_format($rowStockAfterMovement) }} pcs</span>
                                                            </div>
                                                            <div>
                                                                <span class="order-inventory-label">Sales</span>
                                                                <span class="order-inventory-value">{{ number_format($rowSalesOrders) }} pcs</span>
                                                            </div>
                                                            <div>
                                                                <span class="order-inventory-label">Available</span>
                                                                <span class="order-inventory-value {{ $rowStock <= 0 ? 'is-out' : 'is-good' }}">{{ number_format($rowStock) }} pcs</span>
                                                            </div>
                                                            <div>
                                                                <span class="order-inventory-label">Status</span>
                                                                <span class="order-inventory-value {{ $rowStock <= 0 ? 'is-out' : 'is-good' }}">{{ strtoupper($rowInventoryStatus) }}</span>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                @endif
                                            @endif
                                        </td>
                                        <td class="payment-method-cell">
                                            <span class="badge rounded-pill px-3 py-2 text-dark bg-info border fw-semibold">
                                                {{-- {{ ucfirst($order->payment_method) }} --}}
                                                {{ strtoupper(ucwords(str_replace('_', ' ', $order->payment_method))) }}
                                            </span>
                                        </td>

                                        <td class="delivery-type-cell">
                                            <span class="badge rounded-pill px-3 py-2 text-dark bg-info border fw-semibold">
                                                {{ strtoupper(ucfirst($order->delivery_type)) }}
                                            </span>
                                        </td>

                                        <td class="delivery-fee-cell">
                                            @if($order->delivery_type === 'delivery')
                                                {{ number_format($order->delivery_fee ?? 0, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td class="status-cell">
                                            @if($order->status == 'Pending')
                                                <span class="badge rounded-pill px-3 py-2 bg-secondary text-dark fw-semibold">
                                                    <i class="bi bi-clock-history me-1"></i> PENDING
                                                </span>
                                            @elseif($order->status == 'SO Created')
                                                <span class="badge rounded-pill px-3 py-2 bg-secondary text-dark fw-semibold">
                                                   <i class="bi bi-clock-history me-1"></i> SO CREATED
                                                </span>
                                            @elseif($order->status == 'Completed')
                                                <span class="badge rounded-pill px-3 py-2 bg-success fw-semibold">
                                                    <i class="bi bi-check-circle me-1"></i> COMPLETED
                                                </span>
                                            @elseif($order->status == 'Cancelled')
                                                <span class="badge rounded-pill px-3 py-2 bg-danger fw-semibold">
                                                    <i class="bi bi-x-circle me-1"></i> CANCELLED
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-2 bg-secondary fw-semibold">
                                                    {{ strtoupper(ucfirst($order->status)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" 
                                                class="btn btn-primary edit-btn"
                                                data-id="{{ $order->id }}"
                                                data-row-id="transaction-row-{{ $orderTab['key'] }}-{{$order->id}}"
                                                data-source="{{ $order->source_database ?? 'dms_prei' }}"
                                                data-qty="{{ $order->qty }}"
                                                data-price="{{ $order->price }}"
                                                data-payment="{{ $order->payment_method }}"
                                                data-delivery="{{ $order->delivery_type }}"
                                                data-delivery-fee="{{ $order->delivery_fee }}"
                                                data-dealer-type="{{ optional($order->adDealer)->dealer_type ?: 'Project' }}"
                                                @if(empty($order->is_remote) && auth()->user()->role !== 'Admin')
                                                    data-track-stock="1"
                                                    data-stock="{{ $rowStock ?? 0 }}"
                                                    data-editable-stock="{{ ($rowStock ?? 0) + (float) $order->qty }}"
                                                    data-stock-after="{{ $rowStockAfterMovement ?? 0 }}"
                                                    data-sales-orders="{{ $rowSalesOrders ?? 0 }}"
                                                    data-inventory-status="{{ $rowInventoryStatus ?? 'No stock' }}"
                                                    data-area="{{ $rowArea ?? '' }}"
                                                @else
                                                    data-track-stock="0"
                                                    data-stock=""
                                                    data-editable-stock=""
                                                    data-stock-after=""
                                                    data-sales-orders=""
                                                    data-inventory-status=""
                                                    data-area=""
                                                @endif
                                                data-status="{{ $order->status }}" {{ $order->status == 'Completed' ? 'disabled' : '' }}>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        @if(auth()->user()->role == "Admin" && auth()->user()->can_delete === "on")
                                            <td style="text-align: center;">
                                                @if(empty($order->is_remote))
                                                    <button type="button" class="btn btn-danger btn-sm delete-single" 
                                                            data-id="{{ $order->id }}" 
                                                            title="Delete"
                                                            style="cursor: pointer;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="remote-order-pill"><i class="bi bi-shield-lock"></i> CRM</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(auth()->user()->role == "Admin")
  @include('new_transaction_admin')
@else
  @include('new_transaction')
@endif
@include('orders.edit')
@include('orders.purchase_order')

@endsection

@section('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- <script>
  document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#customerSelect', {
      create: false,
      allowEmptyOption: true,
      placeholder: "Search Customer"
    });
    new TomSelect('#dealer', {
      create: false,
      allowEmptyOption: true,
      placeholder: "Search Dealer"
    });
  });
</script> --}}
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    $('.orders-data-table').each(function () {
        const table = $(this).DataTable({
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-sm btn-success export-btn-custom',
                    title: 'Transactions'
                }
            ],
            columnDefs: [
                { 
                    orderable: false, 
                    targets: [0, -1]
                },
                {
                    className: 'text-center',
                    targets: [0]
                }
            ],
            destroy: true,
            order: [[1, 'desc']]
        });

        table.buttons().container().appendTo('#exportExcelContainer');
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $(document).on('change', '.checkbox-item', function() {
        updateUI();
    });

    $(document).on('change', '.select-all', function() {
        const isChecked = $(this).prop('checked');
        $(this).closest('table').find('.checkbox-item').prop('checked', isChecked);
        updateUI();
    });

    $(document).on('click', '#deleteSelectedBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        performBulkDelete();
    });

    $(document).on('click', '.delete-single', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const transactionId = $(this).data('id');
        performSingleDelete(transactionId);
    });

    function updateUI() {
        const $checkboxes = $('.checkbox-item');
        const $checked = $('.checkbox-item:checked');
        const checkedCount = $checked.length;
        const totalCount = $checkboxes.length;
        
        if (checkedCount > 0) {
            $('#deleteSelectedBtn').show();
        } else {
            $('#deleteSelectedBtn').hide();
        }
        
        $('.orders-data-table').each(function () {
            const $table = $(this);
            const $selectAll = $table.find('.select-all');
            const $tableCheckboxes = $table.find('.checkbox-item');
            const $tableChecked = $table.find('.checkbox-item:checked');

            if ($tableChecked.length === $tableCheckboxes.length && $tableCheckboxes.length > 0) {
                $selectAll.prop('checked', true);
                $selectAll.prop('indeterminate', false);
            } else if ($tableChecked.length > 0) {
                $selectAll.prop('checked', false);
                $selectAll.prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false);
                $selectAll.prop('indeterminate', false);
            }
        });
    }

    function performSingleDelete(transactionId) {
        if (!transactionId || isNaN(transactionId)) {
            Swal.fire('Error!', 'Invalid transaction ID', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This transaction will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const deleteUrl = `{{ url('/transactions') }}/${transactionId}`;

                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success || 'Transaction deleted successfully', 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let message = 'An error occurred while deleting the transaction';
                        
                        if (xhr.status === 404) {
                            message = 'Route not found. Please check your routes configuration.';
                        } else if (xhr.status === 405) {
                            message = 'Method not allowed. Check if the route accepts DELETE method.';
                        } else if (xhr.status === 403) {
                            message = 'You are not authorized to delete this transaction.';
                        } else if (xhr.status === 500) {
                            message = 'Server error. Please check the server logs.';
                        }
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                message = response.error;
                            } else if (response.message) {
                                message = response.message;
                            }
                        } catch (e) {
                            // Use default message
                        }
                        
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    }

    function performBulkDelete() {
        const selectedIds = $('.checkbox-item:checked').map(function() {
            return parseInt($(this).data('id'));
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire('No Selection', 'Please select at least one transaction to delete.', 'warning');
            return;
        }

        const invalidIds = selectedIds.filter(id => isNaN(id) || id <= 0);
        if (invalidIds.length > 0) {
            Swal.fire('Error!', 'Some transaction IDs are invalid', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedIds.length} transaction(s). This cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the selected transactions.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const bulkDeleteUrl = '{{ url("/transactions/bulk-delete") }}';

                $.ajax({
                    url: bulkDeleteUrl,
                    type: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: csrfToken
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success || 'Transactions deleted successfully', 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let message = 'An error occurred while deleting the transactions';
                        
                        if (xhr.status === 404) {
                            message = 'Route not found. Please check your routes configuration.';
                        } else if (xhr.status === 405) {
                            message = 'Method not allowed. Check if the route accepts POST method.';
                        } else if (xhr.status === 403) {
                            message = 'You are not authorized to delete these transactions.';
                        } else if (xhr.status === 500) {
                            message = 'Server error. Please check the server logs.';
                        }
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                message = response.error;
                            } else if (response.message) {
                                message = response.message;
                            }
                        } catch (e) {
                            // Use default message
                        }
                        
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    }

    setTimeout(function() {
        updateUI();
    }, 100);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const purchaseOrderForm = document.getElementById('purchaseOrderForm');
    const productSelect = document.getElementById('poProduct');
    const dealerSelect = document.getElementById('poDealer');
    const qtyInput = document.getElementById('poQty');
    const submitButton = document.getElementById('poSubmitBtn');
    const deliveryType = document.getElementById('poDeliveryType');
    const deliveryFeeWrap = document.getElementById('poDeliveryFeeWrap');
    const deliveryFeeInput = document.getElementById('poDeliveryFee');
    const skuText = document.getElementById('poSku');
    const dealerAreaText = document.getElementById('poDealerArea');
    const unitPriceText = document.getElementById('poUnitPrice');
    const pointsText = document.getElementById('poPoints');
    const totalText = document.getElementById('poTotal');
    const stockPanel = document.getElementById('poStockPanel');
    const stockAlert = document.getElementById('poStockAlert');
    const stockIcon = document.getElementById('poStockIcon');
    const stockStatus = document.getElementById('poStockStatus');
    const stockHelp = document.getElementById('poStockHelp');
    const areaStock = window.poAreaStock || {};
    const inventoryStats = window.poInventoryStats || {};
    const stockAfterMovementText = document.getElementById('poStockAfterMovement');
    const salesOrdersText = document.getElementById('poSalesOrders');
    const availableQtyText = document.getElementById('poAvailableQty');
    const inventoryStatusText = document.getElementById('poInventoryStatus');

    if (!productSelect || !dealerSelect) {
        return;
    }

    const submitInitiallyDisabled = submitButton ? submitButton.disabled : false;

    function money(value) {
        return 'PHP ' + Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function selectedOption(select) {
        return select.options[select.selectedIndex] || null;
    }

    function formatQty(value) {
        return Number(value || 0).toLocaleString(undefined, {
            maximumFractionDigits: 2
        });
    }

    function stockForAreaProduct(area, productId) {
        if (!area || !productId || !areaStock[area]) {
            return null;
        }

        return parseFloat(areaStock[area][productId] || 0);
    }

    function statsForAreaProduct(area, productId) {
        if (!area || !productId || !inventoryStats[area]) {
            return null;
        }

        return inventoryStats[area][productId] || null;
    }

    function updateStockMetrics(stats) {
        if (!stockAfterMovementText || !salesOrdersText || !availableQtyText || !inventoryStatusText) {
            return;
        }

        if (!stats) {
            stockAfterMovementText.textContent = '-';
            salesOrdersText.textContent = '-';
            availableQtyText.textContent = '-';
            inventoryStatusText.textContent = '-';
            inventoryStatusText.className = '';
            return;
        }

        const availableQty = parseFloat(stats.available || 0);
        stockAfterMovementText.textContent = formatQty(stats.stock_after_movement || 0) + ' pcs';
        salesOrdersText.textContent = formatQty(stats.sales_orders || 0) + ' pcs';
        availableQtyText.textContent = formatQty(availableQty) + ' pcs';
        inventoryStatusText.textContent = availableQty <= 0 ? 'No stock' : 'Good';
        inventoryStatusText.className = availableQty <= 0 ? 'text-danger' : 'text-success';
    }

    function setStockPanel(state, status, help, iconClass) {
        if (!stockPanel) {
            return;
        }

        stockPanel.classList.remove('is-ok', 'is-low', 'is-out');

        if (state) {
            stockPanel.classList.add(state);
        }

        stockStatus.textContent = status;
        stockHelp.textContent = help;
        stockIcon.className = iconClass;
    }

    function showStockAlert(type, message) {
        if (!stockAlert) {
            return;
        }

        stockAlert.className = `alert alert-${type} mb-3`;
        stockAlert.textContent = message;
        stockAlert.classList.remove('d-none');
    }

    function hideStockAlert() {
        if (!stockAlert) {
            return;
        }

        stockAlert.classList.add('d-none');
        stockAlert.textContent = '';
    }

    function refreshPurchaseOrderSummary() {
        const product = selectedOption(productSelect);
        const dealer = selectedOption(dealerSelect);
        const qty = parseFloat(qtyInput.value || 0);
        const price = product ? parseFloat(product.dataset.price || 0) : 0;
        const points = product ? parseFloat(product.dataset.points || 0) : 0;
        const tracksStock = product && product.dataset.trackStock === '1';
        const dealerArea = dealer && dealer.value ? (dealer.dataset.area || '') : '';
        const inventoryStat = tracksStock ? statsForAreaProduct(dealerArea, product.value) : null;
        const availableStock = inventoryStat ? parseFloat(inventoryStat.available || 0) : stockForAreaProduct(dealerArea, product.value);
        const isDelivery = deliveryType.value === 'delivery';
        const deliveryFee = isDelivery ? parseFloat(deliveryFeeInput.value || 0) : 0;
        let stockBlocksSubmit = false;

        deliveryFeeWrap.classList.toggle('d-none', !isDelivery);
        deliveryFeeInput.required = isDelivery;

        if (!isDelivery) {
            deliveryFeeInput.value = '';
        }

        skuText.textContent = product && product.value ? (product.dataset.sku || '-') : '-';
        dealerAreaText.textContent = dealerArea || '-';
        unitPriceText.textContent = money(price);
        pointsText.textContent = Number(points * qty || 0).toLocaleString();
        totalText.textContent = money((price * qty) + deliveryFee);
        updateStockMetrics(inventoryStat);

        if (tracksStock && product.value) {
            if (!dealerArea) {
                stockBlocksSubmit = true;
                qtyInput.removeAttribute('max');
                setStockPanel(
                    'is-low',
                    'Select dealer area',
                    'Choose a dealer first so this order can check the correct area stock.',
                    'bi bi-exclamation-circle-fill fs-4 text-warning'
                );
                showStockAlert('warning', 'Please select a dealer before choosing quantity. Stock is checked by dealer area.');
            } else if (availableStock === null) {
                stockBlocksSubmit = true;
                qtyInput.max = 0;
                setStockPanel(
                    'is-out',
                    'No area stock record',
                    'No available stock was found for this product in ' + dealerArea + '.',
                    'bi bi-exclamation-triangle-fill fs-4 text-danger'
                );
                showStockAlert('danger', 'No stock record exists for this product in ' + dealerArea + '. Please choose another product or restock this area.');
            } else {
                qtyInput.max = Math.max(availableStock, 0);

                if (availableStock <= 0) {
                    stockBlocksSubmit = true;
                    setStockPanel(
                        'is-out',
                        'Out of stock',
                        'This item has no available stock in ' + dealerArea + '.',
                        'bi bi-exclamation-triangle-fill fs-4 text-danger'
                    );
                    showStockAlert('danger', 'The selected product is out of stock in ' + dealerArea + '. Please choose another product or restock this area.');
                } else if (qty > availableStock) {
                    stockBlocksSubmit = true;
                    setStockPanel(
                        'is-low',
                        formatQty(availableStock) + ' available',
                        'Reduce quantity to continue for ' + dealerArea + '.',
                        'bi bi-exclamation-circle-fill fs-4 text-warning'
                    );
                    showStockAlert('warning', 'Quantity exceeds available stock in ' + dealerArea + '. Reduce the quantity to continue.');
                } else {
                    setStockPanel(
                        availableStock <= 5 ? 'is-low' : 'is-ok',
                        formatQty(availableStock) + ' available',
                        'This item can be ordered for ' + dealerArea + '.',
                        availableStock <= 5
                            ? 'bi bi-exclamation-circle-fill fs-4 text-warning'
                            : 'bi bi-check-circle-fill fs-4 text-success'
                    );
                    hideStockAlert();
                }
            }
        } else {
            qtyInput.removeAttribute('max');
            setStockPanel(
                '',
                product && product.value ? 'Stock not limited' : 'Select a product',
                product && product.value ? 'Admin product selection is not restricted by area stock.' : 'Choose a dealer and product to check area stock.',
                'bi bi-box-seam fs-4 text-secondary'
            );
            hideStockAlert();
        }

        if (submitButton) {
            submitButton.disabled = submitInitiallyDisabled || stockBlocksSubmit;
        }
    }

    [productSelect, dealerSelect, qtyInput, deliveryType, deliveryFeeInput].forEach(function (element) {
        element.addEventListener('change', refreshPurchaseOrderSummary);
        element.addEventListener('input', refreshPurchaseOrderSummary);
    });

    refreshPurchaseOrderSummary();

    if (purchaseOrderForm) {
        purchaseOrderForm.addEventListener('submit', function (event) {
            const product = selectedOption(productSelect);
            const dealer = selectedOption(dealerSelect);
            const qty = parseFloat(qtyInput.value || 0);
            const tracksStock = product && product.dataset.trackStock === '1';
            const dealerArea = dealer && dealer.value ? (dealer.dataset.area || '') : '';
            const availableStock = tracksStock ? stockForAreaProduct(dealerArea, product.value) : null;

            if (tracksStock && product.value && (!dealerArea || availableStock === null || availableStock <= 0 || qty > availableStock)) {
                event.preventDefault();
                refreshPurchaseOrderSummary();
                Swal.fire('Out of stock', 'Please choose an item with available stock in the dealer area or reduce the quantity.', 'warning');
            }
        });
    }
});
</script>
<script id="update-js">
document.addEventListener('DOMContentLoaded', function () {

    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    const updateButton = document.getElementById('update-btn');
    const qtyInput = document.getElementById('edit-qty');
    const deliverySelect = document.getElementById('edit-delivery');
    const deliveryFeeWrapper = document.getElementById('edit-delivery-fee-wrapper');
    const deliveryFeeInput = document.getElementById('edit-delivery-fee');
    const statusSelect = document.getElementById('edit-status');
    const stockPanel = document.getElementById('edit-stock-panel');
    const stockAlert = document.getElementById('edit-stock-alert');
    const stockIcon = document.getElementById('edit-stock-icon');
    const stockStatus = document.getElementById('edit-stock-status');
    const stockHelp = document.getElementById('edit-stock-help');
    const stockAfterText = document.getElementById('edit-stock-after');
    const salesOrdersText = document.getElementById('edit-sales-orders');
    const availableStockText = document.getElementById('edit-available-stock');
    const inventoryStatusText = document.getElementById('edit-inventory-status');
    let editTracksStock = false;
    let editAvailableStock = null;
    let editStockLimit = null;
    let editStockAfterMovement = 0;
    let editSalesOrders = 0;
    let editInventoryStatus = 'No stock';
    let editDealerArea = '';
    let editDealerType = 'Project';
    let editOrderSource = 'dms_prei';
    let editRowId = '';

    function toggleDeliveryFee() {
        const isDelivery = deliverySelect.value === 'delivery';
        const isRegularDealer = String(editDealerType || '').toLowerCase() === 'regular';
        const shouldShowDeliveryFee = isDelivery && isRegularDealer;

        deliveryFeeWrapper.classList.toggle('d-none', !shouldShowDeliveryFee);
        deliveryFeeInput.required = shouldShowDeliveryFee;
        deliveryFeeInput.disabled = !shouldShowDeliveryFee;

        if (!shouldShowDeliveryFee) {
            deliveryFeeInput.value = '';
        }
    }

    function formatQty(value) {
        return Number(value || 0).toLocaleString(undefined, {
            maximumFractionDigits: 2
        });
    }

    function setEditStockPanel(state, status, help, iconClass) {
        if (!stockPanel) {
            return;
        }

        stockPanel.classList.remove('border-success', 'border-warning', 'border-danger', 'bg-light');
        stockPanel.classList.add(state ? `border-${state}` : 'bg-light');
        stockStatus.textContent = status;
        stockHelp.textContent = help;
        stockIcon.className = iconClass;
    }

    function updateEditStockMetrics() {
        if (!stockAfterText || !salesOrdersText || !availableStockText || !inventoryStatusText) {
            return;
        }

        if (!editTracksStock) {
            stockAfterText.textContent = '-';
            salesOrdersText.textContent = '-';
            availableStockText.textContent = '-';
            inventoryStatusText.textContent = '-';
            inventoryStatusText.className = 'fw-bold';
            return;
        }

        stockAfterText.textContent = formatQty(editStockAfterMovement) + ' pcs';
        salesOrdersText.textContent = formatQty(editSalesOrders) + ' pcs';
        availableStockText.textContent = formatQty(editAvailableStock) + ' pcs';
        inventoryStatusText.textContent = editAvailableStock <= 0 ? 'NO STOCK' : 'GOOD';
        inventoryStatusText.className = editAvailableStock <= 0 ? 'fw-bold text-danger' : 'fw-bold text-success';
    }

    function showEditStockAlert(type, message) {
        if (!stockAlert) {
            return;
        }

        stockAlert.className = `alert alert-${type} mb-3`;
        stockAlert.textContent = message;
        stockAlert.classList.remove('d-none');
    }

    function hideEditStockAlert() {
        if (!stockAlert) {
            return;
        }

        stockAlert.classList.add('d-none');
        stockAlert.textContent = '';
    }

    function refreshEditStockState() {
        if (!qtyInput || !statusSelect) {
            return true;
        }

        const qty = parseFloat(qtyInput.value || 0);
        const isCancelled = statusSelect.value === 'Cancelled';
        let stockBlocksUpdate = false;

        if (editTracksStock) {
            const maxEditableQty = Math.max(editStockLimit || 0, 0);
            qtyInput.max = maxEditableQty;
            updateEditStockMetrics();

            if (isCancelled) {
                setEditStockPanel(
                    'warning',
                    'Cancelling order',
                    'Cancelled orders do not consume inventory stock.',
                    'bi bi-exclamation-circle-fill fs-5 text-warning'
                );
                hideEditStockAlert();
            } else if (maxEditableQty <= 0) {
                stockBlocksUpdate = true;
                setEditStockPanel(
                    'danger',
                    'Out of stock',
                    'This item has no available stock in ' + (editDealerArea || 'this dealer area') + '.',
                    'bi bi-exclamation-triangle-fill fs-5 text-danger'
                );
                showEditStockAlert('danger', 'This dealer order cannot be updated because inventory shows no stock for ' + (editDealerArea || 'this dealer area') + '.');
            } else if (qty > maxEditableQty) {
                stockBlocksUpdate = true;
                setEditStockPanel(
                    'warning',
                    formatQty(editAvailableStock) + ' available',
                    'Reduce the quantity before updating this dealer order for ' + (editDealerArea || 'this area') + '.',
                    'bi bi-exclamation-circle-fill fs-5 text-warning'
                );
                showEditStockAlert('warning', 'Quantity exceeds available inventory stock for this order. Reduce the quantity to continue.');
            } else {
                setEditStockPanel(
                    editAvailableStock <= 5 ? 'warning' : 'success',
                    formatQty(editAvailableStock) + ' available',
                    'This dealer order can be updated for ' + (editDealerArea || 'this area') + '.',
                    editAvailableStock <= 5
                        ? 'bi bi-exclamation-circle-fill fs-5 text-warning'
                        : 'bi bi-check-circle-fill fs-5 text-success'
                );
                hideEditStockAlert();
            }
        } else {
            qtyInput.removeAttribute('max');
            setEditStockPanel(
                '',
                'Stock not limited',
                'Admin updates are not restricted by inventory stock.',
                'bi bi-box-seam fs-5 text-secondary'
            );
            updateEditStockMetrics();
            hideEditStockAlert();
        }

        if (updateButton) {
            updateButton.disabled = stockBlocksUpdate;
        }

        return !stockBlocksUpdate;
    }

    // OPEN MODAL
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit-id').value = this.dataset.id;
            qtyInput.value = this.dataset.qty;
            document.getElementById('edit-payment').value = this.dataset.payment;
            deliverySelect.value = this.dataset.delivery;
            deliveryFeeInput.value = this.dataset.deliveryFee || '';
            statusSelect.value = this.dataset.status;
            editTracksStock = this.dataset.trackStock === '1';
            editAvailableStock = editTracksStock ? parseFloat(this.dataset.stock || 0) : null;
            editStockLimit = editTracksStock ? parseFloat(this.dataset.editableStock || this.dataset.stock || 0) : null;
            editStockAfterMovement = editTracksStock ? parseFloat(this.dataset.stockAfter || 0) : 0;
            editSalesOrders = editTracksStock ? parseFloat(this.dataset.salesOrders || 0) : 0;
            editInventoryStatus = this.dataset.inventoryStatus || (editAvailableStock <= 0 ? 'No stock' : 'Good');
            editDealerArea = this.dataset.area || '';
            editDealerType = this.dataset.dealerType || 'Project';
            editOrderSource = this.dataset.source || 'dms_prei';
            editRowId = this.dataset.rowId || ('transaction-row-' + this.dataset.id);
            toggleDeliveryFee();
            refreshEditStockState();

            modal.show();
        });
    });
    deliverySelect.addEventListener('change', toggleDeliveryFee);
    qtyInput.addEventListener('input', refreshEditStockState);
    statusSelect.addEventListener('change', refreshEditStockState);

    // UPDATE FUNCTION
    updateButton.addEventListener('click', async function () {

        if (!refreshEditStockState()) {
            Swal.fire('Out of stock', 'Please reduce the quantity or cancel the order before updating.', 'warning');
            return;
        }

        const id = document.getElementById('edit-id').value;

        const formData = new FormData();
        formData.append('qty', qtyInput.value);
        formData.append('payment_method', document.getElementById('edit-payment').value);
        formData.append('delivery_type', deliverySelect.value);
        formData.append('delivery_fee', deliveryFeeInput.disabled ? '' : deliveryFeeInput.value);
        formData.append('status', statusSelect.value);
        formData.append('order_source', editOrderSource);
        formData.append('_method', 'PUT');

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(`/orders/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message);

            const row = document.getElementById(editRowId);
            const editButton = row.querySelector('.edit-btn');
            const qty = parseFloat(data.qty || 0);
            const price = parseFloat(editButton.dataset.price || 0);
            const deliveryFee = parseFloat(data.delivery_fee || 0);

            row.querySelector('.qty-cell').textContent = formatNumber(qty);
            row.querySelector('.amount-cell').textContent = formatNumber((qty * price) + deliveryFee);
            row.querySelector('.payment-method-cell').innerHTML =
                `<span class="badge rounded-pill px-3 py-2 text-dark bg-info border fw-semibold">
                    ${formatLabel(data.payment_method).toUpperCase()}
                </span>`;
            row.querySelector('.delivery-type-cell').innerHTML =
                `<span class="badge rounded-pill px-3 py-2 text-dark bg-info border fw-semibold">
                    ${capitalize(data.delivery_type).toUpperCase()}
                </span>`;
            row.querySelector('.delivery-fee-cell').textContent =
                data.delivery_type === 'delivery' ? formatNumber(deliveryFee) : '-';
            row.querySelector('.dealer-points-cell').innerHTML =
                `<span class="text-success">${data.points_dealer || 0}</span>`;
            row.querySelector('.status-cell').innerHTML = renderStatus(data.status);

            editButton.dataset.qty = data.qty;
            editButton.dataset.payment = data.payment_method;
            editButton.dataset.delivery = data.delivery_type;
            editButton.dataset.deliveryFee = data.delivery_fee || '';
            editButton.dataset.status = data.status;

            modal.hide();

            Swal.fire('Updated!', 'Order updated successfully', 'success');

        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    });

    function capitalize(text) {
        text = String(text || '');
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function formatLabel(text) {
        return String(text || '').replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }

    function formatNumber(number) {
        return Number(number || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function renderStatus(status) {
        if (status === 'Completed') {
            return `<span class="badge bg-success px-3 py-2">
                        <i class="bi bi-check-circle"></i> Completed
                    </span>`;
        }
        if (status === 'Cancelled') {
            return `<span class="badge bg-danger px-3 py-2">
                        <i class="bi bi-x-circle"></i> Cancelled
                    </span>`;
        }
        if (status === 'SO Created') {
            return `<span class="badge bg-secondary text-dark px-3 py-2">
                        <i class="bi bi-clock-history"></i> SO Created
                    </span>`;
        }
        if (status === 'For Delivery') {
            return `<span class="badge bg-secondary text-dark px-3 py-2">
                        <i class="bi bi-truck"></i> For Delivery
                    </span>`;
        }
        return `<span class="badge bg-secondary text-dark px-3 py-2">
                    <i class="bi bi-clock-history"></i> Pending
                </span>`;
    }

});
</script>

@endsection
