<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerSurveyChannelMix extends Model
{
    protected $fillable = [
        'partner_survey_id',
        'channel_code',
        'other_channel',
        'estimated_monthly_sales',
        'active_customers',
    ];

    protected $casts = [
        'estimated_monthly_sales' => 'decimal:2',
        'active_customers' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(PartnerSurvey::class, 'partner_survey_id');
    }
}
