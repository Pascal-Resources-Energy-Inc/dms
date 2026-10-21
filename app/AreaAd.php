<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class AreaAd extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'ad_areas';
    protected $fillable = [
        'ad_id',
        'ad_user_id',
        'project_type',
        'area_name',
        'joining_date',
        'package_amount',
        'user_role',
    ];
    protected $casts = [
        'package_amount' => 'decimal:2',
    ];
    protected $dates = ['deleted_at'];
    
    public function distributor()
    {
        return $this->belongsTo(AreaDistributor::class, 'ad_id', 'id');
    }
}
