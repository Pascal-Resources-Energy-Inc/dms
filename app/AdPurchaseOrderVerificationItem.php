<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdPurchaseOrderVerificationItem extends Model
{
    protected $fillable = [
        'ad_purchase_order_id',
        'ad_purchase_order_item_id',
        'product_name',
        'ordered_qty',
        'submitted_qty',
        'warehouse_verified_qty',
    ];

    public function order()
    {
        return $this->belongsTo(AdPurchaseOrder::class, 'ad_purchase_order_id');
    }

    public function item()
    {
        return $this->belongsTo(AdPurchaseOrderItem::class, 'ad_purchase_order_item_id');
    }
}
