<?php

namespace App\Http\Controllers;

use App\AdPurchaseOrder;
use App\Exports\AdPurchaseOrdersExport;
use App\Exports\VoucherImportTemplateExport;
use App\Exports\VouchersExport;
use App\Imports\VouchersImport;
use App\User;
use App\Voucher;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class VoucherController extends Controller
{
    public function myVouchers(Request $request)
    {
        $user = auth()->user();
        $storeCode = optional($user->ad)->store_code;
        $ownerKeys = array_values(array_filter([$storeCode, $user->name]));
        $filter = in_array($request->status, ['used', 'unused'], true) ? $request->status : 'all';

        $baseQuery = Voucher::whereIn('name', $ownerKeys);
        $usedCount = (clone $baseQuery)->where('used_count', '>', 0)->count();
        $unusedCount = (clone $baseQuery)->where('used_count', NULL)->count();

        $vouchers = $baseQuery
            ->when($filter === 'used', function ($query) {
                $query->where('used_count', '>', 0);
            })
            ->when($filter === 'unused', function ($query) {
                $query->where('used_count', NULL);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('vouchers.mine', [
            'vouchers' => $vouchers,
            'filter' => $filter,
            'usedCount' => $usedCount,
            'unusedCount' => $unusedCount,
            'totalCount' => $usedCount + $unusedCount,
            'storeCode' => $storeCode,
        ]);
    }

    public function index(Request $request)
    {
        $vouchers = $this->vouchersQuery($request)->get();

        $areaDistributors = User::with(['ad.areas'])
            ->whereIn('role', ['Area Distributor', 'Provincial Distributor'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
        
        
        return view('vouchers.index', compact('vouchers', 'areaDistributors'));
    }

    public function export(Request $request)
    {
        $vouchers = $this->vouchersQuery($request)->get();

        return Excel::download(
            new VouchersExport($vouchers),
            'vouchers-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function distributorAreas(Request $request)
    {
        $request->validate([
            'distributor_id' => 'required|integer',
        ]);

        $distributor = User::with('ad.areas')
            ->whereIn('role', ['Area Distributor', 'Provincial Distributor'])
            ->findOrFail($request->distributor_id);

        $areas = $distributor->ad
            ? $distributor->ad->areas
                ->pluck('area_name')
                ->map(function ($areaName) {
                    return trim($areaName);
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
            : collect();

        return response()->json([
            'areas' => $areas,
            'count' => $areas->count(),
        ]);
    }

    private function vouchersQuery(Request $request)
    {
        return Voucher::query()->when($request->filled('status'), function ($query) use ($request) {
                $this->applyVoucherStatusFilter($query, $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('area_names', 'like', '%' . $search . '%')
                        ->orWhereExists(function ($subQuery) use ($search) {
                            $subQuery->select(DB::raw(1))
                                ->from('area_distributors')
                                ->whereColumn('area_distributors.store_code', 'vouchers.name')
                                ->where(function ($distributorQuery) use ($search) {
                                    $distributorQuery->where('area_distributors.store_code', 'like', '%' . $search . '%')
                                        ->orWhere('area_distributors.name', 'like', '%' . $search . '%')
                                        ->orWhere('area_distributors.email_address', 'like', '%' . $search . '%')
                                        ->orWhere('area_distributors.business_name', 'like', '%' . $search . '%');
                                });
                        });
                });
            })
            ->orderBy('created_at', 'desc');
    }

    private function applyVoucherStatusFilter($query, $status)
    {
        $today = now()->toDateString();

        if ($status === 'active') {
            $query->where('is_active', 1)
                ->where(function ($inner) use ($today) {
                    $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
                })
                ->where(function ($inner) use ($today) {
                    $inner->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
                })
                ->where(function ($inner) {
                    $inner->whereNull('usage_limit')
                        ->orWhereColumn('used_count', '<', 'usage_limit');
                });
        } elseif ($status === 'inactive') {
            $query->where('is_active', 0);
        } elseif ($status === 'scheduled') {
            $query->where('is_active', 1)
                ->whereNotNull('starts_at')
                ->where('starts_at', '>', $today);
        } elseif ($status === 'expired') {
            $query->where('is_active', 1)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $today);
        } elseif ($status === 'used_up') {
            $query->where('is_active', 1)
                ->where(function ($inner) use ($today) {
                    $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
                })
                ->where(function ($inner) use ($today) {
                    $inner->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
                })
                ->whereNotNull('usage_limit')
                ->whereColumn('used_count', '>=', 'usage_limit');
        }
    }

    public function adOrders(Request $request, Voucher $voucher)
    {
        $orders = $this->voucherAdOrdersQuery($request, $voucher)->get();

        $summary = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'Pending')->count(),
            'for_delivery' => $orders->where('status', 'For Delivery')->count(),
            'for_verification' => $orders->where('status', 'For Verification')->count(),
            'completed' => $orders->where('status', 'Completed')->count(),
            'amount' => $orders->sum('total_amount'),
        ];

        return view('ad_purchase_orders.index', [
            'orders' => $orders,
            'summary' => $summary,
            'pageTitle' => 'Voucher ' . $voucher->code,
            'pageSubtitle' => 'AD purchase orders that used this voucher.',
            'panelTitle' => 'Voucher Usage',
            'showCreateButton' => false,
            'clearRoute' => route('vouchers.ad-orders', $voucher->id),
            'exportRoute' => route('vouchers.ad-orders.export', array_merge(['voucher' => $voucher->id], request()->query())),
            'viewRouteName' => 'ad-purchase-orders.show',
        ]);
    }

    public function exportAdOrders(Request $request, Voucher $voucher)
    {
        $orders = $this->voucherAdOrdersQuery($request, $voucher)->get();

        return Excel::download(
            new AdPurchaseOrdersExport($orders),
            'voucher-' . $voucher->code . '-ad-purchase-orders-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function voucherAdOrdersQuery(Request $request, Voucher $voucher)
    {
        $user = auth()->user();

        return AdPurchaseOrder::with(['items', 'ad', 'voucher'])
            ->where(function ($query) use ($voucher) {
                $query->where('voucher_id', $voucher->id)
                    ->orWhere('voucher_code', $voucher->code);
            })
            ->when($user->role !== 'Admin', function ($query) use ($user) {
                $query->where('ad_user_id', $user->id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
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

    public function store(Request $request)
    {
        Voucher::create($this->validatedData($request));

        return redirect()->route('vouchers')->with('success', 'Voucher created successfully.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'voucher_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new VouchersImport;

        DB::beginTransaction();

        try {
            Excel::import($import, $request->file('voucher_file'));

            if ($import->errors()->count() > 0) {
                DB::rollBack();

                return redirect()
                    ->route('vouchers')
                    ->withErrors(new MessageBag($import->errors()->all()));
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('vouchers')
                ->withErrors(new MessageBag(['Voucher upload failed. Please check the file format and try again.']));
        }

        return redirect()->route('vouchers')->with(
            'success',
            'Voucher upload complete. Created: ' . number_format($import->createdCount()) . '. Skipped existing: ' . number_format($import->skippedCount()) . '.'
        );
    }

    public function exportImportTemplate()
    {
        return Excel::download(
            new VoucherImportTemplateExport,
            'voucher-import-template.xlsx'
        );
    }

    public function update(Request $request, Voucher $voucher)
    {
        $voucher->update($this->validatedData($request, $voucher->id));

        return redirect()->route('vouchers')->with('success', 'Voucher updated successfully.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('vouchers')->with('success', 'Voucher deleted successfully.');
    }

    public function availableForTerritory(Request $request)
    {
        $request->validate([
            'area_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $ad = optional($user)->ad;
        $selectedArea = trim((string) $request->area_name);

        if (!$ad || !$ad->store_code) {
            return response()->json(['vouchers' => []]);
        }

        $isAuthorizedArea = $ad->areas->contains(function ($area) use ($selectedArea) {
            return strcasecmp(trim((string) $area->area_name), $selectedArea) === 0;
        });

        if (!$isAuthorizedArea) {
            return response()->json([
                'message' => 'Please select one of your authorized territories.',
                'vouchers' => [],
            ], 422);
        }

        $vouchers = Voucher::where('name', $ad->store_code)
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
            ->filter(function ($voucher) use ($selectedArea) {
                return $voucher->hasArea($selectedArea);
            })
            ->map(function ($voucher) {
                return [
                    'code' => $voucher->code,
                    'label' => $voucher->code . ' - ' . ($voucher->discount_type === 'percent'
                        ? number_format($voucher->discount_value, 2) . '%'
                        : 'PHP ' . number_format($voucher->discount_value, 2)),
                    'minimum_order_amount' => (float) $voucher->minimum_order_amount,
                ];
            })
            ->values();

        return response()->json(['vouchers' => $vouchers]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'subtotal' => 'nullable|numeric|min:0',
            'area_name' => 'nullable|string|max:255',
        ]);

        $subtotal = (float) $request->input('subtotal', 0);
        $user = auth()->user();
        $storeCode = optional($user->ad)->store_code;
        $authorizedAreas = optional($user->ad)->areas
            ? $user->ad->areas->pluck('area_name')->filter()->values()
            : collect();

        if ($authorizedAreas->isNotEmpty() && !$authorizedAreas->contains(function ($areaName) use ($request) {
            return strcasecmp(trim((string) $areaName), trim((string) $request->area_name)) === 0;
        })) {
            return response()->json([
                'valid' => false,
                'message' => 'Please select one of your authorized territories.',
            ], 422);
        }

        $voucher = Voucher::where('code', strtoupper(trim($request->code)))
            ->where('name', $storeCode)
            ->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher code was not found.',
            ], 404);
        }

        if (!$voucher->isUsable($subtotal)) {
            $message = (float) $subtotal < (float) $voucher->minimum_order_amount
                ? 'A minimum order of PHP ' . number_format($voucher->minimum_order_amount, 2) . ' is required for this voucher.'
                : 'Voucher is ' . strtolower($voucher->statusLabel($subtotal)) . '.';

            return response()->json([
                'valid' => false,
                'message' => $message,
            ], 422);
        }

        if (!$voucher->hasArea($request->area_name)) {
            return response()->json([
                'valid' => false,
                'message' => 'Voucher is not available for the selected area.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Voucher applied.',
            'code' => $voucher->code,
            'discount' => $voucher->discountFor($subtotal),
            'discount_type' => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'is_active' => 1,
        ]);
    }

    private function validatedData(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vouchers', 'code')->ignore($ignoreId),
            ],
            'name' => 'required|string|max:255',
            'area_names' => 'nullable|array',
            'area_names.*' => 'string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0.01',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['area_names'] = collect($data['area_names'] ?? [])
            ->map(function ($areaName) {
                return trim($areaName);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $distributor = User::with('ad.areas')
            ->whereHas('ad', function ($query) use ($data) {
                $query->where('store_code', $data['name']);
            })
            ->first();

        if (!$distributor || !$distributor->ad) {
            throw ValidationException::withMessages([
                'name' => 'Please select a valid distributor store code.',
            ]);
        }

        $allowedAreas = $distributor && $distributor->ad
            ? $distributor->ad->areas
                ->pluck('area_name')
                ->map(function ($areaName) {
                    return trim($areaName);
                })
                ->filter()
                ->unique()
                ->values()
                ->all()
            : [];

        if (!empty($allowedAreas) && empty($data['area_names'])) {
            throw ValidationException::withMessages([
                'area_names' => 'Please select at least one authorized area.',
            ]);
        }

        if (!empty($data['area_names'])) {
            foreach ($data['area_names'] as $areaName) {
                if (!in_array($areaName, $allowedAreas, true)) {
                    throw ValidationException::withMessages([
                        'area_names' => 'Please select valid areas for the chosen distributor.',
                    ]);
                }
            }
        }

        $data['minimum_order_amount'] = $data['minimum_order_amount'] ?? 0;
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($data['discount_type'] === 'percent' && (float) $data['discount_value'] > 100) {
            $data['discount_value'] = 100;
        }

        return $data;
    }
}
