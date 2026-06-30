<?php

namespace App\Http\Controllers;

use App\AreaDistributor;
use App\AreaAd;
use App\Area;
use App\Center;
use Illuminate\Http\Request;

class ProvincialDistributorController extends Controller
{
    public function index(Request $request)
    {
        $centers = Center::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        $baseQuery = AreaDistributor::whereHas('userAds', function ($query) {
            $query->where('role', 'Provincial Distributor');
        });

        $totalAds = (clone $baseQuery)->count();
        $activeAds = (clone $baseQuery)->where('status', 'Active')->count();
        $inactiveAds = (clone $baseQuery)->where('status', 'Inactive')->count();
        $totalAwardedAreas = AreaAd::whereHas('distributor.userAds', function ($query) {
            $query->where('role', 'Provincial Distributor');
        })->count();

        $regions = (clone $baseQuery)
            ->whereNotNull('location_region')
            ->where('location_region', '<>', '')
            ->distinct()
            ->orderBy('location_region')
            ->pluck('location_region');

        $projectTypes = AreaAd::whereHas('distributor.userAds', function ($query) {
                $query->where('role', 'Provincial Distributor');
            })
            ->whereNotNull('project_type')
            ->where('project_type', '<>', '')
            ->distinct()
            ->orderBy('project_type')
            ->pluck('project_type');

        $ads = AreaDistributor::with(['areas' => function ($query) {
                $query->orderBy('project_type')->orderBy('area_name');
            }, 'trashedAreas' => function ($query) {
                $query->orderByDesc('deleted_at');
            }, 'userAds'])
            ->whereHas('userAds', function ($query) {
                $query->where('role', 'Provincial Distributor');
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('store_code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('business_name', 'like', '%' . $search . '%')
                        ->orWhere('contact_number', 'like', '%' . $search . '%')
                        ->orWhere('location_region', 'like', '%' . $search . '%')
                        ->orWhereHas('areas', function ($areaQuery) use ($search) {
                            $areaQuery->where('area_name', 'like', '%' . $search . '%')
                                ->orWhere('project_type', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('region'), function ($query) use ($request) {
                $query->where('location_region', $request->region);
            })
            ->when($request->filled('project_type'), function ($query) use ($request) {
                $query->whereHas('areas', function ($areaQuery) use ($request) {
                    $areaQuery->where('project_type', $request->project_type);
                });
            })
            ->when($request->filled('area'), function ($query) use ($request) {
                $area = trim($request->area);
                $query->whereHas('areas', function ($areaQuery) use ($area) {
                    $areaQuery->where('area_name', 'like', '%' . $area . '%');
                });
            })
            ->orderBy('name')
            ->get();

        return view('area_distributor.index', [
            'ads' => $ads,
            'activeAds' => $activeAds,
            'inactiveAds' => $inactiveAds,
            'totalAds' => $totalAds,
            'totalAwardedAreas' => $totalAwardedAreas,
            'regions' => $regions,
            'projectTypes' => $projectTypes,
            'centers' => $centers,
            'areas' => $areas,
            'indexRoute' => 'pds',
            'distributorTitle' => 'Provincial Distributors',
            'distributorSingular' => 'Provincial Distributor',
            'distributorCopy' => 'Monitor provincial distributor status, business information, and awarded territories.',
            'showCreateButton' => false,
        ]);
    }
}
