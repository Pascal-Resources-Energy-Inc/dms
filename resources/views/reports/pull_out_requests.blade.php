@extends('layouts.header')

@section('content')
<style>
    .por-page { min-height: calc(100vh - 80px); padding: 24px; background: #f6f8fb; }
    .por-hero { position: relative; overflow: hidden; padding: 26px 28px; color: #fff; border-radius: 18px; background: linear-gradient(115deg, #0f2747, #0f766e); box-shadow: 0 18px 36px rgba(15, 61, 94, .18); }
    .por-hero::after { position: absolute; right: -65px; bottom: -105px; width: 260px; height: 260px; content: ''; border: 42px solid rgba(255, 255, 255, .08); border-radius: 50%; }
    .por-hero h3 { margin: 0; font-weight: 800; }
    .por-hero p { margin: 6px 0 0; opacity: .82; }
    .por-stat, .por-card { border: 1px solid #e7edf4; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .04); }
    .por-stat { height: 100%; padding: 18px; border-radius: 14px; }
    .por-stat small { display: block; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .por-stat strong { display: block; margin-top: 6px; color: #172554; font-size: 28px; }
    .is-pending strong { color: #d97706; }
    .is-processing strong { color: #0284c7; }
    .is-replacing strong { color: #2563eb; }
    .por-card { margin-top: 20px; padding: 20px; border-radius: 18px; box-shadow: 0 10px 26px rgba(15, 23, 42, .05); }
    .por-card-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .por-card-heading h5 { margin: 0; color: #172554; font-weight: 800; }
    .por-card-heading span { display: inline-flex; gap: 6px; align-items: center; color: #0f766e; font-size: 12px; font-weight: 800; }
    .por-filter { padding: 15px; border: 1px solid #edf2f7; border-radius: 13px; background: #f8fafc; }
    .por-filter label { margin-bottom: 5px; color: #475569; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .por-filter .form-control { height: 40px; border-color: #cbd5e1; border-radius: 9px; background: #fff; }
    .por-table-shell { overflow: hidden; border: 1px solid #e6edf4; border-radius: 12px; background: #fff; }
    #pullOutReportTable { min-width: 1680px; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
    #pullOutReportTable thead th { padding: 13px 12px; color: #475569; font-size: 10px; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-top: 0; border-bottom: 1px solid #dbe3ed; }
    #pullOutReportTable td { padding: 14px 12px; vertical-align: middle; border-color: #edf1f5; }
    #pullOutReportTable tbody tr:hover { background: #f8fbff; }
    #pullOutReportTable td:nth-child(1), #pullOutReportTable td:nth-child(2), #pullOutReportTable td:nth-child(7), #pullOutReportTable td:nth-child(8), #pullOutReportTable td:nth-child(9), #pullOutReportTable td:nth-child(10), #pullOutReportTable td:nth-child(11), #pullOutReportTable td:nth-child(12), #pullOutReportTable td:nth-child(13), #pullOutReportTable td:nth-child(14), #pullOutReportTable td:nth-child(15) { white-space: nowrap; }
    .aging-day { display: inline-flex; padding: 4px 8px; color: #075985; font-size: 11px; font-weight: 800; border-radius: 999px; background: #e0f2fe; }
    .por-card .dataTables_wrapper { padding: 0; }
    .por-card .dataTables_scroll { border-top: 1px solid #edf1f5; border-bottom: 1px solid #edf1f5; }
    .por-card .dataTables_scrollHead { background: #f8fafc; }
    .por-card .dataTables_scrollBody { scrollbar-color: #94a3b8 transparent; }
    .por-card .dataTables_scrollBody::-webkit-scrollbar { width: 9px; height: 9px; }
    .por-card .dataTables_scrollBody::-webkit-scrollbar-thumb { border: 2px solid #fff; border-radius: 99px; background: #94a3b8; }
    .por-datatable-tools, .por-datatable-footer { display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 14px 16px; background: #fff; }
    .por-datatable-tools .dataTables_length, .por-datatable-tools .dataTables_filter, .por-datatable-footer .dataTables_info, .por-datatable-footer .dataTables_paginate { float: none !important; margin: 0 !important; }
    .por-datatable-tools .dataTables_filter { margin-left: auto !important; text-align: right; }
    .por-card .dataTables_filter input, .por-card .dataTables_length select { min-height: 36px; margin-left: 7px; padding: 5px 9px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; outline: none; }
    .por-card .dataTables_filter input:focus, .por-card .dataTables_length select:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .12); }
    .por-card .dataTables_paginate .paginate_button { min-width: 34px; height: 34px; margin: 0 2px !important; padding: 7px 10px !important; color: #475569 !important; font-size: 12px; font-weight: 700; line-height: 18px; border: 1px solid transparent !important; border-radius: 8px; background: transparent !important; }
    .por-card .dataTables_paginate .paginate_button:hover { color: #0f766e !important; border-color: #99f6e4 !important; background: #f0fdfa !important; }
    .por-card .dataTables_paginate .paginate_button.current, .por-card .dataTables_paginate .paginate_button.current:hover { color: #fff !important; border-color: #0f766e !important; background: #0f766e !important; }
    .por-card .dataTables_paginate .paginate_button.disabled, .por-card .dataTables_paginate .paginate_button.disabled:hover { color: #cbd5e1 !important; background: transparent !important; }
    .dataTables_processing { z-index: 10 !important; padding: 13px 22px !important; color: #0f766e !important; font-weight: 800 !important; border: 0 !important; border-radius: 10px !important; background: #fff !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .16) !important; }
    @media (max-width: 991.98px) { #pullOutReportTable { min-width: 1500px; } }
    @media (max-width: 575.98px) { .por-page { padding: 14px; }.por-hero, .por-card-heading, .por-datatable-tools, .por-datatable-footer { align-items: flex-start; gap: 10px; flex-direction: column; }.por-hero { padding: 21px; }.por-datatable-tools .dataTables_filter { margin-left: 0 !important; text-align: left; }.por-datatable-footer .dataTables_paginate { width: 100%; overflow-x: auto; white-space: nowrap; } }
</style>

<div class="por-hero">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="text-white"><i class="bi bi-clipboard-data me-2"></i>Pull Out Request Report</h3>
            <p>Track warehouse approval, replacement quantities, RIS details, and supporting files.</p>
        </div>
        <i class="bi bi-arrow-left-right fs-1 opacity-50"></i>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-6 col-lg-3"><div class="por-stat"><small>Total Requests</small><strong>{{ number_format($summary['total']) }}</strong></div></div>
    <div class="col-6 col-lg-3"><div class="por-stat is-pending"><small>Pending Review</small><strong>{{ number_format($summary['pending']) }}</strong></div></div>
    <div class="col-6 col-lg-3"><div class="por-stat is-processing"><small>Processing</small><strong>{{ number_format($summary['processing']) }}</strong></div></div>
    <div class="col-6 col-lg-3"><div class="por-stat is-replacing"><small>Replacing</small><strong>{{ number_format($summary['replacing']) }}</strong></div></div>
</div>

<section class="por-card">
    <div class="por-card-heading">
        <div>
            <h5>Transaction Register</h5>
            <small class="text-muted">Server-side transaction list with status and aging per request.</small>
        </div>
        <span><i class="bi bi-database-check"></i> Server-side data</span>
    </div>

    <div class="row g-3 por-filter mb-4">
        <div class="col-md-3">
            <label for="statusFilter">Status</label>
            <select id="statusFilter" class="form-control">
                <option value="">All statuses</option>
                <option>Pending</option>
                <option>For Processing</option>
                <option>Replacing</option>
                <option>Approved</option>
                <option>Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="warehouseFilter">Warehouse</label>
            <select id="warehouseFilter" class="form-control">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse }}">{{ ucfirst($warehouse) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label for="dateFrom">From</label><input id="dateFrom" type="date" class="form-control"></div>
        <div class="col-md-2"><label for="dateTo">To</label><input id="dateTo" type="date" class="form-control"></div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="clearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Clear</button>
        </div>
    </div>

    <div id="reportDataError" class="alert alert-danger d-none mb-3"></div>
    <div class="por-table-shell">
        <table id="pullOutReportTable" class="table table-hover w-100" style="margin-bottom: 0px">
            <thead>
                <tr>
                    <th>Date</th><th>Reference</th><th>Distributor</th><th>Pull-out Product</th><th>Replacement Product</th><th>Source Area</th><th>Requested Qty</th><th>Approved Qty</th><th>RIS Number</th><th>RIS Date</th><th>Warehouse</th><th>Status</th><th>Pending Aging</th><th>Processing Aging</th><th>Replacing Aging</th><th>Attachments</th><th>Remarks</th><th>Distributor Confirmation</th>
                </tr>
            </thead>
        </table>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    if (!window.jQuery) return;

    async function loadDataTables() {
        if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;

        const sources = [
            "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
            "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
            'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js'
        ];

        for (const source of sources) {
            try {
                await new Promise(function (resolve, reject) {
                    const script = document.createElement('script');
                    script.src = source;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                if (window.jQuery.fn && window.jQuery.fn.DataTable) return true;
            } catch (error) {
                // Continue to the next configured source.
            }
        }

        return false;
    }

    const $ = window.jQuery;
    const $table = $('#pullOutReportTable');
    const $error = $('#reportDataError');

    if (!await loadDataTables()) {
        $error.text('Unable to load the report table. Please refresh the page.').removeClass('d-none');
        return;
    }

    if ($.fn.DataTable.isDataTable('#pullOutReportTable')) $table.DataTable().destroy();

    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 350,
        pageLength: 25,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        pagingType: 'simple_numbers',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: '<"por-datatable-tools"lf>rt<"por-datatable-footer"ip>',
        order: [[0, 'desc']],
        language: {
            processing: '<i class="bi bi-arrow-repeat me-1"></i> Loading pull-out requests...',
            search: 'Search transactions:',
            lengthMenu: 'Show _MENU_ requests',
            emptyTable: 'No pull-out requests found.',
            zeroRecords: 'No matching pull-out requests found.'
        },
        ajax: {
            url: '{{ route('pull-out-report.data') }}',
            data: function (data) {
                data.status = $('#statusFilter').val();
                data.warehouse = $('#warehouseFilter').val();
                data.date_from = $('#dateFrom').val();
                data.date_to = $('#dateTo').val();
            },
            error: function (xhr) {
                const message = xhr.status === 403
                    ? 'You do not have access to view Pull Out Requests.'
                    : 'Unable to load the Pull Out Request data. Please refresh and try again.';
                $error.text(message).removeClass('d-none');
            }
        },
        columns: [
            { data: 'transfer_date', name: 'pull_out_rows.transfer_date' },
            { data: 'reference_no', name: 'pull_out_rows.reference_no' },
            { data: 'distributor', name: 'distributors.name' },
            { data: 'pull_out_product', orderable: false },
            { data: 'replacement_product', orderable: false },
            { data: 'from_area', name: 'pull_out_rows.from_area' },
            { data: 'request_qty', name: 'pull_out_rows.requested_qty_total' },
            { data: 'approved_qty', name: 'pull_out_rows.approved_qty_total' },
            { data: 'ris_number_display', name: 'pull_out_rows.ris_number' },
            { data: 'ris_date_display', name: 'pull_out_rows.ris_date' },
            { data: 'warehouse', name: 'pull_out_rows.warehouse' },
            { data: 'status_badge', name: 'pull_out_rows.approval_status', orderable: false },
            { data: 'pending_aging', orderable: false, searchable: false },
            { data: 'processing_aging', orderable: false, searchable: false },
            { data: 'replacing_aging', orderable: false, searchable: false },
            { data: 'attachments', orderable: false, searchable: false },
            { data: 'remarks', name: 'inventory_transfers.warehouse_remarks' },
            { data: 'ad_confirmation', orderable: false, searchable: false }
        ]
    });

    $('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').on('change', function () {
        $error.addClass('d-none');
        table.draw();
    });

    $('#clearFilters').on('click', function () {
        $('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').val('');
        $error.addClass('d-none');
        table.draw();
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.js-confirm-replacement-form');
        if (!form) return;

        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const quantity = button ? button.dataset.qty : 'the approved';
        const submitConfirmation = function () {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Confirming...';
            form.submit();
        };

        if (!window.Swal) {
            if (window.confirm('Confirm receipt of ' + quantity + ' pcs? Stock will be added to Current Stock by Area.')) submitConfirmation();
            return;
        }

        window.Swal.fire({
            icon: 'question',
            title: 'Confirm replacement quantity?',
            html: '<p class="mb-2">You are confirming receipt of <strong>' + quantity + ' pcs</strong>.</p><p class="text-muted small mb-0">After confirmation, the quantity will be added to Current Stock by Area.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Yes, confirm quantity',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) submitConfirmation();
        });
    });
});
</script>
@endsection
