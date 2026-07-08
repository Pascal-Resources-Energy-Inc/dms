<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Voucher extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'area_names',
        'description',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'area_names' => 'array',
        'discount_value' => 'float',
        'minimum_order_amount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function isUsable($subtotal = 0)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->startOfDay()->gt(now())) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->endOfDay()->lt(now())) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ((float) $subtotal < (float) $this->minimum_order_amount) {
            return false;
        }

        return true;
    }

    public function discountFor($subtotal)
    {
        $subtotal = (float) $subtotal;

        if ($this->discount_type === 'percent') {
            return min($subtotal, $subtotal * ((float) $this->discount_value / 100));
        }

        return min($subtotal, (float) $this->discount_value);
    }

    public function isAllowedInArea($areaName)
    {
        $areaNames = $this->normalizedAreaNames();

        return $areaNames->isEmpty() || $this->hasArea($areaName);
    }

    public function hasArea($areaName)
    {
        $normalizedArea = strtolower(trim((string) $areaName));

        return $normalizedArea !== '' && $this->normalizedAreaNames()->contains($normalizedArea);
    }

    public function areaNames()
    {
        $areaNames = $this->area_names;

        if (!is_array($areaNames)) {
            $rawAreaNames = $this->getOriginal('area_names');
            $areaNames = $areaNames ?: $rawAreaNames;
        }

        if (is_string($areaNames)) {
            $decoded = json_decode($areaNames, true);
            $areaNames = is_array($decoded) ? $decoded : explode(',', $areaNames);
        }

        return collect($areaNames ?? [])
            ->map(function ($name) {
                return trim((string) $name);
            })
            ->filter()
            ->unique()
            ->values();
    }

    public function areaNamesLabel($fallback = 'All assigned areas')
    {
        $label = $this->areaNames()->implode(', ');

        return $label !== '' ? $label : $fallback;
    }

    private function normalizedAreaNames()
    {
        return $this->areaNames()
            ->map(function ($name) {
                return strtolower(trim((string) $name));
            })
            ->unique()
            ->values();
    }

    public function statusLabel($subtotal = 0)
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->starts_at && $this->starts_at->startOfDay()->gt(now())) {
            return 'Scheduled';
        }

        if ($this->expires_at && $this->expires_at->endOfDay()->lt(now())) {
            return 'Expired';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Used Up';
        }

        // if ((float) $subtotal < (float) $this->minimum_order_amount) {
        //     return 'Minimum Not Met';
        // }

        return 'Active';
    }

    public function user()
    {
        return $this->belongsTo(User::class);  
    }
}
