<?php

namespace App\Services;

use App\ProvincialDistributor;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DistributorProjectAreas
{
    public static function projectTags($user)
    {
        $value = optional($user)->type;
        if (!is_array($value) && trim((string) $value) === '') {
            $value = optional($user)->project_tag;
        }
        $decoded = is_array($value) ? $value : json_decode((string) $value, true);
        $tags = is_array($decoded) ? $decoded : explode(',', (string) $value);

        return collect($tags)->filter(function ($tag) {
            return is_string($tag);
        })->map(function ($tag) {
            return trim($tag);
        })->filter(function ($tag) {
            return in_array($tag, ['Project Rise', 'Project Genesis', 'Regular'], true);
        })->unique()->values()->all();
    }

    public function validate(Request $request, $profile)
    {
        $request->validate([
            'type' => 'nullable|array',
            'type.*' => 'string|in:Project Rise,Project Genesis,Regular',
            'rows' => 'nullable|array',
            'rows.*' => 'array',
            'rows.*.id' => 'nullable|integer|distinct',
            'rows.*.project_type' => 'nullable|string|max:100',
            'rows.*.area_name' => 'required|string|max:255',
            'rows.*.joining_date' => 'nullable|date_format:Y-m-d',
            'rows.*.package_amount' => 'nullable|numeric|min:0|max:9999999999.99',
            'removed_area_ids' => 'nullable|array',
            'removed_area_ids.*' => 'integer|distinct',
        ]);

        $existing = $profile->areas()->get()->keyBy('id');
        $selected = $request->input('type', []) ?: [];
        $removed = array_map('intval', $request->input('removed_area_ids', []) ?: []);
        foreach ($removed as $id) {
            if (!$existing->has($id)) {
                throw ValidationException::withMessages(['removed_area_ids' => 'An area selected for removal does not belong to this distributor or is no longer active.']);
            }
        }

        $rows = [];
        foreach ($request->input('rows', []) ?: [] as $index => $row) {
            $id = !empty($row['id']) ? (int) $row['id'] : null;
            $area = $id ? $existing->get($id) : null;
            $project = array_key_exists('project_type', $row)
                ? (trim((string) $row['project_type']) ?: null)
                : ($area ? $area->project_type : null);
            if ($id && (!$area || in_array($id, $removed, true))) {
                throw ValidationException::withMessages(["rows.{$index}.id" => 'This awarded area is unavailable. Reload the distributor and try again.']);
            }
            // Keep an existing assignment even if its partner tag was later removed.
            if ($project && !in_array($project, $selected, true) && (!$area || $project !== $area->project_type)) {
                throw ValidationException::withMessages(["rows.{$index}.project_type" => 'Select this project in the distributor Project Tag field first.']);
            }
            $name = trim($row['area_name']);
            if ($name === '') {
                throw ValidationException::withMessages(["rows.{$index}.area_name" => 'Select an awarded area.']);
            }
            $validatedRow = ['id' => $id, 'project_type' => $project, 'area_name' => $name, 'joining_date' => ($row['joining_date'] ?? null) ?: null];
            if (array_key_exists('package_amount', $row)) {
                $validatedRow['package_amount'] = $row['package_amount'] !== null && $row['package_amount'] !== ''
                    ? number_format((float) $row['package_amount'], 2, '.', '')
                    : null;
            }
            $rows[] = $validatedRow;
        }

        return ['rows' => $rows, 'removed_ids' => $removed];
    }

    /** Called inside the profile update transaction; omitted areas stay unchanged. */
    public function sync($profile, array $changes)
    {
        $isPd = $profile instanceof ProvincialDistributor;
        foreach ($changes['rows'] as $row) {
            $area = $row['id']
                ? $profile->areas()->lockForUpdate()->findOrFail($row['id'])
                : $profile->areas()->make();
            $attributes = [
                'area_name' => $row['area_name'],
                'project_type' => $row['project_type'],
                'joining_date' => $row['joining_date'],
                'user_role' => $isPd ? User::ROLE_PROVINCIAL_DISTRIBUTOR : User::ROLE_AREA_DISTRIBUTOR,
                $isPd ? 'user_id' : 'ad_user_id' => $profile->user_id,
            ];
            if (array_key_exists('package_amount', $row)) {
                $attributes['package_amount'] = $row['package_amount'];
            }
            $area->fill($attributes)->save();
        }
        if ($changes['removed_ids']) {
            $profile->areas()->whereIn('id', $changes['removed_ids'])->delete();
        }
    }
}
