<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryTransfer extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'ad_id',
        'ad_user_id',
        'product_id',
        'sku',
        'item_name',
        'movement_type',
        'out_type',
        'from_area',
        'to_area',
        'qty',
        'unit_cost',
        'reference_no',
        'pull_out_attachments',
        'transfer_date',
        'remarks',
        'created_by',
    ];

    protected $dates = [
        'transfer_date',
        'deleted_at',
    ];

    protected $casts = [
        'pull_out_attachments' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
