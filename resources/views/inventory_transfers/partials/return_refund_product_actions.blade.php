@if($isDennis && $movement->approval_status === 'Pending')
    <details class="rr-reject"><summary>Reject product</summary>
        <form method="POST" action="{{ route('return-refunds.approve', $movement->id) }}" class="rr-form rr-reject-form">
            @csrf
            <div class="rr-reject-field">
                <label class="rr-field-label" for="rrRejectRemarks{{ $movement->id }}">Rejection remarks <span>(optional)</span></label>
                <input id="rrRejectRemarks{{ $movement->id }}" class="form-control" name="warehouse_remarks" maxlength="1000" placeholder="Add a reason for this product">
            </div>
            <button type="submit" class="btn btn-outline-danger" name="decision" value="Rejected">Reject product</button>
        </form>
    </details>
@elseif($isDennis && $movement->approval_status === 'Approved')
    <form method="POST" action="{{ route('return-refunds.documents', $movement->id) }}" enctype="multipart/form-data" class="rr-form rr-action-grid">
        @csrf
        <div><label class="rr-field-label" for="rrRis{{ $movement->id }}">RIS number</label><input id="rrRis{{ $movement->id }}" class="form-control" name="ris_number" required></div>
        <div><label class="rr-field-label" for="rrReturnDate{{ $movement->id }}">Return date</label><input id="rrReturnDate{{ $movement->id }}" class="form-control" type="date" name="return_date" required></div>
        <div class="rr-wide"><label class="rr-field-label" for="rrProof{{ $movement->id }}">Supporting files</label><input id="rrProof{{ $movement->id }}" class="form-control" type="file" name="return_attachments[]" accept=".jpg,.jpeg,.png,.pdf" multiple required></div>
        <button class="btn btn-primary rr-wide">Submit RIS documents</button>
    </form>
@elseif($isWarehouse && $movement->approval_status === 'Documents Submitted')
    <form method="POST" action="{{ route('return-refunds.receive', $movement->id) }}" class="rr-form js-return-receive-form" data-requested-qty="{{ $movement->qty }}">
        @csrf
        <div class="rr-action-grid">
            <div>
                <label class="rr-field-label" for="warehouse_received_qty_{{ $movement->id }}">Received quantity</label>
                <input id="warehouse_received_qty_{{ $movement->id }}" class="form-control form-control-sm" type="number" name="warehouse_received_qty" min="1" max="{{ $movement->qty }}" value="{{ $movement->qty }}" required placeholder="Received qty">
            </div>
            <div>
                <label class="rr-field-label" for="sirr_number_{{ $movement->id }}">SIRR number</label>
                <input id="sirr_number_{{ $movement->id }}" class="form-control form-control-sm" name="sirr_number" placeholder="SIRR #" required>
            </div>
        </div>
        <label class="rr-field-label mt-2" for="rrReceiveRemarks{{ $movement->id }}">Receiving remarks</label>
        <input id="rrReceiveRemarks{{ $movement->id }}" class="form-control form-control-sm js-receiving-remarks" name="warehouse_remarks" placeholder="Required if quantity changes">
        <small class="form-text text-muted mb-1">Remarks are required when Received Qty differs from {{ number_format($movement->qty) }} pcs.</small>
        <button class="btn btn-primary btn-sm mt-2 w-100">Confirm &amp; email distributor</button>
    </form>
@elseif($canSubmitCreditMemo && $movement->approval_status === 'Processing')
    <form method="POST" action="{{ route('return-refunds.credit-memo', $movement->id) }}" enctype="multipart/form-data" class="rr-form">
        @csrf
        <label class="rr-field-label" for="rrCreditMemo{{ $movement->id }}">Credit memo reference</label>
        <input id="rrCreditMemo{{ $movement->id }}" class="form-control form-control-sm mb-2" name="credit_memo_reference_no" required>
        <label class="rr-field-label" for="rrCreditFiles{{ $movement->id }}">Credit memo files</label>
        <input id="rrCreditFiles{{ $movement->id }}" class="form-control form-control-sm mb-2" type="file" name="credit_memo_attachments[]" accept=".jpg,.jpeg,.png,.pdf" multiple required>
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
