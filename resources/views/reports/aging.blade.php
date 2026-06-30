@extends('layouts.header')

@section('header')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
<div class="container-fluid aging-page">
    <div class="aging-head">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div class="aging-title-block">
                <div class="aging-eyebrow">Inventory Report</div>
                <h4 class="mb-1">Aging Report</h4>
                <div class="text-muted">
                    {{ $isAdmin ? 'Remaining stock for all Area Distributors' : 'Remaining stock' }}
                    grouped by age as of {{ $asOf->format('M d, Y') }}.
                </div>
            </div>
            <div class="aging-actions">
                <span class="aging-asof">
                    <i class="ti ti-calendar-stats"></i>
                    {{ $asOf->format('M d, Y') }}
                </span>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="ti ti-printer"></i>
                    Print
                </button>
                <a href="{{ route('aging') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-refresh"></i>
                    Reset
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="aging-kpi">
                <div class="aging-kpi-icon is-stock"><i class="ti ti-packages"></i></div>
                <div>
                    <div class="text-muted small">Available Stock</div>
                    <div class="aging-kpi-value">{{ number_format($summary->total_qty) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="aging-kpi">
                <div class="aging-kpi-icon is-product"><i class="ti ti-barcode"></i></div>
                <div>
                    <div class="text-muted small">Products</div>
                    <div class="aging-kpi-value">{{ number_format($summary->sku_count) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="aging-kpi">
                <div class="aging-kpi-icon is-area"><i class="ti ti-map-pin"></i></div>
                <div>
                    <div class="text-muted small">{{ $isAdmin ? 'Area Distributors' : 'Areas' }}</div>
                    <div class="aging-kpi-value">{{ number_format($isAdmin ? $summary->distributor_count : $summary->area_count) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="aging-kpi aging-kpi-warn">
                <div class="aging-kpi-icon is-age"><i class="ti ti-hourglass-high"></i></div>
                <div>
                    <div class="text-muted small">Oldest Stock</div>
                    <div class="aging-kpi-value">{{ number_format($summary->oldest_days) }} days</div>
                </div>
            </div>
        </div>
    </div>

    <div class="aging-card card border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('aging') }}" class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-6">
                    <label class="form-label"><i class="ti ti-calendar"></i> As Of</label>
                    <input type="date" name="as_of" class="form-control" value="{{ request('as_of', $asOf->format('Y-m-d')) }}">
                </div>
                @if($isAdmin)
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label"><i class="ti ti-building-store"></i> Area Distributor</label>
                        <select name="distributor" class="form-control">
                            <option value="">All Area Distributors</option>
                            @foreach($distributorOptions as $distributor)
                                <option value="{{ $distributor->id }}" {{ (string) request('distributor') === (string) $distributor->id ? 'selected' : '' }}>
                                    {{ $distributor->business_name ?: $distributor->name ?: $distributor->store_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-lg-3 col-md-6">
                    <label class="form-label"><i class="ti ti-package"></i> Product</label>
                    <select name="product" class="form-control">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product }}" {{ request('product') == $product ? 'selected' : '' }}>{{ $product }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label"><i class="ti ti-map-2"></i> Area</label>
                    <select name="area" class="form-control">
                        <option value="">All Areas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label"><i class="ti ti-clock"></i> Age Bucket</label>
                    <select name="bucket" class="form-control">
                        <option value="">All Buckets</option>
                        @foreach(['0-30', '31-60', '61-90', '90+'] as $bucket)
                            <option value="{{ $bucket }}" {{ request('bucket') == $bucket ? 'selected' : '' }}>{{ $bucket }} days</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-12">
                    <button class="btn btn-primary w-100">
                        <i class="ti ti-filter"></i>
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach($bucketTotals as $bucket => $qty)
            @php
                $bucketClass = $bucket === '90+' ? 'is-critical' : ($bucket === '61-90' ? 'is-watch' : 'is-good');
            @endphp
            <div class="col-lg-3 col-md-6">
                <div class="aging-bucket {{ $bucketClass }}">
                    <div>
                        <div class="aging-bucket-label">{{ $bucket }} days</div>
                        <div class="aging-bucket-copy">Remaining stock</div>
                    </div>
                    <div class="aging-bucket-value">
                        <strong>{{ number_format($qty) }}</strong>
                        <span style="width: {{ $summary->total_qty > 0 ? min(100, max(0, ($qty / $summary->total_qty) * 100)) : 0 }}%;"></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="aging-card card border-0">
        <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div>
                <h5 class="mb-0">Aged Inventory</h5>
                <div class="small text-muted">{{ $batches->count() }} batch{{ $batches->count() == 1 ? '' : 'es' }} with remaining stock</div>
            </div>
            <div class="aging-formula mt-2 mt-lg-0">
                <i class="ti ti-stack-pop"></i>
                FIFO after completed AD, item ordered, and stock out
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table aging-table align-middle mb-0" id="agingReportTable">
                    <thead>
                        <tr>
                            @if($isAdmin)
                                <th>Area Distributor</th>
                            @endif
                            <th>Product</th>
                            <th>Area</th>
                            <th>Source Date</th>
                            <th>Source</th>
                            <th class="text-end">Age</th>
                            <th class="text-end">Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            <tr>
                                @if($isAdmin)
                                    <td>
                                        <div class="fw-semibold">{{ $batch->distributor_name }}</div>
                                        <div class="small text-muted">AD #{{ $batch->distributor_id }}</div>
                                    </td>
                                @endif
                                <td>
                                    <div class="fw-semibold">{{ $batch->product_name }}</div>
                                    <div class="small text-muted">{{ $batch->sku ?: 'No SKU' }}</div>
                                </td>
                                <td>{{ $batch->area }}</td>
                                <td>{{ $batch->date->format('M d, Y') }}</td>
                                <td><span class="aging-source">{{ $batch->source }}</span></td>
                                <td class="text-end">{{ number_format($batch->age_days) }} days</td>
                                <td class="text-end">
                                    <span class="aging-qty">{{ number_format($batch->qty) }}</span>
                                </td>
                                <td>
                                    @if($batch->bucket === '90+')
                                        <span class="aging-status status-critical"><i class="ti ti-alert-triangle"></i> 90+ days</span>
                                    @elseif($batch->bucket === '61-90')
                                        <span class="aging-status status-watch"><i class="ti ti-alert-circle"></i> 61-90 days</span>
                                    @else
                                        <span class="aging-status status-good"><i class="ti ti-circle-check"></i> {{ $batch->bucket }} days</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-center text-muted py-4">No aged inventory found.</td>
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
    .aging-page {
        padding-top: 16px;
        padding-bottom: 28px;
    }

    .aging-head,
    .aging-card,
    .aging-kpi,
    .aging-bucket {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .06);
    }

    .aging-head {
        padding: 20px 22px;
        margin-bottom: 16px;
        border-left: 5px solid #0f766e;
    }

    .aging-title-block {
        min-width: 0;
    }

    .aging-eyebrow {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .aging-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
    }

    .aging-actions .btn,
    .aging-card .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        border-radius: 8px;
        font-weight: 700;
    }

    .aging-asof {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 38px;
        color: #0f766e;
        background: #ecfdf5;
        border: 1px solid #99f6e4;
        border-radius: 8px;
        padding: 7px 11px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .aging-kpi {
        padding: 16px;
        min-height: 94px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .aging-kpi-value {
        margin-top: 6px;
        color: #111827;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.1;
    }

    .aging-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 24px;
    }

    .aging-kpi-icon.is-stock {
        color: #0f766e;
        background: #ccfbf1;
    }

    .aging-kpi-icon.is-product {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .aging-kpi-icon.is-area {
        color: #7c3aed;
        background: #ede9fe;
    }

    .aging-kpi-icon.is-age {
        color: #b45309;
        background: #fef3c7;
    }

    .aging-kpi-warn {
        border-left: 4px solid #f59e0b;
    }

    .aging-card .form-label {
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 6px;
    }

    .aging-card .form-control {
        min-height: 40px;
        border-color: #dbe2ea;
        border-radius: 8px;
        color: #111827;
    }

    .aging-bucket {
        min-height: 82px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .aging-bucket strong {
        font-size: 24px;
        color: #111827;
    }

    .aging-bucket-label {
        font-weight: 800;
        color: #111827;
    }

    .aging-bucket-copy {
        color: #64748b;
        font-size: 12px;
    }

    .aging-bucket-value {
        width: 110px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    .aging-bucket-value span {
        display: block;
        max-width: 100%;
        height: 5px;
        border-radius: 999px;
        background: currentColor;
        opacity: .55;
    }

    .aging-bucket.is-good {
        border-left: 4px solid #16a34a;
        color: #16a34a;
    }

    .aging-bucket.is-watch {
        border-left: 4px solid #f59e0b;
        color: #f59e0b;
    }

    .aging-bucket.is-critical {
        border-left: 4px solid #dc2626;
        color: #dc2626;
    }

    .aging-formula {
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .aging-table th {
        background: #f3f6fa;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .aging-table td {
        border-color: #edf0f5;
        vertical-align: middle;
    }

    .aging-table td:first-child {
        min-width: 240px;
    }

    .aging-table tbody tr:hover {
        background: #f8fafc;
    }

    .aging-source,
    .aging-status,
    .aging-qty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        gap: 5px;
    }

    .aging-source {
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .aging-qty {
        min-width: 74px;
        color: #0f766e;
        background: #ccfbf1;
        border: 1px solid #99f6e4;
    }

    .aging-status.status-good {
        color: #166534;
        background: #dcfce7;
    }

    .aging-status.status-watch {
        color: #92400e;
        background: #fef3c7;
    }

    .aging-status.status-critical {
        color: #991b1b;
        background: #fee2e2;
    }

    .dataTables_wrapper {
        padding: 14px;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border: 1px solid #dbe2ea;
        border-radius: 7px;
        min-height: 36px;
        padding: 4px 8px;
    }

    @media (max-width: 575px) {
        .aging-head {
            padding: 16px;
        }

        .aging-actions,
        .aging-actions .btn,
        .aging-asof {
            width: 100%;
        }

        .aging-kpi-value {
            font-size: 24px;
        }

        .aging-bucket-value {
            width: 86px;
        }
    }

    @media print {
        .aging-actions,
        .aging-card form,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_paginate,
        .dataTables_info {
            display: none !important;
        }

        .aging-page {
            padding: 0;
        }

        .aging-head,
        .aging-card,
        .aging-kpi,
        .aging-bucket {
            box-shadow: none;
        }
    }
</style>

@section('javascript')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && window.jQuery.fn.DataTable) {
            window.jQuery('#agingReportTable').DataTable({
                pageLength: 25,
                order: [[{{ $isAdmin ? 5 : 4 }}, 'desc']],
                autoWidth: false,
                language: {
                    search: 'Search aging:',
                    lengthMenu: 'Show _MENU_ batches',
                    emptyTable: 'No aged inventory found.'
                }
            });
        }
    });
</script>
@endsection
