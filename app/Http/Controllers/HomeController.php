<?php

namespace App\Http\Controllers;
use App\Transaction;
use App\Dealer;
use App\OrderDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Client;
use Illuminate\Support\Collection;
use App\TransactionDetail;
use Illuminate\Http\Request;
use App\Product;
use App\InventoryTransfer;
use App\User;
use App\RedeemedHistory;
use App\AdPurchaseOrderItem;
use App\AdPurchaseOrder;
use App\Voucher;

class HomeController extends Controller
{
    private $crmConnections = ['admin_crms', 'admin_crms2'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $dealer = "";
        $customer = "";
        $threeDaysAgo = Carbon::now()->subDays(7)->toDateString();
        
        $selectedYear = $request->get('year', Carbon::now()->year);
        $selectedMonth = $request->get('month', null);
        $viewType = $selectedMonth ? 'monthly' : 'yearly';

        // $adUser = auth()->user()->ad->id;
        $adUser = optional(auth()->user()->ad)->id;
        $pendingOrdersCount = OrderDetail::where('ad_id', $adUser)
            ->where('status', 'Pending')
            ->count();
            
        $customers_less = Client::where('status', 'Active')->whereDoesntHave('latestTransaction', function ($q) use ($threeDaysAgo) {
            $q->where('date', '>=', $threeDaysAgo);
        })
        ->whereHas('latestTransaction')
        ->orderBy(
            DB::raw('(SELECT date FROM transaction_details WHERE transaction_details.client_id = clients.id ORDER BY date DESC LIMIT 1)'),
            'desc'
        )
        ->get();

        $customers = Client::whereHas('transactions')->get();
        $transactions = Transaction::orderBy('id','desc')->get();
        $dealers = Dealer::get();
        $transactions_details = TransactionDetail::orderBy('id','desc')->get();

        if(auth()->user()->role == "Dealer")
        {
            $dealer = Dealer::with('sales')->where('user_id',auth()->user()->id)->first();
            $transactions_details = TransactionDetail::where('dealer_id',auth()->user()->id)->orderBy('id','desc')->get();
            $total_sales = TransactionDetail::where('dealer_id',auth()->user()->id)->sum('price');

            $totalEarnedPointsDealer = $dealer->sales->sum('points_dealer');
            $redeemedPointsDealer = abs(RedeemedHistory::where('user_id', auth()->user()->id)->sum('points_amount'));
            $dealerAvailablePoints = $totalEarnedPointsDealer - $redeemedPointsDealer;
        }
        if(auth()->user()->role == "Client")
        {
            $customer = Client::where('user_id',auth()->user()->id)->first();
            $transactions_details = TransactionDetail::where('client_id',$customer->id)->orderBy('id','desc')->get();
            $total_sales = TransactionDetail::where('client_id',$customer->id)->sum('price');

            $totalEarnedPointsCustomer = $customer->transactions->sum('points_client');
            $redeemedPointsCustomer = abs(RedeemedHistory::where('user_id', auth()->user()->id)->sum('points_amount'));
            $customerAvailablePoints = $totalEarnedPointsCustomer - $redeemedPointsCustomer;
        }

        $total_sales = TransactionDetail::get()->sum(function($transaction) {
            return $transaction->price * $transaction->qty;
        });

        // Get chart data based on view type
        if ($viewType === 'monthly') {
            $chartData = $this->getDailyData($selectedYear, $selectedMonth);
        } else {
            $chartData = $this->getMonthlyData($selectedYear);
        }
        
        $categories = $chartData['categories'];
        $qty = $chartData['qty'];

        // Get available years and months for dropdowns
        $availableYears = $this->getAvailableYears();
        $availableMonths = $this->getAvailableMonths($selectedYear);

        $dealers = TransactionDetail::select(
            'dealer_id',
            DB::raw('SUM(points_dealer) as total_points'),
            DB::raw('MAX(date) as latest_transaction')
        )
        ->with('dealer')
        ->groupBy('dealer_id')
        ->orderByDesc('total_points')
        ->get();

        $top_customers = TransactionDetail::select(
            'client_id',
            DB::raw('SUM(points_client) as total_points'),
            DB::raw('MAX(created_at) as latest_transaction')
        )
        ->with('customer')
        ->whereNotNull('client_id')
        ->groupBy('client_id')
        ->orderByDesc('total_points')
        ->limit(10)
        ->get();

        $salesTrend = $this->calculateSalesTrend();
        $qtyTrend = $this->calculateQtyTrend();

        $threeDaysAgo = Carbon::now()->subDays(3)->toDateString();

        $dealers_inactive = Dealer::whereDoesntHave('sales', function ($q) use ($threeDaysAgo) {
            $q->where('created_at', '>=', $threeDaysAgo);
        })
        ->whereHas('sales')
        ->get()
        ->map(function($dealer) {
            $lastTransaction = TransactionDetail::where('dealer_id', $dealer->user_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $dealer->last_transaction_date = $lastTransaction ? $lastTransaction->created_at : null;
            $dealer->days_since_transaction = $lastTransaction 
                ? \Carbon\Carbon::parse($lastTransaction->created_at)->diffInDays(\Carbon\Carbon::now()) 
                : null;
            return $dealer;
        })
        ->sortByDesc('days_since_transaction');

        $mapData = $this->getPhilippineMapData();

        return view('home',
            array(
                'transactions' => $transactions,
                'transactions_details' => $transactions_details,
                'dealers' => $dealers,
                'categories' =>  $categories,
                'qty' =>  $qty,
                'customers' =>  $customers,
                'dealer' =>  $dealer,
                'customer' =>  $customer,
                'customers_less' =>  $customers_less,
                'total_sales' => $total_sales,
                'top_customers' => $top_customers,
                'sales_trend' => $salesTrend,
                'qty_trend' => $qtyTrend,
                'available_years' => $availableYears,
                'available_months' => $availableMonths,
                'selected_year' => $selectedYear,
                'selected_month' => $selectedMonth,
                'view_type' => $viewType,
                'dealer_available_points' => $dealerAvailablePoints ?? 0,
                'customer_available_points' => $customerAvailablePoints ?? 0,
                'dealers_inactive' => $dealers_inactive,
                'map_data' => $mapData,
                'pendingOrdersCount' => $pendingOrdersCount
            )
        );
    }

    public function liveOverview(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $source = $request->get('source', 'regular');
        $connection = $this->dashboardConnectionForSource($source);
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $thirtyDaysAgo = Carbon::today()->subDays(29);

        $hasTransactionsTable = $this->dashboardHasTable($connection, 'transaction_details');
        $hasOrdersTable = $this->dashboardHasTable($connection, 'order_details');
        $hasAdOrdersTable = $this->dashboardHasTable($connection, 'ad_purchase_orders');
        $transactionDateColumn = $this->dashboardTransactionDateColumn($connection);
        $transactionAmountExpression = $this->dashboardTransactionAmountExpression($connection);
        $transactionQtyExpression = $this->dashboardTransactionQtyExpression($connection);

        $todaySalesRow = $hasTransactionsTable && $transactionDateColumn
            ? $this->dashboardQuery($connection, 'transaction_details')->whereDate($transactionDateColumn, $today)
                ->selectRaw('COALESCE(SUM(' . $transactionAmountExpression . '), 0) as total')
                ->first()
            : null;
        $monthSalesRow = $hasTransactionsTable && $transactionDateColumn
            ? $this->dashboardQuery($connection, 'transaction_details')->where($transactionDateColumn, '>=', $monthStart)
                ->selectRaw('COALESCE(SUM(' . $transactionAmountExpression . '), 0) as total')
                ->first()
            : null;
        $todaySales = (float) optional($todaySalesRow)->total;
        $monthSales = (float) optional($monthSalesRow)->total;

        $todayTransactions = $hasTransactionsTable && $transactionDateColumn
            ? $this->dashboardQuery($connection, 'transaction_details')->whereDate($transactionDateColumn, $today)->count()
            : 0;
        $monthUnits = $hasTransactionsTable && $transactionDateColumn
            ? (float) optional($this->dashboardQuery($connection, 'transaction_details')
                ->where($transactionDateColumn, '>=', $monthStart)
                ->selectRaw('COALESCE(SUM(' . $transactionQtyExpression . '), 0) as total')
                ->first())->total
            : 0;
        $totalSalesRow = $hasTransactionsTable
            ? $this->dashboardQuery($connection, 'transaction_details')
                ->selectRaw('COALESCE(SUM(' . $transactionAmountExpression . '), 0) as total')
                ->first()
            : null;
        $totalSales = (float) optional($totalSalesRow)->total;
        $totalProductsSold = $hasTransactionsTable
            ? (float) optional($this->dashboardQuery($connection, 'transaction_details')
                ->selectRaw('COALESCE(SUM(' . $transactionQtyExpression . '), 0) as total')
                ->first())->total
            : 0;
        $activeDealers = $this->dashboardActiveDealers($connection);
        $activeCustomers = $this->dashboardActiveCustomers($connection);

        $pendingDealerOrders = $hasOrdersTable
            ? $this->dashboardQuery($connection, 'order_details')->where('status', 'Pending')->count()
            : 0;
        $pendingAdOrders = $hasAdOrdersTable
            ? $this->dashboardQuery($connection, 'ad_purchase_orders')->whereIn('status', ['Pending', 'For Verification'])->count()
            : 0;

        $dailyRows = $hasTransactionsTable && $transactionDateColumn
            ? $this->dashboardQuery($connection, 'transaction_details')->selectRaw(
                    'DATE(' . $transactionDateColumn . ') as sale_date, COALESCE(SUM(' . $transactionAmountExpression . '), 0) as sales, COALESCE(SUM(' . $transactionQtyExpression . '), 0) as units'
                )
                ->whereDate($transactionDateColumn, '>=', $thirtyDaysAgo)
                ->groupBy(DB::raw('DATE(' . $transactionDateColumn . ')'))
                ->orderBy('sale_date')
                ->get()
                ->keyBy('sale_date')
            : collect();

        $salesTrend = collect(range(0, 29))->map(function ($offset) use ($thirtyDaysAgo, $dailyRows) {
            $date = $thirtyDaysAgo->copy()->addDays($offset);
            $row = $dailyRows->get($date->toDateString());

            return [
                'date' => $date->format('M d'),
                'sales' => $row ? round((float) $row->sales, 2) : 0,
                'units' => $row ? (float) $row->units : 0,
            ];
        });

        $dealerOrderStatuses = $hasOrdersTable
            ? $this->dashboardQuery($connection, 'order_details')->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();
        $adOrderStatuses = $hasAdOrdersTable
            ? $this->dashboardQuery($connection, 'ad_purchase_orders')->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        $statusNames = $dealerOrderStatuses->keys()
            ->merge($adOrderStatuses->keys())
            ->filter()
            ->unique()
            ->values();

        $orderStatuses = $statusNames->map(function ($status) use ($dealerOrderStatuses, $adOrderStatuses) {
            return [
                'status' => $status,
                'total' => (int) ($dealerOrderStatuses->get($status, 0) + $adOrderStatuses->get($status, 0)),
            ];
        })->sortByDesc('total')->values();

        $recentTransactions = $this->dashboardRecentTransactions($connection);
        $selectedYear = $request->get('year', Carbon::now()->year);
        $selectedMonth = $request->get('month', null);
        $refillChart = $this->dashboardRefillChart($connection, $selectedYear, $selectedMonth);

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'source' => $source,
            'kpis' => [
                'today_sales' => $todaySales,
                'month_sales' => $monthSales,
                'today_transactions' => $todayTransactions,
                'month_units' => $monthUnits,
                'total_sales' => $totalSales,
                'total_products_sold' => $totalProductsSold,
                'active_dealers' => $activeDealers,
                'active_customers' => $activeCustomers,
                'pending_orders' => $pendingDealerOrders + $pendingAdOrders,
            ],
            'sales_trend' => $salesTrend,
            'order_statuses' => $orderStatuses,
            'recent_transactions' => $recentTransactions,
            'refill_chart' => $refillChart,
            'latest_transactions' => $this->dashboardLatestTransactions($connection),
            'top_dealers' => $this->dashboardTopDealers($connection),
            'inactive_dealers' => $this->dashboardInactiveDealers($connection),
            'top_customers' => $this->dashboardTopCustomers($connection),
        ]);
    }

    private function dashboardConnectionForSource($source)
    {
        return [
            'project_rise' => 'admin_crms',
            'admin_crms' => 'admin_crms',
            'project_genesis' => 'admin_crms2',
            'admin_crms2' => 'admin_crms2',
        ][$source] ?? null;
    }

    private function dashboardDatabase($connection)
    {
        return $connection ? DB::connection($connection) : DB::connection();
    }

    private function dashboardHasTable($connection, $table)
    {
        try {
            return $this->dashboardDatabase($connection)->getSchemaBuilder()->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function dashboardHasColumn($connection, $table, $column)
    {
        try {
            return $this->dashboardDatabase($connection)->getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function dashboardQuery($connection, $table)
    {
        return $this->dashboardDatabase($connection)->table($table);
    }

    private function dashboardTransactionDateColumn($connection)
    {
        foreach (['created_at', 'date', 'transaction_date'] as $column) {
            if ($this->dashboardHasColumn($connection, 'transaction_details', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function dashboardTransactionQtyExpression($connection)
    {
        foreach (['qty', 'quantity'] as $column) {
            if ($this->dashboardHasColumn($connection, 'transaction_details', $column)) {
                return 'COALESCE(' . $column . ', 0)';
            }
        }

        return '0';
    }

    private function dashboardTransactionAmountExpression($connection)
    {
        $qtyExpression = $this->dashboardTransactionQtyExpression($connection);

        if ($this->dashboardHasColumn($connection, 'transaction_details', 'price') && $qtyExpression !== '0') {
            return 'COALESCE(price, 0) * ' . $qtyExpression;
        }

        foreach (['amount', 'total_amount', 'grand_total'] as $column) {
            if ($this->dashboardHasColumn($connection, 'transaction_details', $column)) {
                return 'COALESCE(' . $column . ', 0)';
            }
        }

        return '0';
    }

    private function dashboardClientTable($connection)
    {
        foreach (['clients', 'customers'] as $table) {
            if ($this->dashboardHasTable($connection, $table)) {
                return $table;
            }
        }

        return null;
    }

    private function dashboardTransactionCustomerColumn($connection)
    {
        foreach (['client_id', 'customer_id', 'user_id'] as $column) {
            if ($this->dashboardHasColumn($connection, 'transaction_details', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function dashboardTransactionDealerColumn($connection)
    {
        foreach (['dealer_id', 'user_id'] as $column) {
            if ($this->dashboardHasColumn($connection, 'transaction_details', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function dashboardNameExpression($connection, $table, $alias)
    {
        if ($this->dashboardHasColumn($connection, $table, 'name')) {
            return $alias . '.name';
        }

        $nameColumns = collect(['first_name', 'middle_name', 'last_name'])
            ->filter(function ($column) use ($connection, $table) {
                return $this->dashboardHasColumn($connection, $table, $column);
            })
            ->map(function ($column) use ($alias) {
                return $alias . '.' . $column;
            })
            ->values();

        return $nameColumns->isNotEmpty()
            ? "TRIM(CONCAT_WS(' ', " . $nameColumns->implode(', ') . '))'
            : null;
    }

    private function dashboardActiveCustomers($connection)
    {
        $table = $this->dashboardClientTable($connection);

        if (!$table) {
            return 0;
        }

        $query = $this->dashboardQuery($connection, $table);

        if ($this->dashboardHasColumn($connection, $table, 'status')) {
            $query->whereRaw('LOWER(status) = ?', ['active']);
        }

        return $query->count();
    }

    private function dashboardActiveDealers($connection)
    {
        if ($this->dashboardHasTable($connection, 'dealers')) {
            $query = $this->dashboardQuery($connection, 'dealers');

            if ($this->dashboardHasColumn($connection, 'dealers', 'status')) {
                $query->whereRaw('LOWER(status) = ?', ['active']);
            }

            return $query->count();
        }

        $dealerColumn = $this->dashboardTransactionDealerColumn($connection);

        return $this->dashboardHasTable($connection, 'transaction_details') && $dealerColumn
            ? $this->dashboardQuery($connection, 'transaction_details')
                ->whereNotNull($dealerColumn)
                ->distinct()
                ->count($dealerColumn)
            : 0;
    }

    private function dashboardRecentTransactions($connection)
    {
        if (!$this->dashboardHasTable($connection, 'transaction_details')) {
            return collect();
        }

        $query = $this->dashboardQuery($connection, 'transaction_details')
            ->select('transaction_details.*');
        $customerTable = $this->dashboardClientTable($connection);
        $customerColumn = $this->dashboardTransactionCustomerColumn($connection);
        $dealerColumn = $this->dashboardTransactionDealerColumn($connection);

        try {
            if (
                $customerTable &&
                $customerColumn &&
                $this->dashboardHasColumn($connection, $customerTable, 'id') &&
                ($customerNameExpression = $this->dashboardNameExpression($connection, $customerTable, 'c'))
            ) {
                $query->leftJoin($customerTable . ' as c', 'transaction_details.' . $customerColumn, '=', 'c.id')
                    ->addSelect(DB::raw($customerNameExpression . ' as customer_name'));
            }

            if (
                $this->dashboardHasTable($connection, 'users') &&
                $dealerColumn &&
                $this->dashboardHasColumn($connection, 'users', 'id') &&
                ($dealerNameExpression = $this->dashboardNameExpression($connection, 'users', 'u'))
            ) {
                $query->leftJoin('users as u', 'transaction_details.' . $dealerColumn, '=', 'u.id')
                    ->addSelect(DB::raw($dealerNameExpression . ' as dealer_name'));
            } elseif (
                $this->dashboardHasTable($connection, 'dealers') &&
                $dealerColumn &&
                $this->dashboardHasColumn($connection, 'dealers', 'id') &&
                ($dealerNameExpression = $this->dashboardNameExpression($connection, 'dealers', 'd'))
            ) {
                $query->leftJoin('dealers as d', 'transaction_details.' . $dealerColumn, '=', 'd.id')
                    ->addSelect(DB::raw($dealerNameExpression . ' as dealer_name'));
            }
        } catch (\Throwable $e) {
            //
        }

        $sortColumn = $this->dashboardHasColumn($connection, 'transaction_details', 'id')
            ? 'transaction_details.id'
            : ($this->dashboardTransactionDateColumn($connection)
                ? 'transaction_details.' . $this->dashboardTransactionDateColumn($connection)
                : null);

        if ($sortColumn) {
            $query->orderByDesc($sortColumn);
        }

        return $query->limit(6)
            ->get()
            ->map(function ($transaction) {
                $qty = (float) ($transaction->qty ?? ($transaction->quantity ?? 0));
                $amount = (float) ($transaction->amount ?? ($transaction->total_amount ?? ($transaction->grand_total ?? 0)));
                $price = (float) ($transaction->price ?? ($qty > 0 && $amount > 0 ? $amount / $qty : 0));

                return [
                    'id' => $transaction->id ?? null,
                    'customer' => $transaction->customer_name ?? 'Walk-in customer',
                    'dealer' => $transaction->dealer_name ?? 'Unassigned',
                    'item' => ($transaction->item ?? ($transaction->product_name ?? ($transaction->product ?? null))) ?: 'Product',
                    'amount' => round($amount > 0 ? $amount : $price * $qty, 2),
                    'quantity' => $qty,
                    'customer_points' => (float) ($transaction->points_client ?? ($transaction->points ?? 0)),
                    'dealer_points' => (float) ($transaction->points_dealer ?? 0),
                    'raw_date' => $transaction->created_at ?? ($transaction->date ?? null),
                    'time' => !empty($transaction->created_at ?? $transaction->date ?? null)
                        ? Carbon::parse($transaction->created_at ?? $transaction->date)->diffForHumans()
                        : null,
                ];
            });
    }

    private function dashboardRefillChart($connection, $year, $month = null)
    {
        $year = (int) $year;
        $month = $month ? (int) $month : null;
        $dateColumn = $this->dashboardTransactionDateColumn($connection);
        $qtyExpression = $this->dashboardTransactionQtyExpression($connection);

        if (!$this->dashboardHasTable($connection, 'transaction_details') || !$dateColumn) {
            return [
                'categories' => $month ? range(1, Carbon::create($year, $month ?: 1, 1)->daysInMonth) : collect(range(1, 12))->map(function ($m) { return Carbon::create()->month($m)->format('F'); })->toArray(),
                'qty' => $month ? array_fill(0, Carbon::create($year, $month ?: 1, 1)->daysInMonth, 0) : array_fill(0, 12, 0),
                'year' => $year,
                'month' => $month,
                'view_type' => $month ? 'monthly' : 'yearly',
                'available_months' => [],
            ];
        }

        if ($month) {
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $rows = $this->dashboardQuery($connection, 'transaction_details')
                ->selectRaw('DAY(' . $dateColumn . ') as day_number, COALESCE(SUM(' . $qtyExpression . '), 0) as total_qty')
                ->whereYear($dateColumn, $year)
                ->whereMonth($dateColumn, $month)
                ->whereNotNull($dateColumn)
                ->groupBy(DB::raw('DAY(' . $dateColumn . ')'))
                ->pluck('total_qty', 'day_number');

            $categories = range(1, $daysInMonth);
            $qty = collect($categories)->map(function ($day) use ($rows) {
                return (float) ($rows[$day] ?? 0);
            })->toArray();
        } else {
            $rows = $this->dashboardQuery($connection, 'transaction_details')
                ->selectRaw('MONTH(' . $dateColumn . ') as month_number, COALESCE(SUM(' . $qtyExpression . '), 0) as total_qty')
                ->whereYear($dateColumn, $year)
                ->whereNotNull($dateColumn)
                ->groupBy(DB::raw('MONTH(' . $dateColumn . ')'))
                ->pluck('total_qty', 'month_number');

            $categories = collect(range(1, 12))->map(function ($monthNumber) {
                return Carbon::create()->month($monthNumber)->format('F');
            })->toArray();
            $qty = collect(range(1, 12))->map(function ($monthNumber) use ($rows) {
                return (float) ($rows[$monthNumber] ?? 0);
            })->toArray();
        }

        return [
            'categories' => $categories,
            'qty' => $qty,
            'year' => $year,
            'month' => $month,
            'view_type' => $month ? 'monthly' : 'yearly',
            'available_months' => $this->dashboardAvailableMonths($connection, $year),
        ];
    }

    private function dashboardAvailableMonths($connection, $year)
    {
        $dateColumn = $this->dashboardTransactionDateColumn($connection);

        if (!$this->dashboardHasTable($connection, 'transaction_details') || !$dateColumn) {
            return [];
        }

        return $this->dashboardQuery($connection, 'transaction_details')
            ->selectRaw('DISTINCT MONTH(' . $dateColumn . ') as month_number, MONTHNAME(' . $dateColumn . ') as month_name')
            ->whereYear($dateColumn, (int) $year)
            ->whereNotNull($dateColumn)
            ->orderBy('month_number')
            ->get()
            ->map(function ($month) {
                return [
                    'number' => (int) $month->month_number,
                    'name' => $month->month_name,
                ];
            })
            ->toArray();
    }

    private function dashboardLatestTransactions($connection)
    {
        return $this->dashboardRecentTransactions($connection)->map(function ($transaction) {
            return [
                'customer' => $transaction['customer'],
                'dealer' => $transaction['dealer'],
                'item' => $transaction['item'],
                'date' => $transaction['time'],
                'raw_date' => $transaction['raw_date'] ?? null,
                'amount' => $transaction['amount'],
                'quantity' => $transaction['quantity'],
                'customer_points' => $transaction['customer_points'] ?? 0,
                'dealer_points' => $transaction['dealer_points'] ?? 0,
            ];
        })->values();
    }

    private function dashboardTopDealers($connection)
    {
        $dealerColumn = $this->dashboardTransactionDealerColumn($connection);
        $dateColumn = $this->dashboardTransactionDateColumn($connection);

        if (!$this->dashboardHasTable($connection, 'transaction_details') || !$dealerColumn) {
            return collect();
        }

        if ($this->dashboardHasColumn($connection, 'transaction_details', 'points_dealer')) {
            $pointsExpression = 'COALESCE(td.points_dealer, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'points')) {
            $pointsExpression = 'COALESCE(td.points, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'qty')) {
            $pointsExpression = 'COALESCE(td.qty, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'quantity')) {
            $pointsExpression = 'COALESCE(td.quantity, 0)';
        } else {
            $pointsExpression = '0';
        }

        $query = $this->dashboardQuery($connection, 'transaction_details as td')
            ->selectRaw('td.' . $dealerColumn . ' as id, COALESCE(SUM(' . $pointsExpression . '), 0) as total_points')
            ->whereNotNull('td.' . $dealerColumn)
            ->groupBy('td.' . $dealerColumn)
            ->orderByDesc('total_points')
            ->limit(10);

        if ($dateColumn) {
            $query->addSelect(DB::raw('MAX(td.' . $dateColumn . ') as latest_transaction'));
        }

        return $query->get()->map(function ($row) use ($connection) {
            $row->name = $this->dashboardLookupName($connection, 'dealers', $row->id)
                ?: $this->dashboardLookupName($connection, 'users', $row->id)
                ?: 'Unknown';
            return [
                'name' => $row->name,
                'total_points' => (float) $row->total_points,
                'latest_transaction' => $row->latest_transaction ?? null,
            ];
        });
    }

    private function dashboardTopCustomers($connection)
    {
        $customerColumn = $this->dashboardTransactionCustomerColumn($connection);
        $dateColumn = $this->dashboardTransactionDateColumn($connection);

        if (!$this->dashboardHasTable($connection, 'transaction_details') || !$customerColumn) {
            return collect();
        }

        if ($this->dashboardHasColumn($connection, 'transaction_details', 'points_client')) {
            $pointsExpression = 'COALESCE(td.points_client, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'points')) {
            $pointsExpression = 'COALESCE(td.points, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'qty')) {
            $pointsExpression = 'COALESCE(td.qty, 0)';
        } elseif ($this->dashboardHasColumn($connection, 'transaction_details', 'quantity')) {
            $pointsExpression = 'COALESCE(td.quantity, 0)';
        } else {
            $pointsExpression = '0';
        }

        $query = $this->dashboardQuery($connection, 'transaction_details as td')
            ->selectRaw('td.' . $customerColumn . ' as id, COALESCE(SUM(' . $pointsExpression . '), 0) as total_points')
            ->whereNotNull('td.' . $customerColumn)
            ->groupBy('td.' . $customerColumn)
            ->orderByDesc('total_points')
            ->limit(10);

        if ($dateColumn) {
            $query->addSelect(DB::raw('MAX(td.' . $dateColumn . ') as latest_transaction'));
        }

        $clientTable = $this->dashboardClientTable($connection);

        return $query->get()->map(function ($row) use ($connection, $clientTable) {
            $row->name = ($clientTable ? $this->dashboardLookupName($connection, $clientTable, $row->id) : null) ?: 'Unknown';
            return [
                'name' => $row->name,
                'total_points' => (float) $row->total_points,
                'latest_transaction' => $row->latest_transaction ?? null,
            ];
        });
    }

    private function dashboardInactiveDealers($connection)
    {
        if (!$this->dashboardHasTable($connection, 'dealers')) {
            return collect();
        }

        $dealerColumn = $this->dashboardTransactionDealerColumn($connection);
        $dateColumn = $this->dashboardTransactionDateColumn($connection);
        $dealerIdColumn = $this->dashboardHasColumn($connection, 'dealers', 'user_id') ? 'user_id' : 'id';

        $latestRows = ($dealerColumn && $dateColumn && $this->dashboardHasTable($connection, 'transaction_details'))
            ? $this->dashboardQuery($connection, 'transaction_details')
                ->selectRaw($dealerColumn . ' as dealer_id, MAX(' . $dateColumn . ') as last_transaction_date')
                ->whereNotNull($dealerColumn)
                ->groupBy($dealerColumn)
                ->pluck('last_transaction_date', 'dealer_id')
            : collect();

        return $this->dashboardQuery($connection, 'dealers')
            ->get()
            ->map(function ($dealer) use ($connection, $latestRows, $dealerIdColumn) {
                $dealerId = $dealer->{$dealerIdColumn} ?? ($dealer->id ?? null);
                $lastDate = $dealerId ? ($latestRows[$dealerId] ?? null) : null;

                return [
                    'name' => $this->dashboardResolvedName($connection, 'dealers', $dealer),
                    'store_name' => $dealer->store_name ?? null,
                    'last_transaction_date' => $lastDate,
                    'days_since_transaction' => $lastDate ? Carbon::parse($lastDate)->diffInDays(now()) : null,
                ];
            })
            ->filter(function ($dealer) {
                return empty($dealer['last_transaction_date']) || (int) $dealer['days_since_transaction'] >= 3;
            })
            ->sortByDesc(function ($dealer) {
                return $dealer['days_since_transaction'] ?? 99999;
            })
            ->take(25)
            ->values();
    }

    private function dashboardLookupName($connection, $table, $id)
    {
        if (!$id || !$this->dashboardHasTable($connection, $table) || !$this->dashboardHasColumn($connection, $table, 'id')) {
            return null;
        }

        $row = $this->dashboardQuery($connection, $table)->where('id', $id)->first();

        return $row ? $this->dashboardResolvedName($connection, $table, $row) : null;
    }

    private function dashboardResolvedName($connection, $table, $row)
    {
        if (!empty($row->name)) {
            return $row->name;
        }

        return collect(['first_name', 'middle_name', 'last_name'])
            ->map(function ($column) use ($row) {
                return $row->{$column} ?? null;
            })
            ->filter()
            ->implode(' ') ?: 'Unknown';
    }

    private function getPhilippineMapData()
    {
        $transactions = TransactionDetail::whereNotNull('client_address')
            ->where('client_address', '!=', '')
            ->get();
        
        if ($transactions->isEmpty()) {
            return [];
        }
        
        $provinceMapping = [
            'PH-SOR' => ['SORSOGON'],
            'PH-ABR' => ['ABRA'],
            'PH-AGN' => ['AGUSAN DEL NORTE', 'AGUSAN NORTE'],
            'PH-AGS' => ['AGUSAN DEL SUR', 'AGUSAN SUR'],
            'PH-AKL' => ['AKLAN'],
            'PH-ALB' => ['ALBAY'],
            'PH-ANT' => ['ANTIQUE'],
            'PH-APA' => ['APAYAO'],
            'PH-AUR' => ['AURORA'],
            'PH-BAS' => ['BASILAN'],
            'PH-BAN' => ['BATAAN'],
            'PH-BTG' => ['BATANGAS'],
            'PH-BTN' => ['BATANES'],
            'PH-BEN' => ['BENGUET'],
            'PH-BIL' => ['BILIRAN'],
            'PH-BOH' => ['BOHOL'],
            'PH-BUK' => ['BUKIDNON'],
            'PH-BUL' => ['BULACAN'],
            'PH-CAG' => ['CAGAYAN'],
            'PH-CAN' => ['CAMARINES NORTE', 'CAM. NORTE'],
            'PH-CAS' => ['CAMARINES SUR', 'CAM. SUR'],
            'PH-CAM' => ['CAMIGUIN'],
            'PH-CAP' => ['CAPIZ'],
            'PH-CAT' => ['CATANDUANES'],
            'PH-CAV' => ['CAVITE'],
            'PH-CEB' => ['CEBU'],
            'PH-COM' => ['COMPOSTELA VALLEY', 'DAVAO DE ORO'],
            'PH-NCO' => ['COTABATO', 'NORTH COTABATO'],
            'PH-DAV' => ['DAVAO DEL NORTE', 'DAVAO NORTE'],
            'PH-DAS' => ['DAVAO DEL SUR', 'DAVAO SUR'],
            'PH-DAO' => ['DAVAO ORIENTAL'],
            'PH-DIN' => ['DINAGAT', 'DINAGAT ISLANDS'],
            'PH-EAS' => ['EASTERN SAMAR', 'EAST SAMAR'],
            'PH-GUI' => ['GUIMARAS'],
            'PH-IFU' => ['IFUGAO'],
            'PH-ILN' => ['ILOCOS NORTE'],
            'PH-ILS' => ['ILOCOS SUR'],
            'PH-ILI' => ['ILOILO'],
            'PH-ISA' => ['ISABELA'],
            'PH-KAL' => ['KALINGA'],
            'PH-LUN' => ['LA UNION'],
            'PH-LAG' => ['LAGUNA'],
            'PH-LAN' => ['LANAO DEL NORTE', 'LANAO NORTE'],
            'PH-LAS' => ['LANAO DEL SUR', 'LANAO SUR'],
            'PH-LEY' => ['LEYTE'],
            'PH-MG' => ['MAGUINDANAO'],
            'PH-MAD' => ['MARINDUQUE'],
            'PH-MAS' => ['MASBATE'],
            'PH-MNL' => ['MANILA', 'METRO MANILA', 'NCR', 'MAKATI', 'QUEZON CITY', 'PASIG', 'TAGUIG', 'PARANAQUE', 'MUNTINLUPA', 'CALOOCAN', 'MANDALUYONG'],
            'PH-MDC' => ['MINDORO OCCIDENTAL', 'OCCIDENTAL MINDORO'],
            'PH-MDR' => ['MINDORO ORIENTAL', 'ORIENTAL MINDORO'],
            'PH-MSC' => ['MISAMIS OCCIDENTAL'],
            'PH-MSR' => ['MISAMIS ORIENTAL'],
            'PH-MOU' => ['MOUNTAIN PROVINCE'],
            'PH-NEC' => ['NEGROS OCCIDENTAL'],
            'PH-NER' => ['NEGROS ORIENTAL'],
            'PH-NSA' => ['NORTHERN SAMAR', 'NORTH SAMAR'],
            'PH-NUE' => ['NUEVA ECIJA'],
            'PH-NUV' => ['NUEVA VIZCAYA'],
            'PH-PAM' => ['PAMPANGA'],
            'PH-PAN' => ['PANGASINAN'],
            'PH-PLW' => ['PALAWAN'],
            'PH-QUE' => ['QUEZON'],
            'PH-QUI' => ['QUIRINO'],
            'PH-RIZ' => ['RIZAL'],
            'PH-ROM' => ['ROMBLON'],
            'PH-WSA' => ['SAMAR', 'WESTERN SAMAR'],
            'PH-SAR' => ['SARANGANI'],
            'PH-SIG' => ['SIQUIJOR'],
            'PH-SCO' => ['SOUTH COTABATO'],
            'PH-SLE' => ['SOUTHERN LEYTE'],
            'PH-SUK' => ['SULTAN KUDARAT'],
            'PH-SLU' => ['SULU'],
            'PH-SUN' => ['SURIGAO DEL NORTE', 'SURIGAO NORTE'],
            'PH-SUR' => ['SURIGAO DEL SUR', 'SURIGAO SUR'],
            'PH-TAR' => ['TARLAC'],
            'PH-TAW' => ['TAWI-TAWI'],
            'PH-ZMB' => ['ZAMBALES'],
            'PH-ZAN' => ['ZAMBOANGA DEL NORTE', 'ZAMBOANGA NORTE'],
            'PH-ZAS' => ['ZAMBOANGA DEL SUR', 'ZAMBOANGA SUR'],
            'PH-ZSI' => ['ZAMBOANGA SIBUGAY'],
        ];
        
        $totalBarangaysPerProvince = [
            'PH-SOR' => 541,
            'PH-ABR' => 27,
            'PH-AGN' => 343,
            'PH-AGS' => 333,
            'PH-AKL' => 327,
            'PH-ALB' => 720,
            'PH-ANT' => 590,
            'PH-APA' => 133,
            'PH-AUR' => 151,
            'PH-BAS' => 414,
            'PH-BAN' => 237,
            'PH-BTG' => 1078,
            'PH-BTN' => 29,
            'PH-BEN' => 140,
            'PH-BIL' => 132,
            'PH-BOH' => 1109,
            'PH-BUK' => 464,
            'PH-BUL' => 569,
            'PH-CAG' => 820,
            'PH-CAN' => 282,
            'PH-CAS' => 1063,
            'PH-CAM' => 58,
            'PH-CAP' => 473,
            'PH-CAT' => 315,
            'PH-CAV' => 829,
            'PH-CEB' => 1066,
            'PH-COM' => 330,
            'PH-NCO' => 543,
            'PH-DAV' => 407,
            'PH-DAS' => 545,
            'PH-DAO' => 183,
            'PH-DIN' => 100,
            'PH-EAS' => 597,
            'PH-GUI' => 98,
            'PH-IFU' => 175,
            'PH-ILN' => 557,
            'PH-ILS' => 768,
            'PH-ILI' => 1721,
            'PH-ISA' => 1018,
            'PH-KAL' => 152,
            'PH-LUN' => 576,
            'PH-LAG' => 674,
            'PH-LAN' => 615,
            'PH-LAS' => 1156,
            'PH-LEY' => 1641,
            'PH-MG' => 508,
            'PH-MAD' => 218,
            'PH-MAS' => 550,
            'PH-MNL' => 1710,
            'PH-MDC' => 213,
            'PH-MDR' => 426,
            'PH-MSC' => 482,
            'PH-MSR' => 482,
            'PH-MOU' => 144,
            'PH-NEC' => 662,
            'PH-NER' => 557,
            'PH-NSA' => 569,
            'PH-NUE' => 849,
            'PH-NUV' => 275,
            'PH-PAM' => 538,
            'PH-PAN' => 1364,
            'PH-PLW' => 433,
            'PH-QUE' => 1242,
            'PH-QUI' => 132,
            'PH-RIZ' => 188,
            'PH-ROM' => 279,
            'PH-WSA' => 1201,
            'PH-SAR' => 140,
            'PH-SIG' => 107,
            'PH-SCO' => 218,
            'PH-SLE' => 500,
            'PH-SUK' => 253,
            'PH-SLU' => 410,
            'PH-SUN' => 342,
            'PH-SUR' => 309,
            'PH-TAR' => 511,
            'PH-TAW' => 276,
            'PH-ZMB' => 247,
            'PH-ZAN' => 691,
            'PH-ZAS' => 681,
            'PH-ZSI' => 402,
        ];
        
        $provinceBarangays = [];
        
        foreach ($transactions as $transaction) {
            $address = strtoupper(trim($transaction->client_address));
            
            foreach ($provinceMapping as $pathId => $provinceNames) {
                foreach ($provinceNames as $provinceName) {
                    if (strpos($address, $provinceName) !== false) {
                        if (!isset($provinceBarangays[$pathId])) {
                            $provinceBarangays[$pathId] = [];
                        }
                        
                        $barangay = $this->extractBarangay($address);
                        $provinceBarangays[$pathId][$barangay] = true;
                        
                        break 2;
                    }
                }
            }
        }
        
        if (empty($provinceBarangays)) {
            return [];
        }
        
        $result = [];
        
        foreach ($provinceBarangays as $pathId => $barangays) {
            $reachedBarangays = count($barangays);
            $totalBarangays = $totalBarangaysPerProvince[$pathId] ?? 100;
            
            $percentage = round(($reachedBarangays / $totalBarangays) * 100, 2);
            
            if ($percentage >= 50) {
                $level = 'high';
            } elseif ($percentage >= 20) {
                $level = 'average';
            } else {
                $level = 'low';
            }
            
            $result[$pathId] = [
                'count' => $reachedBarangays,
                'total' => $totalBarangays,
                'level' => $level,
                'percentage' => $percentage
            ];
        }
        
        return $result;
    }

    private function extractBarangay($address)
    {
        $patterns = [
            '/BRGY\.?\s+([A-Z\s]+?)(?:,|$)/i',
            '/BARANGAY\s+([A-Z\s]+?)(?:,|$)/i',
            '/BARIO\s+([A-Z\s]+?)(?:,|$)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $address, $matches)) {
                return trim($matches[1]);
            }
        }
        
        $parts = explode(',', $address);
        return trim($parts[0]);
    }

    public function getProvinceDetails(Request $request)
    {
        $provinceName = $request->get('province');
        
        $transactions = TransactionDetail::whereNotNull('client_address')
            ->where('client_address', 'LIKE', "%{$provinceName}%")
            ->with(['customer', 'dealer'])
            ->get();
        
        $locationData = [];
        
        foreach ($transactions as $transaction) {
            $address = $transaction->client_address;
            
            $location = $this->extractLocation($address, $provinceName);
            
            if (!isset($locationData[$location])) {
                $locationData[$location] = [
                    'location' => $location,
                    'full_address' => $address,
                    'transaction_count' => 0,
                    'total_qty' => 0,
                    'total_amount' => 0,
                    'customer_ids' => [],
                    'dealer_ids' => []
                ];
            }
            
            $locationData[$location]['transaction_count']++;
            $locationData[$location]['total_qty'] += $transaction->qty;
            $locationData[$location]['total_amount'] += ($transaction->qty * $transaction->price);
            $locationData[$location]['customer_ids'][] = $transaction->client_id;
            $locationData[$location]['dealer_ids'][] = $transaction->dealer_id;
        }
        
        $locations = collect($locationData)->map(function($item) {
            return [
                'location' => $item['location'],
                'full_address' => $item['full_address'],
                'transaction_count' => $item['transaction_count'],
                'total_qty' => $item['total_qty'],
                'total_amount' => number_format($item['total_amount'], 2, '.', ''),
                'customer_count' => count(array_unique($item['customer_ids'])),
                'dealer_count' => count(array_unique($item['dealer_ids']))
            ];
        })->sortByDesc('transaction_count')->values();
        
        $summary = [
            'total_transactions' => $transactions->count(),
            'total_qty' => $transactions->sum('qty'),
            'total_amount' => number_format($transactions->sum(function($t) {
                return $t->qty * $t->price;
            }), 2, '.', ''),
            'unique_customers' => $transactions->pluck('client_id')->unique()->count(),
            'active_dealers' => $transactions->pluck('dealer_id')->unique()->count(),
            'total_locations' => count($locationData)
        ];
        
        return response()->json([
            'province' => $provinceName,
            'summary' => $summary,
            'locations' => $locations
        ]);
    }

    private function extractLocation($address, $provinceName)
    {
        $location = str_replace($provinceName, '', strtoupper($address));
        $location = trim($location, ', ');
        
        if (empty($location)) {
            return $provinceName . ' (Main)';
        }
        
        return $location;
    }
    
    public function getChartDataAjax(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', null);
        $viewType = $month ? 'monthly' : 'yearly';
        $source = $request->get('source', 'regular');
        $connection = $this->dashboardConnectionForSource($source);
        
        if (!is_numeric($year) || $year < 1900 || $year > Carbon::now()->year + 10) {
            return response()->json(['error' => 'Invalid year'], 400);
        }
        
        if ($month !== null && (!is_numeric($month) || $month < 1 || $month > 12)) {
            return response()->json(['error' => 'Invalid month'], 400);
        }
        
        if ($source !== 'regular') {
            $sourceChart = $this->dashboardRefillChart($connection, $year, $month);

            return response()->json([
                'categories' => $sourceChart['categories'],
                'qty' => $sourceChart['qty'],
                'year' => (int) $year,
                'month' => $month ? (int) $month : null,
                'view_type' => $viewType,
                'available_months' => $sourceChart['available_months'],
                'total_records' => 0,
            ]);
        }

        if ($viewType === 'monthly') {
            $chartData = $this->getDailyData($year, $month);
        } else {
            $chartData = $this->getMonthlyData($year);
        }
        
        $availableMonths = $this->getAvailableMonths($year);
        
        $totalRecords = DB::table('transaction_details')
            ->whereYear('created_at', $year)
            ->when($month, function ($query) use ($year, $month) {
                return $query->whereMonth('created_at', $month);
            })
            ->count();
        
        return response()->json([
            'categories' => $chartData['categories'],
            'qty' => $chartData['qty'],
            'year' => (int) $year,
            'month' => $month ? (int) $month : null,
            'view_type' => $viewType,
            'available_months' => $availableMonths,
            'total_records' => $totalRecords,
            'debug' => [
                'requested_year' => $year,
                'requested_month' => $month,
                'data_found' => $totalRecords > 0
            ]
        ]);
    }

    private function getMonthlyData($year)
    {
        $year = (int) $year;
        
        $sales = DB::table('transaction_details')
            ->selectRaw('MONTH(created_at) as month_number, MONTHNAME(created_at) as month_name, SUM(qty) as total_qty')
            ->whereYear('created_at', $year)
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('MONTH(created_at), MONTHNAME(created_at)'))
            ->orderBy('month_number')
            ->get();

        $salesData = $sales->keyBy('month_number');

        $allMonths = collect(range(1, 12))->map(function ($monthNumber) use ($salesData) {
            $monthData = $salesData->get($monthNumber);
            return [
                'month' => Carbon::create()->month($monthNumber)->format('F'),
                'total_qty' => $monthData ? (int) $monthData->total_qty : 0,
            ];
        });

        $categories = $allMonths->pluck('month')->toArray();
        $qty = $allMonths->pluck('total_qty')->toArray();

        return [
            'categories' => $categories,
            'qty' => $qty
        ];
    }

    private function getDailyData($year, $month)
    {
        $year = (int) $year;
        $month = (int) $month;
        
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        
        $sales = DB::table('transaction_details')
            ->selectRaw('DAY(created_at) as day_number, SUM(qty) as total_qty')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('DAY(created_at)'))
            ->orderBy('day_number')
            ->get();

        $salesData = $sales->keyBy('day_number');

        $allDays = collect(range(1, $daysInMonth))->map(function ($dayNumber) use ($salesData) {
            $dayData = $salesData->get($dayNumber);
            return [
                'day' => $dayNumber,
                'total_qty' => $dayData ? (int) $dayData->total_qty : 0,
            ];
        });

        $categories = $allDays->pluck('day')->toArray();
        $qty = $allDays->pluck('total_qty')->toArray();

        return [
            'categories' => $categories,
            'qty' => $qty
        ];
    }

    private function getAvailableYears()
    {
        $years = DB::table('transaction_details')
            ->selectRaw('DISTINCT YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        return $years;
    }

    private function getAvailableMonths($year)
    {
        $months = DB::table('transaction_details')
            ->selectRaw('DISTINCT MONTH(created_at) as month_number, MONTHNAME(created_at) as month_name')
            ->whereYear('created_at', $year)
            ->whereNotNull('created_at')
            ->orderBy('month_number')
            ->get()
            ->map(function ($month) {
                return [
                    'number' => $month->month_number,
                    'name' => $month->month_name
                ];
            })
            ->toArray();

        return $months;
    }

    private function calculateSalesTrend()
    {
        // $currentYear = Carbon::now()->year;
        // $currentMonth = Carbon::now()->month;
        
        // $currentYearTransactions = TransactionDetail::whereYear('created_at', $currentYear)->count();
        
        // $targetTransactions = 12 * $currentMonth;
        
        // if ($targetTransactions == 0) {
        //     return [
        //         'percentage' => 0,
        //         'trend' => 'neutral',
        //         'icon' => 'ti-minus'
        //     ];
        // }
        
        // $percentageAchieved = ($currentYearTransactions / $targetTransactions) * 100;
        
        // return [
        //     'percentage' => round($percentageAchieved, 2),
        //     'trend' => $percentageAchieved >= 100 ? 'up' : ($percentageAchieved >= 80 ? 'neutral' : 'down'),
        //     'icon' => $percentageAchieved >= 100 ? 'ti-trending-up' : ($percentageAchieved >= 80 ? 'ti-minus' : 'ti-trending-down')
        // ];
    }


    private function calculateQtyTrend()
    {
        // $currentYear = Carbon::now()->year;
        // $currentMonth = Carbon::now()->month;
        
        // $currentYearQty = TransactionDetail::whereYear('created_at', $currentYear)->sum('qty');
        
        // $targetQty = 12 * $currentMonth;
        
        // if ($targetQty == 0) {
        //     return [
        //         'percentage' => 0,
        //         'trend' => 'neutral',
        //         'icon' => 'ti-minus'
        //     ];
        // }
        
        // $percentageAchieved = ($currentYearQty / $targetQty) * 100;
        
        // return [
        //     'percentage' => round($percentageAchieved, 2),
        //     'trend' => $percentageAchieved >= 100 ? 'up' : ($percentageAchieved >= 80 ? 'neutral' : 'down'),
        //     'icon' => $percentageAchieved >= 100 ? 'ti-trending-up' : ($percentageAchieved >= 80 ? 'ti-minus' : 'ti-trending-down')
        // ];
    }

    public function about()
    {
        return view('about');
    }
    
    public function storelocation()
    {
        $dealers = $this->getFormattedDealers();
        $customers = $this->getFormattedCustomers();
        $locations = $dealers->concat($customers);
        return view('storelocation', compact('locations'));
    }

    public function getLocationsForMap()
    {
        $dealers = $this->getFormattedDealers(true);
        $customers = $this->getFormattedCustomers(true);
        $locations = $dealers->concat($customers);
        return response()->json($locations);
    }

    public function getLocationDetails($id, $type)
    {
        $location = null;

        if ($type === 'dealer') {
            $location = Dealer::select('id', 'name', 'address', 'store_name', 'store_type', 'number', 'email_address', 'latitude', 'longitude')
                ->where('id', $id)
                ->where('status', 'Active')
                ->first();

            if ($location) {
                $location->location_type = 'dealer';
            }
        } elseif ($type === 'customer') {
            $location = Client::select('id', 'name', 'address', 'number', 'email_address')
                ->where('id', $id)
                ->first();

            if ($location) {
                $location->store_name = $location->name;
                $location->store_type = null;
                $location->location_type = 'customer';
                $location->latitude = null;
                $location->longitude = null;
            }
        }

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        return response()->json($location);
    }

    private function getFormattedDealers($withCoordinates = false)
    {
        $columns = ['id', 'name', 'address', 'store_name', 'store_type', 'number', 'email_address'];

        if ($withCoordinates) {
            $columns[] = 'latitude';
            $columns[] = 'longitude';
        }

        return Dealer::select($columns)
            ->where('status', 'Active')
            ->whereNotNull('address')
            ->get()
            ->map(function ($dealer) {
                $dealer->location_type = 'dealer';
                return $dealer;
            });
    }

    private function getFormattedCustomers($withCoordinates = false)
    {
        $customers = Client::select('id', 'name', 'address', 'number', 'email_address')
            ->whereNotNull('address')
            ->get()
            ->map(function ($customer) use ($withCoordinates) {
                $customer->store_name = $customer->name;
                $customer->store_type = null;
                $customer->location_type = 'customer';

                if ($withCoordinates) {
                    $customer->latitude = null;
                    $customer->longitude = null;
                }

                return $customer;
            });

        return $customers;
    }
    
    // Partner
    public function adDashboard(Request $request)
    {

        $user = auth()->user();
        $adId = optional($user->ad)->id; 
        $orderTabs = $this->adOrderTabs($user, $adId);
        $orders = $orderTabs->pluck('orders')->flatten(1);
        $completedOrders = $orders->filter(function ($order) {
            return strtolower((string) ($order->status ?? '')) === 'completed';
        })->values();

        $areas = optional($user->ad)
            ->areas
            ? $user->ad->areas->pluck('area_name')->toArray()
            : [];

        $product_sold = $completedOrders;
        $products = Product::where('ad_user_id', $user->id)->where('status', 'Activate')->whereNotNull('price')->get()->toBase()
            ->merge($this->remoteAdProducts($user->id))
            ->unique(function ($product) {
                return strtolower(trim((string) ($product->product_name ?? $product->name ?? $product->id ?? '')));
            })
            ->values();
        $adVouchers = Voucher::whereIn('name', array_filter([optional($user->ad)->store_code, $user->name]))->get();
        $usedVoucherCount = $adVouchers->filter(function ($voucher) {
            return (int) $voucher->used_count > 0;
        })->count();
        $unusedVoucherCount = $adVouchers->filter(function ($voucher) {
            return (int) $voucher->used_count === 0;
        })->count();
        // dd($products);
        $adUser = optional(auth()->user()->ad)->id;
        $pendingOrdersCount = $orders->filter(function ($order) {
            return strtolower((string) ($order->status ?? '')) === 'pending';
        })->count();
        $dealer = "";
        $customer = "";
        $threeDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $user = auth()->user();
        $areas = $user->ad->areas->pluck('area_name')->toArray();
        
        $selectedYear = $request->get('year', Carbon::now()->year);
        $selectedMonth = $request->get('month', null);
        $viewType = $selectedMonth ? 'monthly' : 'yearly';
        
        // Monthly Sales Overview
        $sales = $completedOrders
            ->filter(function ($order) use ($selectedYear) {
                $date = $this->orderDate($order);

                return $date && (int) $date->format('Y') === (int) $selectedYear;
            })
            ->groupBy(function ($order) {
                return (int) $this->orderDate($order)->format('n');
            })
            ->map(function ($rows) {
                return $rows->sum(function ($order) {
                    return (float) ($order->price ?? 0) * (float) ($order->qty ?? 0);
                });
            });

        $months = [];
        $totals = [];

        for ($i = 1; $i <= 12; $i++) {

            $months[] = date("F", mktime(0, 0, 0, $i, 1));

            $totals[] = isset($sales[$i]) ? (float) $sales[$i] : 0;

        }

        // Map

        $dealers = Dealer::whereIn('area', $areas)->get()->toBase()
            ->merge($this->remoteAdDealers($areas));

        $mapDealers = [];

        foreach ($dealers as $d) {

            if (!$d->latitude || !$d->longitude) continue;

            $mapDealers[] = [
                'name' => $d->name,
                'email' => $d->email,
                'status' => $d->status,
                'region' => $d->location_region,
                'lat' => (float) $d->latitude,
                'lng' => (float) $d->longitude,
            ];
        }
        
        $customers_less = Client::where('status', 'Active')->whereDoesntHave('latestTransaction', function ($q) use ($threeDaysAgo) {
            $q->where('date', '>=', $threeDaysAgo);
        })
        ->whereHas('latestTransaction')
        ->orderBy(
            DB::raw('(SELECT date FROM transaction_details WHERE transaction_details.client_id = clients.id ORDER BY date DESC LIMIT 1)'),
            'desc'
        )
        ->get();

        $customers = Client::whereHas('transactions')->get();
        $transactions = Transaction::orderBy('id','desc')->get();
        
        $adDealers = $dealers;
        $transactions_details = TransactionDetail::orderBy('id','desc')->get();

        $adSalesTransactions = $completedOrders;
        $totalProductsSoldQty = $adSalesTransactions->sum('qty');
        $soldItemCount = $adSalesTransactions->pluck('item')->filter()->unique()->count();
        $avgProductSales = $soldItemCount > 0 ? ($totalProductsSoldQty / $soldItemCount) : 0;
        $totalRefill = $adSalesTransactions->filter(function ($transaction) {
            return stripos((string) $transaction->item, 'refill') !== false;
        })->sum('qty');

        $topDealers = $completedOrders
            ->filter(function ($order) {
                return !empty($order->dealer_id);
            })
            ->groupBy(function ($order) {
                return ($order->source_key ?? 'regular') . ':' . $order->dealer_id;
            })
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'dealer_id' => $first->dealer_id,
                    'dealer' => $first->dealer ?? null,
                    'total_qty' => $rows->sum('qty'),
                    'total_sales' => $rows->sum(function ($order) {
                        return (float) ($order->price ?? 0) * (float) ($order->qty ?? 0);
                    }),
                    'latest_transaction' => optional($rows->map(function ($order) {
                        return $this->orderDate($order);
                    })->filter()->sortByDesc(function ($date) {
                        return $date->timestamp;
                    })->first())->toDateString(),
                ];
            })
            ->sortByDesc('total_sales')
            ->take(10)
            ->values();

        $orderedByItem = $completedOrders
            ->filter(function ($order) {
                return !empty($order->item);
            })
            ->groupBy('item')
            ->map(function ($rows) {
                return $rows->sum('qty');
            });
      
        // $inventoryInByItem = InventoryTransfer::select(
        //         'item_name',
        //         DB::raw('MAX(sku) as sku'),
        //         DB::raw('SUM(qty) as stock_qty')
        //     )
        //     ->where('movement_type', '!=', 'transfer')
        //     ->where('ad_id', $adId)
        //     ->groupBy('item_name')
        //     ->get()
        //     ->keyBy('item_name');

        $inventoryInByItem = AdPurchaseOrderItem::select(
                'product_name as item_name',
                DB::raw('MAX(sku) as sku'),
                DB::raw('SUM(qty) as stock_qty')
            )
            ->whereHas('purchaseOrder', function ($query) use ($adId) {
                $query->where('ad_id', $adId)
                      ->where('status', 'Completed');
            })
            ->groupBy('item_name')
            ->get()
            ->toBase()
            ->merge($this->remoteAdPurchaseOrderStock($adId))
            ->groupBy('item_name')
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'item_name' => $first->item_name,
                    'sku' => $rows->pluck('sku')->filter()->first(),
                    'stock_qty' => $rows->sum('stock_qty'),
                ];
            })
            ->keyBy('item_name');   
         

        $outByItem = collect([$this->localInventoryOutByItem($adId), $this->remoteInventoryOutByItem($adId)])
            ->flatMap(function ($rows) {
                return $rows->map(function ($qty, $item) {
                    return (object) [
                        'item_name' => $item,
                        'out_qty' => $qty,
                    ];
                })->values();
            })
            ->groupBy('item_name')
            ->map(function ($rows) {
                return $rows->sum('out_qty');
            });

        $stockLevels = $orderedByItem->keys()
            ->merge($inventoryInByItem->keys())
            ->merge($outByItem->keys())
            ->filter()
            ->unique()
            ->map(function ($item) use ($inventoryInByItem, $orderedByItem, $outByItem) {
                $inventory = $inventoryInByItem->get($item);
                $stockQty = $inventory ? (float) $inventory->stock_qty : 0;
                $orderedQty = (float) ($orderedByItem[$item] ?? 0);
                $outQty = (float) ($outByItem[$item] ?? 0);

                return (object) [
                    'item' => $item,
                    'sku' => $inventory ? $inventory->sku : null,
                    'stock_qty' => $stockQty,
                    'sold_qty' => $orderedQty,
                    'out_qty' => $outQty,
                    'remaining_qty' => $stockQty - $orderedQty - $outQty,
                ];
            })
            ->sortBy('item')
            ->values();
        // dd($stockLevels);
        $thirtyDaysAgo = Carbon::today()->subDays(29);
        $last30DaysSoldByItem = $completedOrders
            ->filter(function ($order) use ($thirtyDaysAgo) {
                $date = $this->orderDate($order);

                return $date && $date->gte($thirtyDaysAgo);
            })
            ->groupBy('item')
            ->map(function ($rows) {
                return $rows->sum('qty');
            });

        $stockInventoryRows = $stockLevels->map(function ($stock) use ($last30DaysSoldByItem) {
            $endingInventory = (float) $stock->remaining_qty;
            $last30DaysSoldQty = (float) ($last30DaysSoldByItem[$stock->item] ?? 0);
            $averageSalesVolume = $last30DaysSoldQty / 30;
            $fourteenDayStockLevel = $averageSalesVolume * 14;
            $endingInventoryPercent = $fourteenDayStockLevel > 0
                ? ($endingInventory / $fourteenDayStockLevel) * 100
                : 0;
            $inventoryDays = $averageSalesVolume > 0
                ? ($endingInventory / $averageSalesVolume)
                : 0;
           
            return (object) [
                'item' => $stock->item,
                'sku' => $stock->sku,
                'ending_inventory' => $endingInventory,
                'average_sales_volume' => $averageSalesVolume,
                'fourteen_day_stock_level' => $fourteenDayStockLevel,
                'ending_inventory_percent' => $endingInventoryPercent,
                'inventory_days' => $inventoryDays,
            ];
        })->values();
        
       
        if(auth()->user()->role == "Dealer")
        {
            $dealer = Dealer::with('sales')->where('user_id',auth()->user()->id)->first();
            $transactions_details = TransactionDetail::where('dealer_id',auth()->user()->id)->orderBy('id','desc')->get();
            $total_sales = TransactionDetail::where('dealer_id',auth()->user()->id)->sum('price');

            $totalEarnedPointsDealer = $dealer->sales->sum('points_dealer');
            $redeemedPointsDealer = abs(RedeemedHistory::where('user_id', auth()->user()->id)->sum('points_amount'));
            $dealerAvailablePoints = $totalEarnedPointsDealer - $redeemedPointsDealer;
        }
        if(auth()->user()->role == "Client")
        {
            $customer = Client::where('user_id',auth()->user()->id)->first();
            $transactions_details = TransactionDetail::where('client_id',$customer->id)->orderBy('id','desc')->get();
            $total_sales = TransactionDetail::where('client_id',$customer->id)->sum('price');

            $totalEarnedPointsCustomer = $customer->transactions->sum('points_client');
            $redeemedPointsCustomer = abs(RedeemedHistory::where('user_id', auth()->user()->id)->sum('points_amount'));
            $customerAvailablePoints = $totalEarnedPointsCustomer - $redeemedPointsCustomer;
        }

        $total_sales = TransactionDetail::get()->sum(function($transaction) {
            return $transaction->price * $transaction->qty;
        });

        // Get chart data based on view type
        if ($viewType === 'monthly') {
            $chartData = $this->getDailyData($selectedYear, $selectedMonth);
        } else {
            $chartData = $this->getMonthlyData($selectedYear);
        }
        
        $categories = $chartData['categories'];
        $qty = $chartData['qty'];

        // Get available years and months for dropdowns
        $availableYears = $this->getAvailableYears();
        $availableMonths = $this->getAvailableMonths($selectedYear);

        $dealers = TransactionDetail::select(
            'dealer_id',
            DB::raw('SUM(points_dealer) as total_points'),
            DB::raw('MAX(date) as latest_transaction')
        )
        ->with('dealer')
        ->groupBy('dealer_id')
        ->orderByDesc('total_points')
        ->get();

        $top_customers = TransactionDetail::select(
            'client_id',
            DB::raw('SUM(points_client) as total_points'),
            DB::raw('MAX(created_at) as latest_transaction')
        )
        ->with('customer')
        ->whereNotNull('client_id')
        ->groupBy('client_id')
        ->orderByDesc('total_points')
        ->limit(10)
        ->get();

        $salesTrend = $this->calculateSalesTrend();
        $qtyTrend = $this->calculateQtyTrend();

        $threeDaysAgo = Carbon::now()->subDays(3)->toDateString();

        $dealers_inactive = Dealer::whereDoesntHave('sales', function ($q) use ($threeDaysAgo) {
            $q->where('created_at', '>=', $threeDaysAgo);
        })
        ->whereHas('sales')
        ->get()
        ->map(function($dealer) {
            $lastTransaction = TransactionDetail::where('dealer_id', $dealer->user_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $dealer->last_transaction_date = $lastTransaction ? $lastTransaction->created_at : null;
            $dealer->days_since_transaction = $lastTransaction 
                ? \Carbon\Carbon::parse($lastTransaction->created_at)->diffInDays(\Carbon\Carbon::now()) 
                : null;
            return $dealer;
        })
        ->sortByDesc('days_since_transaction');

        $mapData = $this->getPhilippineMapData();

        return view('area_distributor.home',
            array(
                'transactions' => $transactions,
                'transactions_details' => $transactions_details,
                'dealers' => $dealers,
                'categories' =>  $categories,
                'qty' =>  $qty,
                'customers' =>  $customers,
                'dealer' =>  $dealer,
                'customer' =>  $customer,
                'customers_less' =>  $customers_less,
                'total_sales' => $total_sales,
                'top_customers' => $top_customers,
                'sales_trend' => $salesTrend,
                'qty_trend' => $qtyTrend,
                'available_years' => $availableYears,
                'available_months' => $availableMonths,
                'selected_year' => $selectedYear,
                'selected_month' => $selectedMonth,
                'view_type' => $viewType,
                'dealer_available_points' => $dealerAvailablePoints ?? 0,
                'customer_available_points' => $customerAvailablePoints ?? 0,
                'dealers_inactive' => $dealers_inactive,
                'map_data' => $mapData,
                'adDealers' => $adDealers,
                'orders' => $orders,
                'products' => $products,
                'usedVoucherCount' => $usedVoucherCount,
                'unusedVoucherCount' => $unusedVoucherCount,
                'pendingOrdersCount' => $pendingOrdersCount,
                'months' => $months,
                'totals' => $totals,
                'product_sold' => $product_sold,
                'mapDealers' => $mapDealers,
                'avgProductSales' => $avgProductSales,
                'topDealers' => $topDealers,
                'stockLevels' => $stockLevels,
                'totalRefill' => $totalRefill,
                'totalProductsSoldQty' => $totalProductsSoldQty,
                'managedAreas' => $areas,
                'stockInventoryRows' => $stockInventoryRows,
                'orderTabs' => $orderTabs,
            )
        );
    }

    private function adOrderTabs($user, $adId)
    {
        $tabs = collect();

        if (!$adId) {
            return $tabs;
        }

        $decodedTypes = json_decode($user->type ?? '[]', true);
        $types = collect(is_array($decodedTypes) ? $decodedTypes : []);

        $areaTypes = optional($user->ad)->areas
            ? $user->ad->areas->pluck('project_type')
            : collect();

        $availableTypes = $types
            ->merge($areaTypes)
            ->filter()
            ->map(function ($type) {
                return strtolower(trim((string) $type));
            })
            ->unique();

        if ($availableTypes->isEmpty()) {
            $availableTypes = collect(['regular']);
        }

        $definitions = collect([
            [
                'key' => 'regular',
                'label' => 'Regular',
                'type' => 'regular',
                'connection' => null,
            ],
            [
                'key' => 'project_rise',
                'label' => 'Project Rise',
                'type' => 'project rise',
                'connection' => 'admin_crms',
            ],
            [
                'key' => 'project_genesis',
                'label' => 'Project Genesis',
                'type' => 'project genesis',
                'connection' => 'admin_crms2',
            ],
        ]);

        foreach ($definitions as $definition) {
            if (!$availableTypes->contains($definition['type'])) {
                continue;
            }

            $orders = $definition['connection']
                ? $this->remoteAdOrders($definition['connection'], $definition['label'], $adId)
                : OrderDetail::with(['dealer', 'adDealer', 'ad'])
                    ->where('ad_id', $adId)
                    ->orderByDesc('id')
                    ->get()
                    ->map(function ($order) use ($definition) {
                        $order->source_key = $definition['key'];
                        $order->source_label = $definition['label'];
                        $order->is_remote = false;

                        return $order;
                    });

            $tabs->push([
                'key' => $definition['key'],
                'label' => $definition['label'],
                'orders' => $orders,
            ]);
        }

        return $tabs->values();
    }

    private function remoteAdOrders($connection, $label, $adId)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('order_details') || !$schema->hasColumn('order_details', 'ad_id')) {
                return collect();
            }

            $query = DB::connection($connection)->table('order_details as od')
                ->select('od.*')
                ->where('od.ad_id', $adId);

            if ($schema->hasTable('dealers') && $schema->hasColumn('order_details', 'dealer_id')) {
                if ($schema->hasColumn('dealers', 'user_id')) {
                    $query->leftJoin('dealers as d', 'od.dealer_id', '=', 'd.user_id');
                } elseif ($schema->hasColumn('dealers', 'id')) {
                    $query->leftJoin('dealers as d', 'od.dealer_id', '=', 'd.id');
                }

                $dealerSelects = [];

                foreach (['name', 'area', 'dealer_type'] as $column) {
                    if ($schema->hasColumn('dealers', $column)) {
                        $dealerSelects[] = 'd.' . $column . ' as dealer_' . $column;
                    }
                }

                if (!empty($dealerSelects)) {
                    $query->addSelect($dealerSelects);
                }
            }

            if ($schema->hasColumn('order_details', 'deleted_at')) {
                $query->whereNull('od.deleted_at');
            }

            if ($schema->hasColumn('order_details', 'id')) {
                $query->orderByDesc('od.id');
            } elseif ($schema->hasColumn('order_details', 'created_at')) {
                $query->orderByDesc('od.created_at');
            }

            return $query->get()->map(function ($order) use ($connection, $label) {
                $order->source_key = $connection;
                $order->source_label = $label;
                $order->is_remote = true;
                $order->transaction_id = $order->transaction_id ?? $order->id ?? '-';
                $order->date = $order->date ?? $order->created_at ?? now();
                $order->qty = (float) ($order->qty ?? $order->quantity ?? 0);
                $amount = (float) ($order->amount ?? $order->total_amount ?? 0);
                $order->price = (float) ($order->price ?? ($order->qty > 0 && $amount > 0 ? $amount / $order->qty : $amount));
                $order->delivery_fee = (float) ($order->delivery_fee ?? 0);
                $order->item = $order->item ?? $order->product_name ?? $order->product ?? '-';
                $order->is_guest = $order->is_guest ?? false;
                $order->guest_name = $order->guest_name ?? null;
                $order->guest_phone = $order->guest_phone ?? null;
                $order->guest_email = $order->guest_email ?? null;
                $order->guest_authorized_territory = $order->guest_authorized_territory ?? null;
                $order->payment_method = $order->payment_method ?? '-';
                $order->delivery_type = $order->delivery_type ?? '-';
                $order->status = $order->status ?? '-';
                $order->points_dealer = $order->points_dealer ?? $order->points ?? 0;
                $order->dealer = (object) [
                    'name' => $order->dealer_name ?? ($order->dealer ?? ''),
                    'area' => $order->dealer_area ?? '',
                ];
                $order->adDealer = (object) [
                    'area' => $order->dealer_area ?? '',
                    'dealer_type' => $order->dealer_type ?? 'Project',
                ];

                return $order;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function remoteAdProducts($adUserId)
    {
        if (!$adUserId) {
            return collect();
        }

        return collect($this->crmConnections)->flatMap(function ($connection) use ($adUserId) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (!$schema->hasTable('products')) {
                    return collect();
                }

                $query = DB::connection($connection)->table('products');

                if ($schema->hasColumn('products', 'ad_user_id')) {
                    $query->where('ad_user_id', $adUserId);
                }

                if ($schema->hasColumn('products', 'status')) {
                    $query->where(function ($query) {
                        $query->where('status', 'Activate')->orWhereNull('status');
                    });
                }

                if ($schema->hasColumn('products', 'price')) {
                    $query->whereNotNull('price');
                }

                return $query->get()->map(function ($product) use ($connection) {
                    $product->source_key = $connection;

                    return $product;
                });
            } catch (\Exception $exception) {
                return collect();
            }
        })->values();
    }

    private function remoteAdDealers(array $areas)
    {
        $areas = collect($areas)->filter()->values();

        if ($areas->isEmpty()) {
            return collect();
        }

        return collect($this->crmConnections)->flatMap(function ($connection) use ($areas) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (!$schema->hasTable('dealers') || !$schema->hasColumn('dealers', 'area')) {
                    return collect();
                }

                $query = DB::connection($connection)->table('dealers')
                    ->whereIn('area', $areas);

                if ($schema->hasColumn('dealers', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                return $query->get()->map(function ($dealer) use ($connection) {
                    $dealer->source_key = $connection;
                    $dealer->email = $dealer->email ?? $dealer->email_address ?? null;
                    $dealer->location_region = $dealer->location_region ?? $dealer->region ?? null;
                    $dealer->latitude = $dealer->latitude ?? null;
                    $dealer->longitude = $dealer->longitude ?? null;
                    $dealer->user_id = $dealer->user_id ?? $dealer->id ?? null;

                    return $dealer;
                });
            } catch (\Exception $exception) {
                return collect();
            }
        })->values();
    }

    private function remoteAdPurchaseOrderStock($adId)
    {
        if (!$adId) {
            return collect();
        }

        return collect($this->crmConnections)->flatMap(function ($connection) use ($adId) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (
                    !$schema->hasTable('ad_purchase_order_items') ||
                    !$schema->hasTable('ad_purchase_orders') ||
                    !$schema->hasColumn('ad_purchase_orders', 'ad_id') ||
                    !$schema->hasColumn('ad_purchase_order_items', 'product_name') ||
                    !$schema->hasColumn('ad_purchase_order_items', 'qty')
                ) {
                    return collect();
                }

                $query = DB::connection($connection)->table('ad_purchase_order_items')
                    ->join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
                    ->where('ad_purchase_orders.ad_id', $adId);

                if ($schema->hasColumn('ad_purchase_orders', 'status')) {
                    $query->where('ad_purchase_orders.status', 'Completed');
                }

                return $query
                    ->select(
                        'ad_purchase_order_items.product_name as item_name',
                        $schema->hasColumn('ad_purchase_order_items', 'sku')
                            ? DB::raw('MAX(ad_purchase_order_items.sku) as sku')
                            : DB::raw('NULL as sku'),
                        DB::raw('SUM(ad_purchase_order_items.qty) as stock_qty')
                    )
                    ->groupBy('ad_purchase_order_items.product_name')
                    ->get();
            } catch (\Exception $exception) {
                return collect();
            }
        })->values();
    }

    private function localInventoryOutByItem($adId)
    {
        return InventoryTransfer::select('item_name', DB::raw('SUM(qty) as out_qty'))
            ->where('ad_id', $adId)
            ->where('movement_type', 'out')
            ->groupBy('item_name')
            ->pluck('out_qty', 'item_name');
    }

    private function remoteInventoryOutByItem($adId)
    {
        if (!$adId) {
            return collect();
        }

        return collect($this->crmConnections)->flatMap(function ($connection) use ($adId) {
            try {
                $schema = DB::connection($connection)->getSchemaBuilder();

                if (
                    !$schema->hasTable('inventory_transfers') ||
                    !$schema->hasColumn('inventory_transfers', 'ad_id') ||
                    !$schema->hasColumn('inventory_transfers', 'item_name') ||
                    !$schema->hasColumn('inventory_transfers', 'qty') ||
                    !$schema->hasColumn('inventory_transfers', 'movement_type')
                ) {
                    return collect();
                }

                return DB::connection($connection)->table('inventory_transfers')
                    ->select('item_name', DB::raw('SUM(qty) as out_qty'))
                    ->where('ad_id', $adId)
                    ->where('movement_type', 'out')
                    ->groupBy('item_name')
                    ->get();
            } catch (\Exception $exception) {
                return collect();
            }
        })
        ->groupBy('item_name')
        ->map(function ($rows) {
            return $rows->sum('out_qty');
        });
    }

    private function orderDate($order)
    {
        $date = $order->date ?? $order->created_at ?? null;

        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $exception) {
            return null;
        }
    }

    
}
