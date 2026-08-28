<?php

namespace App\Http\Middleware;

use Closure;

class EnsureModuleAccess
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['Admin', 'MFI'], true)) {
            return $next($request);
        }

        $permissions = json_decode($user->access_permissions ?? '{}', true);
        $permissions = is_array($permissions) ? $permissions : [];

        // Admins are unrestricted until a detailed permission list is saved.
        if ($user->role === 'Admin' && empty($permissions)) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        $access = $this->routeAccess($routeName);

        if (! $access) {
            return $next($request);
        }

        [$module, $submodule, $action, $legacyField] = $access;
        if (! empty($permissions)) {
            $actions = $permissions[$module][$submodule] ?? [];

            if (is_array($actions) && in_array($action, $actions, true)) {
                return $next($request);
            }
        } elseif ($module === 'dashboard' || ($legacyField && ($user->{$legacyField} ?? null) === 'on')) {
            return $next($request);
        } elseif ($module === 'users' && $action === 'view' && (
            ($user->can_add ?? null) === 'on'
            || ($user->can_edit ?? null) === 'on'
            || ($user->can_delete ?? null) === 'on'
        )) {
            return $next($request);
        }

        abort(403, 'You do not have access to this module.');
    }

    private function routeAccess($routeName)
    {
        $view = 'view';

        $routes = [
            'home' => ['dashboard', 'overview', $view, null],
            'users' => ['users', 'accounts', $view, null],
            'users.data' => ['users', 'accounts', $view, null],
            'users.show' => ['users', 'accounts', $view, null],
            'users.update' => ['users', 'accounts', 'edit', null],
            'users.access.update' => ['users', 'accounts', 'edit', null],
            'items' => ['settings', 'items', $view, 'can_access_settings'],
            'items.store' => ['settings', 'items', 'add', 'can_access_settings'],
            'areas' => ['settings', 'campaigns', $view, 'can_access_settings'],
            'areas.store' => ['settings', 'campaigns', 'add', 'can_access_settings'],
            'transactions' => ['transactions', 'sales', $view, 'can_access_transactions'],
            'new-transaction' => ['transactions', 'sales', 'add', 'can_access_transactions'],
            'transactions.destroy' => ['transactions', 'sales', 'delete', 'can_access_transactions'],
            'orders' => ['orders', 'sales_orders', $view, null],
            'orders.store' => ['orders', 'sales_orders', 'add', null],
            'ads' => ['distributors', 'records', $view, 'can_access_distributors'],
            'pds' => ['distributors', 'records', $view, 'can_access_distributors'],
            'dealers' => ['dealers', 'records', $view, 'can_access_dealers'],
            'mds' => ['dealers', 'records', $view, 'can_access_dealers'],
            'customers' => ['customers', 'records', $view, 'can_access_customers'],
            'ad-purchase-orders.index' => ['purchase_orders', 'adpo', $view, 'can_access_purchase_orders'],
            'ad-purchase-orders.create' => ['purchase_orders', 'adpo', 'add', 'can_access_purchase_orders'],
            'ad-purchase-orders.store' => ['purchase_orders', 'adpo', 'add', 'can_access_purchase_orders'],
            'inventory-transfers.index' => ['inventory_transfers', 'transfers', $view, null],
            'inventory-transfers.store' => ['inventory_transfers', 'transfers', 'add', null],
            'warehouse-pull-outs.index' => ['inventory_transfers', 'transfers', $view, null],
            'return-refunds.index' => ['return_refunds', 'requests', $view, null],
            'charges' => ['charges', 'records', $view, null],
            'charges.store' => ['charges', 'records', 'add', null],
            'storelocation' => ['locations', 'directory', $view, null],
            'products' => ['products', 'catalog', $view, null],
            'products.create' => ['products', 'catalog', 'add', null],
            'products.store' => ['products', 'catalog', 'add', null],
            'admin.stock.requests' => ['stock_requests', 'approvals', $view, 'can_access_stock_requests'],
            'dealer.stock.requests.store' => ['stock_requests', 'approvals', 'add', 'can_access_stock_requests'],
            'admin.stock.requests.approve' => ['stock_requests', 'approvals', 'edit', 'can_access_stock_requests'],
            'admin.stock.requests.reject' => ['stock_requests', 'approvals', 'edit', 'can_access_stock_requests'],
            'rewards' => ['settings', 'rewards', $view, 'can_access_settings'],
            'rewards.store' => ['settings', 'rewards', 'add', 'can_access_settings'],
            'vouchers' => ['settings', 'campaigns', $view, 'can_access_settings'],
            'raffles' => ['settings', 'campaigns', $view, 'can_access_settings'],
            'dsr' => ['reports', 'sales', $view, 'can_access_reports'],
            'aging' => ['reports', 'operations', $view, 'can_access_reports'],
            'dpo' => ['reports', 'sales', $view, 'can_access_reports'],
            'isl' => ['reports', 'operations', $view, 'can_access_reports'],
            'monthly-sales' => ['reports', 'sales', $view, 'can_access_reports'],
            'voucher-history' => ['reports', 'operations', $view, 'can_access_reports'],
            'signup-incentives' => ['reports', 'sedp', $view, 'can_access_reports'],
            'repeat-purchase-incentives' => ['reports', 'sedp', $view, 'can_access_reports'],
        ];

        return $routes[$routeName] ?? null;
    }
}
