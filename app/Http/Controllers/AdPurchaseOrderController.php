<?php

namespace App\Http\Controllers;

use App\AdPurchaseOrder;
use App\AdPurchaseOrderPartialReceipt;
use App\Exports\AdPurchaseOrdersExport;
use App\Mail\AdPurchaseOrderStatusUpdatedMail;
use App\Mail\AdPurchaseOrderWarehouseStatusNotification;
use App\Mail\AdPurchaseOrderWarehouseNotification;
use App\Item;
use App\User;
use App\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class AdPurchaseOrderController extends Controller
{
    private function normalizePartialDrNumber($drNumber)
    {
        $drNumber = strtoupper(trim((string) $drNumber));
        $drNumber = preg_replace('/\s*-\s*(\d+)\s*$/', '-$1', $drNumber);

        if ($drNumber !== '' && strpos($drNumber, 'DR') !== 0) {
            $drNumber = 'DR' . $drNumber;
        }

        return $drNumber;
    }

    private function incrementPartialDrNumber($drNumber)
    {
        $drNumber = $this->normalizePartialDrNumber($drNumber);

        if (preg_match('/^(.*)-(\d+)$/', $drNumber, $matches)) {
            return $matches[1] . '-' . ((int) $matches[2] + 1);
        }

        return $drNumber !== '' ? $drNumber . '-1' : '';
    }

    public function index(Request $request)
    {
        $orders = $this->adpoIndexQuery($request)->get();
        $favoriteOrders = $this->adpoIndexQuery($request, false)->get();

        $summary = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'Pending')->count(),
            'for_delivery' => $orders->where('status', 'For Delivery')->count(),
            'for_verification' => $orders->where('status', 'For Verification')->count(),
            'so_created' => $orders->where('status', 'SO Created')->count(),
            'partial_received' => $orders->where('status', 'Partial Received')->count(),
            'completed' => $orders->where('status', 'Completed')->count(),
            'amount' => $orders->sum('total_amount'),
        ];

        $favoriteSummary = [
            'total' => $favoriteOrders->count(),
            'pending' => $favoriteOrders->where('status', 'Pending')->count(),
            'for_delivery' => $favoriteOrders->where('status', 'For Delivery')->count(),
            'for_verification' => $favoriteOrders->where('status', 'For Verification')->count(),
            'so_created' => $favoriteOrders->where('status', 'SO Created')->count(),
            'partial_received' => $favoriteOrders->where('status', 'Partial Received')->count(),
            'completed' => $favoriteOrders->where('status', 'Completed')->count(),
        ];

        return view('ad_purchase_orders.index', [
            'orders' => $orders,
            'summary' => $summary,
            'favoriteSummary' => $favoriteSummary,
            'exportRoute' => route('ad-purchase-orders.export', request()->query()),
        ]);
    }

    public function export(Request $request)
    {
        $orders = $this->adpoIndexQuery($request)->get();

        return Excel::download(
            new AdPurchaseOrdersExport($orders),
            'ad-purchase-orders-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function regionVWarehouseIndex(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'Admin' || strtolower((string) $user->warehouse) !== 'guinobatan') {
            abort(403);
        }

        $orders = $this->regionVFilteredOrderQuery($request)->get();
        $favoriteOrders = $this->regionVFilteredOrderQuery($request, false)->get();

        $summary = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'Pending')->count(),
            'for_delivery' => $orders->where('status', 'For Delivery')->count(),
            'for_verification' => $orders->where('status', 'For Verification')->count(),
            'so_created' => $orders->where('status', 'SO Created')->count(),
            'partial_received' => $orders->where('status', 'Partial Received')->count(),
            'completed' => $orders->where('status', 'Completed')->count(),
            'amount' => $orders->sum('total_amount'),
        ];

        $favoriteSummary = [
            'total' => $favoriteOrders->count(),
            'pending' => $favoriteOrders->where('status', 'Pending')->count(),
            'for_delivery' => $favoriteOrders->where('status', 'For Delivery')->count(),
            'for_verification' => $favoriteOrders->where('status', 'For Verification')->count(),
            'so_created' => $favoriteOrders->where('status', 'SO Created')->count(),
            'partial_received' => $favoriteOrders->where('status', 'Partial Received')->count(),
            'completed' => $favoriteOrders->where('status', 'Completed')->count(),
        ];

        return view('ad_purchase_orders.index', [
            'orders' => $orders,
            'summary' => $summary,
            'favoriteSummary' => $favoriteSummary,
            'pageTitle' => 'Purchase Orders',
            'pageSubtitle' => 'Guinobatan warehouse module for DPO deliveries in Region V / Bicol.',
            'panelTitle' => 'Purchase Order History',
            'showCreateButton' => false,
            'clearRoute' => route('warehouse-ad-purchase-orders.region-v'),
            'exportRoute' => route('warehouse-ad-purchase-orders.region-v.export', request()->query()),
            'viewRouteName' => 'ad-purchase-orders.show',
        ]);
    }

    public function exportRegionVWarehouse(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'Admin' || strtolower((string) $user->warehouse) !== 'guinobatan') {
            abort(403);
        }

        $orders = $this->regionVFilteredOrderQuery($request)->get();

        return Excel::download(
            new AdPurchaseOrdersExport($orders),
            'region-v-ad-purchase-orders-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function adpoIndexQuery(Request $request, $applyStatus = true)
    {
        $user = auth()->user();

        return AdPurchaseOrder::with(['items.partialReceipts', 'partialReceipts.item', 'ad'])
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->when($applyStatus && $request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('shipping_type'), function ($query) use ($request) {
                $query->where('shipping_type', $request->shipping_type);
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('po_number', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhere('authorized_territory', 'like', '%' . $search . '%')
                        ->orWhereHas('ad', function ($adQuery) use ($search) {
                            $adQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('business_name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('id', 'desc');
    }

    private function regionVFilteredOrderQuery(Request $request, $applyStatus = true)
    {
        return $this->regionVOrderQuery()
            ->with(['items.partialReceipts', 'partialReceipts.item', 'ad'])
            ->when($applyStatus && $request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('shipping_type'), function ($query) use ($request) {
                $query->where('shipping_type', $request->shipping_type);
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('po_number', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhere('authorized_territory', 'like', '%' . $search . '%')
                        ->orWhere('delivery_address', 'like', '%' . $search . '%')
                        ->orWhereHas('ad', function ($adQuery) use ($search) {
                            $adQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('business_name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('id', 'desc');
    }

    public function create()
    {
        $user = auth()->user();
        $ad = $user->ad;

        if (!$ad && $user->role !== 'Admin') {
            Alert::error('ADPO Error', 'Area Distributor profile not found.');

            return redirect()
                ->route('ad-purchase-orders.index');
        }

        $storeCode = optional($ad)->store_code;
        $authorizedAreas = $this->territoryOptions($ad)->pluck('value');
        $userVouchers = Voucher::where('name', $storeCode)
            ->where('is_active', 1)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhereDate('starts_at', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereNull('used_count')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderBy('code')
            ->get()
            ->filter(function ($voucher) use ($authorizedAreas) {
                return $authorizedAreas->contains(function ($areaName) use ($voucher) {
                    return $voucher->hasArea($areaName);
                });
            })
            ->values();

        $products = Item::orderBy('item')->get();
        $favoriteProductIds = DB::table('adpo_product_favorites')
            ->where('user_id', $user->id)
            ->pluck('item_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
        $showPickupLubao = !$this->isRegionVText(
            optional($ad)->address,
            optional($ad)->delivery_address,
            optional($ad)->location_region
        );

        return view('ad_purchase_orders.create', compact('ad', 'products', 'favoriteProductIds', 'userVouchers', 'showPickupLubao'));
    }

    public function toggleFavoriteProduct(Request $request, Item $item)
    {
        $user = auth()->user();
        $favorite = DB::table('adpo_product_favorites')
            ->where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        if ($favorite) {
            DB::table('adpo_product_favorites')
                ->where('id', $favorite->id)
                ->delete();

            return response()->json([
                'favorited' => false,
                'message' => 'Product removed from favorites.',
            ]);
        }

        DB::table('adpo_product_favorites')->insert([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'favorited' => true,
            'message' => 'Product added to favorites.',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $ad = $user->ad;

        if (!$ad && $user->role !== 'Admin') {
            Alert::error('ADPO Error', 'Area Distributor profile not found.');

            return back()->withInput();
        }

        $territoryOptions = $this->territoryOptions($ad);

        $remarksRules = ['nullable', 'string', 'max:1000'];

        if (in_array($request->input('status'), ['Cancelled', 'Partial Received'], true)) {
            $remarksRules = ['required', 'string', 'max:1000'];
        }

        $request->validate([
            'phone_number' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'authorized_territory' => ($territoryOptions->isNotEmpty() ? 'required' : 'nullable') . '|string|max:255',
            'shipping_type' => 'required|in:delivered,pickup,pickup_lubao,pickup_guinobatan',
            'use_rebate_voucher' => 'required|in:yes,no',
            'voucher_code' => 'nullable|required_if:use_rebate_voucher,yes|string|max:100',
            'rebate_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:voucher,cash,gcash,bank_transfer,credit',
            'bank_name' => 'nullable|required_if:payment_method,bank_transfer|string|max:255',
            'other_bank_name' => 'nullable|required_if:bank_name,Other Bank|string|max:255',
            'delivery_fee' => 'nullable|numeric|min:0',
            'uniform_size' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'products' => 'required|array',
            'products.*.qty' => 'nullable|integer|min:0',
            'products.*.colors' => 'nullable|array',
            'products.*.colors.*' => 'nullable|integer|min:0',
            'products.*.sizes' => 'nullable|array',
            'products.*.sizes.*' => 'nullable|integer|min:0',
            'products.*.other_size' => 'nullable|string|max:100',
        ]);

        if ($request->filled('authorized_territory') && $territoryOptions->isNotEmpty() && !$territoryOptions->contains($request->authorized_territory)) {
            Alert::error('ADPO Error', 'Please select a valid authorized territory.');

            return back()
                ->withInput()
                ->withErrors(['authorized_territory' => 'Please select a valid authorized territory.']);
        }

        $isRegionVAd = $this->isRegionVText(
            $request->delivery_address,
            optional($ad)->address,
            optional($ad)->delivery_address,
            optional($ad)->location_region
        );

        if ($request->shipping_type === 'pickup_lubao' && $isRegionVAd) {
            Alert::error('ADPO Error', 'Pick Up in Lubao Plant is not available for Region V or Albay addresses.');

            return back()
                ->withInput()
                ->withErrors(['shipping_type' => 'Pick Up in Lubao Plant is not available for Region V or Albay addresses.']);
        }

        $selectedProducts = collect($request->input('products'))
            ->filter(function ($row) {
                $colorQty = collect($row['colors'] ?? [])->sum(function ($qty) {
                    return (int) $qty;
                });
                $sizeQty = collect($row['sizes'] ?? [])->sum(function ($qty) {
                    return (int) $qty;
                });

                return isset($row['selected']) && ((int) ($row['qty'] ?? 0) > 0 || $colorQty > 0 || $sizeQty > 0);
            });

        if ($selectedProducts->isEmpty()) {
            Alert::error('ADPO Error', 'Please select at least one product and quantity.');

            return back()
                ->withInput();
        }

        $productIds = $selectedProducts->keys()->map(function ($id) {
            return (int) $id;
        })->values();

        $products = Item::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        if ($products->count() === 0) {
            Alert::error('ADPO Error', 'No valid products were selected.');

            return back()
                ->withInput();
        }

        $authorizedTerritory = $request->authorized_territory ?: $this->authorizedTerritory($ad);
        $bankName = $request->payment_method === 'bank_transfer'
            ? ($request->bank_name === 'Other Bank' ? trim($request->other_bank_name) : $request->bank_name)
            : null;
        $shippingType = $request->shipping_type;
        if (strpos($shippingType, 'pickup') === 0) {
            $shippingType = $isRegionVAd ? 'pickup_guinobatan' : 'pickup_lubao';
        }

        $po = DB::transaction(function () use ($request, $user, $ad, $selectedProducts, $products, $authorizedTerritory, $shippingType, $bankName) {
            $poNumber = $this->nextPoNumber();
            $subtotal = 0;
            $totalQty = 0;
            $adUserId = optional($ad)->user_id ?: $user->id;
            $rebateAmount = 0;
            $voucher = null;

            $po = AdPurchaseOrder::create([
                'po_number' => $poNumber,
                'ad_id' => optional($ad)->id,
                'ad_user_id' => $adUserId,
                'business_name' => optional($ad)->business_name ?: optional($ad)->name ?: $user->name,
                'authorized_territory' => $authorizedTerritory,
                'phone_number' => $request->phone_number ?: optional($ad)->contact_number,
                'email_address' => $request->email_address ?: optional($ad)->email_address ?: $user->email,
                'delivery_address' => $request->delivery_address ?: optional($ad)->delivery_address ?: null,
                'shipping_type' => $shippingType,
                'payment_method' => $request->payment_method,
                'bank_name' => $bankName,
                'voucher_id' => null,
                'voucher_code' => null,
                'rebate_amount' => 0,
                'pickup_discount' => 0,
                'delivery_fee' => $shippingType === 'delivered' ? ($request->delivery_fee ?: 0) : 0,
                'uniform_size' => $request->uniform_size,
                'status' => 'Pending',
                'remarks' => $request->remarks,
                'submitted_at' => now(),
                'created_by' => $user->id,
            ]);

            foreach ($selectedProducts as $productId => $line) {
                $product = $products->get((int) $productId);

                if (!$product) {
                    continue;
                }

                $colorBreakdown = $this->colorBreakdown($product, $line);
                $sizeBreakdown = $this->sizeBreakdown($product, $line);
                $colorQty = array_sum($colorBreakdown);
                $sizeQty = array_sum($sizeBreakdown);
                $hasColorVariants = $product->isGazLiteStoveKit();
                $hasSizeVariants = strpos(strtolower(trim((string) ($product->item ?: $product->product_name))), 'gaz lite authorized retail partner uniform') !== false;
                $qty = $hasColorVariants ? $colorQty : ($hasSizeVariants ? $sizeQty : (int) $line['qty']);

                if ($qty <= 0) {
                    continue;
                }

                $unitPrice = $this->dealerPrice($product);
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;
                $totalQty += $qty;

                $po->items()->create([
                    'product_id' => $product->id,
                    'sku' => null,
                    'product_name' => $product->item ?: $product->product_name,
                    'description' => $product->item_description ?: $product->description,
                    'product_image' => $product->item_image,
                    'color_breakdown' => $colorBreakdown ? json_encode($colorBreakdown) : null,
                    'size_breakdown' => $sizeBreakdown ? json_encode($sizeBreakdown) : null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'dealer_points' => 0,
                ]);
            }

            if ($totalQty <= 0) {
                throw ValidationException::withMessages([
                    'products' => 'Please select at least one product and quantity.',
                ]);
            }

            if ($request->use_rebate_voucher === 'yes') {
                $voucher = Voucher::where('code', strtoupper(trim($request->voucher_code)))
                    ->where('name', optional($ad)->store_code)
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    throw ValidationException::withMessages([
                        'voucher_code' => 'Voucher code was not found.',
                    ]);
                }

                if (!$voucher->isUsable($subtotal)) {
                    $message = (float) $subtotal < (float) $voucher->minimum_order_amount
                        ? 'A minimum order of PHP ' . number_format($voucher->minimum_order_amount, 2) . ' is required for this voucher.'
                        : 'Voucher is ' . strtolower($voucher->statusLabel($subtotal)) . '.';

                    throw ValidationException::withMessages([
                        'voucher_code' => $message,
                    ]);
                }

                if (!$voucher->hasArea($authorizedTerritory)) {
                    throw ValidationException::withMessages([
                        'voucher_code' => 'Voucher is not available for the selected authorized territory.',
                    ]);
                }

                $rebateAmount = $voucher->discountFor($subtotal);
                $voucher->used_count = (int) $voucher->used_count + 1;
                $voucher->save();
            }

            $po->subtotal = $subtotal;
            $po->total_qty = $totalQty;
            $po->voucher_id = $voucher ? $voucher->id : null;
            $po->voucher_code = $voucher ? $voucher->code : null;
            $po->rebate_amount = min($rebateAmount, $subtotal);
            $po->pickup_discount = $this->pickupLubaoDiscount($shippingType, $po->items()->get());
            $taxableTotal = max(0, $subtotal - (float) $po->rebate_amount - (float) $po->pickup_discount);
            $ewtBase = $taxableTotal / 1.12;
            $withholdingTax = optional($ad)->withholding_tax ? ($ewtBase * 0.01) : 0;
            $po->withholding_tax = $withholdingTax;
            $po->total_amount = max(0, $taxableTotal - $withholdingTax);
            $po->save();

            return $po;
        });

        $this->notifyGuinobatanWarehouseForRegionV($po);

        Alert::success('ADPO Created', 'ADPO ' . $po->po_number . ' submitted successfully.');

        return redirect()->route('ad-purchase-orders.show', $po->id);
    }

    public function show($id)
    {
        $user = auth()->user();

        $order = AdPurchaseOrder::with(['items.partialReceipts', 'partialReceipts.item', 'ad'])
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->findOrFail($id);

        return view('ad_purchase_orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Area Distributor') {
            abort(403);
        }

        $order = AdPurchaseOrder::with(['ad.userAds'])->findOrFail($id);

        if (in_array($order->status, ['Completed', 'Cancelled'])) {
            Alert::error('ADPO Locked', 'Completed or cancelled ADPO records can no longer be updated.');

            return back();
        }

        $request->validate([
            'status' => 'required|in:Pending,For Delivery,SO Created,Partial Received,For Verification,Completed,Cancelled',
            'payment_method' => 'sometimes|required|in:voucher,cash,gcash,bank_transfer,credit',
            'proof_of_payment' => (auth()->user()->role === 'Area Distributor' || $order->proof_of_payment ? 'nullable' : 'required') . '|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'so_number' => 'nullable|required_if:status,SO Created|string|max:255',
            'payment_date' => 'nullable|required_if:status,SO Created|date',
            'delivery_date' => 'nullable|required_if:status,For Delivery|date',
            'dr_number' => 'nullable|required_if:status,For Delivery|string|max:255',
            'si_number' => 'nullable|required_if:status,For Delivery|string|max:255',
            'remarks' => $remarksRules,
            'items' => 'sometimes|array',
            'items.*.qty' => 'required_with:items|integer|min:0',
            'partial_items' => 'sometimes|array',
            'partial_items.*.received_qty' => 'required_with:partial_items|integer|min:0',
            'partial_items.*.receive_mode' => 'nullable|in:increment,cumulative',
            'partial_items.*.delivery_date' => 'nullable|date',
            'partial_items.*.dr_number' => 'nullable|string|max:255',
            'partial_receipts' => 'sometimes|array',
            'partial_receipts.*.confirmed_qty' => 'required_with:partial_receipts|integer|min:0',
        ]);

        if (auth()->user()->role === 'Area Distributor' && !in_array($request->status, ['Pending', 'Partial Received', 'Completed', 'Cancelled'])) {
            throw ValidationException::withMessages([
                'status' => 'Area Distributors may only select Pending, Partial Received, Completed, or Cancelled.',
            ]);
        }

        if (
            $request->status === 'Partial Received'
            && auth()->user()->role === 'Admin'
            && filled(auth()->user()->warehouse)
            && !$request->filled('dr_number')
            && !filled(
                $order->partialReceipts()->latest('id')->value('dr_number')
                    ?: $order->dr_number
            )
        ) {
            throw ValidationException::withMessages([
                'dr_number' => 'DR number is required and will be used for all received products.',
            ]);
        }

        if ($request->status === 'Cancelled' && !in_array($order->status, ['Pending', 'SO Created'])) {
            throw ValidationException::withMessages([
                'status' => 'Only Pending or SO Created orders may be cancelled.',
            ]);
        }

        if ($order->status === 'Pending' && !in_array($request->status, ['Pending', 'SO Created', 'Cancelled'])) {
            throw ValidationException::withMessages([
                'status' => 'The status must be changed to SO Created before selecting a later status.',
            ]);
        }

        $postedPartialItems = collect($request->input('partial_items', []));
        $postedPartialReceipts = collect($request->input('partial_receipts', []));
        $previousWarehouseDrNumber = null;

        if (
            $request->status === 'Partial Received'
            && auth()->user()->role === 'Admin'
            && filled(auth()->user()->warehouse)
        ) {
            $previousWarehouseDrNumber = $order->partialReceipts()
                ->latest('id')
                ->value('dr_number')
                ?: $order->dr_number;

            if (filled($previousWarehouseDrNumber)) {
                $request->merge([
                    'dr_number' => $this->incrementPartialDrNumber($previousWarehouseDrNumber),
                ]);
            } elseif ($request->filled('dr_number')) {
                $request->merge([
                    'dr_number' => $this->normalizePartialDrNumber($request->dr_number),
                ]);
            }
        }

        $allReceivingConfirmed = $order->items()->get()->isNotEmpty()
            && $order->items()->get()->every(function ($item) {
                $confirmedQty = max(
                    (int) ($item->partial_received_qty ?? 0),
                    (int) $item->partialReceipts()->sum('confirmed_qty')
                );

                return $confirmedQty >= (int) $item->qty;
            })
            && !$order->partialReceipts()->get()->contains(function ($receipt) {
                return (int) $receipt->confirmed_qty < (int) $receipt->received_qty;
            });

        if ($request->status === 'Partial Received' && $postedPartialItems->isEmpty() && $request->has('items')) {
            $postedPartialItems = collect($request->items)->map(function ($item) {
                return ['received_qty' => data_get($item, 'qty', 0)];
            });
        }

        if ($request->status === 'Partial Received' && $request->filled('delivery_date')) {
            $postedPartialItems = $postedPartialItems->map(function ($item) use ($request) {
                $item['delivery_date'] = $request->delivery_date;

                return $item;
            });
        }

        if (
            $request->status === 'Partial Received'
            && auth()->user()->role === 'Admin'
            && filled(auth()->user()->warehouse)
            && $request->filled('dr_number')
        ) {
            $postedPartialItems = $postedPartialItems->map(function ($item) use ($request) {
                if ((int) data_get($item, 'received_qty', 0) > 0) {
                    $item['dr_number'] = strtoupper(trim((string) $request->dr_number));
                }

                return $item;
            });
        }

        if ($request->status === 'Partial Received' && $postedPartialItems->isEmpty() && $postedPartialReceipts->isEmpty()) {
            throw ValidationException::withMessages([
                'partial_items' => 'Please enter at least one received product quantity.',
            ]);
        }

        if ($request->status === 'Completed' && $order->status === 'Partial Received' && auth()->user()->role === 'Area Distributor' && $postedPartialItems->isEmpty() && $postedPartialReceipts->isEmpty()) {
            if (!$allReceivingConfirmed) {
                throw ValidationException::withMessages([
                    'partial_items' => 'Please confirm all received products before completing this DPO.',
                ]);
            }
        }

        if ($request->has('items') || $postedPartialItems->isNotEmpty() || $postedPartialReceipts->isNotEmpty()) {
            $postedItems = collect($request->items ?? []);

            if ($request->status !== 'Partial Received' && $postedItems->contains(function ($item) {
                return (int) data_get($item, 'qty', 0) < 1;
            })) {
                throw ValidationException::withMessages([
                    'items' => 'Item quantities must be at least 1.',
                ]);
            }

            if ($request->status === 'Partial Received' && $postedPartialItems->isNotEmpty()) {
                if ($postedPartialItems->sum(function ($item) {
                    return (int) data_get($item, 'received_qty', 0);
                }) <= 0) {
                    throw ValidationException::withMessages([
                        'partial_items' => 'Please enter at least one received product quantity.',
                    ]);
                }

                $orderItems = $order->items()->whereIn('id', $postedPartialItems->keys()->all())->get()->keyBy('id');

                foreach ($postedPartialItems as $itemId => $item) {
                    $postedReceivedQty = (int) data_get($item, 'received_qty', 0);
                    $orderedItem = $orderItems->get((int) $itemId);

                    if (!$orderedItem) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'Invalid received product selected.',
                        ]);
                    }

                    $orderedQty = (int) $orderedItem->qty;
                    $currentReceivedQty = min(max((int) ($orderedItem->partial_received_qty ?? 0), 0), $orderedQty);
                    $isIncrementReceive = data_get($item, 'receive_mode') === 'increment';
                    $receivedQty = $isIncrementReceive ? $currentReceivedQty + $postedReceivedQty : $postedReceivedQty;
                    $remainingQty = max($orderedQty - $currentReceivedQty, 0);
                    $isAdConfirmation = auth()->user()->role === 'Area Distributor';

                    if ($isAdConfirmation && !$isIncrementReceive && $postedReceivedQty > $currentReceivedQty) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'AD confirmed quantity cannot be greater than the For Receiving quantity.',
                        ]);
                    }

                    if ($isIncrementReceive && $postedReceivedQty > $remainingQty) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'For receiving quantity cannot be greater than the pending quantity.',
                        ]);
                    }

                    if ($receivedQty > $orderedQty) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'Received quantity cannot be greater than the ordered quantity.',
                        ]);
                    }

                    $isNotFullyReceived = $receivedQty < $orderedQty;

                    $needsPartialDocs = auth()->user()->role === 'Admin'
                        && (
                            ($isIncrementReceive && $postedReceivedQty > 0)
                            || (!$isIncrementReceive && $isNotFullyReceived)
                        );
                    $previousPartialDrNumber = $orderedItem->partialReceipts()
                        ->latest('id')
                        ->value('dr_number')
                        ?: $orderedItem->partial_dr_number
                        ?: $order->partialReceipts()
                            ->latest('id')
                            ->value('dr_number')
                        ?: $order->dr_number;

                    if (
                        $needsPartialDocs
                        && $isIncrementReceive
                        && $postedReceivedQty > 0
                        && $currentReceivedQty > 0
                        && blank(data_get($item, 'dr_number'))
                        && filled($previousPartialDrNumber)
                    ) {
                        $item['dr_number'] = $this->incrementPartialDrNumber($previousPartialDrNumber);
                        $postedPartialItems->put($itemId, $item);
                    }

                    if ($needsPartialDocs && blank(data_get($item, 'delivery_date'))) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'Delivery date is required for each product that is not fully received.',
                        ]);
                    }

                    if ($needsPartialDocs && blank(data_get($item, 'dr_number'))) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'DR number is required for each product that is not fully received.',
                        ]);
                    }
                }
            }

            if (in_array($request->status, ['Partial Received', 'Completed']) && auth()->user()->role === 'Area Distributor' && $postedPartialReceipts->isNotEmpty()) {
                if ($postedPartialReceipts->sum(function ($receipt) {
                    return (int) data_get($receipt, 'confirmed_qty', 0);
                }) <= 0) {
                    throw ValidationException::withMessages([
                        'partial_receipts' => 'Please confirm at least one received product quantity.',
                    ]);
                }

                $receiptModels = AdPurchaseOrderPartialReceipt::where('ad_purchase_order_id', $order->id)
                    ->whereIn('id', $postedPartialReceipts->keys()->all())
                    ->get()
                    ->keyBy('id');

                foreach ($postedPartialReceipts as $receiptId => $receiptInput) {
                    $receipt = $receiptModels->get((int) $receiptId);
                    $confirmedQty = (int) data_get($receiptInput, 'confirmed_qty', 0);

                    if (!$receipt) {
                        throw ValidationException::withMessages([
                            'partial_receipts' => 'Invalid partial receipt selected.',
                        ]);
                    }

                    $forConfirmationQty = max((int) $receipt->received_qty - (int) $receipt->confirmed_qty, 0);

                    if ($confirmedQty > $forConfirmationQty) {
                        throw ValidationException::withMessages([
                            'partial_receipts' => 'AD confirmed quantity cannot be greater than the DR quantity for receiving.',
                        ]);
                    }
                }
            }

            if ($request->status === 'Completed' && auth()->user()->role === 'Area Distributor' && ($postedPartialItems->isNotEmpty() || $postedPartialReceipts->isNotEmpty())) {
                $orderItems = $order->items()->get()->keyBy('id');
                $receiptModels = $postedPartialReceipts->isNotEmpty()
                    ? AdPurchaseOrderPartialReceipt::where('ad_purchase_order_id', $order->id)
                        ->whereIn('id', $postedPartialReceipts->keys()->all())
                        ->get()
                        ->keyBy('id')
                    : collect();
                $postedConfirmedByItem = [];

                foreach ($postedPartialReceipts as $receiptId => $receiptInput) {
                    $receipt = $receiptModels->get((int) $receiptId);

                    if ($receipt) {
                        $itemId = (int) $receipt->ad_purchase_order_item_id;
                        $postedConfirmedByItem[$itemId] = ($postedConfirmedByItem[$itemId] ?? 0) + (int) data_get($receiptInput, 'confirmed_qty', 0);
                    }
                }

                foreach ($orderItems as $orderedItem) {
                    $postedPartialItem = $postedPartialItems->get($orderedItem->id) ?: $postedPartialItems->get((string) $orderedItem->id, []);
                    $orderedQty = (int) $orderedItem->qty;
                    $currentReceivedQty = min(max((int) ($orderedItem->partial_received_qty ?? 0), 0), $orderedQty);
                    $postedReceivedQty = (int) data_get($postedPartialItem, 'received_qty', $currentReceivedQty);
                    $isIncrementReceive = data_get($postedPartialItem, 'receive_mode') === 'increment';
                    $finalReceivedQty = $isIncrementReceive
                        ? min($currentReceivedQty + $postedReceivedQty, $orderedQty)
                        : $postedReceivedQty;
                    $confirmedFromReceiptsQty = $orderedItem->partialReceipts()->sum('confirmed_qty') + ($postedConfirmedByItem[$orderedItem->id] ?? 0);

                    if ($postedPartialItems->isNotEmpty() && !$isIncrementReceive && $postedReceivedQty > $currentReceivedQty) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'AD confirmed quantity cannot be greater than the For Receiving quantity.',
                        ]);
                    }

                    if (max($finalReceivedQty, $confirmedFromReceiptsQty) < $orderedQty) {
                        throw ValidationException::withMessages([
                            'partial_items' => 'All products must be confirmed received before completing this DPO.',
                        ]);
                    }
                }
            }
        }

        $oldStatus = $order->status;

        DB::transaction(function () use ($request, $order, $oldStatus, $postedPartialItems, $postedPartialReceipts) {
            $order->status = $request->status;

            if ($request->has('payment_method')) {
                $order->payment_method = $request->payment_method;
            }

            if ($request->hasFile('proof_of_payment')) {
                $file = $request->file('proof_of_payment');
                $filename = $order->po_number . '-' . time() . '.' . $file->getClientOriginalExtension();
                $uploadPath = public_path('uploads/adpo/proof-of-payment');

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);
                $order->proof_of_payment = 'uploads/adpo/proof-of-payment/' . $filename;
            }

            if ($request->has('so_number')) {
                $order->so_number = $request->so_number;
            }

            if ($request->has('payment_date')) {
                $order->payment_date = $request->payment_date;
            }

            if ($request->has('delivery_date')) {
                $order->delivery_date = $request->delivery_date;
            }

            if ($request->filled('dr_number') && blank($order->dr_number)) {
                $order->dr_number = $request->dr_number;
            }

            if ($request->has('si_number')) {
                $order->si_number = $request->si_number;
            }

            if ($request->has('remarks')) {
                $order->remarks = $request->remarks;
            }

            if ($request->status === 'For Delivery' && $request->filled('delivery_date')) {
                $order->items()->get()->each(function ($item) use ($request) {
                    $item->partial_delivery_date = $request->delivery_date;
                    $item->save();
                });
            }

            if (in_array($request->status, ['Partial Received', 'Completed']) && $postedPartialItems->isNotEmpty()) {
                $items = $order->items()->whereIn('id', $postedPartialItems->keys()->all())->get();

                foreach ($items as $item) {
                    $postedPartialItem = $postedPartialItems->get($item->id) ?: $postedPartialItems->get((string) $item->id, []);
                    $postedReceivedQty = (int) data_get($postedPartialItem, 'received_qty', 0);
                    $orderedQty = (int) $item->qty;
                    $currentReceivedQty = min(max((int) ($item->partial_received_qty ?? 0), 0), $orderedQty);
                    $isIncrementReceive = data_get($postedPartialItem, 'receive_mode') === 'increment';
                    $receivedQty = $isIncrementReceive ? $currentReceivedQty + $postedReceivedQty : $postedReceivedQty;
                    $receiptQty = $isIncrementReceive ? $postedReceivedQty : max($receivedQty - $currentReceivedQty, 0);
                    $item->partial_received_qty = $receivedQty;

                    if (auth()->user()->role === 'Admin' && (!$isIncrementReceive || $postedReceivedQty > 0)) {
                        $isNotFullyReceived = $receivedQty < $orderedQty;
                        $shouldStorePartialDocs = ($isIncrementReceive && $postedReceivedQty > 0) || $isNotFullyReceived;
                        $item->partial_delivery_date = $shouldStorePartialDocs ? data_get($postedPartialItem, 'delivery_date') : null;
                        $item->partial_dr_number = $shouldStorePartialDocs ? data_get($postedPartialItem, 'dr_number') : null;

                        if ($receiptQty > 0 && filled(data_get($postedPartialItem, 'delivery_date')) && filled(data_get($postedPartialItem, 'dr_number'))) {
                            AdPurchaseOrderPartialReceipt::create([
                                'ad_purchase_order_id' => $order->id,
                                'ad_purchase_order_item_id' => $item->id,
                                'delivery_date' => data_get($postedPartialItem, 'delivery_date'),
                                'dr_number' => strtoupper((string) data_get($postedPartialItem, 'dr_number')),
                                'received_qty' => $receiptQty,
                                'confirmed_qty' => 0,
                                'status' => 'Pending',
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }

                    $item->save();
                }
            } elseif ($request->has('items') && auth()->user()->role === 'Admin' && in_array($oldStatus, ['Pending', 'SO Created'])) {
                $items = $order->items()->whereIn('id', array_keys($request->items))->get();

                foreach ($items as $item) {
                    $qty = (int) data_get($request->items, $item->id . '.qty', $item->qty);
                    $item->qty = $qty;
                    $item->line_total = (float) $item->unit_price * $qty;
                    $item->save();
                }

                $order->subtotal = $order->items()->sum('line_total');
                $order->total_qty = $order->items()->sum('qty');
                $order->pickup_discount = $this->pickupLubaoDiscount($order->shipping_type, $order->items()->get());
                $taxableTotal = max(0, (float) $order->subtotal - (float) ($order->rebate_amount ?? 0) - (float) ($order->pickup_discount ?? 0));
                $ewtBase = $taxableTotal / 1.12;
                $withholdingTax = optional($order->ad)->withholding_tax ? ($ewtBase * 0.01) : 0;
                $order->withholding_tax = $withholdingTax;
                $order->total_amount = max(0, $taxableTotal - $withholdingTax);
            }

            if (in_array($request->status, ['Partial Received', 'Completed']) && auth()->user()->role === 'Area Distributor' && $postedPartialReceipts->isNotEmpty()) {
                $receipts = AdPurchaseOrderPartialReceipt::where('ad_purchase_order_id', $order->id)
                    ->whereIn('id', $postedPartialReceipts->keys()->all())
                    ->get();

                foreach ($receipts as $receipt) {
                    $receiptInput = $postedPartialReceipts->get($receipt->id) ?: $postedPartialReceipts->get((string) $receipt->id, []);
                    $confirmedQty = (int) data_get($receiptInput, 'confirmed_qty', 0);

                    if ($confirmedQty <= 0) {
                        continue;
                    }

                    $receipt->confirmed_qty = min((int) $receipt->received_qty, (int) $receipt->confirmed_qty + $confirmedQty);
                    $receipt->status = $receipt->confirmed_qty >= $receipt->received_qty ? 'Confirmed' : 'Partial Confirmed';
                    $receipt->confirmed_by = auth()->id();
                    $receipt->confirmed_at = now();
                    $receipt->save();
                }

            }

            if (
                auth()->user()->role === 'Area Distributor'
                && $oldStatus === 'Partial Received'
                && in_array($request->status, ['Partial Received', 'Completed'])
            ) {
                $items = $order->items()->with('partialReceipts')->get();
                $orderedTotal = $items->sum(function ($item) {
                    return (int) $item->qty;
                });
                $confirmedTotal = $items->sum(function ($item) {
                    return min(
                        (int) $item->qty,
                        max(
                            (int) ($item->partial_received_qty ?? 0),
                            (int) $item->partialReceipts->sum('confirmed_qty')
                        )
                    );
                });
                $pendingTotal = max($orderedTotal - $confirmedTotal, 0);

                $order->status = $orderedTotal > 0 && $pendingTotal === 0
                    ? 'Completed'
                    : 'Partial Received';
            }

            $order->save();
        });

        $order->refresh();

        if ($oldStatus !== $order->status) {
            $this->notifyAdpoStatusChanged($order, $oldStatus);
            $this->notifyWarehouseStatusChanged($order, $oldStatus);
        }

        Alert::success('ADPO Updated', 'ADPO updated successfully.');

        return back();
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $order = AdPurchaseOrder::when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->where('status', 'Pending')
            ->findOrFail($id);

        $order->delete();

        Alert::success('ADPO Deleted', 'ADPO deleted successfully.');

        return redirect()
            ->route('ad-purchase-orders.index');
    }

    private function dealerPrice(Item $product)
    {
        return (float) $product->price;
    }

    private function pickupLubaoDiscount($shippingType, $items)
    {
        if ($shippingType !== 'pickup_lubao') {
            return 0;
        }

        return collect($items)->filter(function ($item) {
                return strpos(strtolower((string) $item->product_name), 'lpg refill') !== false;
            })
            ->sum(function ($item) {
                return (float) $item->line_total;
            }) * 0.02;
    }

    private function colorBreakdown(Item $product, array $line)
    {
        $productName = strtolower(trim((string) ($product->item ?: $product->product_name)));

        if (!$product->isGazLiteStoveKit()) {
            return [];
        }

        $colors = [];

        foreach (array_keys($product->availableStoveKitColors()) as $color) {
            $qty = (int) data_get($line, 'colors.' . $color, 0);

            if ($qty > 0) {
                $colors[$color] = $qty;
            }
        }

        return $colors;
    }

    private function sizeBreakdown(Item $product, array $line)
    {
        $productName = strtolower(trim((string) ($product->item ?: $product->product_name)));

        if (strpos($productName, 'gaz lite authorized retail partner uniform') === false) {
            return [];
        }

        $sizes = [];

        foreach ([
            'extra_small' => 'Extra Small',
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'extra_large' => 'Extra Large',
        ] as $size => $label) {
            $qty = (int) data_get($line, 'sizes.' . $size, 0);

            if ($qty > 0) {
                $sizes[$label] = $qty;
            }
        }

        $otherQty = (int) data_get($line, 'sizes.other', 0);
        $otherLabel = trim((string) data_get($line, 'other_size', ''));

        if ($otherQty > 0) {
            $sizes[$otherLabel !== '' ? $otherLabel : 'Other'] = $otherQty;
        }

        return $sizes;
    }

    private function nextPoNumber()
    {
        do {
            $number = 'ADPO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        } while (AdPurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }

    private function authorizedTerritory($ad)
    {
        $territories = $this->territoryOptions($ad);

        if ($territories->isEmpty()) {
            return null;
        }

        return $territories->implode(', ');
    }

    private function territoryOptions($ad)
    {
        if (!$ad || !$ad->areas) {
            return collect();
        }

        return $ad->areas
            ->pluck('area_name')
            ->filter()
            ->unique()
            ->values();
    }

    private function regionVOrderQuery()
    {
        $needles = $this->regionVNeedles();

        return AdPurchaseOrder::query()
            ->where(function ($query) use ($needles) {
                foreach ($needles as $needle) {
                    $query->orWhereRaw('LOWER(delivery_address) LIKE ?', ['%' . $needle . '%']);
                }

                $query->orWhereHas('ad', function ($adQuery) use ($needles) {
                    $adQuery->where(function ($inner) use ($needles) {
                        foreach ($needles as $needle) {
                            $inner->orWhereRaw('LOWER(delivery_address) LIKE ?', ['%' . $needle . '%'])
                                ->orWhereRaw('LOWER(location_region) LIKE ?', ['%' . $needle . '%']);
                        }
                    });
                });
            });
    }

    private function notifyGuinobatanWarehouseForRegionV(AdPurchaseOrder $po)
    {
        $po->loadMissing(['items', 'ad']);

        if (!$this->isRegionVOrder($po)) {
            return;
        }

        $recipients = User::where('role', 'Admin')
            ->whereRaw('LOWER(warehouse) = ?', ['guinobatan'])
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning('Region V ADPO notification skipped: no Guinobatan warehouse email found.', [
                'ad_purchase_order_id' => $po->id,
                'po_number' => $po->po_number,
            ]);

            return;
        }

        try {
            Mail::to($recipients->all())->send(new AdPurchaseOrderWarehouseNotification($po));
        } catch (\Exception $exception) {
            Log::error('Region V ADPO notification failed.', [
                'ad_purchase_order_id' => $po->id,
                'po_number' => $po->po_number,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyAdpoStatusChanged(AdPurchaseOrder $order, $oldStatus)
    {
        $order->loadMissing(['ad.userAds']);

        $recipients = collect([
                $order->email_address,
                optional($order->ad)->email_address,
                optional(optional($order->ad)->userAds)->email,
            ])
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning('ADPO status email skipped: no AD recipient email found.', [
                'ad_purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
            ]);

            return;
        }

        try {
            Mail::to($recipients->all())->send(new AdPurchaseOrderStatusUpdatedMail($order, $oldStatus));
        } catch (\Exception $exception) {
            Log::error('ADPO status email failed.', [
                'ad_purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyWarehouseStatusChanged(AdPurchaseOrder $order, $oldStatus)
    {
        if (!in_array($order->status, ['For Delivery', 'SO Created', 'Partial Received', 'For Verification'])) {
            return;
        }

        $order->loadMissing(['items', 'ad']);

        $warehouse = $this->warehouseForOrder($order);
        $recipients = User::where('role', 'Admin')
            ->when($warehouse, function ($query) use ($warehouse) {
                $query->whereRaw('LOWER(warehouse) = ?', [$warehouse]);
            }, function ($query) {
                $query->whereNotNull('warehouse');
            })
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning('ADPO warehouse status email skipped: no warehouse recipient email found.', [
                'ad_purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
                'status' => $order->status,
                'warehouse' => $warehouse,
            ]);

            return;
        }

        try {
            Mail::to($recipients->all())->send(new AdPurchaseOrderWarehouseStatusNotification($order, $oldStatus));
        } catch (\Exception $exception) {
            Log::error('ADPO warehouse status email failed.', [
                'ad_purchase_order_id' => $order->id,
                'po_number' => $order->po_number,
                'status' => $order->status,
                'warehouse' => $warehouse,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function warehouseForOrder(AdPurchaseOrder $order)
    {
        if ($this->isRegionVOrder($order) || $order->shipping_type === 'pickup_guinobatan') {
            return 'guinobatan';
        }

        if ($order->shipping_type === 'pickup_lubao') {
            return 'lubao';
        }

        return null;
    }

    private function isRegionVOrder(AdPurchaseOrder $po)
    {
        return $this->isRegionVText(
            $po->delivery_address,
            optional($po->ad)->delivery_address,
            optional($po->ad)->location_region
        );
    }

    private function isRegionVText(...$values)
    {
        $regionText = strtolower(implode(' ', array_filter($values)));

        foreach ($this->regionVNeedles() as $needle) {
            if (strpos($regionText, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function regionVNeedles()
    {
        return [
            'region v',
            'region 5',
            'region-5',
            'region-v',
            'bicol',
            'albay',
            'camarines norte',
            'camarines sur',
            'catanduanes',
            'masbate',
            'sorsogon',
        ];
    }
}
