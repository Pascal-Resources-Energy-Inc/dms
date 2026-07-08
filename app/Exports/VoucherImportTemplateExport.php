<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VoucherImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function headings(): array
    {
        return [
            // 'code',
            // 'name',
            // 'description',
            // 'discount_type',
            // 'discount_value',
            // 'minimum_order_amount',
            // 'usage_limit',
            // 'starts_at',
            // 'expires_at',
            // 'is_active',
            'Code',
            'Name',
            'Areas',
            'Description',
            'Discount Type (fixed or percent)',
            'Discount Value',
            'Minimum Order Amount',
            'Usage Limit',
            'Start Date (YYYY-MM-DD)',
            'Expiration Date (YYYY-MM-DD)',
            'Is Active (1 for active, 0 for inactive)',
        ];
    }

    public function array(): array
    {
        return [];
    }
}
