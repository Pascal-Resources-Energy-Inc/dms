<?php

namespace App\Http\Controllers;
use App\TransactionDetail;
use App\OrderDetail;
use App\Item;
use App\Client;
use App\Dealer;
use App\AreaAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use RealRashid\SweetAlert\Facades\Alert;
class TransactionController extends Controller
{
    //

    public function index(Request $request)
    {
        $customers = Client::where('status', 'Active')->whereHas('serial')->get();
        $items = Item::get();
        $dealers = Dealer::get();
         $transactions = [];
        //  dd(auth()->user());
        if(auth()->user()->role == "Admin")
        {
            $transactions = TransactionDetail::get();
        }
        elseif(auth()->user()->role == "Dealer")
        {
            $transactions = TransactionDetail::where('dealer_id',auth()->user()->id)->get();
        }
        return view('transactions',
            array(
                'transactions' => $transactions,
                'items' => $items,
                'customers' => $customers,
                'dealers' => $dealers,
            )
        );
    }

    public function adTransactions(Request $request)
    {
        $user = auth()->user();
        $centers = optional($user->ad)
            ->areas
            ? $user->ad->areas->pluck('area_name')->toArray()
            : [];

        $dealers = Dealer::whereIn('area', $centers)->get();
        
        $dealerCenters = $dealers->pluck('center')->filter()->unique()->values()->toArray();
        $customers = Client::where('status', 'Active')
            ->whereHas('serial')
            ->when(!empty($dealerCenters), function ($q) use ($dealerCenters) {
                $q->whereIn('center', $dealerCenters);
            })
            ->get();
        $items = Item::get();

        $adUser = optional(auth()->user()->ad)->id;
        $pendingOrdersCount = OrderDetail::where('ad_id', $adUser)
            ->where('status', 'Pending')
            ->count();
        
        $transactions = [];

        $transactions = TransactionDetail::whereHas('adDealer', function($q) use ($centers) {
            $q->whereIn('area', $centers);
        })->get();

        return view('area_distributor.transactions',
            array(
                'transactions' => $transactions,
                'items' => $items,
                'customers' => $customers,
                'dealers' => $dealers,
                'pendingOrdersCount' => $pendingOrdersCount
            )
        );
    }

