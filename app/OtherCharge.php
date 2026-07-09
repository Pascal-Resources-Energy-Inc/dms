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
        if ($this->charge_type === 'percentage') {
            return 'Percentage';
        }

        if ($this->charge_type === 'discount') {
            return 'Discount';
        }

        return 'Fixed Amount';
    }

    public function appliesToLabel()
    {
        return ucwords(str_replace('_', ' ', $this->applies_to));
    }

    public function formattedAmount()
    {
        if ($this->charge_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $this->amount, 2), '0'), '.') . '%';
        }

        $amount = (float) $this->amount;

        if ($this->charge_type === 'discount') {
            return '-PHP ' . number_format(abs($amount), 2);
        }

        return 'PHP ' . number_format($amount, 2);
    }
}
