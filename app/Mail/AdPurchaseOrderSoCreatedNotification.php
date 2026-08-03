<?php

namespace App\Mail;

use App\AdPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdPurchaseOrderSoCreatedNotification extends Mailable
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
        $this->order->loadMissing(['items', 'paymentProofs']);

        $mail = $this->subject('SO Created: ' . $this->order->po_number . ' - ' . ($this->order->so_number ?: 'No SO Number'))
            ->view('emails.adpo_so_created_notification');

        $proofPaths = $this->order->paymentProofs
            ->mapWithKeys(function ($proof) {
                return [$proof->path => $proof->original_name];
            });

        if ($this->order->proof_of_payment && !$proofPaths->has($this->order->proof_of_payment)) {
            $proofPaths->put($this->order->proof_of_payment, basename($this->order->proof_of_payment));
        }

        $proofPaths->each(function ($name, $path) use ($mail) {
            $filePath = public_path($path);

            if (is_file($filePath)) {
                $mail->attach($filePath, ['as' => $name]);
            }
        });

        return $mail;
    }
}
