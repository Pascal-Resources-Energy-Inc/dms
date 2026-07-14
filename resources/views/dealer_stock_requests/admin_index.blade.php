@extends('layouts.header')

@section('content')
@php
    $pendingCount = $requests->where('status', 'Pending')->count();
    $approvedCount = $requests->where('status', 'Approved')->count();
    $rejectedCount = $requests->where('status', 'Rejected')->count();
@endphp


    <header class="page-heading">
        <a class="back-link" href="{{ url('account') }}" aria-label="Back to account"><i class="bi bi-arrow-left"></i></a>
        <div>
            <p class="eyebrow">Inventory management</p>
            <h1>Stock request approvals</h1>
            <p class="heading-copy">Review dealer inventory requests and keep stock moving with confidence.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="alert-message alert-success" role="alert"><i class="bi bi-check-circle-fill"></i><span>{{ session('success') }}</span><button type="button" aria-label="Dismiss message"><i class="bi bi-x-lg"></i></button></div>
    @endif
    @if($errors->any())
        <div class="alert-message alert-error" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $errors->first() }}</span><button type="button" aria-label="Dismiss message"><i class="bi bi-x-lg"></i></button></div>
    @endif

    <section class="summary-grid" aria-label="Request summary">
        <div class="summary-card pending"><span class="summary-icon"><i class="bi bi-hourglass-split"></i></span><div><span>Awaiting review</span><strong>{{ $pendingCount }}</strong><small>requests need action</small></div></div>
        <div class="summary-card approved"><span class="summary-icon"><i class="bi bi-check2-circle"></i></span><div><span>Approved</span><strong>{{ $approvedCount }}</strong><small>all completed requests</small></div></div>
        <div class="summary-card rejected"><span class="summary-icon"><i class="bi bi-x-circle"></i></span><div><span>Rejected</span><strong>{{ $rejectedCount }}</strong><small>all completed requests</small></div></div>
    </section>

    <section class="requests-panel" aria-labelledby="requests-title">
        <div class="panel-header">
            <div><h2 id="requests-title">Dealer requests</h2><p>Newest requests are shown first.</p></div>
            <span class="pending-badge"><span></span>{{ $pendingCount }} pending</span>
        </div>
        <div class="request-tools">
            <label class="search-box"><i class="bi bi-search"></i><input id="requestSearch" type="search" placeholder="Search dealer or product" autocomplete="off" aria-label="Search requests"></label>
            <div class="filter-tabs" role="tablist" aria-label="Filter requests">
                <button type="button" class="filter-tab active" data-filter="all" role="tab" aria-selected="true">All <span>{{ $requests->count() }}</span></button>
                <button type="button" class="filter-tab" data-filter="pending" role="tab" aria-selected="false">Pending <span>{{ $pendingCount }}</span></button>
                <button type="button" class="filter-tab" data-filter="approved" role="tab" aria-selected="false">Approved <span>{{ $approvedCount }}</span></button>
                <button type="button" class="filter-tab" data-filter="rejected" role="tab" aria-selected="false">Rejected <span>{{ $rejectedCount }}</span></button>
            </div>
        </div>

        <div class="request-list" id="requestList">
            @forelse($requests as $request)
                @php
                    $dealerName = optional($request->dealer)->name ?? 'Dealer #' . $request->dealer_id;
                    $storeName = optional(optional($request->dealer)->dealer)->store_name ?: 'Dealer account';
                    $productName = optional($request->product)->product_name ?? 'Product #' . $request->product_id;
                    $remarks = $request->status === 'Pending' ? $request->notes : $request->review_notes;
                    $status = strtolower($request->status);
                @endphp
                <article class="request-item" data-status="{{ $status }}" data-search="{{ strtolower($dealerName . ' ' . $storeName . ' ' . $productName) }}">
                    <div class="dealer-avatar" aria-hidden="true">{{ strtoupper(substr($dealerName, 0, 1)) }}</div>
                    <div class="request-details">
                        <div class="request-primary"><div><h3>{{ $dealerName }}</h3><p class="store-name"><i class="bi bi-shop"></i>{{ $storeName }}</p></div><span class="status-pill {{ $status }}"><i class="bi {{ $status === 'pending' ? 'bi-clock' : ($status === 'approved' ? 'bi-check-lg' : 'bi-x-lg') }}"></i>{{ $request->status }}</span></div>
                        <div class="request-meta"><span><i class="bi bi-box-seam"></i>{{ $productName }}</span><span><i class="bi bi-layers"></i><b>{{ number_format($request->quantity) }}</b> units requested</span><span><i class="bi bi-calendar3"></i>{{ optional($request->created_at)->format('M d, Y · g:i A') }}</span></div>
                        @if($remarks)<p class="request-note"><i class="bi bi-chat-left-text"></i><span><b>{{ $request->status === 'Rejected' ? 'Rejection reason:' : 'Note:' }}</b> {{ $remarks }}</span></p>@endif
                        @if($request->status !== 'Pending' && $request->reviewer)<p class="reviewed-by">Reviewed by {{ $request->reviewer->name }}{{ $request->reviewed_at ? ' on ' . $request->reviewed_at->format('M d, Y') : '' }}</p>@endif
                    </div>
                    @if($request->status === 'Pending')
                        <div class="request-actions">
                            <form method="POST" action="{{ route('admin.stock.requests.approve', ['id' => $request->id]) }}" class="approve-form">@csrf<button class="button button-approve" type="submit"><i class="bi bi-check-lg"></i>Approve</button></form>
                            <button class="button button-reject" type="button" data-reject-id="{{ $request->id }}" data-dealer="{{ $dealerName }}" data-product="{{ $productName }}"><i class="bi bi-x-lg"></i>Reject</button>
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state"><span><i class="bi bi-inboxes"></i></span><h3>No dealer stock requests yet</h3><p>Requests from dealers will appear here when submitted.</p></div>
            @endforelse
            <div id="noResults" class="empty-state compact" hidden><span><i class="bi bi-search"></i></span><h3>No matching requests</h3><p>Try a different search or filter.</p></div>
        </div>
    </section>


