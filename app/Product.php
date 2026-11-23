<?php

namespace App;
    
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'item_id',
        'product_name',
        'srp_price',
        'price',
        'mega_dealer_price',
        'dealer_price',
        'client_price',
        'deposit',
        'product_image', 
        'bundle_product_ids',
        'description',
        'sku',
        'ad_user_id',
        'status',
        'dealer_points',
        'customer_points',
        'item_type',
        'is_new',
    ];

    protected $casts = [
        'is_new' => 'integer',
        'price' => 'decimal:2',
        'srp_price' => 'decimal:2',
        'mega_dealer_price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'client_price' => 'decimal:2',
        'bundle_product_ids' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function sellingPricesFor(?User $user, array $fallbacks = []): array
    {
        $prices = [
            'price' => $this->price,
            'client_price' => $this->client_price ?? $this->price,
            'dealer_price' => $this->dealer_price ?? $this->price,
            'mega_dealer_price' => $this->mega_dealer_price ?? $this->price,
        ];

        $prices = array_replace($prices, $fallbacks);

        return $this->item ? $this->item->productPricesFor($user, $prices) : $prices;
    }
}
