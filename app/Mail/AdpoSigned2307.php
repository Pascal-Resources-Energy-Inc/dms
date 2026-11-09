<?php

namespace App\Mail;

use App\AdpoTaxCertificate;
use Illuminate\Mail\Mailable;

class AdpoSigned2307 extends Mailable
{
    public $certificate;

    public function __construct(AdpoTaxCertificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function build()
    {
        $number = $this->certificate->snapshot['po_number'];
        return $this->subject('Signed BIR 2307 for review - ' . $number)
            ->view('emails.adpo_signed_2307')
            ->attachData(base64_decode($this->certificate->pdf_base64), $number . '-2307.pdf', ['mime' => 'application/pdf']);
    }
}
