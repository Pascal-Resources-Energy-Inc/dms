@php
    $taxDocument = $order->taxCertificates()->whereNull('superseded_at')->with('deliveries')->first();
    $taxSentCount = $taxDocument ? $taxDocument->deliveries->filter(function ($delivery) { return $delivery->sent_at !== null; })->count() : 0;
    $canSeeTaxDocument = $taxDocument && ((int) $taxDocument->signed_by === (int) auth()->id() || app(\App\Services\AdpoTaxService::class)->accountingUsers()->whereKey(auth()->id())->exists());
@endphp
@if($canSeeTaxDocument)
    <link rel="stylesheet" href="{{ asset('css/adpo-tax-signing.css') }}">
    <div class="tax-document">
        <div><strong><i class="bi bi-file-earmark-check"></i> Signed BIR 2307 template</strong><p>{{ $taxDocument->signer_name }} · {{ $taxDocument->signed_at->format('M d, Y h:i A') }}</p>
            <p>{{ $taxSentCount }} of {{ $taxDocument->deliveries->count() }} Accounting emails sent.
            @if($taxDocument->deliveries->isEmpty())Awaiting an active Corporate Accounting recipient.@elseif($taxSentCount < $taxDocument->deliveries->count())Pending emails will be retried.@endif</p>
            @if(session('tax_notice'))<p role="status">{{ session('tax_notice') }}</p>@endif
        </div>
        <div class="d-flex gap-2 flex-wrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('adpo-tax.download', $taxDocument->id) }}">Download signed 2307</a>
            @if($taxDocument->deliveries->isEmpty() || $taxSentCount < $taxDocument->deliveries->count())
                <form method="POST" action="{{ route('adpo-tax.retry', $taxDocument->id) }}">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit">Retry Accounting email</button></form>
            @endif
        </div>
    </div>
@endif
