@extends('layouts.header')

@section('content')
@php
    $isDennis = strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    $isWarehouse = auth()->user()->role === 'Admin' && filled(auth()->user()->warehouse);
    $isPresident = strtolower(trim((string) auth()->user()->designation)) === 'president';
    $canSubmitCreditMemo = !empty($creditMemoProcessor);
    $requestSummary = [
        'total' => $requests->count(),
        'pending' => $requests->where('approval_status', 'Pending')->count(),
        'processing' => $requests->where('approval_status', 'Processing')->count(),
        'review' => $requests->whereIn('approval_status', ['For Review', 'Reviewed'])->count(),
    ];
@endphp


    <section class="rr-hero">
        <div>
            <h3><i class="bi bi-arrow-repeat me-2"></i>Return &amp; Refund Requests</h3>
            <p>Track approvals, RIS documents, warehouse receiving, credit memos, and final review.</p>
        </div>
        <i class="bi bi-arrow-left-right rr-hero-icon"></i>
    </section>

    <div class="row g-3 mt-1">
        <div class="col-6 col-lg-3"><div class="rr-stat"><small>Total Requests</small><strong>{{ number_format($requestSummary['total']) }}</strong></div></div>
        <div class="col-6 col-lg-3"><div class="rr-stat is-pending"><small>Pending Approval</small><strong>{{ number_format($requestSummary['pending']) }}</strong></div></div>
        <div class="col-6 col-lg-3"><div class="rr-stat is-processing"><small>Processing</small><strong>{{ number_format($requestSummary['processing']) }}</strong></div></div>
        <div class="col-6 col-lg-3"><div class="rr-stat is-review"><small>For Review</small><strong>{{ number_format($requestSummary['review']) }}</strong></div></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <div class="card rr-card border-0">
        <div class="rr-card-heading">
            <div>
                <h5>Transaction Register</h5>
                <small>Review every request, its supporting documents, and the current workflow status.</small>
            </div>
            <span><i class="bi bi-table"></i> Live request data</span>
        </div>
        <div class="row g-3 rr-filter mx-0 mb-3">
            <div class="col-md-4">
                <label for="rrStatusFilter">Status</label>
                <select id="rrStatusFilter" class="form-control">
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
            <div class="col-md-3"><label for="rrDateFrom">From</label><input id="rrDateFrom" type="date" class="form-control"></div>
            <div class="col-md-3"><label for="rrDateTo">To</label><input id="rrDateTo" type="date" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button id="rrClearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear</button></div>
        </div>
        <div class="card-body p-0">
            <div class="rr-table-shell">
                <table id="returnRefundRequestsTable" class="table rr-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Distributor</th>
                            <th>Product</th>
                            <th>RIS / Attachments</th>
                            <th>Credit Memo</th>
                            <th>Status</th>
                            <th class="rr-action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $movement)
                            @php
                                $status = [
                                    'Pending' => ['class' => 'warning', 'icon' => 'bi-hourglass-split', 'label' => 'For approval'],
                                    'Approved' => ['class' => 'primary', 'icon' => 'bi-check-circle', 'label' => 'Approved - upload RIS'],
                                    'Documents Submitted' => ['class' => 'info', 'icon' => 'bi-paperclip', 'label' => 'For receiving'],
                                    'Processing' => ['class' => 'primary', 'icon' => 'bi-arrow-repeat', 'label' => 'Processing'],
                                    'For Review' => ['class' => 'secondary', 'icon' => 'bi-search', 'label' => 'For review'],
                                    'Reviewed' => ['class' => 'info', 'icon' => 'bi-clipboard2-check', 'label' => 'Awaiting President approval'],
                                    'President Approved' => ['class' => 'success', 'icon' => 'bi-patch-check', 'label' => 'President approved'],
                                    'Warehouse Confirmed' => ['class' => 'success', 'icon' => 'bi-check2-circle', 'label' => 'Confirmed'],
                                    'Rejected' => ['class' => 'danger', 'icon' => 'bi-x-circle', 'label' => 'Rejected'],
                                ][$movement->approval_status] ?? ['class' => 'secondary', 'icon' => 'bi-info-circle', 'label' => $movement->approval_status ?: 'Pending'];
                            @endphp
                            <tr data-status="{{ $movement->approval_status }}" data-date="{{ optional($movement->transfer_date)->format('Y-m-d') }}">
                                <td data-label="Request" data-order="{{ optional($movement->created_at)->timestamp ?: 0 }}">
                                    <strong>{{ $movement->reference_no ?: 'Draft request' }}</strong>
                                    <small>{{ optional($movement->transfer_date)->format('M d, Y') ?: 'No request date' }}</small>
                                </td>
                                <td data-label="Distributor">
                                    <strong>{{ optional($movement->creator)->name ?: '-' }}</strong>
                                    <small>{{ optional($movement->creator)->email ?: 'No email address' }}</small>
                                </td>
                                <td data-label="Product">
                                    <strong>{{ $movement->item_name }}</strong>
                                    <small>{{ $movement->sku ?: 'No SKU' }} · {{ number_format($movement->qty) }} pcs</small>
                                    <small><i class="bi bi-geo-alt"></i> {{ $movement->from_area ?: 'No source area' }}</small>
                                </td>
                                <td data-label="RIS / Attachments">
                                    @if($movement->ris_number)
                                        <strong>RIS # {{ $movement->ris_number }}</strong>
                                        <small>{{ optional($movement->return_date)->format('M d, Y') ?: 'No return date' }}</small>
                                    @else
                                        <span class="text-muted small">Available after approval</span>
                                    @endif
                                    @if($movement->return_attachments)
                                        <div class="rr-files">
                                            @foreach($movement->return_attachments as $file)
                                                <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ $file['name'] }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Credit Memo">
                                    @if($movement->credit_memo_reference_no)
                                        <strong>Ref. # {{ $movement->credit_memo_reference_no }}</strong>
                                        <small>{{ optional($movement->credit_memo_submitted_at)->format('M d, Y h:i A') ?: 'Submitted' }}</small>
                                    @else
                                        <span class="text-muted small">{{ $movement->approval_status === 'Processing' ? 'Awaiting credit memo' : '—' }}</span>
                                    @endif
                                    @if($movement->credit_memo_attachments)
                                        <div class="rr-files">
                                            @foreach($movement->credit_memo_attachments as $file)
                                                <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ $file['name'] }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-{{ $status['class'] }} rr-status"><i class="bi {{ $status['icon'] }}"></i> {{ $status['label'] }}</span>
                                    @if($movement->warehouse_reference_no)
                                        <small class="d-block mt-2">SIRR # {{ $movement->warehouse_reference_no }}</small>
                                    @endif
                                </td>
                                <td data-label="Action" class="rr-action-cell">
                                    @if($isDennis && $movement->approval_status === 'Pending')
                                        <form method="POST" action="{{ route('return-refunds.approve', $movement->id) }}" class="rr-form">
                                            @csrf
                                            <input class="form-control form-control-sm mb-2" name="warehouse_remarks" placeholder="Optional remarks">
                                            <button class="btn btn-success btn-sm w-100" name="decision" value="Approved">Approve request</button>
                                        </form>
                                        <form method="POST" action="{{ route('return-refunds.approve', $movement->id) }}" class="rr-form mt-2">
                                            @csrf
                                            <input class="form-control form-control-sm mb-2" name="warehouse_remarks" placeholder="Rejection remarks (optional)">
                                            <button class="btn btn-outline-danger btn-sm w-100" name="decision" value="Rejected">Reject request</button>
                                        </form>
                                    @elseif($isDennis && $movement->approval_status === 'Approved')
                                        <form method="POST" action="{{ route('return-refunds.documents', $movement->id) }}" enctype="multipart/form-data" class="rr-form">
                                            @csrf
                                            <input class="form-control form-control-sm mb-1" name="ris_number" placeholder="RIS #" required>
                                            <input class="form-control form-control-sm mb-1" type="date" name="return_date" required>
                                            <input class="form-control form-control-sm mb-2" type="file" name="return_attachments[]" accept=".jpg,.jpeg,.png,.pdf" multiple required>
                                            <button class="btn btn-primary btn-sm w-100">Submit RIS documents</button>
                                        </form>
                                    @elseif($isWarehouse && $movement->approval_status === 'Documents Submitted')
                                        <form method="POST" action="{{ route('return-refunds.receive', $movement->id) }}" class="rr-form js-return-receive-form" data-requested-qty="{{ $movement->qty }}">
                                            @csrf
                                            <div class="row gx-1">
                                                <div class="col-5">
                                                    <label class="sr-only" for="warehouse_received_qty_{{ $movement->id }}">Received quantity</label>
                                                    <input id="warehouse_received_qty_{{ $movement->id }}" class="form-control form-control-sm" type="number" name="warehouse_received_qty" min="1" max="{{ $movement->qty }}" value="{{ $movement->qty }}" required placeholder="Received qty">
                                                </div>
                                                <div class="col-7">
                                                    <label class="sr-only" for="sirr_number_{{ $movement->id }}">SIRR #</label>
                                                    <input id="sirr_number_{{ $movement->id }}" class="form-control form-control-sm" name="sirr_number" placeholder="SIRR #" required>
                                                </div>
                                            </div>
                                            <input class="form-control form-control-sm mt-1 js-receiving-remarks" name="warehouse_remarks" placeholder="Remarks required if received quantity changes">
                                            <small class="form-text text-muted mb-1">Remarks are required when Received Qty differs from {{ number_format($movement->qty) }} pcs.</small>
                                            <button class="btn btn-primary btn-sm mt-2 w-100">Confirm &amp; email distributor</button>
                                        </form>
                                    @elseif($canSubmitCreditMemo && $movement->approval_status === 'Processing')
                                        <form method="POST" action="{{ route('return-refunds.credit-memo', $movement->id) }}" enctype="multipart/form-data" class="rr-form">
                                            @csrf
                                            <input class="form-control form-control-sm mb-1" name="credit_memo_reference_no" placeholder="Credit memo reference #" required>
                                            <input class="form-control form-control-sm mb-2" type="file" name="credit_memo_attachments[]" accept=".jpg,.jpeg,.png,.pdf" multiple required>
                                            <button class="btn btn-primary btn-sm w-100"><i class="bi bi-file-earmark-check me-1"></i>Submit credit memo</button>
                                        </form>
                                    @elseif($isPresident && $movement->approval_status === 'Reviewed')
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-outline-warning btn-sm flex-fill js-show-revert-form" data-target="presidentRevertForm{{ $movement->id }}" aria-expanded="false">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Revert
                                            </button>
                                            <form method="POST" action="{{ route('return-refunds.president-approve', $movement->id) }}" class="flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check2-circle me-1"></i> Approved</button>
                                            </form>
                                        </div>
                                        <div id="presidentRevertForm{{ $movement->id }}" class="d-none mt-2">
                                            <form method="POST" action="{{ route('return-refunds.president-revert', $movement->id) }}" class="rr-form p-2 border rounded bg-light">
                                                @csrf
                                                <label class="small font-weight-bold mb-1" for="president_revert_to_{{ $movement->id }}">Revert to</label>
                                                <select id="president_revert_to_{{ $movement->id }}" class="form-control form-control-sm mb-2" name="revert_to" required>
                                                    <option value="">Select destination</option>
                                                    <option value="Warehouse">Warehouse</option>
                                                    <option value="Accounting">Accounting</option>
                                                </select>
                                                <small class="form-text text-muted mb-2">Warehouse restarts receiving; Accounting restarts credit-memo submission.</small>
                                                <textarea class="form-control form-control-sm mb-2" name="revert_remarks" rows="2" placeholder="Required revert remarks" required></textarea>
                                                <button class="btn btn-warning btn-sm w-100" type="submit"><i class="bi bi-check2-circle me-1"></i> Confirm revert</button>
                                            </form>
                                        </div>
                                    @elseif(in_array('PQD/IAD', (array) auth()->user()->approval_types, true) || in_array('Accounting', (array) auth()->user()->approval_types, true))
                                        @if($movement->approval_status === 'For Review')
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-outline-warning btn-sm flex-fill js-show-revert-form" data-target="revertForm{{ $movement->id }}" aria-expanded="false">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Revert
                                                </button>
                                                <form method="POST" action="{{ route('return-refunds.reviewed', $movement->id) }}" class="flex-fill">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check2-circle me-1"></i> Reviewed</button>
                                                </form>
                                            </div>
                                            <div id="revertForm{{ $movement->id }}" class="d-none mt-2">
                                                <form method="POST" action="{{ route('return-refunds.revert', $movement->id) }}" class="rr-form p-2 border rounded bg-light">
                                                    @csrf
                                                    <label class="small font-weight-bold mb-1" for="revert_to_{{ $movement->id }}">Revert to</label>
                                                    <select id="revert_to_{{ $movement->id }}" class="form-control form-control-sm mb-2" name="revert_to" required>
                                                        <option value="">Select destination</option>
                                                        <option value="Warehouse">Warehouse</option>
                                                        <option value="Accounting">Accounting</option>
                                                    </select>
                                                    <small class="form-text text-muted mb-2">Warehouse restarts receiving; Accounting restarts credit-memo submission.</small>
                                                    <textarea class="form-control form-control-sm mb-2" name="revert_remarks" rows="2" placeholder="Required revert remarks" required></textarea>
                                                    <button class="btn btn-warning btn-sm w-100" type="submit"><i class="bi bi-check2-circle me-1"></i> Confirm revert</button>
                                                </form>
                                            </div>
                                        @endif
                                    @elseif(in_array($movement->approval_status, ['Processing', 'Warehouse Confirmed']))
                                        <span class="text-success small"><i class="bi bi-envelope-check"></i> Distributor notified</span>
                                    @else
                                        <span class="text-muted small">No action required.</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="rr-empty-row" data-empty-state>
                                <td colspan="7">
                                    <i class="bi bi-inbox me-1"></i>
                                    No Return &amp; Refund requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<style>
    .rr-hero { position: relative; display: flex; align-items: center; justify-content: space-between; overflow: hidden; padding: 26px 28px; color: #fff; border-radius: 18px; background: linear-gradient(135deg,#0f172a,#1d4ed8); box-shadow: 0 18px 36px rgba(15, 61, 94, .18); }
    .rr-hero::after { position: absolute; right: -65px; bottom: -105px; width: 260px; height: 260px; content: ''; border: 42px solid rgba(255, 255, 255, .08); border-radius: 50%; }
    .rr-hero h3 { position: relative; z-index: 1; margin: 0; color: #fff; font-weight: 800; }
    .rr-hero p { position: relative; z-index: 1; margin: 6px 0 0; opacity: .84; }
    .rr-hero-icon { position: relative; z-index: 1; font-size: 32px; opacity: .55; }
    .rr-stat, .rr-card { border: 1px solid #e7edf4; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .04); }
    .rr-stat { height: 100%; padding: 18px; border-radius: 14px; }
    .rr-stat small { display: block; color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .rr-stat strong { display: block; margin-top: 6px; color: #172554; font-size: 28px; line-height: 1; }
    .rr-stat.is-pending strong { color: #d97706; }
    .rr-stat.is-processing strong { color: #0284c7; }
    .rr-stat.is-review strong { color: #7c3aed; }
    .rr-card { margin-top: 20px; padding: 20px; border-radius: 18px; box-shadow: 0 10px 26px rgba(15, 23, 42, .05); }
    .rr-card-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .rr-card-heading h5 { margin: 0; color: #172554; font-weight: 800; }
    .rr-card-heading small { display: block; margin-top: 3px; color: #64748b; }
    .rr-card-heading span { display: inline-flex; gap: 6px; align-items: center; color: #0f766e; font-size: 12px; font-weight: 800; white-space: nowrap; }
    .rr-filter { padding: 15px; border: 1px solid #edf2f7; border-radius: 13px; background: #f8fafc; }
    .rr-filter label { margin-bottom: 5px; color: #475569; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .rr-filter .form-control { height: 40px; border-color: #cbd5e1; border-radius: 9px; background: #fff; }
    .rr-table-shell { overflow: hidden; border: 1px solid #e6edf4; border-radius: 12px; background: #fff; }
    .rr-table strong { display: block; color: #1e293b; font-size: 13px; }
    .rr-table small { display: block; color: #64748b; font-size: 11px; }
    .rr-table small i { color: #0284c7; }
    .rr-status { padding: 5px 8px; font-size: 11px; font-weight: 700; }
    .rr-files { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 5px; }
    .rr-files a { max-width: 145px; overflow: hidden; color: #0284c7; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    #returnRefundRequestsTable { min-width: 1180px; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
    #returnRefundRequestsTable thead th { padding: 13px 12px; color: #475569; font-size: 10px; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-top: 0; border-bottom: 1px solid #dbe3ed; }
    #returnRefundRequestsTable td { padding: 14px 12px; vertical-align: middle; border-color: #edf1f5; }
    #returnRefundRequestsTable tbody tr:hover { background: #f8fbff; }
    .rr-card .dataTables_wrapper { padding: 0; }
    .rr-card .dataTables_scroll { border-top: 1px solid #edf1f5; border-bottom: 1px solid #edf1f5; }
    .rr-card .dataTables_scrollHead { background: #f8fafc; }
    .rr-card .dataTables_scrollBody { scrollbar-color: #94a3b8 transparent; }
    .rr-card .dataTables_scrollBody::-webkit-scrollbar { width: 9px; height: 9px; }
    .rr-card .dataTables_scrollBody::-webkit-scrollbar-thumb { border: 2px solid #fff; border-radius: 99px; background: #94a3b8; }
    .rr-datatable-tools, .rr-datatable-footer { display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 14px 16px; background: #fff; }
    .rr-datatable-tools .dataTables_length, .rr-datatable-tools .dataTables_filter, .rr-datatable-footer .dataTables_info, .rr-datatable-footer .dataTables_paginate { float: none !important; margin: 0 !important; }
    .rr-datatable-tools .dataTables_filter { margin-left: auto !important; text-align: right; }
    .rr-card .dataTables_filter input, .rr-card .dataTables_length select { min-height: 36px; margin-left: 7px; padding: 5px 9px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; outline: none; }
    .rr-card .dataTables_filter input:focus, .rr-card .dataTables_length select:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .12); }
    #returnRefundRequestsTable_wrapper td.dataTables_empty,
    #returnRefundRequestsTable .rr-empty-row td { padding: 42px 16px !important; color: #64748b; font-weight: 600; text-align: center !important; vertical-align: middle; }
    .rr-card .dataTables_paginate .paginate_button { min-width: 34px; height: 34px; margin: 0 2px !important; padding: 7px 10px !important; color: #475569 !important; font-size: 12px; font-weight: 700; line-height: 18px; border: 1px solid transparent !important; border-radius: 8px; background: transparent !important; }
    .rr-card .dataTables_paginate .paginate_button:hover { color: #0f766e !important; border-color: #99f6e4 !important; background: #f0fdfa !important; }
    .rr-card .dataTables_paginate .paginate_button.current, .rr-card .dataTables_paginate .paginate_button.current:hover { color: #fff !important; border-color: #0f766e !important; background: #0f766e !important; }
    .rr-card .dataTables_paginate .paginate_button.disabled, .rr-card .dataTables_paginate .paginate_button.disabled:hover { color: #cbd5e1 !important; background: transparent !important; }
    @media (max-width: 991.98px) { #returnRefundRequestsTable { min-width: 1080px; } }
    @media (max-width: 575.98px) { .rr-hero, .rr-card-heading, .rr-datatable-tools, .rr-datatable-footer { align-items: flex-start; gap: 10px; flex-direction: column; }.rr-hero { padding: 21px; }.rr-hero h3 { font-size: 21px; }.rr-hero p { font-size: 13px; }.rr-hero-icon { display: none; }.rr-card { padding: 14px; border-radius: 14px; }.rr-filter { padding: 12px; }.rr-datatable-tools .dataTables_filter { margin-left: 0 !important; text-align: left; }.rr-datatable-footer .dataTables_paginate { width: 100%; overflow-x: auto; white-space: nowrap; } #returnRefundRequestsTable { min-width: 960px; } }
</style>

@endsection

@section('javascript')
<script src="{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !window.jQuery.fn.DataTable) return;

    const $ = window.jQuery;
    const $table = $('#returnRefundRequestsTable');
    $table.find('[data-empty-state]').remove();

    if ($.fn.DataTable.isDataTable('#returnRefundRequestsTable')) $table.DataTable().destroy();

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'returnRefundRequestsTable') return true;

        const row = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
        if (!row) return true;

        const status = $('#rrStatusFilter').val();
        const from = $('#rrDateFrom').val();
        const to = $('#rrDateTo').val();
        const requestStatus = row.dataset.status || '';
        const requestDate = row.dataset.date || '';

        return (!status || requestStatus === status)
            && (!from || requestDate >= from)
            && (!to || requestDate <= to);
    });

    const table = $table.DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        pagingType: 'simple_numbers',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: '<"rr-datatable-tools"lf>rt<"rr-datatable-footer"ip>',
        order: [[0, 'desc']],
        columnDefs: [{ targets: 6, orderable: false, searchable: false }],
        language: {
            search: 'Search requests:',
            lengthMenu: 'Show _MENU_ requests',
            emptyTable: 'No Return & Refund requests found.',
            zeroRecords: 'No matching requests found.'
        }
    });

    $('#rrStatusFilter, #rrDateFrom, #rrDateTo').on('change', function () {
        table.draw();
    });

    $('#rrClearFilters').on('click', function () {
        $('#rrStatusFilter, #rrDateFrom, #rrDateTo').val('');
        table.draw();
    });
});

document.addEventListener('input', function (event) {
    const quantityInput = event.target.closest('.js-return-receive-form [name="warehouse_received_qty"]');
    if (!quantityInput) return;

    const form = quantityInput.closest('.js-return-receive-form');
    const remarks = form.querySelector('.js-receiving-remarks');
    const quantityChanged = Number(quantityInput.value || 0) !== Number(form.dataset.requestedQty || 0);
    remarks.required = quantityChanged;
    remarks.placeholder = quantityChanged ? 'Remarks are required for changed quantity' : 'Optional remarks';
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-show-revert-form');
    if (!button) return;

    const form = document.getElementById(button.dataset.target);
    if (!form) return;

    const isHidden = form.classList.contains('d-none');
    form.classList.toggle('d-none', !isHidden);
    button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    button.innerHTML = isHidden
        ? '<i class="bi bi-x-lg me-1"></i> Cancel revert'
        : '<i class="bi bi-arrow-counterclockwise me-1"></i> Revert';
});
</script>
@endsection
