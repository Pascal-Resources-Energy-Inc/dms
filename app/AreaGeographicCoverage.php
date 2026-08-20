<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AreaGeographicCoverage extends Model
{
    protected $fillable = ['region', 'province', 'city_municipality', 'barangay'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
