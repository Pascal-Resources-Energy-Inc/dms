<?php

namespace App\Http\Controllers;

use App\AreaDistributor;
use App\OrderDetail;
use App\PartnerSurvey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SurveyController extends Controller {
    private const CHANNELS = [
        'mega_dealers' => 'Mega Dealers',
        'dealers' => 'Dealers / Sari-sari Stores',
        'household' => 'Direct Household / End Users',
        'online' => 'Online / Social Media',
        'institutional' => 'Institutional / Commercial',
        'samgyupsal' => 'Samgyupsal',
        'other' => 'Other',
    ];

    // form page
    public function create(Request $request) {
        $distributor = $this->distributor($request);
        $territories = $distributor->areas()->orderBy('id')->get();
        list($periodStart, $periodEnd) = $this->currentSurveyPeriod();

        $survey = PartnerSurvey::with([
            'answers',
            'channelMixes',
            'monthlyPerformances',
        ])
            ->where('area_distributor_id', $distributor->id)
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->first();

        $monthlyPerformance = $survey
            && $survey->status === PartnerSurvey::STATUS_SUBMITTED
            && $survey->monthlyPerformances->isNotEmpty()
                ? $this->savedPerformanceRows($survey)
                : $this->buildPerformanceRows($distributor, $periodStart, $periodEnd);

        return view('surveys.create', [
            'channels' => self::CHANNELS,
            'distributor' => $distributor,
            'formData' => $this->formData($distributor, $territories, $survey),
            'isSubmitted' => $survey && $survey->status === PartnerSurvey::STATUS_SUBMITTED,
            'monthlyPerformance' => $monthlyPerformance,
            'periodEnd' => $periodEnd,
            'periodStart' => $periodStart,
            'survey' => $survey,
            'territories' => $territories,
        ]);
    }

    public function store(Request $request) {
        $distributor = $this->distributor($request);
        list($periodStart, $periodEnd) = $this->currentSurveyPeriod();
        $isSubmitting = $request->input('submission_intent') === 'submit';

        $this->surveyValidator($request, $isSubmitting)->validate();

        $existing = PartnerSurvey::where('area_distributor_id', $distributor->id)
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->first();

        if ($existing && $existing->status === PartnerSurvey::STATUS_SUBMITTED) {
            return redirect()
                ->route('surveys.create')
                ->with('error', 'This survey has already been submitted and can no longer be changed.');
        }

        $performanceRows = $this->buildPerformanceRows(
            $distributor,
            $periodStart,
            $periodEnd
        );

        DB::transaction(function () use (
            $request,
            $distributor,
            $periodStart,
            $periodEnd,
            $isSubmitting,
            $performanceRows
        ) {
            $survey = PartnerSurvey::firstOrNew([
                'area_distributor_id' => $distributor->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ]);

            $survey->fill([
                'distributor_name' => trim((string) $request->input('distributor_name')),
                'business_name' => trim((string) $request->input('business_name')),
                'province' => trim((string) $request->input('province')),
                'awarded_territories' => trim((string) $request->input('territories_display')),
                'status' => $isSubmitting
                    ? PartnerSurvey::STATUS_SUBMITTED
                    : PartnerSurvey::STATUS_IN_PROGRESS,
                'respondent_user_id' => $request->user()->id,
                'respondent_name' => $request->user()->name,
                'started_at' => $survey->started_at ?: Carbon::now(),
                'submitted_at' => $isSubmitting ? Carbon::now() : null,
            ]);
            $survey->save();

            $survey->answers()->delete();
            $answerRows = $this->answerRows($request);
            if (!empty($answerRows)) {
                $survey->answers()->createMany($answerRows);
            }

            $survey->channelMixes()->delete();
            $channelRows = $this->channelRows($request);
            if (!empty($channelRows)) {
                $survey->channelMixes()->createMany($channelRows);
            }

            $survey->monthlyPerformances()->delete();
            $survey->monthlyPerformances()->createMany(
                $performanceRows->map(function ($row) {
                    return [
                        'period_month' => $row['period_month'],
                        'sales_php' => $row['sales_php'],
                        'volume_kg' => $row['volume_kg'],
                        'target_php' => $row['target_php'],
                        'data_source' => 'order_details',
                        'calculated_at' => Carbon::now(),
                    ];
                })->all()
            );
        });

        return redirect()
            ->route('surveys.create')
            ->with(
                'success',
                $isSubmitting
                    ? 'Your partner survey has been submitted.'
                    : 'Your survey draft has been saved.'
            );
    }

    private function distributor(Request $request) {
        $user = $request->user();
        abort_unless($user, 401);

        $distributor = AreaDistributor::where('user_id', $user->id)->first();

        abort_unless($distributor, 403, 'This form is for Area Distributors only.');

        return $distributor;
    }

    private function currentSurveyPeriod() {
        $today = Carbon::today();

        if ($today->month >= 7) {
            $periodStart = Carbon::create($today->year, 1, 1)->startOfDay();
            $periodEnd = Carbon::create($today->year, 6, 30)->endOfDay();
        } else {
            $previousYear = $today->year - 1;
            $periodStart = Carbon::create($previousYear, 7, 1)->startOfDay();
            $periodEnd = Carbon::create($previousYear, 12, 31)->endOfDay();
        }

        return [$periodStart, $periodEnd];
    }

    private function surveyValidator(Request $request, $isSubmitting)
    {
        $required = $isSubmitting ? 'required' : 'nullable';

        $rules = [
            'submission_intent' => ['required', Rule::in(['draft', 'submit'])],
            'distributor_name' => [$required, 'string', 'max:255'],
            'business_name' => [$required, 'string', 'max:255'],
            'province' => [$required, 'string', 'max:150'],
            'territories_display' => [$required, 'string', 'max:5000'],
            'distributor_tenure' => [
                $required,
                Rule::in([
                    'under_6_months',
                    '6_to_12_months',
                    '1_to_2_years',
                    '2_to_3_years',
                    'over_3_years',
                ]),
            ],
            'channel_sales' => ['nullable', 'array'],
            'channel_sales.*' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'channel_customers' => ['nullable', 'array'],
            'channel_customers.*' => ['nullable', 'integer', 'min:0'],
            'channel_other_label' => ['nullable', 'string', 'max:255'],
            'performance_reasons' => [$required, 'string', 'max:5000'],
            'business_performance' => [
                $required,
                Rule::in([
                    'declining_significantly',
                    'declining_slightly',
                    'stable',
                    'growing_slightly',
                    'growing_strongly',
                ]),
            ],
            'growth_constraints' => [
                $isSubmitting ? 'required' : 'nullable',
                'array',
                $isSubmitting ? 'size:3' : 'max:3',
            ],
            'growth_constraints.*' => ['string', 'max:100'],
            'growth_constraint_other' => ['nullable', 'string', 'max:255'],
            'territory_assessment' => [
                $required,
                Rule::in([
                    'definitely_yes',
                    'probably_yes',
                    'unsure',
                    'probably_no',
                    'definitely_no',
                ]),
            ],
            'territory_target_reason' => ['nullable', 'string', 'max:5000'],
            'growth_target_supports' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1', 'max:3'],
            'growth_target_supports.*' => ['string', 'max:100'],
            'growth_target_support_other' => ['nullable', 'string', 'max:255'],
            'growth_confidence' => [$required, 'integer', 'between:1,5'],
            'direct_delivery' => [
                $required,
                Rule::in(['regularly', 'occasionally', 'customer_pickup']),
            ],
            'delivery_methods' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1'],
            'delivery_methods.*' => ['string', 'max:100'],
            'delivery_method_other' => ['nullable', 'string', 'max:255'],
            'prei_delivery_reliance' => [$required, Rule::in(['never', 'rarely', 'sometimes', 'often', 'most_of_time'])],
            'current_delivery_km' => [$required, 'numeric', 'min:0', 'max:999999'],
            'possible_delivery_km' => [$required, 'numeric', 'min:0', 'max:999999'],
            'weekly_deliveries' => [
                $required,
                Rule::in(['none', '1_to_10', '11_to_25', '26_to_50', '51_to_100', 'over_100']),
            ],
            'fulfillment_speed' => [
                $required,
                Rule::in(['within_30_minutes', '31_to_60_minutes', '1_to_2_hours', 'same_day', 'next_day_or_longer', 'depends_on_location']),
            ],
            'resources' => [$isSubmitting ? 'required' : 'nullable', 'array'],
            'resources.*' => [$required, 'integer', 'min:0', 'max:65535'],
            'resource_other_label' => ['nullable', 'string', 'max:255'],
            'staffing' => [$isSubmitting ? 'required' : 'nullable', 'array'],
            'staffing.*' => [$required, 'integer', 'min:0', 'max:65535'],
            'customer_acquisition_roles' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1'],
            'customer_acquisition_roles.*' => ['string', 'max:100'],
            'customer_acquisition_other' => ['nullable', 'string', 'max:255'],
            'order_methods' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1'],
            'order_methods.*' => ['string', 'max:100'],
            'order_method_other' => ['nullable', 'string', 'max:255'],
            'capacity_for_50_percent_growth' => [
                $required,
                Rule::in(['current_resources', 'minor_resources', 'significant_investment', 'no', 'unsure']),
            ],
            'operational_constraints' => [
                $isSubmitting ? 'required' : 'nullable',
                'array',
                $isSubmitting ? 'size:3' : 'max:3',
            ],
            'operational_constraints.*' => ['string', 'max:100'],
            'operational_constraint_other' => ['nullable', 'string', 'max:255'],
            'operational_confidence' => [$required, 'integer', 'between:1,5'],
            'consumer_reasons' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'consumer_reasons.*' => ['string', 'max:100', 'distinct'],
            'consumer_reason_other' => ['nullable', 'string', 'max:255'],
            'ratings' => ['nullable', 'array'],
            'common_consumer_complaint' => [$required, 'string', 'max:5000'],
            'support_effectiveness' => ['nullable', 'array'],
            'household_opportunity' => [$required, 'integer', 'between:1,5'],
            'support_priorities' => [
                $isSubmitting ? 'required' : 'nullable',
                'array',
                $isSubmitting ? 'size:3' : 'max:3',
            ],
            'support_priorities.*' => ['string', 'max:100', 'distinct'],
            'support_priority_other' => ['nullable', 'array', 'max:3'],
            'support_priority_other.*' => ['nullable', 'string', 'max:255'],
            'household_reach' => [
                $required,
                Rule::in(['definitely_yes', 'probably_yes', 'unsure', 'probably_no', 'definitely_no']),
            ],
            'household_opportunity_areas' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1'],
            'household_opportunity_areas.*' => ['string', 'max:100'],
            'household_opportunity_other' => ['nullable', 'string', 'max:255'],
            'household_barriers' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'household_barriers.*' => ['string', 'max:100'],
            'household_barrier_other' => ['nullable', 'string', 'max:255'],
            'household_selling_interest' => [
                $required,
                Rule::in(['definitely_yes', 'probably_yes', 'maybe', 'probably_no', 'definitely_no']),
            ],
            'adjacent_areas_available' => [$required, Rule::in(['yes', 'no', 'unsure'])],
            'adjacent_area_names' => ['nullable', 'string', 'max:5000'],
            'expansion_interest' => [
                $required,
                Rule::in([
                    'definitely_yes',
                    'probably_yes',
                    'maybe',
                    'probably_no',
                    'definitely_no',
                ]),
            ],
            'expansion_constraint' => [
                $required,
                Rule::in(['capital', 'manpower', 'delivery_logistics', 'demand_uncertainty', 'competition', 'current_territory_potential', 'prei_support', 'profitability', 'no_interest', 'other']),
            ],
            'expansion_constraint_other' => ['nullable', 'string', 'max:255'],
            'expansion_timing' => [$required, 'string', 'max:1000'],
            'motivating_incentives' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'motivating_incentives.*' => ['string', 'max:100'],
            'motivating_incentive_other' => ['nullable', 'string', 'max:255'],
            'reward_achievements' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'reward_achievements.*' => ['string', 'max:100'],
            'reward_achievement_other' => ['nullable', 'string', 'max:255'],
            'thanksgiving_motivation' => [$required, 'integer', 'between:1,5'],
            'thanksgiving_values' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1', 'max:3'],
            'thanksgiving_values.*' => ['string', 'max:100'],
            'thanksgiving_value_other' => ['nullable', 'string', 'max:255'],
            'thanksgiving_recognition' => [$isSubmitting ? 'required' : 'nullable', 'array', 'min:1', 'max:3'],
            'thanksgiving_recognition.*' => ['string', 'max:100'],
            'thanksgiving_recognition_other' => ['nullable', 'string', 'max:255'],
            'thanksgiving_change' => [$required, 'string', 'max:5000'],
            'thanksgiving_event_value' => [$required, 'integer', 'between:1,5'],
            'thanksgiving_event_activities' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'thanksgiving_event_activities.*' => ['string', 'max:100'],
            'thanksgiving_event_activity_other' => ['nullable', 'string', 'max:255'],
            'thanksgiving_future' => [
                $required,
                Rule::in(['reward_celebration', 'sales_incentive', 'recognition', 'business_development', 'combined', 'other']),
            ],
            'thanksgiving_future_other' => ['nullable', 'string', 'max:255'],
            'future_growth_likelihood' => [$required, 'integer', 'between:0,10'],
            'partner_confidence_change' => [
                $required,
                Rule::in(['much_lower', 'slightly_lower', 'same', 'higher', 'much_higher']),
            ],
            'growth_actions' => [$isSubmitting ? 'required' : 'nullable', 'array', $isSubmitting ? 'size:3' : 'max:3'],
            'growth_actions.*' => [$required, 'string', 'max:1000'],
            'leader_change' => [$required, 'string', 'max:5000'],
            'management_note' => [$required, 'string', 'max:5000'],
        ];

        $ratingItems = [
            'people' => ['sales_support', 'responsiveness', 'knowledge', 'growth_help', 'communication'],
            'products' => ['quality', 'availability', 'value', 'competitiveness', 'acceptance'],
            'promises' => ['delivery_reliability', 'order_accuracy', 'commitments', 'incentive_fulfillment', 'problem_handling', 'pricing_communication'],
        ];

        foreach ($ratingItems as $matrix => $items) {
            foreach ($items as $item) {
                $rules['ratings.' . $matrix . '.' . $item] = [
                    $required,
                    'integer',
                    'between:1,5',
                ];
            }
        }

        foreach ([
            'sales_support',
            'local_marketing',
            'promotions',
            'retailer_activation',
            'marketing_materials',
            'training_support',
            'incentive_programs',
            'digital_support',
        ] as $item) {
            $rules['support_effectiveness.' . $item] = [
                $required,
                Rule::in(['1', '2', '3', '4', '5', 'na']),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $isSubmitting) {
            if (!$isSubmitting) {
                return;
            }

            $sales = collect($request->input('channel_sales', []));
            $customers = collect($request->input('channel_customers', []));
            $hasChannel = $sales->contains(function ($value) {
                return is_numeric($value) && (float) $value > 0;
            }) || $customers->contains(function ($value) {
                return is_numeric($value) && (int) $value > 0;
            });

            if (!$hasChannel) {
                $validator->errors()->add(
                    'channel_sales',
                    'Enter monthly sales or active customers for at least one channel.'
                );
            }

            $otherHasValue = (float) data_get($sales, 'other', 0) > 0
                || (int) data_get($customers, 'other', 0) > 0;
            if ($otherHasValue && trim((string) $request->input('channel_other_label')) === '') {
                $validator->errors()->add(
                    'channel_other_label',
                    'Specify the name of the other sales channel.'
                );
            }

            if ((int) data_get($request->input('resources', []), 'other', 0) > 0
                && trim((string) $request->input('resource_other_label')) === '') {
                $validator->errors()->add(
                    'resource_other_label',
                    'Specify the other delivery resource.'
                );
            }

            $needsReason = in_array(
                $request->input('territory_assessment'),
                ['probably_no', 'definitely_no'],
                true
            );
            if ($needsReason && trim((string) $request->input('territory_target_reason')) === '') {
                $validator->errors()->add(
                    'territory_target_reason',
                    'Explain what is preventing the territory from reaching the target.'
                );
            }

            if ($request->input('adjacent_areas_available') === 'yes'
                && trim((string) $request->input('adjacent_area_names')) === '') {
                $validator->errors()->add(
                    'adjacent_area_names',
                    'List the adjacent areas you believe you could serve profitably.'
                );
            }

            foreach ([
                'growth_constraints' => 'growth_constraint_other',
                'growth_target_supports' => 'growth_target_support_other',
                'delivery_methods' => 'delivery_method_other',
                'operational_constraints' => 'operational_constraint_other',
                'consumer_reasons' => 'consumer_reason_other',
                'household_opportunity_areas' => 'household_opportunity_other',
                'household_barriers' => 'household_barrier_other',
                'motivating_incentives' => 'motivating_incentive_other',
                'reward_achievements' => 'reward_achievement_other',
                'thanksgiving_values' => 'thanksgiving_value_other',
                'thanksgiving_recognition' => 'thanksgiving_recognition_other',
                'thanksgiving_event_activities' => 'thanksgiving_event_activity_other',
            ] as $selection => $otherField) {
                if (in_array('other', (array) $request->input($selection, []), true)
                    && trim((string) $request->input($otherField)) === '') {
                    $validator->errors()->add($otherField, 'Specify the selected other response.');
                }
            }

            foreach ((array) $request->input('support_priorities', []) as $index => $priority) {
                if ($priority === 'other'
                    && trim((string) data_get($request->input('support_priority_other', []), $index)) === '') {
                    $validator->errors()->add(
                        'support_priority_other.' . $index,
                        'Specify the other support for this rank.'
                    );
                }
            }

            foreach ([
                'expansion_constraint' => 'expansion_constraint_other',
                'thanksgiving_future' => 'thanksgiving_future_other',
            ] as $selection => $otherField) {
                if ($request->input($selection) === 'other'
                    && trim((string) $request->input($otherField)) === '') {
                    $validator->errors()->add($otherField, 'Specify the selected other response.');
                }
            }
        });

        return $validator;
    }

    private function answerRows(Request $request)
    {
        $rows = [];

        $this->addOption($rows, 'A', '4', 'distributor_tenure', $request->input('distributor_tenure'));
        $this->addText($rows, 'B', '6', 'performance_change_reasons', $request->input('performance_reasons'));
        $this->addOption($rows, 'C', '7', 'business_performance_6m', $request->input('business_performance'));
        $this->addOptions($rows, 'C', '8', 'growth_limiting_factors', $request->input('growth_constraints', []));
        $this->addText($rows, 'C', '8', 'growth_limiting_factors_other', $request->input('growth_constraint_other'));

        $this->addOption(
            $rows,
            'D',
            '9',
            'territories_can_reach_200k',
            $request->input('territory_assessment')
        );
        $this->addText($rows, 'D', '10', 'territory_200k_no_reason', $request->input('territory_target_reason'));
        $this->addOptions($rows, 'D', '11', 'supports_to_reach_200k', $request->input('growth_target_supports', []));
        $this->addText($rows, 'D', '11', 'supports_to_reach_200k_other', $request->input('growth_target_support_other'));
        $this->addNumber($rows, 'D', '12', 'growth_confidence_if_constraint_removed', $request->input('growth_confidence'));

        $this->addOption($rows, 'E', '13', 'delivers_directly', $request->input('direct_delivery'));
        $this->addOptions($rows, 'E', '14', 'delivery_methods', $request->input('delivery_methods', []));
        $this->addText($rows, 'E', '14', 'delivery_methods_other', $request->input('delivery_method_other'));
        $this->addOption($rows, 'E', '15', 'prei_delivery_reliance', $request->input('prei_delivery_reliance'));
        $this->addNumber($rows, 'E', '16', 'current_delivery_distance_km', $request->input('current_delivery_km'));
        $this->addNumber($rows, 'E', '17', 'maximum_service_distance_km', $request->input('possible_delivery_km'));
        $this->addOption($rows, 'E', '18', 'weekly_delivery_frequency', $request->input('weekly_deliveries'));
        $this->addOption($rows, 'E', '19', 'order_fulfillment_speed', $request->input('fulfillment_speed'));

        $resources = (array) $request->input('resources', []);
        foreach ([
            'motorcycles' => 'motorcycle',
            'tricycles' => 'tricycle',
            'cars_vans' => 'car_van',
            'trucks' => 'truck',
            'delivery_riders' => 'delivery_personnel',
            'other' => 'other',
        ] as $input => $itemCode) {
            $this->addItemNumber(
                $rows,
                'E',
                '20',
                'delivery_resources',
                $itemCode,
                data_get($resources, $input)
            );
        }
        $this->addText($rows, 'E', '20', 'delivery_resources_other_label', $request->input('resource_other_label'));

        foreach ((array) $request->input('staffing', []) as $itemCode => $value) {
            $this->addItemNumber($rows, 'E', '21', 'staffing', $itemCode, $value);
        }
        $this->addOptions($rows, 'E', '22', 'customer_acquisition_roles', $request->input('customer_acquisition_roles', []));
        $this->addText($rows, 'E', '22', 'customer_acquisition_roles_other', $request->input('customer_acquisition_other'));
        $this->addOptions($rows, 'E', '23', 'customer_order_methods', $request->input('order_methods', []));
        $this->addText($rows, 'E', '23', 'customer_order_methods_other', $request->input('order_method_other'));
        $this->addOption($rows, 'E', '24', 'capacity_for_50_percent_growth', $request->input('capacity_for_50_percent_growth'));
        $this->addOptions($rows, 'E', '25', 'growth_operational_constraints', $request->input('operational_constraints', []));
        $this->addText($rows, 'E', '25', 'growth_operational_constraints_other', $request->input('operational_constraint_other'));
        $this->addNumber($rows, 'E', '26', 'operational_growth_confidence', $request->input('operational_confidence'));

        $this->addOptions($rows, 'G', '35', 'consumer_choice_reasons', $request->input('consumer_reasons', []));
        $this->addText($rows, 'G', '35', 'consumer_choice_reasons_other', $request->input('consumer_reason_other'));
        $this->addText($rows, 'G', '37', 'common_consumer_complaint', $request->input('common_consumer_complaint'));

        $ratings = (array) $request->input('ratings', []);
        foreach ([
            'people' => ['H', '38', 'people_rating'],
            'products' => ['H', '39', 'product_rating'],
            'promises' => ['H', '40', 'promises_rating'],
        ] as $matrix => $question) {
            foreach ((array) data_get($ratings, $matrix, []) as $itemCode => $score) {
                $this->addItemNumber(
                    $rows,
                    $question[0],
                    $question[1],
                    $question[2],
                    $itemCode,
                    $score
                );
            }
        }

        foreach ((array) $request->input('support_effectiveness', []) as $itemCode => $score) {
            $this->addItemRating($rows, 'J', '45', 'support_effectiveness', $itemCode, $score);
        }
        $this->addRankedOptions(
            $rows,
            'J',
            '46',
            'support_priorities_2027',
            $request->input('support_priorities', []),
            $request->input('support_priority_other', [])
        );

        $this->addOption($rows, 'K', '47', 'unreached_households_exist', $request->input('household_reach'));
        $this->addNumber($rows, 'K', '48', 'direct_household_growth_potential', $request->input('household_opportunity'));
        $this->addOptions($rows, 'K', '49', 'household_opportunity_areas', $request->input('household_opportunity_areas', []));
        $this->addText($rows, 'K', '49', 'household_opportunity_areas_other', $request->input('household_opportunity_other'));
        $this->addOptions($rows, 'K', '50', 'household_growth_barriers', $request->input('household_barriers', []));
        $this->addText($rows, 'K', '50', 'household_growth_barriers_other', $request->input('household_barrier_other'));
        $this->addOption($rows, 'K', '51', 'household_selling_interest', $request->input('household_selling_interest'));

        $this->addOption($rows, 'L', '52', 'adjacent_areas_available', $request->input('adjacent_areas_available'));
        $this->addText($rows, 'L', '53', 'adjacent_area_names', $request->input('adjacent_area_names'));
        $this->addOption($rows, 'L', '54', 'territory_expansion_interest', $request->input('expansion_interest'));
        $this->addOption($rows, 'L', '55', 'territory_expansion_constraint', $request->input('expansion_constraint'));
        $this->addText($rows, 'L', '55', 'territory_expansion_constraint_other', $request->input('expansion_constraint_other'));
        $this->addText($rows, 'L', '56', 'territory_expansion_timing', $request->input('expansion_timing'));

        $this->addOptions($rows, 'M', '56', 'motivating_incentives', $request->input('motivating_incentives', []));
        $this->addText($rows, 'M', '56', 'motivating_incentives_other', $request->input('motivating_incentive_other'));
        $this->addOptions($rows, 'M', '58', 'reward_achievements', $request->input('reward_achievements', []));
        $this->addText($rows, 'M', '58', 'reward_achievements_other', $request->input('reward_achievement_other'));

        $this->addNumber($rows, 'N', '59', 'thanksgiving_motivation', $request->input('thanksgiving_motivation'));
        $this->addOptions($rows, 'N', '60', 'thanksgiving_values', $request->input('thanksgiving_values', []));
        $this->addText($rows, 'N', '60', 'thanksgiving_values_other', $request->input('thanksgiving_value_other'));
        $this->addOptions($rows, 'N', '61', 'thanksgiving_recognition', $request->input('thanksgiving_recognition', []));
        $this->addText($rows, 'N', '61', 'thanksgiving_recognition_other', $request->input('thanksgiving_recognition_other'));
        $this->addText($rows, 'N', '63', 'thanksgiving_program_change', $request->input('thanksgiving_change'));
        $this->addNumber($rows, 'N', '64', 'thanksgiving_event_value', $request->input('thanksgiving_event_value'));
        $this->addOptions($rows, 'N', '65', 'thanksgiving_event_activities', $request->input('thanksgiving_event_activities', []));
        $this->addText($rows, 'N', '65', 'thanksgiving_event_activities_other', $request->input('thanksgiving_event_activity_other'));
        $this->addOption($rows, 'N', '66', 'thanksgiving_future', $request->input('thanksgiving_future'));
        $this->addText($rows, 'N', '66', 'thanksgiving_future_other', $request->input('thanksgiving_future_other'));

        $this->addNumber($rows, 'O', '67', 'future_growth_likelihood', $request->input('future_growth_likelihood'));
        $this->addOption($rows, 'O', '68', 'partner_confidence_change', $request->input('partner_confidence_change'));

        foreach ((array) $request->input('growth_actions', []) as $index => $action) {
            $this->addText(
                $rows,
                'P',
                '70',
                'prei_growth_actions',
                $action,
                $index + 1
            );
        }
        $this->addText($rows, 'P', '71', 'leader_change_first', $request->input('leader_change'));
        $this->addText($rows, 'P', '72', 'management_additional_comments', $request->input('management_note'));

        return $rows;
    }

    private function channelRows(Request $request)
    {
        $sales = (array) $request->input('channel_sales', []);
        $customers = (array) $request->input('channel_customers', []);
        $rows = [];

        foreach (array_keys(self::CHANNELS) as $channelCode) {
            $salesValue = data_get($sales, $channelCode);
            $customerValue = data_get($customers, $channelCode);
            $otherLabel = $channelCode === 'other'
                ? trim((string) $request->input('channel_other_label'))
                : null;

            $hasValue = ($salesValue !== null && $salesValue !== '')
                || ($customerValue !== null && $customerValue !== '')
                || ($otherLabel !== null && $otherLabel !== '');

            if (!$hasValue) {
                continue;
            }

            $rows[] = [
                'channel_code' => $channelCode,
                'other_channel' => $otherLabel ?: null,
                'estimated_monthly_sales' => $salesValue === null || $salesValue === ''
                    ? null
                    : (float) $salesValue,
                'active_customers' => $customerValue === null || $customerValue === ''
                    ? null
                    : (int) $customerValue,
            ];
        }

        return $rows;
    }

    private function formData($distributor, $territories, $survey)
    {
        $data = [
            'distributor_name' => $distributor->name,
            'business_name' => $distributor->business_name,
            'province' => $distributor->location_province,
            'territories_display' => '',
            'channel_sales' => [],
            'channel_customers' => [],
            'growth_constraints' => [],
            'growth_target_supports' => [],
            'territory_assessment' => null,
            'delivery_methods' => [],
            'resources' => [],
            'staffing' => [],
            'customer_acquisition_roles' => [],
            'order_methods' => [],
            'operational_constraints' => [],
            'consumer_reasons' => [],
            'ratings' => [],
            'support_effectiveness' => [],
            'support_priorities' => [],
            'support_priority_other' => [],
            'household_opportunity_areas' => [],
            'household_barriers' => [],
            'motivating_incentives' => [],
            'reward_achievements' => [],
            'thanksgiving_values' => [],
            'thanksgiving_recognition' => [],
            'thanksgiving_event_activities' => [],
            'growth_actions' => [],
        ];

        if (!$survey) {
            return $data;
        }

        $data['distributor_name'] = $survey->distributor_name ?: $data['distributor_name'];
        $data['business_name'] = $survey->business_name ?: $data['business_name'];
        $data['province'] = $survey->province ?: $data['province'];
        $data['territories_display'] = $survey->awarded_territories ?: $data['territories_display'];

        $answers = $survey->answers->groupBy('question_code');
        $first = function ($code) use ($answers) {
            return $answers->get($code, collect())->first();
        };
        $option = function ($code) use ($first) {
            $answer = $first($code);
            return $answer ? $answer->option_code : null;
        };
        $number = function ($code) use ($first) {
            $answer = $first($code);
            return $answer ? $answer->value_number : null;
        };
        $text = function ($code) use ($first) {
            $answer = $first($code);
            return $answer ? $answer->value_text : null;
        };
        $options = function ($code) use ($answers) {
            return $answers->get($code, collect())
                ->pluck('option_code')
                ->filter()
                ->values()
                ->all();
        };
        $itemNumbers = function ($code) use ($answers) {
            return $answers->get($code, collect())
                ->pluck('value_number', 'item_code')
                ->all();
        };
        $itemIntegers = function ($code) use ($itemNumbers) {
            return collect($itemNumbers($code))
                ->map(function ($value) {
                    return $value === null ? null : (int) $value;
                })
                ->all();
        };
        $rankedOptions = function ($code) use ($answers) {
            return $answers->get($code, collect())
                ->sortBy('rank')
                ->pluck('option_code')
                ->filter()
                ->values()
                ->all();
        };
        $itemRatings = function ($code) use ($answers) {
            return $answers->get($code, collect())
                ->mapWithKeys(function ($answer) {
                    return [
                        $answer->item_code => $answer->is_not_applicable
                            ? 'na'
                            : (string) (int) $answer->value_number,
                    ];
                })
                ->all();
        };

        $data['distributor_tenure'] = $option('distributor_tenure');
        $data['performance_reasons'] = $text('performance_change_reasons');
        $data['business_performance'] = $option('business_performance_6m');
        $data['growth_constraints'] = $options('growth_limiting_factors');
        $data['growth_constraint_other'] = $text('growth_limiting_factors_other');
        $data['territory_assessment'] = $option('territories_can_reach_200k');
        $data['territory_target_reason'] = $text('territory_200k_no_reason');
        $data['growth_target_supports'] = $options('supports_to_reach_200k');
        $data['growth_target_support_other'] = $text('supports_to_reach_200k_other');
        $data['growth_confidence'] = $number('growth_confidence_if_constraint_removed');
        $data['direct_delivery'] = $option('delivers_directly');
        $data['delivery_methods'] = $options('delivery_methods');
        $data['delivery_method_other'] = $text('delivery_methods_other');
        $data['prei_delivery_reliance'] = $option('prei_delivery_reliance');
        $data['current_delivery_km'] = $number('current_delivery_distance_km');
        $data['possible_delivery_km'] = $number('maximum_service_distance_km');
        $data['weekly_deliveries'] = $option('weekly_delivery_frequency');
        $data['fulfillment_speed'] = $option('order_fulfillment_speed');

        $deliveryResources = $itemIntegers('delivery_resources');
        $staffing = $itemIntegers('staffing');
        $data['resources'] = [
            'motorcycles' => data_get($deliveryResources, 'motorcycle'),
            'tricycles' => data_get($deliveryResources, 'tricycle'),
            'cars_vans' => data_get($deliveryResources, 'car_van'),
            'trucks' => data_get($deliveryResources, 'truck'),
            'delivery_riders' => data_get($deliveryResources, 'delivery_personnel'),
            'other' => data_get($deliveryResources, 'other'),
        ];
        $data['resource_other_label'] = $text('delivery_resources_other_label');
        $data['staffing'] = $staffing;
        $data['customer_acquisition_roles'] = $options('customer_acquisition_roles');
        $data['customer_acquisition_other'] = $text('customer_acquisition_roles_other');
        $data['order_methods'] = $options('customer_order_methods');
        $data['order_method_other'] = $text('customer_order_methods_other');
        $data['capacity_for_50_percent_growth'] = $option('capacity_for_50_percent_growth');
        $data['operational_constraints'] = $options('growth_operational_constraints');
        $data['operational_constraint_other'] = $text('growth_operational_constraints_other');
        $data['operational_confidence'] = $number('operational_growth_confidence');
        $data['consumer_reasons'] = $options('consumer_choice_reasons');
        $data['consumer_reason_other'] = $text('consumer_choice_reasons_other');
        $data['ratings'] = [
            'people' => $itemNumbers('people_rating'),
            'products' => $itemNumbers('product_rating'),
            'promises' => $itemNumbers('promises_rating'),
        ];
        $data['common_consumer_complaint'] = $text('common_consumer_complaint');
        $data['support_effectiveness'] = $itemRatings('support_effectiveness');
        $supportPriorityAnswers = $answers
            ->get('support_priorities_2027', collect())
            ->sortBy('rank')
            ->values();
        $data['support_priorities'] = $supportPriorityAnswers
            ->pluck('option_code')
            ->filter()
            ->values()
            ->all();
        $data['support_priority_other'] = $supportPriorityAnswers
            ->mapWithKeys(function ($answer, $index) {
                return $answer->option_code === 'other' && $answer->value_text
                    ? [$index => $answer->value_text]
                    : [];
            })
            ->all();

        // Load the earlier single-field format when reopening an existing draft.
        $legacySupportPriorityOther = $text('support_priorities_2027_other');
        if ($legacySupportPriorityOther && empty($data['support_priority_other'])) {
            $otherRank = array_search('other', $data['support_priorities'], true);
            if ($otherRank !== false) {
                $data['support_priority_other'][$otherRank] = $legacySupportPriorityOther;
            }
        }
        $data['household_reach'] = $option('unreached_households_exist');
        $data['household_opportunity'] = $number('direct_household_growth_potential');
        $data['household_opportunity_areas'] = $options('household_opportunity_areas');
        $data['household_opportunity_other'] = $text('household_opportunity_areas_other');
        $data['household_barriers'] = $options('household_growth_barriers');
        $data['household_barrier_other'] = $text('household_growth_barriers_other');
        $data['household_selling_interest'] = $option('household_selling_interest');
        $data['adjacent_areas_available'] = $option('adjacent_areas_available');
        $data['adjacent_area_names'] = $text('adjacent_area_names');
        $data['expansion_interest'] = $option('territory_expansion_interest');
        $data['expansion_constraint'] = $option('territory_expansion_constraint');
        $data['expansion_constraint_other'] = $text('territory_expansion_constraint_other');
        $data['expansion_timing'] = $text('territory_expansion_timing');
        $data['motivating_incentives'] = $options('motivating_incentives');
        $data['motivating_incentive_other'] = $text('motivating_incentives_other');
        $data['reward_achievements'] = $options('reward_achievements');
        $data['reward_achievement_other'] = $text('reward_achievements_other');
        $data['thanksgiving_motivation'] = $number('thanksgiving_motivation');
        $data['thanksgiving_values'] = $options('thanksgiving_values');
        $data['thanksgiving_value_other'] = $text('thanksgiving_values_other');
        $data['thanksgiving_recognition'] = $options('thanksgiving_recognition');
        $data['thanksgiving_recognition_other'] = $text('thanksgiving_recognition_other');
        $data['thanksgiving_change'] = $text('thanksgiving_program_change');
        $data['thanksgiving_event_value'] = $number('thanksgiving_event_value');
        $data['thanksgiving_event_activities'] = $options('thanksgiving_event_activities');
        $data['thanksgiving_event_activity_other'] = $text('thanksgiving_event_activities_other');
        $data['thanksgiving_future'] = $option('thanksgiving_future');
        $data['thanksgiving_future_other'] = $text('thanksgiving_future_other');
        $data['future_growth_likelihood'] = $number('future_growth_likelihood');
        $data['partner_confidence_change'] = $option('partner_confidence_change');
        $data['growth_actions'] = $answers
            ->get('prei_growth_actions', collect())
            ->sortBy('rank')
            ->pluck('value_text')
            ->values()
            ->all();
        $data['leader_change'] = $text('leader_change_first');
        $data['management_note'] = $text('management_additional_comments');

        foreach ($survey->channelMixes as $channel) {
            $data['channel_sales'][$channel->channel_code] = $channel->estimated_monthly_sales;
            $data['channel_customers'][$channel->channel_code] = $channel->active_customers;
            if ($channel->channel_code === 'other') {
                $data['channel_other_label'] = $channel->other_channel;
            }
        }

        return $data;
    }

    // order_details as the source of truth for sales perfomance
    private function buildPerformanceRows($distributor, Carbon $periodStart, Carbon $periodEnd)
    {
        $orders = OrderDetail::where('ad_id', $distributor->id)
            ->where('status', 'Completed')
            ->whereBetween(DB::raw('DATE(COALESCE(completed_at, date, created_at))'), [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->get([
                'qty',
                'price',
                'item',
                'item_description',
                'completed_at',
                'date',
                'created_at',
            ]);

        $ordersByMonth = $orders->groupBy(function ($order) {
            $date = $order->completed_at ?: ($order->date ?: $order->created_at);
            return Carbon::parse($date)->format('Y-m');
        });

        return collect(range(0, 5))->map(function ($offset) use (
            $periodStart,
            $ordersByMonth
        ) {
            $month = $periodStart->copy()->addMonths($offset)->startOfMonth();
            $monthOrders = $ordersByMonth->get($month->format('Y-m'), collect());
            $sales = $monthOrders->sum(function ($order) {
                return (float) $order->qty * (float) $order->price;
            });
            $volume = $monthOrders->sum(function ($order) {
                return (float) $order->qty * $this->orderWeightKg($order);
            });

            return [
                'period_month' => $month->toDateString(),
                'month' => $month->format('M Y'),
                'sales_php' => round($sales, 2),
                'volume_kg' => round($volume, 3),
                'target_php' => 200000,
            ];
        });
    }

    private function savedPerformanceRows(PartnerSurvey $survey)
    {
        return $survey->monthlyPerformances->map(function ($performance) {
            $month = Carbon::parse($performance->period_month);

            return [
                'period_month' => $month->toDateString(),
                'month' => $month->format('M Y'),
                'sales_php' => (float) $performance->sales_php,
                'volume_kg' => (float) $performance->volume_kg,
                'target_php' => (float) $performance->target_php,
            ];
        })->values();
    }

    private function orderWeightKg($order)
    {
        $label = trim((string) $order->item . ' ' . (string) $order->item_description);

        if (!preg_match('/(\d+(?:\.\d+)?)\s*(kg|kgs|kilograms?|g|grams?)\b/i', $label, $matches)) {
            return 0.0;
        }

        $weight = (float) $matches[1];
        $unit = strtolower($matches[2]);

        return in_array($unit, ['g', 'gram', 'grams'], true)
            ? $weight / 1000
            : $weight;
    }

    private function addOption(array &$rows, $section, $number, $code, $value)
    {
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'option_code' => $value,
        ];
    }

    private function addOptions(array &$rows, $section, $number, $code, $values)
    {
        foreach ((array) $values as $value) {
            $this->addOption($rows, $section, $number, $code, $value);
        }
    }

    private function addRankedOptions(array &$rows, $section, $number, $code, $values, $otherValues = [])
    {
        foreach (array_values((array) $values) as $index => $value) {
            if (!$this->hasValue($value)) {
                continue;
            }

            $row = $this->baseAnswer($section, $number, $code) + [
                'option_code' => $value,
                'rank' => $index + 1,
            ];

            $otherValue = data_get($otherValues, $index);
            if ($value === 'other' && $this->hasValue($otherValue)) {
                $row['value_text'] = trim((string) $otherValue);
            }

            $rows[] = $row;
        }
    }

    private function addText(array &$rows, $section, $number, $code, $value, $rank = null)
    {
        $value = is_string($value) ? trim($value) : $value;
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'rank' => $rank,
            'value_text' => $value,
        ];
    }

    private function addNumber(array &$rows, $section, $number, $code, $value)
    {
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'value_number' => $value,
        ];
    }

    private function addItemOption(
        array &$rows,
        $section,
        $number,
        $code,
        $itemCode,
        $value
    ) {
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'item_code' => $itemCode,
            'option_code' => $value,
        ];
    }

    private function addItemNumber(
        array &$rows,
        $section,
        $number,
        $code,
        $itemCode,
        $value
    ) {
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'item_code' => $itemCode,
            'value_number' => $value,
        ];
    }

    private function addItemRating(
        array &$rows,
        $section,
        $number,
        $code,
        $itemCode,
        $value
    ) {
        if (!$this->hasValue($value)) {
            return;
        }

        $rows[] = $this->baseAnswer($section, $number, $code) + [
            'item_code' => $itemCode,
            'value_number' => $value === 'na' ? null : $value,
            'is_not_applicable' => $value === 'na',
        ];
    }

    private function baseAnswer($section, $number, $code)
    {
        return [
            'section_code' => $section,
            'question_number' => $number,
            'question_code' => $code,
            'is_not_applicable' => false,
        ];
    }

    private function hasValue($value)
    {
        return $value !== null && $value !== '';
    }
}
