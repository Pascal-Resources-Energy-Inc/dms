<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Item extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    const PRICE_TIERS = [
        'price' => 'SRP',
        'dealer_price' => 'Dealer',
        'md_price' => 'Mega dealer',
        'dprice' => 'Distributor',
    ];

    const STOVE_KIT_COLORS = [
        'white' => 'White',
        'choco' => 'Choco',
        'blue' => 'Blue',
        'green' => 'Green',
        'yellow' => 'Yellow',
        'red' => 'Red',
        'assorted' => 'Assorted',
    ];

    protected $fillable = [
        'item',
        'item_description',
        'price',
        'dealer_price',
        'md_price',
        'dprice',
        'vismin_price',
        'vismin_dealer_price',
        'vismin_md_price',
        'vismin_dprice',
        'item_image',
        'dealer_points',
        'customer_points',
        'status',
        'for_ad',
        'item_type',
        'stove_kit_color_availability',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'md_price' => 'decimal:2',
        'dprice' => 'decimal:2',
        'vismin_price' => 'decimal:2',
        'vismin_dealer_price' => 'decimal:2',
        'vismin_md_price' => 'decimal:2',
        'vismin_dprice' => 'decimal:2',
        'dealer_points' => 'integer',
        'customer_points' => 'integer',
        'for_ad' => 'boolean',
        'stove_kit_color_availability' => 'array',
    ];

    public function distributorPurchaseOrderPrice(?User $buyer = null): float
    {
        if ($this->usesVisminPriceFor($buyer)) {
            return (float) ($this->for_ad ? $this->vismin_price : $this->vismin_dprice);
        }

        // Marketing collateral uses its catalog price; regular products use DPO pricing.
        return (float) ($this->for_ad ? $this->price : $this->dprice);
    }

    public function usesVisminPriceFor(?User $buyer): bool
    {
        // Zero is a configured price; only a missing price uses the existing DPO rate.
        $price = $this->for_ad ? $this->vismin_price : $this->vismin_dprice;

        return $price !== null && $buyer !== null
            && \App\Support\DistributorPricing::usesVisminPrice($buyer);
    }

    public function productPricesFor(?User $user, array $prices): array
    {
        if (!$user || !\App\Support\DistributorPricing::usesVisminPrice($user)) {
            return $prices;
        }

        foreach ([
            'price' => 'vismin_price',
            'client_price' => 'vismin_price',
            'dealer_price' => 'vismin_dealer_price',
            'mega_dealer_price' => 'vismin_md_price',
            'dprice' => 'vismin_dprice',
        ] as $productField => $itemField) {
            // Keep each existing tier independently when its regional price is unset.
            if (array_key_exists($productField, $prices) && $this->{$itemField} !== null) {
                $prices[$productField] = $this->{$itemField};
            }
        }

        return $prices;
    }

    public function productPriceMatrixFor(?User $user): array
    {
        // The matrix is read-only: both its display and save use catalog prices.
        $srp = $this->price ?? $this->dprice ?? 0;

        return $this->productPricesFor($user, [
            'price' => $srp,
            'client_price' => $srp,
            'dealer_price' => $this->dealer_price ?? $this->dprice,
            'mega_dealer_price' => $this->md_price,
            'dprice' => $this->dprice,
        ]);
    }

    public function isGazLiteStoveKit()
    {
        return strpos(strtolower(trim((string) $this->item)), 'gaz lite stove kit') !== false;
    }

    public function availableStoveKitColors()
    {
        $availability = $this->stove_kit_color_availability;

        if (!is_array($availability)) {
            return self::STOVE_KIT_COLORS;
        }

        return collect(self::STOVE_KIT_COLORS)
            ->filter(function ($label, $color) use ($availability) {
                return (bool) ($availability[$color] ?? false);
            })
            ->all();
    }
}
