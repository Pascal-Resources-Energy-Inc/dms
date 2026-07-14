<?php

namespace App\Http\Controllers;

use App\Dealer;
use App\DealerStockRequest;
use App\OrderDetail;
use App\Product;
use App\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DealerStockRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $this->ensureRegularDealer();

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        DB::transaction(function () use ($data, $product) {
            // Serialize requests and approvals for the same dealer.
            Dealer::where('user_id', auth()->id())->lockForUpdate()->first();

            if ($this->getAvailableStock(auth()->id(), $product) > 0) {
                abort(422, 'This item already has stock. Sell or transfer it before requesting more.');
            }

            $hasPendingRequest = DealerStockRequest::where([
                'dealer_id' => auth()->id(),
                'product_id' => $product->id,
                'status' => 'Pending',
            ])->lockForUpdate()->exists();

            if ($hasPendingRequest) {
                abort(422, 'A request for this item is already pending.');
            }

            DealerStockRequest::create([
                'dealer_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'notes' => $data['notes'] ?? null,
                'status' => 'Pending',
            ]);
        });

        return back()->with('success', 'Stock request submitted for admin approval.');
    }

    public function adminIndex()
    {
        $this->ensureAdmin();

        $requests = DealerStockRequest::with(['dealer.dealer', 'product', 'reviewer'])
            ->latest()
            ->get();

        return view('dealer_stock_requests.admin_index', compact('requests'));
    }

    public function approve(Request $request, $id)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $id) {
            $stockRequest = DealerStockRequest::with('product')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($stockRequest->status !== 'Pending') {
                abort(409, 'Request already reviewed.');
            }

            Dealer::where('user_id', $stockRequest->dealer_id)
                ->lockForUpdate()
                ->first();

            if ($this->getAvailableStock($stockRequest->dealer_id, $stockRequest->product) > 0) {
                abort(422, 'Approval blocked: the dealer now has stock for this item.');
            }

            $order = $this->createApprovedStockOrder($stockRequest, $data['review_notes'] ?? null);

            $stockRequest->update([
                'status' => 'Approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $data['review_notes'] ?? null,
                'approved_order_id' => $order->id,
            ]);
        });

        return back()->with('success', 'Stock approved and added to inventory.');
    }

    public function reject(Request $request, $id)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $id) {
            $stockRequest = DealerStockRequest::lockForUpdate()->findOrFail($id);

            if ($stockRequest->status !== 'Pending') {
                abort(409, 'Request already reviewed.');
            }

            $stockRequest->update([
                'status' => 'Rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $data['review_notes'],
            ]);
        });

        return back()->with('success', 'Stock request rejected.');
    }

    private function createApprovedStockOrder(DealerStockRequest $stockRequest, $reviewNotes = null)
    {
        $product = $stockRequest->product;
        $lastOrder = OrderDetail::latest('id')->lockForUpdate()->first();

        $order = new OrderDetail();
        $order->item = $product->product_name;
        $order->sku = $product->sku;
        $order->transaction_id = 'STK-' . str_pad(($lastOrder ? $lastOrder->id : 0) + 1, 6, '0', STR_PAD_LEFT);
        $order->item_description = $product->description;
        $order->qty = $stockRequest->quantity;
        $order->price = $product->dealer_price ?? $product->price ?? 0;
        $order->dealer_id = $stockRequest->dealer_id;
        $order->created_by = auth()->id();
        $order->date = now()->toDateString();
        $order->status = 'Completed';

        if (Schema::hasColumn('order_details', 'completed_at')) {
            $order->completed_at = now();
        }

        if (Schema::hasColumn('order_details', 'remarks')) {
            $order->remarks = 'Approved stock request #' . $stockRequest->id;

            if ($reviewNotes) {
                $order->remarks .= ': ' . $reviewNotes;
            }
        }

        $order->save();

        return $order;
    }

    private function getAvailableStock($dealerId, Product $product)
    {
        $stockIn = OrderDetail::where('dealer_id', $dealerId)
            ->where('status', 'Completed')
            ->where(function ($query) use ($product) {
                $query->where('item', $product->product_name);

                if ($product->sku) {
                    $query->orWhere('sku', $product->sku);
                }
            })
            ->sum('qty');

        $stockOut = TransactionDetail::where('dealer_id', $dealerId)
            ->when(Schema::hasColumn('transaction_details', 'status'), function ($query) {
                $query->where('status', 'Completed');
            })
            ->where(function ($query) use ($product) {
                $query->where('item', $product->product_name)
                    ->orWhere('item_description', $product->product_name);

                if ($product->description) {
                    $query->orWhere('item_description', $product->description);
                }
            })
            ->sum('qty');

        return max(0, (int) $stockIn - (int) $stockOut);
    }

    private function ensureRegularDealer()
    {
        abort_unless(
            auth()->user()->role === 'Dealer'
                && strtolower(optional(auth()->user()->dealer)->dealer_type) === 'regular',
            403
        );
    }

    private function ensureAdmin()
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
    }
}
