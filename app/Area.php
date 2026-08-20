<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Contracts\Auditable;

class Area extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'areas';
    protected $fillable = ['name', 'region', 'province', 'city_municipality', 'barangay'];

    public function usesTimestamps()
    {
        return Schema::hasColumn($this->getTable(), static::CREATED_AT)
            && Schema::hasColumn($this->getTable(), static::UPDATED_AT);
    }

    public function areaAd()
    {
        return $this->hasOne(AreaAd::class, 'area_name', 'name');
    }

    public function assignedAreas()
    {
        return $this->hasMany(AreaAd::class, 'area_name', 'name');
    }

    public function geographicCoverages()
    {
        return $this->hasMany(AreaGeographicCoverage::class)->orderBy('region')->orderBy('province')->orderBy('city_municipality')->orderBy('barangay');
    }
}
