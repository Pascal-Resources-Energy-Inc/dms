<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OtherCharge extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'ad_user_id',
        'name',
        'code',
        'description',
        'amount',
        'type',
        'charge_type',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'ad_user_id' => 'integer',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function adUser()
    {
        return $this->belongsTo(User::class, 'ad_user_id');
    }

    public function typeLabel()
    {
        return $this->type === 'discount' ? 'Discount' : 'Charge';
    }

    public function chargeTypeLabel()
    {
        return $this->charge_type === 'percentage' ? 'Percentage' : 'Fixed Amount';
    }

    public function appliesToLabel()
    {
        return ucwords(str_replace('_', ' ', $this->applies_to));
    }

    public function formattedAmount()
    {
        $amount = (float) $this->amount;
        $prefix = $this->type === 'discount' ? '-' : '';

        if ($this->charge_type === 'percentage') {
            return $prefix . rtrim(rtrim(number_format(abs($amount), 2), '0'), '.') . '%';
        }

        return $prefix . 'PHP ' . number_format(abs($amount), 2);
    }
}
