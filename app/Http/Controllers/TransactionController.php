<?php

namespace App\Http\Controllers;
use App\TransactionDetail;
use App\OrderDetail;
use App\Item;
use App\Client;
use App\Dealer;
use Illuminate\Http\Request;

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

        $centers = [];

        if ($user->role === 'Area Distributor' && optional($user->ad)->areas) {
            $centers = $user->ad->areas->pluck('area_name')->toArray();
        }

        $dealersQuery = Dealer::query();

        if (!empty($centers)) {
            $dealersQuery->whereIn('area', $centers);
        }

        $dealers = $dealersQuery->orderBy('name')->get();

        $dealerCenters = $dealers->pluck('center')->filter()->unique()->values()->toArray();
        $customersQuery = Client::where('status', 'Active')->whereHas('serial');

        if (!empty($dealerCenters)) {
            $customersQuery->whereIn('center', $dealerCenters);
        }

        $customers = $customersQuery->orderBy('name')->get();
        $items = Item::orderBy('item')->get();

        $transactionsQuery = TransactionDetail::with(['customer', 'dealer', 'adDealer'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($centers)) {
            $transactionsQuery->whereHas('adDealer', function ($query) use ($centers) {
                $query->whereIn('area', $centers);
            });
        }

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

        $adUser = optional($user->ad)->id;
        $pendingOrdersCount = OrderDetail::where('ad_id', $adUser)
            ->where('status', 'Pending')
            ->count();

        return view('area_distributor.customer_transactions', array(
            'transactions' => $transactions,
            'items' => $items,
            'customers' => $customers,
            'dealers' => $dealers,
            'pendingOrdersCount' => $pendingOrdersCount
        ));
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
