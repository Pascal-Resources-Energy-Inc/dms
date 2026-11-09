<?php

namespace App\Http\Controllers;

use App\AdpoTaxCertificate;
use App\Services\AdpoTaxService;
use Illuminate\Http\Request;

class AdpoTaxCertificateController extends Controller
{
    public function template()
    {
        abort_unless(auth()->user()->hasAreaDistributorAccess(), 403);
        return response()->file(resource_path('forms/bir-2307-jan-2018.pdf'));
    }

    public function download($id)
    {
        $certificate = AdpoTaxCertificate::findOrFail($id);
        $user = auth()->user();
        $accounting = app(AdpoTaxService::class)->accountingUsers()->whereKey($user->id)->exists();
        abort_unless((int) $certificate->signed_by === (int) $user->id || $accounting, 403);
        return response(base64_decode($certificate->pdf_base64), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="DPO-2307-' . $certificate->id . '.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function retry(Request $request, $id)
    {
        $certificate = AdpoTaxCertificate::findOrFail($id);
        $user = $request->user();
        $accounting = app(AdpoTaxService::class)->accountingUsers()->whereKey($user->id)->exists();
        abort_unless((int) $certificate->signed_by === (int) $user->id || $accounting, 403);
        app(AdpoTaxService::class)->deliver($certificate);
        return back()->with('tax_notice', 'Accounting delivery checked. Any unsent emails remain available for retry.');
    }
}
