@extends('layouts.header')

@section('content')
    <div class="container-fluid pull-out-queue">
        <div class="queue-hero mb-4">
            <div>
                <div class="queue-eyebrow"><i class="bi bi-box-seam me-1"></i> Warehouse Operations</div>
                <h4 class="mb-1 text-white">Pull Out Replacement Requests</h4>
                <p class="mb-0">Review and release replacement stock for the {{ ucfirst($warehouse) }} warehouse.</p>
            </div>
            <div class="queue-hero-icon"><i class="bi bi-arrow-repeat"></i></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="queue-kpi is-pending"><span class="queue-kpi-icon"><i class="bi bi-hourglass-split"></i></span><div><small>Pending Review</small><strong>{{ number_format($summary['pending']) }}</strong></div></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="queue-kpi is-processing"><span class="queue-kpi-icon"><i class="bi bi-gear"></i></span><div><small>For Processing</small><strong>{{ number_format($summary['processing']) }}</strong></div></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="queue-kpi is-replacing"><span class="queue-kpi-icon"><i class="bi bi-check2-circle"></i></span><div><small>Replacing</small><strong>{{ number_format($summary['replacing']) }}</strong></div></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="queue-kpi is-replacing"><span class="queue-kpi-icon"><i class="bi bi-check2-circle"></i></span><div><small>AD Confirmed</small><strong>{{ number_format($summary['ad_confirmed']) }}</strong></div></div>
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
                <span class="queue-total">{{ number_format($summary['total']) }} request{{ $summary['total'] === 1 ? '' : 's' }}</span>
            </div>
            <div class="card-body pt-0">
                <div class="queue-table-shell">
                    <table id="pullOutRequestsTable" class="table queue-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Area Distributor</th>
                                <th>Products &amp; Quantities</th>
                                <th>Source Areas</th>
                                <th>RIS Number</th>
                                <th>RIS Date</th>
                                <th>Warehouse</th>
                                <th>Product Status</th>
                                <th>Pending Aging</th>
                                <th>Processing Aging</th>
                                <th>Replacing Aging</th>
                                <th>Attachments</th>
                                <th>Remarks</th>
                                <th class="queue-action-column">Item Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests->take(0) as $request)
                                <tr>
                                    <td data-label="Date" data-order="{{ $request->created_at ? $request->created_at->timestamp : 0 }}">{{ $request->transfer_date ? $request->transfer_date->format('M d, Y') : '—' }}</td>
                                    <td data-label="Reference"><strong>{{ $request->reference_no ?: 'Draft request' }}</strong></td>
                                    <td data-label="Area Distributor">
                                        <div class="fw-semibold">{{ $request->ad_name ?: 'Area Distributor' }}</div>
                                    </td>
                                    <td data-label="Products & Quantities">
                                        <div class="grouped-products">
                                            @foreach ($request->groupedItems as $item)
                                                <div class="grouped-product-row">
                                                    <div><strong>{{ $item->item_name }}</strong><small>{{ $item->sku ?: 'No SKU' }}</small></div>
                                                    <span class="qty-pill">{{ number_format($item->replacement_qty ?: $item->qty) }} pcs</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="group-total">Total requested: {{ number_format($request->requestedQtyTotal) }} pcs</small>
                                    </td>
                                    <td data-label="Source Areas">
                                        @foreach ($request->groupedItems->pluck('from_area')->filter()->unique() as $area)
                                            <span class="area-chip"><i class="bi bi-geo-alt"></i>{{ $area }}</span>
                                        @endforeach
                                    </td>
                                    <td data-label="RIS Number">{{ $request->ris_number ?: '—' }}</td>
                                    <td data-label="RIS Date">{{ $request->ris_date ? $request->ris_date->format('M d, Y') : '—' }}</td>
                                    <td data-label="Warehouse">{{ ucfirst($request->warehouse ?: $warehouse) }}</td>
                                    <td data-label="Product Status">
                                        <div class="product-workflow-list">
                                            @foreach ($request->groupedItems as $item)
                                                @php
                                                    $itemStatus = $item->approval_status ?: 'Pending';
                                                    $itemStatusMeta = [
                                                        'Pending' => ['class' => 'is-pending', 'icon' => 'bi-hourglass-split'],
                                                        'For Processing' => ['class' => 'is-processing', 'icon' => 'bi-gear'],
                                                        'Replacing' => ['class' => 'is-replacing', 'icon' => 'bi-check2-circle'],
                                                        'Rejected' => ['class' => 'is-rejected', 'icon' => 'bi-x-circle'],
                                                    ][$itemStatus] ?? ['class' => 'is-rejected', 'icon' => 'bi-info-circle'];
                                                @endphp
                                                <div class="product-workflow-row">
                                                    <small>{{ $item->item_name }}</small>
                                                    <span class="workflow-badge {{ $itemStatusMeta['class'] }}"><i class="bi {{ $itemStatusMeta['icon'] }}"></i>{{ $itemStatus }}</span>
                                                    @if ($item->approval_status === 'Replacing' && $item->replacement_dr_number)
                                                        <small class="product-workflow-detail">DR # {{ $item->replacement_dr_number }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    @foreach (['Pending', 'For Processing', 'Replacing'] as $agingStatus)
                                        <td data-label="{{ $agingStatus === 'For Processing' ? 'Processing' : $agingStatus }} Aging">
                                            <div class="product-workflow-list">
                                                @forelse ($request->groupedItems->where('approval_status', $agingStatus) as $item)
                                                    @php
                                                        $statusStartedAt = $agingStatus === 'Pending'
                                                            ? $item->created_at
                                                            : ($item->reviewed_at ?: $item->created_at);
                                                        $agingDays = $statusStartedAt
                                                            ? \Carbon\Carbon::parse($statusStartedAt)->startOfDay()->diffInDays(now()->startOfDay())
                                                            : 0;
                                                    @endphp
                                                    <div class="product-workflow-row">
                                                        <small>{{ $item->item_name }}</small>
                                                        <span class="aging-day">{{ $agingDays }} Day(s)</span>
                                                    </div>
                                                @empty
                                                    <span class="text-muted">—</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    @endforeach
                                    <td data-label="Attachments">
                                        @forelse ($request->pull_out_attachments ?: [] as $file)
                                            @php
                                                $attachmentPath = is_array($file) ? ($file['path'] ?? null) : $file;
                                                $attachmentName = is_array($file) ? ($file['name'] ?? basename($attachmentPath)) : basename($attachmentPath);
                                            @endphp
                                            @if ($attachmentPath)
                                                <a class="d-block text-primary small text-truncate" style="max-width: 160px;" href="{{ asset($attachmentPath) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> {{ $attachmentName }}</a>
                                            @endif
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </td>
                                    <td data-label="Remarks">{{ $request->warehouse_remarks ?: ($request->remarks ?: '—') }}</td>
                                    <td data-label="Item Actions" class="queue-action-cell">
                                        @foreach ($request->groupedItems as $item)
                                            <div class="item-action-card">
                                                <div class="item-action-title">{{ $item->item_name }} <span>{{ number_format($item->replacement_qty ?: $item->qty) }} pcs</span></div>
                                        @if ($item->approval_status === 'Pending')
                                            <form method="POST" action="{{ route('warehouse-pull-outs.review', $item->id) }}" class="queue-action-form">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="col-4"><label for="replacement_qty_{{ $item->id }}">Approve Qty</label><input id="replacement_qty_{{ $item->id }}" class="form-control form-control-sm js-approved-qty" type="number" name="replacement_qty" min="1" max="{{ $item->replacement_qty }}" value="{{ $item->replacement_qty }}" data-requested-qty="{{ $item->replacement_qty }}" required></div>
                                                    <div class="col-4"><label for="replacement_unit_cost_{{ $item->id }}">Unit Cost</label><input id="replacement_unit_cost_{{ $item->id }}" class="form-control form-control-sm" type="number" name="replacement_unit_cost" min="0" step="0.01" value="{{ $item->replacement_unit_cost }}"></div>
                                                    <div class="col-4"><label for="warehouse_remarks_{{ $item->id }}" class="js-remarks-label">Remarks</label><input id="warehouse_remarks_{{ $item->id }}" class="form-control form-control-sm js-warehouse-remarks" name="warehouse_remarks" placeholder="Optional"><small class="quantity-change-note js-quantity-change-note d-none">Required when quantity changes.</small></div>
                                                </div>
                                                <div class="d-flex mt-2"><button class="btn btn-success btn-sm" type="submit" name="decision" value="For Processing"><i class="bi bi-check-lg"></i> Process</button><button class="btn btn-outline-danger btn-sm ml-2" type="submit" name="decision" value="Rejected">Reject</button></div>
                                            </form>
                                        @elseif ($item->approval_status === 'For Processing')
                                            <div class="approved-summary"><span>Approved: <b>{{ number_format($item->qty) }}</b></span><span>PHP {{ number_format($item->unit_cost ?? 0, 2) }}</span></div>
                                            <form method="POST" action="{{ route('warehouse-pull-outs.review', $item->id) }}" class="queue-action-form mt-2">
                                                @csrf
                                                <label for="replacement_dr_number_{{ $item->id }}">DR #</label>
                                                <div class="input-group input-group-sm"><input id="replacement_dr_number_{{ $item->id }}" class="form-control" name="replacement_dr_number" placeholder="Enter DR number" required><div class="input-group-append"><button class="btn btn-primary" type="submit" name="decision" value="Replacing">Replace</button></div></div>
                                            </form>
                                        @else
                                            <div class="queue-complete">
                                                @if ($item->approval_status === 'Replacing')
                                                    <i class="bi bi-check-circle-fill text-primary"></i> Replacement stock posted
                                                @else
                                                    {{ $item->warehouse_remarks ?: 'No warehouse remarks.' }}
                                                @endif
                                            </div>
                                        @endif
                                            </div>
                                        @endforeach
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
        .queue-card { border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,.07); overflow:hidden; }.queue-card .card-header { padding:19px 21px; }.queue-card h5 { font-weight:800; }.queue-total { padding:6px 10px; color:#1d4ed8; font-size:12px; font-weight:800; background:#eff6ff; border-radius:999px; }.dataTables_scrollBody { border-bottom:0!important; }.queue-table { min-width:2230px; table-layout:fixed; }.queue-table thead th { padding:13px 14px; color:#526b8d; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; border-top:0; border-bottom:1px solid #dce5ef; background:#f8fafc; white-space:nowrap; }.queue-table tbody td { padding:13px 14px; border-color:#edf0f5; vertical-align:middle; background:#fff; }.queue-table tbody tr:hover td { background:#fbfdff; }.queue-table th:nth-child(1), .queue-table td:nth-child(1) { width:96px!important; }.queue-table th:nth-child(2), .queue-table td:nth-child(2) { width:154px!important; }.queue-table th:nth-child(3), .queue-table td:nth-child(3) { width:155px!important; }.queue-table th:nth-child(4), .queue-table td:nth-child(4) { width:280px!important; }.queue-table th:nth-child(5), .queue-table td:nth-child(5) { width:135px!important; }.queue-table th:nth-child(6), .queue-table td:nth-child(6) { width:120px!important; }.queue-table th:nth-child(7), .queue-table td:nth-child(7) { width:110px!important; }.queue-table th:nth-child(8), .queue-table td:nth-child(8) { width:125px!important; }.queue-table th:nth-child(9), .queue-table td:nth-child(9), .queue-table th:nth-child(10), .queue-table td:nth-child(10), .queue-table th:nth-child(11), .queue-table td:nth-child(11), .queue-table th:nth-child(12), .queue-table td:nth-child(12) { width:180px!important; }.queue-table th:nth-child(13), .queue-table td:nth-child(13) { width:165px!important; }.queue-table th:nth-child(14), .queue-table td:nth-child(14) { width:180px!important; }.queue-table th:nth-child(15), .queue-table td:nth-child(15) { width:220px!important; }.queue-table td:nth-child(1), .queue-table td:nth-child(2), .queue-table td:nth-child(6), .queue-table td:nth-child(7), .queue-table td:nth-child(8) { font-weight:700; overflow-wrap:anywhere; }.queue-action-column { min-width:205px; }.area-chip { display:inline-flex; align-items:center; gap:4px; margin:2px 4px 2px 0; color:#475569; font-size:12px; font-weight:700; }.area-chip i { color:#2563eb; }.aging-day { display:inline-flex; padding:4px 8px; color:#075985; font-size:11px; font-weight:800; white-space:nowrap; border-radius:999px; background:#e0f2fe; }.grouped-products { min-width:250px; }.grouped-product-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 0; border-bottom:1px solid #edf0f5; }.grouped-product-row:last-child { border-bottom:0; }.grouped-product-row strong, .grouped-product-row small { display:block; }.grouped-product-row small { color:#64748b; font-size:11px; }.qty-pill { padding:3px 7px; color:#075985; font-size:11px; font-weight:800; white-space:nowrap; border-radius:999px; background:#e0f2fe; }.group-total { display:block; margin-top:7px; color:#475569; font-weight:700; }
        .product-workflow-list { min-width:175px; }.product-workflow-row { display:flex; align-items:center; flex-wrap:wrap; gap:5px; padding:7px 0; border-bottom:1px solid #edf0f5; }.product-workflow-row:last-child { border-bottom:0; }.product-workflow-row > small:first-child { flex:1 0 100%; color:#64748b; font-size:10px; font-weight:700; }.aging-product-row > small:first-child { display:flex; align-items:center; justify-content:space-between; gap:7px; }.aging-product-row > small span { color:#94a3b8; font-size:10px; font-weight:800; white-space:nowrap; }.product-workflow-detail { color:#64748b; font-size:10px; }.product-workflow-detail.is-confirmed-qty { color:#15803d; font-weight:800; }.workflow-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; font-size:11px; font-weight:800; border-radius:999px; white-space:nowrap; }.workflow-badge.is-pending { color:#92400e; background:#fef3c7; }.workflow-badge.is-processing { color:#075985; background:#e0f2fe; }.workflow-badge.is-replacing { color:#1d4ed8; background:#dbeafe; }.workflow-badge.is-confirmed { color:#166534; background:#dcfce7; }.workflow-badge.is-rejected { color:#b91c1c; background:#fee2e2; }
        .queue-action-form { min-width:280px; }.item-action-card { padding:10px; margin-bottom:8px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; }.item-action-card:last-child { margin-bottom:0; }.item-action-disclosure { min-width:190px; margin-bottom:7px; border:1px solid #dbe5ef; border-radius:8px; background:#fff; }.item-action-disclosure:last-child { margin-bottom:0; }.item-action-disclosure summary { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 10px; color:#2563eb; font-size:11px; font-weight:800; cursor:pointer; list-style:none; }.item-action-disclosure summary::-webkit-details-marker { display:none; }.item-action-disclosure summary::after { content:'+'; font-size:15px; line-height:1; }.item-action-disclosure[open] summary { border-bottom:1px solid #e5e7eb; }.item-action-disclosure[open] summary::after { content:'–'; }.item-action-disclosure .item-action-card { margin:0; border:0; border-radius:0; }.item-action-title { display:flex; justify-content:space-between; gap:10px; margin-bottom:8px; color:#1e293b; font-size:12px; font-weight:800; }.item-action-title span { color:#075985; white-space:nowrap; }.queue-action-form label { display:block; margin-bottom:3px; color:#64748b; font-size:10px; font-weight:800; text-transform:uppercase; }.quantity-change-note { display:block; margin-top:4px; color:#b45309; font-size:10px; font-weight:700; }.approved-summary { display:flex; justify-content:space-between; padding:7px 9px; color:#075985; font-size:12px; background:#f0f9ff; border-radius:7px; }.queue-complete { color:#64748b; font-size:12px; }.proof-files { display:flex; flex-wrap:wrap; gap:5px; margin-top:9px; }.proof-files a { max-width:155px; overflow:hidden; color:#2563eb; font-size:11px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
        .pull-out-queue .dataTables_wrapper { padding-top:16px; }.pull-out-queue .dataTables_length { float:left!important; }.pull-out-queue .dataTables_filter { float:right!important; margin:0 0 16px!important; text-align:right; }.pull-out-queue .dataTables_filter input, .pull-out-queue .dataTables_length select { min-height:36px; padding:6px 10px; border:1px solid #dbe2ea; border-radius:8px; background:#fff; }.pull-out-queue .dataTables_wrapper::after { display:block; clear:both; content:''; }.pull-out-queue .dataTables_info { float:left!important; padding-top:14px; color:#64748b; font-size:12px; }.pull-out-queue .dataTables_paginate { float:right!important; padding-top:10px; text-align:right!important; }.pull-out-queue .dataTables_paginate .paginate_button { min-width:32px; padding:6px 9px!important; margin-left:4px; color:#2563eb!important; border:1px solid #dbeafe!important; border-radius:7px!important; background:#eff6ff!important; }.pull-out-queue .dataTables_paginate .paginate_button.current { color:#fff!important; border-color:#2563eb!important; background:#2563eb!important; }
        @media (max-width: 991.98px) { .queue-table { min-width:1680px; }.queue-action-column { min-width:300px; }.pull-out-queue .dataTables_wrapper .row { align-items:center; } }
        @media (max-width: 767.98px) { .queue-hero { padding:20px; }.queue-hero-icon { width:46px; height:46px; font-size:22px; }.pull-out-queue .dataTables_length, .pull-out-queue .dataTables_filter, .pull-out-queue .dataTables_info, .pull-out-queue .dataTables_paginate { float:none!important; text-align:left!important; }.pull-out-queue .dataTables_filter { margin-top:10px!important; }.pull-out-queue .dataTables_filter input { width:190px; }.queue-card .card-header { padding:17px; } }
        @media (max-width: 575.98px) { .pull-out-queue { padding-right:12px; padding-left:12px; }.queue-hero { align-items:flex-start; gap:15px; padding:18px; }.queue-hero h4 { font-size:19px; }.queue-kpi { padding:13px; }.queue-kpi strong { font-size:22px; }.queue-card { overflow:visible; }.queue-table { min-width:0; }.queue-table thead { display:none; }.queue-table, .queue-table tbody, .queue-table tr, .queue-table td { display:block; width:100%; }.queue-table tbody tr { margin-bottom:12px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; overflow:hidden; }.queue-table tbody td { align-items:flex-start; gap:14px; padding:11px 13px; border:0; border-bottom:1px solid #f1f5f9; text-align:center; }.queue-table tbody td::before { content:attr(data-label); flex:0 0 82px; color:#64748b; font-size:10px; font-weight:800; text-align:left; text-transform:uppercase; letter-spacing:.04em; }.queue-table .queue-action-cell { display:block; text-align:left; }.queue-table .queue-action-cell::before { display:block; margin-bottom:8px; }.queue-action-form { min-width:0; }.queue-action-form .form-row { margin-right:-3px; margin-left:-3px; }.queue-action-form .form-row > .col-4 { padding-right:3px; padding-left:3px; }.approved-summary { width:100%; }.proof-files { margin-top:10px; }.pull-out-queue .dataTables_wrapper .row > div { width:100%; max-width:100%; }.pull-out-queue .dataTables_length, .pull-out-queue .dataTables_filter { margin-bottom:10px; text-align:left; }.pull-out-queue .dataTables_filter input { width:calc(100% - 115px); }.pull-out-queue .dataTables_paginate { margin-top:10px; text-align:left; }.pull-out-queue .dataTables_info { margin-bottom:8px; } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            function syncChangedQuantityRemarks(quantityInput) {
                var form = quantityInput.closest('form');
                if (!form) return;

                var remarksInput = form.querySelector('.js-warehouse-remarks');
                var remarksLabel = form.querySelector('.js-remarks-label');
                var changeNote = form.querySelector('.js-quantity-change-note');
                var hasChanged = Number(quantityInput.value) !== Number(quantityInput.dataset.requestedQty);

                if (remarksInput) remarksInput.required = hasChanged;
                if (remarksLabel) remarksLabel.textContent = hasChanged ? 'Remarks *' : 'Remarks';
                if (changeNote) changeNote.classList.toggle('d-none', !hasChanged);
            }

            document.addEventListener('input', function (event) {
                if (event.target.matches('.js-approved-qty')) syncChangedQuantityRemarks(event.target);
            });
            document.querySelectorAll('.js-approved-qty').forEach(syncChangedQuantityRemarks);

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
            var csrfToken = @json(csrf_token());
            var appUrl = @json(url('/'));
            var escapeHtml = function (value) {
                return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character];
                });
            };
            var productRows = function (items) {
                return '<div class="grouped-products">' + items.map(function (item) {
                    return '<div class="grouped-product-row"><div><strong>' + escapeHtml(item.name) + '</strong><small>' + escapeHtml(item.sku) + '</small></div><span class="qty-pill">' + Number(item.requested_qty).toLocaleString() + ' pcs</span></div>';
                }).join('') + '</div>';
            };
            var workflowRows = function (items) {
                var statusMeta = {
                    'Pending': ['is-pending', 'bi-hourglass-split'],
                    'For Processing': ['is-processing', 'bi-gear'],
                    'Replacing': ['is-replacing', 'bi-check2-circle'],
                    'AD Confirmed': ['is-confirmed', 'bi-check-circle-fill'],
                    'Rejected': ['is-rejected', 'bi-x-circle']
                };
                return '<div class="product-workflow-list">' + items.map(function (item) {
                    var status = item.status || 'Pending';
                    var meta = statusMeta[status] || ['is-rejected', 'bi-info-circle'];
                    var dr = status === 'Replacing' && item.dr_number ? '<small class="product-workflow-detail">DR # ' + escapeHtml(item.dr_number) + '</small>' : '';
                    var confirmedQty = status === 'AD Confirmed' ? '<small class="product-workflow-detail is-confirmed-qty">Confirmed: ' + Number(item.ad_confirmed_qty || 0).toLocaleString() + ' pcs</small>' : '';
                    return '<div class="product-workflow-row"><small>' + escapeHtml(item.name) + '</small><span class="workflow-badge ' + meta[0] + '"><i class="bi ' + meta[1] + '"></i>' + escapeHtml(status) + '</span>' + dr + confirmedQty + '</div>';
                }).join('') + '</div>';
            };
            var agingRows = function (items, status) {
                var matches = items.filter(function (item) {
                    return item.aging && item.aging[status] !== null && item.aging[status] !== undefined;
                });
                if (!matches.length) return '<span class="text-muted">—</span>';
                return '<div class="product-workflow-list">' + matches.map(function (item) {
                    var days = Number(item.aging[status]);
                    days = Number.isFinite(days) ? Math.max(0, days) : 0;
                    return '<div class="product-workflow-row aging-product-row"><small>' + escapeHtml(item.name) + '<span>' + Number(item.requested_qty).toLocaleString() + ' pcs</span></small><span class="aging-day">' + days + ' Day(s)</span></div>';
                }).join('') + '</div>';
            };
            var actionRows = function (items) {
                return items.map(function (item) {
                    var displayQuantity = item.status === 'AD Confirmed'
                        ? Number(item.ad_confirmed_qty || 0)
                        : Number(item.requested_qty || 0);
                    var heading = '<div class="item-action-title">' + escapeHtml(item.name) + '<span>' + displayQuantity.toLocaleString() + ' pcs</span></div>';
                    var action = '';
                    if (item.status === 'Pending') {
                        action = '<form method="POST" action="' + escapeHtml(item.review_url) + '" class="queue-action-form">'
                            + '<input type="hidden" name="_token" value="' + csrfToken + '"><div class="form-row">'
                            + '<div class="col-4"><label>Approve Qty</label><input class="form-control form-control-sm js-approved-qty" type="number" name="replacement_qty" min="1" max="' + item.requested_qty + '" value="' + item.requested_qty + '" data-requested-qty="' + item.requested_qty + '" required></div>'
                            + '<div class="col-4"><label>Unit Cost</label><input class="form-control form-control-sm" type="number" name="replacement_unit_cost" min="0" step="0.01" value="' + (item.replacement_unit_cost || '') + '"></div>'
                            + '<div class="col-4"><label class="js-remarks-label">Remarks</label><input class="form-control form-control-sm js-warehouse-remarks" name="warehouse_remarks" placeholder="Optional"><small class="quantity-change-note js-quantity-change-note d-none">Required when quantity changes.</small></div>'
                            + '</div><div class="d-flex mt-2"><button class="btn btn-success btn-sm" type="submit" name="decision" value="For Processing"><i class="bi bi-check-lg"></i> Process</button><button class="btn btn-outline-danger btn-sm ml-2" type="submit" name="decision" value="Rejected">Reject</button></div></form>';
                    } else if (item.status === 'For Processing') {
                        action = '<div class="approved-summary"><span>Approved: <b>' + Number(item.approved_qty).toLocaleString() + '</b></span><span>PHP ' + Number(item.unit_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span></div>'
                            + '<form method="POST" action="' + escapeHtml(item.review_url) + '" class="queue-action-form mt-2"><input type="hidden" name="_token" value="' + csrfToken + '"><label>DR #</label><div class="input-group input-group-sm"><input class="form-control" name="replacement_dr_number" placeholder="Enter DR number" required><div class="input-group-append"><button class="btn btn-primary" type="submit" name="decision" value="Replacing">Replace</button></div></div></form>';
                    } else {
                        action = '<div class="queue-complete">' + (item.status === 'Replacing' ? '<i class="bi bi-check-circle-fill text-primary"></i> Replacement stock posted' : escapeHtml(item.remarks || 'No warehouse remarks.')) + '</div>';
                    }
                    return '<details class="item-action-disclosure"><summary>' + escapeHtml(item.name) + '<span>' + displayQuantity.toLocaleString() + ' pcs</span></summary><div class="item-action-card">' + heading + action + '</div></details>';
                }).join('');
            };
            var dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                searchDelay: 350,
                scrollX: true,
                scrollCollapse: true,
                pageLength: 25,
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                order: [[0, 'desc']],
                autoWidth: false,
                ajax: { url: @json(route('warehouse-pull-outs.data')) },
                columns: [
                    { data: 'date', name: 'sort_date' },
                    { data: 'reference' },
                    { data: 'ad_name' },
                    { data: 'items', orderable: false, searchable: true, render: function (items, type, row) { return productRows(items) + '<small class="group-total">Total requested: ' + Number(row.requested_qty_total).toLocaleString() + ' pcs</small>'; } },
                    { data: 'source_areas', orderable: false, render: function (areas) { return areas.length ? areas.map(function (area) { return '<span class="area-chip"><i class="bi bi-geo-alt"></i>' + escapeHtml(area) + '</span>'; }).join('') : '—'; } },
                    { data: 'ris_number' },
                    { data: 'ris_date' },
                    { data: 'warehouse' },
                    { data: 'items', orderable: false, searchable: false, render: workflowRows },
                    { data: 'items', orderable: false, searchable: false, render: function (items) { return agingRows(items, 'Pending'); } },
                    { data: 'items', orderable: false, searchable: false, render: function (items) { return agingRows(items, 'For Processing'); } },
                    { data: 'items', orderable: false, searchable: false, render: function (items) { return agingRows(items, 'Replacing'); } },
                    { data: 'attachments', orderable: false, searchable: false, render: function (files) { return files.length ? files.map(function (file) { var path = typeof file === 'object' ? file.path : file; var name = typeof file === 'object' ? (file.name || path) : path; return path ? '<a class="d-block text-primary small text-truncate" style="max-width:160px" href="' + appUrl + '/' + String(path).replace(/^\/+/, '') + '" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> ' + escapeHtml(name) + '</a>' : ''; }).join('') : '<span class="text-muted">—</span>'; } },
                    { data: 'remarks' },
                    { data: 'items', orderable: false, searchable: false, render: actionRows }
                ],
                createdRow: function (row) {
                    ['Date', 'Reference', 'Area Distributor', 'Products & Quantities', 'Source Areas', 'RIS Number', 'RIS Date', 'Warehouse', 'Product Status', 'Pending Aging', 'Processing Aging', 'Replacing Aging', 'Attachments', 'Remarks', 'Item Actions'].forEach(function (label, index) {
                        row.children[index].setAttribute('data-label', label);
                    });
                },
                language: { processing: '<i class="bi bi-arrow-repeat me-1"></i> Loading replacement requests...', search: 'Search requests:', lengthMenu: 'Show _MENU_ requests', emptyTable: 'No Pull Out replacement requests for this warehouse.', zeroRecords: 'No matching replacement requests found.' }
            });
            table.on('draw.dt', function () { table.find('.js-approved-qty').each(function () { syncChangedQuantityRemarks(this); }); });
        });
    </script>
@endsection
