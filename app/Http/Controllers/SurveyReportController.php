<?php

namespace App\Http\Controllers;

use App\PartnerSurvey;
use App\PartnerSurveyAnswer;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    private const SECTION_LABELS = [
        '1' => 'Distributor profile',
        '2' => 'Performance and growth',
        '3' => 'Operating and delivery capability',
        '4' => 'Why Gaz Lite Wins',
        '5' => 'Household opportunity, expansion and incentives',
        '6' => 'Thanksgiving program and future relationship',
        '7' => 'Final management questions',
        'other' => 'Other stored responses',
    ];

    private const FORM_SECTION_QUESTIONS = [
        '1' => [
            'distributor_tenure',
        ],
        '2' => [
            'performance_change_reasons',
            'business_performance_6m',
            'growth_limiting_factors',
            'growth_limiting_factors_other',
            'territories_can_reach_200k',
            'territory_200k_no_reason',
            'supports_to_reach_200k',
            'supports_to_reach_200k_other',
            'growth_confidence_if_constraint_removed',
        ],
        '3' => [
            'delivers_directly',
            'delivery_methods',
            'delivery_methods_other',
            'prei_delivery_reliance',
            'current_delivery_distance_km',
            'maximum_service_distance_km',
            'weekly_delivery_frequency',
            'order_fulfillment_speed',
            'delivery_resources',
            'delivery_resources_other_label',
            'staffing',
            'customer_acquisition_roles',
            'customer_acquisition_roles_other',
            'customer_order_methods',
            'customer_order_methods_other',
            'capacity_for_50_percent_growth',
            'growth_operational_constraints',
            'growth_operational_constraints_other',
            'operational_growth_confidence',
        ],
        '4' => [
            'consumer_choice_reasons',
            'consumer_choice_reasons_other',
            'people_rating',
            'product_rating',
            'promises_rating',
            'support_effectiveness',
            'support_priorities_2027',
            'support_priorities_2027_other',
            'common_consumer_complaint',
        ],
        '5' => [
            'unreached_households_exist',
            'direct_household_growth_potential',
            'household_opportunity_areas',
            'household_opportunity_areas_other',
            'household_growth_barriers',
            'household_growth_barriers_other',
            'household_selling_interest',
            'adjacent_areas_available',
            'adjacent_area_names',
            'territory_expansion_interest',
            'territory_expansion_constraint',
            'territory_expansion_constraint_other',
            'territory_expansion_timing',
            'motivating_incentives',
            'motivating_incentives_other',
            'reward_achievements',
            'reward_achievements_other',
        ],
        '6' => [
            'thanksgiving_motivation',
            'thanksgiving_values',
            'thanksgiving_values_other',
            'thanksgiving_recognition',
            'thanksgiving_recognition_other',
            'thanksgiving_program_change',
            'thanksgiving_event_value',
            'thanksgiving_event_activities',
            'thanksgiving_event_activities_other',
            'thanksgiving_future',
            'thanksgiving_future_other',
            'future_growth_likelihood',
            'partner_confidence_change',
        ],
        '7' => [
            'prei_growth_actions',
            'leader_change_first',
            'management_additional_comments',
        ],
    ];

    private const QUESTION_LABELS = [
        'distributor_tenure' => 'How long have you been a PREI Area Distributor?',
        'performance_change_reasons' => 'what are the main reasons your sales increased, decreased, or remained stable during the last six months?',
        'business_performance_6m' => 'How would you describe the performance of your Gaz Lite business over the past 6 months?',
        'growth_limiting_factors' => 'What are the THREE factors currently limiting your Gaz Lite growth?',
        'growth_limiting_factors_other' => 'Other limiting factor',
        'territories_can_reach_200k' => 'Do you believe each of your current territory/ies individually can sustainably generate at least PHP 200,000 in Gaz Lite sales per month?',
        'territory_200k_no_reason' => 'If no, why not?',
        'supports_to_reach_200k' => 'What would help you reach or consistently exceed PHP 200,000 per month? Select up to THREE.',
        'supports_to_reach_200k_other' => 'Others',
        'growth_confidence_if_constraint_removed' => 'If PREI could help remove your biggest constraint, how confident are you that the business could grow significantly?',
        'delivers_directly' => 'Do you currently deliver Gaz Lite directly to customers?',
        'delivery_methods' => 'How are Gaz Lite orders currently delivered to your customers? Select all that apply.',
        'delivery_methods_other' => 'Other delivery method',
        'prei_delivery_reliance' => 'How often do you rely on PREI personnel to help with deliveries?',
        'current_delivery_distance_km' => 'What is the farthest distance from your business location that you currently deliver Gaz Lite?',
        'maximum_service_distance_km' => 'If there is sufficient customer demand, what is the farthest distance you could realistically serve using your current delivery resources?',
        'weekly_delivery_frequency' => 'Approximately how many times do you deliver Gaz Lite in a typical week?',
        'order_fulfillment_speed' => 'How quickly can you normally fulfill an order within your regular service area?',
        'delivery_resources' => 'What delivery resources do you currently have available?',
        'delivery_resources_other_label' => 'Specify other resource',
        'staffing' => 'How many people currently work on your Gaz Lite business?',
        'customer_acquisition_roles' => 'Who actively looks for new Gaz Lite customers, retailers, or accounts? Select all that apply.',
        'customer_acquisition_roles_other' => 'Other',
        'customer_order_methods' => 'How do customers normally place orders with you? Select all that apply.',
        'customer_order_methods_other' => 'Other',
        'capacity_for_50_percent_growth' => 'If your Gaz Lite sales increased by approximately 50%, could your current operation handle the additional business?',
        'growth_operational_constraints' => 'What would be your three biggest constraints if sales increased significantly?',
        'growth_operational_constraints_other' => 'Other constraint',
        'operational_growth_confidence' => 'Overall, how confident are you that your current business operation can support significantly higher Gaz Lite sales?',
        'consumer_choice_reasons' => 'What are the THREE reasons consumers choose or stay with Gaz Lite?',
        'consumer_choice_reasons_other' => 'Other reason',
        'people_rating' => 'People',
        'product_rating' => 'Products',
        'promises_rating' => 'Promises',
        'support_effectiveness' => 'How effective are the following PREI activities in helping you grow your business?',
        'support_priorities_2027' => 'Which THREE types of support should PREI focus more on during 2027? Rank them.',
        'support_priorities_2027_other' => 'Specify other support',
        'common_consumer_complaint' => 'What is the most common consumer complaint about Gaz Lite?',
        'unreached_households_exist' => 'Do you believe there are households or communities in your territory that are not currently being effectively reached by Gaz Lite?',
        'direct_household_growth_potential' => 'How significant is the opportunity to grow through direct household or community selling?',
        'household_opportunity_areas' => 'Which areas offer the greatest opportunity to gain additional household customers? Select all that apply.',
        'household_opportunity_areas_other' => 'Other area',
        'household_growth_barriers' => 'What are the THREE barriers to reaching more households directly?',
        'household_growth_barriers_other' => 'Other barrier',
        'household_selling_interest' => 'Would you be interested in more direct household or community-based selling if PREI provided appropriate support?',
        'adjacent_areas_available' => 'Are there areas adjacent to your existing territory that you believe you could serve profitably?',
        'adjacent_area_names' => 'If yes, which areas?',
        'territory_expansion_interest' => 'Would you be interested in expanding your PREI business into additional territories if performance and capability requirements were met?',
        'territory_expansion_constraint' => 'What is the biggest constraint preventing you from expanding?',
        'territory_expansion_constraint_other' => 'Other constraint',
        'territory_expansion_timing' => 'If you were to expand, when?',
        'motivating_incentives' => 'Which THREE incentives would motivate you most to grow your Gaz Lite business?',
        'motivating_incentives_other' => 'Other',
        'reward_achievements' => 'Which THREE achievements should PREI reward most?',
        'reward_achievements_other' => 'Other',
        'thanksgiving_motivation' => "How motivating is PREI's annual Thanksgiving Program in encouraging you to grow your Gaz Lite business?",
        'thanksgiving_values' => 'Which parts of the Thanksgiving Program are most valuable to you? Select up to THREE.',
        'thanksgiving_values_other' => 'Other',
        'thanksgiving_recognition' => 'What achievements should PREI recognize during Thanksgiving? Select up to THREE.',
        'thanksgiving_recognition_other' => 'Other',
        'thanksgiving_program_change' => 'What ONE change would make the Thanksgiving Program more motivating for you?',
        'thanksgiving_event_value' => 'How valuable is the annual PREI Thanksgiving / Partner Event itself?',
        'thanksgiving_event_activities' => 'Which THREE activities would make the Thanksgiving / Partner Event more valuable?',
        'thanksgiving_event_activities_other' => 'Other',
        'thanksgiving_future' => 'Overall, what should the PREI Thanksgiving Program become in the future?',
        'thanksgiving_future_other' => 'Other',
        'future_growth_likelihood' => 'How likely are you to continue growing your business with PREI over the next 2-3 years?',
        'partner_confidence_change' => 'Compared with one year ago, your confidence in PREI as a long-term business partner is:',
        'prei_growth_actions' => 'What are the THREE things PREI could do that would help you grow your business over the next 12 months?',
        'leader_change_first' => 'If you were the leader of PREI for one day, what is the ONE thing you would change first?',
        'management_additional_comments' => 'Is there anything else PREI management needs to know about your market, competitors, customers, or your experience as a PREI partner?',
    ];

    public function index(Request $request)
    {
        $this->authorizeReportAccess($request);

        $filters = [
            'period_start' => trim((string) $request->input('period_start')),
            'period_end' => trim((string) $request->input('period_end')),
            'status' => trim((string) $request->input('status')),
            'distributor' => trim((string) $request->input('distributor')),
        ];

        if (!in_array($filters['status'], ['', 'draft', 'in_progress', 'submitted'], true)) {
            $filters['status'] = '';
        }

        $filteredQuery = $this->applyFilters(PartnerSurvey::query(), $filters);
        $surveyIds = (clone $filteredQuery)->pluck('id');

        $summary = [
            'total' => (clone $filteredQuery)->count(),
            'submitted' => (clone $filteredQuery)->where('status', PartnerSurvey::STATUS_SUBMITTED)->count(),
            'in_progress' => (clone $filteredQuery)->where('status', PartnerSurvey::STATUS_IN_PROGRESS)->count(),
            'average_operational_confidence' => $this->averageAnswer($surveyIds, 'operational_growth_confidence'),
            'average_household_opportunity' => $this->averageAnswer($surveyIds, 'direct_household_growth_potential'),
        ];

        $topConstraints = PartnerSurveyAnswer::query()
            ->whereIn('partner_survey_id', $surveyIds)
            ->where('question_code', 'growth_limiting_factors')
            ->whereNotNull('option_code')
            ->selectRaw('option_code, COUNT(*) as response_count')
            ->groupBy('option_code')
            ->orderByDesc('response_count')
            ->limit(5)
            ->get();

        $surveys = (clone $filteredQuery)
            ->withCount('answers')
            ->with(['monthlyPerformances'])
            ->orderByDesc('period_end')
            ->orderBy('distributor_name')
            ->paginate(25)
            ->appends($request->except('page'));

        $selectedSurvey = null;
        $answerGroups = collect();
        $performanceSummary = null;
        if ($request->filled('survey_id')) {
            $selectedSurvey = PartnerSurvey::with([
                'answers',
                'channelMixes',
                'monthlyPerformances',
            ])->findOrFail((int) $request->input('survey_id'));

            $answerGroups = $this->answersInFormOrder($selectedSurvey->answers);
            $performanceSummary = $this->performanceSummary($selectedSurvey->monthlyPerformances);
        }

        return view('surveys.report', [
            'answerGroups' => $answerGroups,
            'filters' => $filters,
            'performanceSummary' => $performanceSummary,
            'questionLabels' => self::QUESTION_LABELS,
            'sectionLabels' => self::SECTION_LABELS,
            'selectedSurvey' => $selectedSurvey,
            'summary' => $summary,
            'surveys' => $surveys,
            'topConstraints' => $topConstraints,
        ]);
    }

    private function performanceSummary($performances)
    {
        $performances = $performances->sortBy('period_month')->values();

        if ($performances->isEmpty()) {
            return null;
        }

        $totalSales = $performances->sum(function ($performance) {
            return (float) $performance->sales_php;
        });
        $firstSales = (float) $performances->first()->sales_php;
        $lastSales = (float) $performances->last()->sales_php;
        $bestPerformance = $performances->sortByDesc(function ($performance) {
            return (float) $performance->sales_php;
        })->first();
        $lowestPerformance = $performances->sortBy(function ($performance) {
            return (float) $performance->sales_php;
        })->first();

        return [
            'total_sales' => $totalSales,
            'average_sales' => $totalSales / $performances->count(),
            'average_kg' => $performances->avg(function ($performance) {
                return (float) $performance->volume_kg;
            }),
            'months_at_target' => $performances->filter(function ($performance) {
                return (float) $performance->sales_php >= (float) $performance->target_php;
            })->count(),
            'month_count' => $performances->count(),
            'best_month' => optional($bestPerformance->period_month)->format('M Y'),
            'best_month_sales' => (float) $bestPerformance->sales_php,
            'lowest_month' => optional($lowestPerformance->period_month)->format('M Y'),
            'lowest_month_sales' => (float) $lowestPerformance->sales_php,
            'trend' => $lastSales > ($firstSales * 1.05)
                ? 'Growing'
                : ($lastSales < ($firstSales * .95) ? 'Declining' : 'Stable'),
        ];
    }

    private function answersInFormOrder($answers)
    {
        $answersByQuestion = $answers->groupBy('question_code');
        $knownQuestionCodes = collect(self::FORM_SECTION_QUESTIONS)->flatten();
        $orderedSections = collect();

        foreach (self::FORM_SECTION_QUESTIONS as $section => $questionCodes) {
            $orderedQuestions = collect();

            foreach ($questionCodes as $questionCode) {
                if (!$answersByQuestion->has($questionCode)) {
                    continue;
                }

                $orderedQuestions->put(
                    $questionCode,
                    $this->sortQuestionAnswers($answersByQuestion->get($questionCode))
                );
            }

            if ($orderedQuestions->isNotEmpty()) {
                $orderedSections->put($section, $orderedQuestions);
            }
        }

        $unmappedAnswers = $answers
            ->reject(function ($answer) use ($knownQuestionCodes) {
                return $knownQuestionCodes->contains($answer->question_code);
            })
            ->sortBy('id')
            ->groupBy('question_code')
            ->map(function ($questionAnswers) {
                return $this->sortQuestionAnswers($questionAnswers);
            });

        if ($unmappedAnswers->isNotEmpty()) {
            $orderedSections->put('other', $unmappedAnswers);
        }

        return $orderedSections;
    }

    private function sortQuestionAnswers($answers)
    {
        return $answers
            ->sort(function ($left, $right) {
                $rank = ($left->rank ?: 999) <=> ($right->rank ?: 999);

                return $rank !== 0
                    ? $rank
                    : ($left->id <=> $right->id);
            })
            ->values();
    }

    private function applyFilters($query, array $filters)
    {
        return $query
            ->when($filters['period_start'] !== '', function ($query) use ($filters) {
                $query->whereDate('period_start', $filters['period_start']);
            })
            ->when($filters['period_end'] !== '', function ($query) use ($filters) {
                $query->whereDate('period_end', $filters['period_end']);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['distributor'] !== '', function ($query) use ($filters) {
                $search = '%' . $filters['distributor'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('distributor_name', 'like', $search)
                        ->orWhere('business_name', 'like', $search)
                        ->orWhere('province', 'like', $search);
                });
            });
    }

    private function averageAnswer($surveyIds, $questionCode)
    {
        if ($surveyIds->isEmpty()) {
            return null;
        }

        $average = PartnerSurveyAnswer::query()
            ->whereIn('partner_survey_id', $surveyIds)
            ->where('question_code', $questionCode)
            ->avg('value_number');

        return $average === null ? null : round((float) $average, 2);
    }

    private function authorizeReportAccess(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->role === 'Admin') {
            return;
        }

        if ($user->role !== 'MFI') {
            abort(403);
        }

        $permissions = json_decode($user->access_permissions ?: '{}', true);
        $permissions = is_array($permissions) ? $permissions : [];
        $actions = $permissions['reports']['operations'] ?? [];
        $hasDetailedAccess = is_array($actions) && in_array('view', $actions, true);
        $hasLegacyAccess = empty($permissions) && $user->can_access_reports === 'on';

        abort_unless($hasDetailedAccess || $hasLegacyAccess, 403);
    }
}