    public function customerAds(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['Area Distributor', 'Admin'], true)) {
            abort(403);
        }

        $projects = collect(['Regular', 'Project Rise', 'Project Genesis']);
        $selectedProject = $projects->contains($request->input('project'))
            ? $request->input('project')
            : 'Regular';

        if ($selectedProject !== 'Regular') {
            $connection = $selectedProject === 'Project Rise' ? 'admin_crms' : 'admin_crms2';
            $allowedAreas = $user->role === 'Area Distributor'
                ? AreaAd::where('ad_id', optional($user->ad)->id)
                    ->where('project_type', $selectedProject)
                    ->pluck('area_name')
                    ->filter()
                    ->unique()
                    ->values()
                : collect();
            $remoteData = $this->crmCustomerTransactions($request, $connection, $allowedAreas);

            return view('area_distributor.customer_transactions', [
                'transactions' => $remoteData['transactions'],
                'items' => collect(),
                'customers' => $remoteData['customers'],
                'dealers' => $remoteData['dealers'],
                'projects' => $projects,
                'selectedProject' => $selectedProject,
                'pendingOrdersCount' => $this->pendingAdPurchaseOrdersCount($user),
            ]);
        }

        $areasQuery = AreaAd::query();

        if ($user->role === 'Area Distributor') {
            $areasQuery->where('ad_id', optional($user->ad)->id);
        }

        $areas = $areasQuery->get(['project_type', 'area_name']);
        $assignedAreas = $areas->pluck('area_name')->filter()->unique()->values();
        $projectAreas = $areas->where('project_type', $selectedProject)
            ->pluck('area_name')
            ->filter()
            ->unique()
            ->values();
        $regularAreas = $areas->filter(function ($area) {
                return blank($area->project_type) || $area->project_type === 'Regular';
            })
            ->pluck('area_name')
            ->filter()
            ->unique()
            ->values();

        $applyProjectFilter = function ($query) use ($user, $selectedProject, $assignedAreas, $projectAreas, $regularAreas) {
            if ($user->role === 'Area Distributor') {
                if ($assignedAreas->isEmpty()) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('area', $assignedAreas);
            }

            if ($selectedProject === 'Regular') {
                $query->where(function ($dealerQuery) use ($regularAreas) {
                    $dealerQuery->where('dealer_type', 'Regular');

                    if ($regularAreas->isNotEmpty()) {
                        $dealerQuery->orWhereIn('area', $regularAreas);
                    }
                });

                return;
            }

            if ($projectAreas->isEmpty()) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereIn('area', $projectAreas);
        };

        $dealersQuery = Dealer::query();
        $applyProjectFilter($dealersQuery);

        $dealers = $dealersQuery->orderBy('name')->get();

        $dealerCenters = $dealers->pluck('center')->filter()->unique()->values()->toArray();
        $customersQuery = Client::where('status', 'Active')->whereHas('serial');

        if (!empty($dealerCenters)) {
            $customersQuery->whereIn('center', $dealerCenters);
        } else {
            $customersQuery->whereRaw('1 = 0');
        }

        $customers = $customersQuery->orderBy('name')->get();
        $items = Item::orderBy('item')->get();

        $transactionsQuery = TransactionDetail::with(['customer', 'dealer', 'adDealer'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $transactionsQuery->whereHas('adDealer', $applyProjectFilter);

        if ($request->filled('dealer')) {
            $transactionsQuery->where('dealer_id', $request->input('dealer'));
        }

        if ($request->filled('customer')) {
            $transactionsQuery->where('client_id', $request->input('customer'));
        }

        if ($request->filled('date_from')) {
            $transactionsQuery->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $transactionsQuery->whereDate('date', '<=', $request->input('date_to'));
        }

        $transactions = $transactionsQuery->get();

        $pendingOrdersCount = $this->pendingAdPurchaseOrdersCount($user);

        return view('area_distributor.customer_transactions', array(
            'transactions' => $transactions,
            'items' => $items,
            'customers' => $customers,
            'dealers' => $dealers,
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'pendingOrdersCount' => $pendingOrdersCount
        ));
    }

    private function crmCustomerTransactions(Request $request, $connection, $allowedAreas)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (!$schema->hasTable('transaction_details') || !$schema->hasTable('dealers')) {
                return ['transactions' => collect(), 'customers' => collect(), 'dealers' => collect()];
            }

            $transactionTable = 'transaction_details';
            $dealerColumn = $this->firstExistingColumn($schema, $transactionTable, ['dealer_id', 'user_id']);
            $customerColumn = $this->firstExistingColumn($schema, $transactionTable, ['client_id', 'customer_id']);

            if (!$dealerColumn || !$customerColumn) {
                return ['transactions' => collect(), 'customers' => collect(), 'dealers' => collect()];
            }

            $dealerQuery = DB::connection($connection)->table('dealers');

            if ($schema->hasColumn('dealers', 'deleted_at')) {
                $dealerQuery->whereNull('deleted_at');
            }

            if (auth()->user()->role === 'Area Distributor') {
                if ($allowedAreas->isEmpty() || !$schema->hasColumn('dealers', 'area')) {
                    return ['transactions' => collect(), 'customers' => collect(), 'dealers' => collect()];
                }

                $dealerQuery->whereIn('area', $allowedAreas);
            }

            if ($request->filled('dealer')) {
                $dealerQuery->where(function ($query) use ($request, $schema) {
                    $query->where('id', $request->input('dealer'));

                    if ($schema->hasColumn('dealers', 'user_id')) {
                        $query->orWhere('user_id', $request->input('dealer'));
                    }
                });
            }

            $dealers = $dealerQuery->get()->map(function ($dealer) {
                $dealer->user_id = $dealer->user_id ?? $dealer->id;
                $dealer->name = $dealer->name ?? '-';

                return $dealer;
            })->values();
            $dealerIds = $dealers->flatMap(function ($dealer) {
                return [$dealer->id ?? null, $dealer->user_id ?? null];
            })->filter()->unique()->values();

            if ($dealerIds->isEmpty()) {
                return ['transactions' => collect(), 'customers' => collect(), 'dealers' => $dealers];
            }

            $transactionsQuery = DB::connection($connection)->table($transactionTable)
                ->whereIn($dealerColumn, $dealerIds);

            if ($schema->hasColumn($transactionTable, 'deleted_at')) {
                $transactionsQuery->whereNull('deleted_at');
            }

            if ($request->filled('customer')) {
                $transactionsQuery->where($customerColumn, $request->input('customer'));
            }

            $dateColumn = $this->firstExistingColumn($schema, $transactionTable, ['date', 'created_at']);

            if ($dateColumn && $request->filled('date_from')) {
                $transactionsQuery->whereDate($dateColumn, '>=', $request->input('date_from'));
            }

            if ($dateColumn && $request->filled('date_to')) {
                $transactionsQuery->whereDate($dateColumn, '<=', $request->input('date_to'));
            }

            if ($dateColumn) {
                $transactionsQuery->orderBy($dateColumn, 'desc');
            }

            if ($schema->hasColumn($transactionTable, 'id')) {
                $transactionsQuery->orderBy('id', 'desc');
            }

            $transactions = $transactionsQuery->get();
            $customerTable = $schema->hasTable('clients') ? 'clients' : ($schema->hasTable('customers') ? 'customers' : null);
            $customerIds = $transactions->pluck($customerColumn)->filter()->unique()->values();
            $customers = collect();

            if ($customerTable && $customerIds->isNotEmpty()) {
                $customerQuery = DB::connection($connection)->table($customerTable)->whereIn('id', $customerIds);

                if ($schema->hasColumn($customerTable, 'deleted_at')) {
                    $customerQuery->whereNull('deleted_at');
                }

                $customers = $customerQuery->get()->map(function ($customer) {
                    $customer->name = $customer->name ?? trim(collect([
                        $customer->first_name ?? null,
                        $customer->last_name ?? null,
                    ])->filter()->implode(' ')) ?: '-';

                    return $customer;
                })->values();
            }

            $dealersById = $dealers->flatMap(function ($dealer) {
                return collect([$dealer->id ?? null, $dealer->user_id ?? null])
                    ->filter()
                    ->mapWithKeys(function ($id) use ($dealer) {
                        return [$id => $dealer];
                    });
            });
            $customersById = $customers->keyBy('id');

            $transactions = $transactions->map(function ($transaction) use ($dealerColumn, $customerColumn, $dealersById, $customersById) {
                $quantity = (float) ($transaction->qty ?? $transaction->quantity ?? 0);
                $amount = (float) ($transaction->amount ?? $transaction->total_amount ?? 0);

                $transaction->dealer = $dealersById->get($transaction->{$dealerColumn}) ?: (object) ['name' => '-'];
                $transaction->customer = $customersById->get($transaction->{$customerColumn}) ?: (object) ['name' => '-'];
                $transaction->date = $transaction->date ?? ($transaction->created_at ?? null);
                $transaction->item = $transaction->item ?? ($transaction->product ?? ($transaction->product_name ?? '-'));
                $transaction->qty = $quantity;
                $transaction->price = $transaction->price ?? ($quantity > 0 && $amount > 0 ? $amount / $quantity : 0);

                return $transaction;
            })->values();

            return compact('transactions', 'customers', 'dealers');
        } catch (\Exception $exception) {
            return ['transactions' => collect(), 'customers' => collect(), 'dealers' => collect()];
        }
    }

    private function firstExistingColumn($schema, $table, array $columns)
    {
        return collect($columns)->first(function ($column) use ($schema, $table) {
            return $schema->hasColumn($table, $column);
        });
    }

    private function pendingAdPurchaseOrdersCount($user)
    {
        return OrderDetail::where('ad_id', optional($user->ad)->id)
            ->where('status', 'Pending')
            ->count();
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $item = Item::findOrfail($request->item_id);
        
        $transaction = new TransactionDetail;
        $transaction->item = $item->item;
        $transaction->points_dealer = $item->dealer_points * $request->qty;
        $transaction->points_client = $item->customer_points * $request->qty;
        $transaction->item_description = $item->item_description;
        $transaction->qty = $request->qty;
        $transaction->price = $item->price;
        $transaction->client_id = $request->customer_id;
        $transaction->date = date('Y-m-d');
        $transaction->dealer_id = auth()->user()->id;
        $transaction->created_by = auth()->user()->id;
        $transaction->save();


        Alert::success('Successfully Save')->persistent('Dismiss');
        return back();
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'dealer' => 'required|integer',
            'customer_id' => 'required|integer',
            'item_id' => 'required|integer',
            'qty' => 'required|numeric|min:1',
            'date' => 'nullable|date',
        ]);

        $user = auth()->user();
        $areas = optional($user->ad)
            ->areas
            ? $user->ad->areas->pluck('area_name')->toArray()
            : [];

        $dealer = Dealer::where('user_id', $request->dealer)
            ->whereIn('area', $areas)
            ->firstOrFail();

        $customer = Client::where('status', 'Active')
            ->whereHas('serial')
            ->where('id', $request->customer_id)
            ->where('center', $dealer->center)
            ->firstOrFail();

        $item = Item::findOrFail($request->item_id);

        $transaction = new TransactionDetail;
        $transaction->item = $item->item;
        $transaction->points_dealer = $item->dealer_points * $request->qty;
        $transaction->points_client = $item->customer_points * $request->qty;
        $transaction->item_description = $item->item_description;
        $transaction->qty = $request->qty;
        $transaction->price = $item->price;
        $transaction->client_id = $customer->id;
        $transaction->dealer_id = $dealer->user_id;
        $transaction->date = $request->date ?: date('Y-m-d');
        $transaction->created_by = $user->id;
        $transaction->save();

        Alert::success('Successfully Save')->persistent('Dismiss');
        return back();
    }
    
    public function storeAdmin(Request $request)
    {
        // dd($request->all());
        $item = Item::findOrfail($request->item_id);


        $transaction = new TransactionDetail;
        $transaction->item = $item->item;
        $transaction->points_dealer = $item->dealer_points * $request->qty;
        $transaction->points_client = $item->customer_points * $request->qty;
        $transaction->item_description = $item->item_description;
        $transaction->qty = $request->qty;
        $transaction->price = $item->price;
        $transaction->client_id = $request->customer_id;
        $transaction->dealer_id = $request->dealer;
        $transaction->date = $request->date;
        $transaction->created_by = auth()->user()->id;
        $transaction->save();


         Alert::success('Successfully Save')->persistent('Dismiss');
        return back();
    }

  public function destroy($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid transaction ID'], 400);
            }

            $transaction = TransactionDetail::findOrFail($id);

            if (auth()->user()->role === "Dealer" && $transaction->dealer_id != auth()->user()->id) {
                return response()->json(['error' => 'Unauthorized to delete this transaction'], 403);
            }

            $transaction->delete();

            return response()->json([
                'success' => 'Transaction deleted successfully',
                'transaction_id' => $id
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Transaction not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete transaction'], 500);
        }
    }


   public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (!$ids || !is_array($ids) || empty($ids)) {
                return response()->json(['error' => 'No transactions selected'], 400);
            }

            $validIds = array_filter($ids, function ($id) {
                return is_numeric($id) && intval($id) > 0;
            });

            if (empty($validIds)) {
                return response()->json(['error' => 'Invalid transaction IDs provided'], 400);
            }

            $validIds = array_map('intval', $validIds);

            $query = TransactionDetail::whereIn('id', $validIds);

            if (auth()->user()->role === "Dealer") {
                $query->where('dealer_id', auth()->user()->id);
            }

            $transactions = $query->get();

            if ($transactions->isEmpty()) {
                return response()->json(['error' => 'No valid transactions found or unauthorized'], 403);
            }

            $deletedIds = $transactions->pluck('id')->toArray();
            $deletedCount = TransactionDetail::whereIn('id', $deletedIds)->delete();

            return response()->json([
                'success' => "Successfully deleted {$deletedCount} transaction(s)",
                'deleted_count' => $deletedCount,
                'deleted_ids' => $deletedIds
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete transactions'], 500);
        }
    }


       
}
