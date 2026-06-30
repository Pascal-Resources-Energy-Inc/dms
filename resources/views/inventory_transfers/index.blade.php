@extends('layouts.header')

@section('header')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
    <div class="inventory-head d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div>
            <div class="inventory-eyebrow">Inventory Transfer</div>
            <h4 class="mb-1 fw-bold">Out and Area-to-Area Stock Movement</h4>
            <div class="text-muted small">Manage LPG SKU inventory for every area assigned to this account.</div>
        </div>
        <div class="inventory-head-actions mt-3 mt-lg-0">
            <span class="badge bg-light text-dark border px-3 py-2">{{ $areas->count() }} Area{{ $areas->count() == 1 ? '' : 's' }}</span>
            <span class="badge bg-light text-dark border px-3 py-2">{{ $adItems->count() }} Product{{ $adItems->count() == 1 ? '' : 's' }}</span>
            <span class="badge bg-primary px-3 py-2">{{ date('M d, Y') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-3 mb-3">
        {{-- <div class="col-lg-3 col-md-6">
            <div class="inventory-kpi p-3 d-flex align-items-center">
                <span class="inventory-icon bg-success me-3"><i class="ti ti-arrow-down fs-4"></i></span>
                <div>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['total_in'] ?? 0) }}</h4>
                    <div class="small text-muted">Total In</div>
                </div>
            </div>
        </div> --}}
        <div class="col-lg-3 col-md-6">
            <div class="inventory-kpi p-3 d-flex align-items-center">
                <span class="inventory-icon bg-danger me-3"><i class="ti ti-arrow-up fs-4"></i></span>
                <div>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['total_out'] ?? 0) }}</h4>
                    <div class="small text-muted">Total Out</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="inventory-kpi p-3 d-flex align-items-center">
                <span class="inventory-icon bg-primary me-3"><i class="ti ti-arrows-transfer-up fs-4"></i></span>
                <div>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['total_transfer'] ?? 0) }}</h4>
                    <div class="small text-muted">Total Transferred</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="inventory-kpi p-3 d-flex align-items-center">
                <span class="inventory-icon bg-dark me-3"><i class="ti ti-package fs-4"></i></span>
                <div>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['ending_stock'] ?? 0) }}</h4>
                    <div class="small text-muted">Total Ending Stock</div>
                </div>
            </div>
        </div>
    </div>

    <div class="stock-overview mb-3">
        <div class="row g-0">
            <div class="col-lg-3 col-md-6">
                <div class="stock-overview-item">
                    <div class="small text-muted">Areas With Stock</div>
                    <div class="h5 mb-0 fw-bold">{{ $areaSummaries->count() }}</div>
                    <div class="small text-muted">assigned stock locations</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stock-overview-item">
                    <div class="small text-muted">SKUs With Balance</div>
                    <div class="h5 mb-0 fw-bold">{{ $productSummaries->count() }}</div>
                    <div class="small text-muted">products currently tracked</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stock-overview-item">
                    <div class="small text-muted">Low Stock</div>
                    <div class="h5 mb-0 fw-bold text-warning">{{ number_format($summary['low_stock'] ?? 0) }}</div>
                    <div class="small text-muted">balances from 1 to 10 pcs</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stock-overview-item">
                    <div class="small text-muted">No Stock</div>
                    <div class="h5 mb-0 fw-bold text-danger">{{ number_format($summary['no_stock'] ?? 0) }}</div>
                    <div class="small text-muted">balances at zero or below</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="inventory-card h-100 card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Area Stock Snapshot</h5>
                    <span class="badge bg-light text-dark">By area</span>
                </div>
                <div class="card-body">
                    <div class="summary-list">
                        @forelse($areaSummaries as $areaSummary)
                            <div class="summary-row">
                                <div>
                                    <div class="name">{{ $areaSummary->area }}</div>
                                    <div class="meta">{{ number_format($areaSummary->sku_count) }} SKU{{ $areaSummary->sku_count == 1 ? '' : 's' }} tracked</div>
                                </div>
                                <span class="badge {{ $areaSummary->qty <= 10 ? 'bg-warning text-dark' : 'bg-success' }} px-3 py-2">
                                    {{ number_format($areaSummary->qty) }} pcs
                                </span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No area stock yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="inventory-card h-100 card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product/ SKU Stock Snapshot</h5>
                    <span class="badge bg-light text-dark">By product</span>
                </div>
                <div class="card-body">
                    <div class="summary-list">
                        @forelse($productSummaries as $productSummary)
                            <div class="summary-row">
                                <div>
                                    <div class="name">{{ $productSummary->sku ? $productSummary->sku . ' - ' : '' }}{{ $productSummary->product_name }}</div>
                                    <div class="meta">Stored in {{ number_format($productSummary->area_count) }} area{{ $productSummary->area_count == 1 ? '' : 's' }}</div>
                                </div>
                                <span class="badge {{ $productSummary->qty <= 10 ? 'bg-warning text-dark' : 'bg-success' }} px-3 py-2">
                                    {{ number_format($productSummary->qty) }} pcs
                                </span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No SKU stock yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="inventory-card sticky-form">
                <div class="card-header bg-white">
                    <h5 class="mb-0">New Movement</h5>
                    <div class="small text-muted">Save one stock movement to the ledger.</div>
                </div>
                <div class="card-body">
                    <div class="soft-note mb-3">
                        Use IN for new supply, OUT for pull-out or adjustment, and TRANSFER when moving stock between your assigned areas.
                    </div>
                    <form method="POST" action="{{ route('inventory-transfers.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Movement Type</label>
                            @php $oldMovementType = old('movement_type', 'out'); @endphp
                            <div class="movement-type-grid" id="movementTypeGrid">
                                <label class="movement-type-option {{ $oldMovementType == 'in' ? 'active' : '' }}">
                                    <input type="radio" name="movement_type" value="in" {{ $oldMovementType == 'in' ? 'checked' : '' }}>
                                    <div class="fw-bold text-success">IN</div>
                                    <div class="small text-muted">Add stock to an area</div>
                                </label>
                                <label class="movement-type-option {{ $oldMovementType == 'out' ? 'active' : '' }}">
                                    <input type="radio" name="movement_type" value="out" {{ $oldMovementType == 'out' ? 'checked' : '' }}>
                                    <div class="fw-bold text-danger">OUT</div>
                                    <div class="small text-muted">Remove stock from area</div>
                                </label>
                                <label class="movement-type-option {{ $oldMovementType == 'transfer' ? 'active' : '' }}">
                                    <input type="radio" name="movement_type" value="transfer" {{ $oldMovementType == 'transfer' ? 'checked' : '' }}>
                                    <div class="fw-bold text-primary">TRANSFER</div>
                                    <div class="small text-muted">Move area to area</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">LPG SKU / Product</label>
                            <select name="product_id" id="productSelect" class="form-control select2" data-selected-value="{{ old('product_id') }}" required>
                                <option value="">Select product</option>
                                @forelse($stockProducts as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->sku ? $product->sku . ' - ' : '' }}{{ $product->product_name }}
                                    </option>
                                @empty
                                    <option value="" disabled>No stock products available</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="mb-3 js-from-area">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">From Area</label>
                                <span class="available-pill" id="availableQty">Available: 0</span>
                            </div>
                            <select name="from_area" id="fromAreaSelect" class="form-control select2">
                                <option value="">Select source area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area }}" {{ old('from_area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                                @endforeach
                            </select>
                            <div class="stock-warning" id="stockWarning"></div>
                        </div>

                        <div class="mb-3 js-to-area">
                            <label class="form-label">To Area</label>
                            <select name="to_area" id="toAreaSelect" class="form-control select2">
                                <option value="">Select receiving area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area }}" {{ old('to_area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Qty</label>
                                <input type="number" min="1" name="qty" id="qtyInput" class="form-control" value="{{ old('qty') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Cost</label>
                                <input type="number" min="0" step="0.01" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}">
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="DR, invoice, or memo no." data-uppercase> 
                        </div> --}}

                        <div class="mb-3 js-out-type">
                            <label class="form-label">Out Type</label>
                            <select name="out_type" id="outTypeSelect" class="form-control select2">
                                <option value="">Select Type</option>
                                <option value="Inventory Adjustment" {{ old('out_type') == 'Inventory Adjustment' ? 'selected' : '' }} data-uppercase>Inventory Adjustment</option>
                                <option value="Return and Refund" {{ old('out_type') == 'Return and Refund' ? 'selected' : '' }} data-uppercase>Return and Refund</option>
                                <option value="Pull Out" {{ old('out_type') == 'Pull Out' ? 'selected' : '' }} data-uppercase>Pull Out</option>
                                <option value="Replace" {{ old('out_type') == 'Replace' ? 'selected' : '' }} data-uppercase>Replace</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', date('Y-m-d')) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                        </div>

                        <div class="movement-preview mb-3">
                            <div class="small text-muted mb-1">Movement Preview</div>
                            <div class="fw-semibold" id="movementPreview">Select product, area, and quantity.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Save Movement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="inventory-card mb-3 card">
                <div class="card-header bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                        <div>
                            <h5 class="mb-0">Current Stock by Area</h5>
                            <div class="small text-muted">Available stock deducts completed dealer orders in the same area.</div>
                        </div>
                        <span class="badge bg-light text-dark mt-2 mt-lg-0">Ledger balance</span>
                    </div>
                    <div class="balance-tools mt-3">
                        <input type="text" id="balanceSearch" class="form-control" placeholder="Search area, SKU, or product">
                        <select id="balanceStatusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="good">Good</option>
                            <option value="low">Low</option>
                            <option value="none">No stock</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table inventory-table align-middle mb-0" id="stockBalanceTable">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Product</th>
                                    <th class="text-end">Stock After Movement</th>
                                    <th class="text-end">Sales Orders</th>
                                    <th class="text-end">Available</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($balances as $balance)
                                    @php
                                        $orderedQty = (float) ($balance->ordered_qty ?? 0);
                                        $stockAfterMovement = (float) ($balance->stock_after_movement ?? ((float) $balance->qty + $orderedQty));
                                        $availableQty = (float) ($balance->available_qty ?? ($stockAfterMovement - $orderedQty));
                                        $balanceStatus = $availableQty <= 0 ? 'none' : ($availableQty <= 10 ? 'low' : 'good');
                                    @endphp
                                    <tr class="js-balance-row" data-search="{{ strtolower($balance->area . ' ' . ($balance->sku ?? '') . ' ' . $balance->product_name) }}" data-status="{{ $balanceStatus }}">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $balance->area }}</div>
                                            <div class="small text-muted">Area stock</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ strtoupper($balance->product_name) }}</div>
                                            <div class="small text-muted">{{ strtoupper($balance->sku ?? 'No SKU') }}</div>
                                        </td>
                                        <td class="text-end">
                                            <span class="stock-pill stock-pill-in">{{ number_format($stockAfterMovement) }} pcs</span>
                                        </td>
                                        <td class="text-end">
                                            @if($orderedQty > 0)
                                                <span class="stock-pill stock-pill-order">{{ number_format($orderedQty) }} pcs</span>
                                            @else
                                                <span class="stock-pill stock-pill-muted">0 pcs</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="remaining-qty {{ $availableQty < 0 ? 'text-danger' : '' }}">{{ number_format($availableQty) }}</span>
                                        </td>
                                        <td>
                                            @if($availableQty <= 0)
                                                <span class="status-badge status-none">No stock</span>
                                            @elseif($availableQty <= 10)
                                                <span class="status-badge status-low">Low</span>
                                            @else
                                                <span class="status-badge status-good">Good</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="inventory-card card">
                <div class="card-header bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                        <div>
                            <h5 class="mb-0">Movement History</h5>
                            <div class="small text-muted">
                                Showing {{ number_format($movements->firstItem() ?? 0) }}-{{ number_format($movements->lastItem() ?? 0) }}
                                of {{ number_format($movements->total()) }} record{{ $movements->total() == 1 ? '' : 's' }}
                            </div>
                        </div>
                        <a href="{{ route('inventory-transfers.index') }}" class="btn btn-sm btn-outline-secondary mt-2 mt-lg-0">Reset Filters</a>
                    </div>
                    <form method="GET" action="{{ route('inventory-transfers.index') }}" class="row g-2 mt-3">
                        <div class="col-lg-2 col-md-4">
                            <select name="movement_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>IN</option>
                                <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>OUT</option>
                                <option value="transfer" {{ request('movement_type') == 'transfer' ? 'selected' : '' }}>TRANSFER</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <select name="product_id" class="form-control">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->sku ? $product->sku . ' - ' : '' }}{{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <select name="area" class="form-control">
                                <option value="">All Areas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-lg-1 col-md-4">
                            <button class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table inventory-table align-middle mb-0" id="movementHistoryTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Product</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th class="text-end">Qty</th>
                                    {{-- <th>Reference</th> --}}
                                    <th>Remarks</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>{{ $movement->transfer_date ? $movement->transfer_date->format('M d, Y') : '-' }}</td>
                                        <td>
                                            @if($movement->movement_type == 'in')
                                                <span class="badge bg-success movement-badge">IN</span>
                                            @elseif($movement->movement_type == 'out')
                                                <span class="badge bg-danger movement-badge">OUT</span>
                                            @else
                                                <span class="badge bg-primary movement-badge">TRANSFER</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ optional($movement->product)->product_name ?: $movement->item_name }}</div>
                                            <div class="small text-muted">{{ $movement->sku ?? 'No SKU' }}</div>
                                        </td>
                                        <td>{{ $movement->from_area ?? '-' }}</td>
                                        <td>{{ $movement->to_area ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($movement->qty) }}</td>
                                        {{-- <td>{{ $movement->reference_no ?? '-' }}</td> --}}
                                        <td>
                                            @if(trim((string) $movement->remarks) !== '')
                                                <div class="movement-remarks">{{ $movement->remarks }}</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('inventory-transfers.destroy', $movement->id) }}" onsubmit="return confirm('Delete this inventory movement?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($movements->hasPages())
                        <div class="inventory-pagination border-top px-3 py-3">
                            {{ $movements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .inventory-head,
    .inventory-card {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .06);
    }

    .inventory-head {
        padding: 20px 22px;
        margin-bottom: 18px;
        border-left: 5px solid #2563eb;
    }

    .inventory-eyebrow {
        color: #6b7280;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .inventory-head-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .inventory-kpi {
        min-height: 100px;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .06);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .inventory-kpi:hover,
    .inventory-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(31, 41, 55, .08);
    }

    .inventory-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
    }

    .inventory-table th {
        background: #f3f6fa;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
    }

    .inventory-table td {
        border-color: #edf0f5;
        vertical-align: middle;
    }

    .inventory-table tbody tr:hover {
        background: #f8fafc;
    }

    .dataTables_wrapper {
        padding: 14px;
    }

    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 8px;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border: 1px solid #dbe2ea;
        border-radius: 7px;
        min-height: 36px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0 !important;
        margin-left: 4px;
    }

    table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .movement-badge {
        min-width: 72px;
        display: inline-block;
    }

    .movement-remarks {
        max-width: 260px;
        min-width: 160px;
        color: #374151;
        font-size: 13px;
        line-height: 1.35;
        white-space: normal;
        word-break: break-word;
    }

    .stock-pill,
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        padding: 4px 10px;
        white-space: nowrap;
    }

    .stock-pill {
        min-width: 78px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #374151;
    }

    .stock-pill-in {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .stock-pill-order {
        border-color: #fed7aa;
        background: #fff7ed;
        color: #9a3412;
    }

    .stock-pill-muted {
        color: #6b7280;
        background: #f9fafb;
    }

    .remaining-qty {
        display: inline-block;
        min-width: 62px;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }

    .status-badge {
        min-width: 78px;
    }

    .status-good {
        background: #dcfce7;
        color: #166534;
    }

    .status-low {
        background: #fef3c7;
        color: #92400e;
    }

    .status-none {
        background: #fee2e2;
        color: #991b1b;
    }

    .movement-type-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .movement-type-option {
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        background: #fff;
        min-height: 76px;
        transition: border-color .15s ease, background .15s ease, transform .15s ease;
    }

    .movement-type-option:hover {
        transform: translateY(-1px);
        border-color: #93c5fd;
    }

    .movement-type-option.active {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .movement-type-option input {
        display: none;
    }

    .movement-preview {
        border-radius: 8px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 12px;
    }

    .available-pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
    }

    .available-pill.is-good {
        background: #dcfce7;
        color: #166534;
    }

    .available-pill.is-low {
        background: #fef3c7;
        color: #92400e;
    }

    .available-pill.is-empty {
        background: #fee2e2;
        color: #991b1b;
    }

    .stock-warning {
        display: none;
        margin-top: 8px;
        border-radius: 8px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 13px;
        padding: 8px 10px;
    }

    .stock-warning.show {
        display: block;
    }

    .stock-overview {
        border: 1px solid #edf0f5;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .06);
    }

    .stock-overview-item {
        padding: 14px 16px;
        border-right: 1px solid #edf0f5;
        min-height: 86px;
    }

    .stock-overview-item:last-child {
        border-right: 0;
    }

    .balance-tools {
        display: grid;
        grid-template-columns: 1fr 180px;
        gap: 10px;
    }

    .summary-list {
        display: grid;
        gap: 10px;
    }

    .summary-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        background: #fff;
    }

    .summary-row .name {
        font-weight: 700;
        line-height: 1.25;
    }

    .summary-row .meta {
        font-size: 12px;
        color: #6b7280;
    }

    .sticky-form {
        position: sticky;
        padding: 20px;
    }

    .soft-note {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e3a8a;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
    }

    @media (max-width: 575px) {
        .movement-type-grid {
            grid-template-columns: 1fr;
        }

        .balance-tools {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991px) {
        .inventory-head-actions {
            justify-content: flex-start;
        }

        .sticky-form {
            position: static;
        }

        .stock-overview-item {
            border-right: 0;
            border-bottom: 1px solid #edf0f5;
        }
    }
</style>

@section('javascript')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const balanceLookup = @json($balanceLookup ?? []);
        const stockProducts = @json($productOptions ?? []);
        const adProducts = @json($adProductOptions ?? $productOptions ?? []);
        const movementInputs = document.querySelectorAll('input[name="movement_type"]');
        const movementOptions = document.querySelectorAll('.movement-type-option');
        const productSelect = document.getElementById('productSelect');
        const fromAreaSelect = document.getElementById('fromAreaSelect');
        const toAreaSelect = document.getElementById('toAreaSelect');
        const qtyInput = document.getElementById('qtyInput');
        const fromArea = document.querySelector('.js-from-area');
        const toArea = document.querySelector('.js-to-area');
        const outType = document.querySelector('.js-out-type');
        const outTypeSelect = document.getElementById('outTypeSelect');
        const availableQty = document.getElementById('availableQty');
        const stockWarning = document.getElementById('stockWarning');
        const movementPreview = document.getElementById('movementPreview');
        const balanceSearch = document.getElementById('balanceSearch');
        const balanceStatusFilter = document.getElementById('balanceStatusFilter');
        let stockBalanceTable = null;
        let syncingMovementFields = false;
        let activeProducts = [];
        let renderedProductType = null;

        if (window.jQuery && window.jQuery.fn.DataTable) {
            stockBalanceTable = window.jQuery('#stockBalanceTable').DataTable({
                pageLength: 10,
                order: [[0, 'asc'], [1, 'asc']],
                autoWidth: false,
                language: {
                    search: 'Search stock:',
                    lengthMenu: 'Show _MENU_ stock rows',
                    emptyTable: 'No inventory balance yet. Add an IN movement to start.'
                }
            });

        }

        function selectedType() {
            const checked = document.querySelector('input[name="movement_type"]:checked');
            return checked ? checked.value : 'in';
        }

        function selectedProduct() {
            const productId = parseInt(productSelect.value || 0);
            return activeProducts.find(function (product) {
                return product.id === productId;
            });
        }

        function optionText(product) {
            return (product.sku ? product.sku + ' - ' : '') + product.name;
        }

        function productOptionsForType(type) {
            return type === 'in' ? stockProducts : adProducts;
        }

        function renderProductOptions() {
            const type = selectedType();

            if (renderedProductType === type) {
                return;
            }

            const options = productOptionsForType(type);
            const selectedValue = productSelect.value || productSelect.dataset.selectedValue || '';
            const currentValueExists = options.some(function (product) {
                return String(product.id) === String(selectedValue);
            });

            activeProducts = options;
            productSelect.innerHTML = '<option value="">Select product</option>';

            options.forEach(function (product) {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = optionText(product);

                if (currentValueExists && String(product.id) === String(selectedValue)) {
                    option.selected = true;
                }

                productSelect.appendChild(option);
            });

            if (!options.length) {
                const option = document.createElement('option');
                option.value = '';
                option.disabled = true;
                option.textContent = type === 'in'
                    ? 'No stock products available'
                    : 'No completed AD purchase order items available';
                productSelect.appendChild(option);
            }

            if (!currentValueExists) {
                productSelect.value = '';
            }

            productSelect.dataset.selectedValue = '';
            renderedProductType = type;
            syncSelect2(productSelect);
        }

        function sourceAvailableQty() {
            const key = productSelect.value + '|' + fromAreaSelect.value;
            return Number(balanceLookup[key] || 0);
        }

        function syncSelect2(select) {
            if (window.jQuery && window.jQuery.fn.select2 && window.jQuery(select).hasClass('select2-hidden-accessible')) {
                window.jQuery(select).trigger('change.select2');
            }
        }

        function setSelectValue(select, value) {
            if (select.value === value) {
                return;
            }

            select.value = value;
            syncSelect2(select);
        }

        function updateActiveType() {
            movementOptions.forEach(function (option) {
                const input = option.querySelector('input');
                option.classList.toggle('active', input.checked);
            });
        }

        function updateAvailableQty() {
            const type = selectedType();
            const available = sourceAvailableQty();

            availableQty.classList.remove('is-good', 'is-low', 'is-empty');
            availableQty.classList.add(available <= 0 ? 'is-empty' : (available <= 10 ? 'is-low' : 'is-good'));

            if (type === 'in') {
                availableQty.textContent = 'Receiving stock';
                stockWarning.classList.remove('show');
                stockWarning.textContent = '';
                return;
            }

            availableQty.textContent = 'Available: ' + available.toLocaleString() + ' pcs';

            const requestedQty = Number(qtyInput.value || 0);
            if (requestedQty > available) {
                stockWarning.textContent = 'Requested quantity is higher than available stock. Available: ' + available.toLocaleString() + ' pcs.';
                stockWarning.classList.add('show');
            } else {
                stockWarning.classList.remove('show');
                stockWarning.textContent = '';
            }
        }

        function updatePreview() {
            const type = selectedType();
            const product = selectedProduct();
            const productName = product ? ((product.sku ? product.sku + ' - ' : '') + product.name) : 'selected product';
            const qty = Number(qtyInput.value || 0).toLocaleString();

            if (!productSelect.value || Number(qtyInput.value || 0) <= 0) {
                movementPreview.textContent = 'Select product, area, and quantity.';
                return;
            }

            if (type === 'in') {
                movementPreview.textContent = 'Add ' + qty + ' pcs of ' + productName + ' to ' + (toAreaSelect.value || 'receiving area') + '.';
                return;
            }

            if (type === 'out') {
                movementPreview.textContent = 'Remove ' + qty + ' pcs of ' + productName + ' from ' + (fromAreaSelect.value || 'source area') + '.';
                return;
            }

            movementPreview.textContent = 'Transfer ' + qty + ' pcs of ' + productName + ' from ' + (fromAreaSelect.value || 'source area') + ' to ' + (toAreaSelect.value || 'receiving area') + '.';
        }

        function preventSameArea() {
            Array.prototype.forEach.call(toAreaSelect.options, function (option) {
                option.disabled = selectedType() === 'transfer' && option.value && option.value === fromAreaSelect.value;
            });

            syncSelect2(toAreaSelect);
        }

        function refreshAreaFields() {
            if (syncingMovementFields) {
                return;
            }

            syncingMovementFields = true;
            const type = selectedType();

            renderProductOptions();

            const needsFromArea = type === 'out' || type === 'transfer';
            const needsToArea = type === 'in' || type === 'transfer';
            const needsOutType = type === 'out';

            fromArea.style.display = needsFromArea ? '' : 'none';
            toArea.style.display = needsToArea ? '' : 'none';
            outType.style.display = needsOutType ? '' : 'none';

            fromAreaSelect.disabled = !needsFromArea;
            fromAreaSelect.required = needsFromArea;
            toAreaSelect.disabled = !needsToArea;
            toAreaSelect.required = needsToArea;
            outTypeSelect.disabled = !needsOutType;

            if (!needsFromArea) {
                setSelectValue(fromAreaSelect, '');
            }

            if (!needsToArea) {
                setSelectValue(toAreaSelect, '');
            }

            if (!needsOutType) {
                setSelectValue(outTypeSelect, '');
            }

            if (type === 'transfer' && fromAreaSelect.value && fromAreaSelect.value === toAreaSelect.value) {
                setSelectValue(toAreaSelect, '');
            }

            updateActiveType();
            preventSameArea();
            updateAvailableQty();
            updatePreview();
            syncingMovementFields = false;
        }

        function filterBalanceRows() {
            const search = (balanceSearch.value || '').toLowerCase();
            const status = balanceStatusFilter.value;

            if (stockBalanceTable) {
                window.jQuery.fn.dataTable.ext.search = window.jQuery.fn.dataTable.ext.search.filter(function (filterFn) {
                    return !filterFn.inventoryBalanceFilter;
                });

                const inventoryFilter = function (settings, data, dataIndex) {
                    if (settings.nTable.id !== 'stockBalanceTable') {
                        return true;
                    }

                    const row = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                    const rowSearch = row ? (row.getAttribute('data-search') || '') : '';
                    const rowStatus = row ? (row.getAttribute('data-status') || '') : '';
                    const matchesSearch = !search || rowSearch.indexOf(search) !== -1;
                    const matchesStatus = !status || rowStatus === status;

                    return matchesSearch && matchesStatus;
                };

                inventoryFilter.inventoryBalanceFilter = true;
                window.jQuery.fn.dataTable.ext.search.push(inventoryFilter);
                stockBalanceTable.draw();
                return;
            }

            document.querySelectorAll('.js-balance-row').forEach(function (row) {
                const rowSearch = row.getAttribute('data-search') || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const matchesSearch = !search || rowSearch.indexOf(search) !== -1;
                const matchesStatus = !status || rowStatus === status;
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        movementInputs.forEach(function (input) {
            input.addEventListener('change', refreshAreaFields);
        });

        movementOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                const input = option.querySelector('input[name="movement_type"]');

                if (!input || input.checked) {
                    return;
                }

                input.checked = true;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        [productSelect, fromAreaSelect, toAreaSelect, qtyInput].forEach(function (element) {
            element.addEventListener('input', refreshAreaFields);
            element.addEventListener('change', refreshAreaFields);
            element.addEventListener('keyup', refreshAreaFields);
        });

        if (window.jQuery) {
            window.jQuery(productSelect).on('change select2:select select2:clear', refreshAreaFields);
            window.jQuery(fromAreaSelect).on('change select2:select select2:clear', refreshAreaFields);
            window.jQuery(toAreaSelect).on('change select2:select select2:clear', refreshAreaFields);
        }

        const movementForm = productSelect.closest('form');
        movementForm.addEventListener('submit', function (event) {
            const type = selectedType();
            const requestedQty = Number(qtyInput.value || 0);
            const available = sourceAvailableQty();

            if ((type === 'out' || type === 'transfer') && !fromAreaSelect.value) {
                event.preventDefault();
                fromAreaSelect.focus();
                return;
            }

            if ((type === 'in' || type === 'transfer') && !toAreaSelect.value) {
                event.preventDefault();
                toAreaSelect.focus();
                return;
            }

            if (type === 'transfer' && fromAreaSelect.value === toAreaSelect.value) {
                event.preventDefault();
                setSelectValue(toAreaSelect, '');
                toAreaSelect.focus();
                return;
            }

            if (type !== 'in' && requestedQty > available) {
                event.preventDefault();
                updateAvailableQty();
                qtyInput.focus();
            }
        });

        balanceSearch.addEventListener('keyup', filterBalanceRows);
        balanceStatusFilter.addEventListener('change', filterBalanceRows);

        refreshAreaFields();
        filterBalanceRows();
    });
</script>
@endsection
