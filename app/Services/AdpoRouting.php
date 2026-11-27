<?php

namespace App\Services;

use App\AdPurchaseOrder;
use App\User;
use Illuminate\Support\Facades\DB;

class AdpoRouting
{
    const WAREHOUSE = 'prei_warehouse';
    const PLANT = 'prei_plant';
    const PROVINCIAL = 'provincial_distributor';

    public function sourceFor(User $user)
    {
        $profile = $this->profileFor($user);
        if (filled(data_get($profile, 'location_region'))) {
            return $this->isRegionVText($profile->location_region) ? self::WAREHOUSE : self::PLANT;
        }

        return $this->isRegionVText(data_get($profile, 'address'), data_get($profile, 'delivery_address'),
            $user->region, $user->province, $user->delivery_address) ? self::WAREHOUSE : self::PLANT;
    }

    public function profileFor(User $user)
    {
        return $user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? $user->provincialDistributor : $user->ad;
    }

    public function warehouseFor(AdPurchaseOrder $order)
    {
        if ($order->order_source === self::PLANT) return 'lubao';
        if ($order->order_source === self::PROVINCIAL) return null;

        $profile = $order->distributorUser ? $this->profileFor($order->distributorUser) : null;
        $region = data_get($profile, 'location_region') ?: data_get($order->ad, 'location_region');
        if (filled($region)) return $this->isRegionVText($region) ? 'guinobatan' : 'lubao';

        return $order->shipping_type === 'pickup_guinobatan' || $this->isRegionVText(
            $order->delivery_address, data_get($profile, 'delivery_address'), data_get($profile, 'address'),
            data_get($order->ad, 'delivery_address'), data_get($order->ad, 'address')
        ) ? 'guinobatan' : 'lubao';
    }

    public function sourceLabel(AdPurchaseOrder $order)
    {
        if ($order->order_source === self::PROVINCIAL) return 'Provincial Distributor';
        return $this->warehouseFor($order) === 'lubao' ? 'PREI Plant' : 'PREI Warehouse';
    }

    public function isRegionVText(...$values)
    {
        $text = strtolower(implode(' ', array_filter($values)));
        // The boundary after V prevents Region VI, VII and VIII matching Region V.
        if (preg_match('/\bregion[\s-]*(?:v|5)(?![a-z0-9])|^\s*(?:v|5)\s*$/i', $text)) return true;
        foreach ($this->bicolNames() as $name) {
            if (strpos($text, $name) !== false) return true;
        }
        return false;
    }

    public function forWarehouse($query, $warehouse)
    {
        $warehouse = strtolower(trim((string) $warehouse));
        if (!in_array($warehouse, ['lubao', 'guinobatan'], true)) return $query->whereRaw('1 = 0');

        return $query->where(function ($sources) use ($warehouse) {
            if ($warehouse === 'lubao') $sources->where('order_source', self::PLANT);
            $sources->orWhere(function ($legacy) use ($warehouse) {
                $legacy->where(function ($source) {
                    $source->where('order_source', self::WAREHOUSE)->orWhereNull('order_source')->orWhere('order_source', '');
                });
                // Classify existing warehouse orders without rewriting their history.
                $method = $warehouse === 'guinobatan' ? 'whereIn' : 'whereNotIn';
                $legacy->{$method}('ad_purchase_orders.id', $this->regionVOrderIds());
            });
        });
    }

    private function regionVOrderIds()
    {
        $query = DB::table('ad_purchase_orders as routing_orders')
            ->leftJoin('users as routing_users', 'routing_users.id', '=', 'routing_orders.ad_user_id')
            ->leftJoin('provincial_distributors as routing_pd', function ($join) {
                $join->on('routing_pd.user_id', '=', 'routing_users.id')
                    ->where('routing_users.role', User::ROLE_PROVINCIAL_DISTRIBUTOR)->whereNull('routing_pd.deleted_at');
            })
            ->leftJoin('area_distributors as routing_ad', function ($join) {
                $join->on('routing_ad.user_id', '=', 'routing_users.id')
                    ->where('routing_users.role', User::ROLE_AREA_DISTRIBUTOR)->whereNull('routing_ad.deleted_at');
            })
            ->leftJoin('area_distributors as routing_legacy', function ($join) {
                $join->on('routing_legacy.id', '=', 'routing_orders.ad_id')->whereNull('routing_legacy.deleted_at');
            })
            ->select('routing_orders.id');
        $region = "LOWER(COALESCE(NULLIF(TRIM(routing_pd.location_region), ''), NULLIF(TRIM(routing_ad.location_region), ''), NULLIF(TRIM(routing_legacy.location_region), ''), ''))";

        return $query->where(function ($regions) use ($region) {
            $regions->where(function ($known) use ($region) {
                $this->matchRegionV($known, $region);
            })->orWhere(function ($fallback) use ($region) {
                $fallback->whereRaw("{$region} = ''")->where(function ($addresses) {
                    $addresses->where('routing_orders.shipping_type', 'pickup_guinobatan');
                    foreach (['routing_orders.delivery_address', 'routing_pd.delivery_address', 'routing_pd.address',
                        'routing_ad.delivery_address', 'routing_ad.address', 'routing_legacy.delivery_address', 'routing_legacy.address'] as $column) {
                        $expression = "LOWER(COALESCE({$column}, ''))";
                        $addresses->orWhere(function ($address) use ($expression) {
                            $this->matchRegionV($address, $expression, true);
                        });
                    }
                });
            });
        });
    }

    private function matchRegionV($query, $expression, $address = false)
    {
        foreach (['v', '5', 'region v', 'region-v', 'region 5', 'region-5'] as $name) {
            $query->orWhereRaw("{$expression} = ?", [$name]);
            if (strlen($name) > 1) {
                foreach ([' %', '(%', '-%', ',%'] as $suffix) {
                    $query->orWhereRaw("{$expression} LIKE ?", [($address ? '%' : '') . $name . $suffix]);
                }
                if ($address) $query->orWhereRaw("{$expression} LIKE ?", ['%' . $name]);
            }
        }
        foreach ($this->bicolNames() as $name) $query->orWhereRaw("{$expression} LIKE ?", ['%' . $name . '%']);
    }

    private function bicolNames()
    {
        return ['bicol', 'albay', 'camarines norte', 'camarines sur', 'catanduanes', 'masbate', 'sorsogon'];
    }
}
