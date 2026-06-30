<?php

namespace App\Http\Controllers;

use App\User;
use App\Services\DashboardDataService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $dashboardData;

    public function __construct(DashboardDataService $dashboardData)
    {
        $this->dashboardData = $dashboardData;
    }

    public function index()
    {
        abort_unless(Auth::user()->canAccessDms(), 403, 'Your user role is not allowed to access the DMS.');

        return redirect()->route(Auth::user()->dashboardRoute());
    }

    public function superAdmin()
    {
        $users = User::latest()->get();
        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');
        $dashboard = $this->dashboardData->build();

        return view('dashboards.super-admin', compact('users', 'roleCounts', 'dashboard'));
    }

    public function partner()
    {
        $user = Auth::user();
        $dashboard = $this->dashboardData->build();

        return view('dashboards.partner', compact('user', 'dashboard'));
    }
}
