<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OrderDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'transaction_id',
        'item',
        'item_description',
        'ad_id',
        'qty',
        'price',
        'dealer_id',
        'ad_address',
        'payment_method',
        'delivery_type',
        'delivery_fee',
        'is_guest',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_address',
        'guest_authorized_territory',
        'guest_notes',
        'remarks',
        'status'
    ];
    public function dealer()
    {
        return $this->belongsTo(User::class,'dealer_id','id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function adDealer()
    {
        return $this->belongsTo(Dealer::class,'dealer_id','user_id');
    } 

    public function ad()
    {
        return $this->belongsTo(AreaDistributor::class,'ad_id','id');
    }
}
