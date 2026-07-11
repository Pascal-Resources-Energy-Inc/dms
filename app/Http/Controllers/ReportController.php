<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\OrderDetail;
use App\OtherCharge;
use App\Product;
use App\InventoryTransfer;
use App\AdPurchaseOrder;
use App\AdPurchaseOrderItem;
use App\AdPurchaseOrderPartialReceipt;
use App\AreaDistributor;
use App\Center;
use App\User;
use App\Exports\DailySalesExport;
use App\Exports\InventoryStockLevelExport;
use App\Exports\MonthlySalesExport;
use App\Exports\VoucherHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    private $signupIncentiveMonths = [
        1 => 'JANUARY',
        2 => 'FEBRUARY',
        3 => 'MARCH',
        4 => 'APRIL',
        5 => 'MAY',
        6 => 'JUNE',
        7 => 'JULY',
        8 => 'AUGUST',
        9 => 'SEPTEMBER',
        10 => 'OCTOBER',
        11 => 'NOVEMBER',
        12 => 'DECEMBER',
    ];

    private $signupIncentiveRates = [
        'CDW' => 50,
        'CDW2' => 50,
        'SPOM' => 50,
    ];

    private $signupIncentiveCrmConnections = [
        'admin_crms',
    ];

    private function authorizeSedpReports()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        if ($role === 'admin') {
            return;
        }

        if ($role !== 'sedp') {
            abort(403);
        }

        $permissions = json_decode($user->access_permissions ?? '{}', true);
        $permissions = is_array($permissions) ? $permissions : [];
        $sedpReportActions = $permissions['reports']['sedp'] ?? [];
        $hasSedpReportAccess = is_array($sedpReportActions)
            && in_array('view', $sedpReportActions, true);
        $hasLegacyReportAccess = empty($permissions)
            && (($user->can_access_reports ?? null) === 'on');

        if (!$hasSedpReportAccess && !$hasLegacyReportAccess) {
            abort(403);
        }
    }

    public function signupIncentivesReport(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) ($request->year ?: Carbon::now()->year);
        $center = trim((string) $request->center);
        $rates = $this->signupIncentiveRatesFromRequest($request);
        $report = $this->buildSignupIncentivesReport($year, $center, $rates);

        return view('reports.signup_incentives', array_merge($report, [
            'year' => $year,
            'selectedCenter' => $center,
            'rates' => $rates,
        ]));
    }

    public function signupIncentiveClients(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $center = trim((string) $request->input('center'));

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12 || $center === '') {
            return response()->json(['message' => 'Invalid client list request.'], 422);
        }

        $centerKey = $this->normalizeSignupCenter($center);
        $authUser = auth()->user();

        if (strtolower(trim((string) ($authUser->role ?? ''))) === 'sedp') {
            $territoryKeys = $this->splitSignupTerritories($authUser->territory ?? '')
                ->map(function ($territoryCenter) {
                    return $this->normalizeSignupCenter($territoryCenter);
                })
                ->filter()
                ->flip();

            if (!$territoryKeys->has($centerKey)) {
                abort(403);
            }
        }

        try {
            $connection = 'admin_crms';
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('clients')
                || !$schema->hasColumn('clients', 'center')
                || !$schema->hasColumn('clients', 'created_at')) {
                return response()->json(['clients' => []]);
            }

            $columns = ['id', 'center', 'created_at'];

            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'mobile', 'contact_number', 'phone'] as $column) {
                if ($schema->hasColumn('clients', $column)) {
                    $columns[] = $column;
                }
            }

            $clients = DB::connection($connection)->table('clients')
                ->select(array_unique($columns))
                ->whereNotNull('created_at')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereRaw('UPPER(TRIM(center)) = ?', [$centerKey])
                ->when($schema->hasColumn('clients', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->orderBy('created_at')
                ->get()
                ->map(function ($client) {
                    $name = trim((string) ($client->name ?? ''));

                    if ($name === '') {
                        $name = trim(collect([
                            $client->first_name ?? '',
                            $client->middle_name ?? '',
                            $client->last_name ?? '',
                        ])->filter()->implode(' '));
                    }

                    $contact = collect([
                        $client->mobile ?? null,
                        $client->contact_number ?? null,
                        $client->phone ?? null,
                        $client->email ?? null,
                    ])->filter()->first();

                    return [
                        'id' => $client->id,
                        'name' => $name !== '' ? $name : 'Unnamed Client',
                        'contact' => $contact ?: '—',
                        'created_at' => Carbon::parse($client->created_at)->format('M d, Y h:i A'),
                    ];
                })->values();

            return response()->json(['clients' => $clients]);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Unable to load the client list.'], 500);
        }
    }

    public function exportSignupIncentives(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) ($request->year ?: Carbon::now()->year);
        $center = trim((string) $request->center);
        $rates = $this->signupIncentiveRatesFromRequest($request);
        $report = $this->buildSignupIncentivesReport($year, $center, $rates);
        $months = $this->signupIncentiveMonths;
        $filename = 'signup-incentives-' . $year . '.csv';

        return response()->streamDownload(function () use ($report, $months) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge([
                'Center',
                'Location',
                'Designation',
                'Name',
            ], array_values($months), ['Total']));

            foreach ($report['rows'] as $centerRow) {
                fputcsv($handle, array_merge([
                    $centerRow['center'],
                    $centerRow['location'],
                    'Total Incentive Earned',
                    '',
                ], array_map(function ($month) use ($centerRow) {
                    return $centerRow['signups'][$month] ?: 0;
                }, array_keys($months)), [$centerRow['total_signups']]));

                foreach ($centerRow['people'] as $person) {
                    fputcsv($handle, array_merge([
                        '',
                        '',
                        $person['designation'],
                        $person['name'],
                    ], array_map(function ($month) use ($person) {
                        return $person['amounts'][$month]
                            ? number_format($person['amounts'][$month], 2, '.', '')
                            : '';
                    }, array_keys($months)), [number_format($person['total'], 2, '.', '')]));
                }

                fputcsv($handle, array_merge([
                    '',
                    '',
                    '',
                    '',
                ], array_map(function ($month) use ($centerRow) {
                    return $centerRow['monthly_total_amounts'][$month]
                        ? number_format($centerRow['monthly_total_amounts'][$month], 2, '.', '')
                        : '-';
                }, array_keys($months)), [number_format($centerRow['total_amount'], 2, '.', '')]));
            }

            fputcsv($handle, array_merge([
                '',
                '',
                'Grand Total Sign Up',
                '',
            ], array_map(function ($month) use ($report) {
                return $report['grandSignups'][$month] ?: 0;
            }, array_keys($months)), [$report['grandTotalSignups']]));

            fputcsv($handle, array_merge([
                '',
                '',
                'Grand Total Php',
                '',
            ], array_map(function ($month) use ($report) {
                return $report['grandAmounts'][$month]
                    ? number_format($report['grandAmounts'][$month], 2, '.', '')
                    : '-';
            }, array_keys($months)), [number_format($report['grandTotalAmount'], 2, '.', '')]));

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function repeatPurchaseIncentivesReport(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) ($request->year ?: Carbon::now()->year);
        $center = trim((string) $request->center);
        $rate = max((float) $request->input('rate', 1), 0);
        $lowRepeatThreshold = max((float) $request->input('low_repeat_threshold', 5), 0);
        $report = $this->buildRepeatPurchaseIncentivesReport($year, $center, $rate, $lowRepeatThreshold);
       
        return view('reports.repeat_purchase_incentives', array_merge($report, [
            'year' => $year,
            'selectedCenter' => $center,
            'rate' => $rate,
            'lowRepeatThreshold' => $lowRepeatThreshold,
        ]));
    }

    public function repeatPurchaseTransactions(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $clientId = (int) $request->input('client_id');
        $center = trim((string) $request->input('center'));

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12 || $clientId < 1 || $center === '') {
            return response()->json(['message' => 'Invalid transaction list request.'], 422);
        }

        try {
            $connection = 'admin_crms';
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('clients') || !$schema->hasTable('transaction_details')) {
                return response()->json(['transactions' => []]);
            }

            $clientQuery = DB::connection($connection)->table('clients')->where('id', $clientId);

            if ($schema->hasColumn('clients', 'status')) {
                $clientQuery->whereRaw("LOWER(TRIM(status)) = 'active'");
            }

            if ($schema->hasColumn('clients', 'deleted_at')) {
                $clientQuery->whereNull('deleted_at');
            }

            $client = $clientQuery->first();

            if (!$client || $this->normalizeSignupCenter($client->center ?? '') !== $this->normalizeSignupCenter($center)) {
                return response()->json(['message' => 'You are not allowed to view these transactions.'], 403);
            }

            $authUser = auth()->user();

            if (strtolower(trim((string) ($authUser->role ?? ''))) === 'sedp') {
                $territoryKeys = $this->splitSignupTerritories($authUser->territory ?? '')
                    ->map(function ($territoryCenter) {
                        return $this->normalizeSignupCenter($territoryCenter);
                    })->filter()->flip();

                if (!$territoryKeys->has($this->normalizeSignupCenter($center))) {
                    return response()->json(['message' => 'You are not allowed to view these transactions.'], 403);
                }
            }

            $dateColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['date', 'created_at', 'updated_at']);
            $clientColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['client_id', 'customer_id', 'user_id']);
            $qtyColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['qty', 'quantity']);

            if (!$dateColumn || !$clientColumn) {
                return response()->json(['transactions' => []]);
            }

            $transactionColumns = [DB::raw($dateColumn . ' as transaction_date')];
            $transactionColumns[] = DB::raw($qtyColumn ? 'COALESCE(' . $qtyColumn . ', 1) as transaction_quantity' : '1 as transaction_quantity');

            $itemColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['product_name', 'item_name', 'description', 'product', 'name']);
            $referenceColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['reference_no', 'transaction_no', 'invoice_no', 'receipt_no']);

            if ($itemColumn) {
                $transactionColumns[] = $itemColumn . ' as transaction_item';
            }

            if ($referenceColumn) {
                $transactionColumns[] = $referenceColumn . ' as transaction_reference';
            }

            $transactions = DB::connection($connection)->table('transaction_details')
                ->select($transactionColumns)
                ->where($clientColumn, $clientId)
                ->whereNotNull($dateColumn)
                ->whereYear($dateColumn, $year)
                ->whereMonth($dateColumn, $month)
                ->when($schema->hasColumn('transaction_details', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->when($schema->hasColumn('transaction_details', 'status'), function ($query) {
                    $query->whereRaw("LOWER(TRIM(status)) NOT IN ('cancelled', 'canceled', 'void')");
                })
                ->orderBy($dateColumn)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'date' => Carbon::parse($transaction->transaction_date)->format('M d, Y h:i A'),
                        'item' => trim((string) ($transaction->transaction_item ?? 'Repeat purchase')) ?: 'Repeat purchase',
                        'reference' => trim((string) ($transaction->transaction_reference ?? '—')) ?: '—',
                        'quantity' => (float) $transaction->transaction_quantity,
                    ];
                })->values();
            
            return response()->json(['transactions' => $transactions]);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Unable to load transactions.'], 500);
        }
    }

    public function exportRepeatPurchaseIncentives(Request $request)
    {
        $this->authorizeSedpReports();

        $year = (int) ($request->year ?: Carbon::now()->year);
        $center = trim((string) $request->center);
        $rate = max((float) $request->input('rate', 1), 0);
        $lowRepeatThreshold = max((float) $request->input('low_repeat_threshold', 5), 0);
        $report = $this->buildRepeatPurchaseIncentivesReport($year, $center, $rate, $lowRepeatThreshold);
        $months = $this->signupIncentiveMonths;
        $filename = 'repeat-purchase-incentives-' . $year . '.csv';

        return response()->streamDownload(function () use ($report, $months) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge([
                'Center',
                'Location',
                'Members',
            ], array_values($months), ['Total']));

            foreach ($report['rows'] as $centerRow) {
                $firstMember = true;

                foreach ($centerRow['members'] as $member) {
                    fputcsv($handle, array_merge([
                        $firstMember ? $centerRow['center'] : '',
                        $firstMember ? $centerRow['location'] : '',
                        $member['name'],
                    ], array_map(function ($month) use ($member) {
                        return $member['monthly_refills'][$month] ?: 0;
                    }, array_keys($months)), [$member['total_refills']]));

                    $firstMember = false;
                }

                if (!$centerRow['members']) {
                    fputcsv($handle, array_merge([
                        $centerRow['center'],
                        $centerRow['location'],
                        'No repeat purchases found',
                    ], array_fill(0, count($months), 0), [0]));
                }

                fputcsv($handle, array_merge([
                    '',
                    '',
                    'Average',
                ], array_map(function ($month) use ($centerRow) {
                    return number_format($centerRow['monthly_average_refills'][$month], 2, '.', '');
                }, array_keys($months)), [number_format($centerRow['average_refills'], 2, '.', '')]));

                fputcsv($handle, array_merge([
                    '',
                    '',
                    'Total Refill',
                ], array_map(function ($month) use ($centerRow) {
                    return $centerRow['monthly_total_refills'][$month] ?: 0;
                }, array_keys($months)), [$centerRow['total_refills']]));

                fputcsv($handle, array_merge([
                    '',
                    '',
                    'Computed Incentive',
                ], array_map(function ($month) use ($centerRow) {
                    return number_format($centerRow['monthly_computed_incentives'][$month], 2, '.', '');
                }, array_keys($months)), [number_format($centerRow['computed_incentive'], 2, '.', '')]));
            }

            fputcsv($handle, array_merge([
                '',
                '',
                'Grand Total Refill',
            ], array_map(function ($month) use ($report) {
                return $report['grandMonthlyRefills'][$month] ?: 0;
            }, array_keys($months)), [$report['grandTotalRefills']]));

            fputcsv($handle, array_merge([
                '',
                '',
                'Grand Computed Incentive',
            ], array_map(function ($month) use ($report) {
                return number_format($report['grandMonthlyIncentives'][$month], 2, '.', '');
            }, array_keys($months)), [number_format($report['grandComputedIncentive'], 2, '.', '')]));

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function signupIncentiveRatesFromRequest(Request $request)
    {
        return [
            'CDW' => max((float) $request->input('rate_cdw', $this->signupIncentiveRates['CDW']), 0),
            'CDW2' => max((float) $request->input('rate_cdw2', $this->signupIncentiveRates['CDW2']), 0),
            'SPOM' => max((float) $request->input('rate_spom', $this->signupIncentiveRates['SPOM']), 0),
        ];
    }

    private function formatSignupIncentiveRate($rate)
    {
        return rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.');
    }

    private function joinSignupLabels($values)
    {
        $values = collect($values)->filter()->unique()->values();

        if ($values->count() <= 1) {
            return (string) $values->first();
        }

        return $values->slice(0, -1)->implode(', ') . ' and ' . $values->last();
    }

    private function normalizeSignupCenter($center)
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim((string) $center)));
    }

    private function splitSignupTerritories($territory)
    {
        return collect(preg_split('/[,;|\/\r\n]+/', (string) $territory))
            ->map(function ($item) {
                return trim($item);
            })
            ->filter()
            ->values();
    }

    private function crmClientTable($connection)
    {
        $schema = DB::connection($connection)->getSchemaBuilder();

        if ($schema->hasTable('clients')) {
            return 'clients';
        }

        if ($schema->hasTable('customers')) {
            return 'customers';
        }

        return null;
    }

    private function firstExistingCrmColumn($schema, $table, array $columns)
    {
        foreach ($columns as $column) {
            if ($schema->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function crmClientSignups($year, $centerFilterKey, array &$centerKeyToName)
    {
        $months = $this->signupIncentiveMonths;
        $emptyMonths = array_fill_keys(array_keys($months), 0);
        $signups = [];

        try {
            $connection = 'admin_crms';
            $table = 'clients';
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable($table) || !$schema->hasColumn($table, 'center') || !$schema->hasColumn($table, 'created_at')) {
                return $signups;
            }

            $query = DB::connection($connection)->table($table)
                ->select(
                    'center as signup_area',
                    DB::raw('MONTH(created_at) as signup_month'),
                    DB::raw('COUNT(*) as signup_count')
                )
                ->whereNotNull('center')
                ->where('center', '<>', '')
                ->whereNotNull('created_at')
                ->whereYear('created_at', $year)
                ->groupBy('center', DB::raw('MONTH(created_at)'));

            if ($schema->hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            foreach ($query->get() as $client) {
                $centerName = trim((string) $client->signup_area);
                $centerKey = $this->normalizeSignupCenter($centerName);

                if ($centerKey === '' || ($centerFilterKey !== '' && $centerKey !== $centerFilterKey)) {
                    continue;
                }

                $centerName = $centerKeyToName[$centerKey] ?? $centerName;
                $centerKeyToName[$centerKey] = $centerName;
                $month = (int) $client->signup_month;

                $signups[$centerName] = $signups[$centerName] ?? $emptyMonths;
                $signups[$centerName][$month] += (int) $client->signup_count;
            }
        } catch (\Exception $exception) {
            return $signups;
        }

        return $signups;
    }

    private function repeatPurchaseLocation($client)
    {
        $parts = collect([
            $client->location_barangay ?? null,
            $client->location_city ?? null,
            $client->location_province ?? null,
        ])->filter()->map(function ($value) {
            return trim((string) $value);
        })->filter()->values();

        if ($parts->isNotEmpty()) {
            return $parts->implode(', ');
        }

        return trim((string) ($client->address ?? $client->street_address ?? ''));
    }

    private function crmRepeatPurchaseClients($year, $centerFilterKey, array &$centerKeyToName)
    {
        try {
            $connection = 'admin_crms';
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('clients')
                || !$schema->hasColumn('clients', 'center')
                || !$schema->hasTable('transaction_details')) {
                return collect();
            }

            $dateColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['date', 'created_at', 'updated_at']);
            $clientColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['client_id', 'customer_id', 'user_id']);
            $qtyColumn = $this->firstExistingCrmColumn($schema, 'transaction_details', ['qty', 'quantity']);

            if (!$dateColumn || !$clientColumn) {
                return collect();
            }

            $clientSelect = ['id', 'center'];

            foreach (['user_id', 'name', 'first_name', 'middle_name', 'last_name', 'address', 'street_address', 'location_barangay', 'location_city', 'location_province'] as $column) {
                if ($schema->hasColumn('clients', $column)) {
                    $clientSelect[] = $column;
                }
            }

            $clients = DB::connection($connection)->table('clients')
                ->select(array_unique($clientSelect))
                ->whereNotNull('center')
                ->where('center', '<>', '')
                ->when($schema->hasColumn('clients', 'status'), function ($query) {
                    $query->whereRaw("LOWER(TRIM(status)) = 'active'");
                })
                ->when($schema->hasColumn('clients', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->get()
                ->filter(function ($client) use ($centerFilterKey) {
                    $centerKey = $this->normalizeSignupCenter($client->center ?? '');

                    return $centerKey !== '' && ($centerFilterKey === '' || $centerKey === $centerFilterKey);
                })
                ->values();

            if ($clients->isEmpty()) {
                return collect();
            }

            $clientIds = $clients->pluck('id')
                ->filter()
                ->unique()
                ->values();

            if ($clientIds->isEmpty()) {
                return collect();
            }

            $transactionQuery = DB::connection($connection)->table('transaction_details')
                ->select(
                    $clientColumn . ' as repeat_client_id',
                    DB::raw('MONTH(' . $dateColumn . ') as repeat_month'),
                    DB::raw($qtyColumn ? 'SUM(COALESCE(' . $qtyColumn . ', 1)) as repeat_count' : 'COUNT(*) as repeat_count')
                )
                ->whereIn($clientColumn, $clientIds->all())
                ->whereNotNull($dateColumn)
                ->whereYear($dateColumn, $year)
                ->groupBy($clientColumn, DB::raw('MONTH(' . $dateColumn . ')'));

            if ($schema->hasColumn('transaction_details', 'deleted_at')) {
                $transactionQuery->whereNull('deleted_at');
            }

            if ($schema->hasColumn('transaction_details', 'status')) {
                $transactionQuery->whereRaw("LOWER(TRIM(status)) NOT IN ('cancelled', 'canceled', 'void')");
            }

            $transactions = $transactionQuery->get()->groupBy('repeat_client_id');

            return $clients->map(function ($client) use ($transactions, &$centerKeyToName) {
                $centerName = trim((string) $client->center);
                $centerKey = $this->normalizeSignupCenter($centerName);
                $centerName = $centerKeyToName[$centerKey] ?? $centerName;
                $centerKeyToName[$centerKey] = $centerName;

                $monthlyRows = $transactions->get($client->id, collect());

                $name = trim((string) ($client->name ?? ''));

                if ($name === '') {
                    $name = trim(collect([
                        $client->first_name ?? '',
                        $client->middle_name ?? '',
                        $client->last_name ?? '',
                    ])->filter()->implode(' '));
                }

                return (object) [
                    'id' => $client->id,
                    'center_key' => $centerKey,
                    'center' => $centerName,
                    'location' => $this->repeatPurchaseLocation($client),
                    'name' => $name !== '' ? $name : 'Unnamed Member',
                    'monthly_rows' => $monthlyRows,
                ];
            })->filter(function ($client) {
                return $client->monthly_rows->sum('repeat_count') > 0;
            })->values();
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function buildRepeatPurchaseIncentivesReport($year, $centerFilter, $rate, $lowRepeatThreshold = 5)
    {
        $months = $this->signupIncentiveMonths;
        $monthKeys = array_keys($months);
        $emptyMonths = array_fill_keys($monthKeys, 0.0);
        $centers = Center::orderBy('name')->get();
        $centerOptions = $centers->pluck('name')->filter()->values();
        $centerKeyToName = [];

        foreach ($centerOptions as $centerOption) {
            $centerKey = $this->normalizeSignupCenter($centerOption);

            if ($centerKey !== '') {
                $centerKeyToName[$centerKey] = trim((string) $centerOption);
            }
        }

        $centerFilterKey = $this->normalizeSignupCenter($centerFilter);
        $clients = $this->crmRepeatPurchaseClients($year, $centerFilterKey, $centerKeyToName);

        foreach ($clients->pluck('center')->filter()->unique() as $centerName) {
            if (!$centerOptions->contains($centerName)) {
                $centerOptions->push($centerName);
            }
        }

        $rows = $clients->groupBy('center_key')->map(function ($centerClients) use ($monthKeys, $emptyMonths, $rate, $lowRepeatThreshold) {
            $firstClient = $centerClients->first();
            $members = $centerClients->sortBy('name')->map(function ($client) use ($monthKeys, $emptyMonths, $lowRepeatThreshold) {
                $monthlyRefills = $emptyMonths;

                foreach ($client->monthly_rows as $row) {
                    $month = (int) $row->repeat_month;

                    if (array_key_exists($month, $monthlyRefills)) {
                        $monthlyRefills[$month] += (float) $row->repeat_count;
                    }
                }

                return [
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'monthly_refills' => $monthlyRefills,
                    'total_refills' => array_sum($monthlyRefills),
                    'low_repeat_months' => collect($monthKeys)->filter(function ($month) use ($monthlyRefills, $lowRepeatThreshold) {
                        return $monthlyRefills[$month] <= $lowRepeatThreshold;
                    })->count(),
                ];
            })->values()->all();
        
            $memberCount = max(count($members), 1);
            $monthlyTotalRefills = $emptyMonths;

            foreach ($members as $member) {
                foreach ($monthKeys as $month) {
                    $monthlyTotalRefills[$month] += $member['monthly_refills'][$month];
                }
            }

            $monthlyAverageRefills = [];
            $monthlyComputedIncentives = [];

            foreach ($monthKeys as $month) {
                $monthlyAverageRefills[$month] = $monthlyTotalRefills[$month] / $memberCount;
                $monthlyComputedIncentives[$month] = $monthlyAverageRefills[$month] * $rate;
            }

            $totalRefills = array_sum($monthlyTotalRefills);
            $averageRefills = $totalRefills / $memberCount;

            return [
                'center' => $firstClient->center,
                'location' => $centerClients->pluck('location')->filter()->unique()->implode(' / '),
                'members' => $members,
                'member_count' => count($members),
                'low_repeat_months' => collect($members)->sum('low_repeat_months'),
                'monthly_total_refills' => $monthlyTotalRefills,
                'monthly_average_refills' => $monthlyAverageRefills,
                'monthly_computed_incentives' => $monthlyComputedIncentives,
                'total_refills' => $totalRefills,
                'average_refills' => $averageRefills,
                'computed_incentive' => $averageRefills * $rate,
            ];
        })->sortBy('center')->values();
        
        $grandMonthlyRefills = $emptyMonths;
        $grandMonthlyIncentives = $emptyMonths;
        $lowRepeatMonths = 0;

        foreach ($rows as $row) {
            $lowRepeatMonths += $row['low_repeat_months'];

            foreach ($monthKeys as $month) {
                $grandMonthlyRefills[$month] += $row['monthly_total_refills'][$month];
                $grandMonthlyIncentives[$month] += $row['monthly_computed_incentives'][$month];
            }
        }
        
        return [
            'months' => $months,
            'rows' => $rows,
            'centers' => $centerOptions->unique()->sort()->values(),
            'grandMonthlyRefills' => $grandMonthlyRefills,
            'grandMonthlyIncentives' => $grandMonthlyIncentives,
            'grandTotalRefills' => array_sum($grandMonthlyRefills),
            'grandComputedIncentive' => array_sum($grandMonthlyIncentives),
            'lowRepeatMonths' => $lowRepeatMonths,
        ];
    }

    private function buildSignupIncentivesReport($year, $centerFilter, array $rates)
    {
        $months = $this->signupIncentiveMonths;
        $monthKeys = array_keys($months);
        $emptyMonths = array_fill_keys($monthKeys, 0);
        $centers = Center::orderBy('name')->get();
        $centerOptions = $centers->pluck('name')->filter()->values();
        $centerKeyToName = [];
        $authUser = auth()->user();
        $authIsSedp = strtolower(trim((string) ($authUser->role ?? ''))) === 'sedp';
        $allowedCenterKeys = null;
        $territoryCenters = collect();

        foreach ($centerOptions as $centerOption) {
            $centerKey = $this->normalizeSignupCenter($centerOption);

            if ($centerKey !== '') {
                $centerKeyToName[$centerKey] = trim((string) $centerOption);
            }
        }

        if ($authIsSedp) {
            $territoryCenters = $this->splitSignupTerritories($authUser->territory ?? '');
            $allowedCenterKeys = [];

            foreach ($territoryCenters as $territoryCenter) {
                $territoryCenterKey = $this->normalizeSignupCenter($territoryCenter);

                if ($territoryCenterKey === '') {
                    continue;
                }

                $allowedCenterKeys[$territoryCenterKey] = true;
                $centerKeyToName[$territoryCenterKey] = $centerKeyToName[$territoryCenterKey] ?? trim((string) $territoryCenter);
            }

            $centerOptions = $centerOptions->filter(function ($centerOption) use ($allowedCenterKeys) {
                return isset($allowedCenterKeys[$this->normalizeSignupCenter($centerOption)]);
            })->values();

            foreach ($territoryCenters as $territoryCenter) {
                $territoryCenterKey = $this->normalizeSignupCenter($territoryCenter);
                $territoryCenterName = $centerKeyToName[$territoryCenterKey] ?? trim((string) $territoryCenter);

                if ($territoryCenterKey !== '' && !$centerOptions->contains($territoryCenterName)) {
                    $centerOptions->push($territoryCenterName);
                }
            }
        }

        $centerMeta = $centers->mapWithKeys(function ($center) {
            $location = $center->location
                ?? $center->address
                ?? $center->area
                ?? '';

            return [trim((string) $center->name) => [
                'location' => trim((string) $location),
            ]];
        });
        $centerFilterKey = $this->normalizeSignupCenter($centerFilter);

        if ($allowedCenterKeys !== null && $centerFilterKey !== '' && !isset($allowedCenterKeys[$centerFilterKey])) {
            $centerFilterKey = '__NO_ACCESS__';
        }

        $signups = $this->crmClientSignups($year, $centerFilterKey, $centerKeyToName);

        foreach (array_keys($signups) as $centerName) {
            $centerKey = $this->normalizeSignupCenter($centerName);

            if ($allowedCenterKeys !== null && !isset($allowedCenterKeys[$centerKey])) {
                continue;
            }

            if (!$centerOptions->contains($centerName)) {
                $centerOptions->push($centerName);
            }
        }

        $sedpUsers = User::whereRaw('LOWER(TRIM(role)) = ?', ['sedp'])
            ->whereIn('designation', ['CDW', 'CDW2', 'SPOM'])
            ->orderBy('designation')
            ->orderBy('name')
            ->get(['name', 'designation', 'territory']);

        $peopleByCenter = [];
        $includedSedpKeys = [];

        foreach ($sedpUsers as $user) {
            $assignedCenters = $this->splitSignupTerritories($user->territory);

            foreach ($assignedCenters as $assignedCenter) {
                $assignedCenterKey = $this->normalizeSignupCenter($assignedCenter);

                if ($assignedCenterKey === '') {
                    continue;
                }

                $assignedCenter = $centerKeyToName[$assignedCenterKey] ?? trim((string) $assignedCenter);
                $centerKeyToName[$assignedCenterKey] = $assignedCenter;

                if ($allowedCenterKeys !== null && !isset($allowedCenterKeys[$assignedCenterKey])) {
                    continue;
                }

                if ($centerFilterKey !== '' && $assignedCenterKey !== $centerFilterKey) {
                    continue;
                }

                $sedpKey = $assignedCenterKey . '|' . $user->designation . '|' . $user->name;

                if (isset($includedSedpKeys[$sedpKey])) {
                    continue;
                }

                $includedSedpKeys[$sedpKey] = true;

                $peopleByCenter[$assignedCenterKey][] = [
                    'designation' => $user->designation,
                    'name' => $user->name,
                    'rate' => $rates[$user->designation] ?? 0,
                ];

                if (!$centerOptions->contains($assignedCenter)) {
                    $centerOptions->push($assignedCenter);
                }
            }
        }

        $filterCenters = $centerOptions->unique()->sort()->values();
        $centerNamesByKey = [];
        $signupsByCenterKey = [];

        foreach ($signups as $centerName => $monthlySignups) {
            $centerKey = $this->normalizeSignupCenter($centerName);

            if ($centerKey === '' || ($centerFilterKey !== '' && $centerKey !== $centerFilterKey)) {
                continue;
            }

            if ($allowedCenterKeys !== null && !isset($allowedCenterKeys[$centerKey])) {
                continue;
            }

            $centerNamesByKey[$centerKey] = $centerKeyToName[$centerKey] ?? trim((string) $centerName);
            $signupsByCenterKey[$centerKey] = $signupsByCenterKey[$centerKey] ?? $emptyMonths;

            foreach ($monthKeys as $month) {
                $signupsByCenterKey[$centerKey][$month] += $monthlySignups[$month] ?? 0;
            }
        }

        foreach (array_keys($peopleByCenter) as $centerKey) {
            if ($centerKey === '' || ($centerFilterKey !== '' && $centerKey !== $centerFilterKey)) {
                continue;
            }

            if ($allowedCenterKeys !== null && !isset($allowedCenterKeys[$centerKey])) {
                continue;
            }

            $centerNamesByKey[$centerKey] = $centerKeyToName[$centerKey] ?? $centerKey;
        }

        $centerKeys = collect($centerNamesByKey)->keys()->sortBy(function ($centerKey) use ($centerNamesByKey) {
            return $centerNamesByKey[$centerKey];
        })->values();

        $rows = [];
        $grandSignups = $emptyMonths;
        $grandAmounts = array_fill_keys($monthKeys, 0.0);

        foreach ($centerKeys as $centerKey) {
            $centerName = $centerNamesByKey[$centerKey];
            $monthlySignups = $signupsByCenterKey[$centerKey] ?? $emptyMonths;
            $people = collect($peopleByCenter[$centerKey] ?? [])->sortBy(function ($person) {
                $designationOrder = ['CDW' => 1, 'CDW2' => 2, 'SPOM' => 3];

                return sprintf(
                    '%02d-%s',
                    $designationOrder[$person['designation']] ?? 99,
                    $person['name']
                );
            })->map(function ($person) {
                $person['display_rate'] = $this->formatSignupIncentiveRate($person['rate']);

                return $person;
            })->values();

            $personRows = [];
            $monthlyTotalAmounts = array_fill_keys($monthKeys, 0.0);

            foreach ($people as $person) {
                $amounts = [];

                foreach ($monthKeys as $month) {
                    $amounts[$month] = $monthlySignups[$month] * $person['rate'];
                    $monthlyTotalAmounts[$month] += $amounts[$month];
                }

                $personRows[] = array_merge($person, [
                    'amounts' => $amounts,
                    'total' => array_sum($amounts),
                ]);
            }

            foreach ($monthKeys as $month) {
                $grandSignups[$month] += $monthlySignups[$month];
                $grandAmounts[$month] += $monthlyTotalAmounts[$month];
            }

            $rows[] = [
                'center' => $centerName,
                'location' => $centerMeta[$centerName]['location'] ?? '',
                'signups' => $monthlySignups,
                'total_signups' => array_sum($monthlySignups),
                'people' => $personRows,
                'monthly_total_amounts' => $monthlyTotalAmounts,
                'total_amount' => array_sum($monthlyTotalAmounts),
            ];
        }

        return [
            'months' => $months,
            'rows' => $rows,
            'centers' => $filterCenters,
            'grandSignups' => $grandSignups,
            'grandAmounts' => $grandAmounts,
            'grandTotalSignups' => array_sum($grandSignups),
            'grandTotalAmount' => array_sum($grandAmounts),
            'isSedpTerritoryView' => $authIsSedp,
            'territoryCenters' => $authIsSedp
                ? $territoryCenters->map(function ($center) use ($centerKeyToName) {
                    $centerKey = $this->normalizeSignupCenter($center);

                    return $centerKeyToName[$centerKey] ?? trim((string) $center);
                })->filter()->unique()->sort()->values()
                : collect(),
        ];
    }

    public function dpoReport(Request $request)
    {
        $from = $request->from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ?? Carbon::now()->format('Y-m-d');

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $orders = AdPurchaseOrder::with(['items.partialReceipts', 'partialReceipts.item', 'ad'])
            ->whereBetween(DB::raw('DATE(COALESCE(submitted_at, created_at))'), [$from, $to])
            ->whereNotNull('so_number')
            ->whereRaw("TRIM(so_number) <> ''")
            ->when($request->filled('so_number'), function ($query) use ($request) {
                $query->where('so_number', 'like', '%' . trim((string) $request->so_number) . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('po_number', 'like', '%' . $search . '%')
                        ->orWhere('so_number', 'like', '%' . $search . '%')
                        ->orWhere('dr_number', 'like', '%' . $search . '%')
                        ->orWhere('si_number', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhereHas('partialReceipts', function ($receiptQuery) use ($search) {
                            $receiptQuery->where('dr_number', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when(auth()->user()->role !== 'Admin', function ($query) {
                $query->where('ad_user_id', auth()->id());
            })
            ->orderByRaw('CASE WHEN so_number IS NULL OR so_number = "" THEN 1 ELSE 0 END')
            ->orderBy('so_number')
            ->orderByDesc('id')
            ->get();

        $buildOrderHistory = function ($order) {
            $orderedTotal = (int) $order->items->sum('qty');
            $receivedTotal = (int) $order->items->sum(function ($item) {
                return min(
                    (int) $item->qty,
                    max(
                        (int) ($item->partial_received_qty ?? 0),
                        (int) $item->partialReceipts->sum('received_qty')
                    )
                );
            });
            $confirmedTotal = (int) $order->items->sum(function ($item) {
                return min((int) $item->qty, (int) $item->partialReceipts->sum('confirmed_qty'));
            });
            $documentHistory = collect([
                [
                    'date' => optional($order->delivery_date ?: $order->submitted_at ?: $order->created_at)->format('M d, Y'),
                    'so_number' => $order->so_number,
                    'dr_number' => $order->dr_number,
                    'si_number' => $order->si_number,
                    'product_name' => 'All products',
                    'received_qty' => $receivedTotal,
                    'confirmed_qty' => $confirmedTotal,
                    'pending_qty' => max($orderedTotal - max($receivedTotal, $confirmedTotal), 0),
                    'status' => $order->status,
                    'type' => 'DPO Document',
                ],
            ])->merge($order->partialReceipts->sortBy('id')->map(function ($receipt) use ($order) {
                return [
                    'date' => optional($receipt->delivery_date ?: $order->delivery_date)->format('M d, Y'),
                    'so_number' => $order->so_number,
                    'dr_number' => $receipt->dr_number ?: $order->dr_number,
                    'si_number' => $order->si_number,
                    'product_name' => optional($receipt->item)->product_name ?: 'Product',
                    'received_qty' => (int) $receipt->received_qty,
                    'confirmed_qty' => (int) $receipt->confirmed_qty,
                    'pending_qty' => max((int) $receipt->received_qty - (int) $receipt->confirmed_qty, 0),
                    'status' => $receipt->status,
                    'type' => 'Partial DR Receipt',
                ];
            }))->values();

            return [
                'id' => $order->id,
                'po_number' => $order->po_number,
                'so_number' => strtoupper(trim((string) $order->so_number)),
                'dr_number' => $order->dr_number,
                'si_number' => $order->si_number,
                'business_name' => $order->business_name,
                'status' => $order->status,
                'date' => optional($order->submitted_at ?: $order->created_at)->format('M d, Y'),
                'total_qty' => (int) $order->total_qty,
                'total_amount' => (float) $order->total_amount,
                'document_history' => $documentHistory,
                'items' => $order->items->map(function ($item) {
                    $receivedQty = min(max((int) ($item->partial_received_qty ?? 0), 0), (int) $item->qty);
                    $confirmedQty = min((int) $item->qty, (int) $item->partialReceipts->sum('confirmed_qty'));

                    return [
                        'name' => $item->product_name,
                        'sku' => $item->sku,
                        'ordered_qty' => (int) $item->qty,
                        'received_qty' => $receivedQty,
                        'confirmed_qty' => $confirmedQty,
                        'pending_qty' => max((int) $item->qty - max($receivedQty, $confirmedQty), 0),
                        'unit_price' => (float) $item->unit_price,
                        'line_total' => (float) $item->line_total,
                        'receipts' => $item->partialReceipts->map(function ($receipt) {
                            return [
                                'delivery_date' => optional($receipt->delivery_date)->format('M d, Y'),
                                'dr_number' => $receipt->dr_number,
                                'received_qty' => (int) $receipt->received_qty,
                                'confirmed_qty' => (int) $receipt->confirmed_qty,
                                'pending_qty' => max((int) $receipt->received_qty - (int) $receipt->confirmed_qty, 0),
                                'status' => $receipt->status,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        };

        $ordersBySo = $orders->groupBy(function ($order) {
            return strtoupper(trim((string) $order->so_number));
        });

        $rows = $ordersBySo->map(function ($soOrders, $soNumber) {
            $latestOrder = $soOrders->sortByDesc(function ($order) {
                return optional($order->submitted_at ?: $order->created_at)->timestamp ?: 0;
            })->first();

            return (object) [
                'so_key' => $soNumber,
                'so_number' => $soNumber,
                'latest_date' => $latestOrder->submitted_at ?: $latestOrder->created_at,
                'business_name' => $soOrders->pluck('business_name')->filter()->unique()->implode(', '),
                'order_count' => $soOrders->count(),
                'document_count' => $soOrders->sum(function ($order) {
                    return 1 + $order->partialReceipts->count();
                }),
                'total_qty' => (int) $soOrders->sum('total_qty'),
                'total_amount' => (float) $soOrders->sum('total_amount'),
                'status' => $latestOrder->status,
                'statuses' => $soOrders->pluck('status')->filter()->unique()->implode(', '),
            ];
        })->sortByDesc(function ($row) {
            return optional($row->latest_date)->timestamp ?: 0;
        })->values();

        $itemHistories = $ordersBySo->mapWithKeys(function ($soOrders, $soNumber) use ($buildOrderHistory) {
            $sortedOrders = $soOrders->sortBy(function ($order) {
                return optional($order->submitted_at ?: $order->created_at)->timestamp ?: 0;
            })->values();

            return [
                $soNumber => [
                    'so_number' => $soNumber,
                    'business_name' => $soOrders->pluck('business_name')->filter()->unique()->implode(', '),
                    'order_count' => $soOrders->count(),
                    'total_qty' => (int) $soOrders->sum('total_qty'),
                    'total_amount' => (float) $soOrders->sum('total_amount'),
                    'orders' => $sortedOrders->map($buildOrderHistory)->values(),
                ],
            ];
        });

        $statusOptions = ['Pending', 'For Delivery', 'SO Created', 'Partial Received', 'Completed', 'Cancelled'];
        $summary = [
            'so_count' => $rows->count(),
            'adpo_count' => $orders->count(),
            'document_count' => $rows->sum('document_count'),
            'total_qty' => $orders->sum('total_qty'),
            'total_amount' => $orders->sum('total_amount'),
        ];

        return view('reports.dpo_report', compact('orders', 'rows', 'from', 'to', 'statusOptions', 'summary', 'itemHistories'));
    }

    public function dailySalesReport(Request $request)
    {
        $user = auth()->user();

        $from = $request->from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = $request->to?? Carbon::now()->format('Y-m-d');

        $reports = OrderDetail::with('dealer')
            ->where('ad_id', $user->ad->id)
            ->where('status', 'Completed')
            ->whereBetween(
                DB::raw('DATE(date)'),
                [$from, $to]
            )
            ->orderBy('date', 'DESC')
            ->get()
            ->groupBy(function ($item) {

                return Carbon::parse($item->date)->format('Y-m-d');

            });
       
        $items = Product::where('ad_user_id', $user->id)
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();

        $otherCharges = $this->dailySalesOtherCharges($user);

        $grandTotal = OrderDetail::whereBetween(
                DB::raw('DATE(date)'),
                [$from, $to]
            )
            ->where('ad_id', $user->ad->id)
            ->where('status', 'Completed')
            ->get()
            ->sum(function ($r) use ($otherCharges) {
                $lineSubtotal = (float) $r->qty * (float) $r->price;
                $deliveryFee = (float) ($r->delivery_fee ?? 0);
                $otherChargeTotal = $this->calculateDailySalesOtherCharges($lineSubtotal, $r, $otherCharges);

                return $lineSubtotal + $deliveryFee + $otherChargeTotal;

            });

        $reportTabs = $this->dailySalesReportTabs($user, $from, $to, $otherCharges, $reports, $items, $grandTotal);

        return view(
            'reports.daily_sales',
            compact(
                'reports',
                'items',
                'from',
                'to',
                'grandTotal',
                'otherCharges',
                'reportTabs'
            )
        );
    }

    private function dailySalesReportTabs($user, $from, $to, $otherCharges, $regularReports, $regularItems, $regularGrandTotal)
    {
        $definitions = [
            [
                'key' => 'regular',
                'label' => 'Regular',
                'connection' => null,
                'reports' => $regularReports,
                'items' => $regularItems,
                'grand_total' => $regularGrandTotal,
            ],
            [
                'key' => 'project_rise',
                'label' => 'Project Rise',
                'connection' => 'admin_crms',
            ],
            [
                'key' => 'project_genesis',
                'label' => 'Project Genesis',
                'connection' => 'admin_crms2',
            ],
        ];

        return collect($definitions)->map(function ($definition) use ($user, $from, $to, $otherCharges) {
            if (!$definition['connection']) {
                $reports = $definition['reports'];
                $items = $definition['items'];
                $grandTotal = $definition['grand_total'];
            } else {
                $orders = $this->dailySalesRemoteOrders($definition['connection'], $user->ad->id ?? null, $from, $to);
                $reports = $orders->groupBy(function ($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });
                $items = $this->dailySalesRemoteItems($definition['connection'], $orders);
                $grandTotal = $orders->sum(function ($order) use ($otherCharges) {
                    $lineSubtotal = (float) $order->qty * (float) $order->price;
                    $deliveryFee = (float) ($order->delivery_fee ?? 0);
                    $otherChargeTotal = $this->calculateDailySalesOtherCharges($lineSubtotal, $order, $otherCharges);

                    return $lineSubtotal + $deliveryFee + $otherChargeTotal;
                });
            }

            $transactions = $reports->flatten(1);

            return [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'connection' => $definition['connection'],
                'reports' => $reports,
                'items' => $items,
                'grand_total' => $grandTotal,
                'transaction_count' => $transactions->count(),
                'dealer_count' => $transactions->pluck('dealer_id')->filter()->unique()->count(),
                'products_sold' => $transactions->sum('qty'),
            ];
        })->values();
    }

    private function dailySalesRemoteOrders($connection, $adId, $from, $to)
    {
        if (!$adId) {
            return collect();
        }

        try {
            $database = DB::connection($connection);
            $schema = $database->getSchemaBuilder();

            if (!$schema->hasTable('order_details')
                || !$schema->hasColumn('order_details', 'ad_id')
                || !$schema->hasColumn('order_details', 'item')
                || !$schema->hasColumn('order_details', 'qty')) {
                return collect();
            }

            if (!$schema->hasColumn('order_details', 'date') && !$schema->hasColumn('order_details', 'created_at')) {
                return collect();
            }

            $dateColumn = $schema->hasColumn('order_details', 'date') ? 'date' : 'created_at';
            $query = $database->table('order_details as od')
                ->select('od.*')
                ->where('od.ad_id', $adId)
                ->whereBetween(DB::raw('DATE(od.' . $dateColumn . ')'), [$from, $to])
                ->orderBy('od.' . $dateColumn, 'DESC');

            if ($schema->hasColumn('order_details', 'status')) {
                $query->where('od.status', 'Completed');
            }

            if ($schema->hasColumn('order_details', 'deleted_at')) {
                $query->whereNull('od.deleted_at');
            }

            if ($schema->hasTable('dealers') && $schema->hasColumn('order_details', 'dealer_id')) {
                $joinedDealers = false;

                if ($schema->hasColumn('dealers', 'user_id')) {
                    $query->leftJoin('dealers as d', 'od.dealer_id', '=', 'd.user_id');
                    $joinedDealers = true;
                } elseif ($schema->hasColumn('dealers', 'id')) {
                    $query->leftJoin('dealers as d', 'od.dealer_id', '=', 'd.id');
                    $joinedDealers = true;
                }

                if ($joinedDealers && $schema->hasColumn('dealers', 'name')) {
                    $query->addSelect('d.name as dealer_name');
                }
            }

            return $query->get()->map(function ($order) use ($dateColumn) {
                $order->date = $order->date ?? $order->{$dateColumn} ?? now();
                $order->qty = (float) ($order->qty ?? $order->quantity ?? 0);
                $amount = (float) ($order->amount ?? $order->total_amount ?? 0);
                $order->price = (float) ($order->price ?? ($order->qty > 0 && $amount > 0 ? $amount / $order->qty : $amount));
                $order->delivery_fee = (float) ($order->delivery_fee ?? 0);
                $order->delivery_type = $order->delivery_type ?? 'pickup';
                $order->payment_method = strtolower(str_replace(' ', '_', (string) ($order->payment_method ?? 'cash')));
                $order->transaction_id = $order->transaction_id ?? $order->id ?? '-';
                $order->item = $order->item ?? $order->product_name ?? $order->product ?? '-';
                $order->dealer = (object) [
                    'name' => $order->dealer_name ?? ($order->dealer ?? '-'),
                ];

                return $order;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function dailySalesRemoteItems($connection, $orders)
    {
        try {
            $database = DB::connection($connection);
            $schema = $database->getSchemaBuilder();

            if ($schema->hasTable('products') && $schema->hasColumn('products', 'product_name')) {
                $query = $database->table('products')->select('product_name');

                if ($schema->hasColumn('products', 'sku')) {
                    $query->addSelect('sku');
                } else {
                    $query->selectRaw('NULL as sku');
                }

                if ($schema->hasColumn('products', 'status')) {
                    $query->where(function ($query) {
                        $query->where('status', 'Activate')
                            ->orWhereNull('status');
                    });
                }

                $items = $query->orderBy('product_name')->get();

                if ($items->isNotEmpty()) {
                    return $items;
                }
            }
        } catch (\Exception $exception) {
            // Fall through to order-item columns.
        }

        return $orders->pluck('item')
            ->filter()
            ->unique()
            ->sort()
            ->map(function ($item) {
                return (object) [
                    'sku' => null,
                    'product_name' => $item,
                ];
            })
            ->values();
    }

    public function exportDailySales(Request $request)
    {
        $user = auth()->user();

        $from = $request->from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ?? Carbon::now()->format('Y-m-d');

        return Excel::download(
            new DailySalesExport($from, $to, $user),
            'daily-sales-report.xlsx'
        );
    }

    private function dailySalesOtherCharges($user)
    {
        if (!$user || !Schema::hasTable('other_charges')) {
            return collect();
        }

        return OtherCharge::where('ad_user_id', $user->id)
            ->where('is_active', 1)
            ->whereIn('applies_to', ['order', 'delivery', 'dealer', 'customer'])
            ->get();
    }

    private function calculateDailySalesOtherCharges($lineSubtotal, $order, $otherCharges)
    {
        return $otherCharges->sum(function ($charge) use ($lineSubtotal, $order) {
            if ($charge->applies_to === 'delivery' && $order->delivery_type !== 'delivery') {
                return 0;
            }

            if ($charge->charge_type === 'percentage') {
                return (float) $lineSubtotal * ((float) $charge->amount / 100);
            }

            return (float) $charge->amount;
        });
    }

    public function inventoryStockLevelReport(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $report = $this->buildInventoryStockLevelReport($request);

        return view('reports.inventory_stock_level', $report);
    }

    public function exportInventoryStockLevel(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $report = $this->buildInventoryStockLevelReport($request);
        $fileName = 'inventory-stock-level-' . $report['asOf']->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new InventoryStockLevelExport($report['rows'], $report['products'], $report['asOf']),
            $fileName
        );
    }

    public function monthlySalesReport(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        return view('reports.monthly_sales', $this->buildMonthlySalesReport($request));
    }

    public function exportMonthlySales(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $report = $this->buildMonthlySalesReport($request);

        return Excel::download(
            new MonthlySalesExport(
                $report['rows'],
                $report['products'],
                $report['paymentColumns'],
                $report['period']
            ),
            'monthly-sales-' . $report['period']->format('Y-m') . '.xlsx'
        );
    }

    public function voucherHistoryReport(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        return view('reports.voucher_history', $this->buildVoucherHistoryReport($request));
    }

    public function exportVoucherHistory(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $report = $this->buildVoucherHistoryReport($request);

        return Excel::download(
            new VoucherHistoryExport($report['historyRows']),
            'voucher-history-' . $report['from']->format('Ymd') . '-' . $report['to']->format('Ymd') . '.xlsx'
        );
    }

    public function agingReport(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'Admin';
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->as_of)->endOfDay()
            : Carbon::today()->endOfDay();

        $distributorQuery = AreaDistributor::query()
            ->whereHas('userAds', function ($query) {
                $query->where('role', 'Area Distributor');
            });

        if (!$isAdmin) {
            $distributorQuery->where('user_id', $user->id);
        }

        $distributors = $distributorQuery
            ->orderBy('business_name')
            ->get(['id', 'user_id', 'business_name', 'name', 'store_code']);
        $distributorOptions = $distributors;

        $selectedDistributorId = $isAdmin && $request->filled('distributor')
            ? (int) $request->distributor
            : null;

        if ($selectedDistributorId) {
            $distributors = $distributors->where('id', $selectedDistributorId)->values();
        }

        $adIds = $distributors->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values();
        $adUserIds = $distributors->pluck('user_id')->filter()->map(function ($id) {
            return (int) $id;
        })->values();
        $distributorById = $distributors->keyBy('id');
        $distributorByUserId = $distributors->keyBy('user_id');
        $ledger = collect();

        $purchaseRows = AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as adpo_areas'), 'ad_purchase_orders.ad_id', '=', 'adpo_areas.ad_id')
            ->select(
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.product_name',
                'ad_purchase_order_items.sku',
                'ad_purchase_order_items.qty',
                'ad_purchase_orders.ad_id',
                'ad_purchase_orders.ad_user_id',
                'ad_purchase_orders.business_name',
                'ad_purchase_orders.authorized_territory',
                'adpo_areas.area_name as ad_area',
                DB::raw('COALESCE(ad_purchase_orders.submitted_at, ad_purchase_orders.created_at) as stock_date')
            )
            ->whereIn('ad_purchase_orders.ad_user_id', $adUserIds)
            ->where('ad_purchase_orders.status', 'Completed')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('ad_purchase_orders.submitted_at', '<=', $asOf->toDateString())
                    ->orWhere(function ($query) use ($asOf) {
                        $query->whereNull('ad_purchase_orders.submitted_at')
                            ->whereDate('ad_purchase_orders.created_at', '<=', $asOf->toDateString());
                    });
            })
            ->get();

        foreach ($purchaseRows as $row) {
            $distributor = $distributorById->get((int) $row->ad_id)
                ?: $distributorByUserId->get((int) $row->ad_user_id);
            $ledger->push((object) [
                'date' => $row->stock_date ? Carbon::parse($row->stock_date) : $asOf->copy(),
                'area' => $row->authorized_territory ?: $row->ad_area,
                'distributor_id' => $distributor ? (int) $distributor->id : (int) $row->ad_id,
                'distributor_name' => $row->business_name
                    ?: optional($distributor)->business_name
                    ?: optional($distributor)->name
                    ?: 'Area Distributor',
                'product_id' => $row->product_id,
                'sku' => $row->sku,
                'product_name' => $row->product_name,
                'qty' => (float) $row->qty,
                'source' => 'Completed AD',
            ]);
        }

        $movements = InventoryTransfer::whereIn('ad_user_id', $adUserIds)
            ->whereDate('transfer_date', '<=', $asOf->toDateString())
            ->orderBy('transfer_date')
            ->orderBy('id')
            ->get();

        foreach ($movements as $movement) {
            $date = $movement->transfer_date ? $movement->transfer_date->copy() : Carbon::parse($movement->created_at);
            $distributor = $distributorById->get((int) $movement->ad_id)
                ?: $distributorByUserId->get((int) $movement->ad_user_id);
            $distributorId = $distributor ? (int) $distributor->id : (int) $movement->ad_id;
            $distributorName = optional($distributor)->business_name
                ?: optional($distributor)->name
                ?: 'Area Distributor';

            if ($movement->movement_type === 'in') {
                $ledger->push($this->agingLedgerRow($date, $movement->to_area, $movement, (float) $movement->qty, 'Inventory IN', $distributorId, $distributorName));
            }

            if ($movement->movement_type === 'out') {
                $ledger->push($this->agingLedgerRow($date, $movement->from_area, $movement, -1 * (float) $movement->qty, 'Inventory OUT', $distributorId, $distributorName));
            }

            if ($movement->movement_type === 'transfer') {
                $ledger->push($this->agingLedgerRow($date, $movement->from_area, $movement, -1 * (float) $movement->qty, 'Transfer OUT', $distributorId, $distributorName));
                $ledger->push($this->agingLedgerRow($date, $movement->to_area, $movement, (float) $movement->qty, 'Transfer IN', $distributorId, $distributorName));
            }
        }

        if ($adIds->isNotEmpty()) {
            $orderRows = OrderDetail::leftJoin('dealers', 'order_details.dealer_id', '=', 'dealers.user_id')
                ->leftJoin('area_distributors as ordering_ads', 'order_details.dealer_id', '=', 'ordering_ads.user_id')
                ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as ordering_ad_areas'), 'ordering_ads.id', '=', 'ordering_ad_areas.ad_id')
                ->select(
                    'dealers.area as dealer_area',
                    'ordering_ad_areas.area_name as ad_area',
                    'order_details.ad_id',
                    'order_details.item',
                    DB::raw('SUM(order_details.qty) as qty'),
                    DB::raw('MAX(order_details.date) as order_date')
                )
                ->whereIn('order_details.ad_id', $adIds)
                ->where('order_details.status', 'Completed')
                ->whereDate('order_details.date', '<=', $asOf->toDateString())
                ->groupBy('dealers.area', 'ordering_ad_areas.area_name', 'order_details.ad_id', 'order_details.item')
                ->get();

            foreach ($orderRows as $row) {
                $distributor = $distributorById->get((int) $row->ad_id);
                $ledger->push((object) [
                    'date' => $row->order_date ? Carbon::parse($row->order_date) : $asOf->copy(),
                    'area' => $row->dealer_area ?: $row->ad_area,
                    'distributor_id' => (int) $row->ad_id,
                    'distributor_name' => optional($distributor)->business_name
                        ?: optional($distributor)->name
                        ?: 'Area Distributor',
                    'product_id' => null,
                    'sku' => null,
                    'product_name' => $row->item,
                    'qty' => -1 * (float) $row->qty,
                    'source' => 'Dealer Order',
                ]);
            }
        }

        $batches = $this->buildAgingBatches($ledger, $asOf);
        $areas = $batches->pluck('area')->filter()->unique()->sort()->values();
        $products = $batches->pluck('product_name')->filter()->unique()->sort()->values();

        if ($request->filled('area')) {
            $batches = $batches->where('area', $request->area)->values();
        }

        if ($request->filled('product')) {
            $batches = $batches->where('product_name', $request->product)->values();
        }

        if ($request->filled('bucket')) {
            $batches = $batches->where('bucket', $request->bucket)->values();
        }

        $summary = (object) [
            'total_qty' => $batches->sum('qty'),
            'sku_count' => $batches->pluck('product_name')->unique()->count(),
            'area_count' => $batches->pluck('area')->filter()->unique()->count(),
            'distributor_count' => $batches->pluck('distributor_id')->filter()->unique()->count(),
            'oldest_days' => $batches->max('age_days') ?: 0,
        ];

        $bucketTotals = collect(['0-30', '31-60', '61-90', '90+'])->mapWithKeys(function ($bucket) use ($batches) {
            return [$bucket => $batches->where('bucket', $bucket)->sum('qty')];
        });

        return view('reports.aging', compact(
            'batches',
            'areas',
            'products',
            'summary',
            'bucketTotals',
            'asOf',
            'distributors',
            'distributorOptions',
            'isAdmin'
        ));
    }

    private function agingLedgerRow($date, $area, $movement, $qty, $source, $distributorId = null, $distributorName = null)
    {
        return (object) [
            'date' => $date,
            'area' => $area,
            'distributor_id' => $distributorId,
            'distributor_name' => $distributorName ?: 'Area Distributor',
            'product_id' => $movement->product_id,
            'sku' => $movement->sku,
            'product_name' => $movement->item_name,
            'qty' => $qty,
            'source' => $source,
        ];
    }

    private function buildAgingBatches($ledger, Carbon $asOf)
    {
        $openBatches = [];

        foreach ($ledger->sortBy('date') as $entry) {
            if (!$entry->area || !$entry->product_name || (float) $entry->qty == 0) {
                continue;
            }

            $key = ($entry->distributor_id ?: 'unknown') . '|' . $entry->area . '|' . $this->normalizeProductName($entry->product_name);

            if (!isset($openBatches[$key])) {
                $openBatches[$key] = [];
            }

            if ($entry->qty > 0) {
                $openBatches[$key][] = [
                    'date' => $entry->date,
                    'area' => $entry->area,
                    'distributor_id' => $entry->distributor_id,
                    'distributor_name' => $entry->distributor_name,
                    'product_id' => $entry->product_id,
                    'sku' => $entry->sku,
                    'product_name' => $entry->product_name,
                    'qty' => (float) $entry->qty,
                    'source' => $entry->source,
                ];

                continue;
            }

            $remainingDeduction = abs((float) $entry->qty);
            foreach ($openBatches[$key] as &$batch) {
                if ($remainingDeduction <= 0) {
                    break;
                }

                $deduct = min($batch['qty'], $remainingDeduction);
                $batch['qty'] -= $deduct;
                $remainingDeduction -= $deduct;
            }
            unset($batch);

            $openBatches[$key] = array_values(array_filter($openBatches[$key], function ($batch) {
                return $batch['qty'] > 0;
            }));
        }

        return collect($openBatches)
            ->flatten(1)
            ->map(function ($batch) use ($asOf) {
                $ageDays = max(0, $batch['date']->diffInDays($asOf));

                return (object) [
                    'date' => $batch['date'],
                    'area' => $batch['area'],
                    'distributor_id' => $batch['distributor_id'],
                    'distributor_name' => $batch['distributor_name'],
                    'product_id' => $batch['product_id'],
                    'sku' => $batch['sku'],
                    'product_name' => $batch['product_name'],
                    'qty' => $batch['qty'],
                    'source' => $batch['source'],
                    'age_days' => $ageDays,
                    'bucket' => $this->agingBucket($ageDays),
                ];
            })
            ->sortByDesc('age_days')
            ->values();
    }

    private function agingBucket($days)
    {
        if ($days <= 30) {
            return '0-30';
        }

        if ($days <= 60) {
            return '31-60';
        }

        if ($days <= 90) {
            return '61-90';
        }

        return '90+';
    }

    private function normalizeProductName($name)
    {
        return strtolower(trim((string) $name));
    }

    private function buildInventoryStockLevelReport(Request $request)
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->as_of)->endOfDay()
            : Carbon::today()->endOfDay();
        $lowStockThreshold = max(1, min(9999, (int) $request->input('low_stock', 10)));

        $distributorQuery = AreaDistributor::with([
            'userAds:id,name,role',
            'areas:id,ad_id,area_name',
        ])->whereHas('userAds', function ($query) {
            $query->where('role', 'Area Distributor');
        });

        if ($request->filled('region')) {
            $distributorQuery->where('location_region', $request->region);
        }

        if ($request->filled('status')) {
            $distributorQuery->where('status', $request->status);
        }

        if ($request->filled('distributor')) {
            $search = trim((string) $request->distributor);
            $distributorQuery->where(function ($query) use ($search) {
                $query->where('store_code', 'like', '%' . $search . '%')
                    ->orWhere('ad_reference', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhereHas('userAds', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $distributors = $distributorQuery
            ->orderBy('location_region')
            ->orderBy('business_name')
            ->get();

        $adUserIds = $distributors->pluck('user_id')->filter()->map(function ($id) {
            return (int) $id;
        })->values();
        $adIds = $distributors->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values();
        $adUserByAdId = $distributors->mapWithKeys(function ($distributor) {
            return [(int) $distributor->id => (int) $distributor->user_id];
        });

        $purchaseRows = collect();
        $movementRows = collect();
        $orderRows = collect();

        if ($adUserIds->isNotEmpty()) {
            $purchaseRows = AdPurchaseOrderItem::join(
                    'ad_purchase_orders',
                    'ad_purchase_order_items.ad_purchase_order_id',
                    '=',
                    'ad_purchase_orders.id'
                )
                ->select(
                    'ad_purchase_orders.ad_user_id',
                    'ad_purchase_order_items.product_id',
                    'ad_purchase_order_items.sku',
                    'ad_purchase_order_items.product_name',
                    DB::raw('SUM(ad_purchase_order_items.qty) as qty')
                )
                ->whereIn('ad_purchase_orders.ad_user_id', $adUserIds)
                ->where('ad_purchase_orders.status', 'Completed')
                ->where(function ($query) use ($asOf) {
                    $query->whereDate('ad_purchase_orders.submitted_at', '<=', $asOf->toDateString())
                        ->orWhere(function ($inner) use ($asOf) {
                            $inner->whereNull('ad_purchase_orders.submitted_at')
                                ->whereDate('ad_purchase_orders.created_at', '<=', $asOf->toDateString());
                        });
                })
                ->groupBy(
                    'ad_purchase_orders.ad_user_id',
                    'ad_purchase_order_items.product_id',
                    'ad_purchase_order_items.sku',
                    'ad_purchase_order_items.product_name'
                )
                ->get();

            $movementRows = InventoryTransfer::select(
                    'ad_user_id',
                    'product_id',
                    'sku',
                    'item_name',
                    'movement_type',
                    DB::raw('SUM(qty) as qty')
                )
                ->whereIn('ad_user_id', $adUserIds)
                ->whereIn('movement_type', ['in', 'out'])
                ->where(function ($query) use ($asOf) {
                    $query->whereDate('transfer_date', '<=', $asOf->toDateString())
                        ->orWhere(function ($inner) use ($asOf) {
                            $inner->whereNull('transfer_date')
                                ->whereDate('created_at', '<=', $asOf->toDateString());
                        });
                })
                ->groupBy('ad_user_id', 'product_id', 'sku', 'item_name', 'movement_type')
                ->get();
        }

        if ($adIds->isNotEmpty()) {
            $hasOrderProductId = Schema::hasColumn('order_details', 'product_id');
            $orderSelects = [
                'order_details.ad_id',
                'order_details.item',
                DB::raw('SUM(order_details.qty) as qty'),
            ];

            if ($hasOrderProductId) {
                $orderSelects[] = 'order_details.product_id';
            }

            $orderQuery = OrderDetail::select($orderSelects)
                ->whereIn('order_details.ad_id', $adIds)
                ->where('order_details.status', 'Completed')
                ->whereDate('order_details.date', '<=', $asOf->toDateString())
                ->groupBy('order_details.ad_id', 'order_details.item');

            if ($hasOrderProductId) {
                $orderQuery->groupBy('order_details.product_id');
            }

            $orderRows = $orderQuery->get();
        }

        $sourceProductIds = $purchaseRows->pluck('product_id')
            ->merge($movementRows->pluck('product_id'))
            ->merge($orderRows->pluck('product_id'))
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        $catalogProducts = collect();

        if ($adUserIds->isNotEmpty() || $sourceProductIds->isNotEmpty()) {
            $catalogProducts = Product::query()
                ->where(function ($query) use ($adUserIds, $sourceProductIds) {
                    if ($adUserIds->isNotEmpty()) {
                        $query->whereIn('ad_user_id', $adUserIds);
                    }

                    if ($sourceProductIds->isNotEmpty()) {
                        $adUserIds->isNotEmpty()
                            ? $query->orWhereIn('id', $sourceProductIds)
                            : $query->whereIn('id', $sourceProductIds);
                    }
                })
                ->where('status', 'Activate')
                ->orderBy('product_name')
                ->get(['id', 'sku', 'product_name']);
        }

        $productsByKey = collect();
        $productKeyById = collect();
        $productKeyByName = collect();

        $registerProduct = function ($productId, $sku, $name) use (&$productsByKey, &$productKeyById, &$productKeyByName) {
            $name = trim((string) $name);
            $sku = trim((string) $sku);

            if ($name === '') {
                return null;
            }

            $normalizedName = $this->normalizeProductName($name);
            $key = strtolower($sku !== '' ? 'sku:' . $sku : 'name:' . $normalizedName);

            if (!$productsByKey->has($key)) {
                $productsByKey->put($key, (object) [
                    'key' => $key,
                    'sku' => $sku,
                    'product_name' => $name,
                ]);
            }

            if ($productId) {
                $productKeyById->put((int) $productId, $key);
            }

            if (!$productKeyByName->has($normalizedName)) {
                $productKeyByName->put($normalizedName, $key);
            }

            return $key;
        };

        foreach ($catalogProducts as $product) {
            $registerProduct($product->id, $product->sku, $product->product_name);
        }

        foreach ($purchaseRows as $row) {
            $registerProduct($row->product_id, $row->sku, $row->product_name);
        }

        foreach ($movementRows as $row) {
            $registerProduct($row->product_id, $row->sku, $row->item_name);
        }

        $hasOrderProductId = Schema::hasColumn('order_details', 'product_id');

        foreach ($orderRows as $row) {
            $registerProduct(
                $hasOrderProductId ? $row->product_id : null,
                null,
                $row->item
            );
        }

        $resolveProductKey = function ($productId, $name) use ($productKeyById, $productKeyByName, $registerProduct) {
            if ($productId && $productKeyById->has((int) $productId)) {
                return $productKeyById->get((int) $productId);
            }

            $normalizedName = $this->normalizeProductName($name);

            return $productKeyByName->get($normalizedName)
                ?: $registerProduct($productId, null, $name);
        };

        $stockByDistributor = $adUserIds->mapWithKeys(function ($userId) {
            return [(int) $userId => collect()];
        });

        $applyStock = function ($adUserId, $productKey, $qty) use (&$stockByDistributor) {
            $adUserId = (int) $adUserId;

            if (!$productKey || !$stockByDistributor->has($adUserId)) {
                return;
            }

            $stock = $stockByDistributor->get($adUserId);
            $stock->put($productKey, (float) $stock->get($productKey, 0) + (float) $qty);
        };

        foreach ($purchaseRows as $row) {
            $applyStock(
                $row->ad_user_id,
                $resolveProductKey($row->product_id, $row->product_name),
                $row->qty
            );
        }

        foreach ($movementRows as $row) {
            $applyStock(
                $row->ad_user_id,
                $resolveProductKey($row->product_id, $row->item_name),
                $row->movement_type === 'out' ? -1 * (float) $row->qty : (float) $row->qty
            );
        }

        foreach ($orderRows as $row) {
            $adUserId = $adUserByAdId->get((int) $row->ad_id);
            $applyStock(
                $adUserId,
                $resolveProductKey(
                    $hasOrderProductId ? $row->product_id : null,
                    $row->item
                ),
                -1 * (float) $row->qty
            );
        }

        $products = $productsByKey
            ->when($request->filled('product'), function ($items) use ($request) {
                $search = $this->normalizeProductName($request->product);

                return $items->filter(function ($product) use ($search) {
                    return strpos($this->normalizeProductName($product->product_name), $search) !== false
                        || strpos($this->normalizeProductName($product->sku), $search) !== false;
                });
            })
            ->sortBy(function ($product) {
                return $this->normalizeProductName(($product->sku ?: 'zzzz') . ' ' . $product->product_name);
            })
            ->values();

        $visibleProductKeys = $products->pluck('key');
        $rows = $distributors->map(function ($distributor) use ($stockByDistributor, $visibleProductKeys) {
            $stock = $stockByDistributor->get((int) $distributor->user_id, collect())
                ->only($visibleProductKeys->all())
                ->map(function ($qty) {
                    return max(0, (float) $qty);
                });
            $areas = $distributor->areas->pluck('area_name')->filter()->unique()->sort()->values();

            return (object) [
                'region' => $this->formatRegionCode($distributor->location_region),
                'region_name' => $distributor->location_region ?: 'Unassigned',
                'distributor_id' => $distributor->store_code ?: $distributor->ad_reference ?: 'AD-' . $distributor->id,
                'territories' => $areas,
                'business_name' => $distributor->business_name ?: $distributor->name ?: optional($distributor->userAds)->name,
                'customer_type' => optional($distributor->userAds)->role ?: 'Area Distributor',
                'status' => $distributor->status ?: 'Unknown',
                'stock' => $stock,
                'total_stock' => $stock->sum(),
            ];
        });

        if ($request->filled('availability')) {
            $availability = $request->availability;
            $rows = $rows->filter(function ($row) use ($availability, $lowStockThreshold) {
                if ($availability === 'in_stock') {
                    return $row->total_stock > $lowStockThreshold;
                }

                if ($availability === 'low_stock') {
                    return $row->total_stock > 0 && $row->total_stock <= $lowStockThreshold;
                }

                if ($availability === 'out_of_stock') {
                    return $row->total_stock <= 0;
                }

                return true;
            })->values();
        }

        $regions = AreaDistributor::whereNotNull('location_region')
            ->where('location_region', '<>', '')
            ->distinct()
            ->orderBy('location_region')
            ->pluck('location_region');
        $statuses = AreaDistributor::whereNotNull('status')
            ->where('status', '<>', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $summary = (object) [
            'distributors' => $rows->count(),
            'products' => $products->count(),
            'total_stock' => $rows->sum('total_stock'),
            'low_stock_cells' => $rows->sum(function ($row) use ($products, $lowStockThreshold) {
                return $products->filter(function ($product) use ($row, $lowStockThreshold) {
                    $qty = (float) $row->stock->get($product->key, 0);
                    return $qty > 0 && $qty <= $lowStockThreshold;
                })->count();
            }),
            'out_of_stock_cells' => $rows->sum(function ($row) use ($products) {
                return $products->filter(function ($product) use ($row) {
                    return (float) $row->stock->get($product->key, 0) <= 0;
                })->count();
            }),
        ];

        return compact(
            'rows',
            'products',
            'regions',
            'statuses',
            'summary',
            'asOf',
            'lowStockThreshold'
        );
    }

    private function formatRegionCode($region)
    {
        $region = trim((string) $region);

        if ($region === '') {
            return '—';
        }

        if (preg_match('/region\s*([0-9]+)/i', $region, $matches)) {
            return 'R' . $matches[1];
        }

        $romanNumbers = [
            'XVII' => 17, 'XVI' => 16, 'XV' => 15, 'XIV' => 14, 'XIII' => 13,
            'XII' => 12, 'XI' => 11, 'X' => 10, 'IX' => 9, 'VIII' => 8,
            'VII' => 7, 'VI' => 6, 'V' => 5, 'IV' => 4, 'III' => 3, 'II' => 2, 'I' => 1,
        ];

        foreach ($romanNumbers as $roman => $number) {
            if (preg_match('/region[\s-]*' . $roman . '\b/i', $region)) {
                return 'R' . $number;
            }
        }

        return strtoupper($region);
    }

    private function buildMonthlySalesReport(Request $request)
    {
        try {
            $period = $request->filled('month')
                ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $exception) {
            $period = Carbon::now()->startOfMonth();
        }
        $from = $period->copy()->startOfMonth();
        $to = $period->copy()->endOfMonth();

        $distributorQuery = AreaDistributor::with([
            'userAds:id,name,role',
            'areas:id,ad_id,project_type,area_name',
        ])->whereHas('userAds', function ($query) {
            $query->where('role', 'Area Distributor');
        });

        if ($request->filled('region')) {
            $distributorQuery->where('location_region', $request->region);
        }

        if ($request->filled('project')) {
            $distributorQuery->whereHas('areas', function ($query) use ($request) {
                $query->where('project_type', $request->project);
            });
        }

        if ($request->filled('distributor')) {
            $search = trim((string) $request->distributor);
            $distributorQuery->where(function ($query) use ($search) {
                $query->where('store_code', 'like', '%' . $search . '%')
                    ->orWhere('ad_reference', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhereHas('userAds', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $distributors = $distributorQuery
            ->orderBy('location_region')
            ->orderBy('business_name')
            ->get();
        $distributorIds = $distributors->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values();

        $sales = collect();
        if ($distributorIds->isNotEmpty()) {
            $sales = OrderDetail::whereIn('ad_id', $distributorIds)
                ->where('status', 'Completed')
                ->whereBetween(DB::raw('DATE(COALESCE(completed_at, date, created_at))'), [
                    $from->toDateString(),
                    $to->toDateString(),
                ])
                ->get([
                    'id',
                    'transaction_id',
                    'ad_id',
                    'item',
                    'sku',
                    'qty',
                    'price',
                    'payment_method',
                    'delivery_fee',
                    'completed_at',
                    'date',
                    'created_at',
                ]);
        }

        $catalogProducts = Product::where('status', 'Activate')
            ->whereIn('ad_user_id', $distributors->pluck('user_id')->filter())
            ->orderBy('sku')
            ->orderBy('product_name')
            ->get(['sku', 'product_name']);
        $productsByKey = collect();
        $productKeyByName = collect();

        $registerProduct = function ($sku, $name) use (&$productsByKey, &$productKeyByName) {
            $sku = trim((string) $sku);
            $name = trim((string) $name);

            if ($name === '') {
                return null;
            }

            $normalizedName = $this->normalizeProductName($name);
            $key = $productKeyByName->get($normalizedName)
                ?: ($sku !== '' ? 'sku:' . strtolower($sku) : 'name:' . $normalizedName);

            if (!$productsByKey->has($key)) {
                $productsByKey->put($key, (object) [
                    'key' => $key,
                    'sku' => $sku,
                    'product_name' => $name,
                ]);
            }

            $productKeyByName->put($normalizedName, $key);

            return $key;
        };

        foreach ($catalogProducts as $product) {
            $registerProduct($product->sku, $product->product_name);
        }

        foreach ($sales as $sale) {
            $registerProduct($sale->sku, $sale->item);
        }

        $products = $productsByKey
            ->when($request->filled('product'), function ($items) use ($request) {
                $search = $this->normalizeProductName($request->product);

                return $items->filter(function ($product) use ($search) {
                    return strpos($this->normalizeProductName($product->sku), $search) !== false
                        || strpos($this->normalizeProductName($product->product_name), $search) !== false;
                });
            })
            ->sortBy(function ($product) {
                return $this->normalizeProductName(($product->sku ?: 'zzzz') . ' ' . $product->product_name);
            })
            ->values();
        $visibleProductKeys = $products->pluck('key');

        $paymentColumns = collect([
            'cash' => 'Cash',
            'bdo' => 'BDO',
            'pnb' => 'PNB',
            'gcash' => 'GCash',
            'palawan_pay' => 'Palawan Pay',
            'cebuana_pay' => 'Cebuana Pay',
            'online_transfer' => 'Online Transfer',
            'online_transfer_bank' => 'Online Transfer - Bank',
            'check' => 'Check',
            'issuing_bank' => 'Issuing Bank',
            'ar_peddler' => 'AR-Peddler',
            'on_credit' => 'On Credit',
            'voucher' => 'Voucher',
            'other' => 'Other',
        ]);

        $salesByDistributor = $sales->groupBy('ad_id');
        $rows = $distributors->map(function ($distributor) use (
            $salesByDistributor,
            $registerProduct,
            $visibleProductKeys,
            $paymentColumns
        ) {
            $productTotals = $visibleProductKeys->mapWithKeys(function ($key) {
                return [$key => (object) ['qty' => 0.0, 'amount' => 0.0]];
            });
            $paymentTotals = $paymentColumns->mapWithKeys(function ($label, $key) {
                return [$key => 0.0];
            });
            $transactions = collect();

            foreach ($salesByDistributor->get($distributor->id, collect()) as $sale) {
                $productKey = $registerProduct($sale->sku, $sale->item);
                $amount = (float) $sale->qty * (float) $sale->price;

                if (!$productKey || !$productTotals->has($productKey)) {
                    continue;
                }

                $total = $productTotals->get($productKey);
                $total->qty += (float) $sale->qty;
                $total->amount += $amount;

                $paymentKey = $this->monthlySalesPaymentKey($sale->payment_method);
                $paymentTotals->put(
                    $paymentKey,
                    (float) $paymentTotals->get($paymentKey, 0) + $amount
                );
                $transactions->push($sale->transaction_id ?: 'ROW-' . $sale->id);
            }

            $projects = $distributor->areas->pluck('project_type')
                ->filter()
                ->map(function ($project) {
                    return $this->shortProjectName($project);
                })
                ->unique()
                ->sort()
                ->values();
            $totalAmount = $productTotals->sum(function ($total) {
                return $total->amount;
            });

            return (object) [
                'region' => $this->formatRegionCode($distributor->location_region),
                'region_name' => $distributor->location_region ?: 'Unassigned',
                'distributor_id' => $distributor->store_code ?: $distributor->ad_reference ?: 'AD-' . $distributor->id,
                'business_name' => $distributor->business_name ?: $distributor->name ?: optional($distributor->userAds)->name,
                'customer_type' => optional($distributor->userAds)->role ?: 'Area Distributor',
                'projects' => $projects,
                'product_totals' => $productTotals,
                'payment_totals' => $paymentTotals,
                'total_amount' => $totalAmount,
                'total_qty' => $productTotals->sum(function ($total) {
                    return $total->qty;
                }),
                'transaction_count' => $transactions->filter()->unique()->count(),
            ];
        });

        if ($request->input('sales_status') === 'with_sales') {
            $rows = $rows->where('total_amount', '>', 0)->values();
        } elseif ($request->input('sales_status') === 'no_sales') {
            $rows = $rows->where('total_amount', '<=', 0)->values();
        }

        $regions = AreaDistributor::whereNotNull('location_region')
            ->where('location_region', '<>', '')
            ->distinct()
            ->orderBy('location_region')
            ->pluck('location_region');
        $projects = DB::table('ad_areas')
            ->whereNull('deleted_at')
            ->whereNotNull('project_type')
            ->where('project_type', '<>', '')
            ->distinct()
            ->orderBy('project_type')
            ->pluck('project_type');
        $summary = (object) [
            'total_sales' => $rows->sum('total_amount'),
            'total_qty' => $rows->sum('total_qty'),
            'transactions' => $rows->sum('transaction_count'),
            'active_distributors' => $rows->where('total_amount', '>', 0)->count(),
        ];
        $productGrandTotals = $products->mapWithKeys(function ($product) use ($rows) {
            return [$product->key => (object) [
                'qty' => $rows->sum(function ($row) use ($product) {
                    return $row->product_totals->get($product->key)->qty;
                }),
                'amount' => $rows->sum(function ($row) use ($product) {
                    return $row->product_totals->get($product->key)->amount;
                }),
            ]];
        });
        $paymentGrandTotals = $paymentColumns->mapWithKeys(function ($label, $key) use ($rows) {
            return [$key => $rows->sum(function ($row) use ($key) {
                return $row->payment_totals->get($key, 0);
            })];
        });

        return compact(
            'rows',
            'products',
            'paymentColumns',
            'regions',
            'projects',
            'summary',
            'productGrandTotals',
            'paymentGrandTotals',
            'period',
            'from',
            'to'
        );
    }

    private function monthlySalesPaymentKey($paymentMethod)
    {
        $payment = strtolower(trim(str_replace([' ', '-'], '_', (string) $paymentMethod)));

        $aliases = [
            'cash' => 'cash',
            'bdo' => 'bdo',
            'bdo_unibank' => 'bdo',
            'pnb' => 'pnb',
            'philippine_national_bank' => 'pnb',
            'gcash' => 'gcash',
            'palawan' => 'palawan_pay',
            'palawan_pay' => 'palawan_pay',
            'cebuana' => 'cebuana_pay',
            'cebuana_pay' => 'cebuana_pay',
            'bank_transfer' => 'online_transfer',
            'online_transfer' => 'online_transfer',
            'online_transfer_bank' => 'online_transfer_bank',
            'check' => 'check',
            'cheque' => 'check',
            'issuing_bank' => 'issuing_bank',
            'ar_peddler' => 'ar_peddler',
            'credit' => 'on_credit',
            'on_credit' => 'on_credit',
            'voucher' => 'voucher',
        ];

        return $aliases[$payment] ?? 'other';
    }

    private function shortProjectName($project)
    {
        $project = trim((string) $project);
        $normalized = strtolower($project);

        if (strpos($normalized, 'rise') !== false) {
            return 'PR';
        }

        if (strpos($normalized, 'genesis') !== false) {
            return 'PG';
        }

        return strtoupper($project);
    }

    private function buildVoucherHistoryReport(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfYear();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $voucherQuery = \App\Voucher::withTrashed();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $voucherQuery->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('distributor')) {
            $voucherQuery->where('name', $request->distributor);
        }

        $vouchers = $voucherQuery->orderBy('created_at', 'desc')->get();
        $voucherIds = $vouchers->pluck('id');
        $voucherCodes = $vouchers->pluck('code')->filter()->unique()->values();

        $orders = collect();
        if ($voucherIds->isNotEmpty() || $voucherCodes->isNotEmpty()) {
            $orders = AdPurchaseOrder::with(['ad', 'creator'])
                ->where(function ($query) use ($voucherIds, $voucherCodes) {
                    if ($voucherIds->isNotEmpty()) {
                        $query->whereIn('voucher_id', $voucherIds);
                    }

                    if ($voucherCodes->isNotEmpty()) {
                        $voucherIds->isNotEmpty()
                            ? $query->orWhereIn('voucher_code', $voucherCodes)
                            : $query->whereIn('voucher_code', $voucherCodes);
                    }
                })
                ->whereBetween(DB::raw('COALESCE(submitted_at, created_at)'), [$from, $to])
                ->when($request->filled('order_status'), function ($query) use ($request) {
                    $query->where('status', $request->order_status);
                })
                ->orderByDesc('id')
                ->get();
        }

        $auditRows = $voucherIds->isEmpty()
            ? collect()
            : DB::table('audits')
                ->where('auditable_type', 'App\\Voucher')
                ->whereIn('auditable_id', $voucherIds)
                ->whereBetween('created_at', [$from, $to])
                ->when($request->filled('event'), function ($query) use ($request) {
                    $query->where('event', $request->event);
                })
                ->orderByDesc('created_at')
                ->get();
        $auditUsers = \App\User::whereIn('id', $auditRows->pluck('user_id')->filter()->unique())
            ->pluck('name', 'id');
        $ordersByVoucherId = $orders->filter(function ($order) {
            return $order->voucher_id !== null;
        })->groupBy('voucher_id');
        $ordersByVoucherCode = $orders->filter(function ($order) {
            return $order->voucher_id === null;
        })->groupBy(function ($order) {
            return strtoupper(trim((string) $order->voucher_code));
        });
        $auditsByVoucher = $auditRows->groupBy('auditable_id');

        $rows = $vouchers->map(function ($voucher) use (
            $ordersByVoucherId,
            $ordersByVoucherCode,
            $auditsByVoucher,
            $auditUsers
        ) {
            $voucherOrders = $ordersByVoucherId->get($voucher->id, collect())
                ->merge($ordersByVoucherCode->get(strtoupper($voucher->code), collect()))
                ->unique('id')
                ->values();
            $voucherAudits = $auditsByVoucher->get($voucher->id, collect());
            $timeline = collect();

            foreach ($voucherOrders as $order) {
                $timeline->push([
                    'type' => 'usage',
                    'date' => optional($order->submitted_at ?: $order->created_at)->format('M d, Y h:i A'),
                    'timestamp' => optional($order->submitted_at ?: $order->created_at)->timestamp ?: 0,
                    'title' => 'Used on ' . ($order->po_number ?: 'purchase order'),
                    'description' => ($order->business_name ?: optional($order->ad)->business_name ?: 'Area Distributor')
                        . ' · ' . ($order->authorized_territory ?: 'No territory')
                        . ' · Rebate PHP ' . number_format((float) $order->rebate_amount, 2),
                    'status' => $order->status,
                    'url' => route('ad-purchase-orders.show', $order->id),
                    'actor' => optional($order->creator)->name,
                    'rebate_amount' => (float) $order->rebate_amount,
                ]);
            }

            foreach ($voucherAudits as $audit) {
                $oldValues = json_decode((string) $audit->old_values, true) ?: [];
                $newValues = json_decode((string) $audit->new_values, true) ?: [];
                $changes = collect(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))))
                    ->reject(function ($field) {
                        return in_array($field, ['updated_at', 'created_at', 'deleted_at'], true);
                    })
                    ->map(function ($field) use ($oldValues, $newValues) {
                        return [
                            'field' => $this->voucherHistoryFieldLabel($field),
                            'old' => $this->voucherHistoryValue($field, $oldValues[$field] ?? null),
                            'new' => $this->voucherHistoryValue($field, $newValues[$field] ?? null),
                        ];
                    })
                    ->values();

                $timeline->push([
                    'type' => 'audit',
                    'date' => Carbon::parse($audit->created_at)->format('M d, Y h:i A'),
                    'timestamp' => Carbon::parse($audit->created_at)->timestamp,
                    'title' => ucfirst($audit->event) . ' voucher',
                    'description' => $changes->isNotEmpty()
                        ? $changes->pluck('field')->implode(', ') . ' changed'
                        : 'Voucher record ' . $audit->event,
                    'status' => ucfirst($audit->event),
                    'url' => null,
                    'actor' => $auditUsers->get($audit->user_id) ?: 'System',
                    'changes' => $changes,
                    'rebate_amount' => 0,
                ]);
            }

            $usageLimit = (int) ($voucher->usage_limit ?: 0);
            $lifetimeUsed = (int) ($voucher->used_count ?: 0);
            $status = $voucher->trashed() ? 'Deleted' : $voucher->statusLabel();

            return (object) [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'distributor' => $voucher->name,
                'areas' => collect($voucher->area_names ?? [])->filter()->values(),
                'description' => $voucher->description,
                'discount_label' => $voucher->discount_type === 'percent'
                    ? number_format((float) $voucher->discount_value, 2) . '%'
                    : 'PHP ' . number_format((float) $voucher->discount_value, 2),
                'minimum_order_amount' => (float) $voucher->minimum_order_amount,
                'period_used' => $voucherOrders->count(),
                'lifetime_used' => $lifetimeUsed,
                'usage_limit' => $usageLimit ?: null,
                'remaining_uses' => $usageLimit > 0 ? max(0, $usageLimit - $lifetimeUsed) : null,
                'rebate_total' => (float) $voucherOrders->sum('rebate_amount'),
                'order_total' => (float) $voucherOrders->sum('total_amount'),
                'status' => $status,
                'starts_at' => optional($voucher->starts_at)->format('M d, Y'),
                'expires_at' => optional($voucher->expires_at)->format('M d, Y'),
                'created_at' => optional($voucher->created_at)->format('M d, Y'),
                'timeline' => $timeline->sortByDesc('timestamp')->values(),
                'event_count' => $timeline->count(),
            ];
        });

        if ($request->filled('status')) {
            $rows = $rows->filter(function ($row) use ($request) {
                return strtolower($row->status) === strtolower($request->status);
            })->values();
        }

        if ($request->input('usage') === 'used') {
            $rows = $rows->where('period_used', '>', 0)->values();
        } elseif ($request->input('usage') === 'unused') {
            $rows = $rows->where('period_used', '<=', 0)->values();
        }

        $historyRows = collect();
        foreach ($rows as $row) {
            foreach ($row->timeline as $event) {
                $historyRows->push((object) [
                    'date' => $event['date'],
                    'voucher_code' => $row->code,
                    'distributor' => $row->distributor,
                    'event_type' => $event['type'] === 'usage' ? 'Usage' : 'Audit',
                    'event' => $event['title'],
                    'details' => $event['description'],
                    'actor' => $event['actor'] ?: 'System',
                    'status' => $event['status'],
                    'rebate_total' => (float) ($event['rebate_amount'] ?? 0),
                ]);
            }
        }

        $distributors = \App\Voucher::withTrashed()
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $statuses = collect(['Active', 'Inactive', 'Scheduled', 'Expired', 'Used Up', 'Deleted']);
        $orderStatuses = AdPurchaseOrder::whereNotNull('status')
            ->where('status', '<>', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');
        $summary = (object) [
            'vouchers' => $rows->count(),
            'used_vouchers' => $rows->where('period_used', '>', 0)->count(),
            'usage_events' => $rows->sum('period_used'),
            'rebate_total' => $rows->sum('rebate_total'),
            'audit_events' => $rows->sum(function ($row) {
                return $row->timeline->where('type', 'audit')->count();
            }),
        ];

        return compact(
            'rows',
            'historyRows',
            'distributors',
            'statuses',
            'orderStatuses',
            'summary',
            'from',
            'to'
        );
    }

    private function voucherHistoryFieldLabel($field)
    {
        return ucwords(str_replace('_', ' ', (string) $field));
    }

    private function voucherHistoryValue($field, $value)
    {
        if ($field === 'is_active') {
            return $value ? 'Active' : 'Inactive';
        }

        if ($field === 'area_names') {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            return collect(is_array($decoded) ? $decoded : [$value])->filter()->implode(', ') ?: '—';
        }

        if (in_array($field, ['discount_value', 'minimum_order_amount'], true) && $value !== null) {
            return number_format((float) $value, 2);
        }

        if (is_array($value)) {
            return collect($value)->implode(', ');
        }

        return $value === null || $value === '' ? '—' : (string) $value;
    }
}
