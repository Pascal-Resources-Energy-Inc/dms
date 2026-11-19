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
}
