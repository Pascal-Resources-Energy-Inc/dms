<?php

namespace App\Support;

class ModulePermissions
{
    public static function modules()
    {
        $rows = [];
        foreach (config('user_access_modules', []) as $group) {
            foreach ($group['modules'] as $row) $rows[$row['key']] = $row;
        }
        return $rows;
    }

    public static function expand(array $permissions)
    {
        $expanded = $permissions;
        foreach (self::modules() as $key => $row) {
            list($module, $submodule) = explode('.', $key, 2);
            if (isset($permissions[$module]) && is_array($permissions[$module])
                && array_key_exists($submodule, $permissions[$module])) continue;
            foreach ($row['inherits'] as $parent) {
                list($parentModule, $parentSubmodule) = explode('.', $parent, 2);
                $actions = $permissions[$parentModule][$parentSubmodule] ?? null;
                if (is_array($actions)) {
                    $expanded[$module][$submodule] = array_values(array_intersect(array_keys($row['routes']), array_filter($actions, 'is_string')));
                    break;
                }
            }
        }
        return $expanded;
    }

    public static function sanitize(array $permissions)
    {
        $permissions = self::expand($permissions);
        $clean = [];
        foreach (self::modules() as $key => $row) {
            list($module, $submodule) = explode('.', $key, 2);
            if (!isset($permissions[$module][$submodule]) || !is_array($permissions[$module][$submodule])) continue;
            // Empty rows must survive so an unselected child does not inherit
            // the former shared permission when the form is reopened.
            $clean[$module][$submodule] = array_values(array_intersect(array_keys($row['routes']), array_filter($permissions[$module][$submodule], 'is_string')));
        }
        return $clean;
    }

    public static function routeAccess($name)
    {
        foreach (self::modules() as $key => $row) {
            foreach ($row['routes'] as $action => $routes) {
                if (in_array($name, $routes, true)) {
                    list($module, $submodule) = explode('.', $key, 2);
                    return [$module, $submodule, $action, $row['legacy']];
                }
            }
        }
        return null;
    }

    public static function allows($user, $key, $action = 'view')
    {
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        $permissions = json_decode($user->access_permissions ?? '{}', true);
        $permissions = is_array($permissions) ? $permissions : [];
        if ($user->role === 'Admin' && empty($permissions)) return true;
        if (!empty($permissions)) {
            list($module, $submodule) = explode('.', $key, 2);
            $permissions = self::expand($permissions);
            $actions = $permissions[$module][$submodule] ?? [];
            return is_array($actions) && in_array($action, $actions, true);
        }
        $definition = self::modules()[$key] ?? null;
        return $definition && $definition['legacy'] && ($user->{$definition['legacy']} ?? null) === 'on';
    }

    public static function personnelNavigation($user)
    {
        // Use the distributor portal's routes for its shared permission rows.
        $portalRoutes = [
            'dashboard.overview' => 'ad-dashboard',
            'transactions.customer_overview' => 'customer-ads',
            'products.catalog' => 'products',
            'charges.records' => 'charges',
            'inventory_transfers.transfers' => 'inventory-transfers.index',
            'dealers.records' => 'dealer-ads',
            'dealers.mega' => 'md-ads',
            'customers.records' => 'my-customers',
            'orders.sales_orders' => 'orders',
            'orders.guest' => 'guest-order',
            'purchase_orders.adpo' => 'ad-purchase-orders.index',
            'inventory.stock' => 'stock-inventory',
            'forms.borrowing' => 'bmc.index',
            'forms.layout' => 'layout.index',
        ];
        if ($user->role === \App\User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            $portalRoutes['distributors.records'] = 'ads';
            $portalRoutes['purchase_orders.provincial'] = 'provincial-distributor.purchase-orders';
        }
        $groups = [];
        foreach (config('user_access_modules', []) as $key => $group) {
            $links = [];
            foreach ($group['modules'] as $definition) {
                if (!self::allows($user, $definition['key'])) continue;
                if (isset($portalRoutes[$definition['key']])) {
                    $definition['route'] = $portalRoutes[$definition['key']];
                } elseif (in_array($key, ['reports', 'forms'], true)) {
                    $definition['route'] = $definition['routes']['view'][0] ?? null;
                } else {
                    continue;
                }
                $links[] = $definition;
            }
            if ($links) $groups[] = ['key' => $key, 'label' => $group['label'], 'links' => $links];
        }
        return collect($groups);
    }
}
