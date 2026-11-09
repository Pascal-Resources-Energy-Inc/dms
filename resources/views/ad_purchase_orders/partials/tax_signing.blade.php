@if($requiresTaxSignature ?? false)
<section class="tax-sign-card" id="taxSigning" aria-labelledby="taxSigningTitle">
    <div class="tax-sign-head">
        <div class="tax-sign-icon"><i class="bi bi-pen"></i></div>
        <div><span class="tax-sign-kicker">WITHHOLDING TAX • BIR FORM 2307</span><h5 id="taxSigningTitle">Review, sign, and submit</h5><p>Your signed template will be sent to Corporate Accounting with this DPO.</p></div>
        <a href="{{ route('adpo-tax.template') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View official form <i class="bi bi-box-arrow-up-right"></i></a>
    </div>
    <div class="tax-sign-body">
        <div class="tax-sign-note"><i class="bi bi-info-circle"></i><span>This is a signed template for Accounting review. Accounting completes the payee details, reporting period, ATC and reportable payments. Your signature is placed in the <strong>Payor / Authorized Representative</strong> area.</span></div>
        @if(isset($order))<p class="small text-muted">Please sign again to authorize this updated order. The new copy will replace the earlier submission to Accounting.</p>@endif
        <div class="tax-sign-fields">
            <label>Registered business / taxpayer name <span class="text-danger">*</span><input name="tax_registered_name" class="form-control" maxlength="150" required value="{{ old('tax_registered_name', optional($taxProfile)->business_name ?: optional($taxProfile)->name ?: auth()->user()->name) }}" autocomplete="organization"></label>
            <label>Taxpayer identification number <span class="text-danger">*</span><input name="tax_tin" class="form-control" required maxlength="20" inputmode="numeric" placeholder="000-000-000-00000" value="{{ old('tax_tin', optional($taxProfile)->tin) }}" pattern="[0-9]{3}-?[0-9]{3}-?[0-9]{3}(-?[0-9]{3,5})?"></label>
            <label class="tax-sign-address">Registered address <span class="text-danger">*</span><input name="tax_registered_address" class="form-control" maxlength="240" required value="{{ old('tax_registered_address', optional($taxProfile)->address ?: auth()->user()->delivery_address) }}" autocomplete="street-address"></label>
            <label>ZIP code<input name="tax_zip" class="form-control" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" value="{{ old('tax_zip', optional($taxProfile)->zipcode) }}"></label>
        </div>
        <div class="tax-sign-pad-head"><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role }} · Sign with your mouse, finger, or stylus</small></div><button type="button" class="btn btn-sm btn-outline-secondary" id="taxClearSignature"><i class="bi bi-arrow-counterclockwise"></i> Clear</button></div>
        <div class="tax-sign-pad"><canvas id="taxSignatureCanvas" width="960" height="240" aria-label="Draw your signature" tabindex="0"></canvas><span id="taxSignatureHint">Sign here</span></div>
        <input type="hidden" name="tax_signature" id="taxSignatureData" value="">
        <div class="tax-sign-status" id="taxSignatureStatus" aria-live="polite">Signature required before submission</div>
        <label class="tax-sign-consent"><input type="checkbox" name="tax_consent" value="1" required id="taxConsent"><span>I authorize my signature on this BIR 2307 template for this DPO and its delivery to Corporate Accounting for completion and review.</span></label>
        <p id="taxSignatureError" class="text-danger small mb-0" role="alert"></p>
    </div>
</section>
<link rel="stylesheet" href="{{ asset('css/adpo-tax-signing.css') }}">
<script src="{{ asset('js/adpo-tax-signing.js') }}" defer></script>
@endif
