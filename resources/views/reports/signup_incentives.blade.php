@extends('layouts.header')

@section('css')
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css"
>

<style>
    .sir-page {
        --primary: #2f6fa4;
        --primary-dark: #284f99;
        --text: #17324d;
        --muted: #64748b;
        --border: #d1d5db;
        --background: #f6f8fb;
        --total-background: #e2f0d9;

        min-height: 100vh;
        padding: 18px 12px 32px;
        margin-top: 5.5em;
        background: var(--background);
    }

    .sir-card,
    .sir-hero,
    .sir-kpi {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    /* Header */
    .sir-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        margin-bottom: 14px;
        border-left: 5px solid var(--primary);
    }

    .sir-eyebrow {
        margin-bottom: 3px;
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .sir-hero h3 {
        margin: 0 0 4px;
        color: var(--text);
        font-size: 23px;
        font-weight: 800;
    }

    .sir-hero p {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
    }

    .sir-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .sir-actions .btn,
    .sir-filter-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        border-radius: 7px;
        font-weight: 700;
    }

    /* KPI cards */
    .sir-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .sir-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
    }

    .sir-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #eef5ff;
        color: var(--primary);
        font-size: 21px;
    }

    .sir-kpi small,
    .sir-kpi strong {
        display: block;
    }

    .sir-kpi small {
        margin-bottom: 2px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sir-kpi strong {
        color: var(--text);
        font-size: 21px;
        font-weight: 800;
    }

    /* Filters */
    .sir-filter-card {
        padding: 14px;
        margin-bottom: 14px;
    }

    .sir-filter-card label {
        color: #334e68;
        font-size: 12px;
        font-weight: 800;
    }

    .sir-filter-card .form-control {
        min-height: 38px;
        border-color: #cbd5e1;
        border-radius: 7px;
        font-size: 13px;
    }

    .sir-filter-card .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.18rem rgba(47, 111, 164, 0.14);
    }

    .sir-filter-actions {
        display: flex;
        gap: 8px;
    }

    /* Report table */
    .sir-report-card {
        padding: 0;
        overflow: hidden;
    }

    .sir-table-wrap {
        width: 100%;
        max-height: 72vh;
        overflow: auto;
        background: #ffffff;
    }

    .sir-table {
        width: 100%;
        min-width: 1400px;
        border-collapse: collapse;
        table-layout: fixed;
        color: #111827;
        font-size: 13px;
    }

    .sir-table th,
    .sir-table td {
        height: 31px;
        padding: 5px 8px;
        border: 1px solid var(--border);
        vertical-align: middle;
        white-space: nowrap;
    }

    .sir-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #ffffff;
        color: #111827;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
        text-transform: uppercase;
    }

    .sir-designation-column {
        width: 130px;
    }

    .sir-center-column {
        width: 170px;
    }

    .sir-month-column {
        width: 82px;
    }

    .sir-total-column {
        width: 105px;
    }

    .sir-designation-cell {
        background: #f8fafc;
        font-weight: 800;
        text-align: center;
    }

    .sir-center-cell {
        font-weight: 600;
        text-align: left;
    }

    .sir-number {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .sir-row-total {
        background: #f8fafc;
        font-weight: 800;
    }
    .sir-designation-total-row td {
        background: #ddebf7;
        border-top: 2px solid #5b9bd5;
        font-weight: 800;
    }

    .sir-designation-total-label {
        color: #17324d;
        text-align: left;
    }
    /* .sir-grand-total-row td {
        background: var(--total-background);
        border-top: 2px solid #548235;
        font-weight: 800;
    }

    .sir-empty-state {
        padding: 32px !important;
        color: var(--muted);
        text-align: center;
    } */

    @media (max-width: 767.98px) {
        .sir-hero {
            align-items: stretch;
            flex-direction: column;
        }

        .sir-actions {
            width: 100%;
        }

        .sir-actions .btn {
            flex: 1;
        }

        .sir-kpis {
            grid-template-columns: 1fr;
        }

        .sir-filter-actions {
            margin-top: 10px;
        }
    }

    @media print {
        .sidebar,
        .topbar,
        .sir-actions,
        .sir-filter-card {
            display: none !important;
        }

        .sir-page {
            padding: 0;
            margin: 0;
            background: #ffffff;
        }

        .sir-hero,
        .sir-card,
        .sir-kpi {
            border: 0;
            box-shadow: none;
        }

        .sir-table-wrap {
            max-height: none;
            overflow: visible;
        }

        .sir-table {
            min-width: 100%;
            font-size: 9px;
        }

        .sir-table th,
        .sir-table td {
            height: 22px;
            padding: 2px 3px;
        }

        .sir-table thead th {
            position: static;
        }
    }
</style>
@endsection

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Prepare report records
    |--------------------------------------------------------------------------
    |
    | Ideally, move this processing to the controller.
    | This remains here so the template works with your existing $rows data.
    |
    */

    $monthKeys = array_keys($months);
    $reportRecords = collect();

    foreach ($rows as $row) {
        $center = isset($row['center']) ? $row['center'] : 'Unassigned Center';
        $people = isset($row['people']) ? $row['people'] : [];

        foreach ($people as $person) {
            $designation = !empty($person['designation'])
                ? trim($person['designation'])
                : 'Unassigned';

            $recordKey = $designation . '|' . $center;

            if (!$reportRecords->has($recordKey)) {
                $amounts = array_fill_keys($monthKeys, 0);

                $reportRecords->put($recordKey, [
                    'designation' => $designation,
                    'center'      => $center,
                    'amounts'     => $amounts,
                    'total'       => 0,
                ]);
            }

            $record = $reportRecords->get($recordKey);

            foreach ($monthKeys as $monthNumber) {
                $amount = isset($person['amounts'][$monthNumber])
                    ? (float) $person['amounts'][$monthNumber]
                    : 0;

                $record['amounts'][$monthNumber] += $amount;
            }

            $record['total'] = array_sum($record['amounts']);

            $reportRecords->put($recordKey, $record);
        }
    }

    $reportRecords = $reportRecords
        ->values()
        ->sortBy(function ($record) {
            return strtolower($record['designation'] . '|' . $record['center']);
        });

    $designationGroups = $reportRecords->groupBy('designation');

    $monthlyTotals = array_fill_keys($monthKeys, 0);

    foreach ($reportRecords as $record) {
        foreach ($monthKeys as $monthNumber) {
            $monthlyTotals[$monthNumber] += $record['amounts'][$monthNumber];
        }
    }

    $reportGrandTotal = array_sum($monthlyTotals);
@endphp

<div class="container-fluid sir-page">
    <section class="sir-hero">
        <div>
            <div class="sir-eyebrow">Reports</div>

            <h3>Sign Up Incentives Report</h3>

            <p>
                Monthly incentive totals grouped by designation and center.
            </p>
        </div>

        <div class="sir-actions">
            <a
                href="{{ route('signup-incentives.export', request()->query()) }}"
                class="btn btn-outline-primary"
            >
                <i class="ti ti-download"></i>
                Export CSV
            </a>

            <button
                type="button"
                class="btn btn-primary"
                onclick="window.print()"
            >
                <i class="ti ti-printer"></i>
                Print
            </button>
        </div>
    </section>

    <section class="sir-kpis">
        <article class="sir-kpi">
            <span class="sir-kpi-icon">
                <i class="ti ti-building-store"></i>
            </span>

            <div>
                <small>Centers</small>
                <strong>{{ number_format(count($rows)) }}</strong>
            </div>
        </article>

        <article class="sir-kpi">
            <span class="sir-kpi-icon">
                <i class="ti ti-user-plus"></i>
            </span>

            <div>
                <small>Total Sign Ups</small>
                <strong>{{ number_format($grandTotalSignups) }}</strong>
            </div>
        </article>

        <article class="sir-kpi">
            <span class="sir-kpi-icon">
                <i class="ti ti-cash"></i>
            </span>

            <div>
                <small>Total Incentives</small>
                <strong>₱{{ number_format($reportGrandTotal, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="sir-card sir-filter-card">
        <form
            method="GET"
            action="{{ route('signup-incentives') }}"
        >
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="year">Year</label>

                    <input
                        type="number"
                        id="year"
                        name="year"
                        class="form-control"
                        value="{{ $year }}"
                        min="2000"
                        max="2100"
                        required
                    >
                </div>

                <div class="col-lg-3 col-md-5 mb-2">
                    <label for="center">
                        {{ $isSedpTerritoryView ? 'My Territory Centers' : 'Center' }}
                    </label>

                    <select
                        id="center"
                        name="center"
                        class="form-control"
                    >
                        <option value="">
                            {{ $isSedpTerritoryView ? 'All My Centers' : 'All Centers' }}
                        </option>

                        @foreach($centers as $center)
                            <option
                                value="{{ $center }}"
                                {{ $selectedCenter === $center ? 'selected' : '' }}
                            >
                                {{ $center }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 mb-2">
                    <div class="sir-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter"></i>
                            Apply
                        </button>

                        <a
                            href="{{ route('signup-incentives') }}"
                            class="btn btn-outline-secondary"
                            title="Reset filters"
                        >
                            <i class="ti ti-refresh"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <section class="sir-card sir-report-card">
        <div class="sir-table-wrap">
            <table class="sir-table">
                <colgroup>
                    <col class="sir-designation-column">
                    <col class="sir-center-column">

                    @foreach($months as $month)
                        <col class="sir-month-column">
                    @endforeach

                    <col class="sir-total-column">
                </colgroup>

                <thead>
                    <tr>
                        <th>Designation</th>
                        <th>Center</th>

                        @foreach($months as $month)
                            <th>{{ $month }}</th>
                        @endforeach

                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($designationGroups as $designation => $records)
                        @php
                            $designationMonthlyTotals = array_fill_keys($monthKeys, 0);
                            $designationGrandTotal = 0;

                            foreach ($records as $designationRecord) {
                                foreach ($monthKeys as $monthNumber) {
                                    $designationMonthlyTotals[$monthNumber] +=
                                        isset($designationRecord['amounts'][$monthNumber])
                                            ? (float) $designationRecord['amounts'][$monthNumber]
                                            : 0;
                                }

                                $designationGrandTotal += isset($designationRecord['total'])
                                    ? (float) $designationRecord['total']
                                    : 0;
                            }
                        @endphp

                        @foreach($records->values() as $recordIndex => $record)
                            <tr>
                                @if($recordIndex === 0)
                                    <td
                                        class="sir-designation-cell"
                                        rowspan="{{ $records->count() }}"
                                    >
                                        {{ $designation }}
                                    </td>
                                @endif

                                <td class="sir-center-cell">
                                    {{ $record['center'] }}
                                </td>

                                @foreach($monthKeys as $monthNumber)
                                    <td class="sir-number">
                                        {{ number_format(
                                            isset($record['amounts'][$monthNumber])
                                                ? $record['amounts'][$monthNumber]
                                                : 0,
                                            2
                                        ) }}
                                    </td>
                                @endforeach

                                <td class="sir-number sir-row-total">
                                    {{ number_format($record['total'], 2) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="sir-designation-total-row">
                            <td colspan="2" class="sir-designation-total-label">
                                Total Incentive — {{ $designation }}
                            </td>

                            @foreach($monthKeys as $monthNumber)
                                <td class="sir-number">
                                    {{ number_format($designationMonthlyTotals[$monthNumber], 2) }}
                                </td>
                            @endforeach

                            <td class="sir-number">
                                {{ number_format($designationGrandTotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="{{ count($months) + 3 }}"
                                class="sir-empty-state"
                            >
                                @if($isSedpTerritoryView)
                                    No incentive records were found for your assigned territory.
                                @else
                                    No incentive records were found for the selected filters.
                                @endif
                            </td>
                        </tr>
                    @endforelse

                    @if($reportRecords->isNotEmpty())
                        <tr class="sir-grand-total-row">
                            <td colspan="2">
                                Grand Total Incentive Earned
                            </td>

                            @foreach($monthKeys as $monthNumber)
                                <td class="sir-number">
                                    {{ number_format($monthlyTotals[$monthNumber], 2) }}
                                </td>
                            @endforeach

                            <td class="sir-number">
                                {{ number_format($reportGrandTotal, 2) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection