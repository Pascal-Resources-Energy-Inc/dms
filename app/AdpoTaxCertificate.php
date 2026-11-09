<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdpoTaxCertificate extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['pdf_base64', 'signature', 'snapshot'];
    protected $casts = ['snapshot' => 'array', 'signature' => 'array'];
    protected $dates = ['signed_at', 'superseded_at'];

    public function deliveries()
    {
        return $this->hasMany(AdpoTaxDelivery::class, 'certificate_id');
    }
}
