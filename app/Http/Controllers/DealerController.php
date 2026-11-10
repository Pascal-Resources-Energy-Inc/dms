<?php

namespace App\Http\Controllers;
use RealRashid\SweetAlert\Facades\Alert;
use App\User;
use App\Dealer;
use App\MegaDealer;
use App\Center;
use App\AreaDistributor;
use App\TransactionDetail;
use App\Item;
use App\Area;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class DealerController extends Controller
{
    //
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->role === 'Admin';
        $items = Item::select('item')->get(); // master list of items
        $centers = Center::get();
        $areas = Area::with('areaAd.distributor')->get();
        
        $adminCrmDealers = collect();
        $adminCrm2Dealers = collect();
        $adminRegularDealers = collect();

        if ($isAdmin) {
            $adminCrmDealers = $this->crmDealers('admin_crms', 'Admin CRM 1');
            $adminCrm2Dealers = $this->crmDealers('admin_crms2', 'Admin CRM 2');
            $adminRegularDealers = Dealer::with(['user', 'orders', 'sales'])
                ->where(function ($query) {
                    $query->where('dealer_type', 'Regular')
                        ->orWhere('dealer_type', 'regular');
                })
                ->get()
                ->map(function ($dealer) {
                    $dealer->source = 'Regular';
                    $dealer->source_label = 'Regular';
                    $this->applyLocalDealerMetrics($dealer);

                    return $dealer;
                });
            $dealers = $adminCrmDealers
                ->merge($adminCrm2Dealers)
                ->merge($adminRegularDealers)
                ->values();
        } else {
            $dealers = Dealer::with(['user', 'orders', 'sales'])->get()
                ->map(function ($dealer) {
                    $this->applyLocalDealerMetrics($dealer);

                    return $dealer;
                });
        }

        $activeDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Active') === 0;
        })->count();

        $inactiveDealers = $dealers->filter(function ($dealer) {
            return strcasecmp((string) $dealer->status, 'Inactive') === 0;
        })->count();

        return view('dealers',
            array(
                'dealers' => $dealers,
                'adminCrmDealers' => $adminCrmDealers,
                'adminCrm2Dealers' => $adminCrm2Dealers,
                'adminRegularDealers' => $adminRegularDealers,
                'activeDealers' => $activeDealers,
                'inactiveDealers' => $inactiveDealers,
                'items' => $items,
                'centers' => $centers,
                'areas' => $areas

            )
        );
    }

    private function crmDealers($connection, $label)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (! $schema->hasTable('dealers')) {
                return collect();
            }

            $query = DB::connection($connection)->table('dealers');

            if ($schema->hasColumn('dealers', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if ($schema->hasColumn('dealers', 'id')) {
                $query->orderByDesc('id');
            } elseif ($schema->hasColumn('dealers', 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->get()
                ->map(function ($dealer) use ($connection, $label) {
                    $dealer = $this->normalizeCrmDealer($dealer, $connection, $label);
                    $this->applyCrmDealerMetrics($dealer, $connection);

                    return $dealer;
                });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function normalizeCrmDealer($dealer, $connection, $label)
    {
        $location = collect([
            $dealer->street_address ?? null,
            $dealer->location_barangay ?? null,
            $dealer->location_city ?? null,
            $dealer->location_province ?? null,
            $dealer->location_region ?? null,
        ])->filter()->implode(', ');

        $dealer->source = $connection;
        $dealer->source_label = $label;
        $dealer->dealer_reference = $dealer->dealer_reference ?? ('CRM-' . ($dealer->id ?? ''));
        $dealer->dealer_type = $dealer->dealer_type ?? 'Project';
        $dealer->name = $dealer->name ?? '-';
        $dealer->avatar = $dealer->avatar ?? null;
        $dealer->store_name = $dealer->store_name ?? '-';
        $dealer->store_type = $dealer->store_type ?? '-';
        $dealer->email_address = $dealer->email_address ?? ($dealer->email ?? '-');
        $dealer->facebook = $dealer->facebook ?? '-';
        $dealer->location_region = $dealer->location_region ?? '-';
        $dealer->location_province = $dealer->location_province ?? '-';
        $dealer->location_city = $dealer->location_city ?? '-';
        $dealer->location_barangay = $dealer->location_barangay ?? '-';
        $dealer->number = $dealer->number ?? ($dealer->contact_number ?? '-');
        $dealer->address = $dealer->address ?? ($location ?: '-');
        $dealer->area = $dealer->area ?? ($dealer->sales_territory ?? '-');
        $dealer->status = ucfirst(strtolower((string) ($dealer->status ?? 'Active')));
        $dealer->valid_id = $dealer->valid_id ?? null;
        $dealer->valid_id_number = $dealer->valid_id_number ?? null;
        $dealer->signature = $dealer->signature ?? null;
        $dealer->orders = collect();
        $dealer->sales = collect();
        $dealer->stock_qty = (float) ($dealer->stock_qty ?? 0);
        $dealer->sold_qty = (float) ($dealer->sold_qty ?? 0);

        return $dealer;
    }

    private function applyLocalDealerMetrics($dealer)
    {
        $dealer->stock_qty = (float) $dealer->orders->sum('qty');
        $dealer->sold_qty = (float) $dealer->sales->sum('qty');
    }

    private function applyCrmDealerMetrics($dealer, $connection)
    {
        $stockQty = $this->crmDealerQtySum($connection, 'order_details', $dealer);
        $soldQty = $this->crmDealerQtySum($connection, 'transaction_details', $dealer);

        $dealer->stock_qty = $stockQty;
        $dealer->sold_qty = $soldQty;
        $dealer->orders = collect([(object) ['qty' => $stockQty]]);
        $dealer->sales = collect([(object) ['qty' => $soldQty]]);
    }

    private function crmDealerQtySum($connection, $table, $dealer)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable($table)) {
                return 0;
            }

            $qtyColumn = collect(['qty', 'quantity'])
                ->first(function ($column) use ($schema, $table) {
                    return $schema->hasColumn($table, $column);
                });

            if (!$qtyColumn) {
                return 0;
            }

            $dealerIds = collect([
                    $dealer->user_id ?? null,
                    $dealer->dealer_id ?? null,
                    $dealer->id ?? null,
                ])
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->unique()
                ->values()
                ->all();

            if (empty($dealerIds)) {
                return 0;
            }

            $query = DB::connection($connection)->table($table);

            if ($schema->hasColumn($table, 'dealer_id') && $schema->hasColumn($table, 'user_id')) {
                $query->where(function ($inner) use ($dealerIds) {
                    $inner->whereIn('dealer_id', $dealerIds)
                        ->orWhereIn('user_id', $dealerIds);
                });
            } elseif ($schema->hasColumn($table, 'dealer_id')) {
                $query->whereIn('dealer_id', $dealerIds);
            } elseif ($schema->hasColumn($table, 'user_id')) {
                $query->whereIn('user_id', $dealerIds);
            } else {
                return 0;
            }

            if ($schema->hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            return (float) $query->sum($qtyColumn);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    private function crmTransactions($connection, $dealer, array $megaDealerMetadata = null)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (! $schema->hasTable('transaction_details')) {
                return collect();
            }

            $query = DB::connection($connection)->table('transaction_details');
            $dealerIds = collect([$dealer->user_id ?? null, $dealer->id ?? null])->filter()->unique()->values()->all();

            if ($megaDealerMetadata !== null) {
                // Match the same identity used by Mega Dealer stock/sales totals.
                // Profile IDs can overlap another seller's user ID in project databases.
                if ($megaDealerMetadata['dedicated'] && $schema->hasColumn('transaction_details', 'mega_dealer_id')) {
                    $query->where('mega_dealer_id', $dealer->id);
                } elseif (!empty($dealer->user_id) && $schema->hasColumn('transaction_details', 'dealer_id')) {
                    $query->where('dealer_id', $dealer->user_id);
                } elseif (!empty($dealer->user_id) && $schema->hasColumn('transaction_details', 'user_id')) {
                    $query->where('user_id', $dealer->user_id);
                } else {
                    return collect();
                }
            } elseif ($schema->hasColumn('transaction_details', 'dealer_id')) {
                $query->whereIn('dealer_id', $dealerIds);
            } elseif ($schema->hasColumn('transaction_details', 'user_id')) {
                $query->whereIn('user_id', $dealerIds);
            } else {
                return collect();
            }

            if ($megaDealerMetadata !== null && $schema->hasColumn('transaction_details', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if ($schema->hasColumn('transaction_details', 'id')) {
                $query->orderByDesc('id');
            } elseif ($schema->hasColumn('transaction_details', 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->get()->map(function ($transaction) {
                $qty = (float) ($transaction->qty ?? $transaction->quantity ?? 0);
                $amount = (float) ($transaction->amount ?? $transaction->total_amount ?? 0);
                $price = $transaction->price ?? ($qty > 0 && $amount > 0 ? $amount / $qty : 0);

                $transaction->item = $transaction->item ?? ($transaction->product ?? ($transaction->product_name ?? '-'));
                $transaction->qty = $qty;
                $transaction->points_client = $transaction->points_client ?? ($transaction->points ?? 0);
                $transaction->price = $price;
                $transaction->created_at = $transaction->created_at ?? now();

                return $transaction;
            });
        } catch (\Exception $exception) {
            return collect();
        }
    }

    private function paginateCollection($items, Request $request, $perPage = 10)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = collect($items);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    public function viewAdminCrmDealer(Request $request, $source, $id)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);

        $connections = [
            'admin_crms' => 'Admin CRM 1',
            'admin_crms2' => 'Admin CRM 2',
        ];

        abort_unless(array_key_exists($source, $connections), 404);

        $schema = DB::connection($source)->getSchemaBuilder();
        abort_unless($schema->hasTable('dealers'), 404);

        $query = DB::connection($source)->table('dealers')->where('id', $id);

        if ($schema->hasColumn('dealers', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $dealer = $query->first();
        abort_unless($dealer, 404);

        $dealer = $this->normalizeCrmDealer($dealer, $source, $connections[$source]);
        $allTransactions = $this->crmTransactions($source, $dealer);
        $transactions = $this->paginateCollection($allTransactions, $request);
        $transactionStats = [
            'count' => $allTransactions->count(),
            'qty' => $allTransactions->sum('qty'),
            'amount' => $allTransactions->sum(function ($transaction) {
                return (float) $transaction->qty * (float) $transaction->price;
            }),
            'points' => $allTransactions->sum('points_client'),
        ];

        return view('dealer', [
            'dealer' => $dealer,
            'transactions' => $transactions,
            'transactionStats' => $transactionStats,
            'centers' => collect(),
            'areas' => collect(),
            'isRemoteDealer' => true,
        ]);
    }

    public function megaDealers(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->role === 'Admin' || $user->hasAreaDistributorAccess()), 403);
        $isAdmin = $user->role === 'Admin';
        $sourceStats = collect([
            'regular' => $this->megaDealerSourceStats('regular'),
            'admin_crms' => $this->megaDealerSourceStats('admin_crms'),
            'admin_crms2' => $this->megaDealerSourceStats('admin_crms2'),
        ]);
        $totalDealers = $sourceStats->sum('total');
        $activeDealers = $sourceStats->sum('active');
        $inactiveDealers = $sourceStats->sum('inactive');
        $megaDealerTabs = collect([
            ['key' => 'regular', 'label' => 'Regular', 'icon' => 'ti ti-building-store', 'count' => data_get($sourceStats, 'regular.total', 0)],
            ['key' => 'admin_crms', 'label' => 'Project Rise', 'icon' => 'ti ti-database', 'count' => data_get($sourceStats, 'admin_crms.total', 0)],
            ['key' => 'admin_crms2', 'label' => 'Project Genesis', 'icon' => 'ti ti-database-export', 'count' => data_get($sourceStats, 'admin_crms2.total', 0)],
        ]);

        if ($isAdmin) {
            $items = Item::select('item')->get();
            $centers = Center::get();
            $areas = Area::with('areaAd.distributor')->get();
        } else {
            $awards = $this->megaDealerAwardedAreas($user);
            $centers = Center::whereIn('name', $awards->pluck('area_name'))->orderBy('name')->get();
            $areas = $awards->map(function ($award) {
                return (object) ['name' => $award->area_name, 'areaAd' => $award];
            });
            $items = \App\Product::where('ad_user_id', $user->id)
                ->where('status', 'Activate')->orderBy('product_name')->get(['product_name']);
        }

        return view('dealers', [
            'dealers' => collect(),
            'totalDealers' => $totalDealers,
            'activeDealers' => $activeDealers,
            'inactiveDealers' => $inactiveDealers,
            'items' => $items,
            'centers' => $centers,
            'areas' => $areas,
            'dealerPageTitle' => 'Mega Dealers',
            'dealerSingularTitle' => 'Mega Dealer',
            'dealerRouteName' => $isAdmin ? 'mds' : 'md-ads',
            'dealerRole' => 'Mega Dealer',
            'dealerServerSide' => true,
            'dealerDataUrl' => route('mds.data'),
            'isMegaDealerPage' => true,
            'canCreateDealer' => true,
            'megaDealerTabs' => $megaDealerTabs,
        ]);
    }

    public function megaDealersData(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->role === 'Admin' || $user->hasAreaDistributorAccess()), 403);
        $source = $request->input('source', 'regular');
        abort_unless(in_array($source, ['regular', 'admin_crms', 'admin_crms2'], true), 404);

        if (in_array($source, ['admin_crms', 'admin_crms2'], true)) {
            $metadata = $this->crmMegaDealerMetadata($source);
            $query = $this->crmMegaDealerQuery($source, $metadata);

            if (!$query) {
                return DataTables::of(collect())->make(true);
            }

            return $this->formatMegaDealerDataTable(DataTables::of($query), $source, $metadata);
        }

        $completedOrders = $user->role === 'Admin' ? '' : " AND order_details.status = 'Completed'";
        $query = $this->megaDealerQueryForAuthUser()
            ->select('mega_dealers.*')
            ->selectRaw('(SELECT COALESCE(SUM(order_details.qty), 0) FROM order_details WHERE order_details.dealer_id = mega_dealers.user_id' . $completedOrders . ') AS stock_qty')
            ->selectRaw('(SELECT COALESCE(SUM(transaction_details.qty), 0) FROM transaction_details WHERE transaction_details.dealer_id = mega_dealers.user_id) AS sold_qty');

        return $this->formatMegaDealerDataTable(DataTables::of($query));
    }

    private function formatMegaDealerDataTable($dataTable, $source = null, array $metadata = null)
    {
        $table = $source ? 'remote_mega_dealers' : 'mega_dealers';
        $referenceColumn = $metadata['reference_column'] ?? 'dealer_reference';

        return $dataTable
            ->editColumn('dealer_reference', function ($dealer) {
                return '<span class="dealer-ref">' . e(strtoupper($dealer->dealer_reference ?: '-')) . '</span>';
            })
            ->editColumn('name', function ($dealer) use ($source) {
                $url = $source
                    ? route('admin.crm.mega-dealer.view', ['source' => $source, 'id' => $dealer->id])
                    : route('mega-dealer.view', $dealer->id);

                return '<a href="' . e($url) . '" class="dealer-link">'
                    . e(strtoupper($dealer->name ?: '-')) . '</a>';
            })
            ->editColumn('store_name', function ($dealer) {
                return e(strtoupper($dealer->store_name ?: '-'));
            })
            ->editColumn('store_type', function ($dealer) {
                return e(strtoupper($dealer->store_type ?: '-'));
            })
            ->editColumn('number', function ($dealer) {
                return e(strtoupper($dealer->number ?: '-'));
            })
            ->editColumn('stock_qty', function ($dealer) {
                return '<span class="dealer-metric is-stock">' . number_format((float) $dealer->stock_qty) . '</span>';
            })
            ->editColumn('sold_qty', function ($dealer) {
                return '<span class="dealer-metric is-sold">' . number_format((float) $dealer->sold_qty) . '</span>';
            })
            ->editColumn('address', function ($dealer) {
                return '<div class="dealer-muted">' . e(strtoupper($dealer->address ?: '-')) . '</div>';
            })
            ->editColumn('area', function ($dealer) {
                return '<div class="dealer-muted">' . e(strtoupper($dealer->area ?: '-')) . '</div>';
            })
            ->editColumn('status', function ($dealer) {
                $active = strcasecmp((string) $dealer->status, 'Active') === 0;

                return '<span class="dealer-status ' . ($active ? 'is-active' : 'is-inactive') . '">'
                    . ($active ? 'Active' : e($dealer->status ?: 'Inactive')) . '</span>';
            })
            ->addColumn('action', function ($dealer) use ($source) {
                $url = $source
                    ? route('admin.crm.mega-dealer.view', ['source' => $source, 'id' => $dealer->id])
                    : route('mega-dealer.view', $dealer->id);

                return '<a href="' . e($url) . '" class="dealer-action-btn" title="View Mega Dealer">'
                    . '<i class="ti ti-eye"></i></a>';
            })
            ->filterColumn('dealer_reference', function ($query, $keyword) use ($table, $referenceColumn) {
                if ($referenceColumn) {
                    $query->where($table . '.' . $referenceColumn, 'like', '%' . $keyword . '%');
                } else {
                    $query->whereRaw("CONCAT('CRM-MD-', {$table}.id) LIKE ?", ['%' . $keyword . '%']);
                }
            })
            ->filterColumn('name', function ($query, $keyword) use ($table) {
                $query->where($table . '.name', 'like', '%' . $keyword . '%');
            })
            ->rawColumns([
                'dealer_reference', 'name', 'stock_qty', 'sold_qty',
                'address', 'area', 'status', 'action',
            ])
            ->make(true);
    }

    private function megaDealerSourceStats($source)
    {
        try {
            $query = $source === 'regular'
                ? $this->megaDealerQueryForAuthUser()
                : $this->crmMegaDealerQuery($source);

            if (!$query) {
                return ['total' => 0, 'active' => 0, 'inactive' => 0];
            }

            $statusColumn = $source === 'regular' ? 'mega_dealers.status' : 'remote_mega_dealers.status';

            return [
                'total' => (clone $query)->count(),
                'active' => (clone $query)->where($statusColumn, 'Active')->count(),
                'inactive' => (clone $query)->where($statusColumn, 'Inactive')->count(),
            ];
        } catch (\Throwable $exception) {
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    private function crmMegaDealerMetadata($connection)
    {
        if (!in_array($connection, ['admin_crms', 'admin_crms2'], true)) {
            return null;
        }

        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if ($schema->hasTable('mega_dealers')) {
                $table = 'mega_dealers';
                $dedicated = true;
            } elseif ($schema->hasTable('dealers')) {
                $table = 'dealers';
                $dedicated = false;
            } else {
                return null;
            }

            $referenceColumn = collect(['partner_code', 'dealer_reference'])
                ->first(function ($column) use ($schema, $table) {
                    return $schema->hasColumn($table, $column);
                });

            return [
                'table' => $table,
                'dedicated' => $dedicated,
                'reference_column' => $referenceColumn,
            ];
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function crmMegaDealerQuery($connection, array $metadata = null)
    {
        if (!in_array($connection, ['admin_crms', 'admin_crms2'], true)) {
            return null;
        }

        try {
            $metadata = $metadata ?: $this->crmMegaDealerMetadata($connection);

            if (!$metadata) {
                return null;
            }

            $schema = DB::connection($connection)->getSchemaBuilder();
            $table = $metadata['table'];
            $referenceColumn = $metadata['reference_column'];
            $alias = 'remote_mega_dealers';

            $query = DB::connection($connection)->table($table . ' as ' . $alias);

            if ($schema->hasColumn($table, 'deleted_at')) {
                $query->whereNull($alias . '.deleted_at');
            }

            $hasReference = (bool) $referenceColumn;
            $hasRoleUsers = $schema->hasTable('users')
                && $schema->hasColumn('users', 'role')
                && $schema->hasColumn($table, 'user_id');

            if (!$metadata['dedicated'] && !$hasReference && !$hasRoleUsers) {
                return null;
            }

            if (!$metadata['dedicated']) {
                $query->where(function ($megaQuery) use ($hasReference, $hasRoleUsers, $referenceColumn, $alias) {
                    if ($hasReference) {
                        $megaQuery->where($alias . '.' . $referenceColumn, 'like', '%MD%');
                    }

                    if ($hasRoleUsers) {
                        $method = $hasReference ? 'orWhereExists' : 'whereExists';
                        $megaQuery->{$method}(function ($userQuery) use ($alias) {
                            $userQuery->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.id', $alias . '.user_id')
                                ->where('users.role', 'Mega Dealer');
                        });
                    }
                });
            }

            $user = auth()->user();
            if (!$user || ($user->role !== 'Admin' && !$user->hasAreaDistributorAccess())) {
                return null;
            }
            if ($user->role !== 'Admin') {
                // A project without an area column cannot be safely scoped to a distributor.
                if (!$schema->hasColumn($table, 'area')) {
                    return null;
                }
                $areas = $this->megaDealerAwardedAreas($user)->pluck('area_name')->all();
                $query->whereIn($alias . '.area', $areas);
            }

            $stockExpression = '0';
            if ($schema->hasTable('order_details')) {
                $qtyColumn = $schema->hasColumn('order_details', 'qty') ? 'qty' : ($schema->hasColumn('order_details', 'quantity') ? 'quantity' : null);
                $completedOrders = $user->role !== 'Admin' && $schema->hasColumn('order_details', 'status')
                    ? " AND remote_orders.status = 'Completed'" : '';
                if ($qtyColumn && $metadata['dedicated'] && $schema->hasColumn('order_details', 'mega_dealer_id')) {
                    $stockExpression = '(SELECT COALESCE(SUM(remote_orders.' . $qtyColumn . '), 0) FROM order_details AS remote_orders WHERE remote_orders.mega_dealer_id = ' . $alias . '.id' . $completedOrders . ')';
                } elseif ($qtyColumn && $schema->hasColumn('order_details', 'dealer_id') && $schema->hasColumn($table, 'user_id')) {
                    $stockExpression = '(SELECT COALESCE(SUM(remote_orders.' . $qtyColumn . '), 0) FROM order_details AS remote_orders WHERE remote_orders.dealer_id = ' . $alias . '.user_id' . $completedOrders . ')';
                }
            }

            $soldExpression = '0';
            if ($schema->hasTable('transaction_details')) {
                $qtyColumn = $schema->hasColumn('transaction_details', 'qty') ? 'qty' : ($schema->hasColumn('transaction_details', 'quantity') ? 'quantity' : null);
                if ($qtyColumn && $metadata['dedicated'] && $schema->hasColumn('transaction_details', 'mega_dealer_id')) {
                    $soldExpression = '(SELECT COALESCE(SUM(remote_sales.' . $qtyColumn . '), 0) FROM transaction_details AS remote_sales WHERE remote_sales.mega_dealer_id = ' . $alias . '.id)';
                } elseif ($qtyColumn && $schema->hasColumn('transaction_details', 'dealer_id') && $schema->hasColumn($table, 'user_id')) {
                    $soldExpression = '(SELECT COALESCE(SUM(remote_sales.' . $qtyColumn . '), 0) FROM transaction_details AS remote_sales WHERE remote_sales.dealer_id = ' . $alias . '.user_id)';
                }
            }

            $query->select($alias . '.*');

            if ($referenceColumn !== 'dealer_reference') {
                $query->selectRaw($referenceColumn
                    ? $alias . '.' . $referenceColumn . ' AS dealer_reference'
                    : "CONCAT('CRM-MD-', {$alias}.id) AS dealer_reference");
            }

            foreach (['name', 'store_name', 'store_type', 'number', 'address', 'area', 'status'] as $column) {
                if (!$schema->hasColumn($table, $column)) {
                    $query->selectRaw('NULL AS ' . $column);
                }
            }

            return $query
                ->selectRaw($stockExpression . ' AS stock_qty')
                ->selectRaw($soldExpression . ' AS sold_qty');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function viewAdminCrmMegaDealer(Request $request, $source, $id)
    {
        $user = auth()->user();
        abort_unless($user && ($user->role === 'Admin' || $user->hasAreaDistributorAccess()), 403);
        abort_unless(in_array($source, ['admin_crms', 'admin_crms2'], true), 404);

        $metadata = $this->crmMegaDealerMetadata($source);
        $query = $this->crmMegaDealerQuery($source, $metadata);
        abort_unless($query && $metadata, 404);

        $dealer = $query->where('remote_mega_dealers.id', $id)->first();
        abort_unless($dealer, 404);

        $labels = [
            'admin_crms' => 'Project Rise',
            'admin_crms2' => 'Project Genesis',
        ];
        $dealer = $this->normalizeCrmDealer($dealer, $source, $labels[$source]);
        $allTransactions = $this->crmTransactions($source, $dealer, $metadata);
        $transactions = $this->paginateCollection($allTransactions, $request);
        $transactionStats = [
            'count' => $allTransactions->count(),
            'qty' => $allTransactions->sum('qty'),
            'amount' => $allTransactions->sum(function ($transaction) {
                return (float) $transaction->qty * (float) $transaction->price;
            }),
            'points' => $allTransactions->sum('points_client'),
        ];

        return view('dealer', [
            'dealer' => $dealer,
            'transactions' => $transactions,
            'transactionStats' => $transactionStats,
            'centers' => collect(),
            'areas' => collect(),
            'isRemoteDealer' => true,
            'partnerLabel' => 'Mega Dealer',
            'partnerListUrl' => $user->role === 'Admin' ? route('mds') : route('md-ads'),
            'canEditDealer' => false,
            'canDeleteMegaDealer' => false,
            'partnerRecordType' => 'remote_mega_dealer',
        ]);
    }

    private function megaDealerQueryForAuthUser()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $query = MegaDealer::query()
            ->whereHas('user', function ($userQuery) {
                $userQuery->where('role', 'Mega Dealer');
            });

        if ($user->role === 'Admin') {
            return $query;
        }

        abort_unless($user->hasAreaDistributorAccess(), 403);
        $areaNames = $this->megaDealerAwardedAreas($user)->pluck('area_name')->all();

        return $query->whereIn('mega_dealers.area', $areaNames);
    }

    private function megaDealerAwardedAreas($user)
    {
        $relation = $user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 'provincialDistributor' : 'ad';
        $user->loadMissing($relation . '.areas.distributor');
        $distributor = $user->{$relation};

        return $distributor ? $distributor->areas->filter(function ($award) {
            return filled($award->area_name);
        })->unique('area_name')->values() : collect();
    }

    public function destroyMegaDealer($id)
    {
        $dealer = MegaDealer::with('user')->findOrFail($id);
        $user = auth()->user();

        abort_unless(optional($dealer->user)->role === 'Mega Dealer', 404);
        abort_unless($this->canManageDealerForAuthUser($dealer, $user), 403);

        if ($dealer->orders()->exists() || $dealer->sales()->exists()) {
            Alert::warning(
                'Mega Dealer cannot be deleted',
                'This account has order or transaction history that must be retained.'
            );

            return redirect()->back();
        }

        try {
            DB::transaction(function () use ($dealer) {
                $linkedUser = $dealer->user;
                $dealer->delete();

                if ($linkedUser && $linkedUser->role === 'Mega Dealer') {
                    $linkedUser->delete();
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            Alert::error('Delete failed', 'The Mega Dealer is still connected to another record and was not deleted.');

            return redirect()->back();
        }

        Alert::success('Deleted', 'Mega Dealer deleted successfully.');

        return redirect()->route($user->role === 'Admin' ? 'mds' : 'md-ads');
    }
    
    public function show(Request $request)
    {
        return view('dashboard-dealer');
    }

    public function canManageDealerForAuthUser($dealer, $user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return false;
        }

        if (strtolower((string) $user->role) === 'admin') {
            return true;
        }

        if (!$user->hasAreaDistributorAccess()) {
            return false;
        }

        $dealer = ($dealer instanceof Dealer || $dealer instanceof MegaDealer)
            ? $dealer
            : Dealer::find($dealer);

        if (!$dealer) {
            return false;
        }

        if (strcasecmp((string) $dealer->dealer_type, 'Regular') !== 0) {
            return false;
        }

        if ($dealer instanceof MegaDealer) {
            return $this->megaDealerAwardedAreas($user)->contains('area_name', $dealer->area);
        }

        $ad = $user->relationLoaded('ad') ? $user->getRelation('ad') : $user->ad;

        if (!$ad) {
            return false;
        }

        $assignedAreas = $ad->relationLoaded('areas')
            ? $ad->getRelation('areas')
            : ($ad->areas ?? collect());

        if (!$assignedAreas instanceof \Illuminate\Support\Collection && !$assignedAreas instanceof \Illuminate\Database\Eloquent\Collection) {
            $assignedAreas = collect($assignedAreas);
        }

        $areaNames = $assignedAreas
            ->filter(function ($area) {
                return filled(optional($area)->area_name);
            })
            ->pluck('area_name')
            ->map(function ($areaName) {
                return trim((string) $areaName);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($areaNames)) {
            return false;
        }

        $dealerArea = trim((string) ($dealer->area ?? ''));

        return in_array($dealerArea, $areaNames, true);
    }

    public function newDealer(Request $request)
    {
        $dealerType = 'Regular';
        $partnerRole = $request->input('partner_role') === 'Mega Dealer' ? 'Mega Dealer' : 'Dealer';

        $request->validate([
            'dealer_type' => 'nullable|in:Regular',
            'partner_role' => 'nullable|in:Dealer,Mega Dealer',
            'spo' => 'nullable',
            'center' => 'nullable',
        ]);

        if (auth()->user()->hasAreaDistributorAccess()) {
            $assignedAreas = collect(optional(auth()->user()->ad)->areas)
                ->pluck('area_name')
                ->map(function ($area) {
                    return trim((string) $area);
                })
                ->filter()
                ->all();

            if (!in_array(trim((string) $request->area), $assignedAreas, true)) {
                abort(403, 'The selected sales territory is not assigned to you.');
            }
        }

        if ($this->dealerDuplicateExists(
            $request->first_name,
            $request->last_name,
            $request->mothers_name
        )) {
            $message = "Dealer with same First Name, Last Name, and Mother's Name already exists.";

            Alert::error('Duplicate dealer', $message)->persistent('Dismiss');

            return redirect()->back()
                ->withErrors(['dealer_duplicate' => $message])
                ->withInput();
        }

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        $user = new User;
        $user->name = $fullName;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->mothers_name = $request->mothers_name;
        $user->email = $request->email_address;
        $user->role = $partnerRole;
        $user->birthdate = $request->birthdate;
        $user->age = $request->age;
        $user->password = bcrypt('12345678');
        $user->save();

        $dealer = $partnerRole === 'Mega Dealer' ? new MegaDealer : new Dealer;
        $dealerTable = $dealer->getTable();
        $dealer->user_id = $user->id;
        $dealer->dealer_reference = $this->nextDealerReference($partnerRole === 'Mega Dealer' ? 'Mega Dealer' : $dealerType);
        $dealer->name = $fullName;
        if (Schema::hasColumn($dealerTable, 'dealer_type')) {
            $dealer->dealer_type = $dealerType;
        }
        $dealer->spo = $dealerType === 'Project' ? $request->spo : null;
        $dealer->email_address = $request->email_address;
        $dealer->number = $request->number;
        $dealer->facebook = $request->facebook;
        $dealer->address = $request->address;
        $dealer->location_region = $request->location_region;
        $dealer->location_province = $request->location_province;
        $dealer->location_city = $request->location_city;
        $dealer->location_barangay = $request->location_barangay;
        if (Schema::hasColumn($dealerTable, 'postal_code')) {
            $dealer->postal_code = $request->postal_code;
        }
        if (Schema::hasColumn($dealerTable, 'street_address')) {
            $dealer->street_address = $request->street_address;
        }
        $dealer->store_name = $request->store_name;
        $dealer->store_type = $request->store_type;
        $dealer->center = $dealerType === 'Project' ? $request->center : null;
        $dealer->area = $request->area;
        $dealer->latitude = $request->latitude;
        $dealer->longitude = $request->longitude;
        $dealer->status = "Active";
        $dealer->save();
        

        Alert::success($partnerRole . ' successfully encoded')->persistent('Dismiss');
        return redirect()->route($partnerRole === 'Mega Dealer' ? 'mega-dealer.view' : 'dealer.view', $dealer->id);
    }

    public function checkDuplicate(Request $request)
    {
        $exists = $this->dealerDuplicateExists(
            $request->first_name,
            $request->last_name,
            $request->mothers_name
        );

        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? "Dealer with same First Name, Last Name, and Mother's Name already exists."
                : null,
        ]);
    }

    private function dealerDuplicateExists($firstName, $lastName, $mothersName)
    {
        $firstName = trim((string) $firstName);
        $lastName = trim((string) $lastName);
        $mothersName = trim((string) $mothersName);

        if (!$firstName || !$lastName || !$mothersName) {
            return false;
        }

        return User::whereIn('role', ['Dealer', 'Mega Dealer'])
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower($firstName)])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower($lastName)])
            ->whereRaw('LOWER(TRIM(mothers_name)) = ?', [mb_strtolower($mothersName)])
            ->exists();
    }

    public function view(Request $request,$id)
    {
        $dealer = Dealer::with('user')->findOrfail($id);
        return $this->renderPartnerProfile($request, $dealer, false);
    }

    public function viewMegaDealer(Request $request, $id)
    {
        $dealer = MegaDealer::with('user')->findOrFail($id);
        return $this->renderPartnerProfile($request, $dealer, true);
    }

    private function renderPartnerProfile(Request $request, $dealer, $isMegaDealer)
    {
        $user = auth()->user();

        if ($user && $user->hasAreaDistributorAccess() && !$this->canManageDealerForAuthUser($dealer, $user)) {
            abort(403, 'You are not authorized to view this dealer.');
        }

        $transactionQuery = TransactionDetail::where('dealer_id',$dealer->user_id);
        $transactionRows = (clone $transactionQuery)->get();
        $transactionStats = [
            'count' => $transactionRows->count(),
            'qty' => $transactionRows->sum('qty'),
            'amount' => $transactionRows->sum(function ($transaction) {
                return (float) $transaction->qty * (float) $transaction->price;
            }),
            'points' => $transactionRows->sum('points_client'),
        ];
        $transactions = $transactionQuery->orderBy('id','desc')->paginate(10)->appends($request->query());
        $centers = Center::get();
        $areas = Area::with('areaAd.distributor')->get();
        // dd($dealer);
        return view('dealer',
            array(
                'dealer' => $dealer,
                'transactions' => $transactions,
                'transactionStats' => $transactionStats,
                'centers' => $centers,
                'areas' => $areas,
                'canEditDealer' => $this->canManageDealerForAuthUser($dealer, $user),
                'partnerLabel' => $isMegaDealer ? 'Mega Dealer' : 'Dealer',
                'partnerListUrl' => $isMegaDealer
                    ? ($user->role === 'Admin' ? route('mds') : route('md-ads'))
                    : ($user->role === 'Admin' ? route('dealers') : route('dealer-ads')),
                'canDeleteMegaDealer' => $isMegaDealer && $this->canManageDealerForAuthUser($dealer, $user),
                'partnerRecordType' => $isMegaDealer ? 'mega_dealer' : 'dealer',
            )
        );
    }
    public function changeAvatar(Request $request, $id)
    {
        $dealer = $this->partnerRecordFromRequest($request, $id);
        abort_unless($this->canManageDealerForAuthUser($dealer), 403);
        
        $imageData = $request->image_data;
        
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageType = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        } else {
            Alert::error('Invalid image format')->persistent('Dismiss');
            return back();
        }
        
        $imageData = base64_decode($imageData);
        
        if ($imageData === false) {
            Alert::error('Failed to decode image')->persistent('Dismiss');
            return back();
        }
        
        $directory = public_path('avatar-dealer');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fileName = 'avatar_dealer_' . $dealer->id . '_' . time() . '.png';
        $filePath = $directory . '/' . $fileName;
        
        if (file_put_contents($filePath, $imageData)) {
            if ($dealer->avatar && 
                $dealer->avatar !== url('design/assets/images/profile/user-1.png') && 
                file_exists(public_path(str_replace(url('/'), '', $dealer->avatar)))) {
                unlink(public_path(str_replace(url('/'), '', $dealer->avatar)));
            }
            
            $dealer->avatar = 'avatar-dealer/' . $fileName;
            $dealer->save();
            
            Alert::success('Successfully Uploaded')->persistent('Dismiss');
        } else {
            Alert::error('Failed to save image')->persistent('Dismiss');
        }
        
        return back();
    }

    public function uploadValidId(Request $request,$id)
    {
        // dd($request->all());
        $customer = $this->partnerRecordFromRequest($request, $id);
        abort_unless($this->canManageDealerForAuthUser($customer), 403);
        $customer->valid_id = $request->valid_id_type;
        $customer->valid_id_number = $request->id_number;

        $attachment = $request->file('id_file');
        $original_name = $attachment->getClientOriginalName();
        $name = time().'_'.$attachment->getClientOriginalName();
        $attachment->move(public_path().'/valid_ids/', $name);
        $file_name = '/valid_ids/'.$name;

        $customer->valid_file = $file_name;
        $customer->save();

        Alert::success('Successfully Uploaded')->persistent('Dismiss');
        return back();
    }
    
    public function contractSign(Request $request,$id)
    {
        // dd($request->all());

        $customer = $this->partnerRecordFromRequest($request, $id);
        abort_unless($this->canManageDealerForAuthUser($customer), 403);

        $attachment = $request->file('contract_signature');
        $original_name = $attachment->getClientOriginalName();
        $name = time().'_'.$attachment->getClientOriginalName();
        $attachment->move(public_path().'/signatures/', $name);
        $file_name = '/signatures/'.$name;
        $customer->signature = $file_name;

        $customer->save();

        Alert::success('Successfully Uploaded')->persistent('Dismiss');
        return redirect()->route(
            $customer instanceof MegaDealer ? 'mega-dealer.view' : 'dealer.view',
            $customer->id
        );
    }

    public function sign($id)
    {
        $dealer = Dealer::findOrfail($id);

        return view('signature_dealer',
        array(
        'dealer' => $dealer
        ));
    }

    public function update(Request $request, $id)
    {
        return $this->updatePartnerRecord($request, Dealer::with('user')->findOrFail($id), false);
    }

    public function updateMegaDealer(Request $request, $id)
    {
        return $this->updatePartnerRecord($request, MegaDealer::with('user')->findOrFail($id), true);
    }

    private function updatePartnerRecord(Request $request, $dealer, $isMegaDealer)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'Admin';
        $dealerTable = $dealer->getTable();

        if (!$this->canManageDealerForAuthUser($dealer, $user)) {
            Alert::error('Unauthorized', 'You are not authorized to update this dealer.');

            return redirect()->back();
        }
        $existingDealerType = Schema::hasColumn($dealerTable, 'dealer_type')
            ? ($dealer->dealer_type ?: 'Project')
            : 'Project';
        $dealerType = $isMegaDealer
            ? 'Regular'
            : ($isAdmin
            ? (strtolower((string) $request->dealer_type) === 'regular' ? 'Regular' : 'Project')
            : $existingDealerType);

        $request->validate([
            'dealer_type' => $isAdmin && !$isMegaDealer ? 'required|in:Project,Regular' : 'nullable',
            'spo' => $dealerType === 'Project' ? 'required|string|max:255' : 'nullable',
            'center' => $dealerType === 'Project' ? 'required|string|max:255' : 'nullable',
        ]);

        $fullName = trim(collect([
            $request->first_name,
            $request->middle_name,
            $request->last_name,
        ])->filter()->implode(' '));

        if ($dealer->user_id) {

            User::where('id', $dealer->user_id)->update([
                'name' => $fullName ?: $request->name,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
            ]);
        }

        $dealer->name = $fullName ?: $request->name;
        $dealer->number = $request->number;
        $dealer->address = $request->address;
        $dealer->street_address = $request->street_address;
        $dealer->store_name = $request->store_name;
        $dealer->store_type = $request->store_type;
        if ($isMegaDealer && !preg_match('/^MD\d{4}\d+$/i', (string) $dealer->dealer_reference)) {
            $dealer->dealer_reference = $this->nextDealerReference('Mega Dealer');
        } elseif ($isAdmin && strcasecmp($existingDealerType, $dealerType) !== 0) {
            $dealer->dealer_reference = $this->nextDealerReference($dealerType);
        }
        if (Schema::hasColumn($dealerTable, 'dealer_type')) {
            $dealer->dealer_type = $dealerType;
        }
        $dealer->spo = $dealerType === 'Project' ? $request->spo : null;
        $dealer->facebook = $request->facebook;
        $dealer->email_address = $request->email_address;
        $dealer->location_region = $request->location_region;
        $dealer->location_province = $request->location_province;
        $dealer->location_city = $request->location_city;
        $dealer->location_barangay = $request->location_barangay;
        $dealer->postal_code = $request->postal_code;
        $dealer->center = $dealerType === 'Project' ? $request->center : null;
        $dealer->area = $request->area;
        $dealer->latitude = $request->latitude;
        $dealer->longitude = $request->longitude;

        $dealer->save();

        Alert::success('Success', ($isMegaDealer ? 'Mega Dealer' : 'Dealer') . ' updated successfully!');
        return redirect()->back();
    }

    private function partnerRecordFromRequest(Request $request, $id)
    {
        return $request->input('partner_record_type') === 'mega_dealer'
            ? MegaDealer::findOrFail($id)
            : Dealer::findOrFail($id);
    }

    private function nextDealerReference($dealerType)
    {
        if (strcasecmp((string) $dealerType, 'Mega Dealer') === 0) {
            $year = date('Y');
            $prefix = 'MD' . $year;
            $padding = 4;
        } elseif (strcasecmp((string) $dealerType, 'Regular') === 0) {
            $year = date('Y');
            $prefix = 'DL' . $year;
            $padding = 4;
        } else {
            $prefix = 'PRD';
            $padding = 5;
        }

        $referenceModel = strcasecmp((string) $dealerType, 'Mega Dealer') === 0
            ? new MegaDealer
            : new Dealer;

        $latestSequence = $referenceModel->newQuery()
            ->where('dealer_reference', 'like', $prefix . '%')
            ->pluck('dealer_reference')
            ->map(function ($reference) use ($prefix) {
                $suffix = substr(strtoupper(trim((string) $reference)), strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?: 0;

        return $prefix . str_pad($latestSequence + 1, $padding, '0', STR_PAD_LEFT);
    }

    public function getZipCode1(Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'zipcode' => null,
                'message' => 'Missing coordinates'
            ]);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10
            ]);

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'headers' => [
                    'User-Agent' => 'LaravelApp/1.0 (zipcode lookup)'
                ],
                'query' => [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'addressdetails' => 1
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            $zipcode = null;

            if (!empty($data['address'])) {
                $address = $data['address'];

                $zipcode =
                    $address['postcode']
                    ?? $address['postal_code']
                    ?? $address['zip']
                    ?? null;
            }

            return response()->json([
                'success' => true,
                'zipcode' => $zipcode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'zipcode' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function ncrCities()
    {
        $cities = City::where('region', 'NCR')
            ->orderBy('name')
            ->get(['name']);

        return response()->json($cities);
    }
}
