<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Item extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    const STOVE_KIT_COLORS = [
        'white' => 'White',
        'choco' => 'Choco',
        'blue' => 'Blue',
        'green' => 'Green',
        'yellow' => 'Yellow',
        'red' => 'Red',
    ];

    protected $fillable = [
        'item',
        'item_description',
        'price',
        'dealer_price',
        'md_price',
        'dprice',
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
        'dealer_points' => 'integer',
        'customer_points' => 'integer',
        'for_ad' => 'boolean',
        'stove_kit_color_availability' => 'array',
    ];

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
