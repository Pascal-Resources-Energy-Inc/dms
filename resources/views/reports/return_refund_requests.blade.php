@extends('layouts.header')

@section('content')
<style>
    .rrr-hero { position: relative; overflow: hidden; padding: 26px 28px; color: #fff; border-radius: 18px; background: linear-gradient(115deg, #172554, #0f766e); box-shadow: 0 18px 36px rgba(15, 61, 94, .18); }
    .rrr-hero::after { position: absolute; right: -65px; bottom: -105px; width: 260px; height: 260px; content: ''; border: 42px solid rgba(255, 255, 255, .08); border-radius: 50%; }
    .rrr-hero h3 { position: relative; z-index: 1; margin: 0; color: #fff; font-weight: 800; }
    .rrr-hero p { position: relative; z-index: 1; margin: 6px 0 0; opacity: .84; }
    .rrr-hero-icon { position: relative; z-index: 1; font-size: 32px; opacity: .55; }
    .rrr-stat, .rrr-card { border: 1px solid #e7edf4; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .04); }
    .rrr-stat { height: 100%; padding: 18px; border-radius: 14px; }
    .rrr-stat small { display: block; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .rrr-stat strong { display: block; margin-top: 6px; color: #172554; font-size: 28px; line-height: 1; }
    .rrr-stat.is-pending strong { color: #d97706; }
    .rrr-stat.is-receiving strong { color: #0284c7; }
    .rrr-stat.is-processing strong { color: #2563eb; }
    .rrr-stat.is-review strong { color: #7c3aed; }
    .rrr-card { margin-top: 20px; padding: 20px; border-radius: 18px; }
    .rrr-card-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .rrr-card-heading h5 { margin: 0; color: #172554; font-weight: 800; }
    .rrr-card-heading small { display: block; margin-top: 3px; color: #64748b; }
    .rrr-card-heading span { color: #0f766e; font-size: 12px; font-weight: 800; }
    .rrr-filter { padding: 15px; border: 1px solid #edf2f7; border-radius: 13px; background: #f8fafc; }
    .rrr-filter label { margin-bottom: 5px; color: #475569; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .rrr-filter .form-control { height: 40px; border-color: #cbd5e1; border-radius: 9px; background: #fff; }
    .rrr-table-shell { overflow: hidden; border: 1px solid #e6edf4; border-radius: 12px; background: #fff; }
    #returnRefundReportTable { min-width: 1560px; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
    #returnRefundReportTable thead th { padding: 13px 12px; color: #475569; font-size: 10px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-top: 0; border-bottom: 1px solid #dbe3ed; }
    #returnRefundReportTable td { padding: 14px 12px; vertical-align: middle; border-color: #edf1f5; }
    #returnRefundReportTable tbody tr:hover { background: #f8fbff; }
    #returnRefundReportTable td:nth-child(1), #returnRefundReportTable td:nth-child(2), #returnRefundReportTable td:nth-child(8), #returnRefundReportTable td:nth-child(9) { white-space: nowrap; }
    .rr-report-product { padding: 5px 0; border-bottom: 1px solid #edf1f5; }
    .rr-report-product:last-child { border-bottom: 0; }
    .rr-report-product strong, .rr-report-product small { display: block; }
    .rr-report-product small { color: #64748b; font-size: 11px; }
    .rr-report-files { display: flex; flex-direction: column; gap: 3px; margin-top: 5px; }
    .rr-report-files a { max-width: 190px; overflow: hidden; color: #0284c7; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .rrr-card .dataTables_wrapper { padding: 0; }
    .rrr-card .dataTables_scroll { border-top: 1px solid #edf1f5; border-bottom: 1px solid #edf1f5; }
    .rrr-card .dataTables_scrollBody { scrollbar-color: #94a3b8 transparent; }
    .rrr-card .dataTables_scrollBody::-webkit-scrollbar { width: 9px; height: 9px; }
    .rrr-card .dataTables_scrollBody::-webkit-scrollbar-thumb { border: 2px solid #fff; border-radius: 99px; background: #94a3b8; }
    .rrr-datatable-tools, .rrr-datatable-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background: #fff; }
    .rrr-datatable-tools .dataTables_length, .rrr-datatable-tools .dataTables_filter, .rrr-datatable-footer .dataTables_info, .rrr-datatable-footer .dataTables_paginate { float: none !important; margin: 0 !important; }
    .rrr-datatable-tools .dataTables_filter { margin-left: auto !important; text-align: right; }
    .rrr-card .dataTables_filter input, .rrr-card .dataTables_length select { min-height: 36px; margin-left: 7px; padding: 5px 9px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; outline: none; }
    .rrr-card .dataTables_filter input:focus, .rrr-card .dataTables_length select:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .12); }
    #returnRefundReportTable_wrapper td.dataTables_empty { padding: 42px 16px !important; color: #64748b; font-weight: 600; text-align: center !important; vertical-align: middle; }
    .rrr-card .dataTables_paginate .paginate_button { min-width: 34px; height: 34px; margin: 0 2px !important; padding: 7px 10px !important; color: #475569 !important; font-size: 12px; font-weight: 700; line-height: 18px; border: 1px solid transparent !important; border-radius: 8px; background: transparent !important; }
    .rrr-card .dataTables_paginate .paginate_button:hover { color: #0f766e !important; border-color: #99f6e4 !important; background: #f0fdfa !important; }
    .rrr-card .dataTables_paginate .paginate_button.current, .rrr-card .dataTables_paginate .paginate_button.current:hover { color: #fff !important; border-color: #0f766e !important; background: #0f766e !important; }
    .dataTables_processing { z-index: 10 !important; padding: 13px 22px !important; color: #0f766e !important; font-weight: 800 !important; border: 0 !important; border-radius: 10px !important; background: #fff !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .16) !important; }
    @media (max-width: 991.98px) { .return-refund-report { padding: 18px; } #returnRefundReportTable { min-width: 1400px; } }
    @media (max-width: 575.98px) { .return-refund-report { padding: 14px; }.rrr-hero, .rrr-card-heading, .rrr-datatable-tools, .rrr-datatable-footer { align-items: flex-start; flex-direction: column; }.rrr-hero { padding: 21px; }.rrr-hero h3 { font-size: 21px; }.rrr-hero-icon { display: none; }.rrr-card { padding: 14px; }.rrr-datatable-tools .dataTables_filter { margin-left: 0 !important; text-align: left; }.rrr-datatable-footer .dataTables_paginate { width: 100%; overflow-x: auto; white-space: nowrap; } }
</style>

<div class="return-refund-report">
    <section class="rrr-hero d-flex align-items-center justify-content-between">
        <div>
            <h3><i class="bi bi-arrow-repeat me-2"></i>Return &amp; Refund Report</h3>
            <p>Monitor approvals, receiving documents, credit memos, and final review in one place.</p>
        </div>
        <i class="bi bi-clipboard2-check rrr-hero-icon"></i>
    </section>

    <div class="row g-3 mt-1">
        <div class="col-6 col-lg"><div class="rrr-stat"><small>Total Requests</small><strong>{{ number_format($summary['total']) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="rrr-stat is-pending"><small>Pending</small><strong>{{ number_format($summary['pending']) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="rrr-stat is-receiving"><small>For Receiving</small><strong>{{ number_format($summary['receiving']) }}</strong></div></div>
        <div class="col-6 col-lg"><div class="rrr-stat is-processing"><small>Processing</small><strong>{{ number_format($summary['processing']) }}</strong></div></div>
        <div class="col-12 col-lg"><div class="rrr-stat is-review"><small>For Review</small><strong>{{ number_format($summary['review']) }}</strong></div></div>
    </div>

    <section class="rrr-card">
        <div class="rrr-card-heading">
            <div>
                <h5>Transaction Register</h5>
                <small>Server-side return and refund requests, grouped by movement reference.</small>
            </div>
            <span><i class="bi bi-database-check me-1"></i>Server-side data</span>
        </div>

        <div class="row g-3 rrr-filter mb-4">
            <div class="col-md-3">
                <label for="returnRefundStatusFilter">Status</label>
                <select id="returnRefundStatusFilter" class="form-control">
                    <option value="">All statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Documents Submitted">For receiving</option>
                    <option value="Processing">Processing</option>
                    <option value="For Review">For review</option>
                    <option value="Reviewed">Reviewed</option>
                    <option value="President Approved">President approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="returnRefundWarehouseFilter">Warehouse</label>
                <select id="returnRefundWarehouseFilter" class="form-control">
                    <option value="">All warehouses</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse }}">{{ ucfirst($warehouse) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label for="returnRefundDateFrom">From</label><input id="returnRefundDateFrom" type="date" class="form-control"></div>
            <div class="col-md-2"><label for="returnRefundDateTo">To</label><input id="returnRefundDateTo" type="date" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button id="returnRefundClearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear</button></div>
        </div>

        <div id="returnRefundReportError" class="alert alert-danger d-none mb-3"></div>
        <div class="rrr-table-shell">
            <table id="returnRefundReportTable" class="table table-hover w-100 mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Distributor</th>
                        <th>Products &amp; Quantities</th>
                        <th>RIS / Attachments</th>
                        <th>Receiving</th>
                        <th>Credit Memo</th>
                        <th>Warehouse</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script src="{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !window.jQuery.fn.DataTable) return;

    const $ = window.jQuery;
    const $table = $('#returnRefundReportTable');
    const $error = $('#returnRefundReportError');

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
        dom: '<"rrr-datatable-tools"lf>rt<"rrr-datatable-footer"ip>',
        order: [[0, 'desc']],
        language: {
            processing: '<i class="bi bi-arrow-repeat me-1"></i> Loading return and refund requests...',
            search: 'Search transactions:',
            lengthMenu: 'Show _MENU_ requests',
            emptyTable: 'No Return & Refund requests found.',
            zeroRecords: 'No matching Return & Refund requests found.'
        },
        ajax: {
            url: '{{ route('return-refund-report.data') }}',
            data: function (data) {
                data.status = $('#returnRefundStatusFilter').val();
                data.warehouse = $('#returnRefundWarehouseFilter').val();
                data.date_from = $('#returnRefundDateFrom').val();
                data.date_to = $('#returnRefundDateTo').val();
            },
            error: function (xhr) {
                $error.text(xhr.status === 403
                    ? 'You do not have access to view this report.'
                    : 'Unable to load Return & Refund data. Please refresh and try again.')
                    .removeClass('d-none');
            }
        },
        columns: [
            { data: 'transfer_date', name: 'return_refund_rows.transfer_date' },
            { data: 'reference_no', name: 'return_refund_rows.reference_no' },
            { data: 'distributor', name: 'distributors.name' },
            { data: 'products', orderable: false },
            { data: 'ris_details', orderable: false, searchable: false },
            { data: 'receiving_details', orderable: false, searchable: false },
            { data: 'credit_memo_details', orderable: false, searchable: false },
            { data: 'warehouse', name: 'return_refund_rows.warehouse' },
            { data: 'status_badge', name: 'return_refund_rows.approval_status', orderable: false },
            { data: 'remarks_display', name: 'return_refund_rows.warehouse_remarks' }
        ]
    });

    $('#returnRefundStatusFilter, #returnRefundWarehouseFilter, #returnRefundDateFrom, #returnRefundDateTo').on('change', function () {
        $error.addClass('d-none');
        table.draw();
    });

    $('#returnRefundClearFilters').on('click', function () {
        $('#returnRefundStatusFilter, #returnRefundWarehouseFilter, #returnRefundDateFrom, #returnRefundDateTo').val('');
        $error.addClass('d-none');
        table.draw();
    });
});
</script>
@endsection
