<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ProvincialDistributorArea extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'provincial_distributor_id',
        'user_id',
        'area_name',
        'project_type',
        'user_role',
        'joining_date',
        'package_amount',
    ];

    protected $casts = [
        'package_amount' => 'decimal:2',
    ];

    public function distributor()
    {
        return $this->belongsTo(ProvincialDistributor::class, 'provincial_distributor_id');
    }
}
