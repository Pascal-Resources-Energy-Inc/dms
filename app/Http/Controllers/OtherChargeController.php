<?php

namespace App\Http\Controllers;

use App\OtherCharge;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OtherChargeController extends Controller
{
    public function index(Request $request)
    {
        $charges = $this->chargesQuery($request)->get();
        $summaryQuery = $this->baseChargesQuery();
        $adUsers = $this->availableAdUsers();

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'active' => (clone $summaryQuery)->where('is_active', 1)->count(),
            'fixed' => (clone $summaryQuery)->where('charge_type', 'fixed')->count(),
            'percentage' => (clone $summaryQuery)->where('charge_type', 'percentage')->count(),
        ];

        return view('other_charges.index', compact('charges', 'summary', 'adUsers'));
    }

    public function store(Request $request)
    {
        OtherCharge::create($this->validatedData($request));

        return redirect()
            ->route('charges')
            ->with('success', 'Other charge successfully added.');
    }

    public function update(Request $request, OtherCharge $charge)
    {
        $charge->update($this->validatedData($request, $charge->id));

        return redirect()
            ->route('charges')
            ->with('success', 'Other charge successfully updated.');
    }

    public function destroy(OtherCharge $charge)
    {
        $charge->delete();

        return redirect()
            ->route('charges')
            ->with('success', 'Other charge successfully deleted.');
    }

    private function chargesQuery(Request $request)
    {
        return $this->baseChargesQuery()
            ->with('adUser.ad')
            ->when($request->filled('ad_user_id') && auth()->user()->role === 'Admin', function ($query) use ($request) {
                $query->where('ad_user_id', $request->ad_user_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('charge_type', $request->type);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhereHas('adUser', function ($adQuery) use ($search) {
                            $adQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhereHas('ad', function ($adProfileQuery) use ($search) {
                                    $adProfileQuery->where('business_name', 'like', '%' . $search . '%')
                                        ->orWhere('store_code', 'like', '%' . $search . '%');
                                });
                        });
                });
            })
            ->orderBy('ad_user_id')
            ->orderBy('is_active', 'desc')
            ->orderBy('name');
    }

    private function baseChargesQuery()
    {
        $query = OtherCharge::query();
        $user = auth()->user();

        if ($user && in_array($user->role, ['Area Distributor', 'Provincial Distributor'], true)) {
            $query->where('ad_user_id', $user->id);
        }

        return $query;
    }

    private function availableAdUsers()
    {
        $user = auth()->user();

        if ($user && in_array($user->role, ['Area Distributor', 'Provincial Distributor'], true)) {
            return User::with('ad')
                ->where('id', $user->id)
                ->get();
        }

        return User::with('ad')
            ->whereIn('role', ['Area Distributor', 'Provincial Distributor'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    private function validatedData(Request $request, $ignoreId = null)
    {
        $user = auth()->user();
        $isDistributor = $user && in_array($user->role, ['Area Distributor', 'Provincial Distributor'], true);

        $data = $request->validate([
            'ad_user_id' => [
                $isDistributor ? 'nullable' : 'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['Area Distributor', 'Provincial Distributor']);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('other_charges', 'code')->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'charge_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'applies_to' => ['required', Rule::in(['order', 'delivery', 'dealer', 'customer', 'ad_purchase_order'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($isDistributor) {
            $data['ad_user_id'] = $user->id;
        }

        $data['code'] = strtoupper(trim($data['code']));
        $data['name'] = trim($data['name']);
        $data['is_active'] = $request->has('is_active');

        return $data;
    }
}
