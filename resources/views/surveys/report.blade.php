@extends('layouts.header')

@section('css')
<style>
    .survey-report {
        display: grid;
        width: 100%;
        min-width: 0;
        gap: 18px;
    }

    .survey-report-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
    }

    .survey-report-title {
        margin: 0;
        color: #101828;
        font-size: 26px;
        font-weight: 900;
    }

    .survey-report-copy {
        max-width: 720px;
        margin: 6px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.55;
    }

    .report-panel {
        min-width: 0;
        overflow: hidden;
        border: 1px solid #e4e7ec;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .04);
    }

    .report-panel-body {
        padding: 18px;
    }

    .report-filters {
        display: grid;
        grid-template-columns: minmax(280px, 1.6fr) repeat(2, minmax(150px, 1fr)) auto;
        align-items: end;
        gap: 12px;
    }

    .report-field label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 11px;
        font-weight: 800;
    }

    .report-field .form-control,
    .report-field .form-select {
        width: 100%;
        min-height: 42px;
        border-color: #d0d5dd;
        border-radius: 9px;
        font-size: 12px;
    }

    .report-actions {
        display: flex;
        gap: 8px;
    }

    .report-actions .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid #eaecf0;
    }

    .panel-title {
        margin: 0;
        color: #344054;
        font-size: 14px;
        font-weight: 900;
    }

    .panel-subtitle {
        margin: 3px 0 0;
        color: #98a2b3;
        font-size: 10px;
    }

    .report-table-wrap {
        max-width: 100%;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        -webkit-overflow-scrolling: touch;
    }

    .report-table {
        min-width: 900px;
        margin: 0;
    }

    .report-table th {
        padding: 11px 13px;
        color: #667085;
        border-color: #eaecf0;
        background: #f8fafc;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .report-table td {
        padding: 12px 13px;
        color: #475467;
        border-color: #eaecf0;
        font-size: 12px;
        vertical-align: middle;
    }

    .report-table strong {
        color: #25364a;
    }

    .performance-sales.is-target-met {
        color: #15803d;
        font-weight: 800;
    }

    .performance-sales.is-target-not-met {
        color: #c94b4b;
        font-weight: 800;
    }

    .performance-growth {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 800;
        white-space: nowrap;
    }

    .performance-growth.is-positive {
        color: #15803d;
    }

    .performance-growth.is-negative {
        color: #c94b4b;
    }

    .performance-summary {
        margin: 14px;
        overflow: hidden;
        border: 1px solid #e4e7ec;
        border-radius: 10px;
        background: #fff;
    }

    .performance-summary table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .performance-summary th,
    .performance-summary td {
        padding: 10px 13px;
        border-bottom: 1px solid #edf0f5;
        vertical-align: middle;
    }

    .performance-summary tr:last-child th,
    .performance-summary tr:last-child td {
        border-bottom: 0;
    }

    .performance-summary th {
        width: 50%;
        color: #667085;
        background: #f8fafc;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .04em;
        text-align: left;
        text-transform: uppercase;
    }

    .performance-summary td {
        color: #25364a;
        font-size: 11px;
        font-weight: 900;
        text-align: right;
    }

    .performance-summary-sales.is-best {
        color: #15803d;
    }

    .performance-summary-sales.is-lowest {
        color: #c94b4b;
    }

    .report-view-button {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 16px;
    }

    .status-pill {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #475467;
        font-size: 9px;
        font-weight: 850;
        text-transform: capitalize;
    }

    .status-pill.is-submitted {
        color: #176b47;
        background: #eafaf2;
    }

    .status-pill.is-in-progress {
        color: #9a6700;
        background: #fff7df;
    }

    .empty-state {
        padding: 34px 18px;
        color: #98a2b3;
        text-align: center;
        font-size: 12px;
    }

    .pagination-wrap {
        max-width: 100%;
        padding: 14px 18px 5px;
        overflow-x: auto;
    }

    .detail-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .detail-title {
        margin: 0;
        color: #101828;
        font-size: 20px;
        font-weight: 900;
    }

    .detail-meta {
        margin-top: 5px;
        color: #667085;
        font-size: 11px;
    }

    .profile-grid {
        display: grid;
        gap: 9px;
        padding: 14px 16px;
        border: 1px solid #dbe7f1;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
    }

    .profile-item {
        display: grid;
        grid-template-columns: minmax(120px, 18%) minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        min-width: 0;
        padding: 3px 0;
    }

    .profile-item span {
        display: block;
        color: #0877ca;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .profile-item strong {
        display: block;
        margin: 0;
        color: #1f2937;
        font-size: 12px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .section-block {
        overflow: hidden;
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        background: #fff;
    }

    .section-heading {
        padding: 13px 15px;
        color: #075985;
        border-bottom: 1px solid #e4e7ec;
        background: #f0f9ff;
        font-size: 13px;
        font-weight: 900;
    }

    .answer-block {
        padding: 14px 15px;
        border-bottom: 1px solid #f0f2f5;
    }

    .answer-block:last-child {
        border-bottom: 0;
    }

    .answer-question {
        margin: 0 0 8px;
        color: #344054;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.45;
    }

    .answer-list {
        display: grid;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .answer-list li {
        padding: 8px 10px;
        color: #475467;
        border-radius: 8px;
        background: #f8fafc;
        font-size: 10px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .answer-label {
        color: #0877ca;
        font-weight: 850;
    }

    .survey-detail-modal .modal-dialog {
        max-width: min(1400px, calc(100vw - 32px));
    }

    .survey-detail-modal .modal-header {
        border-bottom: 0;
        background: linear-gradient(135deg, #075985, #0877ca);
    }

    .survey-detail-modal .detail-title {
        color: #fff;
    }

    .survey-detail-modal .detail-meta {
        color: #dbeafe;
    }

    .survey-detail-modal .btn-close {
        filter: brightness(0) invert(1);
        opacity: .9;
    }

    .survey-detail-modal .modal-body {
        background: #f8fafc;
    }

    .survey-detail-modal .report-table {
        min-width: 680px;
    }

    .survey-detail-content {
        display: grid;
        min-width: 0;
        gap: 14px;
    }

    @media (max-width: 1100px) {
        .report-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-search-field,
        .report-actions {
            grid-column: 1 / -1;
        }

        .report-actions {
            justify-content: flex-end;
        }

        .detail-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 720px) {
        .survey-report {
            gap: 14px;
        }

        .survey-report-header,
        .detail-head {
            justify-content: space-between;
            align-items: center;
        }

        .survey-report-title {
            font-size: 22px;
        }

        .report-panel {
            border-radius: 12px;
        }

        .report-panel-body,
        .panel-head {
            padding: 14px;
        }

        .report-filters,
        .detail-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .report-search-field,
        .report-actions {
            grid-column: auto;
        }

        .report-actions {
            width: 100%;
        }

        .report-actions .btn {
            flex: 1;
        }

        .submission-table {
            width: 100%;
            min-width: 0;
            margin: 0;
            padding: 2px 12px 12px;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .submission-table thead {
            display: none;
        }

        .submission-table tbody,
        .submission-table tr,
        .submission-table td {
            display: block;
            width: 100%;
        }

        .submission-table tr {
            padding: 10px 14px;
            border: 1px solid #e4e7ec;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
        }

        .submission-table td {
            display: grid;
            grid-template-columns: minmax(112px, 38%) minmax(0, 1fr);
            gap: 10px;
            padding: 8px 0;
            border: 0;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .submission-table td + td {
            border-top: 1px solid #f0f2f5;
        }

        .submission-table td::before {
            content: attr(data-label);
            color: #667085;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .04em;
            text-align: left;
            text-transform: uppercase;
        }

        .submission-table td:first-child {
            text-align: right;
        }

        .submission-table td:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .submission-table td:last-child::before {
            content: 'Details';
        }

        .survey-detail-modal .modal-body {
            padding: 14px;
        }

        .survey-detail-content {
            gap: 14px;
        }

        .section-heading,
        .answer-block {
            padding-right: 12px;
            padding-left: 12px;
        }

        .survey-detail-modal .report-table {
            min-width: 620px;
        }

        .performance-summary {
            margin: 12px;
        }
    }

    @media (max-width: 420px) {
        .report-panel-body,
        .panel-head {
            padding: 12px;
        }

        .report-actions {
            flex-direction: column;
        }

        .submission-table {
            padding-right: 8px;
            padding-left: 8px;
        }

        .submission-table td {
            grid-template-columns: minmax(0, 1fr);
            gap: 4px;
            text-align: left;
        }

        .submission-table td:first-child {
            text-align: left;
        }

        .profile-item {
            grid-template-columns: minmax(0, 1fr);
            gap: 3px;
            padding: 4px 0;
        }

        .profile-grid {
            padding: 12px 14px;
        }

    }
</style>
@endsection

@section('content')
@php
    $humanize = function ($value) {
        return ucwords(str_replace(['_', '-'], ' ', (string) $value));
    };
@endphp

<div class="survey-report">
    <header class="survey-report-header">
        <div>
            <h1 class="survey-report-title">Survey Report</h1>
        </div>
    </header>

    <section class="report-panel">
        <div class="report-panel-body">
            <form method="GET" action="{{ route('surveys.reports') }}" class="report-filters">
                <div class="report-field report-search-field">
                    <label for="distributor">Distributor, business, or province</label>
                    <input
                        id="distributor"
                        name="distributor"
                        class="form-control"
                        value="{{ $filters['distributor'] }}"
                        placeholder="Search distributor, business, or province..."
                    >
                </div>

                <div class="report-field">
                    <label for="period_start">Period start</label>
                    <input
                        id="period_start"
                        name="period_start"
                        type="date"
                        class="form-control"
                        value="{{ $filters['period_start'] }}"
                    >
                </div>

                <div class="report-field">
                    <label for="period_end">Period end</label>
                    <input
                        id="period_end"
                        name="period_end"
                        type="date"
                        class="form-control"
                        value="{{ $filters['period_end'] }}"
                    >
                </div>

                <!-- <div class="report-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_progress" {{ $filters['status'] === 'in_progress' ? 'selected' : '' }}>In progress</option>
                        <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    </select>
                </div> -->

                <div class="report-actions">
                    <button class="btn btn-primary" type="submit">Apply filters</button>
                    <a class="btn btn-light" href="{{ route('surveys.reports') }}">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="report-panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Survey submissions</h2>
                <p class="panel-subtitle">Select the eye icon to inspect all stored answers and table rows.</p>
            </div>
        </div>

        @if($surveys->isEmpty())
            <div class="empty-state">No survey submissions match the current filters.</div>
        @else
            <div class="report-table-wrap">
                <table class="table report-table submission-table">
                    <thead>
                        <tr>
                            <th>Distributor</th>
                            <th>Province</th>
                            <th>Survey period</th>
                            <th>Status</th>
                            <th>Submitted at</th>
                            <!-- <th>Answers</th> -->
                            <th>Average monthly sales</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys as $survey)
                            @php
                                $averageMonthlySales = $survey->monthlyPerformances->avg('sales_php');
                            @endphp
                            <tr>
                                <td data-label="Distributor">
                                    <strong>{{ $survey->distributor_name ?: 'Not provided' }}</strong><br>
                                    <span>{{ $survey->business_name ?: '—' }}</span>
                                </td>
                                <td data-label="Province">{{ $survey->province ?: '—' }}</td>
                                <td data-label="Survey period">
                                    {{ optional($survey->period_start)->format('M d, Y') }}
                                    -
                                    {{ optional($survey->period_end)->format('M d, Y') }}
                                </td>
                                <td data-label="Status">
                                    <span class="status-pill is-{{ str_replace('_', '-', $survey->status) }}">
                                        {{ $humanize($survey->status) }}
                                    </span>
                                </td>
                                <td data-label="Submitted at">{{ $survey->submitted_at ? $survey->submitted_at->format('M d, Y') : '—' }}</td>
                                <!-- <td>{{ number_format($survey->answers_count) }}</td> -->
                                <td data-label="Average monthly sales">{{ $averageMonthlySales === null ? '—' : 'PHP ' . number_format($averageMonthlySales, 2) }}</td>
                                <td data-label="Details">
                                    <a
                                        class="btn btn-sm btn-outline-primary report-view-button"
                                        href="{{ route('surveys.reports', array_merge(request()->except(['page', 'survey_id']), ['survey_id' => $survey->id])) }}"
                                        aria-label="View details for {{ $survey->distributor_name ?: 'survey submission' }}"
                                        title="View details"
                                    >
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        <span class="visually-hidden">View details</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">{{ $surveys->links() }}</div>
        @endif
    </section>

    @if($selectedSurvey)
        <div
            id="surveyDetailModal"
            class="modal fade survey-detail-modal"
            tabindex="-1"
            aria-labelledby="surveyDetailModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header detail-head rounded-top">
                        <div>
                            <h2 class="detail-title" id="surveyDetailModalLabel">
                                Survey details
                            </h2>
                        </div>
                        <div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="survey-detail-content">
                            <div class="profile-grid">
                                <div class="profile-item is-wide">
                                    <span>Name</span>
                                    <strong>{{ $selectedSurvey->distributor_name ?: '—' }}</strong>
                                </div>
                                <div class="profile-item is-wide">
                                    <span>Business Name</span>
                                    <strong>{{ $selectedSurvey->business_name ?: '—' }}</strong>
                                </div>
                                <div class="profile-item is-wide">
                                    <span>Province</span>
                                    <strong>{{ $selectedSurvey->province ?: '—' }}</strong>
                                </div>
                                <div class="profile-item is-wide">
                                    <span>Awarded Area / Territory</span>
                                    <strong>{{ $selectedSurvey->awarded_territories ?: '—' }}</strong>
                                </div>
                                <div class="profile-item is-wide">
                                    <span>Survey Period</span>
                                    <strong>
                                        {{ optional($selectedSurvey->period_start)->format('M d, Y') }}
                                        –
                                        {{ optional($selectedSurvey->period_end)->format('M d, Y') }}
                                    </strong>
                                </div>
                            </div>

                        <div class="detail-grid">
                            <div class="section-block">
                                <div class="section-heading">Sales channel</div>

                                @if($selectedSurvey->channelMixes->isEmpty())
                                    <div class="empty-state">No sales-channel rows were stored.</div>
                                @else
                                    @php
                                        $totalChannelSales = (float) $selectedSurvey->channelMixes->sum('estimated_monthly_sales');
                                    @endphp
                                    <div class="report-table-wrap">
                                        <table class="table report-table">
                                            <thead>
                                                <tr>
                                                    <th>Channel</th>
                                                    <th>Monthly sales</th>
                                                    <th>%</th>
                                                    <th>Active customers</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedSurvey->channelMixes as $channel)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $channel->other_channel ?: $humanize($channel->channel_code) }}</strong>
                                                        </td>
                                                        <td>PHP {{ number_format((float) $channel->estimated_monthly_sales, 2) }}</td>
                                                        <td>
                                                            {{ $totalChannelSales > 0
                                                                ? number_format(((float) $channel->estimated_monthly_sales / $totalChannelSales) * 100, 1) . '%'
                                                                : '—' }}
                                                        </td>
                                                        <td>{{ $channel->active_customers === null ? '—' : number_format($channel->active_customers) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                @endif
                            </div>

                            <div class="section-block">
                                <div class="section-heading">Six-month actual performance table</div>

                                @if($selectedSurvey->monthlyPerformances->isEmpty())
                                    <div class="empty-state">No monthly performance rows were stored.</div>
                                @else
                                    <div class="report-table-wrap">
                                        <table class="table report-table">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Sales</th>
                                                    <th>Volume (kg)</th>
                                                    <th>Target</th>
                                                    <th>Month-on-month</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $previousPerformanceSales = null; @endphp
                                                @foreach($selectedSurvey->monthlyPerformances as $performance)
                                                    @php
                                                        $currentPerformanceSales = (float) $performance->sales_php;
                                                        $hasPreviousPerformanceMonth = $previousPerformanceSales !== null;
                                                        $previousPerformanceMonthWasZero = $hasPreviousPerformanceMonth
                                                            && (float) $previousPerformanceSales === 0.0;
                                                        $performanceMonthGrowth = $hasPreviousPerformanceMonth
                                                            && !$previousPerformanceMonthWasZero
                                                                ? (($currentPerformanceSales - $previousPerformanceSales) / $previousPerformanceSales) * 100
                                                                : null;
                                                        $metTarget = (float) $performance->sales_php >= (float) $performance->target_php;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <strong>{{ optional($performance->period_month)->format('M Y') }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="performance-sales {{ $metTarget ? 'is-target-met' : 'is-target-not-met' }}">
                                                                PHP {{ number_format((float) $performance->sales_php, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ number_format((float) $performance->volume_kg, 3) }}</td>
                                                        <td>PHP {{ number_format((float) $performance->target_php, 2) }}</td>
                                                        <td>
                                                            @if(!$hasPreviousPerformanceMonth)
                                                                <span class="text-muted">Starting month</span>
                                                            @elseif($previousPerformanceMonthWasZero && $currentPerformanceSales > 0)
                                                                <span class="performance-growth is-positive">
                                                                    <i class="bi bi-arrow-up-right"></i>
                                                                    Growth from ₱0
                                                                </span>
                                                            @elseif($previousPerformanceMonthWasZero || $performanceMonthGrowth == 0)
                                                                <span class="text-muted">-</span>
                                                            @else
                                                                <span class="performance-growth {{ $performanceMonthGrowth >= 0 ? 'is-positive' : 'is-negative' }}">
                                                                    <i class="bi {{ $performanceMonthGrowth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                                                    {{ $performanceMonthGrowth > 0 ? 'Growth' : 'Decrease' }}
                                                                    {{ number_format(abs($performanceMonthGrowth), 1) }}%
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $previousPerformanceSales = $currentPerformanceSales; @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="performance-summary">
                                        <table aria-label="Six-month performance summary">
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Total sales</th>
                                                    <td>₱{{ number_format($performanceSummary['total_sales'], 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Average monthly sales</th>
                                                    <td>₱{{ number_format($performanceSummary['average_sales'], 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Average monthly KG</th>
                                                    <td>{{ number_format($performanceSummary['average_kg'], 3) }} KG</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Months at PHP 200K+</th>
                                                    <td>{{ $performanceSummary['months_at_target'] }} / {{ $performanceSummary['month_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Best month</th>
                                                    <td>
                                                        {{ $performanceSummary['best_month'] ?: '—' }} -
                                                        <span class="performance-summary-sales is-best">₱{{ number_format($performanceSummary['best_month_sales'], 2) }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Lowest month</th>
                                                    <td>
                                                        {{ $performanceSummary['lowest_month'] ?: '—' }} -
                                                        <span class="performance-summary-sales is-lowest">₱{{ number_format($performanceSummary['lowest_month_sales'], 2) }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Six-month trend</th>
                                                    <td>{{ $performanceSummary['trend'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @forelse($answerGroups as $sectionCode => $questions)
                            <div class="section-block">
                                <div class="section-heading">
                                    Section {{ $sectionCode }} ·
                                    {{ $sectionLabels[$sectionCode] ?? 'Questionnaire responses' }}
                                </div>

                                @foreach($questions as $questionCode => $answers)
                                    <div class="answer-block">
                                        <h3 class="answer-question">
                                            {{ $answers->first()->question_number }}.
                                            {{ $questionLabels[$questionCode] ?? $humanize($questionCode) }}
                                        </h3>
                                        <ul class="answer-list">
                                            @foreach($answers as $answer)
                                                @php
                                                    $parts = [];

                                                    if ($answer->rank) {
                                                        $parts[] = 'Rank ' . $answer->rank;
                                                    }

                                                    if ($answer->item_code) {
                                                        $parts[] = $humanize($answer->item_code);
                                                    }

                                                    $prefix = empty($parts) ? '' : implode(' · ', $parts) . ': ';

                                                    if ($answer->is_not_applicable) {
                                                        $answerValue = 'Not applicable';
                                                    } elseif ($answer->value_text !== null && $answer->value_text !== '') {
                                                        $answerValue = $answer->value_text;
                                                    } elseif ($answer->option_code !== null && $answer->option_code !== '') {
                                                        $answerValue = $humanize($answer->option_code);
                                                    } elseif ($answer->value_number !== null) {
                                                        $answerValue = rtrim(rtrim(number_format((float) $answer->value_number, 4, '.', ''), '0'), '.');
                                                    } else {
                                                        $answerValue = 'No value stored';
                                                    }
                                                @endphp
                                                <li>
                                                    @if($prefix)
                                                        <span class="answer-label">{{ $prefix }}</span>
                                                    @endif
                                                    {{ $answerValue }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <div class="empty-state">
                                This submission does not contain questionnaire answer rows.
                            </div>
                        @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('js')
    @if($selectedSurvey)
        <script>
            (function () {
                function openSurveyDetailModal() {
                    const modalElement = document.getElementById('surveyDetailModal');

                    if (!modalElement || typeof bootstrap === 'undefined') {
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(modalElement).show();

                    modalElement.addEventListener('hidden.bs.modal', function () {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('survey_id');
                        window.history.replaceState({}, '', url.toString());
                    }, { once: true });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', openSurveyDetailModal, { once: true });
                } else {
                    openSurveyDetailModal();
                }
            })();
        </script>
    @endif
@endsection
