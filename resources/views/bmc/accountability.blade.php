@extends(!empty($preview) ? 'bmc.preview_layout' : 'layouts.header')

@section('css')
@include('bmc.partials.field_styles')
<style>
    .bmc-accountability { max-width: 1050px; margin: 18px auto 30px; color: #172033; }
    .bmc-account-head, .bmc-account-card { background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; margin-bottom: 16px; padding: 20px; }
    .bmc-account-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .bmc-account-head h3 { font-size: 21px; font-weight: 800; margin: 0 0 5px; }
    .bmc-account-card h4 { font-size: 16px; font-weight: 800; margin: 0 0 14px; }
    .bmc-account-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 25px; }
    .bmc-account-grid strong { color: #667085; display: block; font-size: 12px; }
    .bmc-account-grid span { overflow-wrap: anywhere; }
    .bmc-account-table { min-width: 650px; width: 100%; }
    .bmc-account-table th, .bmc-account-table td { padding: 10px; border-bottom: 1px solid #e4e7ec; font-size: 13px; vertical-align: top; }
    .bmc-account-table th { color: #667085; }
    .bmc-account-card label { font-weight: 700; font-size: 13px; }
    .bmc-account-card .form-group { margin-bottom: 15px; }
    .bmc-signature-pad { display: block; max-width: 100%; width: 450px; height: 200px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fff; touch-action: none; }
    .bmc-form-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .bmc-terms li { margin-bottom: 5px; }
    .bmc-serial { display: block; padding: 5px 0; }
    .bmc-history { padding: 10px 0; border-bottom: 1px solid #edf0f5; }
    .bmc-document { width: 210mm; max-width: 100%; min-height: 297mm; margin: 0 auto 16px; padding: 14mm; background: #fff; color: #111; font: 10pt/1.3 Arial, sans-serif; }
    .bmc-document-header { display: flex; justify-content: space-between; align-items: center; gap: 5mm; margin-bottom: 5mm; }
    .bmc-document-header img { width: 1.2in; height: auto; }
    .bmc-document-header p { margin: auto; font-size: 1rem; font-weight: bold; text-align: center; }
    .bmc-document h2 { margin: 5mm 0 2mm; font-size: 10pt; font-weight: bold; }
    .bmc-document section { margin-top: 4mm; }
    .bmc-document-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2mm 5mm; }
    .bmc-parts-single { grid-template-columns: minmax(0, 1fr); }
    .bmc-document-field { display: flex; align-items: baseline; gap: 1.5mm; }
    .bmc-document-field strong { flex: 1; min-width: 8mm; overflow-wrap: anywhere; font-weight: normal; }
    .bmc-document table { width: 100%; border-collapse: collapse; }
    .bmc-document th, .bmc-document td { height: 6mm; padding: 1mm 2mm; border: 1px solid #99c1e5; }
    .bmc-document th { background: #507bc8; color: white; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bmc-document tbody tr:nth-child(even) { background: #f2f7fc; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bmc-document ol { margin: 0; padding-left: 5mm; }
    .bmc-document li + li { margin-top: 1mm; }
    .bmc-document p { margin: 0 0 3mm; }
    .bmc-document-signature { margin-left: auto; width: 80mm; max-width: 100%; }
    .bmc-document-signature-line { position: relative; height: 6mm; margin-bottom: 1mm; border-bottom: 1px solid; }
    .bmc-document-signature-line img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: auto; height: auto; max-width: 100%; max-height: 20mm; object-fit: contain; }
    @media (max-width: 640px) { .bmc-account-grid { grid-template-columns: 1fr; } .bmc-account-card { padding: 16px; } }
    @media print { .bmc-no-print, .bmc-action-form, .bmc-account-card { display: none !important; } .bmc-accountability { max-width: none; } .bmc-document { width: auto; min-height: 0; margin: 0; padding: 0; } }
</style>
@endsection

@section('content')
@php
    $borrowing = $accountability['borrowing'] ?? [];
    $items = collect($accountability['items'] ?? []);
    $releasedItemNames = $items->pluck('item_name');
    $hasBooth = $releasedItemNames->contains('Booth');
    $hasBanner = $releasedItemNames->contains('Negosyo Partner Roll Up Banner') || $releasedItemNames->contains('End User Roll Up Banner');
    $status = $accountability['status'] ?? '';
    $signed = !empty($accountability['borrower_signature']);
    $remaining = $items->sum(function ($item) { return max(0, (int) ($item['released_quantity'] ?? 0) - (int) ($item['returned_quantity'] ?? 0)); });
    $pendingExtension = collect($accountability['extensions'] ?? [])->contains(function ($extension) { return ($extension['status'] ?? '') === 'pending'; });
    $canReturn = $signed && $remaining > 0 && in_array($status, ['Released', 'Partially Returned'], true);
    $canExtend = $remaining > 0 && !$pendingExtension && !empty($accountability['expected_return_at']) && !empty($accountability['released_at']) && in_array($status, ['Released', 'Partially Returned'], true)
        && \Carbon\Carbon::parse($accountability['released_at'])->addDays(40)->startOfDay()->gte(today())
        && \Carbon\Carbon::parse($accountability['expected_return_at'])->startOfDay()->lt(\Carbon\Carbon::parse($accountability['released_at'])->addDays(40)->startOfDay());
    $formatDate = function ($date) { return $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : '-'; };
    $signaturePath = $accountability['borrower_signature'] ?? '';
    $apiOrigin = parse_url((string) config('services.ldr.endpoint'));
    $signatureUrl = null;
    if (preg_match('~^signatures/bmc_accountability_[0-9A-Za-z_.-]+\.png$~', $signaturePath) && !empty($apiOrigin['scheme']) && !empty($apiOrigin['host'])) {
        $signatureUrl = route('bmc.accountability.signature-image', $accountability['id']);
    }
@endphp
<div class="bmc-accountability">
    @if(empty($preview))
    <header class="bmc-account-head">
        <div><h3>Portable Booth &amp; Roll-Up Banner Accountability Form</h3><span class="text-muted">{{ $accountability['accountability_ref'] ?? '-' }} · CEO-MBD-FOR-012-001.B</span></div>
        <div class="bmc-no-print">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('bmc.show', $borrowing['id'] ?? $accountability['marketing_collateral_borrowing_id']) }}">Back to request</a>
        </div>
    </header>
    @if($errors->any())
        <div class="alert alert-danger bmc-no-print" role="alert"><strong>Please check your submission.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @endif

    <main class="bmc-document">
        <header class="bmc-document-header">
            @if(!empty($apiOrigin['scheme']) && !empty($apiOrigin['host']))
                <img src="{{ $apiOrigin['scheme'] . '://' . $apiOrigin['host'] . (isset($apiOrigin['port']) ? ':' . $apiOrigin['port'] : '') . '/images/PASCAL_RESOURCES_ENERGY_INC.png' }}" alt="Pascal Resources Energy, Inc.">
            @endif
            <p>PORTABLE BOOTH &amp; ROLL-UP BANNER ACCOUNTABILITY FORM</p>
        </header>
        <div class="bmc-document-columns">
            <div class="bmc-document-field">Date Issued: <strong>{{ $formatDate($accountability['released_at'] ?? null) }}</strong></div>
            <div class="bmc-document-field">Reference No.: <strong>{{ $accountability['accountability_ref'] ?? '-' }}</strong></div>
        </div>
        <section>
            <h2>USER INFORMATION</h2>
            <div class="bmc-document-columns">
                <div class="bmc-document-field">Name: <strong>{{ trim(($borrowing['borrower_first_name'] ?? '') . ' ' . ($borrowing['borrower_last_name'] ?? '')) ?: '-' }}</strong></div>
                <div class="bmc-document-field">Email: <strong>{{ $borrowing['email'] ?? '-' }}</strong></div>
                <div class="bmc-document-field">Location: <strong>{{ ($borrowing['hub_name'] ?? null) ?: '-' }}</strong></div>
                <div class="bmc-document-field">Phone Number: <strong>{{ $borrowing['phone_number'] ?? '-' }}</strong></div>
                <div class="bmc-document-field">Business Name: <strong>{{ ($borrowing['business_name'] ?? null) ?: '-' }}</strong></div>
            </div>
        </section>
        <section>
            <h2>EVENT DETAILS</h2>
            <div class="bmc-document-columns">
                <div class="bmc-document-field">Date Needed: <strong>{{ $formatDate($borrowing['date_needed'] ?? null) }}</strong></div>
                <div class="bmc-document-field">Event: <strong>{{ $borrowing['event_name'] ?? '-' }}</strong></div>
            </div>
        </section>
        <section>
            <h2>BORROWED ITEMS</h2>
            @if($hasBooth || $hasBanner)
            <div class="bmc-document-columns {{ $hasBooth && $hasBanner ? '' : 'bmc-parts-single' }}">
                @if($hasBooth)
                <div>
                    <p style="margin-bottom: 3px;">Portable Booth Parts</p>
                    <table aria-label="Portable booth parts">
                        <thead><tr><th scope="col">Item</th><th scope="col">Qty Issued</th></tr></thead>
                        <tbody><tr><td>Header</td><td>1 piece</td></tr><tr><td>Body</td><td>1 piece</td></tr><tr><td>Poles</td><td>4 pieces</td></tr><tr><td>Shelves</td><td>3 pieces</td></tr></tbody>
                    </table>
                </div>
                @endif
                @if($hasBanner)
                <div>
                    <p style="margin-bottom: 3px;">Roll-Up Banner</p>
                    <table aria-label="Roll-up banner parts">
                        <thead><tr><th scope="col">Item</th><th scope="col">Qty Issued</th></tr></thead>
                        <tbody><tr><td>Top Rail</td><td>1 piece</td></tr><tr><td>Bungee Pole</td><td>1 piece</td></tr><tr><td>Stand</td><td>1 piece</td></tr><tr><td>Tarpaulin</td><td>1 piece</td></tr></tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif
            <div class="table-responsive" style="margin-top: 3mm;">
                <table aria-label="Borrowed item serial numbers and remarks">
                    <thead><tr><th scope="col">Item</th><th scope="col">Serial Number</th><th scope="col">MBD Remarks</th><th scope="col">Borrower Remarks</th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                            @if(!empty($item['serials']))
                                @foreach($item['serials'] as $serial)
                                    <tr><td>{{ $item['item_name'] ?? '-' }}</td><td>{{ $serial['serial_number'] ?? '-' }}</td><td>{{ ($serial['mbd_remarks'] ?? null) ?: '-' }}</td><td>{{ ($serial['borrower_remarks'] ?? null) ?: '-' }}</td></tr>
                                @endforeach
                            @else
                                <tr><td>{{ $item['item_name'] ?? '-' }}</td><td>{{ ($item['serial_number'] ?? null) ?: '-' }}</td><td>{{ ($item['condition_on_release'] ?? null) ?: '-' }}</td><td>-</td></tr>
                            @endif
                        @empty
                            <tr><td colspan="4">No released items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section>
            <h2>TERMS OF USE</h2>
            <ol>
                <li>The borrower accepts full responsibility for the care and return of the item borrowed in its original condition.</li>
                <li>The item borrowed is exclusive for use or display of Gaz Lite products only.</li>
                <li>Displaying other products or brands on the portable booth is prohibited.</li>
            </ol>
        </section>
        <section>
            <h2>PAYMENT AGREEMENT</h2>
            <ol>
                <li>The Borrower shall pay a Security Deposit of ₱{{ number_format($items->sum('deposit_amount'), 2) }}. The security deposit deposit will be refunded to the Borrower only after the items has been returned to PREI and found to be in good condition.</li>
                <li>If the Borrower wishes to extend the borrowing period, they must submit another BMF Requests indicating the requested extension of date and the reason of its extension.</li>
                <li>The Parties affirm and acknowledge that they have carefully read and understood the terms and conditions.</li>
            </ol>
        </section>
        <section>
            <h2>ACKNOWLEDGEMENT</h2>
            <p>By signing below, I acknowledge that I have received the item/s borrowed in the condition noted above, and I agree to the terms of use and liability as stated in this form.</p>
            <div class="bmc-document-signature">
                <div style="color: #555; font-size: 8pt; text-align: right;">Received: {{ $signed ? $formatDate($accountability['signed_at'] ?? null) : '' }}</div>
                <div style="font-size: 11px; margin-top: 6px;">Understood and Received by</div>
                <div class="bmc-document-signature-line">@if($signatureUrl)<img src="{{ $signatureUrl }}" alt="Borrower signature">@endif</div>
                <span style="font-size: 11px;">{{ trim(($borrowing['borrower_first_name'] ?? '') . ' ' . ($borrowing['borrower_last_name'] ?? '')) }}</span>
                <div style="font-size: 11px;">{{ $borrowing['borrower_profile'] ?? '' }}</div>
            </div>
        </section>
    </main>

    @if(empty($preview))
    @if($status === 'Released' && !$signed && empty($accountability['signed_at']))
        <section class="bmc-account-card bmc-action-form" id="sign-accountability">
            <h4>Sign Accountability</h4>
            <p class="text-muted small">Review the form above before signing.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bmcSignature{{ $accountability['id'] }}">Sign Accountability</button>
        </section>
    @endif

    <section class="bmc-account-card">
        <h4>Return History</h4>
        @forelse(collect($accountability['pending_returns'] ?? [])->map(function ($entry) { $entry['_display_status'] = 'Awaiting confirmation'; return $entry; })->concat(collect($accountability['return_history'] ?? [])->map(function ($entry) { $entry['_display_status'] = 'Confirmed'; return $entry; })) as $entry)
            @php
                $historyItem = $items->firstWhere('id', $entry['bmc_accountability_item_id'] ?? null);
                $historySerials = json_decode($entry['serial_numbers'] ?? '', true);
                $historySerialText = is_array($historySerials) && isset($historySerials['numbers']) ? implode(', ', $historySerials['numbers']) : ($entry['serial_numbers'] ?? '-');
            @endphp
            <div class="bmc-history"><strong>{{ $entry['_display_status'] }}</strong> · {{ $historyItem['item_name'] ?? 'Item' }} · {{ $entry['returned_quantity'] ?? 0 }} · {{ $historySerialText }}<br><small class="text-muted">{{ $formatDate($entry['returned_at'] ?? null) }} · {{ $entry['return_mode'] ?? '-' }}</small><div style="white-space: pre-wrap;">{{ $entry['borrower_remarks'] ?? '' }}</div>@if(!empty($entry['approver_remarks']))<div>Approver remarks: {{ $entry['approver_remarks'] }}</div>@endif</div>
        @empty
            <p class="text-muted mb-0">No return requests yet.</p>
        @endforelse
    </section>

    @if($canReturn)
        <section class="bmc-account-card bmc-action-form" id="request-return">
            <h4>Return items</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestBmcReturn{{ $accountability['id'] }}">Return items</button>
        </section>
    @endif

    <section class="bmc-account-card">
        <h4>Extension Requests</h4>
        @forelse($accountability['extensions'] ?? [] as $extension)
            <div class="bmc-history"><strong>{{ ucfirst($extension['status'] ?? 'Unknown') }}</strong> · Requested return: {{ $formatDate($extension['requested_return_date'] ?? null) }}<div>{{ $extension['reason'] ?? '' }}</div>@if(!empty($extension['remarks']))<div>Review remarks: {{ $extension['remarks'] }}</div>@endif</div>
        @empty
            <p class="text-muted mb-0">No extension requests yet.</p>
        @endforelse
    </section>
    @if($canExtend || !empty($accountability['extensions']))
        <section class="bmc-account-card bmc-action-form" id="request-extension">
            <h4>Request Extension</h4>
            @if($canExtend)<p class="text-muted small">The requested date must be after the current return date, today or later, and within 40 days of release.</p>@endif
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bmcExtension{{ $accountability['id'] }}">{{ $canExtend ? 'Request Extension' : 'View Extension History' }}</button>
        </section>
    @endif
    @endif
</div>
@if(empty($preview))
@if($status === 'Released' && !$signed && empty($accountability['signed_at']))
    @include('bmc.partials.signature_modal', ['signatureBorrowing' => $borrowing])
@endif
@if($canReturn)
    @include('bmc.partials.return_modal', ['accountability' => $accountability])
@endif
@if($canExtend || !empty($accountability['extensions']))
    @include('bmc.partials.extension_modal', ['accountability' => $accountability, 'canSubmitExtension' => $canExtend])
@endif
@endif
@endsection

@section('javascript')
<script>
    document.querySelectorAll('.bmc-document-signature-line img').forEach(function (img) {
        function centerSignature() {
            try {
                var canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                var context = canvas.getContext('2d');
                context.drawImage(img, 0, 0);
                var pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                var left = canvas.width, top = canvas.height, right = -1, bottom = -1;
                for (var y = 0; y < canvas.height; y++) {
                    for (var x = 0; x < canvas.width; x++) {
                        var i = (y * canvas.width + x) * 4;
                        if (pixels[i + 3] > 0 && (pixels[i] < 250 || pixels[i + 1] < 250 || pixels[i + 2] < 250)) {
                            left = Math.min(left, x); top = Math.min(top, y);
                            right = Math.max(right, x); bottom = Math.max(bottom, y);
                        }
                    }
                }
                if (right < left || bottom < top) return;
                var cropped = document.createElement('canvas');
                cropped.width = right - left + 1;
                cropped.height = bottom - top + 1;
                cropped.getContext('2d').drawImage(canvas, left, top, cropped.width, cropped.height, 0, 0, cropped.width, cropped.height);
                img.src = cropped.toDataURL('image/png');
            } catch (error) {
                console.warn('Unable to trim signature image:', error);
            }
        }
        if (img.complete && img.naturalWidth) centerSignature();
        else img.addEventListener('load', centerSignature, { once: true });
    });
    @if($errors->any() && old('extension_accountability_id') && $canExtend)
        document.addEventListener('DOMContentLoaded', function () {
            var extensionModal = document.getElementById('bmcExtension{{ (int) old('extension_accountability_id') }}');
            if (extensionModal) bootstrap.Modal.getOrCreateInstance(extensionModal).show();
        });
    @endif

</script>
@endsection
