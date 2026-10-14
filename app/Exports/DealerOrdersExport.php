<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DealerOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
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
            'Transaction ID',
            'Date',
            'Quantity',
            'Unit Price',
            'Amount',
            'Dealer',
            'Center Chief',
            'Area Distributor',
            'Dealer Points',
            'Item',
            'Payment Method',
            'Delivery Type',
            'Delivery Fee',
            'Status',
        ];
    }

    public function map($order): array
    {
        $deliveryFee = $order->delivery_type === 'delivery' ? (float) ($order->delivery_fee ?? 0) : 0;

        return [
            $order->transaction_id,
            $order->date ? date('Y-m-d', strtotime($order->date)) : optional($order->created_at)->format('Y-m-d'),
            (float) $order->qty,
            (float) $order->price,
            ((float) $order->qty * (float) $order->price) + $deliveryFee,
            optional($order->dealer)->name,
            optional($order->adDealer)->spo,
            optional($order->ad)->business_name ?: optional($order->ad)->name,
            (float) ($order->points_dealer ?? 0),
            $order->item,
            ucwords(str_replace('_', ' ', $order->payment_method)),
            ucfirst($order->delivery_type),
            $order->delivery_type === 'delivery' ? $deliveryFee : null,
            $order->status,
        ];
    }
}