<div class="dialog-backdrop" id="rejectModal" hidden aria-hidden="true">
    <form id="rejectForm" method="POST" class="decision-dialog" aria-labelledby="rejectTitle">@csrf
        <button type="button" class="dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-lg"></i></button>
        <span class="dialog-icon reject-icon"><i class="bi bi-x-lg"></i></span>
        <h2 id="rejectTitle">Reject stock request?</h2>
        <p id="rejectDescription">Tell the dealer why this request cannot be approved.</p>
        <label for="rejectionRemarks">Reason for rejection <span>*</span></label>
        <textarea id="rejectionRemarks" name="review_notes" maxlength="500" required placeholder="Enter a clear reason for the dealer..."></textarea>
        <div class="character-count"><span id="characterCount">0</span>/500</div>
        <div class="dialog-actions"><button type="button" class="button button-cancel" data-close-dialog>Cancel</button><button class="button button-confirm-reject" type="submit"><i class="bi bi-x-lg"></i>Reject request</button></div>
    </form>
</div>
@endsection

@section('css')
<style>
body.main-layout{background:#f4f7fb!important}.page-heading{display:flex;gap:16px;align-items:flex-start;margin-bottom:26px}.back-link{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;margin-top:3px;border:1px solid #dce7f0;border-radius:12px;background:#fff;color:#316a8e;font-size:19px;text-decoration:none;box-shadow:0 3px 8px rgba(26,52,75,.04);transition:.2s}.back-link:hover{background:#eaf5fb;color:#1479ae;transform:translateX(-2px)}.eyebrow{margin:0 0 5px;color:#1688b3;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.page-heading h1{margin:0;color:#102a43;font-size:28px;font-weight:800;letter-spacing:-.035em}.heading-copy{margin:7px 0 0;color:#6c7a89;font-size:14px}.alert-message{display:flex;gap:10px;align-items:center;margin:0 0 18px;padding:13px 14px;border:1px solid;border-radius:12px;font-size:13px;font-weight:600}.alert-message>i{font-size:17px}.alert-message span{flex:1}.alert-message button{border:0;background:transparent;color:inherit;cursor:pointer}.alert-success{background:#effbf4;border-color:#c8efd8;color:#15734a}.alert-error{background:#fff4f3;border-color:#ffd7d3;color:#b42318}.summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px}.summary-card{display:flex;gap:13px;align-items:center;min-height:104px;padding:17px;border:1px solid #e3eaf2;border-radius:15px;background:#fff;box-shadow:0 7px 20px rgba(22,43,66,.04)}.summary-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:12px;font-size:20px}.summary-card>div{display:grid;line-height:1.15}.summary-card>div>span{color:#728096;font-size:12px;font-weight:600}.summary-card strong{margin:4px 0 3px;color:#172b4d;font-size:25px;font-weight:800}.summary-card small{color:#98a4b3;font-size:11px}.summary-card.pending .summary-icon{background:#fff4dd;color:#c77912}.summary-card.approved .summary-icon{background:#e9f9ef;color:#198754}.summary-card.rejected .summary-icon{background:#fff0ef;color:#d44236}.requests-panel{overflow:hidden;border:1px solid #dfe8f1;border-radius:16px;background:#fff;box-shadow:0 12px 30px rgba(25,57,85,.055)}.panel-header{display:flex;align-items:center;justify-content:space-between;padding:21px 22px 16px}.panel-header h2{margin:0;color:#172b4d;font-size:18px;font-weight:800}.panel-header p{margin:5px 0 0;color:#7b8795;font-size:12px}.pending-badge{display:inline-flex;gap:7px;align-items:center;border-radius:99px;background:#fff6e6;color:#a45d00;padding:7px 10px;font-size:12px;font-weight:800}.pending-badge span{width:7px;height:7px;border-radius:99px;background:#e9951a}.request-tools{display:flex;gap:14px;justify-content:space-between;padding:0 22px 17px;border-bottom:1px solid #eaf0f5}.search-box{display:flex;gap:9px;align-items:center;min-width:235px;padding:0 11px;border:1px solid #dce5ee;border-radius:9px;color:#8795a4;background:#fafcfe}.search-box:focus-within{border-color:#58a6cb;box-shadow:0 0 0 3px #e4f5fc}.search-box input{width:100%;height:38px;border:0;outline:0;background:transparent;color:#25364b;font-size:13px}.filter-tabs{display:flex;gap:4px;align-items:center;overflow-x:auto}.filter-tab{white-space:nowrap;border:0;border-radius:7px;background:transparent;color:#69788a;padding:8px 9px;font-size:12px;font-weight:700;cursor:pointer}.filter-tab span{margin-left:3px;color:#98a5b2;font-size:11px}.filter-tab:hover{background:#f2f7fa}.filter-tab.active{background:#e5f4fa;color:#157aa8}.filter-tab.active span{color:#157aa8}.request-item{display:grid;grid-template-columns:43px minmax(0,1fr) auto;gap:13px;align-items:start;padding:19px 22px;border-bottom:1px solid #edf2f6;transition:background .2s}.request-item:hover{background:#fbfdff}.request-item:last-child{border-bottom:0}.dealer-avatar{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#dff3fb,#c8e6f3);color:#176d96;font-size:14px;font-weight:800}.request-primary{display:flex;justify-content:space-between;gap:15px;align-items:flex-start}.request-primary h3{margin:0;color:#162d4a;font-size:14px;font-weight:800}.store-name{display:flex;gap:5px;align-items:center;margin:4px 0 0;color:#738194;font-size:12px}.store-name i{color:#56a1c4}.status-pill{display:inline-flex;gap:5px;align-items:center;flex:0 0 auto;border-radius:99px;padding:5px 8px;font-size:10px;font-weight:800}.status-pill.pending{background:#fff5e5;color:#ad6505}.status-pill.approved{background:#eaf9f0;color:#168450}.status-pill.rejected{background:#fff0ef;color:#c9372c}.request-meta{display:flex;flex-wrap:wrap;gap:7px 17px;margin-top:12px;color:#5e6e80;font-size:12px}.request-meta span{display:inline-flex;gap:6px;align-items:center}.request-meta i{color:#7fa6ba}.request-meta b{color:#273d57}.request-note{display:flex;gap:7px;margin:10px 0 0;padding:8px 10px;border-left:3px solid #e7ae56;border-radius:0 6px 6px 0;background:#fffaf2;color:#715d42;font-size:12px;line-height:1.5}.request-note i{margin-top:2px;color:#d49734}.reviewed-by{margin:10px 0 0;color:#8b98a5;font-size:11px}.request-actions{display:flex;gap:7px;align-items:center;padding-top:2px}.approve-form{margin:0}.button{display:inline-flex;gap:6px;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;padding:9px 11px;font-size:12px;font-weight:800;line-height:1;cursor:pointer;transition:.2s}.button i{font-size:14px}.button-approve{background:#16834a;color:#fff;box-shadow:0 3px 8px rgba(22,131,74,.18)}.button-approve:hover{background:#106d3d;transform:translateY(-1px)}.button-reject{border-color:#f6d7d3;background:#fff;color:#bb342b}.button-reject:hover{background:#fff2f0}.empty-state{padding:56px 20px;text-align:center}.empty-state>span{display:grid;place-items:center;width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:#edf6fa;color:#5795b4;font-size:22px}.empty-state h3{margin:0;color:#344b63;font-size:15px;font-weight:800}.empty-state p{margin:7px 0 0;color:#8491a0;font-size:12px}.empty-state.compact{border-top:1px solid #edf2f6}.dialog-backdrop{position:fixed;z-index:2000;inset:0;display:grid;place-items:center;padding:20px;background:rgba(13,31,48,.48);backdrop-filter:blur(3px)}.decision-dialog{position:relative;width:min(100%,440px);padding:27px;border-radius:17px;background:#fff;box-shadow:0 24px 70px rgba(0,0,0,.25)}.dialog-close{position:absolute;top:14px;right:14px;border:0;border-radius:6px;background:transparent;color:#8391a0;padding:6px;cursor:pointer}.dialog-close:hover{background:#f2f5f8}.dialog-icon{display:grid;place-items:center;width:42px;height:42px;margin-bottom:14px;border-radius:12px;font-size:19px}.reject-icon{background:#fff0ef;color:#d33c32}.decision-dialog h2{margin:0;color:#1d334b;font-size:19px;font-weight:800}.decision-dialog>p{margin:7px 0 19px;color:#728093;font-size:13px;line-height:1.5}.decision-dialog label{display:block;margin-bottom:7px;color:#344b63;font-size:12px;font-weight:800}.decision-dialog label span{color:#d33c32}.decision-dialog textarea{display:block;resize:vertical;width:100%;min-height:102px;padding:10px;border:1px solid #d9e4ed;border-radius:9px;outline:none;color:#273b52;font:inherit;font-size:13px}.decision-dialog textarea:focus{border-color:#60a9cd;box-shadow:0 0 0 3px #e6f5fb}.character-count{margin:6px 0 18px;text-align:right;color:#98a3af;font-size:11px}.dialog-actions{display:flex;justify-content:flex-end;gap:8px}.button-cancel{border-color:#dce5ed;background:#fff;color:#556779}.button-cancel:hover{background:#f5f8fa}.button-confirm-reject{background:#c7362b;color:#fff}.button-confirm-reject:hover{background:#a92920}.button:disabled{cursor:wait;opacity:.68;transform:none}@media(max-width:760px){.stock-approval-page{padding:25px 15px 36px}.summary-grid{gap:10px}.summary-card{min-height:91px;padding:12px;gap:9px}.summary-icon{width:35px;height:35px;border-radius:10px;font-size:16px}.summary-card strong{font-size:21px}.summary-card small{display:none}.request-tools{display:block}.search-box{width:100%;margin-bottom:10px}.filter-tabs{padding-bottom:2px}.request-item{grid-template-columns:39px minmax(0,1fr);padding:16px}.request-actions{grid-column:2;justify-content:flex-start;padding-top:0}.request-primary{display:block}.status-pill{margin-top:9px}.request-meta{gap:7px 12px}.panel-header{padding:18px 16px 14px}.request-tools{padding:0 16px 14px}}@media(max-width:460px){.page-heading h1{font-size:23px}.summary-grid{grid-template-columns:1fr}.summary-card{min-height:68px}.summary-card small{display:block}.pending-badge{display:none}.request-meta{display:grid;gap:7px}.dialog-actions{display:grid;grid-template-columns:1fr 1fr}.dialog-actions .button{min-height:39px}}
</style>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('requestList'), search = document.getElementById('requestSearch'), noResults = document.getElementById('noResults');
    var activeFilter = 'all', modal = document.getElementById('rejectModal'), form = document.getElementById('rejectForm'), textarea = document.getElementById('rejectionRemarks');
    function filterRequests() { var term = search.value.toLowerCase().trim(), visible = 0; list.querySelectorAll('.request-item').forEach(function (item) { var show = (activeFilter === 'all' || item.dataset.status === activeFilter) && item.dataset.search.indexOf(term) !== -1; item.hidden = !show; if (show) visible++; }); noResults.hidden = visible > 0 || !list.querySelector('.request-item'); }
    search.addEventListener('input', filterRequests);
    document.querySelectorAll('.filter-tab').forEach(function (tab) { tab.addEventListener('click', function () { activeFilter = tab.dataset.filter; document.querySelectorAll('.filter-tab').forEach(function (button) { button.classList.toggle('active', button === tab); button.setAttribute('aria-selected', button === tab ? 'true' : 'false'); }); filterRequests(); }); });
    document.querySelectorAll('[data-reject-id]').forEach(function (button) { button.addEventListener('click', function () { form.action = '{{ url('/stock-requests') }}/' + button.dataset.rejectId + '/reject'; document.getElementById('rejectDescription').textContent = 'Reject the request for ' + button.dataset.product + ' from ' + button.dataset.dealer + '? Tell them why.'; textarea.value = ''; document.getElementById('characterCount').textContent = '0'; modal.hidden = false; modal.setAttribute('aria-hidden', 'false'); textarea.focus(); }); });
    function closeModal() { modal.hidden = true; modal.setAttribute('aria-hidden', 'true'); }
    document.querySelectorAll('[data-close-dialog]').forEach(function (button) { button.addEventListener('click', closeModal); });
    modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !modal.hidden) closeModal(); });
    textarea.addEventListener('input', function () { document.getElementById('characterCount').textContent = textarea.value.length; });
    document.querySelectorAll('.approve-form, #rejectForm').forEach(function (requestForm) { requestForm.addEventListener('submit', function () { var submit = requestForm.querySelector('[type="submit"]'); if (submit) { submit.disabled = true; submit.innerHTML = '<i class="bi bi-arrow-repeat"></i> Processing...'; } }); });
    document.querySelectorAll('.alert-message button').forEach(function (button) { button.addEventListener('click', function () { button.closest('.alert-message').remove(); }); });
});
</script>
@endsection
