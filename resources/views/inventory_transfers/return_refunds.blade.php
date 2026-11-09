@extends('layouts.header')

@section('content')
@php
    $isDennis = strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    $isWarehouse = auth()->user()->role === 'Admin' && filled(auth()->user()->warehouse);
    $isPresident = strtolower(trim((string) auth()->user()->designation)) === 'president';
    $canSubmitCreditMemo = !empty($creditMemoProcessor);
    $requestSummary = [
        'total' => $requests->count(),
        'pending' => $requests->filter(function ($request) { return $request->requestLines->contains('approval_status', 'Pending'); })->count(),
        'processing' => $requests->filter(function ($request) { return $request->requestLines->contains('approval_status', 'Processing'); })->count(),
        'review' => $requests->filter(function ($request) { return $request->requestLines->whereIn('approval_status', ['For Review', 'Reviewed'])->isNotEmpty(); })->count(),
    ];
@endphp


<div class="rr-page">
    <header class="rr-page-heading">
        <h3>Return &amp; Refund Requests</h3>
        <p>Review returned products, documents, and approval progress.</p>
    </header>
    <div class="rr-summary" aria-label="Request summary">
        <div class="rr-stat"><small>Total requests</small><strong>{{ number_format($requestSummary['total']) }}</strong></div>
        <div class="rr-stat is-pending"><small>Pending approval</small><strong>{{ number_format($requestSummary['pending']) }}</strong></div>
        <div class="rr-stat is-processing"><small>Processing</small><strong>{{ number_format($requestSummary['processing']) }}</strong></div>
        <div class="rr-stat is-review"><small>For review</small><strong>{{ number_format($requestSummary['review']) }}</strong></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button class="close" type="button" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="rr-card">
        <div class="rr-card-heading">
            <div>
                <h5>Requests</h5>
                <small>Open a request to review and manage its products.</small>
            </div>
            <span>{{ number_format($requests->count()) }} {{ $requests->count() === 1 ? 'request' : 'requests' }}</span>
        </div>
        <div class="rr-filter">
            <div class="rr-filter-search"><label for="rrSearch">Search requests</label><input id="rrSearch" type="search" class="form-control" placeholder="Reference, distributor or product" autocomplete="off"></div>
            <div class="rr-filter-status">
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
            <div class="rr-filter-date"><label for="rrDateFrom">From date</label><input id="rrDateFrom" type="date" class="form-control"></div>
            <div class="rr-filter-date"><label for="rrDateTo">To date</label><input id="rrDateTo" type="date" class="form-control"></div>
            <div class="rr-filter-clear"><button id="rrClearFilters" type="button" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear</button></div>
        </div>
        <div class="rr-register">
            <div class="rr-table-shell">
                <table id="returnRefundRequestsTable" class="table rr-table align-middle mb-0"
                    data-datatables-src="{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"
                    data-datatables-fallback-src="{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Distributor</th>
                            <th>Products</th>
                            <th>Progress</th>
                            <th class="rr-action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $requestGroup)
                            @php
                                $lines = $requestGroup->requestLines;
                                $statuses = $lines->groupBy('approval_status');
                            @endphp
                            <tr data-statuses="{{ json_encode($statuses->keys()->all()) }}" data-date="{{ optional($requestGroup->transfer_date)->format('Y-m-d') }}">
                                <td data-label="Request" data-order="{{ optional($requestGroup->created_at)->timestamp ?: 0 }}">
                                    <strong>{{ $requestGroup->reference_no ?: 'Request #' . $requestGroup->id }}</strong>
                                    <small>{{ optional($requestGroup->transfer_date)->format('M d, Y') ?: 'No request date' }}</small>
                                    <small>{{ ucfirst($requestGroup->warehouse ?: 'Unassigned warehouse') }}</small>
                                </td>
                                <td data-label="Distributor"><strong>{{ optional($requestGroup->creator)->name ?: '—' }}</strong><small>{{ optional($requestGroup->creator)->email }}</small></td>
                                <td data-label="Products" data-search="{{ $lines->map(function ($line) { return implode(' ', [$line->item_name, $line->sku, $line->from_area, $line->ris_number, $line->credit_memo_reference_no, $line->warehouse_reference_no, $line->remarks, $line->warehouse_remarks]); })->implode(' ') }}">
                                    @foreach($lines as $line)
                                        <div class="rr-product-summary"><span>{{ $line->item_name }}</span><b>{{ number_format($line->qty) }} pcs</b></div>
                                    @endforeach
                                    <small>{{ $lines->count() }} {{ $lines->count() === 1 ? 'product' : 'products' }} · {{ number_format($lines->sum('qty')) }} pcs total</small>
                                </td>
                                <td data-label="Progress">
                                    @foreach($statuses as $stage => $stageLines)
                                        <span class="rr-stage {{ $stage === 'Pending' ? 'is-pending' : '' }}">{{ $stage ?: 'Pending' }} · {{ $stageLines->count() }}</span>
                                    @endforeach
                                </td>
                                <td data-label="Actions"><button type="button" class="btn btn-outline-primary btn-sm rr-view-products" data-template="rrDetails{{ $requestGroup->id }}" aria-expanded="false" aria-controls="rrContent{{ $requestGroup->id }}">View products</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($requests as $requestGroup)
    <template id="rrDetails{{ $requestGroup->id }}"><div id="rrContent{{ $requestGroup->id }}">@include('inventory_transfers.partials.return_refund_details')</div></template>
@endforeach
<link rel="stylesheet" href="{{ asset('css/return-refund-requests.css') }}?v={{ filemtime(public_path('css/return-refund-requests.css')) }}">


@endsection

@section('javascript')
<script src="{{ asset('js/return-refund-requests.js') }}?v={{ filemtime(public_path('js/return-refund-requests.js')) }}"></script>
@endsection
