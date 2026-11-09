<?php

namespace App\Services;

use App\AdpoTaxCertificate;
use App\AdpoTaxDelivery;
use App\AdPurchaseOrder;
use App\Mail\AdpoSigned2307;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;

class AdpoTaxService
{
    const DEPARTMENT = 'CORPORATE ACCOUNTING DEPARTMENT';

    public function profile(User $user)
    {
        return $user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? $user->provincialDistributor : $user->ad;
    }

    public function required(User $user)
    {
        if (!$user->hasAreaDistributorAccess()) {
            return false;
        }
        $profile = $this->profile($user);
        return (bool) (optional($profile)->withholding_tax || $user->withholding_tax);
    }

    public function validateSigning(Request $request, User $user, $required = null)
    {
        if (!($required === null ? $this->required($user) : $required)) {
            return null;
        }
        abort_unless($user->hasAreaDistributorAccess(), 403);
        $request->validate([
            'tax_registered_name' => 'required|string|max:150',
            'tax_registered_address' => 'required|string|max:240',
            'tax_tin' => ['required', 'string', 'regex:/^\d{3}-?\d{3}-?\d{3}(-?\d{3,5})?$/'],
            'tax_zip' => 'nullable|digits:4',
            'tax_consent' => 'accepted',
            'tax_signature' => 'required|string|max:150000',
        ]);
        $strokes = json_decode($request->tax_signature, true);
        if (!is_array($strokes) || count($strokes) > 100) {
            $this->invalidSignature();
        }
        $count = 0;
        $length = 0;
        foreach ($strokes as $stroke) {
            if (!is_array($stroke) || count($stroke) < 2 || array_keys($stroke) !== range(0, count($stroke) - 1)) {
                $this->invalidSignature();
            }
            $previous = null;
            foreach ($stroke as $point) {
                if (!is_array($point) || count($point) !== 2 || !isset($point[0], $point[1])
                    || !is_numeric($point[0]) || !is_numeric($point[1])
                    || $point[0] < 0 || $point[0] > 1 || $point[1] < 0 || $point[1] > 1 || ++$count > 5000) {
                    $this->invalidSignature();
                }
                if ($previous) {
                    $length += hypot($point[0] - $previous[0], $point[1] - $previous[1]);
                }
                $previous = $point;
            }
        }
        if ($count < 8 || $length < 0.15) {
            $this->invalidSignature();
        }
        return [
            'name' => trim($request->tax_registered_name),
            'address' => trim($request->tax_registered_address),
            'tin' => preg_replace('/\D/', '', $request->tax_tin),
            'zip' => $request->tax_zip,
            'strokes' => $strokes,
        ];
    }

    private function invalidSignature()
    {
        throw ValidationException::withMessages(['tax_signature' => 'Please draw your signature and confirm the declaration before submitting.']);
    }

    public function capture(AdPurchaseOrder $order, User $user, array $details)
    {
        $snapshot = [
            'po_number' => $order->po_number,
            'role' => $user->role,
            'payor_name' => $details['name'],
            'payor_address' => $details['address'],
            'payor_tin' => $details['tin'],
            'payor_zip' => $details['zip'],
            'source' => $order->order_source === 'provincial_distributor'
                ? 'Provincial Distributor - ' . optional($order->provincialDistributor)->name : 'PREI Warehouse',
            'subtotal' => (float) $order->subtotal,
            'rebate' => (float) $order->rebate_amount,
            'pickup_discount' => (float) $order->pickup_discount,
            'withholding_tax' => (float) $order->withholding_tax,
            'total' => (float) $order->total_amount,
            'consent' => 'I authorize my signature on this BIR 2307 template for this DPO and its delivery to Corporate Accounting for completion and review.',
        ];
        $certificate = new AdpoTaxCertificate([
            'ad_purchase_order_id' => $order->id,
            'signed_by' => $user->id,
            'signer_name' => $user->name,
            'signed_at' => now(),
            'snapshot' => $snapshot,
            'signature' => $details['strokes'],
        ]);
        $pdf = $this->renderPdf($certificate);
        $certificate->pdf_base64 = base64_encode($pdf);
        $certificate->sha256 = hash('sha256', $pdf);
        AdpoTaxCertificate::where('ad_purchase_order_id', $order->id)->whereNull('superseded_at')->update(['superseded_at' => now()]);
        $certificate->save();
        return $certificate;
    }

