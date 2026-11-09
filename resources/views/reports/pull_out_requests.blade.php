@extends('layouts.header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pull-out-report.css') }}">
@endsection

@section('content')
<div class="por-page">
    <header class="por-heading">
        <h1>Pull Out Request Report</h1>
        <p>Track requests, replacement quantities, and delivery receipts.</p>
    </header>
    <dl class="por-stats" aria-label="Request totals">
        @foreach(['total' => 'Total requests', 'pending' => 'Pending review', 'processing' => 'Processing', 'replacing' => 'Replacing'] as $key => $label)
            <div class="por-stat is-{{ $key }}"><dt>{{ $label }}</dt><dd>{{ number_format($summary[$key]) }}</dd></div>
        @endforeach
    </dl>
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <section class="por-card" aria-labelledby="porRegisterTitle">
        <header class="por-card-heading"><h2 id="porRegisterTitle">Transaction register</h2><p>Open a request to view its details and delivery receipts.</p></header>
        <div class="por-filter" role="group" aria-label="Filter requests">
            <div><label for="statusFilter">Status</label><select id="statusFilter" class="form-select">
                <option value="">All statuses</option><option>Pending</option><option>Partial</option><option>For Processing</option><option>Replacing</option><option>Approved</option><option>AD Confirmed</option><option>Rejected</option>
            </select></div>
            <div><label for="warehouseFilter">Warehouse</label><select id="warehouseFilter" class="form-select">
                <option value="">All warehouses</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse }}">{{ ucfirst($warehouse) }}</option>
                @endforeach
            </select></div>
            <div><label for="dateFrom">From date</label><input id="dateFrom" type="date" class="form-control"></div>
            <div><label for="dateTo">To date</label><input id="dateTo" type="date" class="form-control"></div>
            <button id="clearFilters" type="button" class="btn btn-light">Clear filters</button>
        </div>
        <div id="reportDataError" class="alert alert-danger d-none" role="alert"></div>
        <div class="por-table-shell">
            <table id="pullOutReportTable" class="por-table">
                <thead><tr><th scope="col">Request</th><th scope="col">Distributor</th><th scope="col">Products</th><th scope="col">Status</th><th scope="col">Actions</th><th>Date</th><th>Area</th><th>Warehouse</th><th>RIS number</th><th>RIS date</th><th>Remarks</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
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

    const text = function (value) {
        const entities = {'&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&#039;': "'"};
        return String(value == null ? '' : value)
            .replace(/&(amp|lt|gt|quot|#039);/g, entity => entities[entity])
            .replace(/[&<>"']/g, character => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character]));
    };
    const units = value => Number(value || 0).toLocaleString();
    function productSummary(products, row) {
        const names = products.slice(0, 2).map(product => `<div class="por-product-summary"><span>${text(product.name)}</span><b>${units(product.qty)} pcs</b></div>`).join('');
        return names + `<small class="por-muted">${products.length > 2 ? '+' + (products.length - 2) + ' more · ' : ''}${units(row.requested_qty_total)} requested · ${units(row.approved_qty_total)} approved</small>`;
    }
    function statusSummary(status, type, row) {
        const colors = {'Pending': 'pending', 'Partial': 'pending', 'For Processing': 'processing', 'Replacing': 'replacing', 'Rejected': 'rejected', 'Approved': 'confirmed', 'AD Confirmed': 'confirmed', 'Warehouse Confirmed': 'confirmed'};
        const aging = {'Pending': row.pending_aging, 'For Processing': row.processing_aging, 'Replacing': row.replacing_aging}[status];
        return `<span class="por-status ${colors[status] || ''}">${text(status || 'Approved')}</span>` + (aging && aging !== '—' ? `<small class="por-muted por-age">In this stage ${aging}</small>` : '');
    }
    function requestDetails(row, panelId) {
        const metadata = [['Source area', row.from_area], ['Warehouse', row.warehouse], ['RIS number', row.ris_number], ['RIS date', row.ris_date_display]]
            .map(([label, value]) => `<div><dt>${label}</dt><dd>${text(value || '—')}</dd></div>`).join('');
        const products = row.products.map(product => {
            const replacement = product.replacement_name && (product.replacement_name !== product.name || product.replacement_sku !== product.sku)
                ? `<small class="por-muted">Replacement: ${text(product.replacement_name)} · ${text(product.replacement_sku)}</small>` : '';
            const quantities = [['Requested', product.qty], ['Approved', product.approved], ['Awaiting release', product.open], ['Received', product.confirmed]]
                .map(([label, qty]) => `<div><dt>${label}</dt><dd>${units(qty)} <small>pcs</small></dd></div>`).join('');
            return `<div class="por-product-detail"><div><strong>${text(product.name)}</strong><small class="por-muted">${text(product.sku)}</small>${replacement}</div><dl class="por-quantities">${quantities}</dl></div>`;
        }).join('');
        const confirmation = Number(row.confirmation_count) > 0 ? `<section class="por-panel por-confirmations"><h3>Confirm receipt</h3><p class="por-muted">Confirm the products you have received for each delivery receipt.</p>${row.ad_confirmation}</section>` : '';
        const aging = [['Pending', row.pending_aging], ['Processing', row.processing_aging], ['Replacing', row.replacing_aging]]
            .map(([label, value]) => `<div><dt>${label}</dt><dd>${value || '—'}</dd></div>`).join('');
        return `<section class="por-details" id="${panelId}" aria-label="Request details"><div class="por-details-main">
            ${confirmation}<section class="por-panel"><h3>Product quantities</h3>${products}</section>
            </div><aside class="por-panel por-context"><h3>Request information</h3><dl class="por-meta">${metadata}</dl>
                <div class="por-context-section"><h4>Time in stage</h4><dl class="por-aging">${aging}</dl></div>
                <div class="por-context-section por-files"><h4>Attachments</h4>${row.attachments}</div>
                <div class="por-context-section"><h4>Remarks</h4><p>${text(row.remarks || '—')}</p></div>
            </aside></section>`;
    }
    const labels = ['Request', 'Distributor', 'Products', 'Status', 'Actions'];
    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 350,
        pageLength: 25,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        pagingType: 'simple_numbers',
        autoWidth: false,
        dom: '<"por-datatable-tools"lf>rt<"por-datatable-footer"ip>',
        order: [[5, 'desc']],
        language: {
            processing: '<i class="bi bi-arrow-repeat me-1"></i> Loading pull-out requests...',
            search: 'Search', searchPlaceholder: 'Reference, distributor, RIS…',
            lengthMenu: 'Show _MENU_',
            info: 'Showing _START_–_END_ of _TOTAL_ requests', infoEmpty: '0 requests',
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
            },
            dataSrc: function (response) {
                $error.addClass('d-none');
                return response.data;
            }
        },
        columns: [
            { data: 'reference_no', name: 'pull_out_rows.reference_no', orderData: [5], render: (value, type, row) => `<strong class="por-reference">${text(value || '—')}</strong><small class="por-muted">${text(row.transfer_date)}</small>` },
            { data: 'distributor', name: 'distributors.name', render: (value, type, row) => `<strong>${text(row.distributor_name || value)}</strong><small class="por-muted">${text(row.from_area)}</small><small class="por-muted por-warehouse">${text(row.warehouse)} warehouse</small>` },
            { data: 'products', orderable: false, searchable: false, render: (value, type, row) => productSummary(value, row) },
            { data: 'approval_status', name: 'pull_out_rows.approval_status', orderable: false, render: statusSummary },
            { data: null, orderable: false, searchable: false, render: (value, type, row) => `<button type="button" class="btn ${Number(row.confirmation_count) > 0 ? 'btn-success' : 'btn-outline-primary'} por-open-request" aria-expanded="false">${Number(row.confirmation_count) > 0 ? 'Review delivery' : 'View details'}</button>${Number(row.confirmation_count) > 0 ? `<small class="por-muted">${units(row.confirmation_count)} awaiting confirmation</small>` : ''}` },
            { data: 'transfer_date', name: 'pull_out_rows.transfer_date', visible: false },
            { data: 'from_area', name: 'pull_out_rows.from_area', visible: false },
            { data: 'warehouse', name: 'pull_out_rows.warehouse', visible: false },
            { data: 'ris_number_display', name: 'pull_out_rows.ris_number', visible: false },
            { data: 'ris_date_display', name: 'pull_out_rows.ris_date', visible: false },
            { data: 'remarks', name: 'pull_out_rows.warehouse_remarks', visible: false }
        ],
        createdRow: function (row) {
            labels.forEach((label, i) => row.cells[i].setAttribute('data-label', label));
        }
    });

    $table.on('click', '.por-open-request', function () {
        const tr = this.closest('tr'), row = table.row(tr), opening = !row.child.isShown();
        if (opening) {
            const panelId = 'por-details-' + row.index();
            if (!row.child()) row.child(requestDetails(row.data(), panelId), 'por-detail-row');
            row.child.show();
            this.setAttribute('aria-controls', panelId);
            this.dataset.closedLabel = this.textContent;
        } else row.child.hide();
        tr.classList.toggle('is-expanded', opening);
        this.setAttribute('aria-expanded', String(opening));
        this.textContent = opening ? 'Close details' : this.dataset.closedLabel;
    });

    $('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').on('change', function () {
        $error.addClass('d-none');
        table.draw();
    });

    $('#clearFilters').on('click', function () {
        $('#statusFilter, #warehouseFilter, #dateFrom, #dateTo').val('');
        $error.addClass('d-none');
        table.search('').draw();
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
