<!doctype html>
<html><body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#213047;padding:24px">
<div style="max-width:640px;margin:auto;background:white;border:1px solid #dce5f0;border-radius:14px;overflow:hidden">
    <div style="background:#104c82;color:white;padding:28px"><small>DISTRIBUTOR PURCHASE ORDER</small><h2 style="margin:10px 0">Signed BIR 2307</h2><div>{{ $certificate->snapshot['po_number'] }}</div></div>
    <div style="padding:28px">
        <p>Dear Corporate Accounting Department,</p>
        <p>{{ $certificate->signer_name }} ({{ $certificate->snapshot['role'] }}) submitted the attached signed 2307 template for this DPO.</p>
        <p><strong>Registered business:</strong> {{ $certificate->snapshot['payor_name'] }}<br><strong>DPO withholding tax:</strong> PHP {{ number_format($certificate->snapshot['withholding_tax'], 2) }}<br><strong>Signed:</strong> {{ $certificate->signed_at->format('M d, Y h:i A') }} (Asia/Manila)</p>
        <p>Please review and complete the payee details, reporting period, ATC and reportable payments before issuing the certificate. The attachment includes the order amounts at signing.</p>
        <p>For an edited DPO, this copy supersedes earlier signed submissions for the same order.</p>
    </div>
</div></body></html>
