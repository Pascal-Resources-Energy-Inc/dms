<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Dealer extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'user_id', 'name', 'email_address', 'number', 'facebook', 
        'address', 'store_name', 'store_type', 'dealer_type', 'status', 'center', 'area', 'spo',
        'location_region', 'location_province', 'location_city', 'location_barangay',
        'postal_code', 'street_address', 'latitude', 'longitude'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sales()
    {
        return $this->hasMany(TransactionDetail::class, 'dealer_id', 'user_id');
    }
    
    public function orders()
    {
        return $this->hasMany(OrderDetail::class, 'dealer_id', 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function ad()
    {
        return $this->belongsTo(AreaDistributor::class, 'ad_id');
    }
}
