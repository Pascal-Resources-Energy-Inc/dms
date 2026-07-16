@extends('layouts.header')

@section('css')
<style>
    .receipt { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
    .receipt-head { display: flex; justify-content: space-between; gap: 18px; padding: 24px; border-bottom: 1px solid #edf0f5; }
    .receipt-logo { height: 48px; width: auto; object-fit: contain; }
    .receipt-title { margin: 10px 0 0; color: #111827; font-size: 22px; font-weight: 800; }
    .meta-grid { display: grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap: 12px; padding: 18px 24px; border-bottom: 1px solid #edf0f5; }
    .meta-item span { display: block; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .meta-item strong { display: block; margin-top: 4px; color: #111827; }
    .status-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 800; background: #fff7ed; color: #c2410c; }
    .update-panel { padding: 22px 24px; border-bottom: 1px solid #edf0f5; background: linear-gradient(180deg, #f8fafc 0%, #eef5ff 100%); }
    .admin-update-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
    .admin-update-kicker { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 4px; color: #0d6efd; font-size: 11px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
    .admin-update-title { margin: 0; color: #111827; font-size: 18px; font-weight: 900; }
    .admin-update-copy { margin: 3px 0 0; color: #667085; font-size: 13px; }
    .admin-update-state { display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto; padding: 8px 12px; border: 1px solid #dbeafe; border-radius: 999px; background: #fff; color: #1d4ed8; font-size: 12px; font-weight: 900; }
    .update-panel-main { align-items: stretch; }
    .update-field-card { height: 100%; padding: 13px; border: 1px solid #dbe4f0; border-radius: 8px; background: rgba(255, 255, 255, .92); box-shadow: 0 10px 24px rgba(15, 23, 42, .04); }
    .update-field-label { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; color: #475467; font-size: 11px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .update-field-label i { color: #0d6efd; font-size: 14px; }
    .update-proof-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .update-action-card { width: 100%; height: 100%; display: flex; align-items: flex-end; }
    .update-action-card .btn { min-height: 38px; font-weight: 800; }
    .form-check-inline { margin-bottom: 0; }
    .form-check-input { cursor: pointer; width: 18px; height: 18px; border-radius: 4px; margin-top: 2px; }
    .status-details { display: none; padding: 16px; border: 1px solid #dbe4f0; border-radius: 8px; background: #fff; box-shadow: 0 10px 24px rgba(15, 23, 42, .06); }
    .status-details.is-visible { display: block; }
    .status-details-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #eef2f7; }
    .status-details-title { display: flex; align-items: center; gap: 9px; margin: 0; color: #111827; font-size: 14px; font-weight: 800; }
    .status-details-title i { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #eff6ff; color: #0d6efd; font-size: 15px; }
    .status-details-copy { margin: 4px 0 0; color: #667085; font-size: 12px; }
    .status-details-badge { flex: 0 0 auto; border: 1px solid #bfdbfe; border-radius: 999px; padding: 5px 10px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .status-input-card { height: 100%; padding: 12px; border: 1px solid #eef2f7; border-radius: 8px; background: #fbfdff; }
    .partial-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .partial-summary-item { padding: 11px 12px; border: 1px solid #fde68a; border-radius: 8px; background: #fffbeb; }
    .partial-summary-item:nth-child(2) { border-color: #bbf7d0; background: #f0fdf4; }
    .partial-summary-item:nth-child(2) span { color: #15803d; }
    .partial-summary-item:nth-child(3) { border-color: #fecaca; background: #fff1f2; }
    .partial-summary-item:nth-child(3) span { color: #be123c; }
    .partial-summary-item span { display: block; color: #92400e; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .partial-summary-item strong { display: block; margin-top: 3px; color: #111827; font-size: 18px; font-weight: 900; }
    .partial-shared-date { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; padding: 9px 11px; border: 1px solid #dbe7f4; border-radius: 8px; background: #f8fbff; color: #344054; font-size: 12px; font-weight: 700; }
    .partial-shared-date .form-check-input { margin: 0; }
    .partial-item-list { display: grid; gap: 10px; }
    .partial-item-row { display: grid; grid-template-columns: minmax(220px, 1fr) 120px 155px 155px; align-items: center; gap: 12px; padding: 12px; border: 1px solid #edf0f5; border-radius: 8px; background: #fff; }
    .partial-item-row:hover { border-color: #cfe0f5; background: #fbfdff; }
    .partial-item-name { color: #111827; font-size: 13px; font-weight: 800; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .partial-item-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; color: #667085; font-size: 11px; font-weight: 700; }
    .partial-item-meta span { display: inline-flex; align-items: center; border-radius: 999px; padding: 2px 7px; background: #f8fafc; }
    .partial-qty-label { display: block; margin-bottom: 4px; color: #667085; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-align: center; text-transform: uppercase; }
    .partial-received-qty { text-align: center; font-weight: 800; }
    .partial-doc-input { font-weight: 700; }
    .partial-locked-field { min-height: 31px; display: flex; align-items: center; justify-content: center; border: 1px dashed #d0d5dd; border-radius: 6px; background: #f9fafb; color: #98a2b3; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .partial-doc-wrap.is-locked .partial-doc-input { display: none; }
    .partial-doc-wrap:not(.is-locked) .partial-locked-field { display: none; }
    .status-remarks { display: none; }
    .status-remarks.is-visible { display: block; }
    .receipt-body { padding: 24px; }
    .item-thumb { width: 58px; height: 58px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 8px; background: #f8fafc; }
    .qty-input { width: 92px; margin: 0 auto; }
    .total-card { max-width: 360px; margin-left: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
    .adpo-loading { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(255, 255, 255, .82); backdrop-filter: blur(2px); }
    .adpo-loading.is-visible { display: flex; }
    .adpo-loading-box { width: min(100%, 340px); padding: 24px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; box-shadow: 0 18px 48px rgba(15, 23, 42, .14); text-align: center; }
    .adpo-loading-spinner { width: 42px; height: 42px; margin: 0 auto 14px; border: 4px solid #dbeafe; border-top-color: #0d6efd; border-radius: 50%; animation: adpoSpin .8s linear infinite; }
    .adpo-loading-title { margin: 0; color: #101828; font-size: 16px; font-weight: 800; }
    .adpo-loading-copy { margin: 6px 0 0; color: #667085; font-size: 13px; line-height: 1.45; }
    @keyframes adpoSpin { to { transform: rotate(360deg); } }
    @media (max-width: 768px) {
        .receipt-head { flex-direction: column; }
        .meta-grid { grid-template-columns: repeat(2, minmax(120px, 1fr)); }
        .admin-update-head { flex-direction: column; align-items: flex-start; }
        .admin-update-state { width: 100%; justify-content: center; border-radius: 8px; }
        .update-panel-main { align-items: stretch; }
        .status-details-head { flex-direction: column; }
        .partial-summary, .partial-item-row { grid-template-columns: 1fr; }
        .table-wrap { overflow-x: auto; }
        .table { min-width: 760px; }
    }
</style>
@endsection

@section('content')
    @php
        $isFinalStatus = in_array($order->status, ['Completed', 'Cancelled']);
        $availableStatuses = $order->status === 'Pending'
            ? ['Pending', 'SO Created', 'Cancelled']
            : ['Pending', 'SO Created', 'For Delivery', 'Partial Received', 'Completed'];

        if ($order->status === 'SO Created') {
            $availableStatuses[] = 'Cancelled';
        }
        $partialEditableItems = $order->items;
        $partialOrderedQty = $partialEditableItems->sum('qty');
        $partialReceivedQty = $partialEditableItems->sum(function ($item) {
            return (int) ($item->partial_received_qty ?? 0);
        });
        $partialPendingQty = max($partialOrderedQty - $partialReceivedQty, 0);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('ad-purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to ADPO
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    @if(auth()->user()->role === 'Admin')
        <form action="{{ route('ad-purchase-orders.updateStatus', $order->id) }}" method="POST" enctype="multipart/form-data" id="adpoUpdateForm">
            @csrf
            @method('PATCH')
    @endif

    <div class="receipt">
        <div class="receipt-head">
            <div>
                <img src="{{ asset('images/logo_mo.png') }}" class="receipt-logo" alt="Gaz Lite">
                <h4 class="receipt-title">Distributor Purchase Order</h4>
                <div class="text-muted">(DPO)</div>
            </div>
            <div class="text-md-end">
                <div class="text-muted small">PO Number</div>
                <h5 class="fw-bold mb-2">{{ $order->po_number }}</h5>
                <span class="status-pill">{{ $order->status }}</span>
            </div>
        </div>

        @if(auth()->user()->role === 'Admin')
            <div class="update-panel">
                <div class="admin-update-head">
                    <div>
                        <div class="admin-update-kicker">
                            <i class="bi bi-shield-check"></i>
                            Admin Update
                        </div>
                        <h5 class="admin-update-title">Manage Order Status</h5>
                        <p class="admin-update-copy">Update payment, documents, and receiving details from one review panel.</p>
                    </div>
                    <div class="admin-update-state">
                        <i class="bi {{ $isFinalStatus ? 'bi-lock-fill' : 'bi-pencil-square' }}"></i>
                        {{ $isFinalStatus ? 'Locked' : 'Editable' }}
                    </div>
                </div>
                <div class="row g-3 update-panel-main">
                    <div class="col-md-3">
                        <div class="update-field-card">
                            <label class="update-field-label" for="adpoStatus">
                                <i class="bi bi-flag"></i>
                                Status
                            </label>
                            <select name="status" id="adpoStatus" class="form-select form-select-sm" @if($isFinalStatus) disabled @endif>
                                @foreach($availableStatuses as $status)
                                    <option value="{{ $status }}" @if(old('status', $order->status) === $status) selected @endif>{{ strtoupper($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="update-field-card">
                            <label class="update-field-label">
                                <i class="bi bi-credit-card"></i>
                                Payment Method
                            </label>
                            <select name="payment_method" class="form-select form-select-sm" @if($isFinalStatus) disabled @endif>
                                @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                                    <option value="{{ $value }}" @if(old('payment_method', $order->payment_method) === $value) selected @endif>{{ strtoupper($label) }}</option>
                                @endforeach
                            </select>
                            @if($order->bank_name)<small>{{ strtoupper($order->bank_name) }}</small>@endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="update-field-card">
                            <label class="update-field-label">
                                <i class="bi bi-file-earmark-arrow-up"></i>
                                Proof of Payment <span class="text-danger @if($order->proof_of_payment || old('status', $order->status) === 'Cancelled') d-none @endif" id="proofRequiredMarker">*</span>
                            </label>
                            @if(!$isFinalStatus)
                                <input type="file" name="proof_of_payment" id="adpoProofOfPayment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" data-has-current-proof="{{ $order->proof_of_payment ? '1' : '0' }}" @if(!$order->proof_of_payment && old('status', $order->status) !== 'Cancelled') required @endif>
                                <div class="form-text" id="proofOfPaymentHelp">JPG, PNG, or PDF. Maximum size: 5 MB.</div>
                            @endif
                            <div class="update-proof-actions">
                                @if($order->proof_of_payment)
                                    <a href="{{ asset($order->proof_of_payment) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Current Proof
                                    </a>
                                @elseif($isFinalStatus)
                                    <span class="text-muted small">No proof of payment uploaded.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($order->payment_date)
                        <div class="col-md-2">
                            <div class="update-field-card">
                                <label class="update-field-label">
                                    <i class="bi bi-pause-circle"></i>
                                    Order Hold
                                </label>
                                <div class="form-check form-check-inline w-100">
                                    <input type="checkbox" name="is_on_hold" id="isOnHold" class="form-check-input" value="1" @if(old('is_on_hold', $order->is_on_hold)) checked @endif @if($isFinalStatus) disabled @endif>
                                    <label class="form-check-label small" for="isOnHold">Place on hold</label>
                                </div>
                                @if($order->is_on_hold)
                                    <small class="d-block text-warning mt-2"><i class="bi bi-exclamation-triangle"></i> On Hold</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if(!$isFinalStatus)
                        <div class="col-md-2 d-grid">
                            <div class="update-action-card">
                                <button class="btn btn-sm btn-primary w-100" type="submit">
                                    <i class="bi bi-check2-circle"></i> Update
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="col-md-2 d-grid">
                            <div class="update-action-card">
                                <button class="btn btn-sm btn-secondary w-100" type="button" disabled>
                                    <i class="bi bi-lock-fill"></i> Locked
                                </button>
                            </div>
                        </div>
                    @endif
                    <div class="col-12 status-details @if(old('status', $order->status) === 'SO Created') is-visible @endif" id="soDetailsWrap">
                        <div class="status-details-head">
                            <div>
                                <h6 class="status-details-title">
                                    <i class="bi bi-receipt"></i>
                                    Sales Order Details
                                </h6>
                                <p class="status-details-copy">Add the official SO number before moving this order forward.</p>
                            </div>
                            <span class="status-details-badge">Required for SO Created</span>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="status-input-card">
                                    <label class="form-label small fw-bold text-uppercase text-muted">SO Number</label>
                                    <input type="text" name="so_number" id="soNumber" class="form-control form-control-sm" value="{{ old('so_number', $order->so_number) }}" placeholder="Enter SO number" data-uppercase @if(old('status', $order->status) === 'SO Created') required @endif @if($isFinalStatus) readonly @endif>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-input-card">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Payment Date</label>
                                    <input type="date" name="payment_date" id="paymentDate" class="form-control form-control-sm" value="{{ old('payment_date', optional($order->payment_date)->format('Y-m-d')) }}" @if(old('status', $order->status) === 'SO Created') required @endif @if($isFinalStatus) readonly @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 status-details @if(in_array(old('status', $order->status), ['For Delivery', 'Partial Received'])) is-visible @endif" id="deliveryDetailsWrap">
                        <div class="status-details-head">
                            <div>
                                <h6 class="status-details-title">
                                    <i class="bi bi-truck"></i>
                                    Delivery Documents
                                </h6>
                                <p class="status-details-copy">Record the delivery schedule and document numbers used by warehouse and accounting.</p>
                            </div>
                            <span class="status-details-badge">Required for Delivery</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="status-input-card">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Delivery Date</label>
                                    <input type="date" name="delivery_date" id="deliveryDate" class="form-control form-control-sm" value="{{ old('delivery_date', optional($order->delivery_date)->format('Y-m-d')) }}" @if(old('status', $order->status) === 'For Delivery') required @endif @if($isFinalStatus) readonly @endif>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-input-card">
                                    <label class="form-label small fw-bold text-uppercase text-muted">DR Number</label>
                                    <input type="text" name="dr_number" id="drNumber" class="form-control form-control-sm" value="{{ old('dr_number', $order->dr_number) }}" placeholder="Enter DR number" data-uppercase @if(old('status', $order->status) === 'For Delivery') required @endif @if($isFinalStatus) readonly @endif>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-input-card">
                                    <label class="form-label small fw-bold text-uppercase text-muted">SI Number</label>
                                    <input type="text" name="si_number" id="siNumber" class="form-control form-control-sm" value="{{ old('si_number', $order->si_number) }}" placeholder="Enter SI number" data-uppercase @if(old('status', $order->status) === 'For Delivery') required @endif @if($isFinalStatus) readonly @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 status-details @if(old('status', $order->status) === 'Partial Received') is-visible @endif" id="partialDetailsWrap">
                        <div class="status-details-head">
                            <div>
                                <h6 class="status-details-title">
                                    <i class="bi bi-box-seam"></i>
                                    Partial Received Items
                                </h6>
                                <p class="status-details-copy">Record the received quantity, delivery date, and DR number for each product in this order.</p>
                            </div>
                            <span class="status-details-badge">Required for Partial</span>
                        </div>
                        <div class="partial-summary">
                            <div class="partial-summary-item">
                                <span>Ordered</span>
                                <strong id="showPartialOrdered">{{ number_format($partialOrderedQty) }}</strong>
                            </div>
                            <div class="partial-summary-item">
                                <span>Received</span>
                                <strong id="showPartialReceived">{{ number_format($partialReceivedQty) }}</strong>
                            </div>
                            <div class="partial-summary-item">
                                <span>Pending</span>
                                <strong id="showPartialPending">{{ number_format($partialPendingQty) }}</strong>
                            </div>
                        </div>
                        <label class="partial-shared-date" for="samePartialDeliveryDate">
                            <input type="checkbox"
                                id="samePartialDeliveryDate"
                                class="form-check-input"
                                @if(old('status', $order->status) !== 'Partial Received' || $isFinalStatus) disabled @endif>
                            Use the same delivery date for all received products
                        </label>
                        <div class="partial-item-list" id="showPartialItems">
                            @forelse($partialEditableItems as $item)
                                @php
                                    $orderedQty = (int) $item->qty;
                                    $previousReceivedQty = min(max((int) ($item->partial_received_qty ?? 0), 0), $orderedQty);
                                    $remainingBeforeQty = max($orderedQty - $previousReceivedQty, 0);
                                    $nextReceivedQty = (int) old('partial_items.' . $item->id . '.received_qty', 0);
                                    $nextReceivedQty = min(max($nextReceivedQty, 0), $remainingBeforeQty);
                                    $totalReceivedQty = min($previousReceivedQty + $nextReceivedQty, $orderedQty);
                                    $pendingQty = max($orderedQty - $totalReceivedQty, 0);
                                    $isFullyReceived = $previousReceivedQty >= $orderedQty;
                                    $hasPreviousPartial = $previousReceivedQty > 0 && !$isFullyReceived;
                                    $latestPartialReceipt = $item->partialReceipts->sortByDesc('id')->first();
                                    $latestOrderPartialReceipt = $order->partialReceipts->sortByDesc('id')->first();
                                    $previousPartialDrNumber = optional($latestPartialReceipt)->dr_number
                                        ?: $item->partial_dr_number
                                        ?: optional($latestOrderPartialReceipt)->dr_number
                                        ?: $order->dr_number;
                                    $partialItemDeliveryDate = $isFullyReceived ? '' : old('partial_items.' . $item->id . '.delivery_date', $hasPreviousPartial ? '' : optional($item->partial_delivery_date)->format('Y-m-d'));
                                    $partialItemDrNumber = $isFullyReceived ? '' : old('partial_items.' . $item->id . '.dr_number', $hasPreviousPartial ? '' : $item->partial_dr_number);
                                @endphp
                                <div class="partial-item-row">
                                    <div>
                                        <div class="partial-item-name" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                                        <div class="partial-item-meta">
                                            <span>Ordered: {{ number_format($orderedQty) }}</span>
                                            @if($previousReceivedQty > 0)
                                                <span>Received: {{ number_format($previousReceivedQty) }}</span>
                                            @endif
                                            <span class="js-partial-pending">Pending: {{ number_format($pendingQty) }}</span>
                                            @if($isFullyReceived)
                                                <span>Fully Received</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="partial-qty-label">{{ $hasPreviousPartial ? 'Next Received Qty' : 'Received Qty' }}</span>
                                        <input type="hidden"
                                            name="partial_items[{{ $item->id }}][receive_mode]"
                                            class="partial-receive-mode"
                                            value="increment"
                                            @if(old('status', $order->status) !== 'Partial Received' || $isFullyReceived) disabled @endif>
                                        <input type="number"
                                            name="partial_items[{{ $item->id }}][received_qty]"
                                            class="form-control form-control-sm partial-received-qty"
                                            min="0"
                                            max="{{ $remainingBeforeQty }}"
                                            value="{{ $nextReceivedQty }}"
                                            data-ordered-qty="{{ $orderedQty }}"
                                            data-previous-received-qty="{{ $previousReceivedQty }}"
                                            data-remaining-qty="{{ $remainingBeforeQty }}"
                                            @if(old('status', $order->status) !== 'Partial Received' || $isFullyReceived) disabled @endif
                                            @if($isFinalStatus) readonly @endif>
                                    </div>
                                    <div class="partial-doc-wrap @if($isFullyReceived) is-locked @endif">
                                        <span class="partial-qty-label">{{ $hasPreviousPartial ? 'Next Delivery Date' : 'Delivery Date' }}</span>
                                        <input type="date"
                                            name="partial_items[{{ $item->id }}][delivery_date]"
                                            class="form-control form-control-sm partial-doc-input partial-delivery-date"
                                            value="{{ $partialItemDeliveryDate }}"
                                            @if($isFullyReceived)
                                                disabled readonly
                                            @endif
                                            @if(old('status', $order->status) !== 'Partial Received') disabled @endif
                                            @if($isFinalStatus) readonly @endif>
                                        <div class="partial-locked-field">Not required</div>
                                    </div>
                                    <div class="partial-doc-wrap @if($isFullyReceived) is-locked @endif">
                                        <span class="partial-qty-label">{{ $hasPreviousPartial ? 'Next DR No.' : 'DR No.' }}</span>
                                        <input type="text"
                                            name="partial_items[{{ $item->id }}][dr_number]"
                                            class="form-control form-control-sm partial-doc-input partial-dr-number"
                                            value="{{ $partialItemDrNumber }}"
                                            placeholder="{{ $isFullyReceived ? 'Fully received' : ($hasPreviousPartial ? 'Enter next DR no.' : 'Enter DR no.') }}"
                                            data-previous-dr-number="{{ $hasPreviousPartial ? $previousPartialDrNumber : '' }}"
                                            data-uppercase
                                            @if($isFullyReceived)
                                                disabled readonly
                                            @endif
                                            @if(old('status', $order->status) !== 'Partial Received') disabled @endif
                                            @if($isFinalStatus) readonly @endif>
                                        <div class="partial-locked-field">Not required</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    All products are already fully received.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-12 status-remarks @if(in_array(old('status', $order->status), ['Cancelled', 'Partial Received'])) is-visible @endif" id="statusRemarksWrap">
                        <div class="update-field-card">
                            <label class="update-field-label" for="statusRemarks">
                                <i class="bi bi-chat-left-text"></i>
                                <span id="statusRemarksLabel">{{ old('status', $order->status) === 'Partial Received' ? 'Partial Received Remarks' : 'Cancellation Remarks' }}</span>
                            </label>
                            <textarea name="remarks" id="statusRemarks" class="form-control form-control-sm" rows="3" @if(in_array(old('status', $order->status), ['Cancelled', 'Partial Received'])) required @endif @if($isFinalStatus) readonly @endif>{{ old('remarks', $order->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="meta-grid">
            <div class="meta-item"><span>Business</span><strong>{{ $order->business_name }}</strong></div>
            <div class="meta-item"><span>Territory</span><strong>{{ $order->authorized_territory ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Phone</span><strong>{{ $order->phone_number ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Email</span><strong>{{ strtoupper($order->email_address) ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Shipping</span><strong>{{ strtoupper(ucwords(str_replace('_', ' ', $order->shipping_type))) }}</strong></div>
            <div class="meta-item"><span>Payment</span><strong>{{ strtoupper(ucwords(str_replace('_', ' ', $order->payment_method))) }}@if($order->bank_name)<br><small>{{ strtoupper($order->bank_name) }}</small>@endif</strong></div>
            <div class="meta-item">
                <span>Proof of Payment</span>
                <strong>
                    @if($order->proof_of_payment)
                        <a href="{{ asset($order->proof_of_payment) }}" target="_blank" rel="noopener">View File</a>
                    @else
                        N/A
                    @endif
                </strong>
            </div>
            @if($order->so_number)
                <div class="meta-item"><span>SO Number</span><strong>{{ strtoupper($order->so_number) }}</strong></div>
            @endif
            @if($order->delivery_date)
                <div class="meta-item"><span>Delivery Date</span><strong>{{ $order->delivery_date->format('M d, Y') }}</strong></div>
            @endif
            @if($order->dr_number)
                <div class="meta-item"><span>DR Number</span><strong>{{ strtoupper($order->dr_number) }}</strong></div>
            @endif
            @if($order->si_number)
                <div class="meta-item"><span>SI Number</span><strong>{{ strtoupper($order->si_number) }}</strong></div>
            @endif
            <div class="meta-item"><span>Voucher Code</span><strong>{{ strtoupper($order->voucher_code) ?: 'N/A' }}</strong></div>
            {{-- <div class="meta-item"><span>Uniform Size</span><strong>{{ $order->uniform_size ?: 'N/A' }}</strong></div> --}}
            <div class="meta-item"><span>Delivery Address</span><strong>{{ strtoupper($order->delivery_address) ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Submitted</span><strong>{{ optional($order->submitted_at ?: $order->created_at)->format('M d, Y h:i A') }}</strong></div>
        </div>

        <div class="receipt-body">
            <div class="table-wrap">
                @php
                    $receiptItems = $order->items;
                @endphp
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Order Qty</th>
                            @if($order->status === 'Partial Received')
                                <th class="text-center">Received / Pending</th>
                                <th class="text-center">Delivery Date</th>
                                <th class="text-center">DR No.</th>
                            @endif
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receiptItems as $item)
                            @php
                                $imagePath = $item->product_image && file_exists(public_path('uploads/products/' . $item->product_image))
                                    ? asset('uploads/products/' . $item->product_image)
                                    : asset('design/assets/images/products/empty-shopping-bag.gif');
                                $colorBreakdown = $item->color_breakdown ? json_decode($item->color_breakdown, true) : [];
                                $sizeBreakdown = $item->size_breakdown ? json_decode($item->size_breakdown, true) : [];
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $imagePath }}" class="item-thumb" alt="{{ $item->product_name }}">
                                        <div>
                                            <div class="fw-bold">{{ $item->product_name }}</div>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</small>
                                            @if(!empty($colorBreakdown))
                                                <div class="small text-muted mt-1">
                                                    Colors:
                                                    @foreach($colorBreakdown as $color => $qty)
                                                        <span class="badge bg-light text-dark border">{{ ucfirst($color) }}: {{ $qty }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(!empty($sizeBreakdown))
                                                <div class="small text-muted mt-1">
                                                    Sizes:
                                                    @foreach($sizeBreakdown as $size => $qty)
                                                        <span class="badge bg-light text-dark border">{{ $size }}: {{ $qty }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if(auth()->user()->role === 'Admin' && in_array($order->status, ['Pending', 'SO Created']))
                                        <input type="number" name="items[{{ $item->id }}][qty]" class="form-control form-control-sm qty-input text-center" value="{{ old('items.' . $item->id . '.qty', $item->qty) }}" min="1">
                                    @else
                                        {{ number_format($item->qty) }}
                                    @endif
                                </td>
                                @if($order->status === 'Partial Received')
                                    @php
                                        $tableReceivedQty = (int) ($item->partial_received_qty ?? 0);
                                        $tablePendingQty = max((int) $item->qty - $tableReceivedQty, 0);
                                    @endphp
                                    <td class="text-center">
                                        <div class="fw-bold text-success">{{ number_format($tableReceivedQty) }} received</div>
                                        <small class="text-muted">{{ number_format($tablePendingQty) }} pending</small>
                                    </td>
                                    <td class="text-center">
                                        {{ $item->partial_delivery_date ? $item->partial_delivery_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $item->partial_dr_number ? strtoupper($item->partial_dr_number) : 'N/A' }}
                                    </td>
                                @endif
                                <td class="text-end">PHP {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">PHP {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $order->status === 'Partial Received' ? 7 : 4 }}" class="text-center text-muted py-4">
                                    No products yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($order->remarks && in_array($order->status, ['Cancelled', 'Partial Received']))
                <div class="alert {{ $order->status === 'Cancelled' ? 'alert-danger' : 'alert-warning' }} border mt-3">
                    <strong>Remarks:</strong> {{ $order->remarks }}
                </div>
            @endif

            <div class="total-card mt-3">
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>PHP {{ number_format($order->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><strong>PHP {{ number_format($order->delivery_fee, 2) }}</strong></div>
                @if((float) ($order->rebate_amount ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-2"><span>Rebate Voucher</span><strong>- PHP {{ number_format($order->rebate_amount, 2) }}</strong></div>
                @endif
                @if((float) ($order->pickup_discount ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-2"><span>Pick Up Lubao Discount</span><strong>- PHP {{ number_format($order->pickup_discount, 2) }}</strong></div>
                @endif
                {{-- <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><strong>PHP {{ number_format($order->delivery_fee, 2) }}</strong></div> --}}
                @if((float) ($order->withholding_tax ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-2"><span>Less: EWT</span><strong>- PHP {{ number_format($order->withholding_tax, 2) }}</strong></div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>PHP {{ number_format($order->total_amount, 2) }}</strong></div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'Admin')
        </form>
    @endif

    @if(auth()->user()->role === 'Admin')
        <div class="adpo-loading" id="adpoLoading" aria-live="polite" aria-hidden="true">
            <div class="adpo-loading-box">
                <div class="adpo-loading-spinner"></div>
                <p class="adpo-loading-title">Updating ADPO</p>
                <p class="adpo-loading-copy">Please wait while we save the changes and notify the customer if needed.</p>
            </div>
        </div>
    @endif

@endsection

@section('javascript')
    @if(auth()->user()->role === 'Admin')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('adpoUpdateForm');
                const loading = document.getElementById('adpoLoading');
                const updateButton = form.querySelector('button[type="submit"]');
                const status = document.getElementById('adpoStatus');
                const proofOfPayment = document.getElementById('adpoProofOfPayment');
                const proofRequiredMarker = document.getElementById('proofRequiredMarker');
                const proofOfPaymentHelp = document.getElementById('proofOfPaymentHelp');
                const remarksWrap = document.getElementById('statusRemarksWrap');
                const remarksLabel = document.getElementById('statusRemarksLabel');
                const remarks = document.getElementById('statusRemarks');
                const soDetailsWrap = document.getElementById('soDetailsWrap');
                const deliveryDetailsWrap = document.getElementById('deliveryDetailsWrap');
                const soNumber = document.getElementById('soNumber');
                const deliveryDate = document.getElementById('deliveryDate');
                const drNumber = document.getElementById('drNumber');
                const siNumber = document.getElementById('siNumber');
                const partialDetailsWrap = document.getElementById('partialDetailsWrap');
                const partialItems = document.getElementById('showPartialItems');
                const partialOrdered = document.getElementById('showPartialOrdered');
                const partialReceived = document.getElementById('showPartialReceived');
                const partialPending = document.getElementById('showPartialPending');
                const samePartialDeliveryDate = document.getElementById('samePartialDeliveryDate');
                const warehousePreviousDrNumber = @json(
                    optional($order->partialReceipts->sortByDesc('id')->first())->dr_number
                        ?: $order->dr_number
                );

                if (!form || !status || !remarksWrap || !remarksLabel || !remarks || !soDetailsWrap || !deliveryDetailsWrap || !partialDetailsWrap || !partialItems || !soNumber || !deliveryDate || !drNumber || !siNumber || !samePartialDeliveryDate) {
                    return;
                }

                function applySharedPartialDeliveryDate(sourceInput) {
                    if (!samePartialDeliveryDate.checked) {
                        return;
                    }

                    const dateInputs = Array.from(partialItems.querySelectorAll('.partial-delivery-date:not(:disabled)'));
                    const existingDateInput = dateInputs.find(function (input) {
                        return input.value;
                    });
                    const sharedDate = sourceInput && sourceInput.value
                        ? sourceInput.value
                        : (existingDateInput ? existingDateInput.value : '');

                    if (!sharedDate) {
                        return;
                    }

                    dateInputs.forEach(function (input) {
                        const row = input.closest('.partial-item-row');
                        const qtyInput = row ? row.querySelector('.partial-received-qty') : null;

                        if (Number(qtyInput ? qtyInput.value || 0 : 0) > 0) {
                            input.value = sharedDate;
                        }
                    });
                }

                function applyMainDeliveryDateToPartialItems() {
                    if (!['For Delivery', 'Partial Received'].includes(status.value)) {
                        return;
                    }

                    const hasMainDeliveryDate = deliveryDate.value !== '';
                    samePartialDeliveryDate.checked = hasMainDeliveryDate;
                    samePartialDeliveryDate.disabled = hasMainDeliveryDate || @json($isFinalStatus);

                    partialItems.querySelectorAll('.partial-delivery-date').forEach(function (input) {
                        const row = input.closest('.partial-item-row');
                        const qtyInput = row ? row.querySelector('.partial-received-qty') : null;
                        const orderedQty = Number(qtyInput ? qtyInput.dataset.orderedQty || 0 : 0);
                        const previousReceivedQty = Number(qtyInput ? qtyInput.dataset.previousReceivedQty || 0 : 0);

                        if (hasMainDeliveryDate && previousReceivedQty < orderedQty) {
                            input.value = deliveryDate.value;
                        }

                        if (status.value === 'Partial Received') {
                            input.readOnly = hasMainDeliveryDate || @json($isFinalStatus);
                        }
                    });
                }

                function applyMainDrNumberToPartialItems() {
                    if (status.value !== 'Partial Received' || !@json(filled(auth()->user()->warehouse))) {
                        drNumber.readOnly = @json($isFinalStatus);
                        return;
                    }

                    if (
                        warehousePreviousDrNumber
                        && drNumber.dataset.partialDrGenerated !== '1'
                    ) {
                        drNumber.value = incrementPartialDrNumber(warehousePreviousDrNumber);
                        drNumber.dataset.partialDrGenerated = '1';
                    }

                    const sharedDrNumber = normalizePartialDrNumber(drNumber.value);
                    drNumber.value = sharedDrNumber;
                    drNumber.readOnly = Boolean(warehousePreviousDrNumber) || @json($isFinalStatus);

                    partialItems.querySelectorAll('.partial-dr-number:not(:disabled)').forEach(function (input) {
                        const row = input.closest('.partial-item-row');
                        const qtyInput = row ? row.querySelector('.partial-received-qty') : null;
                        const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);

                        input.readOnly = sharedDrNumber !== '' || @json($isFinalStatus);

                        if (receivedQty > 0 && sharedDrNumber !== '') {
                            input.value = sharedDrNumber;
                            input.dataset.autoGenerated = '1';
                        }
                    });
                }

                function normalizePartialDrNumber(drValue) {
                    let normalizedDrNumber = String(drValue || '')
                        .trim()
                        .toUpperCase()
                        .replace(/\s*-\s*(\d+)\s*$/, '-$1');

                    if (normalizedDrNumber && !normalizedDrNumber.startsWith('DR')) {
                        normalizedDrNumber = 'DR' + normalizedDrNumber;
                    }

                    return normalizedDrNumber;
                }

                function incrementPartialDrNumber(previousDrNumber) {
                    const normalizedDrNumber = normalizePartialDrNumber(previousDrNumber);
                    const incrementMatch = normalizedDrNumber.match(/^(.*)-(\d+)$/);

                    if (incrementMatch) {
                        return incrementMatch[1] + '-' + (Number(incrementMatch[2]) + 1);
                    }

                    return normalizedDrNumber ? normalizedDrNumber + '-1' : '';
                }

                function applyNextPartialDrNumber(row) {
                    if (!row) {
                        return;
                    }

                    const qtyInput = row.querySelector('.partial-received-qty');
                    const drInput = row.querySelector('.partial-dr-number');
                    const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);
                    const previousReceivedQty = Number(qtyInput ? qtyInput.dataset.previousReceivedQty || 0 : 0);
                    const previousDrNumber = drInput ? drInput.dataset.previousDrNumber || '' : '';

                    if (!drInput || previousReceivedQty <= 0 || !previousDrNumber) {
                        return;
                    }

                    if (receivedQty > 0 && (!drInput.value.trim() || drInput.dataset.autoGenerated === '1')) {
                        drInput.value = incrementPartialDrNumber(previousDrNumber);
                        drInput.dataset.autoGenerated = '1';
                    } else if (receivedQty <= 0 && drInput.dataset.autoGenerated === '1') {
                        drInput.value = '';
                        drInput.dataset.autoGenerated = '0';
                    }
                }

                function syncPartialRow(row) {
                    if (!row) {
                        return;
                    }

                    const qtyInput = row.querySelector('.partial-received-qty');
                    const modeInput = row.querySelector('.partial-receive-mode');
                    const dateInput = row.querySelector('.partial-delivery-date');
                    const drInput = row.querySelector('.partial-dr-number');
                    const docWraps = row.querySelectorAll('.partial-doc-wrap');
                    const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);
                    const orderedQty = Number(qtyInput ? qtyInput.dataset.orderedQty || 0 : 0);
                    const previousReceivedQty = Number(qtyInput ? qtyInput.dataset.previousReceivedQty || 0 : 0);
                    const isPartialStatus = status.value === 'Partial Received';
                    const wasAlreadyFullyReceived = previousReceivedQty >= orderedQty;
                    const needsDocs = isPartialStatus && !wasAlreadyFullyReceived && receivedQty > 0;

                    docWraps.forEach(function (wrap) {
                        wrap.classList.toggle('is-locked', wasAlreadyFullyReceived);
                    });

                    if (modeInput) {
                        modeInput.disabled = !isPartialStatus || wasAlreadyFullyReceived;
                    }

                    if (qtyInput) {
                        qtyInput.disabled = !isPartialStatus || wasAlreadyFullyReceived;
                    }

                    if (dateInput) {
                        dateInput.disabled = !isPartialStatus || wasAlreadyFullyReceived;
                        dateInput.readOnly = wasAlreadyFullyReceived;
                        dateInput.required = needsDocs;

                        if (wasAlreadyFullyReceived) {
                            dateInput.value = '';
                        }
                    }

                    if (drInput) {
                        drInput.disabled = !isPartialStatus || wasAlreadyFullyReceived;
                        drInput.readOnly = wasAlreadyFullyReceived;
                        drInput.required = needsDocs;

                        if (wasAlreadyFullyReceived) {
                            drInput.value = '';
                        }
                    }

                    applyNextPartialDrNumber(row);
                    applyMainDrNumberToPartialItems();
                }

                function updatePartialSummary() {
                    const qtyInputs = Array.from(partialItems.querySelectorAll('.partial-received-qty'));
                    const orderedTotal = qtyInputs.reduce(function (total, input) {
                        return total + Number(input.dataset.orderedQty || 0);
                    }, 0);
                    const receivedTotal = qtyInputs.reduce(function (total, input) {
                        return total + Number(input.dataset.previousReceivedQty || 0) + Number(input.value || 0);
                    }, 0);
                    const pendingTotal = Math.max(orderedTotal - receivedTotal, 0);

                    if (partialOrdered) {
                        partialOrdered.textContent = orderedTotal.toLocaleString();
                    }

                    if (partialReceived) {
                        partialReceived.textContent = receivedTotal.toLocaleString();
                    }

                    if (partialPending) {
                        partialPending.textContent = pendingTotal.toLocaleString();
                    }
                }

                function toggleStatusFields() {
                    const needsRemarks = ['Cancelled', 'Partial Received'].includes(status.value);
                    const needsSoDetails = status.value === 'SO Created';
                    const showsDeliveryDetails = ['For Delivery', 'Partial Received'].includes(status.value);
                    const needsDeliveryDetails = status.value === 'For Delivery';
                    const needsPartialDetails = status.value === 'Partial Received';
                    const needsWarehousePartialDr = needsPartialDetails && @json(filled(auth()->user()->warehouse));
                    const proofIsRequired = proofOfPayment
                        && proofOfPayment.dataset.hasCurrentProof !== '1'
                        && status.value !== 'Cancelled';

                    if (proofOfPayment) {
                        proofOfPayment.required = proofIsRequired;
                    }
                    if (proofRequiredMarker) {
                        proofRequiredMarker.classList.toggle('d-none', !proofIsRequired);
                    }
                    if (proofOfPaymentHelp) {
                        proofOfPaymentHelp.textContent = status.value === 'Cancelled'
                            ? 'Proof of payment is optional when cancelling an order.'
                            : 'JPG, PNG, or PDF. Maximum size: 5 MB.';
                    }

                    remarksWrap.classList.toggle('is-visible', needsRemarks);
                    remarks.required = needsRemarks;
                    remarksLabel.textContent = status.value === 'Partial Received'
                        ? 'Partial Received Remarks'
                        : 'Cancellation Remarks';
                    remarks.placeholder = status.value === 'Partial Received'
                        ? 'Add the items or quantity still pending.'
                        : 'Add the reason for cancellation.';

                    soDetailsWrap.classList.toggle('is-visible', needsSoDetails);
                    soNumber.disabled = !needsSoDetails;
                    soNumber.required = needsSoDetails;

                    deliveryDetailsWrap.classList.toggle('is-visible', showsDeliveryDetails);
                    deliveryDate.disabled = !showsDeliveryDetails;
                    drNumber.disabled = !showsDeliveryDetails;
                    siNumber.disabled = !showsDeliveryDetails;
                    deliveryDate.required = needsDeliveryDetails;
                    drNumber.required = needsDeliveryDetails || needsWarehousePartialDr;
                    siNumber.required = needsDeliveryDetails;

                    partialDetailsWrap.classList.toggle('is-visible', needsPartialDetails);
                    samePartialDeliveryDate.disabled = !needsPartialDetails || @json($isFinalStatus);
                    partialItems.querySelectorAll('.partial-receive-mode, .partial-received-qty, .partial-delivery-date, .partial-dr-number').forEach(function (input) {
                        input.disabled = !needsPartialDetails;
                    });
                    partialItems.querySelectorAll('.partial-item-row').forEach(syncPartialRow);

                    applyMainDeliveryDateToPartialItems();
                    applyMainDrNumberToPartialItems();
                    updatePartialSummary();
                }

                status.addEventListener('change', toggleStatusFields);
                deliveryDate.addEventListener('input', applyMainDeliveryDateToPartialItems);
                deliveryDate.addEventListener('change', applyMainDeliveryDateToPartialItems);
                drNumber.addEventListener('input', applyMainDrNumberToPartialItems);
                drNumber.addEventListener('change', applyMainDrNumberToPartialItems);
                drNumber.addEventListener('input', function () {
                    drNumber.dataset.partialDrGenerated = '1';
                });
                toggleStatusFields();

                partialItems.addEventListener('input', function (event) {
                    if (event.target.classList.contains('partial-dr-number')) {
                        event.target.dataset.autoGenerated = '0';
                        return;
                    }

                    if (!event.target.classList.contains('partial-received-qty')) {
                        return;
                    }

                    const input = event.target;
                    const orderedQty = Number(input.dataset.orderedQty || 0);
                    const previousReceivedQty = Number(input.dataset.previousReceivedQty || 0);
                    const remainingQty = Number(input.dataset.remainingQty || input.max || 0);
                    let value = Number(input.value || 0);

                    if (value < 0) {
                        value = 0;
                    }

                    if (value > remainingQty) {
                        value = remainingQty;
                    }

                    input.value = value;

                    const row = input.closest('.partial-item-row');
                    const pending = row ? row.querySelector('.js-partial-pending') : null;

                    if (pending) {
                        pending.textContent = 'Pending: ' + Math.max(orderedQty - previousReceivedQty - value, 0).toLocaleString();
                    }

                    syncPartialRow(row);
                    applyMainDeliveryDateToPartialItems();
                    applyMainDrNumberToPartialItems();
                    applySharedPartialDeliveryDate();
                    updatePartialSummary();
                });

                samePartialDeliveryDate.addEventListener('change', function () {
                    applySharedPartialDeliveryDate();
                });

                partialItems.addEventListener('change', function (event) {
                    if (event.target.classList.contains('partial-delivery-date')) {
                        applySharedPartialDeliveryDate(event.target);
                    }
                });

                function showLoading() {
                    loading.classList.add('is-visible');
                    loading.setAttribute('aria-hidden', 'false');

                    if (updateButton) {
                        updateButton.disabled = true;
                        updateButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...';
                    }

                    Swal.fire({
                        title: 'Updating ADPO',
                        text: 'Please wait while we save your changes.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                }

                @if($errors->any())
                    Swal.fire({
                        icon: 'error',
                        title: 'ADPO Error',
                        text: @json($errors->first()),
                        confirmButtonText: 'OK'
                    });
                @endif

                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        showLoading();
                        return;
                    }

                    event.preventDefault();

                    if (['Cancelled', 'Partial Received'].includes(status.value) && remarks.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Remarks required',
                            text: 'Please add remarks before saving this status.',
                            confirmButtonText: 'OK'
                        });
                        remarks.focus();
                        return;
                    }

                    if (status.value === 'SO Created' && soNumber.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'SO number required',
                            text: 'Please enter the SO number before saving this status.',
                            confirmButtonText: 'OK'
                        });
                        soNumber.focus();
                        return;
                    }

                    if (status.value === 'For Delivery' && (deliveryDate.value.trim() === '' || drNumber.value.trim() === '' || siNumber.value.trim() === '')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Delivery details required',
                            text: 'Please complete the delivery date, DR number, and SI number before saving this status.',
                            confirmButtonText: 'OK'
                        });

                        if (deliveryDate.value.trim() === '') {
                            deliveryDate.focus();
                        } else if (drNumber.value.trim() === '') {
                            drNumber.focus();
                        } else {
                            siNumber.focus();
                        }

                        return;
                    }

                    if (status.value === 'Partial Received') {
                        const qtyInputs = Array.from(partialItems.querySelectorAll('.partial-received-qty'));
                        const hasReceivedQty = qtyInputs.some(function (input) {
                            return Number(input.value || 0) > 0;
                        });
                        const hasInvalidQty = qtyInputs.some(function (input) {
                            const value = Number(input.value || 0);
                            const max = Number(input.max || 0);

                            return value < 0 || value > max;
                        });
                        const missingDocsRow = Array.from(partialItems.querySelectorAll('.partial-item-row')).find(function (row) {
                            const qtyInput = row.querySelector('.partial-received-qty');
                            const dateInput = row.querySelector('.partial-delivery-date');
                            const drInput = row.querySelector('.partial-dr-number');
                            const receivedQty = Number(qtyInput ? qtyInput.value || 0 : 0);

                            return receivedQty > 0
                                && (!dateInput || dateInput.value.trim() === '' || !drInput || drInput.value.trim() === '');
                        });

                        if (!qtyInputs.length || !hasReceivedQty || hasInvalidQty || missingDocsRow) {
                            Swal.fire({
                                icon: 'warning',
                                title: missingDocsRow ? 'Product delivery details required' : 'Received quantity required',
                                text: missingDocsRow
                                    ? 'Please enter the next delivery date and DR number for each product received in this update.'
                                    : (hasInvalidQty
                                    ? 'Received quantity cannot be less than 0 or greater than pending quantity.'
                                    : 'Please enter at least one received product quantity.'),
                                confirmButtonText: 'OK'
                            });

                            if (missingDocsRow) {
                                const missingInput = missingDocsRow.querySelector('.partial-delivery-date:invalid, .partial-dr-number:invalid')
                                    || missingDocsRow.querySelector('.partial-delivery-date, .partial-dr-number');
                                missingInput.focus();
                            } else if (qtyInputs.length) {
                                qtyInputs[0].focus();
                            }

                            return;
                        }
                    }

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    Swal.fire({
                        icon: status.value === 'Cancelled' ? 'warning' : 'question',
                        title: 'Update ADPO?',
                        text: 'This will save the changes and set the status to ' + status.value + '.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, update',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: status.value === 'Cancelled' ? '#b42318' : '#0d6efd',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            showLoading();
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endif
@endsection
