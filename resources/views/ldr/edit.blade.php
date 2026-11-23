@extends('layouts.header')

@section('css')
<style>
    .ldr-edit-page {
        --ldr-blue: #2563eb;
        --ldr-red: #dc2626;
        --ldr-ink: #111827;
        --ldr-muted: #667085;
        --ldr-line: #e4e7ec;
        display: grid;
        gap: 16px;
        padding-top: 18px;
        color: var(--ldr-ink);
    }
    .ldr-edit-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 20px 22px; background: linear-gradient(135deg, #fff, #f8fbff); border: 1px solid var(--ldr-line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .ldr-edit-head h2 { margin: 0; font-size: 24px; font-weight: 900; }
    .ldr-edit-head p { margin: 5px 0 0; color: var(--ldr-muted); font-size: 12px; }
    .ldr-edit-reference { display: inline-flex; margin-top: 9px; padding: 6px 10px; color: #1d4ed8; font-size: 11px; font-weight: 900; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 999px; }
    .ldr-edit-alert { padding: 14px 16px; color: #991b1b; font-size: 13px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 9px; }
    .ldr-edit-alert ul { margin: 8px 0 0; padding-left: 20px; }
    .ldr-edit-card { overflow: hidden; background: #fff; border: 1px solid var(--ldr-line); border-radius: 10px; box-shadow: 0 12px 30px rgba(15, 23, 42, .05); }
    .ldr-edit-card-head { display: flex; align-items: center; gap: 12px; padding: 17px 20px; border-bottom: 1px solid var(--ldr-line); }
    .ldr-edit-step { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 32px; color: #fff; font-size: 12px; font-weight: 900; background: var(--ldr-blue); border-radius: 50%; }
    .ldr-edit-card-head h3 { margin: 0; font-size: 16px; font-weight: 900; }
    .ldr-edit-card-head span:last-child { display: block; margin-top: 2px; color: var(--ldr-muted); font-size: 11px; }
    .ldr-edit-card-body { padding: 20px; }
    .ldr-edit-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; }
    .ldr-edit-wide { grid-column: 1 / -1; }
    .ldr-edit-label { display: block; margin-bottom: 7px; color: #344054; font-size: 12px; font-weight: 800; }
    .ldr-edit-required { color: var(--ldr-red); }
    .ldr-edit-field { width: 100%; min-height: 43px; padding: 10px 12px; color: #344054; font-size: 13px; background: #fff; border: 1px solid #d0d5dd; border-radius: 8px; outline: none; }
    textarea.ldr-edit-field { min-height: 100px; resize: vertical; }
    .ldr-edit-field:focus { border-color: var(--ldr-blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, .11); }
    .ldr-edit-field.is-invalid { border-color: var(--ldr-red); }
    .ldr-edit-invalid { margin-top: 5px; color: var(--ldr-red); font-size: 11px; font-weight: 700; }
    .ldr-edit-hint { display: block; margin-top: 6px; color: var(--ldr-muted); font-size: 11px; }
    .ldr-edit-choices { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .ldr-edit-choice { display: flex; align-items: flex-start; gap: 10px; min-height: 46px; padding: 11px 12px; color: #344054; font-size: 12px; font-weight: 800; cursor: pointer; background: #f8fafc; border: 1px solid var(--ldr-line); border-radius: 8px; }
    .ldr-edit-choice.is-selected { color: #1d4ed8; background: #eff6ff; border-color: var(--ldr-blue); }
    .ldr-edit-choice input { margin-top: 2px; accent-color: var(--ldr-blue); }
    .ldr-edit-store-signage { display: none; margin-top: 12px; padding: 14px; background: #fff; border: 1px solid var(--ldr-line); border-radius: 9px; }
    .ldr-edit-store-signage.is-visible { display: block; }
    .ldr-edit-store-layout { display: grid; grid-template-columns: 1fr; gap: 16px; }
    .ldr-edit-store-image { width: 100%; max-height: 260px; object-fit: contain; background: #f8fafc; border: 1px solid var(--ldr-line); border-radius: 8px; }
    .ldr-edit-store-options, .ldr-edit-attachments { display: grid; gap: 9px; }
    .ldr-edit-store-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
    .ldr-edit-attachment { padding: 12px; background: #f8fafc; border: 1px solid var(--ldr-line); border-radius: 8px; }
    .ldr-edit-attachment-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 9px; }
    .ldr-edit-attachment-name { min-width: 0; color: #1d4ed8; font-size: 12px; font-weight: 800; overflow-wrap: anywhere; text-decoration: none; }
    .ldr-edit-remove { display: inline-flex; align-items: center; gap: 6px; color: #b42318; font-size: 11px; font-weight: 800; }
    .ldr-edit-actions { display: flex; justify-content: flex-end; gap: 10px; }
    .ldr-edit-submit:disabled { cursor: wait; opacity: .68; }
    @media (max-width: 640px) {
        .ldr-edit-head { align-items: flex-start; flex-direction: column; }
        .ldr-edit-grid, .ldr-edit-choices { grid-template-columns: 1fr; }
        .ldr-edit-store-layout { grid-template-columns: 1fr; }
        .ldr-edit-store-fields { grid-template-columns: 1fr; }
        .ldr-edit-wide { grid-column: auto; }
        .ldr-edit-actions, .ldr-edit-actions .btn, .ldr-edit-submit { width: 100%; }
        .ldr-edit-actions { flex-direction: column-reverse; }
    }
</style>
@endsection

@section('content')
@php
    $selectedIntendedUses = old('intended_use', $layoutRequest['intended_use'] ?? []);
    $selectedCollateralTypes = old('marketing_collateral_type', $layoutRequest['marketing_collateral_type'] ?? []);
    $selectedStoreSignageOption = old('store_signage_option', $storeSignageOption);
    $pegSamples = $layoutRequest['peg_samples'] ?? [];
    $filePegSamples = [];
    $currentPegSampleLink = '';
    foreach ($pegSamples as $pegSample) {
        if (!empty($pegSample['is_link'])) {
            $currentPegSampleLink = $pegSample['output_attachment_path'] ?? '';
        } else {
            $filePegSamples[] = $pegSample;
        }
    }
    $pegSampleLink = old('peg_sample_link', $currentPegSampleLink);
    $dateNeeded = old('date_needed', !empty($layoutRequest['date_needed'])
        ? \Carbon\Carbon::parse($layoutRequest['date_needed'])->format('Y-m-d')
        : '');
@endphp
<main class="ldr-edit-page">
    <header class="ldr-edit-head">
        <div>
            <h2>Edit Layout Design Request</h2>
            <p>Update the request details while its status is still Pending.</p>
            <span class="ldr-edit-reference">{{ $layoutRequest['reference_no'] ?? 'Pending request' }}</span>
        </div>
        <a href="{{ route('layout.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </header>

    <form action="{{ route('layout.update', $layoutRequest['id']) }}" method="POST" enctype="multipart/form-data" id="ldrEditForm">
        @csrf
        @method('PUT')

        <section class="ldr-edit-card">
            <div class="ldr-edit-card-head">
                <span class="ldr-edit-step">A</span>
                <div><h3>Request Information</h3><span>Confirm the requestor and partner details.</span></div>
            </div>
            <div class="ldr-edit-card-body">
                <div class="ldr-edit-grid">
                    <div>
                        <label for="requestorFirstName" class="ldr-edit-label">First Name <span class="ldr-edit-required">*</span></label>
                        <input type="text" name="requestor_first_name" id="requestorFirstName" class="ldr-edit-field {{ $errors->has('requestor_first_name') ? 'is-invalid' : '' }}" value="{{ old('requestor_first_name', $layoutRequest['requestor_first_name'] ?? '') }}" required>
                        @if($errors->has('requestor_first_name'))<div class="ldr-edit-invalid">{{ $errors->first('requestor_first_name') }}</div>@endif
                    </div>
                    <div>
                        <label for="requestorLastName" class="ldr-edit-label">Last Name <span class="ldr-edit-required">*</span></label>
                        <input type="text" name="requestor_last_name" id="requestorLastName" class="ldr-edit-field {{ $errors->has('requestor_last_name') ? 'is-invalid' : '' }}" value="{{ old('requestor_last_name', $layoutRequest['requestor_last_name'] ?? '') }}" required>
                        @if($errors->has('requestor_last_name'))<div class="ldr-edit-invalid">{{ $errors->first('requestor_last_name') }}</div>@endif
                    </div>
                    <div>
                        <label for="department" class="ldr-edit-label">Partner Type <span class="ldr-edit-required">*</span></label>
                        <select name="department" id="department" class="ldr-edit-field {{ $errors->has('department') ? 'is-invalid' : '' }}" required>
                            @foreach($departments as $department)
                                <option value="{{ $department }}" @if(old('department', $layoutRequest['department'] ?? '') === $department) selected @endif>{{ $department }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('department'))<div class="ldr-edit-invalid">{{ $errors->first('department') }}</div>@endif
                    </div>
                    <div>
                        <label for="requestorEmail" class="ldr-edit-label">Email Address</label>
                        <input type="email" name="requestor_email" id="requestorEmail" class="ldr-edit-field" value="{{ $requestorEmail }}" readonly required>
                    </div>
                </div>
            </div>
        </section>

        <section class="ldr-edit-card">
            <div class="ldr-edit-card-head">
                <span class="ldr-edit-step">B</span>
                <div><h3>Design Details</h3><span>Update the output, purpose, and deadline.</span></div>
            </div>
            <div class="ldr-edit-card-body">
                <div class="ldr-edit-grid">
                    <div class="ldr-edit-wide">
                        <label for="mediumType" class="ldr-edit-label">Medium Type <span class="ldr-edit-required">*</span></label>
                        <select name="medium_type" id="mediumType" class="ldr-edit-field {{ $errors->has('medium_type') ? 'is-invalid' : '' }}" required>
                            @foreach($mediumTypes as $medium)
                                <option value="{{ $medium }}" @if(old('medium_type', $layoutRequest['medium_type'] ?? '') === $medium) selected @endif>{{ $medium }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('medium_type'))<div class="ldr-edit-invalid">{{ $errors->first('medium_type') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <span class="ldr-edit-label">Intended Use <span class="ldr-edit-required">*</span></span>
                        <div class="ldr-edit-choices">
                            @foreach($intendedUses as $use)
                                <label class="ldr-edit-choice @if(in_array($use, $selectedIntendedUses)) is-selected @endif">
                                    <input type="checkbox" name="intended_use[]" value="{{ $use }}" @if(in_array($use, $selectedIntendedUses)) checked @endif>
                                    <span>{{ $use }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($errors->has('intended_use'))<div class="ldr-edit-invalid">{{ $errors->first('intended_use') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <span class="ldr-edit-label">Marketing Collateral Type <span class="ldr-edit-required">*</span></span>
                        <div class="ldr-edit-choices">
                            @foreach($collateralTypes as $collateral)
                                <label class="ldr-edit-choice @if(in_array($collateral, $selectedCollateralTypes)) is-selected @endif">
                                    <input type="checkbox" name="marketing_collateral_type[]" value="{{ $collateral }}" @if(in_array($collateral, $selectedCollateralTypes)) checked @endif>
                                    <span>{{ $collateral }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($errors->has('marketing_collateral_type'))<div class="ldr-edit-invalid">{{ $errors->first('marketing_collateral_type') }}</div>@endif
                        <div id="storeSignageDetails" class="ldr-edit-store-signage @if(in_array('Store Signage', $selectedCollateralTypes)) is-visible @endif">
                            <div class="ldr-edit-store-layout">
                                <img src="{{ asset('images/store_signage_options.jpg') }}" alt="Store Signage option reference" class="ldr-edit-store-image">
                                <div>
                                    <span class="ldr-edit-label">Store Signage Option <span class="ldr-edit-required">*</span></span>
                                    <div class="ldr-edit-store-options">
                                        @foreach($storeSignageOptions as $option)
                                            <label class="ldr-edit-choice @if($selectedStoreSignageOption === $option) is-selected @endif">
                                                <input type="radio" name="store_signage_option" value="{{ $option }}" @if($selectedStoreSignageOption === $option) checked @endif>
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @if($errors->has('store_signage_option'))<div class="ldr-edit-invalid">{{ $errors->first('store_signage_option') }}</div>@endif
                                    <div class="ldr-edit-store-fields">
                                        <div>
                                            <label for="storeSignageBusinessName" class="ldr-edit-label">Business Name <span class="ldr-edit-required">*</span></label>
                                            <input type="text" name="business_name" id="storeSignageBusinessName" class="ldr-edit-field {{ $errors->has('business_name') ? 'is-invalid' : '' }}" value="{{ old('business_name', $layoutRequest['business_name'] ?? '') }}" maxlength="255" placeholder="e.g EMJ Minimart" data-store-signage-field>
                                            @if($errors->has('business_name'))<div class="ldr-edit-invalid">{{ $errors->first('business_name') }}</div>@endif
                                        </div>
                                        <div>
                                            <label for="storeSignageAdTerritory" class="ldr-edit-label">AD Territory <span class="ldr-edit-required">*</span></label>
                                            <input type="text" name="ad_territory" id="storeSignageAdTerritory" class="ldr-edit-field {{ $errors->has('ad_territory') ? 'is-invalid' : '' }}" value="{{ old('ad_territory', $layoutRequest['ad_territory'] ?? '') }}" maxlength="255" placeholder="e.g Bugallon" data-store-signage-field>
                                            @if($errors->has('ad_territory'))<div class="ldr-edit-invalid">{{ $errors->first('ad_territory') }}</div>@endif
                                        </div>
                                        <div>
                                            <label for="storeSignageBusinessAddress" class="ldr-edit-label">Business Address <span class="ldr-edit-required">*</span></label>
                                            <input type="text" name="business_address" id="storeSignageBusinessAddress" class="ldr-edit-field {{ $errors->has('business_address') ? 'is-invalid' : '' }}" value="{{ old('business_address', $layoutRequest['business_address'] ?? '') }}" maxlength="255" placeholder="Enter your business address" data-store-signage-field>
                                            @if($errors->has('business_address'))<div class="ldr-edit-invalid">{{ $errors->first('business_address') }}</div>@endif
                                        </div>
                                        <div>
                                            <label for="storeSignageContactNumber" class="ldr-edit-label">Contact No. <span class="ldr-edit-required">*</span></label>
                                            <input type="tel" name="contact_number" id="storeSignageContactNumber" class="ldr-edit-field {{ $errors->has('contact_number') ? 'is-invalid' : '' }}" value="{{ old('contact_number', $layoutRequest['contact_number'] ?? '') }}" maxlength="11" inputmode="numeric" pattern="[0-9]{1,11}" autocomplete="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" placeholder="09xxxxxxxxx" data-store-signage-field>
                                            @if($errors->has('contact_number'))<div class="ldr-edit-invalid">{{ $errors->first('contact_number') }}</div>@endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ldr-edit-wide">
                        <label for="layoutPurpose" class="ldr-edit-label">Purpose of the Layout Request <span class="ldr-edit-required">*</span></label>
                        <textarea style="resize: none;" name="purpose_of_layout_request" id="layoutPurpose" class="ldr-edit-field {{ $errors->has('purpose_of_layout_request') ? 'is-invalid' : '' }}" required>{{ old('purpose_of_layout_request', $layoutRequest['purpose_of_layout_request'] ?? '') }}</textarea>
                        @if($errors->has('purpose_of_layout_request'))<div class="ldr-edit-invalid">{{ $errors->first('purpose_of_layout_request') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <label for="projectDescription" class="ldr-edit-label">Project Description <span class="ldr-edit-required">*</span></label>
                        <textarea style="resize: none;" name="project_description" id="projectDescription" class="ldr-edit-field {{ $errors->has('project_description') ? 'is-invalid' : '' }}" required>{{ old('project_description', $layoutRequest['project_description'] ?? '') }}</textarea>
                        @if($errors->has('project_description'))<div class="ldr-edit-invalid">{{ $errors->first('project_description') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <span class="ldr-edit-label">Peg Sample Files</span>
                        <div class="ldr-edit-attachments">
                            @forelse($filePegSamples as $attachment)
                                <div class="ldr-edit-attachment">
                                    <div class="ldr-edit-attachment-head">
                                        <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="ldr-edit-attachment-name">
                                            <i class="ti ti-paperclip"></i> {{ $attachment['display_name'] ?? 'Peg sample' }}
                                        </a>
                                        <label class="ldr-edit-remove">
                                            <input type="checkbox" name="remove_peg_samples[]" value="{{ $attachment['id'] }}" class="ldr-remove-attachment">
                                            Remove
                                        </label>
                                    </div>
                                    <label for="pegReplacement{{ $attachment['id'] }}" class="ldr-edit-hint mb-1">Replace this file</label>
                                    <input type="file" name="peg_sample_replacements[{{ $attachment['id'] }}]" id="pegReplacement{{ $attachment['id'] }}" class="ldr-edit-field ldr-replacement-file" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.ppt,.pptx">
                                </div>
                            @empty
                                @if(!empty($layoutRequest['peg_sample_path']))
                                    <div class="ldr-edit-attachment">
                                        <div class="ldr-edit-attachment-head">
                                            <a href="{{ $layoutRequest['peg_sample_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="ldr-edit-attachment-name">
                                                <i class="ti ti-paperclip"></i> {{ basename($layoutRequest['peg_sample_path']) }}
                                            </a>
                                            <label class="ldr-edit-remove">
                                                <input type="checkbox" name="remove_legacy_peg_sample" value="1" id="removeLegacyPegSample">
                                                Remove
                                            </label>
                                        </div>
                                        <label for="legacyPegSample" class="ldr-edit-hint mb-1">Replace this file</label>
                                        <input type="file" name="peg_sample" id="legacyPegSample" class="ldr-edit-field" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.ppt,.pptx">
                                    </div>
                                @else
                                    <input type="file" name="peg_sample" id="legacyPegSample" class="ldr-edit-field" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.ppt,.pptx">
                                @endif
                            @endforelse
                        </div>
                        <span class="ldr-edit-hint">Maximum 30 MB per file. Replacing a file keeps the rest unchanged.</span>
                        @if($errors->has('peg_sample_replacements.*'))<div class="ldr-edit-invalid">{{ $errors->first('peg_sample_replacements.*') }}</div>@endif
                        @if($errors->has('peg_sample'))<div class="ldr-edit-invalid">{{ $errors->first('peg_sample') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <label for="pegSampleLink" class="ldr-edit-label">Peg Sample Link <span class="ldr-edit-hint d-inline">(optional)</span></label>
                        <input type="url" name="peg_sample_link" id="pegSampleLink" class="ldr-edit-field {{ $errors->has('peg_sample_link') ? 'is-invalid' : '' }}" value="{{ $pegSampleLink }}" placeholder="https://drive.google.com/...">
                        <span class="ldr-edit-hint">Update the URL, or clear it to remove the shared link.</span>
                        @if($errors->has('peg_sample_link'))<div class="ldr-edit-invalid">{{ $errors->first('peg_sample_link') }}</div>@endif
                    </div>

                    <div class="ldr-edit-wide">
                        <label for="dateNeeded" class="ldr-edit-label">Preferred Date Needed</label>
                        <input type="date" name="date_needed" id="dateNeeded" class="ldr-edit-field {{ $errors->has('date_needed') ? 'is-invalid' : '' }}" value="{{ $dateNeeded }}" min="{{ date('Y-m-d') }}">
                        @if($errors->has('date_needed'))<div class="ldr-edit-invalid">{{ $errors->first('date_needed') }}</div>@endif
                    </div>
                </div>
            </div>
        </section>

        <div class="ldr-edit-actions mt-2">
            <a href="{{ route('layout.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="ldr-edit-submit btn btn-primary" id="ldrEditSubmit">Update request</button>
        </div>
    </form>
</main>
@endsection

@section('javascript')
@include('ldr.partials.sweet_alerts')
<script>
    (function () {
        var storeSignageDetails = document.getElementById('storeSignageDetails');
        var storeSignageCheckbox = document.querySelector('input[name="marketing_collateral_type[]"][value="Store Signage"]');

        function updateStoreSignageVisibility() {
            var visible = storeSignageCheckbox && storeSignageCheckbox.checked;
            storeSignageDetails.classList.toggle('is-visible', visible);

            storeSignageDetails.querySelectorAll('[data-store-signage-field]').forEach(function (input) {
                input.required = visible;

                if (!visible) {
                    input.value = '';
                }
            });

            if (!visible) {
                storeSignageDetails.querySelectorAll('input[type="radio"]').forEach(function (input) {
                    input.checked = false;
                    input.closest('.ldr-edit-choice').classList.remove('is-selected');
                });
            }
        }

        document.querySelectorAll('.ldr-edit-choice input').forEach(function (input) {
            input.addEventListener('change', function () {
                if (this.type === 'radio') {
                    document.getElementsByName(this.name).forEach(function (radio) {
                        radio.closest('.ldr-edit-choice').classList.toggle('is-selected', radio.checked);
                    });
                } else {
                    this.closest('.ldr-edit-choice').classList.toggle('is-selected', this.checked);
                }

                if (this === storeSignageCheckbox) {
                    updateStoreSignageVisibility();
                }
            });
        });

        document.querySelectorAll('.ldr-edit-attachment').forEach(function (attachment) {
            var removeInput = attachment.querySelector('.ldr-remove-attachment, #removeLegacyPegSample');
            var fileInput = attachment.querySelector('input[type="file"]');

            if (!removeInput || !fileInput) {
                return;
            }

            removeInput.addEventListener('change', function () {
                if (this.checked) {
                    fileInput.value = '';
                }
            });

            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length) {
                    removeInput.checked = false;
                }
            });
        });

        updateStoreSignageVisibility();

        document.getElementById('ldrEditForm').addEventListener('submit', function () {
            var button = document.getElementById('ldrEditSubmit');
            button.disabled = true;
            button.textContent = 'Saving…';
        });
    })();
</script>
@endsection
