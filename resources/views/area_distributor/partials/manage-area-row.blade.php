@php
    $managePrefix = 'manage-' . $ad->id . '-' . $manageIndex;
    $manageId = optional($manageArea)->id;
    $manageAreaName = trim((string) optional($manageArea)->area_name);
    $manageName = optional($manageArea)->business_name;
    $manageType = optional($manageArea)->business_type;
    $manageSameName = $manageMainName !== '' && (!$manageName || $manageName === $manageMainName);
    $manageSameType = in_array($manageMainType, $manageBusinessTypes, true) && (!$manageArea || $manageType === $manageMainType);
    $manageOptionNames = $areas->pluck('name')->map(function ($name) { return trim((string) $name); })->unique();
@endphp
<section class="manage-area-row" data-area-id="{{ $manageId }}">
    <div class="manage-area-row-header">
        <div><strong class="manage-area-row-title">{{ $manageAreaName ?: 'New area' }}</strong>@if(optional($manageArea)->project_type)<span class="manage-area-project">{{ $manageArea->project_type }}</span>@endif</div>
        <button type="button" class="btn btn-outline-danger manage-area-remove" aria-label="Delete {{ $manageAreaName ?: 'new area' }}" title="Delete area"><i class="ti ti-trash" aria-hidden="true"></i><span class="visually-hidden">Delete area</span></button>
    </div>
    <fieldset class="manage-area-fields">
        <legend class="visually-hidden">Area details</legend>
        <input type="hidden" name="rows[{{ $manageIndex }}][id]" value="{{ $manageId }}">
        <div class="manage-area-assignment">
            <div><label for="{{ $managePrefix }}-area">Area name <span aria-hidden="true">*</span></label>
                <select id="{{ $managePrefix }}-area" class="form-select manage-area-select" name="rows[{{ $manageIndex }}][area_name]" required>
                    <option value="">Select area</option>
                    @if($manageAreaName !== '' && !$manageOptionNames->contains($manageAreaName))<option selected value="{{ $manageAreaName }}">{{ $manageAreaName }}</option>@endif
                    @foreach($manageOptionNames as $name)<option value="{{ $name }}" {{ $manageAreaName === $name ? 'selected' : '' }}>{{ $name }}</option>@endforeach
                </select>
            </div>
            <div><label for="{{ $managePrefix }}-amount">Package amount (PHP)</label><input id="{{ $managePrefix }}-amount" class="form-control" type="number" inputmode="decimal" step="0.01" min="0" max="9999999999.99" name="rows[{{ $manageIndex }}][package_amount]" value="{{ optional($manageArea)->package_amount }}" placeholder="0.00"></div>
            <div><label for="{{ $managePrefix }}-joined">Joining date</label><input id="{{ $managePrefix }}-joined" class="form-control" type="date" name="rows[{{ $manageIndex }}][joining_date]" value="{{ optional($manageArea)->joining_date ? \Carbon\Carbon::parse($manageArea->joining_date)->format('Y-m-d') : '' }}"></div>
        </div>
        <div class="manage-area-business">
            <div><label for="{{ $managePrefix }}-name">Business name <span aria-hidden="true">*</span></label>
                <input id="{{ $managePrefix }}-name" class="form-control manage-area-business-name" name="rows[{{ $manageIndex }}][business_name]" maxlength="255" value="{{ $manageSameName ? $manageMainName : $manageName }}" data-custom-value="{{ $manageName }}" required {{ $manageSameName ? 'readonly' : '' }}>
                <input type="hidden" name="rows[{{ $manageIndex }}][same_business_name]" value="0">
                <label class="manage-area-check" for="{{ $managePrefix }}-same-name"><input id="{{ $managePrefix }}-same-name" type="checkbox" class="manage-area-same-name" name="rows[{{ $manageIndex }}][same_business_name]" value="1" {{ $manageSameName ? 'checked' : '' }} {{ $manageMainName === '' ? 'disabled' : '' }}>Same as main business name</label>
            </div>
            <div><label for="{{ $managePrefix }}-type">Business type</label>
                <select id="{{ $managePrefix }}-type" class="form-select manage-area-business-type" name="rows[{{ $manageIndex }}][business_type]" data-custom-value="{{ $manageType }}" {{ $manageSameType ? 'disabled' : '' }}>
                    <option value="">Select business type</option>
                    @foreach($manageBusinessTypes as $businessType)<option value="{{ $businessType }}" {{ ($manageSameType ? $manageMainType : $manageType) === $businessType ? 'selected' : '' }}>{{ $businessType }}</option>@endforeach
                </select>
                <input type="hidden" name="rows[{{ $manageIndex }}][same_business_type]" value="0">
                <label class="manage-area-check" for="{{ $managePrefix }}-same-type"><input id="{{ $managePrefix }}-same-type" type="checkbox" class="manage-area-same-type" name="rows[{{ $manageIndex }}][same_business_type]" value="1" {{ $manageSameType ? 'checked' : '' }} {{ !in_array($manageMainType, $manageBusinessTypes, true) ? 'disabled' : '' }}>Same as main business type</label>
                @if(!in_array($manageMainType, $manageBusinessTypes, true))<small>Select a type for this area, or update the main type in Edit.</small>@endif
            </div>
        </div>
        <div class="manage-area-file"><div><label for="{{ $managePrefix }}-offer">Offer sheet <small>(optional)</small></label><input id="{{ $managePrefix }}-offer" class="form-control manage-area-offer" type="file" name="rows[{{ $manageIndex }}][offer_sheet]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" aria-describedby="{{ $managePrefix }}-file-help"><small id="{{ $managePrefix }}-file-help">PDF, Word or image · Maximum 10 MB</small></div>
            @if(optional($manageArea)->offer_sheet_path)
                <div class="manage-area-current-file"><small>Current offer sheet</small><a href="{{ route($manageOfferRoute, [$ad->id, $manageArea->id]) }}">{{ $manageArea->offer_sheet_name ?: 'Download offer sheet' }}</a><small>Choose a new file only to replace it.</small></div>
            @endif
        </div>
    </fieldset>
    @if($manageId)
        <div class="manage-area-disengagement" hidden>
            <input class="manage-area-removed-id" type="hidden" name="removed_area_ids[]" value="{{ $manageId }}" disabled>
            <div><label for="{{ $managePrefix }}-disengaged">Date of disengagement <span aria-hidden="true">*</span></label><input id="{{ $managePrefix }}-disengaged" class="form-control manage-area-disengagement-date" type="date" name="disengagement_dates[{{ $manageId }}]" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" @if($manageArea->joining_date) min="{{ \Carbon\Carbon::parse($manageArea->joining_date)->format('Y-m-d') }}" @endif disabled><small>This area will move to disengagement history when you save.</small></div>
            <button type="button" class="btn btn-outline-secondary manage-area-undo">Undo removal</button>
        </div>
    @endif
</section>
