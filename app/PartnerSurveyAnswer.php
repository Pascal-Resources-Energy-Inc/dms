<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerSurveyAnswer extends Model
{
    protected $fillable = [
        'partner_survey_id',
        'section_code',
        'question_number',
        'question_code',
        'item_code',
        'option_code',
        'rank',
        'value_text',
        'value_number',
        'is_not_applicable',
    ];

    protected $casts = [
        'value_number' => 'decimal:4',
        'is_not_applicable' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(PartnerSurvey::class, 'partner_survey_id');
    }
}
