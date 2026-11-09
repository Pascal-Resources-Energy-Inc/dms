<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AreaDistributor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'ad_reference',
        'name',
        'store_code',
        'email_address',
        'avatar',
        'attachment',
        'status',
        'contact_number',
        'facebook',
        'address',
        'delivery_address',
        'street_address',
        'location_region',
        'location_province',
        'location_city',
        'location_barangay',
        'zipcode',
        'business_name',
        'business_type',
        'has_service_vehicle',
        'business_years_operation',
        'contract_attachments',
        'tin',
        'store_picture',
        'latitude',
        'longitude',
        'center_id',
        'joining_date',
        'withholding_tax',
    ];

    protected $casts = [
        'has_service_vehicle' => 'boolean',
        'business_years_operation' => 'integer',
        'contract_attachments' => 'array',
        'withholding_tax' => 'boolean',
    ];

    // public function areas()
    // {
    //     return $this->hasMany(AreaAd::class, 'ad_id');
    // }

    public function areas()
    {
        return $this->hasMany(AreaAd::class, 'ad_id');
    }

    public function trashedAreas()
    {
        return $this->hasMany(AreaAd::class, 'ad_id')->onlyTrashed();
    }

    public function userAds()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
