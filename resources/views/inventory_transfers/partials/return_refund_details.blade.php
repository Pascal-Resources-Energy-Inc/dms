@php
    $canApproveProducts = $isDennis && $requestGroup->requestLines->contains('approval_status', 'Pending');
@endphp
<div class="rr-request-details {{ $canApproveProducts ? 'has-approval' : '' }}">
    @if($canApproveProducts)
        <form id="rrApproveForm{{ $requestGroup->id }}" method="POST" action="{{ route('return-refunds.approve-products', $requestGroup->id) }}" class="rr-bulk-approval">
            @csrf
            <div class="rr-bulk-heading">
                <div><h6>Request products</h6><p>Select pending products to approve together.</p></div>
                <label class="rr-select-all-label"><input type="checkbox" class="rr-select-all"> Select all pending</label>
            </div>
        </form>
    @else
        <div class="rr-bulk-heading"><h6>Request products</h6><span>{{ $requestGroup->requestLines->count() }} product(s)</span></div>
    @endif

    <div class="rr-product-list">
    @foreach($requestGroup->requestLines as $movement)
        @php
            $hasSupportingDetails = $movement->ris_number || $movement->return_attachments
                || $movement->credit_memo_reference_no || $movement->credit_memo_attachments
                || $movement->warehouse_reference_no || $movement->warehouse_remarks || $movement->remarks;
            $status = [
                'Pending' => ['class' => 'warning', 'label' => 'Pending approval'],
                'Approved' => ['class' => 'primary', 'label' => 'Approved · Upload RIS'],
                'Documents Submitted' => ['class' => 'info', 'label' => 'For receiving'],
                'Processing' => ['class' => 'primary', 'label' => 'Processing'],
                'For Review' => ['class' => 'secondary', 'label' => 'For review'],
                'Reviewed' => ['class' => 'info', 'label' => 'Awaiting President approval'],
                'President Approved' => ['class' => 'success', 'label' => 'President approved'],
                'Warehouse Confirmed' => ['class' => 'success', 'label' => 'Confirmed'],
                'Rejected' => ['class' => 'danger', 'label' => 'Rejected'],
            ][$movement->approval_status] ?? ['class' => 'secondary', 'label' => $movement->approval_status ?: 'Pending'];
        @endphp
        <article class="rr-product-detail" aria-labelledby="rrProduct{{ $movement->id }}">
            <header>
                <div class="rr-product-heading">
                    @if($isDennis && $movement->approval_status === 'Pending')
                        <input id="rrSelect{{ $movement->id }}" type="checkbox" class="rr-approval-select" name="product_ids[]" value="{{ $movement->id }}" form="rrApproveForm{{ $requestGroup->id }}" data-qty="{{ (int) $movement->qty }}" aria-labelledby="rrProduct{{ $movement->id }}">
                    @endif
                    <div>
                        <h6 id="rrProduct{{ $movement->id }}">@if($isDennis && $movement->approval_status === 'Pending')<label for="rrSelect{{ $movement->id }}">{{ $movement->item_name }}</label>@else{{ $movement->item_name }}@endif</h6>
                        <small>{{ $movement->sku ?: 'No SKU' }} · {{ $movement->from_area }}</small>
                    </div>
                </div>
                <div class="rr-product-meta"><span class="rr-product-quantity"><b>{{ number_format($movement->qty) }}</b> pcs</span><span class="badge badge-{{ $status['class'] }} rr-status">{{ $status['label'] }}</span></div>
            </header>
            <div class="rr-product-detail-body {{ $hasSupportingDetails ? 'has-documents' : '' }} {{ $movement->approval_status === 'Pending' ? 'is-pending' : '' }}">
                @if($hasSupportingDetails)
                <details class="rr-documents">
                    <summary>Documents &amp; notes</summary>
                    <div class="rr-product-documents">
                        @if($movement->ris_number || $movement->return_attachments)
                            <section><h6>RIS documents</h6>
                                @if($movement->ris_number)<strong>RIS # {{ $movement->ris_number }}</strong><small>{{ optional($movement->return_date)->format('M d, Y') }}</small>@endif
                                <div class="rr-files">@foreach(($movement->return_attachments ?: []) as $file)<a href="{{ asset($file['path']) }}" target="_blank" rel="noopener">{{ $file['name'] }}</a>@endforeach</div>
                            </section>
                        @endif
                        @if($movement->credit_memo_reference_no || $movement->credit_memo_attachments)
                            <section><h6>Credit memo</h6>
                                @if($movement->credit_memo_reference_no)<strong>{{ $movement->credit_memo_reference_no }}</strong><small>{{ optional($movement->credit_memo_submitted_at)->format('M d, Y h:i A') }}</small>@endif
                                <div class="rr-files">@foreach(($movement->credit_memo_attachments ?: []) as $file)<a href="{{ asset($file['path']) }}" target="_blank" rel="noopener">{{ $file['name'] }}</a>@endforeach</div>
                            </section>
                        @endif
                        @if($movement->warehouse_reference_no)
                            <section><h6>Warehouse receipt</h6><strong>SIRR # {{ $movement->warehouse_reference_no }}</strong><small>{{ number_format($movement->warehouse_received_qty) }} pcs received</small></section>
                        @endif
                        @if($movement->warehouse_remarks || $movement->remarks)
                            <section><h6>Remarks</h6><span>{{ $movement->warehouse_remarks ?: $movement->remarks }}</span></section>
                        @endif
                    </div>
                </details>
                @endif
                <div class="rr-product-actions">@include('inventory_transfers.partials.return_refund_product_actions')</div>
            </div>
        </article>
    @endforeach
    </div>

    @if($canApproveProducts)
        <aside class="rr-bulk-controls" aria-label="Approval summary">
            <div class="rr-approval-heading"><h6>Approval summary</h6><span class="rr-approval-summary" aria-live="polite">0 products · 0 units selected</span></div>
            <div><label for="rrApprovalRemarks{{ $requestGroup->id }}">Remarks <span>(optional)</span></label><textarea id="rrApprovalRemarks{{ $requestGroup->id }}" name="warehouse_remarks" form="rrApproveForm{{ $requestGroup->id }}" class="form-control" rows="3" maxlength="1000" placeholder="Add a note for selected products"></textarea></div>
            <div class="rr-approval-submit">
                <button type="submit" form="rrApproveForm{{ $requestGroup->id }}" class="btn btn-success rr-approve-selected" disabled>Approve selected products</button>
                <p>Unselected products stay pending in this transaction.</p>
            </div>
        </aside>
    @endif
</div>
