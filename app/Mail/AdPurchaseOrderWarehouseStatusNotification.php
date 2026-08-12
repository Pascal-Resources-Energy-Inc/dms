<?php

namespace App\Mail;

use App\AdPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdPurchaseOrderWarehouseStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $oldStatus;

    public function __construct(AdPurchaseOrder $order, $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    public function build()
    {
        $this->order->loadMissing('paymentProofs', 'verificationItems');
        $mail = $this->subject('Warehouse Action Needed: ' . $this->order->po_number . ' - ' . $this->order->status)
            ->view('emails.adpo_warehouse_status_notification');

        $this->order->paymentProofs->each(function ($proof) use ($mail) {
            $file = public_path($proof->path);
            if (is_file($file)) {
                $mail->attach($file, ['as' => $proof->original_name ?: basename($proof->path)]);
            }
        });

        foreach (json_decode($this->order->verification_proofs ?: '[]', true) ?: [] as $proof) {
            $file = public_path($proof['path'] ?? '');
            if (is_file($file)) {
                $mail->attach($file, ['as' => $proof['name'] ?? basename($file)]);
            }
        }

        return $mail;
    }
}
