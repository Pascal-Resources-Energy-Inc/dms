<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdPurchaseOrderPaymentProof extends Model
{
    protected $fillable = [
        'ad_purchase_order_id',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function order()
    {
        return $this->belongsTo(AdPurchaseOrder::class, 'ad_purchase_order_id');
    }
}
