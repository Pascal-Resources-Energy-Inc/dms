<?php

namespace App\Imports;

use App\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class VouchersImport implements ToCollection, WithHeadingRow
{
    protected $created = 0;
    protected $skipped = 0;
    protected $errors;
    protected $seenCodes = [];

    public function __construct()
    {
        $this->errors = new MessageBag;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalizeRow($row);

            if ($this->isBlankRow($data)) {
                continue;
            }

            $validator = Validator::make($data, [
                'code' => 'required|string|max:100',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:fixed,percent',
                'discount_value' => 'required|numeric|min:0.01',
                'minimum_order_amount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:starts_at',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $this->errors->add('row_' . $rowNumber, 'Row ' . $rowNumber . ': ' . $validator->errors()->first());
                continue;
            }

            if (isset($this->seenCodes[$data['code']]) || Voucher::where('code', $data['code'])->exists()) {
                $this->skipped++;
                continue;
            }

            $this->seenCodes[$data['code']] = true;

            if ($data['discount_type'] === 'percent' && (float) $data['discount_value'] > 100) {
                $data['discount_value'] = 100;
            }

            Voucher::create($data);
            $this->created++;
        }
    }

    public function createdCount()
    {
        return $this->created;
    }

    public function skippedCount()
    {
        return $this->skipped;
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function normalizeRow($row)
    {
        $data = [
            'code' => strtoupper(trim((string) $this->rowValue($row, ['code'], 0))),
            'name' => trim((string) $this->rowValue($row, ['name', 'ad_name'], 1)),
            'area_names' => $this->blankToNull($this->rowValue($row, ['areas', 'area_names'], 2)),
            'description' => $this->blankToNull($this->rowValue($row, ['description'], 3)),
            'discount_type' => $this->normalizeDiscountType($this->rowValue($row, ['discount_type', 'discount type'], 4)),
            'discount_value' => $this->blankToNull($this->rowValue($row, ['discount_value', 'discount value'], 5)),
            'minimum_order_amount' => $this->blankToNull($this->rowValue($row, ['minimum_order_amount', 'minimum order amount'], 6)),
            'usage_limit' => $this->blankToNull($this->rowValue($row, ['usage_limit', 'usage limit'], 7)),
            'starts_at' => $this->normalizeDate($this->rowValue($row, ['starts_at', 'start_date', 'start date'], 8)),
            'expires_at' => $this->normalizeDate($this->rowValue($row, ['expires_at', 'expiry_date', 'expiry date'], 9)),
            'is_active' => $this->normalizeBoolean($this->rowValue($row, ['is_active', 'active'], 10)),
        ];

        $data['minimum_order_amount'] = $data['minimum_order_amount'] === null ? 0 : $data['minimum_order_amount'];
        $data['is_active'] = $data['is_active'] === null ? 1 : $data['is_active'];

        return $data;
    }

    protected function rowValue($row, array $keys, $fallbackIndex)
    {
        foreach ($keys as $key) {
            if ($row->has($key)) {
                return $row->get($key);
            }
        }

        return $row->values()->get($fallbackIndex);
    }

    protected function isBlankRow(array $data)
    {
        return empty($data['code'])
            && empty($data['name'])
            && empty($data['discount_type'])
            && $data['discount_value'] === null;
    }

    protected function blankToNull($value)
    {
        if ($value === null) {
            return null;
        }

        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }

    protected function normalizeDate($value)
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $exception) {
            return $value;
        }
    }

    protected function normalizeDiscountType($value)
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            return null;
        }

        $value = strtolower(trim((string) $value));
        $value = str_replace(['-', '_'], ' ', $value);

        if (in_array($value, ['fixed', 'fixed amount', 'amount'], true)) {
            return 'fixed';
        }

        if (in_array($value, ['percent', 'percentage', 'percentage discount'], true)) {
            return 'percent';
        }

        return $value;
    }

    protected function normalizeBoolean($value)
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1', 'yes', 'y', 'true', 'active'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'no', 'n', 'false', 'inactive'], true)) {
            return 0;
        }

        return $value;
    }
}
