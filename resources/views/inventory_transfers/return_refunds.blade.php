@extends('layouts.header')

@section('content')
@php
    $pendingCount = $requests->where('approval_status', 'Pending')->count();
    $documentsCount = $requests->where('approval_status', 'Documents Submitted')->count();
    $confirmedCount = $requests->where('approval_status', 'Warehouse Confirmed')->count();
    $rejectedCount = $requests->where('approval_status', 'Rejected')->count();
    $isDennis = strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    $isWarehouse = auth()->user()->role === 'Admin' && filled(auth()->user()->warehouse);
@endphp

<div class="container-fluid py-4 return-refund-queue">
    <div class="rr-hero mb-4">
        <div><div class="rr-eyebrow"><i class="bi bi-arrow-return-left me-1"></i> Inventory operations</div><h4 class="mb-1">Return &amp; Refund Requests</h4><p class="mb-0">Track approvals, documents, and warehouse receiving in one place.</p></div>
        <div class="rr-hero-icon"><i class="bi bi-box-arrow-in-left"></i></div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-xl-3 mb-3"><div class="rr-kpi is-pending"><span><i class="bi bi-hourglass-split"></i></span><div><small>For approval</small><strong>{{ number_format($pendingCount) }}</strong></div></div></div>
        <div class="col-6 col-xl-3 mb-3"><div class="rr-kpi is-documents"><span><i class="bi bi-paperclip"></i></span><div><small>For receiving</small><strong>{{ number_format($documentsCount) }}</strong></div></div></div>
        <div class="col-6 col-xl-3 mb-3"><div class="rr-kpi is-confirmed"><span><i class="bi bi-check2-circle"></i></span><div><small>Confirmed</small><strong>{{ number_format($confirmedCount) }}</strong></div></div></div>
        <div class="col-6 col-xl-3 mb-3"><div class="rr-kpi is-rejected"><span><i class="bi bi-x-circle"></i></span><div><small>Rejected</small><strong>{{ number_format($rejectedCount) }}</strong></div></div></div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>@endif

    <div class="card rr-card border-0">
        <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between"><div><h5 class="mb-1">Request queue</h5><p class="text-muted small mb-0">{{ $isDennis ? 'Approve or reject submitted requests.' : ($isWarehouse ? 'Confirm returns after documents are submitted.' : 'Review the current workflow.') }}</p></div><span class="rr-total">{{ number_format($requests->count()) }} request{{ $requests->count() === 1 ? '' : 's' }}</span></div>
        <div class="card-body pt-0"><div class="table-responsive"><table id="returnRefundRequestsTable" class="table rr-table align-middle mb-0">
            <thead><tr><th>Request</th><th>Distributor</th><th>Product</th><th>Return details</th><th>Status</th><th class="rr-action-column">Action</th></tr></thead>
            <tbody>
            @forelse($requests as $movement)
                @php
                    $statusMeta = [
                        'Pending' => ['class' => 'is-pending', 'icon' => 'bi-hourglass-split', 'label' => 'For approval'],
                        'Approved' => ['class' => 'is-approved', 'icon' => 'bi-check-circle', 'label' => 'Awaiting documents'],
                        'Documents Submitted' => ['class' => 'is-documents', 'icon' => 'bi-paperclip', 'label' => 'For receiving'],
                        'Warehouse Confirmed' => ['class' => 'is-confirmed', 'icon' => 'bi-check2-circle', 'label' => 'Warehouse confirmed'],
                        'Rejected' => ['class' => 'is-rejected', 'icon' => 'bi-x-circle', 'label' => 'Rejected'],
                    ][$movement->approval_status] ?? ['class' => 'is-neutral', 'icon' => 'bi-info-circle', 'label' => $movement->approval_status ?: 'Pending'];
                @endphp
                <tr>
                    <td data-label="Request" data-order="{{ optional($movement->created_at)->timestamp ?: 0 }}"><strong>{{ $movement->reference_no ?: 'Draft request' }}</strong><small>{{ optional($movement->transfer_date)->format('M d, Y') ?: 'No request date' }}</small></td>
                    <td data-label="Distributor"><strong>{{ optional($movement->creator)->name ?: '-' }}</strong><small>{{ optional($movement->creator)->email ?: 'No email address' }}</small></td>
                    <td data-label="Product"><strong>{{ $movement->item_name }}</strong><small>{{ $movement->sku ?: 'No SKU' }} · {{ number_format($movement->qty) }} pcs</small><span class="rr-area"><i class="bi bi-geo-alt"></i>{{ $movement->from_area ?: 'No source area' }}</span></td>
                    <td data-label="Return details">
                        @if($movement->ris_number)<strong>RIS # {{ $movement->ris_number }}</strong><small>{{ optional($movement->return_date)->format('M d, Y') ?: 'No return date' }}</small>@else <span class="rr-muted">RIS and files pending</span>@endif
                        @if($movement->return_attachments)<div class="rr-files">@foreach($movement->return_attachments as $file)<a href="{{ asset($file['path']) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i>{{ $file['name'] }}</a>@endforeach</div>@endif
                    </td>
                    <td data-label="Status"><span class="rr-status {{ $statusMeta['class'] }}"><i class="bi {{ $statusMeta['icon'] }}"></i>{{ $statusMeta['label'] }}</span>@if($movement->warehouse_reference_no)<small class="d-block mt-2">Warehouse ref. <strong>{{ $movement->warehouse_reference_no }}</strong></small>@endif@if($movement->warehouse_remarks)<small class="d-block text-muted mt-1">{{ $movement->warehouse_remarks }}</small>@endif</td>
                    <td data-label="Action" class="rr-action-cell">
                        @if($isDennis && $movement->approval_status === 'Pending')
                            <form method="POST" action="{{ route('return-refunds.approve', $movement->id) }}" class="rr-form">@csrf<label for="approval_remarks_{{ $movement->id }}">Review remarks</label><input id="approval_remarks_{{ $movement->id }}" class="form-control form-control-sm" name="warehouse_remarks" placeholder="Optional remarks"><div class="rr-buttons"><button class="btn btn-success btn-sm" name="decision" value="Approved"><i class="bi bi-check-lg"></i> Approve</button><button class="btn btn-outline-danger btn-sm" name="decision" value="Rejected">Reject</button></div></form>
                        @elseif($isWarehouse && $movement->approval_status === 'Documents Submitted')
                            <form method="POST" action="{{ route('return-refunds.receive', $movement->id) }}" class="rr-form">@csrf<label>Warehouse receiving</label><div class="row gx-1"><div class="col-5"><input class="form-control form-control-sm" type="number" name="warehouse_received_qty" min="1" max="{{ $movement->qty }}" value="{{ $movement->qty }}" required aria-label="Received quantity"></div><div class="col-7"><input class="form-control form-control-sm" name="warehouse_reference_no" placeholder="Reference #" required></div></div><input class="form-control form-control-sm mt-1" name="warehouse_remarks" placeholder="Optional remarks"><button class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-check2-square"></i> Confirm &amp; email distributor</button></form>
                        @elseif($movement->approval_status === 'Warehouse Confirmed')
                            <div class="rr-complete"><i class="bi bi-envelope-check"></i> Distributor notified</div>
                        @else
                            <span class="rr-muted">{{ $movement->approval_status === 'Approved' ? 'Waiting for distributor documents.' : 'No action required.' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No Return and Refund requests found.</td></tr>
            @endforelse
            </tbody>
        </table></div></div>
    </div>
</div>

<style>
.return-refund-queue{color:#1f2937}.rr-hero{display:flex;justify-content:space-between;align-items:center;padding:25px 27px;color:#fff;background:linear-gradient(135deg,#0f172a,#075985);border-radius:15px;box-shadow:0 14px 30px rgba(7,89,133,.18)}.rr-eyebrow{margin-bottom:5px;color:#bae6fd;font-size:11px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.rr-hero h4,.rr-card h5{font-weight:800}.rr-hero p{color:#e0f2fe}.rr-hero-icon{display:grid;place-items:center;width:56px;height:56px;border-radius:15px;background:rgba(255,255,255,.14);font-size:27px}.rr-kpi{display:flex;align-items:center;gap:13px;height:100%;padding:16px 18px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 5px 14px rgba(15,23,42,.05)}.rr-kpi>span{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;font-size:19px}.rr-kpi small,.rr-table small{display:block;color:#64748b;font-size:11px;font-weight:700}.rr-kpi strong{display:block;font-size:25px;line-height:1.1}.rr-kpi.is-pending>span{color:#92400e;background:#fef3c7}.rr-kpi.is-documents>span{color:#075985;background:#e0f2fe}.rr-kpi.is-confirmed>span{color:#166534;background:#dcfce7}.rr-kpi.is-rejected>span{color:#b91c1c;background:#fee2e2}.rr-card{overflow:hidden;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.07)}.rr-card .card-header{padding:19px 21px}.rr-total{padding:6px 10px;color:#075985;background:#e0f2fe;border-radius:999px;font-size:12px;font-weight:800}.rr-table thead th{padding:13px 14px;color:#64748b;background:#f8fafc;border-top:1px solid #edf0f5;border-bottom:1px solid #e5e7eb;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}.rr-table tbody td{padding:16px 14px;border-color:#edf0f5}.rr-table tbody tr:hover{background:#fafcff}.rr-table strong{display:block;color:#1e293b;font-size:13px}.rr-action-column{min-width:285px}.rr-area{display:inline-flex;align-items:center;gap:4px;margin-top:6px;color:#475569;font-size:11px;font-weight:700}.rr-area i{color:#0284c7}.rr-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:11px;font-weight:800;white-space:nowrap}.rr-status.is-pending{color:#92400e;background:#fef3c7}.rr-status.is-approved{color:#1d4ed8;background:#dbeafe}.rr-status.is-documents{color:#075985;background:#e0f2fe}.rr-status.is-confirmed{color:#166534;background:#dcfce7}.rr-status.is-rejected{color:#b91c1c;background:#fee2e2}.rr-muted{color:#94a3b8;font-size:12px}.rr-files{display:flex;flex-wrap:wrap;gap:5px;margin-top:6px}.rr-files a{max-width:145px;overflow:hidden;color:#0284c7;font-size:11px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.rr-form label{display:block;margin-bottom:4px;color:#64748b;font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.rr-buttons{display:flex;gap:7px;margin-top:8px}.rr-complete{color:#15803d;font-size:12px;font-weight:700}.return-refund-queue .dataTables_wrapper{padding-top:16px}.return-refund-queue .dataTables_filter{text-align:right}.return-refund-queue .dataTables_filter input,.return-refund-queue .dataTables_length select{min-height:34px;border:1px solid #dbe2ea;border-radius:7px}.return-refund-queue .dataTables_info{color:#64748b;font-size:12px}@media(max-width:991.98px){.rr-table{min-width:940px}.rr-action-column{min-width:280px}}@media(max-width:767.98px){.rr-hero{padding:20px}.rr-hero-icon{width:46px;height:46px;font-size:22px}.return-refund-queue .dataTables_filter{text-align:left;margin-top:10px}.rr-card .card-header{padding:17px}}@media(max-width:575.98px){.return-refund-queue{padding-right:12px;padding-left:12px}.rr-hero{align-items:flex-start;gap:14px;padding:18px}.rr-hero h4{font-size:19px}.rr-kpi{padding:13px}.rr-kpi strong{font-size:22px}.rr-card{overflow:visible}.rr-table{min-width:0}.rr-table thead{display:none}.rr-table,.rr-table tbody,.rr-table tr,.rr-table td{display:block;width:100%}.rr-table tbody tr{margin-bottom:12px;overflow:hidden;background:#fff;border:1px solid #e5e7eb;border-radius:10px}.rr-table tbody td{display:block;padding:11px 13px;border:0;border-bottom:1px solid #f1f5f9}.rr-table tbody td:before{display:block;margin-bottom:4px;color:#64748b;content:attr(data-label);font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.rr-action-cell{background:#fafcff}.return-refund-queue .dataTables_wrapper .row>div{width:100%;max-width:100%}.return-refund-queue .dataTables_length,.return-refund-queue .dataTables_filter{margin-bottom:10px;text-align:left}.return-refund-queue .dataTables_filter input{width:calc(100% - 115px)}}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){if(!window.jQuery)return;var init=function(){if(!window.jQuery.fn||!window.jQuery.fn.DataTable)return;var table=window.jQuery('#returnRefundRequestsTable');if(window.jQuery.fn.DataTable.isDataTable('#returnRefundRequestsTable'))table.DataTable().destroy();table.DataTable({pageLength:10,lengthMenu:[[10,25,50,-1],[10,25,50,'All']],order:[[0,'desc']],autoWidth:false,columnDefs:[{targets:5,orderable:false,searchable:false}],language:{search:'Search requests:',lengthMenu:'Show _MENU_ requests',emptyTable:'No Return and Refund requests found.',zeroRecords:'No matching requests found.'}})};if(window.jQuery.fn&&window.jQuery.fn.DataTable){init();return}var script=document.createElement('script');script.src="{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}";script.onload=init;document.head.appendChild(script)});
</script>
@endsection
