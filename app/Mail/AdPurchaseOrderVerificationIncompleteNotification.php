<?php

namespace App\Mail;

use App\AdPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdPurchaseOrderVerificationIncompleteNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $incompleteItems;
    public $warehouseRemarks;

    public function __construct(AdPurchaseOrder $order, $incompleteItems, $warehouseRemarks = null)
    {
        $this->order = $order;
        $this->incompleteItems = $incompleteItems;
        $this->warehouseRemarks = $warehouseRemarks;
    }

    public function build()
    {
        $mail = $this->subject('Action Required: Incomplete Crate / Refill Verification - ' . $this->order->po_number)
            ->view('emails.adpo_verification_incomplete_notification');

        foreach (json_decode($this->order->warehouse_verification_proofs ?: '[]', true) ?: [] as $proof) {
            $file = public_path(data_get($proof, 'path'));
            if (is_file($file)) {
                $mail->attach($file, ['as' => data_get($proof, 'name') ?: basename($file)]);
            }
        }

        return $mail;
    }
}
