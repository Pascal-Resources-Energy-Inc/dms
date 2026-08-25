@extends('layouts.header')

@section('content')
    @php
        $pendingCount = $requests->where('approval_status', 'Pending')->count();
        $processingCount = $requests->where('approval_status', 'For Processing')->count();
        $replacingCount = $requests->where('approval_status', 'Replacing')->count();
    @endphp

    <div class="container-fluid py-4 pull-out-queue">
        <div class="queue-hero mb-4">
            <div>
                <div class="queue-eyebrow"><i class="bi bi-box-seam me-1"></i> Warehouse Operations</div>
                <h4 class="mb-1 text-white">Pull Out Replacement Requests</h4>
                <p class="mb-0">Review and release replacement stock for the {{ ucfirst($warehouse) }} warehouse.</p>
            </div>
            <div class="queue-hero-icon"><i class="bi bi-arrow-repeat"></i></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="queue-kpi is-pending"><span class="queue-kpi-icon"><i class="bi bi-hourglass-split"></i></span><div><small>Pending Review</small><strong>{{ number_format($pendingCount) }}</strong></div></div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="queue-kpi is-processing"><span class="queue-kpi-icon"><i class="bi bi-gear"></i></span><div><small>For Processing</small><strong>{{ number_format($processingCount) }}</strong></div></div>
            </div>
            <div class="col-md-4">
                <div class="queue-kpi is-replacing"><span class="queue-kpi-icon"><i class="bi bi-check2-circle"></i></span><div><small>Replacing</small><strong>{{ number_format($replacingCount) }}</strong></div></div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        @endif

        <div class="card queue-card border-0">
            <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div><h5 class="mb-1">Replacement Queue</h5><p class="text-muted small mb-0">Approve requests first, then enter the DR # to post replacement stock.</p></div>
                <span class="queue-total">{{ number_format($requests->count()) }} request{{ $requests->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="pullOutRequestsTable" class="table queue-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Product</th>
                                <th>Source Area</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th class="queue-action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                @php
                                    $statusMeta = [
                                        'Pending' => ['class' => 'is-pending', 'icon' => 'bi-hourglass-split'],
                                        'For Processing' => ['class' => 'is-processing', 'icon' => 'bi-gear'],
                                        'Replacing' => ['class' => 'is-replacing', 'icon' => 'bi-check2-circle'],
                                        'Rejected' => ['class' => 'is-rejected', 'icon' => 'bi-x-circle'],
                                    ][$request->approval_status] ?? ['class' => 'is-rejected', 'icon' => 'bi-info-circle'];
                                @endphp
                                <tr>
                                    <td data-label="Request" data-order="{{ $request->created_at ? $request->created_at->timestamp : 0 }}">
                                        <div class="fw-bold text-dark">{{ $request->reference_no ?: 'Draft request' }}</div>
                                        <small class="text-muted">{{ $request->transfer_date ? $request->transfer_date->format('M d, Y') : '-' }}</small>
                                    </td>
                                    <td data-label="Product">
                                        <div class="fw-semibold">{{ $request->item_name }}</div>
                                        <small class="text-muted">{{ $request->sku ?: 'No SKU' }}</small>
                                    </td>
                                    <td data-label="Source Area"><span class="area-chip"><i class="bi bi-geo-alt"></i>{{ $request->from_area ?: '-' }}</span></td>
                                    <td data-label="Requested">
                                        <div class="fw-bold">{{ number_format($request->replacement_qty) }}</div>
                                        <small class="text-muted">PHP {{ number_format($request->replacement_unit_cost ?? 0, 2) }}</small>
                                    </td>
                                    <td data-label="Status" data-order="{{ $request->approval_status }}">
                                        <span class="workflow-badge {{ $statusMeta['class'] }}"><i class="bi {{ $statusMeta['icon'] }}"></i>{{ $request->approval_status }}</span>
                                        @if ($request->approval_status === 'Replacing' && $request->replacement_dr_number)
                                            <small class="d-block text-muted mt-1">DR # {{ $request->replacement_dr_number }}</small>
                                        @endif
                                    </td>
                                    <td data-label="Action" class="queue-action-cell">
                                        @if ($request->approval_status === 'Pending')
                                            <form method="POST" action="{{ route('warehouse-pull-outs.review', $request->id) }}" class="queue-action-form">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="col-4"><label for="replacement_qty_{{ $request->id }}">Approve Qty</label><input id="replacement_qty_{{ $request->id }}" class="form-control form-control-sm" type="number" name="replacement_qty" min="1" max="{{ $request->replacement_qty }}" value="{{ $request->replacement_qty }}" required></div>
                                                    <div class="col-4"><label for="replacement_unit_cost_{{ $request->id }}">Unit Cost</label><input id="replacement_unit_cost_{{ $request->id }}" class="form-control form-control-sm" type="number" name="replacement_unit_cost" min="0" step="0.01" value="{{ $request->replacement_unit_cost }}"></div>
                                                    <div class="col-4"><label for="warehouse_remarks_{{ $request->id }}">Remarks</label><input id="warehouse_remarks_{{ $request->id }}" class="form-control form-control-sm" name="warehouse_remarks" placeholder="Optional"></div>
                                                </div>
                                                <div class="d-flex mt-2"><button class="btn btn-success btn-sm" type="submit" name="decision" value="For Processing"><i class="bi bi-check-lg"></i> Process</button><button class="btn btn-outline-danger btn-sm ml-2" type="submit" name="decision" value="Rejected">Reject</button></div>
                                            </form>
                                        @elseif ($request->approval_status === 'For Processing')
                                            <div class="approved-summary"><span>Approved: <b>{{ number_format($request->qty) }}</b></span><span>PHP {{ number_format($request->unit_cost ?? 0, 2) }}</span></div>
                                            <form method="POST" action="{{ route('warehouse-pull-outs.review', $request->id) }}" class="queue-action-form mt-2">
                                                @csrf
                                                <label for="replacement_dr_number_{{ $request->id }}">DR #</label>
                                                <div class="input-group input-group-sm"><input id="replacement_dr_number_{{ $request->id }}" class="form-control" name="replacement_dr_number" placeholder="Enter DR number" required><div class="input-group-append"><button class="btn btn-primary" type="submit" name="decision" value="Replacing">Replace</button></div></div>
                                            </form>
                                        @else
                                            <div class="queue-complete">
                                                @if ($request->approval_status === 'Replacing')
                                                    <i class="bi bi-check-circle-fill text-primary"></i> Replacement stock posted
                                                @else
                                                    {{ $request->warehouse_remarks ?: 'No warehouse remarks.' }}
                                                @endif
                                            </div>
                                        @endif

                                        @if ($request->pull_out_attachments)
                                            <div class="proof-files">
                                                @foreach ($request->pull_out_attachments as $file)
                                                    <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i>{{ $file['name'] }}</a>
                                                @endforeach
                                            </div>
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
    </div>

    <style>
        .pull-out-queue { color: #1f2937; }
        .queue-hero { display:flex; align-items:center; justify-content:space-between; padding:24px 26px; color:#fff; background:linear-gradient(135deg,#0f172a,#1d4ed8); border-radius:14px; box-shadow:0 14px 30px rgba(30,64,175,.18); }
        .queue-hero h4 { font-weight:800; }.queue-hero p { color:#dbeafe; }.queue-eyebrow { color:#bfdbfe; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; margin-bottom:5px; }.queue-hero-icon { display:grid; place-items:center; width:54px; height:54px; border-radius:14px; font-size:26px; background:rgba(255,255,255,.14); }
        .queue-kpi { height:100%; display:flex; align-items:center; gap:13px; padding:16px 18px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 5px 14px rgba(15,23,42,.05); }.queue-kpi-icon { display:grid; place-items:center; width:40px; height:40px; border-radius:10px; font-size:18px; }.queue-kpi small { display:block; color:#64748b; font-weight:700; }.queue-kpi strong { display:block; font-size:25px; line-height:1.1; }.queue-kpi.is-pending .queue-kpi-icon { color:#92400e;background:#fef3c7; }.queue-kpi.is-processing .queue-kpi-icon { color:#075985;background:#e0f2fe; }.queue-kpi.is-replacing .queue-kpi-icon { color:#1d4ed8;background:#dbeafe; }
        .queue-card { border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,.07); overflow:hidden; }.queue-card .card-header { padding:19px 21px; }.queue-card h5 { font-weight:800; }.queue-total { padding:6px 10px; color:#1d4ed8; font-size:12px; font-weight:800; background:#eff6ff; border-radius:999px; }
        .queue-table thead th { padding:13px 14px; color:#64748b; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; border-top:1px solid #edf0f5; border-bottom:1px solid #e5e7eb; background:#f8fafc; white-space:nowrap; }.queue-table tbody td { padding:16px 14px; border-color:#edf0f5; }.queue-table tbody tr:hover { background:#fafcff; }.queue-action-column { min-width:310px; }.area-chip { display:inline-flex; align-items:center; gap:4px; color:#475569; font-size:12px; font-weight:700; }.area-chip i { color:#2563eb; }
        .workflow-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; font-size:11px; font-weight:800; border-radius:999px; white-space:nowrap; }.workflow-badge.is-pending { color:#92400e; background:#fef3c7; }.workflow-badge.is-processing { color:#075985; background:#e0f2fe; }.workflow-badge.is-replacing { color:#1d4ed8; background:#dbeafe; }.workflow-badge.is-rejected { color:#b91c1c; background:#fee2e2; }
        .queue-action-form { min-width:280px; }.queue-action-form label { display:block; margin-bottom:3px; color:#64748b; font-size:10px; font-weight:800; text-transform:uppercase; }.approved-summary { display:flex; justify-content:space-between; padding:7px 9px; color:#075985; font-size:12px; background:#f0f9ff; border-radius:7px; }.queue-complete { color:#64748b; font-size:12px; }.proof-files { display:flex; flex-wrap:wrap; gap:5px; margin-top:9px; }.proof-files a { max-width:155px; overflow:hidden; color:#2563eb; font-size:11px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
        .pull-out-queue .dataTables_wrapper { padding-top:16px; }.pull-out-queue .dataTables_filter input, .pull-out-queue .dataTables_length select { min-height:34px; border:1px solid #dbe2ea; border-radius:7px; }.pull-out-queue .dataTables_filter { text-align:right; }.pull-out-queue .dataTables_info { color:#64748b; font-size:12px; }.pull-out-queue .dataTables_paginate .paginate_button { padding:0!important; margin-left:4px; }
        @media (max-width: 991.98px) { .queue-table { min-width:960px; }.queue-action-column { min-width:300px; }.pull-out-queue .dataTables_wrapper .row { align-items:center; } }
        @media (max-width: 767.98px) { .queue-hero { padding:20px; }.queue-hero-icon { width:46px; height:46px; font-size:22px; }.pull-out-queue .dataTables_filter { margin-top:10px; text-align:left; }.pull-out-queue .dataTables_filter input { width:190px; }.queue-card .card-header { padding:17px; } }
        @media (max-width: 575.98px) { .pull-out-queue { padding-right:12px; padding-left:12px; }.queue-hero { align-items:flex-start; gap:15px; padding:18px; }.queue-hero h4 { font-size:19px; }.queue-kpi { padding:13px; }.queue-kpi strong { font-size:22px; }.queue-card { overflow:visible; }.queue-table { min-width:0; }.queue-table thead { display:none; }.queue-table, .queue-table tbody, .queue-table tr, .queue-table td { display:block; width:100%; }.queue-table tbody tr { margin-bottom:12px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; overflow:hidden; }.queue-table tbody td { align-items:flex-start; gap:14px; padding:11px 13px; border:0; border-bottom:1px solid #f1f5f9; text-align:center; }.queue-table tbody td::before { content:attr(data-label); flex:0 0 82px; color:#64748b; font-size:10px; font-weight:800; text-align:left; text-transform:uppercase; letter-spacing:.04em; }.queue-table .queue-action-cell { display:block; text-align:left; }.queue-table .queue-action-cell::before { display:block; margin-bottom:8px; }.queue-action-form { min-width:0; }.queue-action-form .form-row { margin-right:-3px; margin-left:-3px; }.queue-action-form .form-row > .col-4 { padding-right:3px; padding-left:3px; }.approved-summary { width:100%; }.proof-files { margin-top:10px; }.pull-out-queue .dataTables_wrapper .row > div { width:100%; max-width:100%; }.pull-out-queue .dataTables_length, .pull-out-queue .dataTables_filter { margin-bottom:10px; text-align:left; }.pull-out-queue .dataTables_filter input { width:calc(100% - 115px); }.pull-out-queue .dataTables_paginate { margin-top:10px; text-align:left; }.pull-out-queue .dataTables_info { margin-bottom:8px; } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            if (!window.jQuery) return;

            async function loadDataTables() {
                if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;
                var sources = [
                    "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                    "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                    'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js'
                ];
                for (var i = 0; i < sources.length; i++) {
                    try {
                        await new Promise(function (resolve, reject) {
                            var script = document.createElement('script');
                            script.src = sources[i];
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                        if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;
                    } catch (error) {}
                }
                return false;
            }

            if (!await loadDataTables()) return;
            var table = window.jQuery('#pullOutRequestsTable');
            if (window.jQuery.fn.DataTable.isDataTable('#pullOutRequestsTable')) table.DataTable().destroy();
            table.DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[0, 'desc']],
                autoWidth: false,
                columnDefs: [{ targets: 5, orderable: false, searchable: false }],
                language: { search: 'Search requests:', lengthMenu: 'Show _MENU_ requests', emptyTable: 'No Pull Out replacement requests for this warehouse.', zeroRecords: 'No matching replacement requests found.' }
            });
        });
    </script>
@endsection
