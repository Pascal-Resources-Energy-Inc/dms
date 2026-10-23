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

    /** @var bool|null */
    protected $customerFullNameVisibility;

    public const ROLE_PROVINCIAL_DISTRIBUTOR = 'Provincial Distributor';
    public const ROLE_AREA_DISTRIBUTOR = 'Area Distributor';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'serial', 'read_notifications',
        'warehouse', 'delivery_address', 'designation', 'employee_number', 'department', 'approval_types',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'read_notifications' => 'array',
        'approval_types' => 'array',
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

    /**
     * Customer names remain visible for every role except MFI accounts whose
     * assigned MFI explicitly disables full-name visibility.
     */
    public function canViewCustomerFullNames(): bool
    {
        if ($this->customerFullNameVisibility !== null) {
            return $this->customerFullNameVisibility;
        }

        if (strcasecmp(trim((string) $this->role), 'MFI') !== 0) {
            return $this->customerFullNameVisibility = true;
        }

        $mfiName = strtoupper(trim((string) ($this->mfi_type ?? '')));
        if ($mfiName === '') {
            return $this->customerFullNameVisibility = false;
        }

        $mfi = Mfi::query()
            ->whereRaw('UPPER(TRIM(name)) = ?', [$mfiName])
            ->first();

        return $this->customerFullNameVisibility = $mfi
            ? (bool) $mfi->show_customer_full_name
            : false;
    }

    public function customerNameForDisplay($fullName): string
    {
        $fullName = trim(preg_replace('/\s+/u', ' ', (string) $fullName));

        if ($fullName === '' || $fullName === '-') {
            return '-';
        }

        if ($this->canViewCustomerFullNames()) {
            return $fullName;
        }

        return collect(preg_split('/\s+/u', $fullName))
            ->filter()
            ->map(function ($namePart) {
                return mb_strtoupper(mb_substr($namePart, 0, 1)) . '•••';
            })
            ->implode(' ');
    }
}
