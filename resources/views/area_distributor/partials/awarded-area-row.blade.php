@php
    $savedArea = $area->id ? $ad->areas->firstWhere('id', $area->id) : null;
    $mainBusinessName = $restoreProjectAreas ? old('business_name', $ad->business_name) : $ad->business_name;
    $areaBusinessName = $area->business_name ?? $mainBusinessName;
    $sameBusinessName = $restoreProjectAreas
        ? filter_var($area->same_business_name ?? false, FILTER_VALIDATE_BOOLEAN)
        : (!$area->business_name || $area->business_name === $mainBusinessName);
    $mainBusinessType = $restoreProjectAreas ? old('business_type', $ad->business_type) : $ad->business_type;
    $sameBusinessType = $restoreProjectAreas
        ? filter_var($area->same_business_type ?? false, FILTER_VALIDATE_BOOLEAN)
        : (in_array($mainBusinessType, \App\Services\DistributorProjectAreas::BUSINESS_TYPES, true)
            && (!$area->id || ($area->business_type ?? null) === $mainBusinessType));
    $areaBusinessType = $sameBusinessType ? $mainBusinessType : ($area->business_type ?? null);
    $offerSheetPath = optional($savedArea)->offer_sheet_path;
    $offerSheetName = optional($savedArea)->offer_sheet_name;
@endphp
<div class="ad-project-row" data-project-row>
    <input type="hidden" class="ad-row-id" name="rows[{{ $areaIndex }}][id]" value="{{ $area->id }}">
    <input type="hidden" class="ad-row-project-value" name="rows[{{ $areaIndex }}][project_type]" value="{{ $area->project_type }}">
    <div class="ad-area-heading">
        <div><strong>Awarded area</strong><small>{{ $area->project_type ?: 'No project assigned' }}</small></div>
        <button type="button" class="btn btn-outline-danger ad-remove-project-row" aria-label="Remove awarded area"><i class="ti ti-trash" aria-hidden="true"></i> Remove</button>
    </div>
    <div class="ad-area-fields">
        <div class="ad-area-selection">
            <label class="form-label" for="ad_area_name_{{ $ad->id }}_{{ $areaIndex }}">Awarded area <span class="text-danger">*</span></label>
            <select class="form-select ad-row-area-name select2" id="ad_area_name_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][area_name]" data-placeholder="Search awarded areas..." required>
                <option value="">Select area</option>
                @foreach($awardedAreaOptions as $areaOption)
                    <option value="{{ $areaOption }}" {{ trim((string) $area->area_name) === $areaOption ? 'selected' : '' }}>{{ $areaOption }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="ad_area_date_{{ $ad->id }}_{{ $areaIndex }}">Joining date</label>
            <input type="date" class="form-control" id="ad_area_date_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][joining_date]" value="{{ $restoreProjectAreas ? $area->joining_date : ($area->joining_date ? \Carbon\Carbon::parse($area->joining_date)->format('Y-m-d') : '') }}">
        </div>
        <div>
            <label class="form-label" for="ad_area_amount_{{ $ad->id }}_{{ $areaIndex }}">Package amount</label>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control ad-row-package-amount" id="ad_area_amount_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][package_amount]" value="{{ $restoreProjectAreas ? $area->package_amount : ($area->package_amount !== null ? number_format((float) $area->package_amount, 2, '.', '') : '') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" placeholder="Not set">
            </div>
        </div>
        <div class="ad-area-business">
            <label class="form-label" for="ad_area_business_{{ $ad->id }}_{{ $areaIndex }}">Business name <span class="text-danger">*</span></label>
            <input type="text" class="form-control ad-row-business-name" id="ad_area_business_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][business_name]" value="{{ $sameBusinessName ? $mainBusinessName : $areaBusinessName }}" maxlength="255" placeholder="Business name for this area" required @if($sameBusinessName) readonly @endif>
            <input type="hidden" name="rows[{{ $areaIndex }}][same_business_name]" value="0">
            <label class="ad-area-same-name" for="ad_area_same_name_{{ $ad->id }}_{{ $areaIndex }}">
                <input type="checkbox" class="ad-row-same-business" id="ad_area_same_name_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][same_business_name]" value="1" @if($sameBusinessName) checked @endif>
                <span>Same as main business name</span>
            </label>
        </div>
        <div class="ad-area-business-type">
            <label class="form-label" for="ad_area_type_{{ $ad->id }}_{{ $areaIndex }}">Business type</label>
            <select class="form-select ad-row-business-type" id="ad_area_type_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][business_type]" @if($sameBusinessType) disabled @endif>
                <option value="">Select business type</option>
                @foreach(\App\Services\DistributorProjectAreas::BUSINESS_TYPES as $businessType)
                    <option value="{{ $businessType }}" {{ $areaBusinessType === $businessType ? 'selected' : '' }}>{{ $businessType }}</option>
                @endforeach
            </select>
            <input type="hidden" name="rows[{{ $areaIndex }}][same_business_type]" value="0">
            <label class="ad-area-same-name" for="ad_area_same_type_{{ $ad->id }}_{{ $areaIndex }}">
                <input type="checkbox" class="ad-row-same-business-type" id="ad_area_same_type_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][same_business_type]" value="1" @if($sameBusinessType) checked @endif>
                <span>Same as main business type</span>
            </label>
        </div>
        <div class="ad-area-offer">
            <label class="form-label" for="ad_area_offer_{{ $ad->id }}_{{ $areaIndex }}">Offer sheet <span class="text-muted fw-normal">(optional)</span></label>
            @if($offerSheetPath)
                <a class="ad-area-current-file" href="{{ route($ad instanceof \App\ProvincialDistributor ? 'pds.areas.offer-sheet' : 'ad.areas.offer-sheet', ['id' => $ad->id, 'area' => $savedArea->id]) }}">
                    <i class="ti ti-file-download" aria-hidden="true"></i><span>{{ $offerSheetName ?: 'Download current offer sheet' }}</span>
                </a>
            @endif
            <input type="file" class="form-control ad-row-offer-sheet" id="ad_area_offer_{{ $ad->id }}_{{ $areaIndex }}" name="rows[{{ $areaIndex }}][offer_sheet]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <small>PDF, Word, JPG or PNG · Up to 10 MB. @if($offerSheetPath) Upload a new file to replace this offer sheet. @endif</small>
        </div>
    </div>
</div>
