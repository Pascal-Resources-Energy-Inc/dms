<?php

namespace App\Mail;

use App\InventoryTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InventoryReturnRefundNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $movement;
    public $recipientType;

    public function __construct(InventoryTransfer $movement, string $recipientType)
    {
        $this->movement = $movement;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        $subject = $this->recipientType === 'approver'
            ? 'Return and Refund approval required: ' . $this->movement->reference_no
            : 'Return and Refund received: ' . $this->movement->reference_no;

        return $this->subject($subject)->view('emails.inventory_return_refund_notification');
    }
}
