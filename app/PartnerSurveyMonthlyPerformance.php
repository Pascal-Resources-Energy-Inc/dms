<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerSurveyMonthlyPerformance extends Model
{
    protected $fillable = [
        'partner_survey_id',
        'period_month',
        'sales_php',
        'volume_kg',
        'target_php',
        'data_source',
        'calculated_at',
    ];

    protected $casts = [
        'sales_php' => 'decimal:2',
        'volume_kg' => 'decimal:3',
        'target_php' => 'decimal:2',
    ];

    protected $dates = [
        'period_month',
        'calculated_at',
    ];

    public function survey()
    {
        return $this->belongsTo(PartnerSurvey::class, 'partner_survey_id');
    }
}
