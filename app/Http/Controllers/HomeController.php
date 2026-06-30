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

    public function liveOverview()
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $thirtyDaysAgo = Carbon::today()->subDays(29);

        $todaySalesRow = TransactionDetail::whereDate('created_at', $today)
            ->selectRaw('COALESCE(SUM(price * qty), 0) as total')
            ->first();
        $monthSalesRow = TransactionDetail::where('created_at', '>=', $monthStart)
            ->selectRaw('COALESCE(SUM(price * qty), 0) as total')
            ->first();
        $todaySales = (float) optional($todaySalesRow)->total;
        $monthSales = (float) optional($monthSalesRow)->total;
        $todayTransactions = TransactionDetail::whereDate('created_at', $today)->count();
        $monthUnits = (float) TransactionDetail::where('created_at', '>=', $monthStart)->sum('qty');
        $activeDealers = TransactionDetail::where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('dealer_id')
            ->distinct()
            ->count('dealer_id');

        $pendingDealerOrders = OrderDetail::where('status', 'Pending')->count();
        $pendingAdOrders = AdPurchaseOrder::whereIn('status', ['Pending', 'For Verification'])->count();

        $dailyRows = TransactionDetail::selectRaw(
                'DATE(created_at) as sale_date, COALESCE(SUM(price * qty), 0) as sales, COALESCE(SUM(qty), 0) as units'
            )
            ->whereDate('created_at', '>=', $thirtyDaysAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $salesTrend = collect(range(0, 29))->map(function ($offset) use ($thirtyDaysAgo, $dailyRows) {
            $date = $thirtyDaysAgo->copy()->addDays($offset);
            $row = $dailyRows->get($date->toDateString());

            return [
                'date' => $date->format('M d'),
                'sales' => $row ? round((float) $row->sales, 2) : 0,
                'units' => $row ? (float) $row->units : 0,
            ];
        });

        $dealerOrderStatuses = OrderDetail::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $adOrderStatuses = AdPurchaseOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

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

        $recentTransactions = TransactionDetail::with(['dealer', 'customer'])
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'customer' => optional($transaction->customer)->name ?: 'Walk-in customer',
                    'dealer' => optional($transaction->dealer)->name ?: 'Unassigned',
                    'item' => $transaction->item ?: 'Product',
                    'amount' => round((float) $transaction->price * (float) $transaction->qty, 2),
                    'quantity' => (float) $transaction->qty,
                    'time' => optional($transaction->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'kpis' => [
                'today_sales' => $todaySales,
                'month_sales' => $monthSales,
                'today_transactions' => $todayTransactions,
                'month_units' => $monthUnits,
                'active_dealers' => $activeDealers,
                'pending_orders' => $pendingDealerOrders + $pendingAdOrders,
            ],
            'sales_trend' => $salesTrend,
            'order_statuses' => $orderStatuses,
            'recent_transactions' => $recentTransactions,
        ]);
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
        
        if (!is_numeric($year) || $year < 1900 || $year > Carbon::now()->year + 10) {
            return response()->json(['error' => 'Invalid year'], 400);
        }
        
        if ($month !== null && (!is_numeric($month) || $month < 1 || $month > 12)) {
            return response()->json(['error' => 'Invalid month'], 400);
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

        $areas = optional($user->ad)
            ->areas
            ? $user->ad->areas->pluck('area_name')->toArray()
            : [];

        $product_sold = OrderDetail::with('ad')
            ->where('ad_id', $adId)
            ->where('status', 'Completed')
            ->get();
        $products = Product::where('ad_user_id', $user->id)->where('status', 'Activate')->whereNotNull('price')->get();
        $adVouchers = Voucher::whereIn('name', array_filter([optional($user->ad)->store_code, $user->name]))->get();
        $usedVoucherCount = $adVouchers->filter(function ($voucher) {
            return (int) $voucher->used_count > 0;
        })->count();
        $unusedVoucherCount = $adVouchers->filter(function ($voucher) {
            return (int) $voucher->used_count === 0;
        })->count();
        // dd($products);
        $adUser = optional(auth()->user()->ad)->id;
        $pendingOrdersCount = OrderDetail::where('ad_id', $adUser)
            ->where('status', 'Pending')
            ->count();
        $dealer = "";
        $customer = "";
        $threeDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $user = auth()->user();
        $areas = $user->ad->areas->pluck('area_name')->toArray();
        
        $selectedYear = $request->get('year', Carbon::now()->year);
        $selectedMonth = $request->get('month', null);
        $viewType = $selectedMonth ? 'monthly' : 'yearly';
        
        // Monthly Sales Overview
        $sales = OrderDetail::select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw("SUM(price * qty) as total")
            )
            ->where('status', 'Completed')
            ->where('ad_id', $adId)
            ->whereYear('created_at', $selectedYear)
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->pluck('total', 'month');

        $months = [];
        $totals = [];

        for ($i = 1; $i <= 12; $i++) {

            $months[] = date("F", mktime(0, 0, 0, $i, 1));

            $totals[] = isset($sales[$i]) ? (float) $sales[$i] : 0;

        }

        // Map

        $dealers = Dealer::whereIn('area', $areas)->get();

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
        
        $adDealers = Dealer::whereIn('area', $areas)->get();
        $transactions_details = TransactionDetail::orderBy('id','desc')->get();
        $adDealerUserIds = $adDealers->pluck('user_id')->filter()->values();

        $adSalesTransactions = TransactionDetail::whereIn('dealer_id', $adDealerUserIds)->get();
        $totalProductsSoldQty = $adSalesTransactions->sum('qty');
        $soldItemCount = $adSalesTransactions->pluck('item')->filter()->unique()->count();
        $avgProductSales = $soldItemCount > 0 ? ($totalProductsSoldQty / $soldItemCount) : 0;
        $totalRefill = $adSalesTransactions->filter(function ($transaction) {
            return stripos((string) $transaction->item, 'refill') !== false;
        })->sum('qty');

        $topDealers = TransactionDetail::select(
                'dealer_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(price * qty) as total_sales'),
                DB::raw('MAX(date) as latest_transaction')
            )
            ->with('dealer')
            ->whereIn('dealer_id', $adDealerUserIds)
            ->groupBy('dealer_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        $orderedByItem = OrderDetail::select('item', DB::raw('SUM(qty) as ordered_qty'))
            ->where('ad_id', $adId)
            ->where('status', 'Completed')
            ->groupBy('item')
            ->pluck('ordered_qty', 'item');
      
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
            ->keyBy('item_name');   
         

        $outByItem = InventoryTransfer::select('item_name', DB::raw('SUM(qty) as out_qty'))
            ->where('ad_id', $adId)
            ->where('movement_type', 'out')
            ->groupBy('item_name')
            ->pluck('out_qty', 'item_name');

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
        $last30DaysSoldByItem = OrderDetail::select('item', DB::raw('SUM(qty) as sold_qty'))
            ->whereIn('dealer_id', $adDealerUserIds)
            ->whereDate('date', '>=', $thirtyDaysAgo)
            ->groupBy('item')
            ->pluck('sold_qty', 'item');

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
                'connection' => 'admin_crms2',
            ],
            [
                'key' => 'project_genesis',
                'label' => 'Project Genesis',
                'type' => 'project genesis',
                'connection' => 'admin_crms',
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

    
}
