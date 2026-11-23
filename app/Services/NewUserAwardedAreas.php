<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class NewUserAwardedAreas
{
    public function validate(Request $request)
    {
        $rules = [
            'business_name' => 'required|string|max:255',
            'business_type' => ['required', 'string', Rule::in(DistributorProjectAreas::BUSINESS_TYPES)],
            'area_name' => 'required|array|min:1|max:100',
            'area_name.*' => 'required|string|max:255|exists:areas,name',
            'joining_date' => 'required|array',
            'package_amount' => 'nullable|array',
            'area_business_name' => 'nullable|array',
            'area_business_type' => 'nullable|array',
            'area_business_type.*' => ['nullable', 'string', Rule::in(DistributorProjectAreas::BUSINESS_TYPES)],
            'area_same_business_name' => 'nullable|array',
            'area_same_business_name.*' => 'boolean',
            'area_same_business_type' => 'nullable|array',
            'area_same_business_type.*' => 'boolean',
            'area_offer_sheet' => 'nullable|array',
            'area_offer_sheet.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
        $attributes = [];
        foreach ((array) $request->input('area_name', []) as $index => $name) {
            if (!ctype_digit((string) $index)) {
                throw ValidationException::withMessages(['area_name' => 'Please remove and re-add the invalid awarded area.']);
            }
            $same = filter_var($request->input("area_same_business_name.{$index}", false), FILTER_VALIDATE_BOOLEAN);
            $rules["joining_date.{$index}"] = 'required|date_format:Y-m-d';
            $rules["package_amount.{$index}"] = 'nullable|numeric|min:0|max:9999999999.99|regex:/^\d+(\.\d{1,2})?$/';
            $rules["area_business_name.{$index}"] = $same ? 'nullable|string|max:255' : 'required|string|max:255';
            foreach (['area_name' => 'area', 'joining_date' => 'joining date', 'package_amount' => 'package amount',
                'area_business_name' => 'business name', 'area_business_type' => 'business type', 'area_offer_sheet' => 'offer sheet'] as $field => $label) {
                $attributes["{$field}.{$index}"] = (is_string($name) && $name !== '' ? $name : 'Awarded area') . ' ' . $label;
            }
        }
        $request->validate($rules, [], $attributes);

        $rows = [];
        $seen = [];
        foreach ($request->input('area_name') as $index => $name) {
            $name = trim($name);
            $key = mb_strtolower($name);
            if (in_array($key, $seen, true)) {
                throw ValidationException::withMessages(["area_name.{$index}" => 'Each awarded area can only be selected once.']);
            }
            $seen[] = $key;
            $same = filter_var($request->input("area_same_business_name.{$index}", false), FILTER_VALIDATE_BOOLEAN);
            $sameType = filter_var($request->input("area_same_business_type.{$index}", false), FILTER_VALIDATE_BOOLEAN);
            $amount = $request->input("package_amount.{$index}");
            $rows[$index] = [
                'area_name' => $name,
                'joining_date' => $request->input("joining_date.{$index}"),
                'business_name' => trim($same ? $request->business_name : $request->input("area_business_name.{$index}")),
                'business_type' => $sameType ? $request->business_type : ($request->input("area_business_type.{$index}") ?: null),
                'package_amount' => $amount !== null && $amount !== '' ? number_format((float) $amount, 2, '.', '') : null,
            ];
        }
        return $rows;
    }
}
