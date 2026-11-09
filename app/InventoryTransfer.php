<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
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
        'batch_number',
        'pull_out_attachments',
        'ris_number',
        'ris_date',
        'return_date',
        'return_attachments',
        'warehouse_received_qty',
        'warehouse_reference_no',
        'warehouse_received_at',
        'ad_notified_at',
        'credit_memo_reference_no',
        'credit_memo_attachments',
        'credit_memo_submitted_by',
        'credit_memo_submitted_at',
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
        'ris_date',
        'return_date',
        'warehouse_received_at',
        'ad_notified_at',
        'credit_memo_submitted_at',
        'deleted_at',
    ];

    protected $casts = [
        'pull_out_attachments' => 'array',
        'return_attachments' => 'array',
        'credit_memo_attachments' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returnRefundTransactionKey()
    {
        return json_encode([
            $this->ad_user_id, $this->warehouse,
            $this->batch_number ? 'batch' : ($this->reference_no ? 'reference' : 'row'),
            $this->batch_number ?: ($this->reference_no ?: $this->id),
        ]);
    }

    public function scopeSameReturnRefund($query, self $movement)
    {
        $query->where('out_type', 'Return and Refund')
            ->where('ad_user_id', $movement->ad_user_id)->where('warehouse', $movement->warehouse);
        if ($movement->batch_number) {
            return $query->where('batch_number', $movement->batch_number);
        }
        $query->whereRaw("NULLIF(batch_number, '') IS NULL");
        return $movement->reference_no
            ? $query->where('reference_no', $movement->reference_no)
            : $query->where('id', $movement->id);
    }

    public function scopeGroupReturnRefundTransactions($query)
    {
        return $query->groupBy('inventory_transfers.ad_user_id', 'inventory_transfers.warehouse')
            ->groupBy(DB::raw("NULLIF(inventory_transfers.batch_number, '')"))
            ->groupBy(DB::raw("CASE WHEN NULLIF(inventory_transfers.batch_number, '') IS NULL THEN NULLIF(inventory_transfers.reference_no, '') END"))
            ->groupBy(DB::raw("CASE WHEN NULLIF(inventory_transfers.batch_number, '') IS NULL AND NULLIF(inventory_transfers.reference_no, '') IS NULL THEN inventory_transfers.id END"));
    }

    public static function countReturnRefundTransactions($query)
    {
        $groups = (clone $query)->select([])->selectRaw('MIN(inventory_transfers.id) as id')
            ->groupReturnRefundTransactions();
        return DB::query()->fromSub($groups, 'return_refund_transactions')->count();
    }

    // Older requests had no batch number. Their reference and original proof
    // identify the request within the same distributor and warehouse.
    public function scopeSamePullOut($query, $row)
    {
        $query->where('out_type', 'Pull Out')->where('ad_user_id', $row->ad_user_id)
            ->where('warehouse', $row->warehouse);
        if ($row->batch_number) {
            return $query->where('batch_number', $row->batch_number);
        }
        if (!$row->reference_no) {
            return $query->where('id', $row->id);
        }
        $proof = $row instanceof self ? $row->getAttributes()['pull_out_attachments'] ?? null : $row->pull_out_attachments;
        return $query->whereRaw("NULLIF(batch_number, '') IS NULL")->where('reference_no', $row->reference_no)
            ->where('pull_out_attachments', $proof);
    }

    public function scopeGroupPullOutTransactions($query)
    {
        return $query->groupBy('inventory_transfers.ad_user_id', 'inventory_transfers.warehouse', 'inventory_transfers.batch_number')
            ->groupBy(DB::raw("CASE WHEN NULLIF(inventory_transfers.batch_number, '') IS NULL THEN inventory_transfers.reference_no END"))
            ->groupBy(DB::raw("CASE WHEN NULLIF(inventory_transfers.batch_number, '') IS NULL THEN inventory_transfers.pull_out_attachments END"))
            ->groupBy(DB::raw("CASE WHEN NULLIF(inventory_transfers.batch_number, '') IS NULL AND NULLIF(inventory_transfers.reference_no, '') IS NULL THEN inventory_transfers.id END"));
    }

    public static function groupPullOutProducts($lines)
    {
        return $lines->groupBy(function ($line) {
            return $line->product_id ?: $line->sku . '|' . $line->item_name;
        });
    }

    public static function pullOutProducts($lines)
    {
        return static::groupPullOutProducts($lines)->map(function ($parts) {
            $product = $parts->first();
            return [
                'name' => $product->item_name, 'sku' => $product->sku,
                'replacement_name' => $product->replacement_item_name ?: $product->item_name,
                'replacement_sku' => $product->replacement_sku ?: $product->sku,
                'qty' => (int) $parts->sum(function ($part) { return $part->replacement_qty ?? $part->qty; }),
                'pending' => (int) $parts->where('approval_status', 'Pending')->sum('qty'),
                'open' => (int) $parts->whereIn('approval_status', ['Pending', 'For Processing'])->sum('qty'),
                'approved' => (int) $parts->whereIn('approval_status', ['Approved', 'For Processing', 'Replacing', 'Warehouse Confirmed', 'AD Confirmed'])->sum('qty'),
                'confirmed' => (int) $parts->where('approval_status', 'AD Confirmed')->sum('qty'),
            ];
        })->values();
    }
}
