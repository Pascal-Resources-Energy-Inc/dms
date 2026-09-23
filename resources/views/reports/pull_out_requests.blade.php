@extends('layouts.header')

@section('content')
<style>
    .por-page { padding: 24px; background: #f6f8fb; min-height: calc(100vh - 80px); }
    .por-hero { position:relative; overflow:hidden; padding:26px 28px; color:#fff; border-radius:18px; background:linear-gradient(115deg,#0f2747,#0f766e); box-shadow:0 18px 36px rgba(15,61,94,.18); }
    .por-hero::after { content:''; position:absolute; right:-65px; bottom:-105px; width:260px; height:260px; border:42px solid rgba(255,255,255,.08); border-radius:50%; }
    .por-hero h3 { margin: 0; font-weight: 800; } .por-hero p { margin: 6px 0 0; opacity: .82; }
    .por-stat { height:100%; padding:18px; border:1px solid #e7edf4; border-radius:14px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.04); }
    .por-stat small { display:block; color:#64748b; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; } .por-stat strong { display:block; margin-top:6px; font-size:28px; color:#172554; }
    .por-stat.is-pending strong { color:#d97706; }.por-stat.is-processing strong { color:#0284c7; }.por-stat.is-completed strong { color:#059669; }.por-stat.is-rejected strong { color:#dc2626; }
    .por-card { margin-top:20px; padding:22px; border:1px solid #e7edf4; border-radius:18px; background:#fff; box-shadow:0 10px 26px rgba(15,23,42,.05); }
    .por-card-heading { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }.por-card-heading h5 { margin:0; font-weight:800; color:#172554; }.por-card-heading span { display:inline-flex; align-items:center; gap:6px; color:#0f766e; font-size:12px; font-weight:800; }
    .por-filter { padding:15px; background:#f8fafc; border:1px solid #edf2f7; border-radius:13px; }.por-filter label { margin-bottom:5px; color:#475569; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; } .por-filter .form-control { height:40px; border-color:#cbd5e1; border-radius:9px; background:#fff; }
    #pullOutReportTable { margin-top:8px!important; border-collapse:separate; border-spacing:0; } #pullOutReportTable thead th { padding:13px 12px; color:#475569; font-size:10px; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; background:#f8fafc; border-top:1px solid #e5e7eb; border-bottom:1px solid #dbe3ed; } #pullOutReportTable td { padding:14px 12px; vertical-align:middle; border-color:#edf1f5; } #pullOutReportTable tbody tr:hover { background:#f8fbff; }
    .dataTables_processing { z-index:10!important; padding:13px 22px!important; color:#0f766e!important; font-weight:800!important; border:0!important; border-radius:10px!important; background:#fff!important; box-shadow:0 8px 24px rgba(15,23,42,.16)!important; }
    .por-card .dataTables_wrapper .dataTables_filter input, .por-card .dataTables_wrapper .dataTables_length select { min-height:36px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; }.por-card .dataTables_wrapper .dataTables_filter { margin-bottom:12px; }.por-card .dataTables_info { color:#64748b; font-size:12px; }.por-card .dataTables_paginate .paginate_button { border-radius:7px!important; }
    @media (max-width: 991.98px) { #pullOutReportTable { min-width:1280px; } }
    @media (max-width: 575.98px) { .por-page { padding:14px; }.por-hero { padding:21px; }.por-hero h3 { font-size:21px; }.por-card { padding:14px; }.por-card-heading { align-items:flex-start; gap:8px; flex-direction:column; } }
</style>
<div class="por-page">
    <div class="por-hero"><div class="d-flex justify-content-between align-items-center"><div><h3 class="text-white"><i class="bi bi-clipboard-data me-2"></i>Pull Out Request Report</h3><p>Track warehouse approval, replacement quantities, and supporting proof files.</p></div><i class="bi bi-arrow-left-right fs-1 opacity-50"></i></div></div>
    <div class="row g-3 mt-1"><div class="col-6 col-lg-3"><div class="por-stat"><small>Total Requests</small><strong>{{ number_format($summary['total']) }}</strong></div></div><div class="col-6 col-lg-3"><div class="por-stat is-pending"><small>Pending Review</small><strong>{{ number_format($summary['pending']) }}</strong></div></div><div class="col-6 col-lg-3"><div class="por-stat is-processing"><small>In Progress</small><strong>{{ number_format($summary['processing']) }}</strong></div></div><div class="col-6 col-lg-3"><div class="por-stat is-completed"><small>Replacing</small><strong>{{ number_format($summary['replacing']) }}</strong></div></div></div>
    <div class="por-card"><div class="por-card-heading"><div><h5>Request Register</h5><small class="text-muted">Live pull-out replacement requests</small></div><span><i class="bi bi-database-check"></i> Server-side data</span></div><div class="row g-3 por-filter mb-4"><div class="col-md-3"><label>Status</label><select id="statusFilter" class="form-control"><option value="">All statuses</option><option>Pending</option><option>For Processing</option><option>Replacing</option><option>Approved</option><option>Rejected</option></select></div><div class="col-md-3"><label>Warehouse</label><select id="warehouseFilter" class="form-control"><option value="">All warehouses</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse }}">{{ ucfirst($warehouse) }}</option>@endforeach</select></div><div class="col-md-2"><label>From</label><input id="dateFrom" type="date" class="form-control"></div><div class="col-md-2"><label>To</label><input id="dateTo" type="date" class="form-control"></div><div class="col-md-2 d-flex align-items-end"><button id="clearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Clear</button></div></div>
        <div id="reportDataError" class="alert alert-danger d-none mb-3"></div>
        <div class="table-responsive"><table id="pullOutReportTable" class="table table-hover w-100"><thead><tr><th>Date</th><th>Reference</th><th>Area Distributor</th><th>Pull-out Product</th><th>Replacement Product</th><th>Source Area</th><th>Requested Qty</th><th>Approved Qty</th><th>RIS Number</th><th>RIS Date</th><th>Warehouse</th><th>Status</th><th>Attachments</th><th>Remarks</th></tr></thead></table></div>
    </div>
</div>
@endsection

@section('javascript')
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

    if (!await loadDataTables()) {
        window.jQuery('#reportDataError')
            .text('Unable to load the report table. Please refresh the page.')
            .removeClass('d-none');
        return;
    }

    var $table = window.jQuery('#pullOutReportTable');
    if (window.jQuery.fn.DataTable.isDataTable('#pullOutReportTable')) {
        $table.DataTable().destroy();
    }

    var table = $table.DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'desc']],
        ajax: {
            url: '{{ route('pull-out-report.data') }}',
            data: function (data) {
                data.status = window.jQuery('#statusFilter').val();
                data.warehouse = window.jQuery('#warehouseFilter').val();
                data.date_from = window.jQuery('#dateFrom').val();
                data.date_to = window.jQuery('#dateTo').val();
            },
            error: function (xhr) {
                var message = xhr.status === 403
                    ? 'You do not have access to view Pull Out Requests.'
                    : 'Unable to load the Pull Out Request data. Please refresh and try again.';
                window.jQuery('#reportDataError').text(message).removeClass('d-none');
            }
        },
        columns: [
            { data: 'transfer_date', name: 'inventory_transfers.transfer_date' },
            { data: 'reference_no', name: 'inventory_transfers.reference_no' },
            { data: 'distributor', name: 'distributors.name' },
            { data: 'pull_out_product', name: 'inventory_transfers.item_name', orderable: false },
            { data: 'replacement_product', name: 'inventory_transfers.replacement_item_name', orderable: false },
            { data: 'from_area', name: 'inventory_transfers.from_area' },
            { data: 'request_qty', name: 'inventory_transfers.replacement_qty' },
            { data: 'approved_qty', name: 'inventory_transfers.qty' },
            { data: 'ris_number_display', name: 'inventory_transfers.ris_number' },
            { data: 'ris_date_display', name: 'inventory_transfers.ris_date' },
            { data: 'warehouse', name: 'inventory_transfers.warehouse' },
            { data: 'status_badge', name: 'inventory_transfers.approval_status', orderable: false },
            { data: 'attachments', orderable: false, searchable: false },
            { data: 'remarks', name: 'inventory_transfers.warehouse_remarks' }
        ]
    });

    window.jQuery('#statusFilter,#warehouseFilter,#dateFrom,#dateTo').on('change', function () {
        window.jQuery('#reportDataError').addClass('d-none');
        table.draw();
    });

    window.jQuery('#clearFilters').on('click', function () {
        window.jQuery('#statusFilter,#warehouseFilter').val('');
        window.jQuery('#dateFrom,#dateTo').val('');
        window.jQuery('#reportDataError').addClass('d-none');
        table.draw();
    });
});
</script>
@endsection
