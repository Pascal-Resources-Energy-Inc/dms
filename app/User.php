<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use Notifiable;

    public const ROLE_PROVINCIAL_DISTRIBUTOR = 'Provincial Distributor';
    public const ROLE_AREA_DISTRIBUTOR = 'Area Distributor';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'serial', 'read_notifications',
        'warehouse', 'delivery_address', 'designation', 'employee_number', 'department',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'read_notifications' => 'array',
    ];

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id');
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    // public function ad()
    // {
    //     return $this->belongsTo(AreaDistributor::class, 'id', 'user_id');
    // }

    // public function ad()
    // {
    //     return $this->hasOne(AreaDistributor::class);
    // }

    public function ad()
    {
        return $this->hasOne(AreaDistributor::class, 'user_id', 'id');
    }
    
    public function redeemedHistory()
    {
        return $this->hasMany(RedeemedHistory::class);
    }

    /**
     * Provincial Distributors use the same partner portal and permissions as
     * Area Distributors while keeping their distinct stored role.
     */
    public function hasAreaDistributorAccess(): bool
    {
        return in_array($this->role, [
            self::ROLE_AREA_DISTRIBUTOR,
            self::ROLE_PROVINCIAL_DISTRIBUTOR,
        ], true);
    }

    public function raffleEntries()
    {
        return $this->hasMany(RaffleEntry::class);
    }
}
