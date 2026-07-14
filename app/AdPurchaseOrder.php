<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdPurchaseOrder extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'ad_id',
        'ad_user_id',
        'business_name',
        'authorized_territory',
        'phone_number',
        'email_address',
        'delivery_address',
        'shipping_type',
        'payment_method',
        'bank_name',
        'reference_no',
        'proof_of_payment',
        'so_number',
        'payment_date',
        'delivery_date',
        'dr_number',
        'si_number',
        'voucher_id',
        'voucher_code',
        'rebate_amount',
        'pickup_discount',
        'delivery_fee',
        'uniform_size',
        'subtotal',
        'total_amount',
        'total_qty',
        'withholding_tax',
        'status',
        'remarks',
        'is_on_hold',
        'submitted_at',
        'created_by',
    ];

    protected $dates = [
        'delivery_date',
        'payment_date',
        'submitted_at',
        'deleted_at',
    ];

    public function items()
    {
        return $this->hasMany(AdPurchaseOrderItem::class, 'ad_purchase_order_id');
    }

    public function partialReceipts()
    {
        return $this->hasMany(AdPurchaseOrderPartialReceipt::class, 'ad_purchase_order_id');
    }

    public function ad()
    {
        return $this->belongsTo(AreaDistributor::class, 'ad_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
