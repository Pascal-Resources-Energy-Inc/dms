<?php

namespace App\Services;

use App\ProvincialDistributor;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DistributorProjectAreas
{
    const BUSINESS_TYPES = ['Sole Proprietorship', 'Partnership', 'Limited Liability Company (LLC)', 'Corporation', 'Cooperative', 'Nonprofit'];

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
            'rows.*.business_name' => 'nullable|string|max:255',
            'rows.*.same_business_name' => 'nullable|boolean',
            'rows.*.business_type' => ['nullable', 'string', Rule::in(self::BUSINESS_TYPES)],
            'rows.*.same_business_type' => 'nullable|boolean',
            'rows.*.offer_sheet' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
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
            $sameBusiness = filter_var($row['same_business_name'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($sameBusiness) {
                $request->validate(['business_name' => 'required|string|max:255']);
                $validatedRow['business_name'] = trim($request->business_name);
            } elseif (array_key_exists('business_name', $row)) {
                $request->validate(["rows.{$index}.business_name" => 'required|string|max:255']);
                $validatedRow['business_name'] = trim($row['business_name']);
            }
            $sameBusinessType = filter_var($row['same_business_type'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($sameBusinessType) {
                $request->validate(['business_type' => ['required', 'string', Rule::in(self::BUSINESS_TYPES)]], [
                    'business_type.in' => 'Select a main business type from the six available types, or uncheck Same as main business type for this area.',
                ]);
                $validatedRow['business_type'] = $request->business_type;
            } elseif (array_key_exists('business_type', $row)) {
                $validatedRow['business_type'] = $row['business_type'] ?: null;
            }
            if ($request->hasFile("rows.{$index}.offer_sheet")) {
                $validatedRow['offer_sheet'] = $request->file("rows.{$index}.offer_sheet");
            }
            $rows[] = $validatedRow;
        }

        return ['rows' => $rows, 'removed_ids' => $removed];
    }

    /** Called inside the profile update transaction; omitted areas stay unchanged. */
    public function sync($profile, array $changes, array &$uploadedPaths = [])
    {
        $isPd = $profile instanceof ProvincialDistributor;
        try {
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
                foreach (['package_amount', 'business_name', 'business_type'] as $field) {
                    if (array_key_exists($field, $row)) $attributes[$field] = $row[$field];
                }
                if (!empty($row['offer_sheet'])) {
                    $file = $row['offer_sheet'];
                    $path = $file->store('distributor-offer-sheets/' . $profile->user_id, 'local');
                    if (!$path) throw new \RuntimeException('Unable to store the awarded area offer sheet.');
                    $uploadedPaths[] = $path;
                    $attributes['offer_sheet_path'] = $path;
                    $attributes['offer_sheet_name'] = mb_substr(basename($file->getClientOriginalName()), 0, 255);
                }
                $area->fill($attributes)->save();
            }
            if ($changes['removed_ids']) {
                foreach ($changes['removed_ids'] as $id) {
                    $area = $profile->areas()->lockForUpdate()->findOrFail($id);
                    $area->delete();
                    // Disengagement history and reports use the soft-delete date.
                    if (isset($changes['disengagement_dates'][$id])) {
                        $area->deleted_at = $changes['disengagement_dates'][$id];
                        $area->save();
                    }
                }
            }
        } catch (\Throwable $error) {
            $this->discardUploads($uploadedPaths);
            throw $error;
        }
    }

    public function discardUploads(array $paths)
    {
        if ($paths) Storage::disk('local')->delete($paths);
    }
}
