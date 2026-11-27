@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
<link rel="stylesheet" href="{{ asset('design/vendors/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('design/assets/css/inventory-movement.css') }}">
@endsection

@section('content')
<div class="inventory-module">
    <header class="inventory-head">
        <div class="inventory-heading"><div><h4>Inventory Movement</h4><p>Review stock movements and available quantities by area.</p></div></div>
        <button type="button" class="btn btn-primary" id="openMovementForm" data-bs-toggle="modal" data-bs-target="#newMovementModal"><i class="ti ti-plus" aria-hidden="true"></i> New Movement</button>
    </header>
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

    <dl class="inventory-summary" aria-label="Inventory totals">
        @foreach([
            ['total_in', 'Stock in'],
            ['total_out', 'Stock out'],
            ['total_transfer', 'Transferred'],
            ['ending_stock', 'Available stock'],
        ] as $metric)
            <div class="inventory-metric"><dt>{{ $metric[1] }}</dt><dd>{{ number_format($summary[$metric[0]] ?? 0) }}</dd></div>
        @endforeach
    </dl>

    <section class="inventory-card inventory-history" aria-labelledby="movementHistoryHeading">
        <div class="card-header">
            <div class="inventory-panel-heading"><div><h5 id="movementHistoryHeading">Movement History</h5><p>View products and progress for each transaction.</p></div></div>
            <form id="movementHistoryFilters" class="inventory-filters" method="GET" action="{{ route('inventory-transfers.index') }}">
                <div class="filter-wide"><label for="historySearch">Search movements</label><input type="search" id="historySearch" class="form-control" placeholder="Reference, product, SKU or area"></div>
                <div><label for="historyMovementType">Movement type</label><select name="movement_type" id="historyMovementType" class="form-select"><option value="">All types</option>@foreach(['in'=>'Stock in','out'=>'Stock out','transfer'=>'Transfer'] as $value=>$label)<option value="{{ $value }}" {{ request('movement_type') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                <div class="filter-wide"><label for="historyProduct">Product</label><select name="product_id" id="historyProduct" class="form-select"><option value="">All products</option>@foreach($stockProducts->concat($products)->unique('id')->sortBy('product_name') as $product)<option value="{{ $product->id }}" {{ (string)request('product_id') === (string)$product->id ? 'selected' : '' }}>{{ $product->product_name }}{{ $product->sku ? ' · '.$product->sku : '' }}</option>@endforeach</select></div>
                <div><label for="historyArea">Area</label><select name="area" id="historyArea" class="form-select"><option value="">All areas</option>@foreach($areas as $area)<option value="{{ $area }}" {{ request('area') === $area ? 'selected' : '' }}>{{ $area }}</option>@endforeach</select></div>
                <div class="filter-wide"><label for="historyDateFrom">From date</label><input type="date" name="date_from" id="historyDateFrom" class="form-control" value="{{ request('date_from') }}"></div>
                <div class="filter-wide"><label for="historyDateTo">To date</label><input type="date" name="date_to" id="historyDateTo" class="form-control" value="{{ request('date_to') }}"></div>
                <div class="filter-actions filter-wide"><button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Apply filters</button><button type="button" class="btn btn-outline-secondary" id="clearMovementFilters">Clear</button></div>
            </form>
        </div>
        <div class="inventory-table-error" id="movementHistoryError" role="alert" hidden>Movement history could not be loaded. <button type="button" class="btn btn-sm btn-outline-primary" data-retry-table="history">Retry</button></div>
        <div class="inventory-table-container"><table class="table inventory-table align-middle" id="movementHistoryTable" aria-label="Movement history"><thead><tr><th>Date</th><th>Type</th><th>Products / Quantity</th><th>From</th><th>To</th><th>Reference No.</th><th>Action</th></tr></thead><tbody></tbody></table></div>
    </section>

    <section class="inventory-card" aria-labelledby="stockBalanceHeading">
        <div class="card-header">
            <div class="inventory-panel-heading"><div><h5 id="stockBalanceHeading">Current Stock by Area</h5><p>Stock from completed orders and recorded movements.</p></div><span class="inventory-panel-meta">{{ $areas->count() }} assigned {{ $areas->count() === 1 ? 'area' : 'areas' }}</span></div>
            <div class="balance-tools"><div><label for="balanceSearch">Search stock</label><input type="search" id="balanceSearch" class="form-control" placeholder="Area, product or SKU"></div><div><label for="balanceStatusFilter">Stock status</label><select id="balanceStatusFilter" class="form-select"><option value="">All stock levels</option><option value="good">Good stock</option><option value="low">Low stock</option><option value="none">No stock</option></select></div></div>
        </div>
        <div class="inventory-table-error" id="stockBalanceError" role="alert" hidden>Stock balances could not be loaded. <button type="button" class="btn btn-sm btn-outline-primary" data-retry-table="stock">Retry</button></div>
        <div class="inventory-table-container"><table class="table inventory-table align-middle" id="stockBalanceTable" aria-label="Current stock by area"><thead><tr><th>Area</th><th>Product</th><th>Stock after movement</th><th>Sales orders</th><th>Available</th><th>Status</th></tr></thead><tbody></tbody></table></div>
    </section>
    <details class="inventory-insights"><summary><span>Stock insights</span><span class="small text-muted">Area and product breakdown <i class="ti ti-chevron-down ms-2" aria-hidden="true"></i></span></summary><div class="inventory-insights-body">
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
                                <span class="badge {{ $areaSummary->qty <= 0 ? 'bg-danger' : ($areaSummary->qty <= 10 ? 'bg-warning text-dark' : 'bg-success') }} px-3 py-2">
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


</div></details>
    @include('inventory_transfers.partials.new_movement_modal')
    <div class="modal fade movement-details-modal" id="movementDetailsModal" tabindex="-1" role="dialog" aria-labelledby="movementDetailsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header movement-modal-header">
                    <div><div class="movement-modal-eyebrow">Inventory Ledger</div><h5 class="modal-title" id="movementDetailsModalTitle"><i class="bi bi-journal-text me-1"></i> Movement Details</h5></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body movement-modal-body">
                    <div class="movement-modal-reference"><span>Reference No.</span><strong id="movementDetailReference">—</strong></div>
                    <div class="row movement-details-grid">
                        <div class="col-md-4"><div class="movement-detail-field"><small>Date</small><strong id="movementDetailDate">—</strong></div></div>
                        <div class="col-md-4"><div class="movement-detail-field"><small>Movement Type</small><strong id="movementDetailType">—</strong></div></div>
                        <div class="col-md-4"><div class="movement-detail-field"><small>Type / Reason</small><strong id="movementDetailReason">—</strong></div></div>
                        <div class="col-md-6"><div class="movement-detail-field"><small>From Area</small><strong id="movementDetailFromArea">—</strong></div></div>
                        <div class="col-md-6"><div class="movement-detail-field"><small>To Area</small><strong id="movementDetailToArea">—</strong></div></div>
                        <div class="col-md-6"><div class="movement-detail-field"><small>Total Quantity</small><strong id="movementDetailQuantity">—</strong></div></div>
                        <div class="col-md-6"><div class="movement-detail-field"><small>Unit Cost</small><strong id="movementDetailUnitCost">—</strong></div></div>
                        <div class="col-12"><div class="movement-detail-section"><div class="movement-detail-section-title"><i class="bi bi-box-seam"></i> Products &amp; Quantities</div><div id="movementDetailProducts" class="movement-detail-products"></div></div></div>
                        <div id="movementReplacementWorkflow" class="col-12 d-none">
                            <div class="movement-detail-section replacement-workflow">
                                <div class="movement-detail-section-title"><i class="bi bi-arrow-repeat"></i> Pull Out Replacement</div>
                                <div class="row">
                                    <div class="col-md-4"><div class="movement-detail-field"><small>Status</small><strong id="movementDetailWorkflowStatus">—</strong></div></div>
                                    <div class="col-md-4"><div class="movement-detail-field"><small>Requested Qty</small><strong id="movementDetailRequestedQuantity">—</strong></div></div>
                                    <div class="col-md-4"><div class="movement-detail-field"><small>Approved Qty</small><strong id="movementDetailApprovedQuantity">—</strong></div></div>
                                    <div class="col-md-4"><div class="movement-detail-field"><small>DR #</small><strong id="movementDetailDrNumber">—</strong></div></div>
                                    <div class="col-md-8"><div class="movement-detail-field"><small>Warehouse Remarks</small><strong id="movementDetailWarehouseRemarks">—</strong></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12"><div class="movement-detail-section"><div class="movement-detail-section-title"><i class="bi bi-chat-left-text"></i> Remarks</div><div id="movementDetailRemarks" class="movement-detail-copy">—</div></div></div>
                        <div class="col-12"><div class="movement-detail-section"><div class="movement-detail-section-title"><i class="bi bi-paperclip"></i> Attachments</div><div id="movementDetailAttachments" class="movement-detail-attachments text-muted">No supporting files attached.</div></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
<script src="{{ asset('design/vendors/bootstrap-select/bootstrap-select.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const balanceLookup = @json($balanceLookup ?? []);
        const singleArea = @json($areas->count() === 1 ? $areas->first() : null);
        const stockProducts = @json($productOptions ?? []);
        const availableBalanceProducts = @json($availableBalanceProductOptions ?? []);
        const movementInputs = document.querySelectorAll('input[name="movement_type"]');
        const movementOptions = document.querySelectorAll('.movement-type-option');
        const productSelect = document.getElementById('productSelect');
        const fromAreaSelect = document.getElementById('fromAreaSelect');
        const toAreaSelect = document.getElementById('toAreaSelect');
        const outAdjustmentNote = document.querySelector('.js-out-adjustment-note');
        const returnRefundNote = document.querySelector('.js-return-refund-note');
        const fromArea = document.querySelector('.js-from-area');
        const toArea = document.querySelector('.js-to-area');
        const movementReason = document.querySelector('.js-movement-reason');
        const outTypeSelect = document.getElementById('outTypeSelect');
        const pullOutAttachments = document.querySelector('.js-pull-out-attachments');
        const pullOutAttachmentsInput = document.getElementById('pullOutAttachments');
        const pullOutRis = document.querySelector('.js-pull-out-ris');
        const pullOutRisNumber = document.getElementById('pullOutRisNumber');
        const pullOutRisDate = document.getElementById('pullOutRisDate');
        const transferDateGroup = document.querySelector('.js-transfer-date');
        const transferDateInput = document.getElementById('transferDateInput');
        const movementReasonLabel = document.getElementById('movementReasonLabel');
        const movementReasonHelp = document.getElementById('movementReasonHelp');
        const availableQty = document.getElementById('availableQty');
        const stockWarning = document.getElementById('stockWarning');
        const movementPreview = document.getElementById('movementPreview');
        const movementRows = document.getElementById('movementRows');
        const addMovementRow = document.getElementById('addMovementRow');
        const singleProductGroup = document.querySelector('.js-single-product');
        const balanceSearch = document.getElementById('balanceSearch');
        const balanceStatusFilter = document.getElementById('balanceStatusFilter');
        const appUrl = @json(url('/'));
        let stockBalanceTable = null;
        let historyTable = null;
        let balanceSearchTimer;
        let historySearchTimer;
        let syncingMovementFields = false;
        let activeProducts = [];
        let renderedProductKey = null;
        let movementRowSequence = 0;
        const movementReasons = {
            in: ['Beginning Balance', 'Inventory Adjustment'],
            out: ['Return and Refund', 'Pull Out', 'Replace', 'Inventory Adjustment']
        };

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

        function escapeCell(value) {
            const span = document.createElement('span');
            span.textContent = value == null ? '' : String(value);
            return span.innerHTML;
        }

        function showInventoryModal(id) {
            const modal = document.getElementById(id);
            if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
            } else if (window.jQuery && window.jQuery.fn.modal) {
                window.jQuery(modal).modal('show');
            }
        }

        function tableFailure(id, tableId) {
            document.getElementById(id).hidden = false;
            const wrapper = document.getElementById(tableId + '_wrapper');
            if (wrapper) wrapper.querySelectorAll('.dataTables_processing').forEach(function (node) { node.style.display = 'none'; });
            document.getElementById(tableId).setAttribute('aria-busy', 'false');
        }

        function initializeInventoryTables() {
            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                tableFailure('movementHistoryError', 'movementHistoryTable');
                tableFailure('stockBalanceError', 'stockBalanceTable');
                return;
            }
            const $ = window.jQuery;
            const commonOptions = {
                processing: true, serverSide: true, deferRender: true, autoWidth: false,
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                searchDelay: 350, pagingType: 'simple_numbers',
                dom: 'rt<"inventory-table-footer"<"inventory-table-meta"li>p>',
                language: {
                    processing: '<span class="inventory-loading" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Loading records…</span>',
                    lengthMenu: '_MENU_ per page', info: '_START_–_END_ of _TOTAL_ records',
                    infoEmpty: '0 records', infoFiltered: '(filtered from _MAX_)',
                    emptyTable: 'No records yet.', zeroRecords: 'No matching records. Try changing your filters.',
                    paginate: { previous: 'Previous', next: 'Next' }
                }
            };
            const historyLabels = ['Date', 'Type', 'Products / Quantity', 'From', 'To', 'Reference No.', 'Action'];
            historyTable = $('#movementHistoryTable').DataTable($.extend(true, {}, commonOptions, {
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('inventory-transfers.data') }}',
                    data: function (data) {
                        ['movement_type', 'product_id', 'area', 'date_from', 'date_to'].forEach(function (field) {
                            data[field] = $('#movementHistoryFilters [name="' + field + '"]').val();
                        });
                        document.getElementById('movementHistoryError').hidden = true;
                    },
                    error: function () { tableFailure('movementHistoryError', 'movementHistoryTable'); }
                },
                columns: [
                    {data:'transfer_date', name:'inventory_transfers.transfer_date'},
                    {data:'movement_type_display', name:'inventory_transfers.movement_type'},
                    {data:'products', orderable:false, searchable:false},
                    {data:'from_area', name:'inventory_transfers.from_area', defaultContent:'—'},
                    {data:'to_area', name:'inventory_transfers.to_area', defaultContent:'—'},
                    {data:'reference_no', name:'inventory_transfers.reference_no', defaultContent:'—'},
                    {data:'action', orderable:false, searchable:false}
                ],
                createdRow: function (row) {
                    $(row).find('td').each(function (index) { this.setAttribute('data-label', historyLabels[index]); });
                }
            }));
            const stockLabels = ['Area', 'Product', 'Stock after movement', 'Sales orders', 'Available', 'Status'];
            const quantityCell = function (value, type) {
                return type === 'display' ? '<span class="stock-pill">' + Number(value || 0).toLocaleString() + ' pcs</span>' : value;
            };
            stockBalanceTable = $('#stockBalanceTable').DataTable($.extend(true, {}, commonOptions, {
                order: [[0, 'asc']],
                ajax: {
                    url: '{{ route('inventory-transfers.stock-data') }}',
                    data: function (data) {
                        data.stock_status = balanceStatusFilter.value;
                        document.getElementById('stockBalanceError').hidden = true;
                    },
                    error: function () { tableFailure('stockBalanceError', 'stockBalanceTable'); }
                },
                columns: [
                    {data:'area', render:$.fn.dataTable.render.text()},
                    {data:'product_name', render:function (value, type, row) {
                        return type === 'display' ? '<strong>' + escapeCell(value) + '</strong><div class="small text-muted mt-1">' + escapeCell(row.sku || 'No SKU') + '</div>' : value;
                    }},
                    {data:'stock_after_movement', className:'inventory-number', render:quantityCell},
                    {data:'ordered_qty', className:'inventory-number', render:quantityCell},
                    {data:'available_qty', className:'inventory-number', render:function (value, type) {
                        return type === 'display' ? '<span class="remaining-qty' + (Number(value) < 0 ? ' text-danger' : '') + '">' + Number(value || 0).toLocaleString() + '<small>pcs</small></span>' : value;
                    }},
                    {data:'status', render:function (value, type) {
                        const labels = {good:'Good stock', low:'Low stock', none:'No stock'};
                        return type === 'display' ? '<span class="status-badge status-' + value + '">' + labels[value] + '</span>' : value;
                    }}
                ],
                createdRow: function (row) {
                    $(row).find('td').each(function (index) { this.setAttribute('data-label', stockLabels[index]); });
                }
            }));
            $('#movementHistoryTable, #stockBalanceTable').on('processing.dt', function (event, settings, processing) {
                this.setAttribute('aria-busy', processing ? 'true' : 'false');
            });
            $('#movementHistoryFilters').on('submit', function (event) {
                event.preventDefault(); historyTable.search(document.getElementById('historySearch').value).draw();
            });
            document.getElementById('historySearch').addEventListener('input', function () {
                clearTimeout(historySearchTimer);
                historySearchTimer = setTimeout(function () { historyTable.search(document.getElementById('historySearch').value).draw(); }, 350);
            });
            document.getElementById('clearMovementFilters').addEventListener('click', function () {
                document.getElementById('historySearch').value = '';
                document.querySelectorAll('#movementHistoryFilters select, #movementHistoryFilters input[type="date"]').forEach(function (field) { field.value = ''; });
                historyTable.search('').draw();
            });
            document.querySelectorAll('[data-retry-table]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const table = button.dataset.retryTable === 'history' ? historyTable : stockBalanceTable;
                    if (table) table.ajax.reload();
                });
            });
        }
        initializeInventoryTables();
        @if($errors->any())
            showInventoryModal('newMovementModal');
        @endif

        function optionText(product) {
            return (product.sku ? product.sku + ' - ' : '') + product.name;
        }

        function productOptionsForType(type) {
            if (type === 'in') {
                return stockProducts;
            }

            const sourceArea = fromAreaSelect.value;

            return availableBalanceProducts.filter(function(product) {
                if (sourceArea) {
                    return Number(balanceLookup[product.id + '|' + sourceArea] || 0) > 0;
                }

                return Object.keys(balanceLookup).some(function(key) {
                    return key.indexOf(product.id + '|') === 0 && Number(balanceLookup[key] || 0) > 0;
                });
            });
        }

        function renderProductOptions() {
            const type = selectedType();
            const sourceArea = type === 'in' ? '' : fromAreaSelect.value;
            const renderKey = type + '|' + sourceArea;

            if (renderedProductKey === renderKey) {
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
                const available = sourceArea ? Number(balanceLookup[product.id + '|' + sourceArea] || 0) : null;
                option.textContent = available === null
                    ? optionText(product)
                    : optionText(product) + ' (Available: ' + available.toLocaleString() + ' pcs)';

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
                    : 'No products with available stock' + (sourceArea ? ' in ' + sourceArea : '');
                productSelect.appendChild(option);
            }

            if (!currentValueExists) {
                productSelect.value = '';
            }

            productSelect.dataset.selectedValue = '';
            renderedProductKey = renderKey;
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

        function renderMovementReasons(type) {
            const reasons = movementReasons[type] || [];
            const selectedValue = outTypeSelect.value || outTypeSelect.dataset.selectedValue || '';

            movementReason.style.display = reasons.length ? '' : 'none';
            outTypeSelect.disabled = !reasons.length;
            outTypeSelect.required = reasons.length > 0;
            outTypeSelect.innerHTML = '<option value="">Select type</option>';
            reasons.forEach(function (reason) {
                const option = document.createElement('option');
                option.value = reason;
                option.textContent = reason;
                option.selected = reason === selectedValue;
                outTypeSelect.appendChild(option);
            });
            if (!reasons.includes(selectedValue)) {
                outTypeSelect.value = '';
            }
            outTypeSelect.dataset.selectedValue = '';
            movementReasonLabel.textContent = type === 'in' ? 'IN Type' : 'OUT Type';
            movementReasonHelp.textContent = type === 'in' ? 'Choose why stock is being added to this area.' : 'Choose why stock is being removed from this area.';
            syncSelect2(outTypeSelect);
        }

        function syncPullOutAttachments() {
            const isPullOut = selectedType() === 'out' && outTypeSelect.value === 'Pull Out';
            pullOutAttachments.style.display = isPullOut ? '' : 'none';
            pullOutAttachmentsInput.disabled = !isPullOut;
            pullOutAttachmentsInput.required = isPullOut;

            if (!isPullOut) {
                pullOutAttachmentsInput.value = '';
            }
        }

        function syncPullOutRis() {
            const isPullOut = selectedType() === 'out' && outTypeSelect.value === 'Pull Out';
            pullOutRis.style.display = isPullOut ? '' : 'none';
            pullOutRisNumber.disabled = !isPullOut;
            pullOutRisDate.disabled = !isPullOut;
            pullOutRisNumber.required = isPullOut;
            pullOutRisDate.required = isPullOut;
            transferDateGroup.style.display = isPullOut ? 'none' : '';
            transferDateInput.disabled = isPullOut;

            if (!isPullOut) {
                pullOutRisNumber.value = '';
            }
        }

        function syncOutAdjustmentState() {
            const isOutAdjustment = selectedType() === 'out' && outTypeSelect.value === 'Inventory Adjustment';
            outAdjustmentNote.style.display = isOutAdjustment ? '' : 'none';
        }

        function syncReturnRefundState() {
            const isReturnRefund = selectedType() === 'out' && outTypeSelect.value === 'Return and Refund';
            returnRefundNote.style.display = isReturnRefund ? '' : 'none';
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

            stockWarning.classList.remove('show');
            stockWarning.textContent = '';
        }

        function updatePreview() {
            const selected = Array.from(movementRows.querySelectorAll('.js-movement-row')).filter(function (row) {
                return Boolean(row.querySelector('[name$="[product_id]"]').value);
            });
            const quantity = selected.reduce(function (total, row) {
                return total + Math.max(0, Number(row.querySelector('[name$="[qty]"]').value || 0));
            }, 0);
            movementPreview.textContent = selected.length
                ? selected.length + (selected.length === 1 ? ' product' : ' products') + ' · ' + quantity.toLocaleString() + (quantity === 1 ? ' unit' : ' units')
                : 'Select products and quantities.';
        }

        function refreshMovementRows() {
            singleProductGroup.style.display = 'none';
            movementRows.parentElement.style.display = '';
            productSelect.disabled = true;
            productSelect.required = false;
            updateMovementRowAvailability();
        }

        function updateMovementRowAvailability() {
            const requiresStock = ['out', 'transfer'].includes(selectedType());

            movementRows.querySelectorAll('.js-movement-row').forEach(function (row) {
                const productField = row.querySelector('[name$="[product_id]"]');
                const qtyField = row.querySelector('[name$="[qty]"]');
                const availability = row.querySelector('.js-row-availability');
                const hasProduct = Boolean(productField.value);
                const available = Number(balanceLookup[(productField.value || '') + '|' + fromAreaSelect.value] || 0);
                const invalid = requiresStock && Number(qtyField.value || 0) > available;

                qtyField.disabled = !hasProduct;
                qtyField.placeholder = '0';

                availability.innerHTML = !hasProduct
                    ? 'Select a product.'
                    : requiresStock
                    ? 'Available: <strong>' + available.toLocaleString() + ' pcs</strong>'
                    : 'Stock will be added to the receiving area.';
                availability.className = 'form-text js-row-availability ' + (!hasProduct ? 'text-muted' : (invalid ? 'text-danger fw-semibold' : 'text-success'));
                row.classList.toggle('border-danger', invalid);
                row.classList.toggle('bg-danger-subtle', invalid);
                qtyField.setCustomValidity(invalid ? 'Quantity exceeds available stock.' : '');
            });
            updatePreview();
        }

        function syncMovementRowProductOptions() {
            const options = Array.prototype.map.call(productSelect.options, function (option) {
                return { value: option.value, label: option.textContent, disabled: option.disabled };
            });

            movementRows.querySelectorAll('[name$="[product_id]"]').forEach(function (select) {
                const selectedValue = select.value;
                // Recreate the picker so removed stock options cannot remain in its cache.
                destroyMovementProductPicker(select);
                select.innerHTML = '';
                options.forEach(function (option) {
                    const element = document.createElement('option');
                    element.value = option.value;
                    element.textContent = option.label;
                    element.disabled = option.disabled;
                    select.appendChild(element);
                });
                select.value = Array.prototype.some.call(select.options, function (option) {
                    return option.value === selectedValue;
                }) ? selectedValue : '';
                initializeMovementProductPicker(select);
            });
        }

        function closeMovementProductPicker(select) {
            if (!window.jQuery) return;
            const picker = window.jQuery(select).data('selectpicker');
            if (!picker) return;
            if (picker.dropdown && typeof picker.dropdown.hide === 'function') picker.dropdown.hide();
            else picker.close();
        }

        function destroyMovementProductPicker(select) {
            if (!window.jQuery) return;
            const $select = window.jQuery(select);
            const picker = $select.data('selectpicker');
            if (!picker) return;
            closeMovementProductPicker(select);
            if (picker.dropdown && typeof picker.dropdown.dispose === 'function') picker.dropdown.dispose();
            $select.selectpicker('destroy');
        }

        function initializeMovementProductPicker(select) {
            if (!window.jQuery || !window.jQuery.fn.selectpicker) return;

            const emptyOption = select.querySelector('option[value=""]');
            if (emptyOption) emptyOption.hidden = true;
            const $select = window.jQuery(select);
            if ($select.data('selectpicker')) return;
            $select.addClass('selectpicker').selectpicker({
                liveSearch: true,
                liveSearchNormalize: true,
                liveSearchPlaceholder: 'Search product name or SKU',
                noneResultsText: 'No products found for {0}',
                title: 'Select product',
                container: '#newMovementModal',
                width: '100%',
                size: 'auto',
                windowPadding: 16,
                style: 'btn-movement-product',
                hideDisabled: true
            });
            $select.data('selectpicker').$button.attr({ 'aria-label': 'Product', 'aria-required': 'true' });
            $select.on('shown.bs.select', function () {
                const picker = $select.data('selectpicker');
                picker.$searchbox.attr('aria-label', 'Search product name or SKU').trigger('focus');
            });
        }

        function addMovementProductRow() {
            const row = document.createElement('div');
            row.className = 'js-movement-row movement-item-grid';
            const index = movementRows.querySelectorAll('.js-movement-row').length;
            const rowId = 'movement-product-' + (++movementRowSequence);
            row.innerHTML = '<div class="movement-item-product"><label class="form-label" for="' + rowId + '">Product</label><select id="' + rowId + '" name="items[' + index + '][product_id]" class="form-control selectpicker js-movement-product-select" data-live-search="true" required></select><div class="form-text js-row-availability"></div></div>' +
                '<div class="movement-item-quantity"><label class="form-label" for="' + rowId + '-qty">Quantity</label><input id="' + rowId + '-qty" type="number" name="items[' + index + '][qty]" min="1" step="1" inputmode="numeric" class="form-control" placeholder="0" required></div>' +
                '<div class="movement-item-action"><button type="button" class="btn btn-outline-danger js-delete-movement-row" aria-label="Remove product row" title="Remove product"><i class="ti ti-trash" aria-hidden="true"></i></button></div>';
            const rowSelect = row.querySelector('select');
            Array.from(productSelect.options).forEach(function (option) { rowSelect.appendChild(option.cloneNode(true)); });
            rowSelect.value = '';
            row.querySelector('.js-delete-movement-row').addEventListener('click', function () {
                destroyMovementProductPicker(rowSelect);
                row.remove();
                Array.prototype.forEach.call(movementRows.querySelectorAll('.js-movement-row'), function (movementRow, rowIndex) {
                    movementRow.querySelectorAll('[name]').forEach(function (field) {
                        field.name = field.name.replace(/items\[\d+\]/, 'items[' + rowIndex + ']');
                    });
                });
                refreshMovementRows();
            });
            row.querySelector('[name$="[product_id]"]').addEventListener('change', updateMovementRowAvailability);
            row.querySelector('[name$="[qty]"]').addEventListener('input', updateMovementRowAvailability);
            movementRows.appendChild(row);
            initializeMovementProductPicker(rowSelect);
            refreshMovementRows();
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

            const needsFromArea = type === 'out' || type === 'transfer';
            const needsToArea = type === 'in' || type === 'transfer';
            const needsMovementReason = type === 'in' || type === 'out';

            fromArea.style.display = needsFromArea ? '' : 'none';
            toArea.style.display = needsToArea ? '' : 'none';

            fromAreaSelect.disabled = !needsFromArea;
            fromAreaSelect.required = needsFromArea;
            toAreaSelect.disabled = !needsToArea;
            toAreaSelect.required = needsToArea;

            if (!needsFromArea && !singleArea) {
                setSelectValue(fromAreaSelect, '');
            }

            if (!needsToArea && !singleArea) {
                setSelectValue(toAreaSelect, '');
            }

            // Keep the source area selected whenever the OUT tab is active.
            if (singleArea && type === 'out') {
                setSelectValue(fromAreaSelect, singleArea);
            }

            // Keep the receiving area selected whenever the IN tab is active.
            if (singleArea && type === 'in') {
                setSelectValue(toAreaSelect, singleArea);
            }

            renderMovementReasons(needsMovementReason ? type : '');
            syncOutAdjustmentState();
            syncReturnRefundState();
            syncPullOutAttachments();
            syncPullOutRis();
            refreshMovementRows();

            if (type === 'transfer' && fromAreaSelect.value && fromAreaSelect.value === toAreaSelect.value) {
                setSelectValue(toAreaSelect, '');
            }

            renderProductOptions();
            syncMovementRowProductOptions();
            updateMovementRowAvailability();
            updateActiveType();
            preventSameArea();
            updateAvailableQty();
            updatePreview();
            syncingMovementFields = false;
        }

        function filterBalanceRows() {
            if (stockBalanceTable) stockBalanceTable.search(balanceSearch.value).draw();
        }

        movementInputs.forEach(function (input) {
            input.addEventListener('change', refreshAreaFields);
        });

        movementOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                const input = option.querySelector('input[name="movement_type"]');

                if (!input || input.disabled || input.checked) {
                    return;
                }

                input.checked = true;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        [productSelect, fromAreaSelect, toAreaSelect].forEach(function (element) {
            element.addEventListener('input', refreshAreaFields);
            element.addEventListener('change', refreshAreaFields);
            element.addEventListener('keyup', refreshAreaFields);
        });

        outTypeSelect.addEventListener('change', syncPullOutAttachments);
        outTypeSelect.addEventListener('change', syncPullOutRis);
        outTypeSelect.addEventListener('change', syncOutAdjustmentState);
        outTypeSelect.addEventListener('change', syncReturnRefundState);
        outTypeSelect.addEventListener('change', refreshMovementRows);
        addMovementRow.addEventListener('click', addMovementProductRow);

        function parseMovementData(value, fallback) {
            try {
                return JSON.parse(value || '[]');
            } catch (error) {
                return fallback;
            }
        }

        function populateMovementDetails(button) {
            const details = {
                date: button.dataset.date,
                type: button.dataset.type,
                reason: button.dataset.reason,
                fromArea: button.dataset.fromArea,
                toArea: button.dataset.toArea,
                quantity: button.dataset.quantity,
                unitCost: button.dataset.unitCost,
                reference: button.dataset.reference,
                remarks: button.dataset.remarks,
                workflowStatus: button.dataset.workflowStatus,
                requestedQuantity: button.dataset.requestedQuantity,
                approvedQuantity: button.dataset.approvedQuantity,
                drNumber: button.dataset.drNumber,
                warehouseRemarks: button.dataset.warehouseRemarks
            };

            Object.keys(details).forEach(function (key) {
                const detailElement = document.getElementById('movementDetail' + key.charAt(0).toUpperCase() + key.slice(1));
                if (detailElement) detailElement.textContent = details[key] || '—';
            });

            const productList = document.getElementById('movementDetailProducts');
            const fallbackProduct = { name: button.dataset.product || 'Product', sku: button.dataset.sku || '—', qty: button.dataset.quantity || '—' };
            const products = parseMovementData(button.dataset.products, [fallbackProduct]);
            productList.innerHTML = '';
            (products.length ? products : [fallbackProduct]).forEach(function (product) {
                const row = document.createElement('div');
                row.className = 'movement-detail-product';
                const identity = document.createElement('div');
                const name = document.createElement('strong');
                name.textContent = product.name || fallbackProduct.name;
                const sku = document.createElement('small');
                sku.textContent = product.sku || 'No SKU';
                if (product.open !== undefined) {
                    sku.textContent += ' · ' + product.open + ' awaiting release · ' + product.approved + ' approved · ' + product.confirmed + ' received';
                }
                identity.appendChild(name);
                identity.appendChild(sku);
                const quantity = document.createElement('span');
                quantity.className = 'movement-detail-qty';
                quantity.textContent = (product.qty || '—') + ' pcs';
                row.appendChild(identity);
                row.appendChild(quantity);
                productList.appendChild(row);
            });

            document.getElementById('movementReplacementWorkflow').classList.toggle(
                'd-none',
                button.dataset.isReplacementWorkflow !== '1'
            );

            const attachmentList = document.getElementById('movementDetailAttachments');
            const attachments = parseMovementData(button.dataset.attachments, []);
            attachmentList.innerHTML = '';
            if (!attachments.length) {
                attachmentList.textContent = 'No supporting files attached.';
                attachmentList.classList.add('text-muted');
                return;
            }

            attachmentList.classList.remove('text-muted');
            attachments.forEach(function (attachment) {
                const path = typeof attachment === 'object' ? attachment.path : attachment;
                const name = typeof attachment === 'object' ? (attachment.name || path) : path;
                if (!path) return;
                const link = document.createElement('a');
                link.href = appUrl + '/' + String(path).replace(/^\/+/, '');
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'movement-detail-attachment';
                link.innerHTML = '<i class="bi bi-paperclip"></i> ';
                link.appendChild(document.createTextNode(name));
                attachmentList.appendChild(link);
            });
        }

        // DataTables replaces rows after every server-side draw, so this
        // delegated handler always prepares the modal from the clicked row.
        document.addEventListener('click', function (event) {
            const button = event.target.closest('#movementHistoryTable .js-view-movement');
            if (button) { populateMovementDetails(button); showInventoryModal('movementDetailsModal'); }
        });

        if (window.jQuery) {
            window.jQuery('#newMovementModal').on('hide.bs.modal', function () {
                window.jQuery(movementRows).find('select.js-movement-product-select').each(function () {
                    closeMovementProductPicker(this);
                });
            });
            window.jQuery(productSelect).on('change select2:select select2:clear', refreshAreaFields);
            window.jQuery(fromAreaSelect).on('change select2:select select2:clear', refreshAreaFields);
            window.jQuery(toAreaSelect).on('change select2:select select2:clear', refreshAreaFields);
            window.jQuery(outTypeSelect).on('change select2:select select2:clear', syncPullOutAttachments);
            window.jQuery(outTypeSelect).on('change select2:select select2:clear', syncPullOutRis);
            window.jQuery(outTypeSelect).on('change select2:select select2:clear', syncOutAdjustmentState);
            window.jQuery(outTypeSelect).on('change select2:select select2:clear', syncReturnRefundState);
        }

        const movementForm = document.getElementById('newMovementForm');
        const saveMovementButton = document.getElementById('saveMovementButton');
        let isSavingMovement = false;

        movementForm.addEventListener('submit', function (event) {
            if (isSavingMovement) {
                event.preventDefault();
                return;
            }

            const type = selectedType();
            const movementRows = document.querySelectorAll('.js-movement-row');

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

            if (!movementRows.length) { event.preventDefault(); addMovementProductRow(); return; }

            updateMovementRowAvailability();
            const invalidRow = Array.prototype.find.call(movementRows, function (row) {
                return !row.querySelector('[name$="[qty]"]').checkValidity();
            });
            if (invalidRow) {
                event.preventDefault();
                invalidRow.querySelector('[name$="[qty]"]').reportValidity();
                return;
            }

            if (!movementForm.checkValidity()) {
                return;
            }

            isSavingMovement = true;
            saveMovementButton.disabled = true;
            saveMovementButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span><span>Saving Movement...</span>';
        });

        balanceSearch.addEventListener('input', function () { clearTimeout(balanceSearchTimer); balanceSearchTimer = setTimeout(filterBalanceRows, 350); });
        balanceStatusFilter.addEventListener('change', filterBalanceRows);

        refreshAreaFields();
        if (!movementRows.querySelector('.js-movement-row')) {
            addMovementProductRow();
        }
    });
</script>
@endsection
