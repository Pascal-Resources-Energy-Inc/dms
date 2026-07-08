<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VouchersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $vouchers;

    public function __construct(Collection $vouchers)
    {
        $this->vouchers = $vouchers;
    }

    public function collection()
    {
        return $this->vouchers;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Distributor Store Code',
            'Areas',
            'Description',
            'Discount Type',
            'Discount Value',
            'Minimum Order Amount',
            'Used Count',
            'Usage Limit',
            'Starts At',
            'Expires At',
            'Status',
            'Is Active',
            'Created At',
        ];
    }

    public function map($voucher): array
    {
        return [
            $voucher->code,
            $voucher->name,
            $voucher->areaNamesLabel(),
            $voucher->description,
            ucfirst($voucher->discount_type),
            (float) $voucher->discount_value,
            (float) $voucher->minimum_order_amount,
            (int) $voucher->used_count,
            $voucher->usage_limit ? (int) $voucher->usage_limit : null,
            optional($voucher->starts_at)->format('Y-m-d'),
            optional($voucher->expires_at)->format('Y-m-d'),
            $voucher->statusLabel(),
            $voucher->is_active ? 'Yes' : 'No',
            optional($voucher->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
