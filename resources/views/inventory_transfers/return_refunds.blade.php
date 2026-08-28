@extends('layouts.header')

@section('content')
@php
    $isDennis = strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    $isWarehouse = auth()->user()->role === 'Admin' && filled(auth()->user()->warehouse);
@endphp

<div class="container-fluid py-4 return-refund-page">
    <div class="rr-page-header">
        <div>
            <h4>Return &amp; Refund Requests</h4>
            <p>Review approvals, return documents, and warehouse receiving.</p>
        </div>
        <span class="rr-count">{{ number_format($requests->count()) }} request{{ $requests->count() === 1 ? '' : 's' }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <div class="card rr-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="returnRefundRequestsTable" class="table rr-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Distributor</th>
                            <th>Product</th>
                            <th>RIS / Attachments</th>
                            <th>Status</th>
                            <th class="rr-action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $movement)
                            @php
                                $status = [
                                    'Pending' => ['class' => 'warning', 'icon' => 'bi-hourglass-split', 'label' => 'For approval'],
                                    'Approved' => ['class' => 'info', 'icon' => 'bi-check-circle', 'label' => 'Awaiting documents'],
                                    'Documents Submitted' => ['class' => 'info', 'icon' => 'bi-paperclip', 'label' => 'For receiving'],
                                    'Warehouse Confirmed' => ['class' => 'success', 'icon' => 'bi-check2-circle', 'label' => 'Confirmed'],
                                    'Rejected' => ['class' => 'danger', 'icon' => 'bi-x-circle', 'label' => 'Rejected'],
                                ][$movement->approval_status] ?? ['class' => 'secondary', 'icon' => 'bi-info-circle', 'label' => $movement->approval_status ?: 'Pending'];
                            @endphp
                            <tr>
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
                                        <span class="text-muted small">Not submitted</span>
                                    @endif
                                    @if($movement->return_attachments)
                                        <div class="rr-files">
                                            @foreach($movement->return_attachments as $file)
                                                <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ $file['name'] }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-{{ $status['class'] }} rr-status"><i class="bi {{ $status['icon'] }}"></i> {{ $status['label'] }}</span>
                                    @if($movement->warehouse_reference_no)
                                        <small class="d-block mt-2">Ref. # {{ $movement->warehouse_reference_no }}</small>
                                    @endif
                                </td>
                                <td data-label="Action" class="rr-action-cell">
                                    @if($isDennis && $movement->approval_status === 'Pending')
                                        <form method="POST" action="{{ route('return-refunds.approve', $movement->id) }}" class="rr-form">
                                            @csrf
                                            <input class="form-control form-control-sm mb-2" name="warehouse_remarks" placeholder="Optional remarks">
                                            <button class="btn btn-success btn-sm" name="decision" value="Approved">Approve</button>
                                            <button class="btn btn-outline-danger btn-sm" name="decision" value="Rejected">Reject</button>
                                        </form>
                                    @elseif($isWarehouse && $movement->approval_status === 'Documents Submitted')
                                        <form method="POST" action="{{ route('return-refunds.receive', $movement->id) }}" class="rr-form">
                                            @csrf
                                            <div class="row gx-1">
                                                <div class="col-5"><input class="form-control form-control-sm" type="number" name="warehouse_received_qty" min="1" max="{{ $movement->qty }}" value="{{ $movement->qty }}" required aria-label="Received quantity"></div>
                                                <div class="col-7"><input class="form-control form-control-sm" name="warehouse_reference_no" placeholder="Reference #" required></div>
                                            </div>
                                            <input class="form-control form-control-sm mt-1" name="warehouse_remarks" placeholder="Optional remarks">
                                            <button class="btn btn-primary btn-sm mt-2 w-100">Confirm &amp; email distributor</button>
                                        </form>
                                    @elseif($movement->approval_status === 'Warehouse Confirmed')
                                        <span class="text-success small"><i class="bi bi-envelope-check"></i> Distributor notified</span>
                                    @else
                                        <span class="text-muted small">{{ $movement->approval_status === 'Approved' ? 'Waiting for documents.' : 'No action required.' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No Return and Refund requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.return-refund-page{color:#334155}.rr-page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}.rr-page-header h4{margin:0;font-weight:700}.rr-page-header p{margin:4px 0 0;color:#64748b;font-size:13px}.rr-count{padding:6px 10px;color:#0369a1;background:#e0f2fe;border-radius:999px;font-size:12px;font-weight:700}.rr-card{overflow:hidden;border-radius:10px;box-shadow:0 4px 18px rgba(15,23,42,.07)}.rr-table thead th{padding:13px 14px;color:#64748b;background:#f8fafc;border-top:0;border-bottom:1px solid #e5e7eb;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}.rr-table td{padding:15px 14px;border-color:#eef2f7}.rr-table tbody tr:hover{background:#fafcff}.rr-table strong{display:block;color:#1e293b;font-size:13px}.rr-table small{display:block;color:#64748b;font-size:11px}.rr-table small i{color:#0284c7}.rr-status{padding:5px 8px;font-size:11px;font-weight:700}.rr-action-column{min-width:245px}.rr-files{display:flex;flex-wrap:wrap;gap:4px;margin-top:5px}.rr-files a{max-width:145px;overflow:hidden;color:#0284c7;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.return-refund-page .dataTables_wrapper{padding:16px}.return-refund-page .dataTables_filter{text-align:right}.return-refund-page .dataTables_filter input,.return-refund-page .dataTables_length select{min-height:34px;border:1px solid #dbe2ea;border-radius:7px}.return-refund-page .dataTables_info{color:#64748b;font-size:12px}.return-refund-page .dataTables_paginate .paginate_button{padding:0!important;margin-left:4px}@media(max-width:991px){.rr-table{min-width:900px}}@media(max-width:575px){.return-refund-page{padding-right:12px;padding-left:12px}.rr-page-header{align-items:flex-start;gap:12px}.rr-page-header h4{font-size:19px}.rr-count{white-space:nowrap}.rr-card{overflow:visible}.rr-table{min-width:0}.rr-table thead{display:none}.rr-table,.rr-table tbody,.rr-table tr,.rr-table td{display:block;width:100%}.rr-table tbody tr{margin-bottom:12px;overflow:hidden;border:1px solid #e5e7eb;border-radius:9px}.rr-table td{padding:11px 13px;border:0;border-bottom:1px solid #f1f5f9}.rr-table td:before{display:block;margin-bottom:4px;color:#64748b;content:attr(data-label);font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.rr-action-cell{background:#fafcff}.return-refund-page .dataTables_wrapper{padding:12px 0}.return-refund-page .dataTables_wrapper .row>div{width:100%;max-width:100%}.return-refund-page .dataTables_length,.return-refund-page .dataTables_filter{margin-bottom:10px;text-align:left}.return-refund-page .dataTables_filter input{width:calc(100% - 115px)}}
</style>

<script>
document.addEventListener('DOMContentLoaded',async function(){if(!window.jQuery)return;async function loadDataTables(){if(window.jQuery.fn&&window.jQuery.fn.DataTable)return true;var sources=["{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}","{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js'];for(var i=0;i<sources.length;i++){try{await new Promise(function(resolve,reject){var script=document.createElement('script');script.src=sources[i];script.onload=resolve;script.onerror=reject;document.head.appendChild(script)});if(window.jQuery.fn&&window.jQuery.fn.DataTable)return true}catch(error){}}return false}if(!await loadDataTables())return;var table=window.jQuery('#returnRefundRequestsTable');if(window.jQuery.fn.DataTable.isDataTable('#returnRefundRequestsTable'))table.DataTable().destroy();table.DataTable({pageLength:10,lengthMenu:[[10,25,50,-1],[10,25,50,'All']],order:[[0,'desc']],autoWidth:false,columnDefs:[{targets:5,orderable:false,searchable:false}],language:{search:'Search requests:',lengthMenu:'Show _MENU_ requests',emptyTable:'No Return and Refund requests found.',zeroRecords:'No matching requests found.'}})});
</script>
@endsection
