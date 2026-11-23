<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ManageDistributorAreas
{
    public function validate(Request $request, $profile)
    {
        $request->validate([
            'rows' => 'nullable|array',
            'rows.*' => 'array',
            'rows.*.id' => 'nullable|integer|distinct',
            'removed_area_ids' => 'nullable|array',
            'removed_area_ids.*' => 'integer|distinct',
            'disengagement_dates' => 'nullable|array',
        ]);
        $existing = $profile->areas()->get()->keyBy('id');
        $rows = $request->input('rows', []) ?: [];
        foreach ($rows as &$row) {
            // Manage Areas cannot change project assignments or main business details.
            $area = $existing->get($row['id'] ?? null);
            $row['project_type'] = $area ? $area->project_type : null;
        }
        unset($row);
        $request->merge([
            'rows' => $rows,
            'business_name' => $profile->business_name,
            'business_type' => $profile->business_type,
        ]);
        $changes = app(DistributorProjectAreas::class)->validate($request, $profile);

        // Include omitted active rows when checking duplicates; never delete them implicitly.
        $names = [];
        $submitted = collect($changes['rows'])->keyBy('id');
        foreach ($existing as $area) {
            if (in_array((int) $area->id, $changes['removed_ids'], true)) continue;
            $row = $submitted->get($area->id);
            $name = strtolower(trim($row ? $row['area_name'] : $area->area_name));
            $names[$name][] = strtolower(trim($area->area_name)) === $name;
        }
        foreach ($changes['rows'] as $row) {
            if (!$row['id']) $names[strtolower(trim($row['area_name']))][] = false;
        }
        foreach ($names as $assignments) {
            // Existing assignments to the same area across projects must remain editable.
            if (count($assignments) > 1 && in_array(false, $assignments, true)) {
                throw ValidationException::withMessages(['rows' => 'Each awarded area can be selected only once.']);
            }
        }

        $dates = [];
        foreach ($changes['removed_ids'] as $id) {
            $rules = ['required', 'date_format:Y-m-d', 'before_or_equal:today'];
            if ($existing->get($id)->joining_date) {
                $rules[] = 'after_or_equal:' . \Carbon\Carbon::parse($existing->get($id)->joining_date)->format('Y-m-d');
            }
            $request->validate(["disengagement_dates.{$id}" => $rules], [
                "disengagement_dates.{$id}.required" => 'Enter a disengagement date for each removed area.',
                "disengagement_dates.{$id}.before_or_equal" => 'The disengagement date cannot be in the future.',
                "disengagement_dates.{$id}.after_or_equal" => 'The disengagement date cannot be before the joining date.',
            ]);
            $dates[$id] = $request->input("disengagement_dates.{$id}");
        }
        $changes['disengagement_dates'] = $dates;
        return $changes;
    }
}
