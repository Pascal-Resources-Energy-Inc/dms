<?php

namespace App\Http\Controllers;

use App\InventoryTransfer;
use App\AdPurchaseOrderItem;
use App\Product;
use App\User;
use App\Mail\InventoryReturnRefundNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InventoryTransferController extends Controller
{
    private $crmConnections = ['admin_crms', 'admin_crms2'];

    public function index(Request $request)
    {
        $user = auth()->user();
        $ad = $user->ad;
        $areas = $ad ? $ad->areas->pluck('area_name')->filter()->values() : collect();
        $canTransfer = $areas->count() > 1;
        $singleArea = $areas->count() === 1 ? $areas->first() : null;

        $adItems = $this->completedAdPurchaseOrderItems($user->id);

        $adItemsById = $adItems->keyBy('id');
        $inventoryProducts = Product::whereIn('id', $adItems->pluck('id')->filter()->values())
            ->get()
            ->map(function ($product) use ($adItemsById) {
                $adItem = $adItemsById->get($product->id);

                if ($adItem) {
                    $product->product_name = $adItem->product_name;
                    $product->sku = $adItem->sku ?: $product->sku;
                }

                return $product;
            });
        $products = $inventoryProducts->sortBy('product_name')->values();

        $allMovements = InventoryTransfer::select(
                'id',
                'product_id',
                'sku',
                'item_name',
                'movement_type',
                'from_area',
                'to_area',
                'qty'
            )
            ->where('ad_user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('approval_status')
                    ->orWhere('approval_status', 'Approved')
                    ->orWhere(function ($query) {
                        $query->where('out_type', 'Pull Out')
                            ->where('approval_status', 'For Processing');
                    })
                    ->orWhere(function ($query) {
                        $query->whereIn('out_type', ['Pull Out', 'Replacement'])
                            ->where('approval_status', 'Replacing');
                    })
                    ->orWhere(function ($query) {
                        $query->where('out_type', 'Return and Refund')
                            ->where('approval_status', 'Warehouse Confirmed');
                    });
            })
            ->orderBy('transfer_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $dealerOrderDeductions = $this->completedDealerOrderDeductions(
            $ad ? $ad->id : null,
            $inventoryProducts
        );

        $completedAdPurchaseOrderStock = $this->completedAdPurchaseOrderStock(
            $user->id,
            $inventoryProducts
        );
        $balances = $this->buildBalances($allMovements, $dealerOrderDeductions, $completedAdPurchaseOrderStock);
        $stockProducts = Product::where('ad_user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'Activate')
                    ->orWhereNull('status');
            })
            ->orderBy('product_name')
            ->get();
        $productOptions = $stockProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'sku' => $product->sku,
            ];
        })->values();
        $adProductOptions = $adItems->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'sku' => $product->sku,
            ];
        })->values();
       
        $summary = [
            'total_in' => $allMovements->where('movement_type', 'in')->sum('qty'),
            'total_out' => $allMovements->where('movement_type', 'out')->sum('qty'),
            'total_transfer' => $allMovements->where('movement_type', 'transfer')->sum('qty'),
            'ending_stock' => $balances->sum('qty'),
            'low_stock' => $balances->filter(function ($balance) {
                return $balance->qty > 0 && $balance->qty <= 10;
            })->count(),
            'no_stock' => $balances->where('qty', '<=', 0)->count(),
        ];

        $areaSummaries = $balances->groupBy('area')->map(function ($rows, $area) {
            return (object) [
                'area' => $area,
                'qty' => $rows->sum('qty'),
                'sku_count' => $rows->count(),
            ];
        })->sortBy('area')->values();

        // $productSummaries = $balances->groupBy('product_id')->map(function ($rows) {
        //     $first = $rows->first();

        //     return (object) [
        //         'sku' => $first->sku,
        //         'item_name' => $first->item_name,
        //         'qty' => $rows->sum('qty'),
        //         'area_count' => $rows->count(),
        //     ];
        // })->sortBy('item_name')->values();
        $productSummaries = $balances
            ->filter(function ($balance) {
                return $balance->product_id;
            })
            ->groupBy('product_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'product_id' => $first->product_id,
                    'sku' => $first->sku,
                    'product_name' => $first->product_name,
                    'qty' => $rows->sum('qty'),
                    'area_count' => $rows->pluck('area')->filter()->unique()->count(),
                ];
            })
            ->sortBy('product_name')
            ->values();
        
        $movementQuery = InventoryTransfer::with('product')
            ->where('ad_user_id', $user->id);

        if ($request->filled('movement_type')) {
            $movementQuery->where('movement_type', $request->movement_type);
        }

        if ($request->filled('product_id')) {
            $movementQuery->where('product_id', $request->product_id);
        }

        if ($request->filled('area')) {
            $movementQuery->where(function ($query) use ($request) {
                $query->where('from_area', $request->area)
                    ->orWhere('to_area', $request->area);
            });
        }

        if ($request->filled('date_from')) {
            $movementQuery->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $movementQuery->whereDate('transfer_date', '<=', $request->date_to);
        }

        $movements = $movementQuery
            ->orderBy('transfer_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->appends($request->query());

        $balanceLookup = collect();
        $availableBalanceProductOptions = $balances
            ->filter(function ($balance) {
                return $balance->product_id && (float) ($balance->available_qty ?? $balance->qty) > 0;
            })
            ->groupBy('product_id')
            ->map(function ($rows) {
                $product = $rows->first();

                return [
                    'id' => (int) $product->product_id,
                    'name' => $product->product_name ?: $product->item_name,
                    'sku' => $product->sku,
                ];
            })
            ->values();
        foreach ($balances as $balance) {
            $availableQty = $balance->available_qty ?? $balance->qty;

            if ($balance->product_id) {
                $balanceLookup->put($balance->product_id . '|' . $balance->area, $availableQty);
            }

            foreach ($adItems as $adItem) {
                if ($this->normalizeProductName($adItem->product_name) === $this->normalizeProductName($balance->product_name)) {
                    $balanceLookup->put($adItem->id . '|' . $balance->area, $availableQty);
                }
            }
        }

        return view('inventory_transfers.index', compact(
            'areas',
            'canTransfer',
            'singleArea',
            'products',
            'movements',
            'balances',
            'summary',
            'balanceLookup',
            'areaSummaries',
            'productSummaries',
            'productOptions',
            'availableBalanceProductOptions',
            'adProductOptions',
            'stockProducts',
            'adItems'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $ad = $user->ad;
        $areas = $ad ? $ad->areas->pluck('area_name')->filter()->values()->toArray() : [];

        // A single-area AD always uses their assigned area. This also covers
        // submissions made without the browser-side form behaviour.
        if (count($areas) === 1) {
            $singleArea = $areas[0];

            if ($request->input('movement_type') === 'in' && !$request->filled('to_area')) {
                $request->merge(['to_area' => $singleArea]);
            }

            if ($request->input('movement_type') === 'out' && !$request->filled('from_area')) {
                $request->merge(['from_area' => $singleArea]);
            }
        }

        if ($request->input('movement_type') === 'out' && $request->input('out_type') === 'Pull Out') {
            $request->merge([
                'qty' => $request->input('replacement_qty'),
                'unit_cost' => $request->input('replacement_unit_cost'),
            ]);
        }

        $request->validate([
            'movement_type' => 'required|in:in,out,transfer',
            'product_id' => 'required|integer',
            'from_area' => 'nullable|string|max:255',
            'to_area' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'out_type' => 'nullable|string|max:255',
            'pull_out_attachments' => 'required_if:out_type,Pull Out|array|max:5',
            'pull_out_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'replacement_qty' => 'required_if:out_type,Pull Out|integer|min:1',
            'replacement_unit_cost' => 'nullable|numeric|min:0',
            'transfer_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $type = $request->movement_type;

        if ($type === 'transfer' && count($areas) < 2) {
            return back()->withInput()->with('error', 'Transfer is unavailable because your account has only one assigned area.');
        }

        $allowedReasons = [
            'in' => ['Beginning Balance', 'Inventory Adjustment', 'Replacement'],
            'out' => ['Return and Refund', 'Pull Out', 'Replace'],
        ];

        if (in_array($type, ['in', 'out'], true) && !in_array($request->out_type, $allowedReasons[$type], true)) {
            return back()->withInput()->with('error', 'Please select a valid movement type.');
        }
        $adItems = $this->completedAdPurchaseOrderItems($user->id);
        $adItem = $adItems->firstWhere('id', (int) $request->product_id);
        // OUT and transfer selectors are populated from the completed ADPO
        // stock balance. Those products may be system products (without this
        // AD's `ad_user_id`), so do not reject a valid balance product merely
        // because it is not an AD-owned product record.
        $stockProduct = Product::where('ad_user_id', $user->id)
            ->where('id', $request->product_id)
            ->first();
        $selectedProduct = $stockProduct ?: Product::find($request->product_id);

        // A completed ADPO can remain in stock after its product master row
        // has been removed or moved to another account. Keep that stock
        // usable by resolving the ADPO product details as an in-memory model.
        if (!$selectedProduct && $adItem) {
            $selectedProduct = new Product();
            $selectedProduct->id = $adItem->id;
            $selectedProduct->sku = $adItem->sku;
            $selectedProduct->product_name = $adItem->product_name;
        }

        if (!$selectedProduct) {
            $stockMovement = InventoryTransfer::where('ad_user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->latest('id')
                ->first();

            if ($stockMovement) {
                $selectedProduct = new Product();
                $selectedProduct->id = $stockMovement->product_id;
                $selectedProduct->sku = $stockMovement->sku;
                $selectedProduct->product_name = $stockMovement->item_name;
            }
        }

        if ($type === 'in' && !$request->to_area) {
            return back()->withInput()->with('error', 'Please select the receiving area for inventory IN.');
        }

        if ($type === 'out' && !$request->from_area) {
            return back()->withInput()->with('error', 'Please select the source area for inventory OUT.');
        }

        if ($type === 'transfer') {
            if (!$request->from_area || !$request->to_area) {
                return back()->withInput()->with('error', 'Please select both source and receiving areas for transfer.');
            }

            if ($request->from_area === $request->to_area) {
                return back()->withInput()->with('error', 'Source and receiving areas must be different.');
            }
        }

        foreach ([$request->from_area, $request->to_area] as $area) {
            if ($area && !in_array($area, $areas)) {
                return back()->withInput()->with('error', 'Selected area is not assigned to your account.');
            }
        }

        if ($type === 'in' && !$stockProduct) {
            return back()->withInput()->with('error', 'Please select a product from your AD stock product list.');
        }

        if (in_array($type, ['out', 'transfer']) && !$selectedProduct) {
            return back()->withInput()->with('error', 'Please select a valid product.');
        }

        $product = $selectedProduct;

        if ($adItem) {
            $product->product_name = $adItem->product_name;
            $product->sku = $adItem->sku ?: $product->sku;
        }

        $replacementProduct = $type === 'out' && $request->out_type === 'Pull Out' ? $product : null;
        $isReturnRefund = $type === 'out' && $request->out_type === 'Return and Refund';

        if (in_array($type, ['out', 'transfer'])) {
            $availableQty = $this->availableQty($user->id, $ad ? $ad->id : null, $request->from_area, $product);

            if ($request->qty > $availableQty) {
                return back()->withInput()->with('error', 'Not enough stock in source area. Available: ' . number_format($availableQty));
            }
        }

        $pullOutAttachments = [];
        if ($type === 'out' && $request->out_type === 'Pull Out' && $request->hasFile('pull_out_attachments')) {
            $uploadPath = public_path('uploads/inventory-movements/pull-outs');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            foreach ($request->file('pull_out_attachments') as $file) {
                $filename = 'pull-out-' . $user->id . '-' . str_replace('.', '', uniqid('', true)) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/inventory-movements/pull-outs/' . $filename;
                $file->move($uploadPath, $filename);
                $pullOutAttachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        $assignedWarehouse = ($replacementProduct || $isReturnRefund) ? $this->warehouseForAuthenticatedAd() : null;
        $movementReferences = DB::transaction(function () use ($request, $user, $ad, $product, $replacementProduct, $isReturnRefund, $type, $pullOutAttachments, $assignedWarehouse) {
            $movement = InventoryTransfer::create([
                'ad_id' => $ad ? $ad->id : null,
                'ad_user_id' => $user->id,
                'product_id' => $product->id,
                'sku' => $product->sku,
                'item_name' => $product->product_name,
                'movement_type' => $type,
                'from_area' => in_array($type, ['out', 'transfer']) ? $request->from_area : null,
                'to_area' => in_array($type, ['in', 'transfer']) ? $request->to_area : null,
                'qty' => $request->qty,
                'unit_cost' => $request->unit_cost,
                'reference_no' => null,
                'pull_out_attachments' => $pullOutAttachments ?: null,
                'replacement_product_id' => $replacementProduct ? $replacementProduct->id : null,
                'replacement_sku' => $replacementProduct ? $replacementProduct->sku : null,
                'replacement_item_name' => $replacementProduct ? $replacementProduct->product_name : null,
                'replacement_qty' => $replacementProduct ? $request->replacement_qty : null,
                'replacement_unit_cost' => $replacementProduct ? $request->replacement_unit_cost : null,
                'approval_status' => ($replacementProduct || $isReturnRefund) ? 'Pending' : 'Approved',
                'warehouse' => ($replacementProduct || $isReturnRefund) ? $assignedWarehouse : null,
                'transfer_date' => $request->transfer_date ?: Carbon::today()->toDateString(),
                'remarks' => $request->remarks,
                'out_type' => $request->out_type,
                'created_by' => $user->id,
            ]);

            $referenceNo = 'MOV-' . Carbon::now()->format('Ymd') . '-' . str_pad($movement->id, 6, '0', STR_PAD_LEFT);
            $replacementReferenceNo = null;

            if ($replacementProduct) {
                DB::table('inventory_transfers')->where('id', $movement->id)->update(['reference_no' => $referenceNo]);
                return [$referenceNo, null, true, $movement->id];
            }

            if ($replacementProduct) {
                $replacementMovement = InventoryTransfer::create([
                    'ad_id' => $ad ? $ad->id : null,
                    'ad_user_id' => $user->id,
                    'product_id' => $replacementProduct->id,
                    'sku' => $replacementProduct->sku,
                    'item_name' => $replacementProduct->product_name,
                    'movement_type' => 'in',
                    'to_area' => $request->from_area,
                    'qty' => $request->replacement_qty,
                    'unit_cost' => $request->replacement_unit_cost,
                    'related_movement_id' => $movement->id,
                    'transfer_date' => $request->transfer_date ?: Carbon::today()->toDateString(),
                    'remarks' => 'Replacement for pull-out movement ' . $referenceNo,
                    'out_type' => 'Replacement',
                    'created_by' => $user->id,
                ]);

                $replacementReferenceNo = 'MOV-' . Carbon::now()->format('Ymd') . '-' . str_pad($replacementMovement->id, 6, '0', STR_PAD_LEFT);
                DB::table('inventory_transfers')->where('id', $movement->id)->update(['related_movement_id' => $replacementMovement->id]);
                DB::table('inventory_transfers')->where('id', $replacementMovement->id)->update(['reference_no' => $replacementReferenceNo]);
            }

            DB::table('inventory_transfers')
                ->where('id', $movement->id)
                ->update(['reference_no' => $referenceNo]);

            return [$referenceNo, $replacementReferenceNo, false, $movement->id];
        });

        if ($isReturnRefund) {
            $this->notifyDennisReturnRefund(InventoryTransfer::find($movementReferences[3]));
            return redirect()->route('inventory-transfers.index')->with('success', 'Return and Refund request ' . $movementReferences[0] . ' was sent to Dennis Villareal for approval.');
        }

        if ($movementReferences[2]) {
            return redirect()->route('inventory-transfers.index')->with('success', 'Pull Out request ' . $movementReferences[0] . ' was sent to ' . ucfirst($assignedWarehouse) . ' warehouse for approval.');
        }

        $message = 'Inventory movement ' . $movementReferences[0] . ' saved successfully.';
        if ($movementReferences[1]) {
            $message .= ' Replacement movement ' . $movementReferences[1] . ' was added to the same area.';
        }

        return redirect()->route('inventory-transfers.index')->with('success', $message);
    }

    public function returnRefundRequests()
    {
        $this->ensureReturnRefundReviewer();

        $requests = InventoryTransfer::where('out_type', 'Return and Refund')
            ->when(!$this->isDennisApprover(), function ($query) {
                $query->where('warehouse', strtolower((string) auth()->user()->warehouse));
            })
            ->with(['product', 'creator'])
            ->latest()
            ->get();

        return view('inventory_transfers.return_refunds', compact('requests'));
    }

    public function approveReturnRefund(Request $request, $id)
    {
        abort_unless($this->isDennisApprover(), 403);
        $request->validate(['decision' => 'required|in:Approved,Rejected', 'warehouse_remarks' => 'nullable|string|max:1000']);
        $movement = InventoryTransfer::where('out_type', 'Return and Refund')->findOrFail($id);
        abort_unless($movement->approval_status === 'Pending', 422);

        $movement->update([
            'approval_status' => $request->decision,
            'warehouse_remarks' => $request->warehouse_remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Return and Refund request ' . strtolower($request->decision) . '.');
    }

    public function uploadReturnDocuments(Request $request, $id)
    {
        $movement = InventoryTransfer::where('ad_user_id', auth()->id())->where('out_type', 'Return and Refund')->findOrFail($id);
        abort_unless($movement->approval_status === 'Approved', 422);
        $request->validate([
            'ris_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'return_attachments' => 'required|array|max:5',
            'return_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachments = $this->storeReturnAttachments($request->file('return_attachments'), $movement->id);
        $movement->update([
            'ris_number' => strtoupper(trim($request->ris_number)),
            'return_date' => $request->return_date,
            'return_attachments' => $attachments,
            'approval_status' => 'Documents Submitted',
        ]);

        return back()->with('success', 'Return documents submitted to the warehouse.');
    }

    public function receiveReturnRefund(Request $request, $id)
    {
        $this->ensureWarehouseUser();
        $request->validate([
            'warehouse_received_qty' => 'required|integer|min:1',
            'warehouse_reference_no' => 'required|string|max:255',
            'warehouse_remarks' => 'nullable|string|max:1000',
        ]);
        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('warehouse', strtolower((string) auth()->user()->warehouse))
            ->findOrFail($id);
        abort_unless($movement->approval_status === 'Documents Submitted', 422);
        if ($request->warehouse_received_qty > $movement->qty) return back()->with('error', 'Received quantity cannot exceed the requested quantity.');

        $product = $this->productForInventoryTransfer($movement);
        if (!$product) {
            return back()->with('error', 'The product for this return request could not be resolved.');
        }

        $availableQty = $this->availableQty($movement->ad_user_id, $movement->ad_id, $movement->from_area, $product);
        if ($request->warehouse_received_qty > $availableQty) {
            return back()->with('error', 'Received quantity exceeds the distributor’s current available stock: ' . number_format($availableQty) . '.');
        }

        $movement->update([
            'qty' => $request->warehouse_received_qty,
            'warehouse_received_qty' => $request->warehouse_received_qty,
            'warehouse_reference_no' => strtoupper(trim($request->warehouse_reference_no)),
            'warehouse_received_at' => Carbon::now(),
            'warehouse_remarks' => $request->warehouse_remarks,
            'approval_status' => 'Warehouse Confirmed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);
        $this->notifyAdReturnReceived($movement->fresh());

        return back()->with('success', 'Return received and confirmed. The Area/Provincial Distributor was emailed.');
    }

    private function isDennisApprover(): bool
    {
        return strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    }

    private function ensureReturnRefundReviewer(): void
    {
        if ($this->isDennisApprover()) return;
        $this->ensureWarehouseUser();
    }

    private function storeReturnAttachments(array $files, int $movementId): array
    {
        $directory = public_path('uploads/inventory-movements/return-refunds');
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        return collect($files)->map(function ($file) use ($directory, $movementId) {
            $filename = 'return-' . $movementId . '-' . str_replace('.', '', uniqid('', true)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);
            return ['path' => 'uploads/inventory-movements/return-refunds/' . $filename, 'name' => $file->getClientOriginalName()];
        })->all();
    }

    private function productForInventoryTransfer(InventoryTransfer $movement): ?Product
    {
        if ($movement->product) {
            return $movement->product;
        }

        if (!$movement->product_id) {
            return null;
        }

        $product = Product::find($movement->product_id);
        if ($product) {
            return $product;
        }

        $product = new Product();
        $product->id = $movement->product_id;
        $product->sku = $movement->sku;
        $product->product_name = $movement->item_name;

        return $product;
    }

    private function notifyDennisReturnRefund(InventoryTransfer $movement): void
    {
        $email = User::whereRaw('LOWER(TRIM(name)) = ?', ['dennis villareal'])->value('email');
        if (!$email) { Log::warning('Return/refund notification skipped: Dennis Villareal has no user email.', ['movement_id' => $movement->id]); return; }
        try { Mail::to($email)->send(new InventoryReturnRefundNotification($movement, 'approver')); } catch (\Exception $e) { Log::error('Return/refund approval email failed.', ['movement_id' => $movement->id, 'error' => $e->getMessage()]); }
    }

    private function notifyAdReturnReceived(InventoryTransfer $movement): void
    {
        $email = optional(User::find($movement->ad_user_id))->email;
        if (!$email) return;
        try { Mail::to($email)->send(new InventoryReturnRefundNotification($movement, 'ad')); $movement->update(['ad_notified_at' => Carbon::now()]); } catch (\Exception $e) { Log::error('Return/refund AD email failed.', ['movement_id' => $movement->id, 'error' => $e->getMessage()]); }
    }

    public function warehousePullOuts()
    {
        $this->ensureWarehouseUser();
        $warehouse = strtolower((string) auth()->user()->warehouse);
        $requests = InventoryTransfer::where('out_type', 'Pull Out')
            ->where('warehouse', $warehouse)
            ->with('product')
            ->orderByRaw("CASE approval_status WHEN 'Pending' THEN 0 WHEN 'For Processing' THEN 1 WHEN 'Replacing' THEN 2 ELSE 3 END")
            ->latest()
            ->get();
        return view('inventory_transfers.warehouse_pull_outs', compact('requests', 'warehouse'));
    }

    public function reviewPullOut(Request $request, $id)
    {
        $this->ensureWarehouseUser();
        $request->validate([
            'decision' => 'required|in:For Processing,Rejected,Replacing',
            'replacement_qty' => 'required_if:decision,For Processing|integer|min:1',
            'replacement_unit_cost' => 'nullable|numeric|min:0',
            'replacement_dr_number' => 'required_if:decision,Replacing|string|max:255',
            'warehouse_remarks' => 'nullable|string|max:1000',
        ]);

        $movement = InventoryTransfer::where('out_type', 'Pull Out')
            ->where('warehouse', strtolower((string) auth()->user()->warehouse))
            ->with('product')
            ->findOrFail($id);

        if ($request->decision === 'Rejected') {
            if ($movement->approval_status !== 'Pending') {
                return back()->with('error', 'Only pending Pull Out requests can be rejected.');
            }

            $movement->update([
                'approval_status' => 'Rejected',
                'warehouse_remarks' => $request->warehouse_remarks,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            return back()->with('success', 'Pull Out request rejected. No stock movement was posted.');
        }

        if ($request->decision === 'For Processing') {
            if ($movement->approval_status !== 'Pending') {
                return back()->with('error', 'Only pending Pull Out requests can be processed.');
            }

            if ($request->replacement_qty > $movement->replacement_qty) {
                return back()->with('error', 'Approved quantity cannot be higher than the requested quantity of ' . number_format($movement->replacement_qty) . '.');
            }

            $movement->update([
                'qty' => $request->replacement_qty,
                'unit_cost' => $request->replacement_unit_cost,
                'approval_status' => 'For Processing',
                'warehouse_remarks' => $request->warehouse_remarks,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            return back()->with('success', 'Pull Out request is now For Processing.');
        }

        if ($movement->approval_status !== 'For Processing') {
            return back()->with('error', 'Only requests marked For Processing can be replaced.');
        }

        $available = $this->availableQty($movement->ad_user_id, $movement->ad_id, $movement->from_area, $movement->product);
        if ($movement->qty > $available) {
            return back()->with('error', 'Replacement cannot exceed available stock: ' . number_format($available));
        }

        DB::transaction(function () use ($movement, $request) {
            $movement->update([
                'approval_status' => 'Replacing',
                'replacement_dr_number' => strtoupper(trim($request->replacement_dr_number)),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            $replacement = InventoryTransfer::create([
                'ad_id' => $movement->ad_id,
                'ad_user_id' => $movement->ad_user_id,
                'product_id' => $movement->product_id,
                'sku' => $movement->sku,
                'item_name' => $movement->item_name,
                'movement_type' => 'in',
                'to_area' => $movement->from_area,
                'qty' => $movement->qty,
                'unit_cost' => $movement->unit_cost,
                'out_type' => 'Replacement',
                'related_movement_id' => $movement->id,
                'approval_status' => 'Replacing',
                'replacement_dr_number' => strtoupper(trim($request->replacement_dr_number)),
                'transfer_date' => $movement->transfer_date,
                'remarks' => 'Warehouse replacement for ' . $movement->reference_no . ' (DR # ' . strtoupper(trim($request->replacement_dr_number)) . ')',
                'created_by' => auth()->id(),
            ]);

            DB::table('inventory_transfers')->where('id', $replacement->id)->update([
                'reference_no' => 'MOV-' . Carbon::now()->format('Ymd') . '-' . str_pad($replacement->id, 6, '0', STR_PAD_LEFT),
            ]);
            $movement->update(['related_movement_id' => $replacement->id]);
        });

        return back()->with('success', 'Replacement stock posted with DR # ' . strtoupper(trim($request->replacement_dr_number)) . '.');
    }

    private function ensureWarehouseUser() { if (auth()->user()->role !== 'Admin' || !in_array(strtolower((string) auth()->user()->warehouse), ['lubao', 'guinobatan'], true)) abort(403); }
    private function warehouseForAuthenticatedAd()
    {
        $ad = auth()->user()->ad;
        $text = strtolower(($ad->location_region ?? '') . ' ' . ($ad->location_province ?? ''));

        return preg_match('/region v|region 5|bicol|albay|camarines|catanduanes|masbate|sorsogon/', $text)
            ? 'guinobatan'
            : 'lubao';
    }

    public function destroy($id)
    {
        $movement = InventoryTransfer::where('ad_user_id', auth()->id())->findOrFail($id);
        $user = auth()->user();
        $ad = $user->ad;
        $products = Product::where('ad_user_id', $user->id)->get();
        $dealerOrderDeductions = $this->completedDealerOrderDeductions($ad ? $ad->id : null, $products);
        $completedAdPurchaseOrderStock = $this->completedAdPurchaseOrderStock($user->id, $products);
        $remainingMovements = InventoryTransfer::where('ad_user_id', auth()->id())
            ->where('id', '!=', $movement->id)
            ->get();

        $negativeBalance = $this->buildBalances($remainingMovements, $dealerOrderDeductions, $completedAdPurchaseOrderStock)->first(function ($balance) {
            return $balance->qty < 0;
        });

        if ($negativeBalance) {
            return back()->with('error', 'Cannot delete this movement because it will create negative stock for ' . $negativeBalance->item_name . ' in ' . $negativeBalance->area . '.');
        }

        $movement->delete();

        return back()->with('success', 'Inventory movement deleted successfully.');
    }

    private function availableQty($adUserId, $adId, $area, Product $product)
    {
        $movements = InventoryTransfer::where('ad_user_id', $adUserId)
            ->where('product_id', $product->id)
            ->where(function ($query) {
                $query->whereNull('approval_status')
                    ->orWhere('approval_status', 'Approved')
                    ->orWhere(function ($query) {
                        $query->where('out_type', 'Pull Out')
                            ->where('approval_status', 'For Processing');
                    })
                    ->orWhere(function ($query) {
                        $query->whereIn('out_type', ['Pull Out', 'Replacement'])
                            ->where('approval_status', 'Replacing');
                    })
                    ->orWhere(function ($query) {
                        $query->where('out_type', 'Return and Refund')
                            ->where('approval_status', 'Warehouse Confirmed');
                    });
            })
            ->get();

        $qty = 0;

        foreach ($movements as $movement) {
            if ($movement->movement_type === 'in' && $movement->to_area === $area) {
                $qty += $movement->qty;
            }

            if ($movement->movement_type === 'out' && $movement->from_area === $area) {
                $qty -= $movement->qty;
            }

            if ($movement->movement_type === 'transfer') {
                if ($movement->from_area === $area) {
                    $qty -= $movement->qty;
                }

                if ($movement->to_area === $area) {
                    $qty += $movement->qty;
                }
            }
        }

        $dealerOrderQty = $this->dealerOrderQtyForAreaProduct($adId, $area, $product);

        $adPurchaseOrderQty = AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as adpo_areas'), 'ad_purchase_orders.ad_id', '=', 'adpo_areas.ad_id')
            ->where('ad_purchase_orders.ad_user_id', $adUserId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->where(function ($query) use ($area) {
                $query->where('ad_purchase_orders.authorized_territory', $area)
                    ->orWhere(function ($query) use ($area) {
                        $query->whereNull('ad_purchase_orders.authorized_territory')
                            ->where('adpo_areas.area_name', $area);
                    });
            })
            ->where(function ($query) use ($product) {
                $query->where('ad_purchase_order_items.product_id', $product->id)
                    ->orWhereRaw('LOWER(TRIM(ad_purchase_order_items.product_name)) = ?', [$this->normalizeProductName($product->product_name)]);
            })
            ->sum('ad_purchase_order_items.qty');

        return $qty + $adPurchaseOrderQty - $dealerOrderQty;
    }

    private function completedAdPurchaseOrderItems($adUserId)
    {
        return AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin('products', 'ad_purchase_order_items.product_id', '=', 'products.id')
            ->where('ad_purchase_orders.ad_user_id', $adUserId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->whereNotNull('ad_purchase_order_items.product_id')
            ->select(
                'ad_purchase_order_items.product_id',
                DB::raw('MAX(ad_purchase_order_items.product_name) as ad_item_name'),
                DB::raw('MAX(products.product_name) as product_name'),
                DB::raw('MAX(products.sku) as sku')
            )
            ->groupBy('ad_purchase_order_items.product_id')
            ->orderBy('ad_item_name')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => (int) $item->product_id,
                    'sku' => $item->sku,
                    'product_name' => $item->ad_item_name ?: $item->product_name,
                ];
            })
            ->filter(function ($item) {
                return $item->id && $item->product_name;
            })
            ->values();
    }

    private function buildBalances($movements, $dealerOrderDeductions = null, $completedAdPurchaseOrderStock = null)
    {
        $balances = collect();

        foreach ($movements as $movement) {
            if ($movement->movement_type === 'in') {
                $this->addBalance($balances, $movement, $movement->to_area, $movement->qty, 0);
            }

            if ($movement->movement_type === 'out') {
                $this->addBalance($balances, $movement, $movement->from_area, -1 * $movement->qty, 0);
            }

            if ($movement->movement_type === 'transfer') {
                $this->addBalance($balances, $movement, $movement->from_area, -1 * $movement->qty, 0);
                $this->addBalance($balances, $movement, $movement->to_area, $movement->qty, 0);
            }
        }

        foreach (($completedAdPurchaseOrderStock ?: collect()) as $adpoStock) {
            $this->addBalance($balances, $adpoStock, $adpoStock->area, $adpoStock->qty, 0);
        }

        foreach (($dealerOrderDeductions ?: collect()) as $orderDeduction) {
            $this->addBalance($balances, $orderDeduction, $orderDeduction->area, -1 * $orderDeduction->qty, $orderDeduction->qty);
        }
        
        return $balances->map(function ($row) {
            $row['stock_after_movement'] = $row['qty'] + $row['ordered_qty'];
            $row['available_qty'] = $row['stock_after_movement'] - $row['ordered_qty'];

            return (object) $row;
        })->sortBy('sku')->sortBy('area')->values();
    }

    private function completedDealerOrderDeductions($adId, $products)
    {
        if (!$adId) {
            return collect();
        }

        $deductions = $this->dealerOrderDeductionsForConnection(null, $adId, $products);

        foreach ($this->crmConnections as $connection) {
            $deductions = $deductions->merge($this->dealerOrderDeductionsForConnection($connection, $adId, $products));
        }

        return $deductions->values();
    }

    private function dealerOrderQtyForAreaProduct($adId, $area, Product $product)
    {
        if (!$adId || !$area) {
            return 0;
        }

        $qty = $this->dealerOrderQtyForConnection(null, $adId, $area, $product);

        foreach ($this->crmConnections as $connection) {
            $qty += $this->dealerOrderQtyForConnection($connection, $adId, $area, $product);
        }

        return $qty;
    }

    private function dealerOrderDeductionsForConnection($connection, $adId, $products)
    {
        $queryData = $this->dealerOrderBaseQuery($connection, $adId);

        if (!$queryData) {
            return collect();
        }

        [$query, $meta] = $queryData;

        if (!$this->dealerOrderHasAreaSource($meta)) {
            return collect();
        }

        $productsById = $products->keyBy('id');
        $productsByName = $products->keyBy(function ($product) {
            return $this->normalizeProductName($product->product_name);
        });

        $query->where(function ($query) use ($meta) {
                $hasAreaFilter = false;

                if ($meta['has_dealer_area']) {
                    $query->whereNotNull('d.area');
                    $hasAreaFilter = true;
                }

                if ($meta['has_ad_area']) {
                    $hasAreaFilter
                        ? $query->orWhereNotNull('ordering_ad_areas.area_name')
                        : $query->whereNotNull('ordering_ad_areas.area_name');
                    $hasAreaFilter = true;
                }

                if ($meta['has_guest_territory']) {
                    $hasAreaFilter
                        ? $query->orWhereNotNull('od.guest_authorized_territory')
                        : $query->whereNotNull('od.guest_authorized_territory');
                }
            })
            ->groupBy('od.item');

        if ($meta['has_dealer_area']) {
            $query->groupBy('d.area');
        }

        if ($meta['has_ad_area']) {
            $query->groupBy('ordering_ad_areas.area_name');
        }

        if ($meta['has_guest_territory']) {
            $query->groupBy('od.guest_authorized_territory');
        }

        if ($meta['has_product_id']) {
            $query->groupBy('od.product_id');
        }

        return $query->get()
            ->map(function ($order) use ($productsById, $productsByName, $meta) {
                $product = null;

                if ($meta['has_product_id'] && !empty($order->product_id)) {
                    $product = $productsById->get($order->product_id);
                }

                if (!$product) {
                    $product = $productsByName->get($this->normalizeProductName($order->item));
                }

                return (object) [
                    'area' => ($meta['has_guest_territory'] ? $order->guest_area : null) ?: $order->dealer_area ?: $order->ad_area,
                    'product_id' => $product ? $product->id : null,
                    'sku' => $product ? $product->sku : null,
                    'product_name' => $product ? $product->product_name : $order->item,
                    'item_name' => $product ? $product->product_name : $order->item,
                    'qty' => (float) $order->qty,
                    'source_database' => $meta['connection'] ?: config('database.default'),
                ];
            });
    }

    private function dealerOrderQtyForConnection($connection, $adId, $area, Product $product)
    {
        $queryData = $this->dealerOrderBaseQuery($connection, $adId);

        if (!$queryData) {
            return 0;
        }

        [$query, $meta] = $queryData;

        if (!$this->dealerOrderHasAreaSource($meta)) {
            return 0;
        }

        $query->where(function ($query) use ($area, $meta) {
                $hasAreaFilter = false;

                if ($meta['has_dealer_area']) {
                    $query->where('d.area', $area);
                    $hasAreaFilter = true;
                }

                if ($meta['has_ad_area']) {
                    $hasAreaFilter
                        ? $query->orWhere('ordering_ad_areas.area_name', $area)
                        : $query->where('ordering_ad_areas.area_name', $area);
                    $hasAreaFilter = true;
                }

                if ($meta['has_guest_territory']) {
                    $hasAreaFilter
                        ? $query->orWhere('od.guest_authorized_territory', $area)
                        : $query->where('od.guest_authorized_territory', $area);
                }
            })
            ->where(function ($query) use ($product, $meta) {
                if ($meta['has_product_id']) {
                    $query->where('od.product_id', $product->id)
                        ->orWhereRaw('LOWER(TRIM(od.item)) = ?', [$this->normalizeProductName($product->product_name)]);
                    return;
                }

                $query->whereRaw('LOWER(TRIM(od.item)) = ?', [$this->normalizeProductName($product->product_name)]);
            });

        return (float) $query->sum('od.qty');
    }

    private function dealerOrderHasAreaSource($meta)
    {
        return $meta['has_dealer_area'] || $meta['has_ad_area'] || $meta['has_guest_territory'];
    }

    private function dealerOrderBaseQuery($connection, $adId)
    {
        try {
            $database = $connection ? DB::connection($connection) : DB::connection();
            $schema = $database->getSchemaBuilder();

            if (!$schema->hasTable('order_details')
                || !$schema->hasColumn('order_details', 'ad_id')
                || !$schema->hasColumn('order_details', 'item')
                || !$schema->hasColumn('order_details', 'qty')) {
                return null;
            }

            $hasProductId = $schema->hasColumn('order_details', 'product_id');
            $hasGuestTerritory = $schema->hasColumn('order_details', 'guest_authorized_territory');
            $hasDealerId = $schema->hasColumn('order_details', 'dealer_id');
            $hasDealers = $schema->hasTable('dealers') && $hasDealerId;
            $dealerJoinColumn = null;
            $hasDealerArea = false;

            if ($hasDealers) {
                if ($schema->hasColumn('dealers', 'user_id')) {
                    $dealerJoinColumn = 'user_id';
                } elseif ($schema->hasColumn('dealers', 'id')) {
                    $dealerJoinColumn = 'id';
                }

                $hasDealerArea = $dealerJoinColumn && $schema->hasColumn('dealers', 'area');
            }

            $hasAdArea = $hasDealerId
                && $schema->hasTable('area_distributors')
                && $schema->hasTable('ad_areas')
                && $schema->hasColumn('area_distributors', 'user_id')
                && $schema->hasColumn('area_distributors', 'id')
                && $schema->hasColumn('ad_areas', 'ad_id')
                && $schema->hasColumn('ad_areas', 'area_name');

            $selects = [
                $hasGuestTerritory ? 'od.guest_authorized_territory as guest_area' : DB::raw('NULL as guest_area'),
                $hasDealerArea ? 'd.area as dealer_area' : DB::raw('NULL as dealer_area'),
                $hasAdArea ? 'ordering_ad_areas.area_name as ad_area' : DB::raw('NULL as ad_area'),
                'od.item',
                DB::raw('SUM(od.qty) as qty'),
            ];

            if ($hasProductId) {
                $selects[] = 'od.product_id';
            }

            $query = $database->table('order_details as od')
                ->select($selects)
                ->where('od.ad_id', $adId);

            if ($schema->hasColumn('order_details', 'status')) {
                $query->where('od.status', 'Completed');
            }

            if ($schema->hasColumn('order_details', 'deleted_at')) {
                $query->whereNull('od.deleted_at');
            }

            if ($hasDealerArea) {
                $query->leftJoin('dealers as d', 'od.dealer_id', '=', 'd.' . $dealerJoinColumn);
            }

            if ($hasAdArea) {
                $query->leftJoin('area_distributors as ordering_ads', 'od.dealer_id', '=', 'ordering_ads.user_id');

                $adAreaWhere = $schema->hasColumn('ad_areas', 'deleted_at') ? ' where deleted_at is null' : '';
                $query->leftJoin(
                    DB::raw('(select ad_id, min(area_name) as area_name from ad_areas' . $adAreaWhere . ' group by ad_id) as ordering_ad_areas'),
                    'ordering_ads.id',
                    '=',
                    'ordering_ad_areas.ad_id'
                );
            }

            return [$query, [
                'connection' => $connection,
                'has_product_id' => $hasProductId,
                'has_guest_territory' => $hasGuestTerritory,
                'has_dealer_area' => $hasDealerArea,
                'has_ad_area' => $hasAdArea,
            ]];
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeProductName($name)
    {
        return strtolower(trim((string) $name));
    }

    private function completedAdPurchaseOrderStock($adUserId, $products)
    {
        $productsById = $products->keyBy('id');
        $productsByName = $products->keyBy(function ($product) {
            return $this->normalizeProductName($product->product_name);
        });

        return AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as adpo_areas'), 'ad_purchase_orders.ad_id', '=', 'adpo_areas.ad_id')
            ->select(
                'ad_purchase_orders.authorized_territory as territory_area',
                'adpo_areas.area_name as ad_area',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.sku',
                'ad_purchase_order_items.product_name',
                DB::raw('SUM(ad_purchase_order_items.qty) as qty')
            )
            ->where('ad_purchase_orders.ad_user_id', $adUserId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->where(function ($query) {
                $query->whereNotNull('ad_purchase_orders.authorized_territory')
                    ->orWhereNotNull('adpo_areas.area_name');
            })
            ->groupBy(
                'ad_purchase_orders.authorized_territory',
                'adpo_areas.area_name',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.sku',
                'ad_purchase_order_items.product_name'
            )
            ->get()
            ->toBase()
            ->map(function ($item) use ($productsById, $productsByName) {
                $product = $productsById->get($item->product_id);

                if (!$product) {
                    $product = $productsByName->get($this->normalizeProductName($item->product_name));
                }

                return (object) [
                    'area' => $item->territory_area ?: $item->ad_area,
                    'product_id' => $product ? $product->id : $item->product_id,
                    'sku' => $product ? $product->sku : $item->sku,
                    'product_name' => $product ? $product->product_name : $item->product_name,
                    'item_name' => $product ? $product->product_name : $item->product_name,
                    'qty' => (float) $item->qty,
                ];
            });
    }

    private function addBalance($balances, $movement, $area, $qty, $orderedQty = 0)
    {
        if (!$area) {
            return;
        }

        $productName = $movement->product_name ?: $movement->item_name;
        $productKey = $this->normalizeProductName($productName);
        $key = $area . '|' . $productKey;
        $row = $balances->get($key, [
            'area' => $area,
            'product_id' => $movement->product_id,
            'sku' => $movement->sku,
            'product_name' => $productName,
            'item_name' => $productName,
            'qty' => 0,
            'ordered_qty' => 0,
        ]);

        $row['product_id'] = $row['product_id'] ?: $movement->product_id;
        $row['sku'] = $row['sku'] ?: $movement->sku;
        $row['product_name'] = $row['product_name'] ?: $productName;
        $row['item_name'] = $row['item_name'] ?: $productName;
        $row['qty'] += $qty;
        $row['ordered_qty'] += $orderedQty;
        $balances->put($key, $row);
    }
}
