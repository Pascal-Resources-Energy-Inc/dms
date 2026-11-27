<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdPurchaseOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Source',
            'Business',
            'Distributor',
            'Authorized Territory',
            'Delivery Address',
            'Shipping Type',
            'Payment Method',
            'Bank Name',
            'Voucher Code',
            'Subtotal',
            'Delivery Fee',
            'Rebate Amount',
            'Pick Up Lubao Discount',
            'Total Amount',
            'Total Quantity',
            'Line Count',
            'Status',
            'Submitted At',
            'Remarks',
        ];
    }

    public function map($order): array
    {
        $submittedAt = $order->submitted_at ?: $order->created_at;

        return [
            $order->po_number,
            $order->source_label,
            $order->business_name ?: 'Area Distributor',
            optional($order->distributorUser)->name ?: optional($order->ad)->name ?: optional($order->ad)->business_name ?: 'N/A',
            $order->authorized_territory,
            $order->delivery_address,
            ucwords(str_replace('_', ' ', $order->shipping_type)),
            ucwords(str_replace('_', ' ', $order->payment_method)),
            $order->bank_name,
            $order->voucher_code,
            (float) $order->subtotal,
            (float) $order->delivery_fee,
            (float) $order->rebate_amount,
            (float) ($order->pickup_discount ?? 0),
            (float) $order->total_amount,
            (int) $order->total_qty,
            $order->items->count(),
            $order->status,
            optional($submittedAt)->format('Y-m-d H:i:s'),
            $order->remarks,
        ];
    }
}
