<?php

namespace App\Http\Controllers;

use App\InventoryTransfer;
use App\AdPurchaseOrderItem;
use App\AreaDistributor;
use App\Product;
use App\User;
use App\Mail\InventoryReturnRefundNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

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
                            ->whereIn('approval_status', ['Processing', 'For Review', 'Warehouse Confirmed']);
                    });
            })
            ->orderBy('transfer_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $stockProducts = Product::where('ad_user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'Activate')
                    ->orWhereNull('status');
            })
            ->orderBy('product_name')
            ->get();

        $dealerOrderDeductions = $this->completedDealerOrderDeductions(
            $ad ? $ad->id : null,
            $inventoryProducts
        );

        if ($user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            $dealerOrderDeductions = $dealerOrderDeductions->merge(
                $this->completedProvincialDistributorOrderDeductions(
                    $user->id,
                    $stockProducts->concat($adItems)->values()
                )
            );
        }

        $completedAdPurchaseOrderStock = $this->completedAdPurchaseOrderStock(
            $user->id,
            $inventoryProducts
        );
        $balances = $this->buildBalances($allMovements, $dealerOrderDeductions, $completedAdPurchaseOrderStock);

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

        if (!$request->filled('product_id')) {
            $movementQuery->where(function ($query) use ($user) {
                $query->whereNull('batch_number')->orWhereIn('id', function ($batchQuery) use ($user) {
                    $batchQuery->from('inventory_transfers as batch_rows')->selectRaw('MIN(id)')
                        ->where('ad_user_id', $user->id)->whereNotNull('batch_number')->groupBy('batch_number');
                });
            });
        }

        $movements = $movementQuery
            ->orderBy('transfer_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->appends($request->query());
        $batchNumbers = $movements->getCollection()->pluck('batch_number')->filter();
        $batchItems = $batchNumbers->isEmpty() ? collect() : InventoryTransfer::whereIn('batch_number', $batchNumbers)
            ->select('batch_number', 'sku', 'item_name', 'qty')->orderBy('id')->get()->groupBy('batch_number');

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
            'batchItems',
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

    public function movementHistoryData(Request $request)
    {
        $query = InventoryTransfer::where('ad_user_id', auth()->id());

        if ($request->filled('movement_type')) $query->where('movement_type', $request->movement_type);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);
        if ($request->filled('area')) {
            $query->where(function ($areaQuery) use ($request) {
                $areaQuery->where('from_area', $request->area)->orWhere('to_area', $request->area);
            });
        }
        if ($request->filled('date_from')) $query->whereDate('transfer_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('transfer_date', '<=', $request->date_to);

        if (!$request->filled('product_id')) {
            $query->where(function ($batchQuery) {
                $batchQuery->whereNull('batch_number')->orWhereIn('id', function ($ids) {
                    $ids->from('inventory_transfers as batch_rows')->selectRaw('MIN(id)')
                        ->where('ad_user_id', auth()->id())->whereNotNull('batch_number')->whereNull('deleted_at')
                        ->groupBy('batch_number');
                });
            });
        }

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('transfer_date', function ($movement) {
                return $movement->transfer_date ? Carbon::parse($movement->transfer_date)->format('M d, Y') : '—';
            })
            ->addColumn('movement_type_display', function ($movement) {
                $color = $movement->movement_type === 'in' ? 'success' : ($movement->movement_type === 'out' ? 'danger' : 'primary');
                $detail = $movement->movement_type === 'transfer'
                    ? ($movement->from_area ?: 'Source area') . ' to ' . ($movement->to_area ?: 'Receiving area')
                    : ($movement->out_type ?: 'Stock movement');
                return '<span class="badge bg-' . $color . ' movement-badge">' . e(strtoupper($movement->movement_type)) . '</span><span class="movement-type-detail">' . e($detail) . '</span>';
            })
            ->addColumn('products', function ($movement) {
                $items = $movement->batch_number
                    ? InventoryTransfer::where('batch_number', $movement->batch_number)->select('sku', 'item_name', 'qty')->orderBy('id')->get()
                    : collect([$movement]);
                return $items->map(function ($item) {
                    return '<div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom"><span class="small fw-semibold">' . e($item->item_name) . '<small class="text-muted ms-1">' . e($item->sku ?: 'No SKU') . '</small></span><span class="badge bg-light text-dark border">' . number_format($item->qty) . '</span></div>';
                })->implode('');
            })
            ->addColumn('action', function ($movement) {
                $items = $movement->batch_number
                    ? InventoryTransfer::where('batch_number', $movement->batch_number)
                        ->select('item_name', 'sku', 'qty')
                        ->orderBy('id')
                        ->get()
                    : collect([$movement]);
                $itemsJson = $items->map(function ($item) {
                    return ['name' => $item->item_name, 'sku' => $item->sku, 'qty' => $item->qty];
                })->values()->toJson();

                return '<button type="button" class="btn btn-sm btn-outline-primary js-view-movement" data-toggle="modal" data-target="#movementDetailsModal"'
                    . ' data-date="' . e($movement->transfer_date ? Carbon::parse($movement->transfer_date)->format('M d, Y') : '—') . '"'
                    . ' data-type="' . e(strtoupper($movement->movement_type)) . '"'
                    . ' data-reason="' . e($movement->out_type ?: '—') . '"'
                    . ' data-product="' . e($movement->item_name) . '"'
                    . ' data-sku="' . e($movement->sku ?: '—') . '"'
                    . ' data-products="' . e($itemsJson) . '"'
                    . ' data-from-area="' . e($movement->from_area ?: '—') . '"'
                    . ' data-to-area="' . e($movement->to_area ?: '—') . '"'
                    . ' data-quantity="' . number_format($movement->qty) . '"'
                    . ' data-unit-cost="' . e($movement->unit_cost !== null ? 'PHP ' . number_format($movement->unit_cost, 2) : 'Not set') . '"'
                    . ' data-reference="' . e($movement->reference_no ?: '—') . '"'
                    . ' data-remarks="' . e($movement->remarks ?: '—') . '"'
                    . ' data-is-replacement-workflow="' . (in_array($movement->out_type, ['Pull Out', 'Replacement'], true) ? '1' : '0') . '"'
                    . ' data-workflow-status="' . e($movement->approval_status ?: '—') . '"'
                    . ' data-requested-quantity="' . ($movement->replacement_qty !== null ? number_format($movement->replacement_qty) : '—') . '"'
                    . ' data-approved-quantity="' . ($movement->approval_status !== 'Pending' ? number_format($movement->qty) : '—') . '"'
                    . ' data-dr-number="' . e($movement->replacement_dr_number ?: '—') . '"'
                    . ' data-warehouse-remarks="' . e($movement->warehouse_remarks ?: '—') . '"'
                    . ' data-attachments="' . e(json_encode($movement->pull_out_attachments ?: [])) . '">'
                    . '<i class="bi bi-eye"></i> View</button>';
            })
            ->rawColumns(['movement_type_display', 'products', 'action'])
            ->make(true);
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
                // The RIS date is the official movement date for pull-outs.
                'transfer_date' => $request->input('ris_date'),
            ]);
        }

        $request->validate([
            'movement_type' => 'required|in:in,out,transfer',
            'product_id' => 'nullable|integer',
            'from_area' => 'nullable|string|max:255',
            'to_area' => 'nullable|string|max:255',
            'qty' => 'nullable|integer|min:1',
            'items' => 'nullable|array|max:20',
            'items.*.product_id' => 'required_with:items|integer',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'out_type' => 'nullable|string|max:255',
            'ris_number' => 'required_if:out_type,Pull Out|string|max:255',
            'ris_date' => 'required_if:out_type,Pull Out|date',
            'pull_out_attachments' => 'required_if:out_type,Pull Out|array|max:5',
            'pull_out_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'replacement_qty' => 'nullable|integer|min:1',
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
            'out' => ['Return and Refund', 'Pull Out', 'Replace', 'Inventory Adjustment'],
        ];

        if (in_array($type, ['in', 'out'], true) && !in_array($request->out_type, $allowedReasons[$type], true)) {
            return back()->withInput()->with('error', 'Please select a valid movement type.');
        }
        if ($request->filled('items')) {
            return $this->storeMultipleMovements($request, $user, $ad, $areas, $type);
        }
        if (!$request->filled('product_id') || !$request->filled('qty')) {
            return back()->withInput()->with('error', 'Please add a product and quantity.');
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
                'ris_number' => $replacementProduct ? strtoupper(trim($request->ris_number)) : null,
                'ris_date' => $replacementProduct ? $request->ris_date : null,
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

            $referenceNo = $this->nextMovementReferenceNo();
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

                $replacementReferenceNo = $this->nextMovementReferenceNo();
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

    private function nextMovementReferenceNo()
    {
        // One sequence is maintained per calendar month, producing references
        // such as MOV-202609-000001.
        $month = Carbon::today();
        $sequenceKey = $month->copy()->startOfMonth()->toDateString();
        $prefix = 'MOV-' . $month->format('Ym') . '-';
        $sequence = DB::table('inventory_movement_reference_sequences')
            ->where('reference_date', $sequenceKey)
            ->lockForUpdate()
            ->first();

        if (!$sequence) {
            $lastNumber = InventoryTransfer::where('reference_no', 'like', $prefix . '%')
                ->pluck('reference_no')
                ->map(function ($reference) {
                    preg_match('/-(\d+)$/', (string) $reference, $matches);
                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->max() ?: 0;

            DB::table('inventory_movement_reference_sequences')->insert([
                'reference_date' => $sequenceKey,
                'last_number' => $lastNumber,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $sequence = (object) ['last_number' => $lastNumber];
        }

        $nextNumber = (int) $sequence->last_number + 1;
        DB::table('inventory_movement_reference_sequences')
            ->where('reference_date', $sequenceKey)
            ->update(['last_number' => $nextNumber, 'updated_at' => Carbon::now()]);

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function storePullOutAttachments(Request $request, $userId)
    {
        if (!$request->hasFile('pull_out_attachments')) {
            return [];
        }

        $directory = public_path('uploads/inventory-movements/pull-outs');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return collect($request->file('pull_out_attachments'))->map(function ($file) use ($directory, $userId) {
            $filename = 'pull-out-' . $userId . '-' . str_replace('.', '', uniqid('', true)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            return [
                'path' => 'uploads/inventory-movements/pull-outs/' . $filename,
                'name' => $file->getClientOriginalName(),
            ];
        })->all();
    }

    private function storeMultipleMovements(Request $request, $user, $ad, array $areas, $type)
    {
        if (($type === 'in' && !$request->to_area) || ($type === 'out' && !$request->from_area) || ($type === 'transfer' && (!$request->from_area || !$request->to_area || $request->from_area === $request->to_area))) {
            return back()->withInput()->with('error', 'Please complete the required area fields.');
        }
        foreach ([$request->from_area, $request->to_area] as $area) {
            if ($area && !in_array($area, $areas, true)) return back()->withInput()->with('error', 'Selected area is not assigned to your account.');
        }
        $items = collect($request->items);
        $hasDuplicateProducts = $items->pluck('product_id')->count() !== $items->pluck('product_id')->unique()->count();
        if ($hasDuplicateProducts) return back()->withInput()->with('error', 'A product can be added only once.');
        $isPullOut = $type === 'out' && $request->out_type === 'Pull Out';
        $isReturnRefund = $type === 'out' && $request->out_type === 'Return and Refund';
        $attachments = $isPullOut ? $this->storePullOutAttachments($request, $user->id) : [];
        $warehouse = ($isPullOut || $isReturnRefund) ? $this->warehouseForAuthenticatedAd() : null;
        $batch = 'BATCH-' . Carbon::now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -5));
        $referenceNo = null;
        $movementIds = [];
        try {
            DB::transaction(function () use ($items, $request, $user, $ad, $type, $batch, &$referenceNo, &$movementIds, $isPullOut, $isReturnRefund, $attachments, $warehouse) {
                $referenceNo = $this->nextMovementReferenceNo();
                foreach ($items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    if ($type === 'in' && (int) $product->ad_user_id !== (int) $user->id) abort(422, 'Invalid product.');
                    if (in_array($type, ['out', 'transfer'], true) && (int) $item['qty'] > $this->availableQty($user->id, $ad ? $ad->id : null, $request->from_area, $product)) abort(422, 'Insufficient stock for ' . $product->product_name . '.');
                    $movement = InventoryTransfer::create([
                        'ad_id' => $ad ? $ad->id : null, 'ad_user_id' => $user->id, 'product_id' => $product->id,
                        'sku' => $product->sku, 'item_name' => $product->product_name, 'movement_type' => $type, 'out_type' => $request->out_type,
                        'from_area' => in_array($type, ['out', 'transfer'], true) ? $request->from_area : null,
                        'to_area' => in_array($type, ['in', 'transfer'], true) ? $request->to_area : null,
                        'qty' => $item['qty'], 'batch_number' => $batch, 'approval_status' => ($isPullOut || $isReturnRefund) ? 'Pending' : 'Approved',
                        'ris_number' => $isPullOut ? strtoupper(trim($request->ris_number)) : null,
                        'ris_date' => $isPullOut ? $request->ris_date : null,
                        'pull_out_attachments' => $attachments ?: null,
                        'replacement_product_id' => $isPullOut ? $product->id : null,
                        'replacement_sku' => $isPullOut ? $product->sku : null,
                        'replacement_item_name' => $isPullOut ? $product->product_name : null,
                        'replacement_qty' => $isPullOut ? $item['qty'] : null,
                        'warehouse' => $warehouse,
                        'transfer_date' => $request->transfer_date ?: Carbon::today(), 'remarks' => $request->remarks, 'created_by' => $user->id,
                    ]);
                    $movement->update(['reference_no' => $referenceNo]);
                    $movementIds[] = $movement->id;
                }
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
        if ($isReturnRefund) {
            $firstMovement = InventoryTransfer::find($movementIds[0] ?? null);
            if ($firstMovement) {
                $this->notifyDennisReturnRefund($firstMovement);
            }

            return redirect()->route('inventory-transfers.index')->with('success', $items->count() . ' Return and Refund items were submitted for approval. Reference No.: ' . $referenceNo);
        }

        return redirect()->route('inventory-transfers.index')->with('success', $items->count() . ' movement items saved successfully. Reference No.: ' . $referenceNo);
    }

    public function returnRefundRequests()
    {
        $this->ensureReturnRefundReviewer();

        $requests = InventoryTransfer::where('out_type', 'Return and Refund')
            ->when($this->returnRefundCreditProcessor(), function ($query, $processor) {
                $query->leftJoin('area_distributors as return_refund_ads', 'return_refund_ads.id', '=', 'inventory_transfers.ad_id')
                    ->select('inventory_transfers.*')
                    ->whereIn('inventory_transfers.approval_status', ['Processing', 'For Review']);

                if ($processor === 'princess') {
                    $this->applyRegionVFilter($query, 'return_refund_ads.location_region');
                    return;
                }

                $query->where(function ($regionQuery) {
                    $regionQuery->whereNull('return_refund_ads.location_region')
                        ->orWhereRaw("LOWER(TRIM(return_refund_ads.location_region)) NOT REGEXP ?", [$this->regionVPattern()]);
                });
            }, function ($query) {
                if ($this->isPresident()) {
                    return;
                }

                if ($this->isDennisApprover()) {
                    return;
                }

                if ($this->hasReturnRefundViewAccess()) {
                    return;
                }

                $query->where('warehouse', strtolower((string) auth()->user()->warehouse));
            })
            ->with(['product', 'creator'])
            ->latest('inventory_transfers.created_at')
            ->get();

        $creditMemoProcessor = $this->returnRefundCreditProcessor();

        return view('inventory_transfers.return_refunds', compact('requests', 'creditMemoProcessor'));
    }

    public function approveReturnRefund(Request $request, $id)
    {
        abort_unless($this->isDennisApprover(), 403);
        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'warehouse_remarks' => 'nullable|string|max:1000',
        ]);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')->findOrFail($id);
        abort_unless($movement->approval_status === 'Pending', 422);

        if ($request->decision === 'Rejected') {
            $movement->update([
                'approval_status' => 'Rejected',
                'warehouse_remarks' => $request->warehouse_remarks,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            return back()->with('success', 'Return and Refund request rejected.');
        }

        $movement->update([
            'approval_status' => 'Approved',
            'warehouse_remarks' => $request->warehouse_remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Return and Refund request approved. Please submit the RIS documents.');
    }

    public function uploadReturnDocuments(Request $request, $id)
    {
        abort_unless($this->isDennisApprover(), 403);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')->findOrFail($id);
        abort_unless($movement->approval_status === 'Approved', 422);

        $request->validate([
            'ris_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'return_attachments' => 'required|array|min:1|max:5',
            'return_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachments = $this->storeReturnAttachments($request->file('return_attachments'), $movement->id);
        $movement->update([
            'ris_number' => strtoupper(trim($request->ris_number)),
            'return_date' => $request->return_date,
            'return_attachments' => $attachments,
            'approval_status' => 'Documents Submitted',
        ]);

        return back()->with('success', 'RIS documents submitted to the warehouse.');
    }

    public function receiveReturnRefund(Request $request, $id)
    {
        $this->ensureWarehouseUser();
        $request->validate([
            'warehouse_received_qty' => 'required|integer|min:1',
            'sirr_number' => 'required|string|max:255',
            'warehouse_remarks' => 'nullable|string|max:1000',
        ]);
        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('warehouse', strtolower((string) auth()->user()->warehouse))
            ->findOrFail($id);
        abort_unless($movement->approval_status === 'Documents Submitted', 422);
        if ($request->warehouse_received_qty > $movement->qty) return back()->with('error', 'Received quantity cannot exceed the requested quantity.');
        if ((int) $request->warehouse_received_qty !== (int) $movement->qty && !$request->filled('warehouse_remarks')) {
            return back()->withInput()->with('error', 'Remarks are required when the received quantity differs from the requested quantity.');
        }

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
            // Reuse the existing warehouse reference column for SIRR numbers
            // so historical Return and Refund records remain compatible.
            'warehouse_reference_no' => strtoupper(trim($request->sirr_number)),
            'warehouse_received_at' => Carbon::now(),
            'warehouse_remarks' => $request->warehouse_remarks,
            'approval_status' => 'Processing',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);
        $this->notifyAdReturnReceived($movement->fresh());

        return back()->with('success', 'Return receiving is now processing. The Area/Provincial Distributor was emailed.');
    }

    public function submitReturnRefundCreditMemo(Request $request, $id)
    {
        $processor = $this->returnRefundCreditProcessor();
        abort_unless($processor, 403);

        $request->validate([
            'credit_memo_reference_no' => 'required|string|max:255',
            'credit_memo_attachments' => 'required|array|min:1|max:5',
            'credit_memo_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('approval_status', 'Processing')
            ->findOrFail($id);

        abort_unless($this->maySubmitReturnRefundCreditMemo($movement, $processor), 403);

        $attachments = $this->storeCreditMemoAttachments($request->file('credit_memo_attachments'), $movement->id);
        $movement->update([
            'credit_memo_reference_no' => strtoupper(trim($request->credit_memo_reference_no)),
            'credit_memo_attachments' => $attachments,
            'credit_memo_submitted_by' => auth()->id(),
            'credit_memo_submitted_at' => Carbon::now(),
            'approval_status' => 'For Review',
        ]);

        return back()->with('success', 'Credit memo submitted. The Return and Refund request is now For Review.');
    }

    public function revertReturnRefund(Request $request, $id)
    {
        abort_unless($this->mayRevertReturnRefund(), 403);
        $request->validate([
            'revert_to' => 'required|in:Warehouse,Accounting',
            'revert_remarks' => 'required|string|max:1000',
        ]);
        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('approval_status', 'For Review')
            ->findOrFail($id);

        $this->revertReturnRefundToDestination(
            $movement,
            $request->revert_to,
            $request->revert_remarks
        );

        return back()->with('success', 'Return and Refund request reverted to ' . $request->revert_to . '.');
    }

    public function markReturnRefundReviewed($id)
    {
        abort_unless($this->mayRevertReturnRefund(), 403);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('approval_status', 'For Review')
            ->findOrFail($id);

        try {
            $this->sendReturnRefundEvidenceToSpp(
                $movement,
                'Return & Refund Reviewed - ' . $movement->reference_no
            );
        } catch (\Exception $exception) {
            Log::error('SPP return/refund email failed.', [
                'movement_id' => $movement->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Unable to send the consolidated PDF to SPP. The request was not marked as Reviewed.');
        }

        $movement->update([
            'approval_status' => 'Reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Return and Refund request marked as Reviewed.');
    }

    public function approveReturnRefundByPresident($id)
    {
        abort_unless($this->isPresident(), 403);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('approval_status', 'Reviewed')
            ->findOrFail($id);

        $movement->update([
            'approval_status' => 'President Approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Return and Refund request approved by the President.');
    }

    public function revertReturnRefundByPresident(Request $request, $id)
    {
        abort_unless($this->isPresident(), 403);

        $request->validate([
            'revert_to' => 'required|in:Warehouse,Accounting',
            'revert_remarks' => 'required|string|max:1000',
        ]);

        $movement = InventoryTransfer::where('out_type', 'Return and Refund')
            ->where('approval_status', 'Reviewed')
            ->findOrFail($id);

        $this->revertReturnRefundToDestination(
            $movement,
            $request->revert_to,
            $request->revert_remarks
        );

        return back()->with('success', 'Return and Refund request reverted to ' . $request->revert_to . '.');
    }

    private function mayRevertReturnRefund(): bool
    {
        return (bool) array_intersect(['PQD/IAD', 'Accounting'], (array) auth()->user()->approval_types);
    }

    private function isPresident(): bool
    {
        return strtolower(trim((string) auth()->user()->designation)) === 'president';
    }

    private function revertReturnRefundToDestination(
        InventoryTransfer $movement,
        string $destination,
        string $revertRemarks
    ): void {
        $destinationStatus = [
            'Warehouse' => 'Documents Submitted',
            'Accounting' => 'Processing',
        ][$destination];

        $revertNote = 'Reverted to ' . $destination . ': ' . trim($revertRemarks);
        $remarks = collect([$movement->warehouse_remarks, $revertNote])
            ->filter()
            ->implode(PHP_EOL);

        $movement->update([
            'approval_status' => $destinationStatus,
            'warehouse_remarks' => $remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);
    }

    private function consolidateReturnRefundAttachments(InventoryTransfer $movement): string
    {
        $attachments = array_merge(
            (array) $movement->return_attachments,
            (array) $movement->credit_memo_attachments
        );

        $files = collect($attachments)
            ->map(function ($attachment) {
                $path = is_array($attachment) ? ($attachment['path'] ?? '') : $attachment;

                return public_path($path);
            })
            ->filter(function ($path) {
                return is_file($path);
            })
            ->values();

        abort_if($files->isEmpty(), 422, 'No attachments are available to consolidate.');

        $pdf = new Fpdi();

        foreach ($files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
                $pages = $pdf->setSourceFile($file);

                for ($page = 1; $page <= $pages; $page++) {
                    $template = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }

                continue;
            }

            $imageSize = getimagesize($file);
            if (!$imageSize) {
                continue;
            }

            $orientation = $imageSize[0] > $imageSize[1] ? 'L' : 'P';
            $pdf->AddPage($orientation, 'A4');

            $availableWidth = $orientation === 'L' ? 277 : 190;
            $width = min($availableWidth, 190);
            $height = min(277, $width * ($imageSize[1] / $imageSize[0]));

            $pdf->Image($file, 10, 10, $width, $height);
        }

        $directory = public_path('uploads/inventory-movements/return-refunds/consolidated');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . 'return-refund-' . $movement->id . '-' . time() . '.pdf';
        $pdf->Output('F', $path);

        return $path;
    }

    private function sendReturnRefundEvidenceToSpp(InventoryTransfer $movement, string $subject): void
    {
        $pdf = $this->consolidateReturnRefundAttachments($movement);

        Mail::send('emails.return_refund_spp', compact('movement'), function ($mail) use ($pdf, $movement, $subject) {
            $mail->to('ian.bautista@pascalresources.com.ph')
                ->subject($subject)
                ->attach($pdf, [
                    'as' => 'return-refund-' . $movement->reference_no . '.pdf',
                    'mime' => 'application/pdf',
                ]);
        });
    }

    private function isDennisApprover(): bool
    {
        return strtolower(trim((string) auth()->user()->name)) === 'dennis villareal';
    }

    private function ensureReturnRefundReviewer(): void
    {
        if ($this->isDennisApprover() || $this->isPresident() || $this->returnRefundCreditProcessor() || $this->hasReturnRefundViewAccess()) return;
        $this->ensureWarehouseUser();
    }

    private function ensureReturnRefundReportViewer(): void
    {
        if (auth()->user()->hasAreaDistributorAccess()) {
            return;
        }

        $this->ensureReturnRefundReviewer();
    }

    private function hasReturnRefundViewAccess(): bool
    {
        $permissions = json_decode(auth()->user()->access_permissions ?: '{}', true);
        $actions = is_array($permissions) ? ($permissions['return_refunds']['requests'] ?? []) : [];

        return is_array($actions) && in_array('view', $actions, true);
    }

    private function returnRefundCreditProcessor(): ?string
    {
        $name = strtolower(trim((string) auth()->user()->name));

        if (preg_match('/\bprincess\b/', $name)) return 'princess';
        if (preg_match('/\bdenver\b/', $name)) return 'denver';

        return null;
    }

    private function maySubmitReturnRefundCreditMemo(InventoryTransfer $movement, string $processor): bool
    {
        $region = strtolower(trim((string) AreaDistributor::where('id', $movement->ad_id)->value('location_region')));
        $isRegionV = $this->isRegionVLocation($region);

        return $processor === 'princess' ? $isRegionV : !$isRegionV;
    }

    private function applyRegionVFilter($query, string $column): void
    {
        $query->whereRaw("LOWER(TRIM({$column})) REGEXP ?", [$this->regionVPattern()]);
    }

    private function isRegionVLocation(string $region): bool
    {
        return (bool) preg_match('/(^|[^a-z0-9])region[[:space:]\\-]*(v|5)([^a-z0-9]|$)/i', $region);
    }

    private function regionVPattern(): string
    {
        // Matches Region V / Region 5, but not Region VI.
        return '(^|[^[:alnum:]])region[[:space:]-]*(v|5)([^[:alnum:]]|$)';
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
        $requests = $this->warehousePullOutRequestGroups($warehouse);
        $summary = [
            'pending' => $requests->filter(fn ($request) => $request->groupedItems->contains('approval_status', 'Pending'))->count(),
            'processing' => $requests->filter(fn ($request) => $request->groupedItems->contains('approval_status', 'For Processing'))->count(),
            'replacing' => $requests->filter(fn ($request) => $request->groupedItems->contains('approval_status', 'Replacing'))->count(),
            'ad_confirmed' => $requests->filter(fn ($request) => $request->groupedItems->contains('approval_status', 'AD Confirmed'))->count(),
            'total' => $requests->count(),
        ];

        return view('inventory_transfers.warehouse_pull_outs', compact('requests', 'summary', 'warehouse'));
    }

    public function warehousePullOutsData()
    {
        $this->ensureWarehouseUser();
        $warehouse = strtolower((string) auth()->user()->warehouse);
        $requests = $this->warehousePullOutRequestGroups($warehouse);
        $itemIds = $requests->flatMap(function ($request) {
            return $request->groupedItems->pluck('id');
        })->unique()->values();
        $auditsByMovement = Schema::hasTable('audits') && $itemIds->isNotEmpty()
            ? DB::table('audits')
                ->select('auditable_id', 'new_values', 'created_at')
                ->where('auditable_type', InventoryTransfer::class)
                ->whereIn('auditable_id', $itemIds)
                ->orderBy('created_at')
                ->get()
                ->groupBy('auditable_id')
            : collect();

        $rows = $requests->map(function ($request) use ($auditsByMovement) {
            $items = $request->groupedItems->map(function ($item) use ($auditsByMovement) {
                $statusStartedAt = $item->approval_status === 'Pending'
                    ? $item->created_at
                    : ($item->reviewed_at ?: $item->created_at);

                return [
                    'id' => $item->id,
                    'name' => $item->item_name ?: 'Product',
                    'sku' => $item->sku ?: 'No SKU',
                    'requested_qty' => (int) ($item->replacement_qty ?: $item->qty),
                    'approved_qty' => (int) $item->qty,
                    'ad_confirmed_qty' => $item->approval_status === 'AD Confirmed' ? (int) $item->qty : null,
                    'unit_cost' => $item->unit_cost,
                    'replacement_unit_cost' => $item->replacement_unit_cost,
                    'status' => $item->approval_status ?: 'Pending',
                    'dr_number' => $item->replacement_dr_number,
                    'remarks' => $item->warehouse_remarks,
                    'status_started_at' => $statusStartedAt ? Carbon::parse($statusStartedAt)->toDateString() : null,
                    'aging' => $this->pullOutWorkflowAging($item, $auditsByMovement->get($item->id, collect())),
                    'review_url' => route('warehouse-pull-outs.review', $item->id),
                ];
            })->values()->all();

            return [
                'date' => $request->transfer_date ? Carbon::parse($request->transfer_date)->format('M d, Y') : '—',
                'sort_date' => $request->created_at ? Carbon::parse($request->created_at)->timestamp : 0,
                'reference' => $request->reference_no ?: 'Draft request',
                'ad_name' => $request->ad_name ?: 'Distributor',
                'source_areas' => $request->groupedItems->pluck('from_area')->filter()->unique()->values()->all(),
                'ris_number' => $request->ris_number ?: '—',
                'ris_date' => $request->ris_date ? Carbon::parse($request->ris_date)->format('M d, Y') : '—',
                'warehouse' => ucfirst($request->warehouse ?: auth()->user()->warehouse),
                'attachments' => $request->pull_out_attachments ?: [],
                'remarks' => $request->warehouse_remarks ?: ($request->remarks ?: '—'),
                'items' => $items,
                'requested_qty_total' => (int) $request->requestedQtyTotal,
                'search_text' => collect([$request->reference_no, $request->ad_name, $request->ris_number, $request->warehouse])
                    ->merge($request->groupedItems->pluck('item_name'))
                    ->merge($request->groupedItems->pluck('sku'))
                    ->filter()
                    ->implode(' '),
            ];
        })->values();

        return \Yajra\DataTables\Facades\DataTables::of($rows)->make(true);
    }

    private function pullOutWorkflowAging(InventoryTransfer $movement, $audits): array
    {
        $stages = ['Pending', 'For Processing', 'Replacing'];
        $movementCreatedAt = $movement->created_at ? Carbon::parse($movement->created_at) : Carbon::now();
        $stageStarts = ['Pending' => $movementCreatedAt];
        $currentStatus = $movement->approval_status ?: 'Pending';
        $currentRank = array_search($currentStatus, array_merge($stages, ['AD Confirmed']), true);
        $currentRank = $currentRank === false ? 0 : $currentRank;

        foreach ($audits as $audit) {
            // Some historic audit records can share an ID after restored or
            // reseeded data. Only use events belonging to this request run.
            if (!$audit->created_at || Carbon::parse($audit->created_at)->lt($movementCreatedAt)) {
                continue;
            }

            $newValues = json_decode($audit->new_values ?: '{}', true) ?: [];
            $status = $newValues['approval_status'] ?? null;
            if (in_array($status, $stages, true) && !isset($stageStarts[$status])) {
                $stageStarts[$status] = Carbon::parse($audit->created_at);
            }
        }

        $reviewedAt = $movement->reviewed_at ? Carbon::parse($movement->reviewed_at) : null;
        if ($currentRank >= 1 && !isset($stageStarts['For Processing'])) {
            $stageStarts['For Processing'] = $reviewedAt ?: $stageStarts['Pending'];
        }
        if ($currentRank >= 2 && !isset($stageStarts['Replacing'])) {
            $stageStarts['Replacing'] = $reviewedAt ?: $stageStarts['For Processing'];
        }

        $aging = ['Pending' => null, 'For Processing' => null, 'Replacing' => null];
        foreach ($stages as $index => $stage) {
            if (!isset($stageStarts[$stage])) {
                continue;
            }

            $nextStage = $stages[$index + 1] ?? null;
            $endedAt = $nextStage && isset($stageStarts[$nextStage])
                ? $stageStarts[$nextStage]
                : ($currentRank > $index ? Carbon::parse($movement->updated_at ?: Carbon::now()) : Carbon::now());
            $aging[$stage] = $stageStarts[$stage]->copy()->startOfDay()->diffInDays($endedAt->copy()->startOfDay());
        }

        return $aging;
    }

    private function warehousePullOutRequestGroups(string $warehouse)
    {
        $requestRows = InventoryTransfer::where('inventory_transfers.out_type', 'Pull Out')
            ->where('inventory_transfers.warehouse', $warehouse)
            ->leftJoin('users as area_distributors', 'area_distributors.id', '=', 'inventory_transfers.ad_user_id')
            ->select('inventory_transfers.*', 'area_distributors.name as ad_name')
            ->with('product')
            ->orderByRaw("CASE approval_status WHEN 'Pending' THEN 0 WHEN 'For Processing' THEN 1 WHEN 'Replacing' THEN 2 ELSE 3 END")
            ->latest()
            ->get();

        $requests = $requestRows
            ->groupBy(function ($request) {
                $risNumber = trim((string) $request->ris_number);

                return $risNumber !== ''
                    ? implode('|', [$risNumber, $request->ad_user_id, $request->warehouse])
                    : 'individual|' . $request->id;
            })
            ->map(function ($items) {
                $request = $items->first();
                $statuses = $items->pluck('approval_status')->filter()->unique()->values();

                $request->groupedItems = $items->values();
                $request->requestedQtyTotal = $items->sum(function ($item) {
                    return $item->replacement_qty ?: $item->qty;
                });
                $request->approvedQtyTotal = $items->sum('qty');
                $request->workflowStatus = $statuses->count() === 1 ? $statuses->first() : 'Mixed';

                return $request;
            })
            ->values();

        return $requests;
    }

    public function pullOutReport()
    {
        $query = $this->pullOutReportQuery();
        $summary = [
            'total' => $this->pullOutTransactionCount(clone $query),
            'pending' => $this->pullOutTransactionCount((clone $query)->where('approval_status', 'Pending')),
            'processing' => $this->pullOutTransactionCount((clone $query)->whereIn('approval_status', ['For Processing', 'Processing'])),
            'replacing' => $this->pullOutTransactionCount((clone $query)->where('approval_status', 'Replacing')),
            'completed' => $this->pullOutTransactionCount((clone $query)->whereIn('approval_status', ['Approved', 'Warehouse Confirmed', 'AD Confirmed'])),
            'rejected' => $this->pullOutTransactionCount((clone $query)->where('approval_status', 'Rejected')),
        ];
        $warehouses = (clone $query)->whereNotNull('warehouse')->distinct()->orderBy('warehouse')->pluck('warehouse');

        return view('reports.pull_out_requests', compact('summary', 'warehouses'));
    }

    public function returnRefundReport()
    {
        $this->ensureReturnRefundReportViewer();

        $query = $this->returnRefundReportQuery();
        $summary = [
            'total' => $this->returnRefundTransactionCount(clone $query),
            'pending' => $this->returnRefundTransactionCount((clone $query)->where('approval_status', 'Pending')),
            'receiving' => $this->returnRefundTransactionCount((clone $query)->where('approval_status', 'Documents Submitted')),
            'processing' => $this->returnRefundTransactionCount((clone $query)->where('approval_status', 'Processing')),
            'review' => $this->returnRefundTransactionCount((clone $query)->whereIn('approval_status', ['For Review', 'Reviewed'])),
        ];
        $warehouses = (clone $query)->whereNotNull('warehouse')->distinct()->orderBy('warehouse')->pluck('warehouse');

        return view('reports.return_refund_requests', compact('summary', 'warehouses'));
    }

    public function returnRefundReportData(Request $request)
    {
        $this->ensureReturnRefundReportViewer();

        $movements = $this->returnRefundReportQuery();

        if ($request->filled('status')) {
            $movements->where('inventory_transfers.approval_status', $request->status);
        }

        if ($request->filled('warehouse')) {
            $movements->where('inventory_transfers.warehouse', $request->warehouse);
        }

        if ($request->filled('date_from')) {
            $movements->whereDate('inventory_transfers.transfer_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $movements->whereDate('inventory_transfers.transfer_date', '<=', $request->date_to);
        }

        $groupedMovements = $movements
            ->select([])
            ->selectRaw('MIN(inventory_transfers.id) as id')
            ->selectRaw('MAX(inventory_transfers.transfer_date) as transfer_date')
            ->selectRaw('MAX(inventory_transfers.reference_no) as reference_no')
            ->selectRaw('MAX(inventory_transfers.ad_user_id) as ad_user_id')
            ->selectRaw('MAX(inventory_transfers.warehouse) as warehouse')
            ->selectRaw('MAX(inventory_transfers.approval_status) as approval_status')
            ->selectRaw('MAX(inventory_transfers.ris_number) as ris_number')
            ->selectRaw('MAX(inventory_transfers.return_date) as return_date')
            ->selectRaw('MAX(inventory_transfers.return_attachments) as return_attachments')
            ->selectRaw('MAX(inventory_transfers.warehouse_reference_no) as warehouse_reference_no')
            ->selectRaw('SUM(COALESCE(inventory_transfers.warehouse_received_qty, 0)) as warehouse_received_qty')
            ->selectRaw('MAX(inventory_transfers.credit_memo_reference_no) as credit_memo_reference_no')
            ->selectRaw('MAX(inventory_transfers.credit_memo_attachments) as credit_memo_attachments')
            ->selectRaw('MAX(inventory_transfers.warehouse_remarks) as warehouse_remarks')
            ->selectRaw('MAX(inventory_transfers.remarks) as remarks')
            ->selectRaw("GROUP_CONCAT(CONCAT_WS('||', inventory_transfers.item_name, inventory_transfers.sku, inventory_transfers.qty, inventory_transfers.from_area) ORDER BY inventory_transfers.id SEPARATOR '~~') as item_rows")
            ->groupBy('inventory_transfers.reference_no', 'inventory_transfers.ad_user_id', 'inventory_transfers.warehouse');

        $query = DB::query()
            ->fromSub($groupedMovements, 'return_refund_rows')
            ->leftJoin('users as distributors', 'distributors.id', '=', 'return_refund_rows.ad_user_id')
            ->select('return_refund_rows.*', 'distributors.name as distributor_name');

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('transfer_date', function ($row) {
                return $row->transfer_date ? Carbon::parse($row->transfer_date)->format('M d, Y') : '—';
            })
            ->addColumn('distributor', function ($row) {
                return e($row->distributor_name ?: 'Distributor');
            })
            ->addColumn('products', function ($row) {
                return $this->returnRefundReportProducts($row->item_rows);
            })
            ->addColumn('ris_details', function ($row) {
                $details = $row->ris_number
                    ? '<strong>RIS # ' . e($row->ris_number) . '</strong><small>' . e($row->return_date ? Carbon::parse($row->return_date)->format('M d, Y') : 'No return date') . '</small>'
                    : '<span class="text-muted">—</span>';

                return $details . $this->returnRefundReportFiles($row->return_attachments);
            })
            ->addColumn('receiving_details', function ($row) {
                if (!$row->warehouse_reference_no) {
                    return '<span class="text-muted">—</span>';
                }

                return '<strong>SIRR # ' . e($row->warehouse_reference_no) . '</strong><small>Received: ' . number_format((int) $row->warehouse_received_qty) . ' pcs</small>';
            })
            ->addColumn('credit_memo_details', function ($row) {
                $details = $row->credit_memo_reference_no
                    ? '<strong>Ref. # ' . e($row->credit_memo_reference_no) . '</strong>'
                    : '<span class="text-muted">—</span>';

                return $details . $this->returnRefundReportFiles($row->credit_memo_attachments);
            })
            ->addColumn('status_badge', function ($row) {
                $status = $row->approval_status ?: 'Pending';
                $class = [
                    'Pending' => 'warning text-dark',
                    'Approved' => 'primary',
                    'Documents Submitted' => 'info text-dark',
                    'Processing' => 'primary',
                    'For Review' => 'secondary',
                    'Reviewed' => 'info text-dark',
                    'President Approved' => 'success',
                    'Rejected' => 'danger',
                ][$status] ?? 'secondary';

                return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
            })
            ->addColumn('remarks_display', function ($row) {
                return e($row->warehouse_remarks ?: $row->remarks ?: '—');
            })
            ->filterColumn('distributor', function ($builder, $keyword) {
                $builder->where('distributors.name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['products', 'ris_details', 'receiving_details', 'credit_memo_details', 'status_badge'])
            ->make(true);
    }

    public function pullOutReportData(Request $request)
    {
        $movements = $this->pullOutReportQuery();

        if ($request->filled('warehouse')) $movements->where('inventory_transfers.warehouse', $request->warehouse);

        $groupedMovements = $movements
            ->selectRaw('MIN(inventory_transfers.id) as id')
            ->selectRaw('MAX(inventory_transfers.transfer_date) as transfer_date')
            ->selectRaw('MAX(inventory_transfers.reference_no) as reference_no')
            ->selectRaw('MAX(inventory_transfers.ad_user_id) as ad_user_id')
            ->selectRaw('MAX(inventory_transfers.from_area) as from_area')
            ->selectRaw('MAX(inventory_transfers.warehouse) as warehouse')
            ->selectRaw('MAX(inventory_transfers.approval_status) as approval_status')
            ->selectRaw('MAX(inventory_transfers.ris_number) as ris_number')
            ->selectRaw('MAX(inventory_transfers.ris_date) as ris_date')
            ->selectRaw('MAX(inventory_transfers.reviewed_at) as reviewed_at')
            ->selectRaw('MAX(inventory_transfers.created_at) as created_at')
            ->selectRaw('MAX(inventory_transfers.pull_out_attachments) as pull_out_attachments')
            ->selectRaw('MAX(inventory_transfers.warehouse_remarks) as warehouse_remarks')
            ->selectRaw('MAX(inventory_transfers.remarks) as remarks')
            ->selectRaw("GROUP_CONCAT(CONCAT_WS('||', inventory_transfers.item_name, inventory_transfers.sku, inventory_transfers.qty, inventory_transfers.replacement_item_name, inventory_transfers.replacement_sku, inventory_transfers.replacement_qty) ORDER BY inventory_transfers.id SEPARATOR '~~') as item_rows")
            ->selectRaw('SUM(COALESCE(inventory_transfers.replacement_qty, inventory_transfers.qty)) as requested_qty_total')
            ->selectRaw('SUM(inventory_transfers.qty) as approved_qty_total')
            ->groupBy(
                'inventory_transfers.reference_no',
                'inventory_transfers.ad_user_id',
                'inventory_transfers.warehouse',
                'inventory_transfers.pull_out_attachments'
            );

        $query = DB::query()
            ->fromSub($groupedMovements, 'pull_out_rows')
            ->leftJoin('users as distributors', 'distributors.id', '=', 'pull_out_rows.ad_user_id')
            ->select('pull_out_rows.*', 'distributors.name as distributor_name');

        if ($request->filled('status')) $query->where('pull_out_rows.approval_status', $request->status);
        if ($request->filled('date_from')) $query->whereDate('pull_out_rows.transfer_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('pull_out_rows.transfer_date', '<=', $request->date_to);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('transfer_date', function ($row) { return $row->transfer_date ? Carbon::parse($row->transfer_date)->format('M d, Y') : '—'; })
            ->addColumn('distributor', function ($row) { return e($row->distributor_name ?: 'Distributor'); })
            ->addColumn('pull_out_product', function ($row) { return $this->pullOutReportItems($row->item_rows, 0, 1, 2); })
            ->addColumn('replacement_product', function ($row) { return $this->pullOutReportItems($row->item_rows, 3, 4, 5); })
            ->addColumn('request_qty', function ($row) { return number_format($row->requested_qty_total) . ' pcs'; })
            ->addColumn('approved_qty', function ($row) {
                return in_array($row->approval_status, ['For Processing', 'Replacing', 'Warehouse Confirmed'], true)
                    ? number_format($row->approved_qty_total) . ' pcs'
                    : '—';
            })
            ->addColumn('ris_number_display', function ($row) { return e($row->ris_number ?: '—'); })
            ->addColumn('ris_date_display', function ($row) { return $row->ris_date ? Carbon::parse($row->ris_date)->format('M d, Y') : '—'; })
            ->addColumn('status_badge', function ($row) {
                $status = $row->approval_status ?: 'Approved';
                $class = [
                    'Approved' => 'success', 'Rejected' => 'danger', 'For Processing' => 'info text-dark',
                    'Replacing' => 'primary', 'Warehouse Confirmed' => 'success', 'AD Confirmed' => 'success', 'Documents Submitted' => 'secondary',
                ][$status] ?? 'warning text-dark';
                return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
            })
            ->addColumn('pending_aging', function ($row) { return $this->pullOutStatusAging($row, 'Pending'); })
            ->addColumn('processing_aging', function ($row) { return $this->pullOutStatusAging($row, 'For Processing'); })
            ->addColumn('replacing_aging', function ($row) { return $this->pullOutStatusAging($row, 'Replacing'); })
            ->addColumn('attachments', function ($row) {
                $files = is_array($row->pull_out_attachments) ? $row->pull_out_attachments : (json_decode($row->pull_out_attachments ?: '[]', true) ?: []);
                if (!count($files)) return '<span class="text-muted">—</span>';

                return collect($files)->map(function ($file) {
                    $path = is_array($file) ? ($file['path'] ?? '') : $file;
                    $name = is_array($file) ? ($file['name'] ?? basename($path)) : basename($path);
                    return $path ? '<a class="d-block text-primary text-nowrap" href="' . e(asset($path)) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> ' . e($name) . '</a>' : '';
                })->filter()->implode('');
            })
            ->addColumn('remarks', function ($row) { return e($row->warehouse_remarks ?: $row->remarks ?: '—'); })
            ->addColumn('ad_confirmation', function ($row) {
                if (!auth()->user()->hasAreaDistributorAccess() || $row->approval_status !== 'Replacing') {
                    return '—';
                }

                return '<form method="POST" class="js-confirm-replacement-form" action="' . e(route('pull-out-report.confirm-replacement', $row->id)) . '">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-sm btn-success text-nowrap" data-qty="' . number_format($row->approved_qty_total) . '">'
                    . '<i class="bi bi-check2-circle me-1"></i>Confirm ' . number_format($row->approved_qty_total) . ' pcs</button></form>';
            })
            ->filterColumn('distributor', function ($builder, $keyword) { $builder->where('distributors.name', 'like', "%{$keyword}%"); })
            ->rawColumns(['pull_out_product', 'replacement_product', 'status_badge', 'pending_aging', 'processing_aging', 'replacing_aging', 'attachments', 'ad_confirmation'])
            ->make(true);
    }

    private function storeCreditMemoAttachments(array $files, int $movementId): array
    {
        $directory = public_path('uploads/inventory-movements/credit-memos');
        if (!is_dir($directory)) mkdir($directory, 0755, true);

        return collect($files)->map(function ($file) use ($directory, $movementId) {
            $filename = 'credit-memo-' . $movementId . '-' . str_replace('.', '', uniqid('', true)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            return [
                'path' => 'uploads/inventory-movements/credit-memos/' . $filename,
                'name' => $file->getClientOriginalName(),
            ];
        })->all();
    }

    private function pullOutStatusAging($row, string $status): string
    {
        if ($row->approval_status !== $status) {
            return '—';
        }

        $startedAt = $status === 'Pending' ? $row->created_at : ($row->reviewed_at ?: $row->created_at);
        $days = $startedAt ? Carbon::parse($startedAt)->startOfDay()->diffInDays(Carbon::today()) : 0;

        return '<span class="aging-day">' . $days . ' Day(s)</span>';
    }

    private function pullOutReportItems($items, int $nameIndex, int $skuIndex, int $qtyIndex): string
    {
        return collect(explode('~~', (string) $items))->filter()->map(function ($item) use ($nameIndex, $skuIndex, $qtyIndex) {
            $parts = explode('||', $item);
            $name = $parts[$nameIndex] ?? 'Product';
            $sku = $parts[$skuIndex] ?? 'No SKU';
            $qty = $parts[$qtyIndex] ?? 0;

            return '<div class="mb-1"><strong>' . e($name) . '</strong><small class="d-block text-muted">' . e($sku ?: 'No SKU') . ' · ' . number_format($qty) . ' pcs</small></div>';
        })->implode('');
    }

    private function pullOutAgingBreakdown($query, bool $useReviewedAt): array
    {
        $dateColumn = $useReviewedAt ? 'COALESCE(reviewed_at, created_at)' : 'created_at';
        $ageExpression = "DATEDIFF(CURDATE(), DATE({$dateColumn}))";
        $transactionKey = "COALESCE(NULLIF(batch_number, ''), NULLIF(reference_no, ''), id)";

        return $query->selectRaw("{$ageExpression} as age_days, COUNT(DISTINCT {$transactionKey}) as request_count")
            ->groupBy(DB::raw($ageExpression))
            ->orderBy('age_days')
            ->get()
            ->mapWithKeys(function ($row) {
                return [(int) $row->age_days => (int) $row->request_count];
            })
            ->all();
    }

    private function pullOutTransactionCount($query): int
    {
        $transactionKey = "COALESCE(NULLIF(batch_number, ''), NULLIF(reference_no, ''), id)";

        return (int) $query->selectRaw("COUNT(DISTINCT {$transactionKey}) as transaction_count")
            ->value('transaction_count');
    }

    private function pullOutReportQuery()
    {
        // Qualify the columns because the report joins users for distributor names.
        // Without this, warehouse-admin reports fail with an ambiguous `warehouse`
        // column during DataTables' count query.
        $query = InventoryTransfer::query()
            ->whereRaw("LOWER(TRIM(COALESCE(inventory_transfers.out_type, ''))) = ?", ['pull out']);
        $user = auth()->user();
        $warehouse = strtolower((string) ($user->warehouse ?? ''));

        if ($user->hasAreaDistributorAccess()) {
            // A distributor may only see the pull-out requests that they submitted.
            $query->where('inventory_transfers.ad_user_id', $user->id);
        } elseif ($user->role === 'Admin' && in_array($warehouse, ['lubao', 'guinobatan'], true)) {
            // Warehouse admins only see requests routed to their warehouse.
            $query->where('inventory_transfers.warehouse', $warehouse);
        }

        return $query;
    }

    private function returnRefundReportQuery()
    {
        $query = InventoryTransfer::query()
            ->whereRaw("LOWER(TRIM(COALESCE(inventory_transfers.out_type, ''))) = ?", ['return and refund']);
        $user = auth()->user();

        if ($user->hasAreaDistributorAccess()) {
            return $query->where('inventory_transfers.ad_user_id', $user->id);
        }

        if ($processor = $this->returnRefundCreditProcessor()) {
            $query->leftJoin('area_distributors as return_refund_ads', 'return_refund_ads.id', '=', 'inventory_transfers.ad_id')
                ->select('inventory_transfers.*')
                ->whereIn('inventory_transfers.approval_status', ['Processing', 'For Review']);

            if ($processor === 'princess') {
                $this->applyRegionVFilter($query, 'return_refund_ads.location_region');

                return $query;
            }

            return $query->where(function ($regionQuery) {
                $regionQuery->whereNull('return_refund_ads.location_region')
                    ->orWhereRaw("LOWER(TRIM(return_refund_ads.location_region)) NOT REGEXP ?", [$this->regionVPattern()]);
            });
        }

        if ($this->isPresident() || $this->isDennisApprover() || $this->hasReturnRefundViewAccess()) {
            return $query;
        }

        return $query->where('inventory_transfers.warehouse', strtolower((string) $user->warehouse));
    }

    private function returnRefundTransactionCount($query): int
    {
        return (int) $query
            ->select([])
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(reference_no, ''), id)) as transaction_count")
            ->value('transaction_count');
    }

    private function returnRefundReportProducts($items): string
    {
        return collect(explode('~~', (string) $items))
            ->filter()
            ->map(function ($item) {
                [$name, $sku, $qty, $area] = array_pad(explode('||', $item), 4, null);

                return '<div class="rr-report-product">'
                    . '<strong>' . e($name ?: 'Product') . '</strong>'
                    . '<small>' . e($sku ?: 'No SKU') . ' · ' . number_format((int) $qty) . ' pcs</small>'
                    . '<small><i class="bi bi-geo-alt"></i> ' . e($area ?: 'No source area') . '</small>'
                    . '</div>';
            })
            ->implode('');
    }

    private function returnRefundReportFiles($attachments): string
    {
        $files = is_array($attachments) ? $attachments : (json_decode($attachments ?: '[]', true) ?: []);

        if (!$files) {
            return '';
        }

        $links = collect($files)->map(function ($file) {
            $path = is_array($file) ? ($file['path'] ?? '') : $file;
            $name = is_array($file) ? ($file['name'] ?? basename($path)) : basename($path);

            return $path
                ? '<a href="' . e(asset($path)) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> ' . e($name) . '</a>'
                : '';
        })->filter()->implode('');

        return $links ? '<div class="rr-report-files">' . $links . '</div>' : '';
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

            $approvedQuantityChanged = (int) $request->replacement_qty !== (int) $movement->replacement_qty;
            if ($approvedQuantityChanged && blank($request->warehouse_remarks)) {
                return back()
                    ->withInput()
                    ->with('error', 'Warehouse remarks are required when the approved quantity differs from the requested quantity.');
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
        });

        return back()->with('success', 'Replacement marked with DR # ' . strtoupper(trim($request->replacement_dr_number)) . '. Waiting for distributor confirmation before stock is posted.');
    }

    public function confirmPullOutReplacement($id)
    {
        $movement = InventoryTransfer::where('out_type', 'Pull Out')
            ->where('ad_user_id', auth()->id())
            ->findOrFail($id);
        $rawAttachments = $movement->getAttributes()['pull_out_attachments'] ?? null;

        $requests = InventoryTransfer::where('out_type', 'Pull Out')
            ->where('ad_user_id', $movement->ad_user_id)
            ->where('reference_no', $movement->reference_no)
            ->where('warehouse', $movement->warehouse)
            ->when(
                $rawAttachments,
                function ($query, $attachments) {
                    $query->where('pull_out_attachments', $attachments);
                },
                function ($query) {
                    $query->whereNull('pull_out_attachments');
                }
            )
            ->get();

        if ($requests->isEmpty() || $requests->contains(function ($request) {
            return $request->approval_status !== 'Replacing';
        })) {
            return back()->with('error', 'Only replacements awaiting your confirmation can be confirmed.');
        }

        DB::transaction(function () use ($requests) {
            foreach ($requests as $movement) {
                if ($movement->related_movement_id) {
                    $movement->update([
                        'approval_status' => 'AD Confirmed',
                        'ad_notified_at' => Carbon::now(),
                    ]);
                    continue;
                }

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
                    'approval_status' => 'AD Confirmed',
                    'replacement_dr_number' => $movement->replacement_dr_number,
                    'transfer_date' => Carbon::today(),
                    'remarks' => 'AD confirmed warehouse replacement for ' . $movement->reference_no . '.',
                    'created_by' => auth()->id(),
                ]);
                $replacement->update(['reference_no' => $this->nextMovementReferenceNo()]);
                $movement->update(['related_movement_id' => $replacement->id]);

                $movement->update([
                    'approval_status' => 'AD Confirmed',
                    'ad_notified_at' => Carbon::now(),
                ]);
            }
        });

        return back()->with('success', 'Replacement quantities confirmed. Stock was added to Current Stock by Area.');
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
                            ->whereIn('approval_status', ['Processing', 'For Review', 'Warehouse Confirmed']);
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

    private function completedProvincialDistributorOrderDeductions($provincialDistributorId, $products)
    {
        $productsById = $products->filter(function ($product) {
            return !empty($product->id);
        })->keyBy('id');
        $productsByName = $products->filter(function ($product) {
            return !empty($product->product_name);
        })->keyBy(function ($product) {
            return $this->normalizeProductName($product->product_name);
        });

        return AdPurchaseOrderItem::join('ad_purchase_orders', 'ad_purchase_order_items.ad_purchase_order_id', '=', 'ad_purchase_orders.id')
            ->leftJoin(DB::raw('(select ad_id, min(area_name) as area_name from ad_areas where deleted_at is null group by ad_id) as buyer_areas'), 'ad_purchase_orders.ad_id', '=', 'buyer_areas.ad_id')
            ->where('ad_purchase_orders.order_source', 'provincial_distributor')
            ->where('ad_purchase_orders.provincial_distributor_id', $provincialDistributorId)
            ->where('ad_purchase_orders.status', 'Completed')
            ->select(
                'ad_purchase_orders.authorized_territory as territory_area',
                'buyer_areas.area_name as buyer_area',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.sku',
                'ad_purchase_order_items.product_name',
                DB::raw('SUM(ad_purchase_order_items.qty) as qty')
            )
            ->groupBy(
                'ad_purchase_orders.authorized_territory',
                'buyer_areas.area_name',
                'ad_purchase_order_items.product_id',
                'ad_purchase_order_items.sku',
                'ad_purchase_order_items.product_name'
            )
            ->get()
            ->toBase()
            ->map(function ($item) use ($productsById, $productsByName) {
                $product = $productsById->get($item->product_id)
                    ?: $productsByName->get($this->normalizeProductName($item->product_name));

                return (object) [
                    'area' => $item->territory_area ?: $item->buyer_area,
                    'product_id' => $product ? $product->id : $item->product_id,
                    'sku' => $product ? $product->sku : $item->sku,
                    'product_name' => $product ? $product->product_name : $item->product_name,
                    'item_name' => $product ? $product->product_name : $item->product_name,
                    'qty' => (float) $item->qty,
                ];
            })
            ->filter(function ($item) {
                return $item->area && $item->product_name;
            })
            ->values();
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
