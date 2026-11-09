@php
    $isPullOut = $movement->out_type === 'Pull Out';
    $products = $isPullOut ? \App\InventoryTransfer::pullOutProducts($historyItems) : $historyItems->map(function ($line) {
        return ['name' => $line->item_name, 'sku' => $line->sku, 'qty' => $line->qty];
    })->values();
    $statuses = $historyItems->pluck('approval_status')->unique();
@endphp
<button
    type="button"
    class="btn btn-sm btn-outline-primary js-view-movement"
    data-toggle="modal"
    data-target="#movementDetailsModal"
    data-date="{{ $movement->transfer_date ? $movement->transfer_date->format('M d, Y') : '-' }}"
    data-type="{{ strtoupper($movement->movement_type) }}"
    data-reason="{{ $movement->out_type ?: '-' }}"
    data-product="{{ $movement->item_name }}"
    data-sku="{{ $movement->sku ?: '-' }}"
    data-products="{{ $products->toJson() }}"
    data-from-area="{{ $movement->from_area ?: '-' }}"
    data-to-area="{{ $movement->to_area ?: '-' }}"
    data-quantity="{{ number_format($products->sum('qty')) }}"
    data-unit-cost="{{ $movement->unit_cost !== null ? 'PHP ' . number_format($movement->unit_cost, 2) : 'Not set' }}"
    data-reference="{{ $movement->reference_no ?: '-' }}"
    data-remarks="{{ $movement->remarks ?: '-' }}"
    data-is-replacement-workflow="{{ in_array($movement->out_type, ['Pull Out', 'Replacement']) ? '1' : '0' }}"
    data-workflow-status="{{ $statuses->count() > 1 ? 'Partial' : ($statuses->first() ?: '-') }}"
    data-requested-quantity="{{ $isPullOut ? number_format($products->sum('qty')) : '-' }}"
    data-approved-quantity="{{ $isPullOut ? number_format($products->sum('approved')) : ($movement->approval_status !== 'Pending' ? number_format($movement->qty) : '-') }}"
    data-dr-number="{{ $historyItems->pluck('replacement_dr_number')->filter()->unique()->implode(', ') ?: '-' }}"
    data-warehouse-remarks="{{ $historyItems->pluck('warehouse_remarks')->filter()->unique()->implode(' · ') ?: '-' }}"
    data-attachments="{{ json_encode($movement->pull_out_attachments ?: []) }}">
    <i class="bi bi-eye"></i> View
</button>
