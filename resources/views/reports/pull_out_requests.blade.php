@extends('layouts.header')

@section('content')
<style>
    .por-page { min-height: calc(100vh - 80px); padding: 24px; background: #f6f8fb; }
    .por-hero { position: relative; overflow: hidden; padding: 26px 28px; color: #fff; border-radius: 18px; background: linear-gradient(115deg, #0f2747, #0f766e); box-shadow: 0 18px 36px rgba(15, 61, 94, .18); }
    .por-hero::after { position: absolute; right: -65px; bottom: -105px; width: 260px; height: 260px; content: ''; border: 42px solid rgba(255, 255, 255, .08); border-radius: 50%; }
    .por-hero h3 { margin: 0; font-weight: 800; }.por-hero p { margin: 6px 0 0; opacity: .82; }
    .por-stat, .por-aging-panel, .por-card { border: 1px solid #e7edf4; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .04); }
    .por-stat { height: 100%; padding: 18px; border-radius: 14px; }.por-stat small { display: block; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }.por-stat strong { display: block; margin-top: 6px; color: #172554; font-size: 28px; }
    .is-pending strong { color: #d97706; }.is-processing strong { color: #0284c7; }.is-replacing strong { color: #2563eb; }
    .por-aging-panel, .por-card { margin-top: 20px; padding: 20px; border-radius: 18px; }.por-card { box-shadow: 0 10px 26px rgba(15, 23, 42, .05); }
    .por-section-title { margin: 0 0 14px; color: #172554; font-size: 14px; font-weight: 800; }.por-section-title small { display: block; margin-top: 3px; color: #64748b; font-size: 11px; font-weight: 600; }
    .por-card-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }.por-card-heading h5 { margin: 0; color: #172554; font-weight: 800; }.por-card-heading span { display: inline-flex; gap: 6px; align-items: center; color: #0f766e; font-size: 12px; font-weight: 800; }
    .por-filter { padding: 15px; border: 1px solid #edf2f7; border-radius: 13px; background: #f8fafc; }.por-filter label { margin-bottom: 5px; color: #475569; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }.por-filter .form-control { height: 40px; border-color: #cbd5e1; border-radius: 9px; background: #fff; }
    .aging-table { margin-bottom: 0; overflow: hidden; border: 1px solid #e5eaf0; border-radius: 10px; }.aging-table th { padding: 10px 14px; color: #64748b; font-size: 10px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; background: #f8fafc; }.aging-table td { padding: 10px 14px; color: #334155; font-size: 13px; font-weight: 700; border-color: #edf1f5; }.aging-table tbody tr:hover { background: #f8fbff; }
    .status-pending { color: #b45309 !important; }.status-processing { color: #0284c7 !important; }.status-replacing { color: #2563eb !important; }.aging-count { display: inline-grid; width: 25px; height: 25px; place-items: center; color: #fff; font-size: 11px; border-radius: 7px; background: currentColor; }.aging-count span { color: #fff; }.aging-total strong { color: #172554; font-size: 16px; }.aging-total small { margin-left: 4px; color: #64748b; font-size: 10px; text-transform: uppercase; }.aging-day { display: inline-flex; padding: 4px 8px; color: #075985; font-size: 11px; font-weight: 800; border-radius: 999px; background: #e0f2fe; }
    #pullOutReportTable { margin-top: 8px !important; border-collapse: separate; border-spacing: 0; }#pullOutReportTable thead th { padding: 13px 12px; color: #475569; font-size: 10px; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #dbe3ed; }#pullOutReportTable td { padding: 14px 12px; vertical-align: middle; border-color: #edf1f5; }#pullOutReportTable tbody tr:hover { background: #f8fbff; }
    .dataTables_processing { z-index: 10 !important; padding: 13px 22px !important; color: #0f766e !important; font-weight: 800 !important; border: 0 !important; border-radius: 10px !important; background: #fff !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .16) !important; }.por-card .dataTables_filter input, .por-card .dataTables_length select { min-height: 36px; border: 1px solid #cbd5e1; border-radius: 8px; }
    @media (max-width: 991.98px) { #pullOutReportTable { min-width: 1370px; } }
    @media (max-width: 575.98px) { .por-page { padding: 14px; }.por-hero { padding: 21px; }.por-card-heading { align-items: flex-start; gap: 8px; flex-direction: column; } }
</style>

@php
    $agingDays = array_unique(array_merge(
        array_keys($agingBreakdown['pending']),
        array_keys($agingBreakdown['processing']),
        array_keys($agingBreakdown['replacing'])
    ));
    sort($agingDays, SORT_NUMERIC);
@endphp

<div class="por-page">
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

    <section class="por-aging-panel">
        <h5 class="por-section-title">Status Aging Summary <small>Daily transaction count by current workflow status.</small></h5>
        <div class="table-responsive">
            <table class="table aging-table">
                <thead><tr><th>Aging Day</th><th class="status-pending">Pending</th><th class="status-processing">Processing</th><th class="status-replacing">Replacing</th><th>Total Transactions</th></tr></thead>
                <tbody>
                    @forelse($agingDays as $days)
                        @php($totalTransactions = ($agingBreakdown['pending'][$days] ?? 0) + ($agingBreakdown['processing'][$days] ?? 0) + ($agingBreakdown['replacing'][$days] ?? 0))
                        <tr>
                            <td>Day {{ $days }}</td>
                            <td class="status-pending"><span class="aging-count"><span>{{ $agingBreakdown['pending'][$days] ?? 0 }}</span></span></td>
                            <td class="status-processing"><span class="aging-count"><span>{{ $agingBreakdown['processing'][$days] ?? 0 }}</span></span></td>
                            <td class="status-replacing"><span class="aging-count"><span>{{ $agingBreakdown['replacing'][$days] ?? 0 }}</span></span></td>
                            <td class="aging-total"><strong>{{ $totalTransactions }}</strong><small>requests</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted fw-normal">No pending, processing, or replacing requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="por-card">
        <div class="por-card-heading"><div><h5>Transaction Register</h5><small class="text-muted">Server-side transaction list with status and aging per request.</small></div><span><i class="bi bi-database-check"></i> Server-side data</span></div>
        <div class="row g-3 por-filter mb-4">
            <div class="col-md-3"><label for="statusFilter">Status</label><select id="statusFilter" class="form-control"><option value="">All statuses</option><option>Pending</option><option>For Processing</option><option>Replacing</option><option>Approved</option><option>Rejected</option></select></div>
            <div class="col-md-3"><label for="warehouseFilter">Warehouse</label><select id="warehouseFilter" class="form-control"><option value="">All warehouses</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse }}">{{ ucfirst($warehouse) }}</option>@endforeach</select></div>
            <div class="col-md-2"><label for="dateFrom">From</label><input id="dateFrom" type="date" class="form-control"></div>
            <div class="col-md-2"><label for="dateTo">To</label><input id="dateTo" type="date" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button id="clearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Clear</button></div>
        </div>
        <div id="reportDataError" class="alert alert-danger d-none mb-3"></div>
        <div class="table-responsive">
            <table id="pullOutReportTable" class="table table-hover w-100">
                <thead><tr><th>Date</th><th>Reference</th><th>Area Distributor</th><th>Pull-out Product</th><th>Replacement Product</th><th>Source Area</th><th>Requested Qty</th><th>Approved Qty</th><th>RIS Number</th><th>RIS Date</th><th>Warehouse</th><th>Status</th><th>Aging</th><th>Attachments</th><th>Remarks</th></tr></thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('javascript')
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
            } catch (error) {}
        }
        return false;
    }

    const $table = window.jQuery('#pullOutReportTable');
    const $error = window.jQuery('#reportDataError');

    if (!await loadDataTables()) {
        $error.text('Unable to load the report table. Please refresh the page.').removeClass('d-none');
        return;
    }

    if (window.jQuery.fn.DataTable.isDataTable('#pullOutReportTable')) $table.DataTable().destroy();

    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 350,
        pageLength: 25,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
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
                data.status = window.jQuery('#statusFilter').val();
                data.warehouse = window.jQuery('#warehouseFilter').val();
                data.date_from = window.jQuery('#dateFrom').val();
                data.date_to = window.jQuery('#dateTo').val();
            },
            error: function (xhr) {
                $error.text(xhr.status === 403 ? 'You do not have access to view Pull Out Requests.' : 'Unable to load the Pull Out Request data. Please refresh and try again.').removeClass('d-none');
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
            { data: 'aging', orderable: false, searchable: false },
            { data: 'attachments', orderable: false, searchable: false },
            { data: 'remarks', name: 'inventory_transfers.warehouse_remarks' }
        ]
    });

    window.jQuery('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').on('change', function () {
        $error.addClass('d-none');
        table.draw();
    });

    window.jQuery('#clearFilters').on('click', function () {
        window.jQuery('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').val('');
        $error.addClass('d-none');
        table.draw();
    });
});
</script>
@endsection
