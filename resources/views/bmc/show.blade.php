@extends('layouts.header')

@section('css')
@include('bmc.partials.field_styles')
<style>
    .bmc-detail { padding: 18px 0 30px; color: #172033; }
    .bmc-detail-card { padding: 22px; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .bmc-detail-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 22px; }
    .bmc-detail-head h3 { margin: 0 0 5px; color: #101828; font-size: 22px; font-weight: 800; }
    .bmc-detail-reference { color: #667085; font-size: 12px; font-weight: 700; }
    .bmc-detail-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .bmc-detail-status { display: inline-flex; margin-top: 8px; padding: 5px 10px; color: #1d4ed8; background: #eff6ff; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .bmc-section { overflow: hidden; margin-bottom: 16px; border: 1px solid #e4e7ec; border-radius: 10px; }
    .bmc-section:last-child { margin-bottom: 0; }
    .bmc-section-head { padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e4e7ec; }
    .bmc-section-head h4 { margin: 0; font-size: 15px; font-weight: 800; }
    .bmc-section-head i { margin-right: 8px; color: #2563eb; }
    .bmc-section-body { padding: 14px 18px; }
    .bmc-detail a.bmc-proof-link, .bmc-modal-card a.bmc-proof-link {
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .bmc-detail a.bmc-proof-link:hover, .bmc-modal-card a.bmc-proof-link:hover { color: #1e40af; }
    .bmc-detail a.bmc-proof-link:focus-visible, .bmc-modal-card a.bmc-proof-link:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
    .bmc-pending-badge { display: inline-block; padding: 4px 7px; border-radius: 4px; background: #fff0c2; color: #704600; font-size: 11px; font-weight: 700; }
    #bmcPaymentMethod:invalid { color: #667085; }
    #bmcPaymentMethod option { color: #1d2939; }
    .bmc-prepared-items {
        overflow: hidden;
        margin-bottom: 16px;
    }
    .bmc-prepared-items .table { margin-bottom: 0; }
    .bmc-prepared-items .table > :not(caption) > * > * {
        padding: 10px 14px;
        border: 0;
    }
    .bmc-prepared-items .table tr > * + * { border-left: 1px solid #d9e5f1; }
    .bmc-prepared-items .table tr > :first-child { border-left: 1px solid #d9e5f1; }
    .bmc-prepared-items .table tr > :last-child { border-right: 1px solid #d9e5f1; }
    .bmc-prepared-items .table tbody tr td { border-top: 1px solid #d9e5f1; }
    .bmc-prepared-items .table thead th {
        color: #24516f;
        background-color: #edf5fc;
        font-weight: 700;
    }
    .bmc-prepared-items .table tbody td { color: #344054; background-color: #fff; }
    .bmc-prepared-items .table tbody tr:last-child td { border-bottom: 1px solid #d9e5f1; }
    .bmc-info { width: 100%; margin: 0; }
    .bmc-info th, .bmc-info td { padding: 8px 6px; vertical-align: top; overflow-wrap: anywhere; }
    .bmc-info th { width: 30%; color: #667085; font-size: 12px; font-weight: 700; }
    .bmc-info td { color: #344054; font-size: 13px; white-space: pre-wrap; }
    .bmc-item-list { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
    .bmc-item { display: inline-flex; align-items: center; gap: 10px; max-width: 100%; padding: 12px 15px; color: #24516f; background: #eef8ff; border: 1px solid #cfe9f8; border-left: 4px solid #59a9d8; border-radius: 8px; font-size: 13px; font-weight: 700; overflow-wrap: anywhere; }
    .bmc-item i { color: #3d8fbe; font-size: 18px; }
    .bmc-guide { margin-bottom: 10px; padding: 12px 18px; color: #4b5563; background: #edf1f8; border-left: 3px solid #6c8eab; border-radius: 4px; font-size: 13px; }
    .bmc-guide strong { display: block; margin-bottom: 4px; }
    .bmc-guide-line { display: flex; align-items: center; gap: 8px; margin-top: 3px; }
    .bmc-swatch { width: 11px; height: 11px; border-radius: 2px; }
    .bmc-swatch.signed, .bmc-release-table .signed { background: #d4edda; }
    .bmc-swatch.unsigned, .bmc-release-table .unsigned { background: #f8d7da; }
    .bmc-swatch.signed { border: 1px solid #9fcbaa; }
    .bmc-swatch.unsigned { border: 1px solid #dfa7ac; }
    .bmc-release { overflow: hidden; margin-bottom: 24px; border: 1px solid #d0d5dd; border-radius: 6px; }
    .bmc-release-title { padding: 9px 18px; color: #fff; background: #a0a0a0; font-weight: 700; }
    .bmc-release-table { min-width: 850px; margin: 0; }
    .bmc-release-table th, .bmc-release-table td { padding: 7px 9px; vertical-align: middle; white-space: nowrap; border: 1px solid #d0d5dd; font-size: 13px; }
    .bmc-release-actions { display: flex; align-items: center; gap: 4px; }
    .bmc-release-action { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 0; border-radius: 50%; color: white; text-decoration: none; }
    .bmc-release-action:hover { color: white; filter: brightness(.9); }
    .bmc-release-action.view { background: #2387ed; }
    .bmc-release-action.document { background: #4240b7; }
    .bmc-release-action.sign { background: #ffb900; color: #343434; }
    .bmc-release-action.sign i { font-size: 20px; }
    .bmc-release-action.return { background: #ffb900; color: #343434; }
    .bmc-release-action.extend { background: #52b85e; }
    .bmc-release-status { display: inline-block; padding: 4px 9px; color: white; background: #51b85d; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .bmc-release-status.partial { background: #ffc107; color: #343434; }
    .bmc-release-status.pending { background: #17a2b8; }
    .bmc-release-status.closed { background: #6c757d; }
    .bmc-modal-header { background: #3993d1; color: white; }
    .bmc-modal-header .btn-close { filter: brightness(0) invert(1); }
    .bmc-modal-card { overflow: hidden; margin-top: 16px; border: 1px solid #d0d5dd; border-radius: 8px; }
    .bmc-modal-card h6 { margin: 0; padding: 9px 16px; background: #f8f9fa; border-bottom: 1px solid #d0d5dd; font-weight: 700; }
    .bmc-modal-card-body { padding: 14px 16px; }
    .bmc-modal-table { min-width: 600px; margin-bottom: 0; }
    .bmc-modal-table th, .bmc-modal-table td { font-size: 13px; vertical-align: middle; }
    .bmc-return-history-table { min-width: 1240px; }
    .bmc-return-history-table th, .bmc-return-history-table td { vertical-align: top; }
    .bmc-returned { background: #d4edda !important; color: #155724; font-weight: 700; }
    .bmc-remaining { background: #f8d7da !important; color: #721c24; font-weight: 700; }
    .bmc-modal-details { width: 100%; }
    .bmc-modal-details th, .bmc-modal-details td { padding: 4px 8px; vertical-align: top; font-size: 13px; }
    .bmc-modal-details th { width: 42%; color: #667085; font-weight: 700; }
    @media (max-width: 640px) {
        .bmc-detail-card { padding: 16px; }
        .bmc-section-body { padding: 12px; }
        .bmc-info th, .bmc-info td { display: block; width: 100%; padding: 3px 4px; }
        .bmc-info td { padding-bottom: 12px; }
    }
</style>
@endsection

@section('content')
@php
    $borrowerName = trim(($borrowing['borrower_first_name'] ?? '') . ' ' . ($borrowing['borrower_last_name'] ?? ''));
    $requestedItems = collect($borrowing['borrowed_items'] ?? []);
    $hasUnknownDeposit = $requestedItems->contains(function ($item) use ($depositRates) {
        return !array_key_exists($item['item_name'] ?? '', $depositRates);
    });
    $requestedDeposit = $requestedItems->sum(function ($item) use ($depositRates) {
        return ($depositRates[$item['item_name'] ?? ''] ?? 0) * (int) ($item['requested_quantity'] ?? 0);
    });
@endphp
<div class="bmc-detail">
    <div class="bmc-detail-card">
        <header class="bmc-detail-head">
            <div>
                <h3>{{ $borrowerName ?: 'Borrowing request' }}</h3>
                <div class="bmc-detail-reference">{{ $borrowing['reference_no'] ?? '-' }}</div>
                <span class="bmc-detail-status">{{ $borrowing['status'] ?? '-' }}</span>
            </div>
            <div class="bmc-detail-actions">
                <a href="{{ route('bmc.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
                @if(($borrowing['status'] ?? '') === 'For Approval')
                    <a href="{{ route('bmc.edit', $borrowing['id']) }}" class="btn btn-sm btn-primary"><i class="ti ti-edit"></i> Edit request</a>
                    <form method="POST" action="{{ route('bmc.cancel', $borrowing['id']) }}" onsubmit="return confirm('Cancel this borrowing request?');">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel request</button>
                    </form>
                @endif
            </div>
        </header>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        @if(!empty($borrowing['release_items']) && !empty($borrowing['release_token']) && in_array($borrowing['status'] ?? '', ['Approved', 'Partially Released'], true))
            <section class="bmc-section" id="release-payment">
                <div class="bmc-section-head"><h4><i class="ti ti-package"></i> Deposit Value</h4></div>
                <div class="bmc-section-body">
                    <p class="text-muted">Review the items prepared for this release before submitting your payment deposit.</p>
                    @php
                        $preparedItems = collect($borrowing['release_items']);
                        $ratesKnown = !$preparedItems->contains(function ($preparedItem) use ($depositRates) {
                            return !array_key_exists($preparedItem['item_name'] ?? '', $depositRates);
                        });
                        $calculatedDeposit = $preparedItems->sum(function ($preparedItem) use ($depositRates) {
                            return ($depositRates[$preparedItem['item_name'] ?? ''] ?? 0) * (int) ($preparedItem['quantity'] ?? 0);
                        });
                        $showBreakdown = $ratesKnown && isset($borrowing['release_deposit']) && abs($calculatedDeposit - (float) $borrowing['release_deposit']) < 0.01;
                    @endphp
                    <div class="table-responsive bmc-prepared-items">
                        <table class="table table-sm">
                            <thead class="table-light"><tr><th>Item</th><th>Quantity</th><th>Deposit per item</th><th>Item deposit</th></tr></thead>
                            <tbody>
                                @foreach($preparedItems as $preparedItem)
                                    @php $rate = $showBreakdown ? $depositRates[$preparedItem['item_name']] : null; @endphp
                                    <tr>
                                        <td>{{ $preparedItem['item_name'] ?? '-' }}</td>
                                        <td>{{ $preparedItem['quantity'] ?? '-' }}</td>
                                        <td>{{ $rate !== null ? '₱' . number_format($rate, 2) : 'Unavailable' }}</td>
                                        <td>{{ $rate !== null ? '₱' . number_format($rate * (int) ($preparedItem['quantity'] ?? 0), 2) : 'Unavailable' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p><strong>Deposit for this release:</strong> {{ isset($borrowing['release_deposit']) ? '₱' . number_format((float) $borrowing['release_deposit'], 2) : 'Unavailable' }}</p>
                    @if(!$showBreakdown)<p class="text-muted small">The item deposit breakdown is unavailable. Please confirm the amount with the Marketing Department.</p>@endif
                    <p class="text-muted small"><strong>Payment instructions:</strong> Please contact the Marketing Department for the deposit payment details before making your transfer.</p>
                    @if(!empty($borrowing['payment_method']) || !empty($borrowing['proof_of_transfer']))
                        <div class="alert alert-info">
                            <div>Payment method: {{ ($borrowing['payment_method'] ?? null) ?: '-' }}</div>
                            @if(!empty($borrowing['proof_of_transfer']))
                                <a class="bmc-proof-link" href="{{ route('bmc.payment-proof', $borrowing['id']) }}" target="_blank" rel="noopener noreferrer">View proof of transfer</a>
                            @endif
                        </div>
                    @endif
                    <form method="POST" action="{{ route('bmc.payment', $borrowing['id']) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="release_token" value="{{ $borrowing['release_token'] }}">
                        <div class="mb-3">
                            <label for="bmcPaymentMethod" class="form-label fw-bold">Payment method <span class="text-danger">*</span></label>
                            <select id="bmcPaymentMethod" class="form-control" name="payment_method" required>
                                <option value="">Select a payment method</option>
                                @foreach(['COD', 'GCash', 'Bank Transfer'] as $paymentMethod)
                                    <option value="{{ $paymentMethod }}" {{ old('payment_method', $borrowing['payment_method'] ?? '') === $paymentMethod ? 'selected' : '' }}>{{ $paymentMethod }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('payment_method'))<div class="text-danger small">{{ $errors->first('payment_method') }}</div>@endif
                        </div>
                        <div class="mb-3">
                            <label for="bmcPaymentProof" class="form-label fw-bold">Proof of transfer <span id="bmcPaymentProofRequired" class="text-danger">*</span></label>
                            <input id="bmcPaymentProof" class="form-control" type="file" name="proof_of_transfer" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" {{ old('payment_method', $borrowing['payment_method'] ?? '') === 'COD' ? '' : 'required' }}>
                            <small class="text-muted">JPG, PNG, or PDF; maximum 5 MB.</small>
                            @if($errors->has('proof_of_transfer'))<div class="text-danger small">{{ $errors->first('proof_of_transfer') }}</div>@endif
                        </div>
                        @if(!empty($borrowing['proof_of_transfer']))<p class="text-muted small">Submitting new details replaces the current proof for this prepared release.</p>@endif
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm">{{ !empty($borrowing['proof_of_transfer']) ? 'Update payment details' : 'Submit deposit' }}</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @if(($borrowing['status'] ?? '') === 'Closed' || !empty($borrowing['has_refund_proof']))
            <section class="bmc-section">
                <div class="bmc-section-head"><h4><i class="ti ti-cash"></i> Deposit Refund</h4></div>
                <div class="bmc-section-body">
                    @if(!empty($borrowing['has_refund_proof']))
                        <p class="mb-2"><strong>Refund payment method:</strong> {{ ($borrowing['refund_payment_method'] ?? null) ?: 'Not provided' }}</p>
                        <a class="bmc-proof-link" href="{{ route('bmc.refund-proof', $borrowing['id']) }}" target="_blank" rel="noopener noreferrer">View proof of refund</a>
                    @elseif(array_key_exists('has_refund_proof', $borrowing))
                        <p class="text-muted mb-0">Proof of refund is not yet available.</p>
                    @else
                        <p class="text-muted mb-0">Refund proof status is unavailable.</p>
                    @endif
                </div>
            </section>
        @endif

        @if(!empty($borrowing['accountabilities']))
            <div class="bmc-guide">
                <strong><i class="ti ti-info-circle"></i> Reference guide</strong>
                <div class="bmc-guide-line"><span class="bmc-swatch signed"></span> Green means the borrower has signed.</div>
                <div class="bmc-guide-line"><span class="bmc-swatch unsigned"></span> Red means the borrower's signature is still pending.</div>
            </div>
            <section class="bmc-release">
                <div class="bmc-release-title">Release History</div>
                <div class="table-responsive">
                    <table class="table table-bordered bmc-release-table">
                        <thead><tr><th scope="col">Action</th><th scope="col">Ref</th><th scope="col">Released At</th><th scope="col">Signed At</th><th scope="col">Released By</th><th scope="col">To return</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                            @foreach($borrowing['accountabilities'] as $release)
                                @php
                                    $signed = !empty($release['borrower_signature']) || !empty($release['signed_at']);
                                    $releaseStatus = $release['status'] ?? '';
                                    $remaining = collect($release['items'] ?? [])->sum(function ($item) { return max(0, (int) ($item['released_quantity'] ?? 0) - (int) ($item['returned_quantity'] ?? 0)); });
                                    $hasPendingExtension = collect($release['extensions'] ?? [])->contains(function ($extension) { return ($extension['status'] ?? '') === 'pending'; });
                                    $canAct = $remaining > 0 && in_array($releaseStatus, ['Released', 'Partially Returned'], true);
                                    $canExtend = $canAct && !$hasPendingExtension && !empty($release['released_at']) && !empty($release['expected_return_at']) && \Carbon\Carbon::parse($release['released_at'])->addDays(40)->startOfDay()->gte(today()) && \Carbon\Carbon::parse($release['expected_return_at'])->startOfDay()->lt(\Carbon\Carbon::parse($release['released_at'])->addDays(40)->startOfDay());
                                @endphp
                                <tr>
                                    <td><div class="bmc-release-actions">
                                        <button type="button" class="bmc-release-action view" data-bs-toggle="modal" data-bs-target="#bmcRelease{{ $release['id'] }}" title="View summary" aria-label="View summary {{ $release['accountability_ref'] ?? '' }}"><i class="ti ti-eye"></i></button>
                                        <a class="bmc-release-action document" href="{{ route('bmc.accountability.preview', $release['id']) }}" target="_blank" rel="noopener noreferrer" title="View accountability" aria-label="View accountability {{ $release['accountability_ref'] ?? '' }}"><i class="ti ti-file"></i></a>
                                        @if(!$signed && $releaseStatus === 'Released')
                                            <button type="button" class="bmc-release-action sign" data-bs-toggle="modal" data-bs-target="#bmcSignature{{ $release['id'] }}" title="Sign accountability" aria-label="Sign accountability {{ $release['accountability_ref'] ?? '' }}"><i class="ti ti-signature"></i></button>
                                        @endif
                                        @if(!empty($release['borrower_signature']) && $canAct)
                                            <button type="button" class="bmc-release-action return" data-bs-toggle="modal" data-bs-target="#requestBmcReturn{{ $release['id'] }}" title="Initiate return" aria-label="Initiate return for {{ $release['accountability_ref'] ?? '' }}"><i class="ti ti-arrow-back-up"></i></button>
                                        @endif
                                        @if($canExtend || !empty($release['extensions']))
                                            <button type="button" class="bmc-release-action extend" data-bs-toggle="modal" data-bs-target="#bmcExtension{{ $release['id'] }}" title="Request extension" aria-label="Request extension {{ $release['accountability_ref'] ?? '' }}"><i class="ti ti-calendar"></i></button>
                                        @endif
                                    </div></td>
                                    <td class="{{ $signed ? 'signed' : 'unsigned' }}">{{ $release['accountability_ref'] ?? '-' }}</td>
                                    <td>{{ !empty($release['released_at']) ? \Carbon\Carbon::parse($release['released_at'])->format('M d, Y g:i A') : '-' }}</td>
                                    <td>{{ !empty($release['signed_at']) ? \Carbon\Carbon::parse($release['signed_at'])->format('M d, Y g:i A') : '-' }}</td>
                                    <td>{{ $release['released_by_user']['name'] ?? $release['released_by']['name'] ?? '-' }}</td>
                                    <td>{{ !empty($release['expected_return_at']) ? \Carbon\Carbon::parse($release['expected_return_at'])->format('M d, Y') : '-' }}</td>
                                    <td><span class="bmc-release-status {{ $releaseStatus === 'Partially Returned' ? 'partial' : ($releaseStatus === 'For Return Confirmation' ? 'pending' : ($releaseStatus === 'Closed' ? 'closed' : '')) }}">{{ $releaseStatus ?: '-' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="bmc-section">
            <div class="bmc-section-head"><h4><i class="ti ti-user"></i> I. Borrower's Information</h4></div>
            <div class="bmc-section-body">
                <table class="bmc-info">
                    <tbody>
                        <tr><th scope="row">Borrower's Profile</th><td>{{ $borrowing['borrower_profile'] ?? '-' }}</td></tr>
                        <tr><th scope="row">Borrower's Full Name</th><td>{{ $borrowerName ?: '-' }}</td></tr>
                        @if(!empty($borrowing['business_name']))<tr><th scope="row">Business Name</th><td>{{ $borrowing['business_name'] }}</td></tr>@endif
                        <tr><th scope="row">Email</th><td>{{ $borrowing['email'] ?? '-' }}</td></tr>
                        <tr><th scope="row">Phone Number</th><td>{{ $borrowing['phone_number'] ?? '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bmc-section">
            <div class="bmc-section-head"><h4><i class="ti ti-calendar"></i> II. Event Details</h4></div>
            <div class="bmc-section-body">
                <table class="bmc-info">
                    <tbody>
                        <tr><th scope="row">Date Needed</th><td>{{ !empty($borrowing['date_needed']) ? \Carbon\Carbon::parse($borrowing['date_needed'])->format('F j, Y') : '-' }}</td></tr>
                        <tr><th scope="row">Event Name</th><td>{{ $borrowing['event_name'] ?? '-' }}</td></tr>
                        <tr><th scope="row">Request Details</th><td>{{ $borrowing['request_details'] ?? '-' }}</td></tr>
                        <tr><th scope="row">Number of Days Item is Needed</th><td>{{ isset($borrowing['number_of_days_item_needed']) ? $borrowing['number_of_days_item_needed'] . ' day(s)' : '-' }}</td></tr>
                        @if(!empty($borrowing['mbd_remarks']))<tr><th scope="row">MBD Remarks</th><td>{{ $borrowing['mbd_remarks'] }}</td></tr>@endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bmc-section">
            <div class="bmc-section-head"><h4><i class="ti ti-gift"></i> III. Items for Borrowing</h4></div>
            <div class="bmc-section-body">
                <div class="bmc-item-list">
                    @forelse($borrowing['borrowed_items'] ?? [] as $item)
                        <div class="bmc-item"><i class="ti ti-package"></i><span>{{ $item['item_name'] ?? '-' }} &times; {{ $item['requested_quantity'] ?? '-' }}</span></div>
                    @empty
                        <span class="text-muted">No items found.</span>
                    @endforelse
                </div>
                <table class="bmc-info mt-3">
                    <tbody>
                        <tr><th scope="row">Total Deposit</th><td><strong>{{ $hasUnknownDeposit ? 'Unavailable' : '₱' . number_format($requestedDeposit, 2) }}</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

@foreach($borrowing['accountabilities'] ?? [] as $release)
    @php
        $releasedItems = collect($release['items'] ?? []);
        $returnEntries = collect($release['pending_returns'] ?? [])->map(function ($entry) { $entry['_status'] = 'Awaiting confirmation'; return $entry; })
            ->concat(collect($release['return_history'] ?? [])->map(function ($entry) { $entry['_status'] = 'Confirmed'; return $entry; }));
    @endphp
    <div class="modal fade" id="bmcRelease{{ $release['id'] }}" tabindex="-1" aria-labelledby="bmcReleaseTitle{{ $release['id'] }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header rounded-top bmc-modal-header">
                    <div><h5 class="modal-title text-white" id="bmcReleaseTitle{{ $release['id'] }}">Request Details</h5><small>{{ $release['accountability_ref'] ?? '-' }}</small></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm bmc-modal-table">
                            <thead class="table-light"><tr><th>Borrowed Item(s)</th><th class="text-center">Released</th><th class="text-center">Returned</th><th class="text-center">Remaining</th><th>Serial No.</th><th>Deposit</th></tr></thead>
                            <tbody>
                                @forelse($releasedItems as $item)
                                    @php
                                        $released = (int) ($item['released_quantity'] ?? 0);
                                        $returned = (int) ($item['returned_quantity'] ?? 0);
                                        $remaining = max(0, $released - $returned);
                                        $serialText = collect($item['serials'] ?? [])->pluck('serial_number')->filter()->implode(', ');
                                    @endphp
                                    <tr>
                                        <td>{{ $item['item_name'] ?? '-' }}</td>
                                        <td class="text-center">{{ $released }}</td>
                                        <td class="text-center {{ $released > 0 && $returned >= $released ? 'bmc-returned' : '' }}">{{ $returned }}</td>
                                        <td class="text-center {{ $remaining > 0 ? 'bmc-remaining' : '' }}">{{ $remaining }}</td>
                                        <td>{{ $serialText ?: (($item['serial_number'] ?? null) ?: '-') }}</td>
                                        <td>₱{{ number_format((float) ($item['deposit_amount'] ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No released items found.</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light"><tr><th colspan="5" class="text-end">Total Deposit</th><th>₱{{ number_format($releasedItems->sum('deposit_amount'), 2) }}</th></tr></tfoot>
                        </table>
                    </div>

                    <section class="bmc-modal-card">
                        <h6>Return History</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm bmc-modal-table bmc-return-history-table">
                                <thead class="table-light"><tr><th style="width: 140px;">Date Returned</th><th style="min-width: 250px;">Item</th><th class="text-center" style="width: 90px;">Quantity</th><th style="min-width: 220px;">Serial Number(s)</th><th style="min-width: 170px;">Received By</th><th style="min-width: 260px; width: 260px;">MOR</th><th style="min-width: 260px; width: 260px;">Borrower Remarks</th><th style="min-width: 220px;">Approver Remarks</th></tr></thead>
                                <tbody>
                                    @forelse($returnEntries as $return)
                                        @php
                                            $returnItem = $releasedItems->firstWhere('id', $return['bmc_accountability_item_id'] ?? null);
                                            $serials = json_decode($return['serial_numbers'] ?? '', true);
                                            $serialText = is_array($serials) && isset($serials['numbers']) ? implode(', ', $serials['numbers']) : ($return['serial_numbers'] ?? '-');
                                            $rawRemarks = trim((string) ($return['borrower_remarks'] ?? ''));
                                            preg_match('/^Details:[ \t]*(.+?)(?:\r?\n|$)/', $rawRemarks, $detailsMatch);
                                            $mode = $return['return_mode'] ?? '';
                                            $mode = ['Officer Name' => 'Thru Officer', 'Field location' => 'Thru Plant/Warehouse'][$mode] ?? $mode;
                                            $mor = trim($mode . (isset($detailsMatch[1]) ? ': ' . trim($detailsMatch[1]) : ''), ': ');
                                            $remarks = preg_replace('/^Details:\s*.*?(?:\r?\n|$)/', '', $rawRemarks, 1);
                                            $remarks = trim(preg_replace('/^Borrower remarks:\s*/i', '', trim($remarks)));
                                            $attachments = $return['attachment_paths'] ?? ($return['attachment'] ?? []);
                                            if (is_string($attachments)) $attachments = json_decode($attachments, true) ?: [$attachments];
                                            $attachments = is_array($attachments) ? $attachments : [];
                                            $serviceUrl = parse_url((string) config('services.ldr.endpoint'));
                                            $serviceOrigin = isset($serviceUrl['scheme'], $serviceUrl['host']) ? $serviceUrl['scheme'] . '://' . $serviceUrl['host'] . (isset($serviceUrl['port']) ? ':' . $serviceUrl['port'] : '') : '';
                                        @endphp
                                        <tr>
                                            <td style="white-space: nowrap;">{{ !empty($return['returned_at']) ? \Carbon\Carbon::parse($return['returned_at'])->format('M d, Y') : '-' }}</td>
                                            <td>{{ $returnItem['item_name'] ?? '-' }} @if($return['_status'] === 'Awaiting confirmation')<span class="bmc-pending-badge">Awaiting confirmation</span>@endif</td>
                                            <td class="text-center">{{ $return['returned_quantity'] ?? 0 }}</td>
                                            <td style="white-space: pre-wrap;">{{ $serialText }}</td>
                                            <td>{{ $return['received_by_user']['name'] ?? $return['received_by']['name'] ?? '-' }}</td>
                                            <td style="white-space: pre-wrap; word-break: break-word;">{{ $mor ?: '-' }}</td>
                                            <td style="word-break: break-word;"><div style="white-space: pre-wrap;">{{ $remarks ?: '-' }}</div>
                                                @foreach($attachments as $path)
                                                    @php $path = is_string($path) ? preg_replace('#^bmc/return-proofs/#', 'bmc_return_proofs/', ltrim($path, '/')) : ''; @endphp
                                                    @if($serviceOrigin && preg_match('#^bmc_return_proofs/[A-Za-z0-9._-]+$#', $path))
                                                        <a href="{{ $serviceOrigin . '/' . $path }}" target="_blank" rel="noopener noreferrer" class="d-block mt-1 text-primary">View proof photo{{ count($attachments) > 1 ? ' ' . $loop->iteration : '' }}</a>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td style="white-space: pre-wrap; word-break: break-word;">{{ ($return['approver_remarks'] ?? null) ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted">No return history recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="bmc-modal-card">
                        <h6>Release Details</h6>
                        <div class="bmc-modal-card-body"><table class="bmc-modal-details">
                            <tr><th scope="row">Released At</th><td>{{ !empty($release['released_at']) ? \Carbon\Carbon::parse($release['released_at'])->format('M d, Y g:i A') : '-' }}</td></tr>
                            <tr><th scope="row">Released By</th><td>{{ $release['released_by_user']['name'] ?? $release['released_by']['name'] ?? '-' }}</td></tr>
                            <tr><th scope="row">Expected Return</th><td>{{ !empty($release['expected_return_at']) ? \Carbon\Carbon::parse($release['expected_return_at'])->format('M d, Y') : '-' }}</td></tr>
                            <tr><th scope="row">Signed At</th><td>{{ !empty($release['signed_at']) ? \Carbon\Carbon::parse($release['signed_at'])->format('M d, Y g:i A') : '-' }}</td></tr>
                            <tr><th scope="row">Issuance Notes</th><td style="white-space: pre-wrap;">{{ ($release['release_notes'] ?? null) ?: '-' }}</td></tr>
                            @if(!empty($release['payment_method']) || !empty($release['proof_of_transfer']))
                                <tr><th scope="row">Payment Method</th><td>{{ ($release['payment_method'] ?? null) ?: '-' }}</td></tr>
                                <tr><th scope="row">Proof of Transfer</th><td>@if(!empty($release['proof_of_transfer']))<a class="bmc-proof-link" href="{{ route('bmc.accountability-payment-proof', $release['id']) }}" target="_blank" rel="noopener noreferrer">View proof of transfer</a>@else - @endif</td></tr>
                            @endif
                        </table></div>
                    </section>

                    <section class="bmc-modal-card">
                        <h6>Closure Details</h6>
                        <div class="bmc-modal-card-body"><table class="bmc-modal-details">
                            <tr><th scope="row">Status</th><td>{{ $release['status'] ?? '-' }}</td></tr>
                            <tr><th scope="row">Closed By</th><td>{{ $release['closed_by_user']['name'] ?? $release['closed_by']['name'] ?? '-' }}</td></tr>
                            <tr><th scope="row">Closed At</th><td>{{ !empty($release['closed_at']) ? \Carbon\Carbon::parse($release['closed_at'])->format('M d, Y g:i A') : '-' }}</td></tr>
                        </table></div>
                    </section>
                    @if(!empty($release['extensions']))
                        <section class="bmc-modal-card">
                            <h6>Extension Requests</h6>
                            <div class="bmc-modal-card-body">
                                @foreach($release['extensions'] as $extension)
                                    <div><strong>{{ ucfirst($extension['status'] ?? 'Unknown') }}</strong> · {{ !empty($extension['requested_return_date']) ? \Carbon\Carbon::parse($extension['requested_return_date'])->format('M d, Y') : '-' }} · {{ $extension['reason'] ?? '-' }}@if(!empty($extension['remarks']))<div>Review remarks: {{ $extension['remarks'] }}</div>@endif</div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
    @php
        $extensionRemaining = collect($release['items'] ?? [])->contains(function ($item) { return (int) ($item['released_quantity'] ?? 0) > (int) ($item['returned_quantity'] ?? 0); });
        $extensionPending = collect($release['extensions'] ?? [])->contains(function ($extension) { return ($extension['status'] ?? '') === 'pending'; });
        $extensionEligible = $extensionRemaining && !$extensionPending && in_array($release['status'] ?? '', ['Released', 'Partially Returned'], true)
            && !empty($release['released_at']) && !empty($release['expected_return_at'])
            && \Carbon\Carbon::parse($release['released_at'])->addDays(40)->startOfDay()->gte(today())
            && \Carbon\Carbon::parse($release['expected_return_at'])->startOfDay()->lt(\Carbon\Carbon::parse($release['released_at'])->addDays(40)->startOfDay());
    @endphp
    @if($extensionEligible || !empty($release['extensions']))
        @include('bmc.partials.extension_modal', ['accountability' => $release, 'canSubmitExtension' => $extensionEligible])
    @endif
    @if(empty($release['borrower_signature']) && empty($release['signed_at']) && ($release['status'] ?? '') === 'Released')
        @include('bmc.partials.signature_modal', ['accountability' => $release, 'signatureBorrowing' => $borrowing, 'signatureFromRequest' => true])
    @endif
    @if(!empty($release['borrower_signature']) && $extensionRemaining && in_array($release['status'] ?? '', ['Released', 'Partially Returned'], true))
        @include('bmc.partials.return_modal', ['accountability' => $release])
    @endif
@endforeach
@endsection

@section('javascript')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var method = document.getElementById('bmcPaymentMethod');
        var proof = document.getElementById('bmcPaymentProof');
        var requiredMark = document.getElementById('bmcPaymentProofRequired');
        if (!method || !proof) return;
        function updateProofRequirement() {
            var required = method.value !== 'COD';
            proof.required = required;
            requiredMark.hidden = !required;
        }
        method.addEventListener('change', updateProofRequirement);
        updateProofRequirement();
    });
</script>
@if($errors->any() && old('extension_accountability_id'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('bmcExtension{{ (int) old('extension_accountability_id') }}');
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    });
</script>
@endif
@endsection
