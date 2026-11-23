<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// this will serve as the snapshot for survey record per AD

class PartnerSurvey extends Model {
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'area_distributor_id',
        'questionnaire_version',
        'period_start',
        'period_end',
        'distributor_name',
        'business_name',
        'province',
        'awarded_territories',
        'status',
        'respondent_user_id',
        'respondent_name',
        'started_at',
        'submitted_at',
        'management_classification',
        'classification_rationale',
        'agreed_actions',
        'classified_by_user_id',
        'classified_at',
    ];

    protected $dates = [
        'period_start',
        'period_end',
        'started_at',
        'submitted_at',
        'classified_at',
    ];

    public function distributor()
    {
        return $this->belongsTo(AreaDistributor::class, 'area_distributor_id');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_user_id');
    }

    public function answers()
    {
        return $this->hasMany(PartnerSurveyAnswer::class);
    }

    public function channelMixes()
    {
        return $this->hasMany(PartnerSurveyChannelMix::class);
    }

    public function monthlyPerformances()
    {
        return $this->hasMany(PartnerSurveyMonthlyPerformance::class)
            ->orderBy('period_month');
    }
}