    public function renderPdf(AdpoTaxCertificate $certificate)
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pages = $pdf->setSourceFile(resource_path('forms/bir-2307-jan-2018.pdf'));
        $s = $certificate->snapshot;
        for ($page = 1; $page <= $pages; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
            if ($page !== 1) {
                continue;
            }
            $pdf->SetTextColor(20, 40, 65);
            $this->pdfText($pdf, 13, 99.3, 194, 4.5, $s['payor_name']);
            $this->pdfText($pdf, 13, 109.5, 174, 4.5, $s['payor_address'], 8);
            $this->pdfText($pdf, 192, 109.5, 16, 4.5, $s['payor_zip'], 8);
            $digits = str_pad($s['payor_tin'], 14, ' ', STR_PAD_RIGHT);
            foreach ([75.3,80,84.6,93.7,98.3,103,112,116.7,121.4,130.7,135.4,140.1,144.8,149.5] as $index => $x) {
                $this->pdfText($pdf, $x, 89.4, 4, 4.5, $digits[$index], 9);
            }
            $pdf->SetDrawColor(21, 50, 85);
            $pdf->SetLineWidth(0.3);
            foreach ($certificate->signature as $stroke) {
                for ($i = 1; $i < count($stroke); $i++) {
                    $pdf->Line(76 + $stroke[$i-1][0] * 64, 255.5 + $stroke[$i-1][1] * 7,
                        76 + $stroke[$i][0] * 64, 255.5 + $stroke[$i][1] * 7);
                }
            }
            $this->pdfText($pdf, 25, 262.5, 165, 2.7, $certificate->signer_name . ' | ' . $s['role'] . ' | TIN ' . $s['payor_tin'], 7, 'C');
            $this->pdfText($pdf, 8, 316, 200, 4, 'DPO ' . $s['po_number'] . ' - SIGNED TEMPLATE FOR ACCOUNTING REVIEW', 8, 'C');
        }
        $pdf->AddPage('P', 'A4');
        $pdf->SetFillColor(14, 65, 115);
        $pdf->Rect(0, 0, 210, 35, 'F');
        $pdf->SetTextColor(255);
        $this->pdfText($pdf, 16, 11, 178, 9, 'DPO / Signed 2307 submission', 19);
        $this->pdfText($pdf, 16, 23, 178, 6, $s['po_number'], 11);
        $pdf->SetTextColor(35, 50, 70);
        $y = 47;
        foreach ([
            'Signed by' => $certificate->signer_name . ' (' . $s['role'] . ')',
            'Signed at' => $certificate->signed_at->copy()->timezone('Asia/Manila')->format('M d, Y h:i A') . ' Asia/Manila',
            'Registered business' => $s['payor_name'],
            'Supplier' => $s['source'],
            'DPO subtotal' => 'PHP ' . number_format($s['subtotal'], 2),
            'Rebate / pickup discount' => 'PHP ' . number_format($s['rebate'] + $s['pickup_discount'], 2),
            'Withholding tax on DPO' => 'PHP ' . number_format($s['withholding_tax'], 2),
            'DPO total' => 'PHP ' . number_format($s['total'], 2),
        ] as $label => $value) {
            $this->pdfText($pdf, 16, $y, 65, 6, $label, 10);
            $this->pdfText($pdf, 83, $y, 111, 6, $value, 10);
            $y += 13;
        }
        $pdf->SetXY(16, $y + 8);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(178, 6, 'For Corporate Accounting review. The official template contains the distributor/payor details and signature. Accounting must confirm the payee details, reporting period, ATC, and reportable payments before issuing the completed certificate. Amounts above are an immutable snapshot of the DPO when signed; they are not a quarterly tax certification.');
        $pdf->Ln(8);
        $pdf->SetX(16);
        $pdf->MultiCell(178, 6, $s['consent']);
        return $pdf->Output('S');
    }

    private function pdfText($pdf, $x, $y, $width, $height, $text, $size = 10, $align = 'L')
    {
        $text = iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $text);
        $pdf->SetFont('Arial', '', $size);
        while ($pdf->GetStringWidth($text) > $width - 2 && $size > 6) {
            $pdf->SetFont('Arial', '', --$size);
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $text, 0, 0, $align);
    }

    public function accountingUsers()
    {
        return User::whereRaw('UPPER(TRIM(department)) = ?', [self::DEPARTMENT])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereRaw('LOWER(TRIM(status)) = ?', ['active']);
            });
    }

    public function deliver(AdpoTaxCertificate $certificate)
    {
        if ($certificate->superseded_at) {
            return;
        }
        $emails = $this->accountingUsers()->pluck('email')->map(function ($email) {
            return strtolower(trim($email));
        })->filter(function ($email) { return filter_var($email, FILTER_VALIDATE_EMAIL); })->unique();
        foreach ($emails as $email) {
            AdpoTaxDelivery::firstOrCreate(['certificate_id' => $certificate->id, 'email' => $email]);
        }
        foreach ($certificate->deliveries()->whereNull('sent_at')->get() as $delivery) {
            if (!$emails->contains($delivery->email)) {
                continue;
            }
            DB::transaction(function () use ($delivery, $certificate) {
                $delivery = AdpoTaxDelivery::lockForUpdate()->findOrFail($delivery->id);
                if ($delivery->sent_at || ($delivery->attempted_at && $delivery->attempted_at->gt(now()->subMinutes(5)))) {
                    return;
                }
                $delivery->attempts++;
                $delivery->attempted_at = now();
                try {
                    if (in_array(config('mail.driver'), ['log', 'array'], true) && !app()->runningUnitTests()) {
                        throw new \RuntimeException('A delivery-capable mail driver is required.');
                    }
                    Mail::to($delivery->email)->send(new AdpoSigned2307($certificate));
                    if (Mail::failures()) {
                        throw new \RuntimeException('Mail transport rejected the recipient.');
                    }
                    $delivery->sent_at = now();
                } catch (\Throwable $exception) {
                    Log::warning('DPO 2307 email pending retry.', ['certificate_id' => $certificate->id, 'delivery_id' => $delivery->id]);
                }
                $delivery->save();
            });
        }
    }

    public function deliverForOrder(AdPurchaseOrder $order)
    {
        try {
            $certificate = $order->taxCertificates()->whereNull('superseded_at')->first();
            if ($certificate) {
                $this->deliver($certificate);
            }
        } catch (\Throwable $exception) {
            Log::warning('DPO saved; 2307 delivery awaits retry.', ['order_id' => $order->id]);
        }
    }
}
