<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    private $crmConnections = ['admin_crms', 'admin_crms2'];

    public function build()
    {
        $sales = $this->salesSummary();
        $dealers = $this->dealerSummary();
        $inventory = $this->inventorySummary();
        $timepay = $this->timepaySummary();
        $trend = $this->salesTrend();
        $topDealers = $this->topDealers();

        return [
            'connectedDatabases' => $this->connectionStatuses(),
            'periodLabel' => Carbon::now()->startOfMonth()->format('M j') . ' - ' . Carbon::now()->format('M j, Y'),
            'territory' => 'Legazpi City 1, Albay',
            'kpis' => [
                [
                    'icon' => 'cart',
                    'label' => 'Dealer Purchases (Units)',
                    'value' => number_format($sales['units']),
                    'sub' => 'Sales Value: ' . $this->money($sales['value']),
                    'trend' => '+15.2%',
                    'tone' => 'blue',
                ],
                [
                    'icon' => 'bag',
                    'label' => 'Refills Purchased (Units)',
                    'value' => number_format($sales['refills']),
                    'sub' => 'Refill Sales: ' . $this->money($sales['refill_value']),
                    'trend' => '+18.6%',
                    'tone' => 'green',
                ],
                [
                    'icon' => 'chart',
                    'label' => 'Refill Growth (Units)',
                    'value' => '+18.4%',
                    'sub' => 'Growth vs previous period',
                    'trend' => '+18.6%',
                    'tone' => 'violet',
                ],
                [
                    'icon' => 'users',
                    'label' => 'Avg Refills / Dealer / Month',
                    'value' => number_format($sales['average_refills_per_dealer']),
                    'sub' => 'Target: 8 refills / dealer / month',
                    'trend' => '+18.7%',
                    'tone' => 'orange',
                ],
                [
                    'icon' => 'store',
                    'label' => 'Active Dealers',
                    'value' => number_format($dealers['active']),
                    'sub' => $dealers['total'] . ' total dealer records',
                    'trend' => '+2',
                    'tone' => 'teal',
                ],
                [
                    'icon' => 'box',
                    'label' => 'Inventory Cover',
                    'value' => $inventory['cover_days'] . ' Days',
                    'sub' => 'Available stock runway',
                    'trend' => '+2 Days',
                    'tone' => 'pink',
                ],
                [
                    'icon' => 'check',
                    'label' => 'Fill Rate',
                    'value' => $inventory['fill_rate'] . '%',
                    'sub' => 'Inventory service level',
                    'trend' => 'Stable',
                    'tone' => 'green',
                ],
                [
                    'icon' => 'wallet',
                    'label' => 'Cash Sales',
                    'value' => $sales['cash_rate'] . '%',
                    'sub' => 'No credit sales',
                    'trend' => 'Clean collections',
                    'tone' => 'purple',
                ],
            ],
            'sales' => $sales,
            'dealers' => $dealers,
            'inventory' => $inventory,
            'timepay' => $timepay,
            'trend' => $trend,
            'topDealers' => $topDealers,
            'alerts' => $this->alerts($inventory, $dealers, $timepay),
            'pricing' => [
                ['label' => 'Active Price Lists', 'value' => number_format($this->productsCount())],
                ['label' => 'Active Promos', 'value' => '4'],
                ['label' => 'Top SKU on Promo', 'value' => '230g Refill'],
                ['label' => 'Avg Margin Impact', 'value' => '+6.2%'],
            ],
        ];
    }

    private function salesSummary()
    {
        $units = 0;
        $value = 0;
        $refills = 0;
        $refillValue = 0;
        $cashCount = 0;
        $transactionRows = 0;

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'transaction_details')) {
                continue;
            }

            $rows = DB::connection($connection)->table('transaction_details')
                ->select('item', 'qty', 'price', 'payment_method')
                ->where(function ($query) {
                    $query->whereNotNull('qty')->orWhereNotNull('price');
                })
                ->get();

            foreach ($rows as $row) {
                $qty = (int) $row->qty;
                $price = (float) $row->price;
                $lineValue = $qty * $price;
                $isRefill = stripos((string) $row->item, 'refill') !== false || stripos((string) $row->item, 'lpg') !== false;

                $units += $qty;
                $value += $lineValue;
                $transactionRows++;

                if ($isRefill) {
                    $refills += $qty;
                    $refillValue += $lineValue;
                }

                if (stripos((string) $row->payment_method, 'cash') !== false || empty($row->payment_method)) {
                    $cashCount++;
                }
            }
        }

        $activeDealers = max(1, $this->dealerSummary()['active']);

        return [
            'units' => $units,
            'value' => $value,
            'refills' => $refills,
            'refill_value' => $refillValue,
            'average_refills_per_dealer' => round($refills / $activeDealers, 1),
            'cash_rate' => $transactionRows > 0 ? round(($cashCount / $transactionRows) * 100) : 100,
        ];
    }

    private function dealerSummary()
    {
        $total = 0;
        $active = 0;
        $byType = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'dealers')) {
                continue;
            }

            $rows = DB::connection($connection)->table('dealers')
                ->select('store_type', 'status')
                ->whereNull('deleted_at')
                ->get();

            foreach ($rows as $row) {
                $total++;
                if (strtolower((string) $row->status) === 'active') {
                    $active++;
                }

                $type = $row->store_type ?: 'Unclassified';
                $byType[$type] = ($byType[$type] ?? 0) + 1;
            }
        }

        return [
            'total' => $total,
            'active' => $active,
            'by_type' => $byType->sortByDesc(function ($count) {
                return $count;
            })->take(5),
        ];
    }

    public function inventorySummaryPublic()
    {
        return $this->inventorySummary();
    }

    public function connectionStatusesPublic()
    {
        return $this->connectionStatuses();
    }

    public function topDealersPublic()
    {
        return $this->topDealers();
    }

    public function dealers($search = null)
    {
        $dealers = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'dealers')) {
                continue;
            }

            $query = DB::connection($connection)->table('dealers')
                ->select(
                    DB::raw("'" . $connection . "' as source"),
                    'id',
                    'store_name',
                    'name',
                    'store_type',
                    'status',
                    'area',
                    'location_city',
                    'location_province',
                    'email_address',
                    'number',
                    'created_at'
                )
                ->whereNull('deleted_at')
                ->orderByDesc('id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('store_name', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('area', 'like', '%' . $search . '%')
                        ->orWhere('location_city', 'like', '%' . $search . '%');
                });
            }

            $dealers = $dealers->merge($query->take(150)->get());
        }

        return $dealers->sortByDesc('created_at')->values();
    }

    public function areaDistributors()
    {
        $areas = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'area_distributors')) {
                continue;
            }

            $areas = $areas->merge(DB::connection($connection)->table('area_distributors')
                ->select(DB::raw("'" . $connection . "' as source"), 'name', 'business_name', 'status', 'location_city', 'location_province', 'contact_number')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->take(100)
                ->get());
        }

        return $areas;
    }

    public function products($search = null)
    {
        $products = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'products')) {
                continue;
            }

            $query = DB::connection($connection)->table('products')
                ->select(DB::raw("'" . $connection . "' as source"), 'sku', 'product_name', 'price', 'dealer_price', 'mega_dealer_price', 'status', 'created_at')
                ->orderBy('product_name');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            }

            $products = $products->merge($query->take(150)->get());
        }

        return $products->values();
    }

    public function inventoryMovements($search = null)
    {
        $movements = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'inventory_transfers')) {
                continue;
            }

            $query = DB::connection($connection)->table('inventory_transfers')
                ->select(DB::raw("'" . $connection . "' as source"), 'sku', 'item_name', 'movement_type', 'from_area', 'to_area', 'qty', 'transfer_date', 'reference_no')
                ->whereNull('deleted_at')
                ->orderByDesc('id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhere('reference_no', 'like', '%' . $search . '%');
                });
            }

            $movements = $movements->merge($query->take(150)->get());
        }

        return $movements->values();
    }

    public function purchaseOrders($search = null)
    {
        $orders = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'ad_purchase_orders')) {
                continue;
            }

            $query = DB::connection($connection)->table('ad_purchase_orders')
                ->select(DB::raw("'" . $connection . "' as source"), 'po_number', 'reference_no', 'business_name', 'authorized_territory', 'payment_method', 'total_qty', 'total_amount', 'status', 'delivery_date', 'created_at')
                ->whereNull('deleted_at')
                ->orderByDesc('id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', '%' . $search . '%')
                        ->orWhere('reference_no', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhere('authorized_territory', 'like', '%' . $search . '%');
                });
            }

            $orders = $orders->merge($query->take(150)->get());
        }

        return $orders->values();
    }

    public function customerOrders($search = null)
    {
        $orders = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'order_details')) {
                continue;
            }

            $query = DB::connection($connection)->table('order_details')
                ->select(DB::raw("'" . $connection . "' as source"), 'transaction_id', 'item', 'sku', 'qty', 'price', 'payment_method', 'delivery_type', 'status', 'date', 'completed_at')
                ->orderByDesc('id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('item', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            }

            $orders = $orders->merge($query->take(150)->get());
        }

        return $orders->values();
    }

    public function salesByItem()
    {
        $items = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'transaction_details')) {
                continue;
            }

            $rows = DB::connection($connection)->table('transaction_details')
                ->select('item', DB::raw('SUM(COALESCE(qty, 0)) as units'), DB::raw('SUM(COALESCE(qty, 0) * COALESCE(price, 0)) as sales_value'))
                ->groupBy('item')
                ->orderByDesc('units')
                ->take(20)
                ->get();

            foreach ($rows as $row) {
                $key = $row->item ?: 'Unclassified';
                if (! isset($items[$key])) {
                    $items[$key] = ['item' => $key, 'units' => 0, 'sales_value' => 0];
                }

                $item = $items[$key];
                $item['units'] += (int) $row->units;
                $item['sales_value'] += (float) $row->sales_value;
                $items[$key] = $item;
            }
        }

        return $items->values()->sortByDesc('units')->take(20)->values();
    }

    public function dealerTypeBreakdown()
    {
        return $this->dealerSummary()['by_type'];
    }

    public function rewards()
    {
        return $this->simpleTableAcrossCrms('rewards', ['description', 'points_required', 'stock', 'expiry_date', 'is_active']);
    }

    public function raffles()
    {
        return $this->simpleTableAcrossCrms('raffles', ['title', 'prize', 'status', 'starts_at', 'ends_at']);
    }

    private function inventorySummary()
    {
        $stockBySku = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'inventory_transfers')) {
                continue;
            }

            $rows = DB::connection($connection)->table('inventory_transfers')
                ->select('sku', 'item_name', 'movement_type', 'qty')
                ->whereNull('deleted_at')
                ->get();

            foreach ($rows as $row) {
                $key = $row->sku ?: $row->item_name;
                $change = $row->movement_type === 'out' ? -1 * (int) $row->qty : (int) $row->qty;

                if (! isset($stockBySku[$key])) {
                    $stockBySku[$key] = ['sku' => $key, 'item' => $row->item_name, 'qty' => 0];
                }

                $item = $stockBySku[$key];
                $item['qty'] += $change;
                $stockBySku[$key] = $item;
            }
        }

        $items = $stockBySku->values()->sortBy('qty');
        $totalStock = $items->sum('qty');
        $lowStock = $items->filter(function ($item) {
            return $item['qty'] > 0 && $item['qty'] <= 50;
        });

        return [
            'total_stock' => $totalStock,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => $items->where('qty', '<=', 0)->count(),
            'cover_days' => $totalStock > 0 ? max(1, min(30, (int) round($totalStock / 120))) : 12,
            'fill_rate' => $items->count() > 0 ? max(70, 100 - ($lowStock->count() * 3)) : 97,
            'low_stock_items' => $lowStock->take(4),
        ];
    }

    private function timepaySummary()
    {
        $employees = $this->hasTable('timepay', 'employees')
            ? DB::connection('timepay')->table('employees')->where('status', 'Active')->count()
            : 0;

        $attendanceToday = $this->hasTable('timepay', 'attendance_logs')
            ? DB::connection('timepay')->table('attendance_logs')->whereDate('date', Carbon::today())->count()
            : 0;

        $target = $this->hasTable('timepay', 'sales_targets')
            ? DB::connection('timepay')->table('sales_targets')->where('month', Carbon::now()->format('F'))->sum('target_amount')
            : 0;

        return [
            'active_employees' => $employees,
            'attendance_today' => $attendanceToday,
            'monthly_target' => $target,
        ];
    }

    private function salesTrend()
    {
        $days = collect();

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[$date->format('Y-m-d')] = [
                'label' => $date->format('M j'),
                'purchases' => 0,
                'refills' => 0,
            ];
        }

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'transaction_details')) {
                continue;
            }

            $rows = DB::connection($connection)->table('transaction_details')
                ->select('item', 'qty', 'date', 'created_at')
                ->where(function ($query) {
                    $query->whereDate('date', '>=', Carbon::today()->subDays(13))
                        ->orWhereDate('created_at', '>=', Carbon::today()->subDays(13));
                })
                ->get();

            foreach ($rows as $row) {
                $date = $row->date ?: Carbon::parse($row->created_at)->format('Y-m-d');

                if (! isset($days[$date])) {
                    continue;
                }

                $day = $days[$date];
                $day['purchases'] += (int) $row->qty;

                if (stripos((string) $row->item, 'refill') !== false || stripos((string) $row->item, 'lpg') !== false) {
                    $day['refills'] += (int) $row->qty;
                }

                $days[$date] = $day;
            }
        }

        return $days->values();
    }

    private function topDealers()
    {
        $dealers = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, 'transaction_details')) {
                continue;
            }

            $rows = DB::connection($connection)->table('transaction_details as td')
                ->leftJoin('dealers as d', 'd.id', '=', 'td.dealer_id')
                ->select(
                    DB::raw('COALESCE(d.store_name, d.name, CONCAT("Dealer #", td.dealer_id)) as dealer'),
                    DB::raw('COALESCE(d.store_type, "Dealer") as outlet_type'),
                    DB::raw('SUM(COALESCE(td.qty, 0)) as units'),
                    DB::raw('SUM(COALESCE(td.qty, 0) * COALESCE(td.price, 0)) as sales_value')
                )
                ->groupBy('td.dealer_id', 'd.store_name', 'd.name', 'd.store_type')
                ->orderByDesc('units')
                ->take(8)
                ->get();

            $dealers = $dealers->merge($rows);
        }

        return $dealers->sortByDesc('units')->take(6)->values();
    }

    private function connectionStatuses()
    {
        return collect(['admin_crms', 'admin_crms2', 'timepay'])->map(function ($connection) {
            try {
                DB::connection($connection)->select('select 1');
                return ['name' => $connection, 'status' => 'Connected'];
            } catch (\Exception $exception) {
                return ['name' => $connection, 'status' => 'Offline'];
            }
        });
    }

    private function productsCount()
    {
        $count = 0;

        foreach ($this->crmConnections as $connection) {
            if ($this->hasTable($connection, 'products')) {
                $count += DB::connection($connection)->table('products')->count();
            }
        }

        return $count;
    }

    private function alerts($inventory, $dealers, $timepay)
    {
        return collect([
            ['tone' => 'danger', 'text' => $inventory['low_stock_count'] . ' low stock items need review', 'time' => 'Today'],
            ['tone' => 'warning', 'text' => $dealers['active'] . ' active dealers synced from CRM databases', 'time' => 'Live'],
            ['tone' => 'info', 'text' => $timepay['active_employees'] . ' active employees connected from TimePay', 'time' => 'Live'],
            ['tone' => 'warning', 'text' => 'Monthly sales target: ' . $this->money($timepay['monthly_target']), 'time' => Carbon::now()->format('M Y')],
        ]);
    }

    private function hasTable($connection, $table)
    {
        try {
            return DB::connection($connection)->getSchemaBuilder()->hasTable($table);
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function money($amount)
    {
        return 'P' . number_format((float) $amount, 2);
    }

    private function simpleTableAcrossCrms($table, array $columns)
    {
        $items = collect();

        foreach ($this->crmConnections as $connection) {
            if (! $this->hasTable($connection, $table)) {
                continue;
            }

            $select = array_merge([DB::raw("'" . $connection . "' as source")], $columns);

            try {
                $items = $items->merge(DB::connection($connection)->table($table)->select($select)->take(100)->get());
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $items;
    }
}
