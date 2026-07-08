<?php

namespace App\Http\Controllers;

use App\Client;
use App\Dealer;
use App\Exports\DealerOrdersExport;
use App\AdPurchaseOrderItem;
use App\Item;
use App\InventoryTransfer;
use App\Mail\OrderStatusUpdatedMail;
use App\OrderDetail;
use App\Product;
use App\AreaDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class OrderController extends Controller
{
    public function index()
    {
        $customers = Client::where('status', 'Active')
            ->whereHas('serial')
            ->get();

        $user = auth()->user();
        $items = Item::get();
        $adAreas = optional($user->ad) ? $user->ad->areas->pluck('area_name')->toArray() : [];

        $dealers = Dealer::when($user->role !== 'Admin', function ($query) use ($adAreas) {
                $query->whereIn('area', $adAreas);
            })
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $products = Product::when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();
        $stockByProduct = $user->role === 'Admin'
            ? collect()
            : $this->availableStockByProduct($user->id, optional($user->ad)->id, $products);
        $stockByAreaProduct = $user->role === 'Admin'
            ? collect()
            : $this->availableStockByAreaProduct($user->id, optional($user->ad)->id, $products, $adAreas);
        $inventoryStatsByAreaProduct = $user->role === 'Admin'
            ? collect()
            : $this->inventoryStatsByAreaProduct(optional($user->ad)->id, $products, $adAreas, $stockByAreaProduct);

        $pendingOrdersQuery = OrderDetail::where('status', 'Pending');

        if ($user->role !== 'Admin') {
            $pendingOrdersQuery->where('ad_id', optional($user->ad)->id);
        }

        $pendingOrdersCount = $pendingOrdersQuery->count();

        $orderTabs = $this->orderTabsForUser($user);
        $orders = $orderTabs->pluck('orders')->flatten(1);
        // dd($orders);
        return view('orders.index', [
            'orders' => $orders,
            'orderTabs' => $orderTabs,
            'items' => $items,
            'customers' => $customers,
            'dealers' => $dealers,
            'products' => $products,
            'stockByProduct' => $stockByProduct,
            'stockByAreaProduct' => $stockByAreaProduct,
            'inventoryStatsByAreaProduct' => $inventoryStatsByAreaProduct,
            'pendingOrdersCount' => $pendingOrdersCount,
        ]);
    }

    public function export()
    {
        $orders = $this->orderIndexQuery()->get();

        return Excel::download(
            new DealerOrdersExport($orders),
            'md-dealer-orders-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function orderIndexQuery()
    {
        $user = auth()->user();

        return OrderDetail::with(['ad', 'dealer', 'adDealer'])
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_id', optional($user->ad)->id);
            })
            ->orderBy('id', 'desc');
    }

    private function orderTabsForUser($user)
    {
        $adId = optional($user->ad)->id;

        $regularOrders = $this->orderIndexQuery()->get()->map(function ($order) {
            $order->source_key = 'regular';
            $order->source_label = 'Regular';
            $order->source_database = 'dms_prei';
            $order->is_remote = false;

            return $order;
        });

        return collect([
            [
                'key' => 'regular',
                'label' => 'Regular',
                'database' => 'dms_prei',
                'icon' => 'bi bi-building',
                'orders' => $regularOrders,
            ],
            [
                'key' => 'project_rise',
                'label' => 'Project Rise',
                'database' => 'admin_crms',
                'icon' => 'bi bi-graph-up-arrow',
                'orders' => $this->remoteOrderDetails('admin_crms', 'Project Rise', $adId),
            ],
            [
                'key' => 'project_genesis',
                'label' => 'Project Genesis',
                'database' => 'admin_crms2',
                'icon' => 'bi bi-lightning-charge',
                'orders' => $this->remoteOrderDetails('admin_crms2', 'Project Genesis', $adId),
            ],
        ]);
    }

    private function remoteOrderDetails($connection, $label, $adId)
    {
        if (!$adId) {
            return collect();
        }

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
                $order->source_database = $connection;
                $order->is_remote = true;
                $order->id = $order->id ?? uniqid($connection . '-');
                $order->transaction_id = $order->transaction_id ?? $order->id ?? '-';
                $order->date = $order->date ?? $order->created_at ?? now();
                $order->qty = (float) ($order->qty ?? $order->quantity ?? 0);

                $amount = (float) ($order->amount ?? $order->total_amount ?? 0);
                $order->price = (float) ($order->price ?? ($order->qty > 0 && $amount > 0 ? $amount / $order->qty : $amount));
                $order->delivery_fee = (float) ($order->delivery_fee ?? 0);
                $order->item = $order->item ?? $order->product_name ?? $order->product ?? '-';
                $paymentMethod = strtolower(str_replace(' ', '_', (string) ($order->payment_method ?? 'cash')));
                $deliveryType = strtolower((string) ($order->delivery_type ?? 'pickup'));
                $order->payment_method = in_array($paymentMethod, ['voucher', 'cash', 'gcash', 'credit', 'bank_transfer'], true)
                    ? $paymentMethod
                    : 'cash';
                $order->delivery_type = in_array($deliveryType, ['pickup', 'delivery'], true)
                    ? $deliveryType
                    : 'pickup';
                $order->status = $order->status ?? '-';
                $order->points_dealer = $order->points_dealer ?? $order->points ?? 0;
                $order->is_guest = $order->is_guest ?? false;
                $order->guest_name = $order->guest_name ?? null;
                $order->guest_phone = $order->guest_phone ?? null;
                $order->guest_email = $order->guest_email ?? null;
                $order->guest_authorized_territory = $order->guest_authorized_territory ?? null;
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
            Log::warning('Unable to load remote order details.', [
                'connection' => $connection,
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    public function create()
    {
        //
    }

    public function guestOrder(Request $request)
    {
        $products = Product::with('item')
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();
        $authorizedTerritories = Schema::hasTable('ad_areas')
            ? DB::table('ad_areas')
                ->whereNull('deleted_at')
                ->whereNotNull('area_name')
                ->where('area_name', '<>', '')
                ->distinct()
                ->orderBy('area_name')
                ->pluck('area_name')
            : collect();
        $prefillClient = null;

        if ($request->filled('client_id')) {
            $prefillClient = Client::where('status', 'Active')
                ->whereNotNull('serial_number')
                ->find($request->client_id);
        }

        return view('orders.guest_order', [
            'products' => $products,
            'authorizedTerritories' => $authorizedTerritories,
            'prefillClient' => $prefillClient,
        ]);
    }

    public function purchaseOrder()
    {
        $user = auth()->user();
        $ad = $user->ad;

        if (!$ad && $user->role !== 'Admin') {
            return redirect()
                ->route('orders')
                ->with('error', 'Area Distributor profile not found.');
        }

        $products = Product::when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();
        $stockByProduct = $user->role === 'Admin'
            ? collect()
            : $this->availableStockByProduct($user->id, optional($ad)->id, $products);

        return view('orders.purchase_order_page', [
            'ad' => $ad,
            'products' => $products,
            'stockByProduct' => $stockByProduct,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('products')) {
            return $this->storeAdpo($request);
        }

        $user = auth()->user();

        $request->validate([
            'dealer_id' => 'required|integer',
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|numeric|min:1',
            'payment_method' => 'required|in:voucher,cash,gcash,credit,bank_transfer',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_fee' => 'nullable|required_if:delivery_type,delivery|numeric|min:0',
            'date' => 'nullable|date',
        ]);

        $product = Product::when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->where('status', 'Activate')
            ->findOrFail($request->product_id);

        $ad = AreaDistributor::where('user_id', $product->ad_user_id)->firstOrFail();
        $dealer = Dealer::where('user_id', $request->dealer_id)
            ->where('status', 'Active')
            ->firstOrFail();

        if ($user->role !== 'Admin') {
            $adAreas = optional($user->ad) ? $user->ad->areas->pluck('area_name')->toArray() : [];

            abort_unless(in_array($dealer->area, $adAreas), 403);

            $availableQty = $this->availableStockForAreaProduct($user->id, optional($user->ad)->id, $dealer->area, $product);
            if ((float) $request->qty > $availableQty) {
                return back()
                    ->withInput()
                    ->with('error', $product->product_name . ' is out of stock or has only ' . number_format($availableQty) . ' available in ' . $dealer->area . '.');
            }
        }

        $order = new OrderDetail();
        $order->transaction_id = $this->nextPurchaseOrderNumber();
        $order->item = $product->product_name;
        $order->item_description = $product->description;
        $order->ad_id = $ad->id;
        $order->qty = $request->qty;
        $order->price = $this->dealerPrice($product);
        $order->dealer_id = $dealer->user_id;
        $order->ad_address = $ad->address;
        $order->payment_method = $request->payment_method;
        $order->delivery_type = $request->delivery_type;
        $order->status = 'Pending';

        if (Schema::hasColumn('order_details', 'product_id')) {
            $order->product_id = $product->id;
        }

        if (Schema::hasColumn('order_details', 'date')) {
            $order->date = $request->date ?: now()->toDateString();
        }

        if (Schema::hasColumn('order_details', 'delivery_fee')) {
            $order->delivery_fee = $request->delivery_type === 'delivery'
                ? $request->delivery_fee
                : null;
        }

        if (Schema::hasColumn('order_details', 'points_dealer')) {
            $order->points_dealer = (float) $product->dealer_points * (float) $request->qty;
        }

        $order->save();

        Alert::success('Success', 'Purchase order created successfully.');

        return redirect()
            ->route('orders');
    }

    public function storeGuest(Request $request)
    {
        $request->validate([
            'guest_name' => 'nullable|string|max:150',
            'guest_email' => 'nullable|email|max:150',
            'guest_phone' => 'nullable|string|max:40',
            'guest_authorized_territory' => 'nullable|string|max:150',
            'guest_notes' => 'nullable|string|max:1000',
            'loyalty_client_id' => 'nullable|integer|exists:clients,id',
            'products' => 'required|array',
            'products.*.qty' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
        ]);

        $loyaltyClient = null;
        if ($request->filled('loyalty_client_id')) {
            $loyaltyClient = Client::where('status', 'Active')
                ->whereNotNull('serial_number')
                ->find($request->loyalty_client_id);
        }

        $selectedProducts = collect($request->input('products'))
            ->filter(function ($row) {
                return (int) ($row['qty'] ?? 0) > 0;
            });

        if ($selectedProducts->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['products' => 'Please select at least one product and quantity.']);
        }

        $productIds = $selectedProducts->keys()->map(function ($id) {
            return (int) $id;
        })->values();

        $products = Product::whereIn('id', $productIds)
            ->where('status', 'Activate')
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['products' => 'Selected products are no longer available.']);
        }

        $poNumber = $this->nextPurchaseOrderNumber();
        $created = 0;
        $deliveryFeeApplied = false;

        foreach ($selectedProducts as $productId => $line) {
            $product = $products->get((int) $productId);

            if (!$product) {
                continue;
            }

            $ad = AreaDistributor::where('user_id', $product->ad_user_id)->first();
            $order = new OrderDetail();
            $order->transaction_id = $poNumber;
            $order->item = $product->product_name;
            $order->item_description = $product->description;
            $order->ad_id = optional($ad)->id;
            $order->qty = (int) $line['qty'];
            $order->price = $this->guestPrice($product);
            $order->dealer_id = 0;
            $order->ad_address = optional($ad)->address;
            $order->payment_method = $request->payment_method;
            $order->delivery_type = 'pickup';
            $order->status = 'Completed';

            if (Schema::hasColumn('order_details', 'product_id')) {
                $order->product_id = $product->id;
            }

            if (Schema::hasColumn('order_details', 'date')) {
                $order->date = now()->toDateString();
            }

            if (Schema::hasColumn('order_details', 'delivery_fee')) {
                $order->delivery_fee = null;
                $deliveryFeeApplied = true;
            }

            if (Schema::hasColumn('order_details', 'is_guest')) {
                $order->is_guest = $loyaltyClient ? 0 : 1;
            }

            foreach (['guest_name', 'guest_email', 'guest_phone', 'guest_authorized_territory', 'guest_notes'] as $field) {
                if (Schema::hasColumn('order_details', $field)) {
                    $order->{$field} = $request->input($field);
                }
            }

            if (Schema::hasColumn('order_details', 'points_dealer')) {
                $order->points_dealer = 0;
            }

            $order->save();
            $created++;
        }

        if ($created === 0) {
            return back()
                ->withInput()
                ->withErrors(['products' => 'No valid products were selected.']);
        }

        return redirect()
            ->route('guest-order')
            ->with('success', 'Your order was submitted successfully. Reference: ' . $poNumber);
    }

    private function storeAdpo(Request $request)
    {
        $user = auth()->user();
        $ad = $user->ad;

        if (!$ad && $user->role !== 'Admin') {
            return back()->with('error', 'Area Distributor profile not found.');
        }

        $request->validate([
            'ad_email' => 'nullable|email',
            'shipping_type' => 'required|in:delivered,pickup',
            'payment_method' => 'required|in:voucher,cash,gcash,bank_transfer,credit',
            'delivery_fee' => 'nullable|numeric|min:0',
            'products' => 'required|array',
            'products.*.qty' => 'nullable|numeric|min:0',
        ]);

        $selectedProducts = collect($request->input('products'))
            ->filter(function ($row) {
                return isset($row['selected']) && (float) ($row['qty'] ?? 0) > 0;
            });

        if ($selectedProducts->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Please select at least one product and quantity.');
        }

        $productIds = $selectedProducts->keys()->map(function ($id) {
            return (int) $id;
        })->values();

        $products = Product::whereIn('id', $productIds)
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->where('status', 'Activate')
            ->get()
            ->keyBy('id');
        $stockByProduct = $user->role === 'Admin'
            ? collect()
            : $this->availableStockByProduct($user->id, optional($ad)->id, $products);

        foreach ($selectedProducts as $productId => $line) {
            $product = $products->get((int) $productId);

            if (!$product) {
                continue;
            }

            $availableQty = (float) $stockByProduct->get($product->id, 0);
            $qty = (float) $line['qty'];

            if ($qty > $availableQty) {
                return back()
                    ->withInput()
                    ->with('error', $product->product_name . ' is out of stock or has only ' . number_format($availableQty) . ' available.');
            }
        }

        $poNumber = $this->nextPurchaseOrderNumber();
        $created = 0;

        foreach ($selectedProducts as $productId => $line) {
            $product = $products->get((int) $productId);

            if (!$product) {
                continue;
            }

            $lineAd = $ad ?: AreaDistributor::where('user_id', $product->ad_user_id)->first();
            $qty = (float) $line['qty'];

            $order = new OrderDetail();
            $order->transaction_id = $poNumber;
            $order->item = $product->product_name;
            $order->item_description = $product->description;
            $order->ad_id = optional($lineAd)->id;
            $order->qty = $qty;
            $order->price = $this->dealerPrice($product);
            $order->dealer_id = $user->id;
            $order->ad_address = optional($lineAd)->address;
            $order->payment_method = $request->payment_method;
            $order->delivery_type = $request->shipping_type === 'delivered' ? 'delivery' : 'pickup';
            $order->status = 'Pending';

            if (Schema::hasColumn('order_details', 'product_id')) {
                $order->product_id = $product->id;
            }

            if (Schema::hasColumn('order_details', 'date')) {
                $order->date = now()->toDateString();
            }

            if (Schema::hasColumn('order_details', 'delivery_fee')) {
                $order->delivery_fee = $order->delivery_type === 'delivery'
                    ? ($request->delivery_fee ?: 0)
                    : null;
            }

            if (Schema::hasColumn('order_details', 'points_dealer')) {
                $order->points_dealer = (float) $product->dealer_points * $qty;
            }

            $order->save();
            $created++;
        }

        if ($created === 0) {
            return back()
                ->withInput()
                ->with('error', 'No valid products were selected.');
        }

        Alert::success('Success', 'AD purchase order ' . $poNumber . ' submitted successfully.');

        return redirect()->route('orders');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $connection = $this->orderSourceConnection($request->input('order_source'));

        if ($connection) {
            return $this->updateRemoteOrder($request, $id, $connection);
        }

        $order = OrderDetail::with(['dealer', 'ad'])
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_id', optional($user->ad)->id);
            })
            ->findOrFail($id);

        $isRegularDealer = strtolower((string) optional($order->adDealer)->dealer_type) === 'regular';

        $request->validate([
            'qty' => 'required|numeric|min:1',
            'payment_method' => 'required|in:voucher,cash,gcash,credit,bank_transfer',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_fee' => ($isRegularDealer && $request->delivery_type === 'delivery')
                ? 'required|numeric|min:0'
                : 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,For Verification,For Delivery,Completed,Cancelled',
        ]);

        if ($user->role !== 'Admin' && $request->status !== 'Cancelled') {
            $product = $this->productForOrder($order);

            if ($product) {
                $dealerArea = optional($order->dealer)->area;
                $availableQty = $this->availableStockForAreaProduct($user->id, optional($user->ad)->id, $dealerArea, $product);
                $editableQty = $availableQty + (float) $order->getOriginal('qty');

                if ((float) $request->qty > $editableQty) {
                    return response()->json([
                        'message' => $product->product_name . ' is out of stock or has only ' . number_format($availableQty) . ' available in ' . ($dealerArea ?: 'this area') . '.',
                    ], 422);
                }
            }
        }

        $dealerPointsPerUnit = $this->dealerPointsForOrder($order);

        $order->qty = $request->qty;
        $order->payment_method = $request->payment_method;
        $order->delivery_type = $request->delivery_type;
        $order->status = $request->status;

        if (Schema::hasColumn('order_details', 'delivery_fee')) {
            $order->delivery_fee = $request->delivery_type === 'delivery' && $isRegularDealer
                ? $request->delivery_fee
                : null;
        }

        if (Schema::hasColumn('order_details', 'points_dealer')) {
            $order->points_dealer = $dealerPointsPerUnit * $request->qty;
        }

        $order->save();

        try {
            if ($order->dealer && !empty($order->dealer->email)) {
                Mail::to($order->dealer->email)
                    ->send(new OrderStatusUpdatedMail($order));
            }
        } catch (\Exception $e) {
            Log::warning('Order status email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'qty' => $order->qty,
            'payment_method' => $order->payment_method,
            'delivery_type' => $order->delivery_type,
            'delivery_fee' => $order->delivery_fee,
            'status' => $order->status,
            'points_dealer' => $order->points_dealer,
        ]);
    }

    private function orderSourceConnection($source)
    {
        $source = strtolower(trim((string) $source));

        $connections = [
            'admin_crms2' => 'admin_crms2',
            'project_rise' => 'admin_crms',
            'admin_crms' => 'admin_crms',
            'project_genesis' => 'admin_crms2',
        ];

        return $connections[$source] ?? null;
    }

    private function updateRemoteOrder(Request $request, $id, $connection)
    {
        $user = auth()->user();
        $adId = optional($user->ad)->id;
        $schema = DB::connection($connection)->getSchemaBuilder();

        if (!$schema->hasTable('order_details')) {
            return response()->json([
                'message' => 'Order table was not found in ' . $connection . '.',
            ], 404);
        }

        $query = DB::connection($connection)->table('order_details')->where('id', $id);

        if ($user->role !== 'Admin') {
            $query->where('ad_id', $adId);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order was not found in ' . $connection . '.',
            ], 404);
        }

        $request->validate([
            'qty' => 'required|numeric|min:1',
            'payment_method' => 'required|in:voucher,cash,gcash,credit,bank_transfer',
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_fee' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,For Verification,For Delivery,SO Created,Completed,Cancelled',
        ]);

        $updates = [];

        foreach ([
            'qty' => (float) $request->qty,
            'payment_method' => $request->payment_method,
            'delivery_type' => $request->delivery_type,
            'status' => $request->status,
        ] as $column => $value) {
            if ($schema->hasColumn('order_details', $column)) {
                $updates[$column] = $value;
            }
        }

        if ($schema->hasColumn('order_details', 'delivery_fee')) {
            $updates['delivery_fee'] = $request->delivery_type === 'delivery'
                ? ($request->delivery_fee ?: 0)
                : null;
        }

        if ($schema->hasColumn('order_details', 'points_dealer')) {
            $currentQty = (float) ($order->qty ?? 0);
            $currentPoints = (float) ($order->points_dealer ?? 0);
            $pointsPerUnit = $currentQty > 0 ? $currentPoints / $currentQty : 0;
            $updates['points_dealer'] = $pointsPerUnit * (float) $request->qty;
        }

        if ($schema->hasColumn('order_details', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::connection($connection)
            ->table('order_details')
            ->where('id', $id)
            ->update($updates);

        $updatedOrder = DB::connection($connection)->table('order_details')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'qty' => $updatedOrder->qty ?? $request->qty,
            'payment_method' => $updatedOrder->payment_method ?? $request->payment_method,
            'delivery_type' => $updatedOrder->delivery_type ?? $request->delivery_type,
            'delivery_fee' => $updatedOrder->delivery_fee ?? null,
            'status' => $updatedOrder->status ?? $request->status,
            'points_dealer' => $updatedOrder->points_dealer ?? 0,
        ]);
    }

    public function destroy($id)
    {
        //
    }

    private function dealerPointsForOrder(OrderDetail $order)
    {
        $product = $this->productForOrder($order);

        if ($product) {
            return (float) $product->dealer_points;
        }

        $originalQty = (float) $order->getOriginal('qty');

        if ($originalQty > 0 && $order->getOriginal('points_dealer') !== null) {
            return (float) $order->getOriginal('points_dealer') / $originalQty;
        }

        return 0;
    }

    private function productForOrder(OrderDetail $order)
    {
        $productQuery = Product::query();

        if (Schema::hasColumn('order_details', 'product_id') && $order->product_id) {
            $productQuery->where('id', $order->product_id);
        } else {
            $productQuery->where('product_name', $order->item);
        }

        if ($order->ad && $order->ad->user_id) {
            $productQuery->where('ad_user_id', $order->ad->user_id);
        }

        return $productQuery->first();
    }

    private function dealerPrice(Product $product)
    {
        if (Schema::hasColumn('products', 'dealer_price') && $product->dealer_price !== null) {
            return $product->dealer_price;
        }

        return $product->price;
    }

    private function guestPrice(Product $product)
    {
        if (Schema::hasColumn('products', 'client_price') && $product->client_price !== null) {
            return $product->client_price;
        }

        if (Schema::hasColumn('products', 'srp_price') && $product->srp_price !== null) {
            return $product->srp_price;
        }

        return $product->price;
    }

    private function availableStockForProduct($adUserId, $adId, Product $product)
    {
        return (float) $this->availableStockByProduct($adUserId, $adId, collect([$product]))->get($product->id, 0);
    }

    private function availableStockForAreaProduct($adUserId, $adId, $area, Product $product)
    {
        if (!$area) {
            return 0.0;
        }

        return (float) $this->availableStockByAreaProduct($adUserId, $adId, collect([$product]), [$area])
            ->get($area, collect())
            ->get($product->id, 0);
    }

    private function inventoryStatsByAreaProduct($adId, $products, $areas, $availableStockByAreaProduct)
    {
        $products = collect($products)->filter();
        $areas = collect($areas)->filter()->values();

        if (!$adId || $products->isEmpty() || $areas->isEmpty()) {
            return collect();
        }

        $productIds = $products->pluck('id')->filter()->map(fn($id) => (int) $id)->values();
        $productNames = $products->pluck('product_name')->filter()->map(fn($name) => $this->normalizeProductName($name))->unique()->values();
        $salesOrders = $areas->mapWithKeys(function ($area) use ($products) {
            return [$area => $products->mapWithKeys(fn($product) => [$product->id => 0.0])];
        });

        $hasOrderProductId = Schema::hasColumn('order_details', 'product_id');
        $hasGuestTerritory = Schema::hasColumn('order_details', 'guest_authorized_territory');
        $selects = [
            'dealers.area as dealer_area',
            'ordering_ad_areas.area_name as ad_area',
            'order_details.item',
            DB::raw('SUM(order_details.qty) as qty'),
        ];

        if ($hasGuestTerritory) {
            $selects[] = 'order_details.guest_authorized_territory as guest_area';
        }

        if ($hasOrderProductId) {
            $selects[] = 'order_details.product_id';
        }

        $orderRows = OrderDetail::leftJoin('dealers', 'order_details.dealer_id', '=', 'dealers.user_id')
            ->leftJoin('area_distributors as ordering_ads', 'order_details.dealer_id', '=', 'ordering_ads.user_id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as ordering_ad_areas'), 'ordering_ads.id', '=', 'ordering_ad_areas.ad_id')
            ->select($selects)
            ->where('order_details.ad_id', $adId)
            ->where('order_details.status', 'Completed')
            ->where(function ($query) use ($areas, $hasGuestTerritory) {
                $query->whereIn('dealers.area', $areas)
                    ->orWhereIn('ordering_ad_areas.area_name', $areas);

                if ($hasGuestTerritory) {
                    $query->orWhereIn('order_details.guest_authorized_territory', $areas);
                }
            })
            ->where(function ($query) use ($productIds, $productNames, $hasOrderProductId) {
                if ($hasOrderProductId) {
                    $query->whereIn('order_details.product_id', $productIds);
                }

                if ($productNames->isNotEmpty()) {
                    $hasOrderProductId
                        ? $query->orWhereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames)
                        : $query->whereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames);
                }
            })
            ->groupBy('dealers.area', 'ordering_ad_areas.area_name', 'order_details.item');

        if ($hasGuestTerritory) {
            $orderRows->groupBy('order_details.guest_authorized_territory');
        }

        if ($hasOrderProductId) {
            $orderRows->groupBy('order_details.product_id');
        }

        foreach ($orderRows->get() as $row) {
            $area = ($hasGuestTerritory ? $row->guest_area : null) ?: $row->dealer_area ?: $row->ad_area;
            $product = $this->matchStockProduct($products, $hasOrderProductId ? $row->product_id : null, $row->item);

            if ($area && $product && $salesOrders->has($area)) {
                $areaSales = $salesOrders->get($area);
                $areaSales->put($product->id, (float) $areaSales->get($product->id, 0) + (float) $row->qty);
            }
        }

        return $availableStockByAreaProduct->map(function ($areaStock, $area) use ($salesOrders) {
            return collect($areaStock)->map(function ($availableQty, $productId) use ($salesOrders, $area) {
                $orderedQty = (float) $salesOrders->get($area, collect())->get($productId, 0);
                $availableQty = (float) $availableQty;

                return [
                    'stock_after_movement' => $availableQty + $orderedQty,
                    'sales_orders' => $orderedQty,
                    'available' => $availableQty,
                    'status' => $availableQty <= 0 ? 'No stock' : 'Good',
                ];
            });
        });
    }

    private function availableStockByAreaProduct($adUserId, $adId, $products, $areas)
    {
        $products = collect($products)->filter();
        $areas = collect($areas)->filter()->values();

        if (!$adUserId || $products->isEmpty() || $areas->isEmpty()) {
            return collect();
        }

        $productIds = $products->pluck('id')->filter()->map(fn($id) => (int) $id)->values();
        $productNames = $products->pluck('product_name')->filter()->map(fn($name) => $this->normalizeProductName($name))->unique()->values();
        $stock = $areas->mapWithKeys(function ($area) use ($products) {
            return [$area => $products->mapWithKeys(fn($product) => [$product->id => 0.0])];
        });

        $adpoRows = AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as adpo_areas'), 'ad_purchase_orders.ad_id', '=', 'adpo_areas.ad_id')
            ->select(
                'ad_purchase_orders.authorized_territory as territory_area',
                'adpo_areas.area_name as ad_area',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.product_name',
                DB::raw('SUM(ad_purchase_order_items.qty) as qty')
            )
            ->where('ad_purchase_orders.ad_user_id', $adUserId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->where(function ($query) use ($areas) {
                $query->whereIn('ad_purchase_orders.authorized_territory', $areas)
                    ->orWhereIn('adpo_areas.area_name', $areas);
            })
            ->where(function ($query) use ($productIds, $productNames) {
                $query->whereIn('ad_purchase_order_items.product_id', $productIds);

                if ($productNames->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('LOWER(TRIM(ad_purchase_order_items.product_name))'), $productNames);
                }
            })
            ->groupBy(
                'ad_purchase_orders.authorized_territory',
                'adpo_areas.area_name',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.product_name'
            )
            ->get();

        foreach ($adpoRows as $row) {
            $area = $row->territory_area ?: $row->ad_area;
            $product = $this->matchStockProduct($products, $row->product_id, $row->product_name);

            if ($area && $product && $stock->has($area)) {
                $areaStock = $stock->get($area);
                $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) + (float) $row->qty);
            }
        }

        $movementRows = InventoryTransfer::select('product_id', 'item_name', 'movement_type', 'from_area', 'to_area', DB::raw('SUM(qty) as qty'))
            ->where('ad_user_id', $adUserId)
            ->where(function ($query) use ($areas) {
                $query->whereIn('from_area', $areas)
                    ->orWhereIn('to_area', $areas);
            })
            ->where(function ($query) use ($productIds, $productNames) {
                $query->whereIn('product_id', $productIds);

                if ($productNames->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('LOWER(TRIM(item_name))'), $productNames);
                }
            })
            ->groupBy('product_id', 'item_name', 'movement_type', 'from_area', 'to_area')
            ->get();

        foreach ($movementRows as $row) {
            $product = $this->matchStockProduct($products, $row->product_id, $row->item_name);

            if (!$product) {
                continue;
            }

            $qty = (float) $row->qty;

            if ($row->movement_type === 'in' && $stock->has($row->to_area)) {
                $areaStock = $stock->get($row->to_area);
                $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) + $qty);
            }

            if ($row->movement_type === 'out' && $stock->has($row->from_area)) {
                $areaStock = $stock->get($row->from_area);
                $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) - $qty);
            }

            if ($row->movement_type === 'transfer') {
                if ($stock->has($row->from_area)) {
                    $areaStock = $stock->get($row->from_area);
                    $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) - $qty);
                }

                if ($stock->has($row->to_area)) {
                    $areaStock = $stock->get($row->to_area);
                    $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) + $qty);
                }
            }
        }

        if ($adId) {
            $hasOrderProductId = Schema::hasColumn('order_details', 'product_id');
            $hasGuestTerritory = Schema::hasColumn('order_details', 'guest_authorized_territory');
            $selects = [
                'dealers.area as dealer_area',
                'ordering_ad_areas.area_name as ad_area',
                'order_details.item',
                DB::raw('SUM(order_details.qty) as qty'),
            ];

            if ($hasGuestTerritory) {
                $selects[] = 'order_details.guest_authorized_territory as guest_area';
            }

            if ($hasOrderProductId) {
                $selects[] = 'order_details.product_id';
            }

            $orderRows = OrderDetail::leftJoin('dealers', 'order_details.dealer_id', '=', 'dealers.user_id')
                ->leftJoin('area_distributors as ordering_ads', 'order_details.dealer_id', '=', 'ordering_ads.user_id')
                ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as ordering_ad_areas'), 'ordering_ads.id', '=', 'ordering_ad_areas.ad_id')
                ->select($selects)
                ->where('order_details.ad_id', $adId)
                ->where('order_details.status', 'Completed')
                ->where(function ($query) use ($areas, $hasGuestTerritory) {
                    $query->whereIn('dealers.area', $areas)
                        ->orWhereIn('ordering_ad_areas.area_name', $areas);

                    if ($hasGuestTerritory) {
                        $query->orWhereIn('order_details.guest_authorized_territory', $areas);
                    }
                })
                ->where(function ($query) use ($productIds, $productNames, $hasOrderProductId) {
                    if ($hasOrderProductId) {
                        $query->whereIn('order_details.product_id', $productIds);
                    }

                    if ($productNames->isNotEmpty()) {
                        $hasOrderProductId
                            ? $query->orWhereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames)
                            : $query->whereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames);
                    }
                })
                ->groupBy('dealers.area', 'ordering_ad_areas.area_name', 'order_details.item');

            if ($hasGuestTerritory) {
                $orderRows->groupBy('order_details.guest_authorized_territory');
            }

            if ($hasOrderProductId) {
                $orderRows->groupBy('order_details.product_id');
            }

            foreach ($orderRows->get() as $row) {
                $area = ($hasGuestTerritory ? $row->guest_area : null) ?: $row->dealer_area ?: $row->ad_area;
                $product = $this->matchStockProduct($products, $hasOrderProductId ? $row->product_id : null, $row->item);

                if ($area && $product && $stock->has($area)) {
                    $areaStock = $stock->get($area);
                    $areaStock->put($product->id, (float) $areaStock->get($product->id, 0) - (float) $row->qty);
                }
            }
        }

        return $stock->map(function ($areaStock) {
            return $areaStock->map(fn($qty) => max(0, (float) $qty));
        });
    }

    private function availableStockByProduct($adUserId, $adId, $products)
    {
        $products = collect($products)->filter();

        if (!$adUserId || $products->isEmpty()) {
            return collect();
        }

        $productIds = $products->pluck('id')->filter()->map(fn($id) => (int) $id)->values();
        $productNames = $products->pluck('product_name')->filter()->map(fn($name) => $this->normalizeProductName($name))->unique()->values();
        $stock = $products->mapWithKeys(fn($product) => [$product->id => 0.0]);

        $adpoRows = AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->select('ad_purchase_order_items.product_id', 'ad_purchase_order_items.product_name', DB::raw('SUM(ad_purchase_order_items.qty) as qty'))
            ->where('ad_purchase_orders.ad_user_id', $adUserId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->where(function ($query) use ($productIds, $productNames) {
                $query->whereIn('ad_purchase_order_items.product_id', $productIds);

                if ($productNames->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('LOWER(TRIM(ad_purchase_order_items.product_name))'), $productNames);
                }
            })
            ->groupBy('ad_purchase_order_items.product_id', 'ad_purchase_order_items.product_name')
            ->get();

        foreach ($adpoRows as $row) {
            $product = $this->matchStockProduct($products, $row->product_id, $row->product_name);
            if ($product) {
                $stock->put($product->id, (float) $stock->get($product->id, 0) + (float) $row->qty);
            }
        }

        $movementRows = InventoryTransfer::select('product_id', 'item_name', 'movement_type', DB::raw('SUM(qty) as qty'))
            ->where('ad_user_id', $adUserId)
            ->where(function ($query) use ($productIds, $productNames) {
                $query->whereIn('product_id', $productIds);

                if ($productNames->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('LOWER(TRIM(item_name))'), $productNames);
                }
            })
            ->groupBy('product_id', 'item_name', 'movement_type')
            ->get();

        foreach ($movementRows as $row) {
            $product = $this->matchStockProduct($products, $row->product_id, $row->item_name);
            if (!$product) {
                continue;
            }

            $qty = (float) $row->qty;
            if ($row->movement_type === 'in') {
                $stock->put($product->id, (float) $stock->get($product->id, 0) + $qty);
            } elseif ($row->movement_type === 'out') {
                $stock->put($product->id, (float) $stock->get($product->id, 0) - $qty);
            }
        }

        if ($adId) {
            $hasOrderProductId = Schema::hasColumn('order_details', 'product_id');
            $selects = ['order_details.item', DB::raw('SUM(order_details.qty) as qty')];

            if ($hasOrderProductId) {
                $selects[] = 'order_details.product_id';
            }

            $orderRows = OrderDetail::select($selects)
                ->where('order_details.ad_id', $adId)
                ->where('order_details.status', 'Completed')
                ->where(function ($query) use ($productIds, $productNames, $hasOrderProductId) {
                    if ($hasOrderProductId) {
                        $query->whereIn('order_details.product_id', $productIds);
                    }

                    if ($productNames->isNotEmpty()) {
                        $hasOrderProductId
                            ? $query->orWhereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames)
                            : $query->whereIn(DB::raw('LOWER(TRIM(order_details.item))'), $productNames);
                    }
                })
                ->groupBy('order_details.item');

            if ($hasOrderProductId) {
                $orderRows->groupBy('order_details.product_id');
            }

            foreach ($orderRows->get() as $row) {
                $product = $this->matchStockProduct($products, $hasOrderProductId ? $row->product_id : null, $row->item);
                if ($product) {
                    $stock->put($product->id, (float) $stock->get($product->id, 0) - (float) $row->qty);
                }
            }
        }

        return $stock->map(fn($qty) => max(0, (float) $qty));
    }

    private function matchStockProduct($products, $productId, $productName)
    {
        if ($productId) {
            $product = $products->firstWhere('id', (int) $productId);

            if ($product) {
                return $product;
            }
        }

        $normalizedName = $this->normalizeProductName($productName);

        return $products->first(function ($product) use ($normalizedName) {
            return $this->normalizeProductName($product->product_name) === $normalizedName;
        });
    }

    private function normalizeProductName($name)
    {
        return strtolower(trim((string) $name));
    }

    private function nextPurchaseOrderNumber()
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (OrderDetail::where('transaction_id', $number)->exists());

        return $number;
    }
}
