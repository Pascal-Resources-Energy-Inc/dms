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
        'ris_number',
        'return_date',
        'return_attachments',
        'warehouse_received_qty',
        'warehouse_reference_no',
        'warehouse_received_at',
        'ad_notified_at',
        'related_movement_id',
        'replacement_product_id',
        'replacement_sku',
        'replacement_item_name',
        'replacement_qty',
        'replacement_unit_cost',
        'replacement_dr_number',
        'approval_status',
        'warehouse',
        'warehouse_remarks',
        'reviewed_by',
        'reviewed_at',
        'transfer_date',
        'return_date',
        'warehouse_received_at',
        'ad_notified_at',
        'remarks',
        'created_by',
    ];

    protected $dates = [
        'transfer_date',
        'deleted_at',
    ];

    protected $casts = [
        'pull_out_attachments' => 'array',
        'return_attachments' => 'array',
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
