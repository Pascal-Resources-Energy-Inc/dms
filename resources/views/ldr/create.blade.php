@extends('layouts.header')

@section('css')
<style>
    :root {
        --ldr-blue: #2563eb;
        --ldr-blue-soft: #eff6ff;
        --ldr-red: #dc2626;
        --ldr-ink: #111827;
        --ldr-muted: #667085;
        --ldr-line: #e4e7ec;
        --ldr-soft: #f8fafc;
    }

    .ldr-page { color: var(--ldr-ink); font-family: Inter, Nunito, system-ui, sans-serif; }
    .ldr-form-code { flex: 0 0 auto; padding: 7px 11px; color: var(--ldr-muted); font-size: 11px; font-weight: 800; letter-spacing: .04em; background: #f8fafc; border: 1px solid var(--ldr-line); border-radius: 999px; }

    .ldr-wrap { width: 100%; padding-top: 18px; }
    .ldr-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 16px; padding: 20px 22px; background: linear-gradient(135deg, #fff, #f8fbff); border: 1px solid var(--ldr-line); border-radius: 12px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); }
    .ldr-eyebrow { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 9px; padding: 6px 10px; color: #1d4ed8; font-size: 11px; font-weight: 900; background: var(--ldr-blue-soft); border: 1px solid #bfdbfe; border-radius: 999px; }
    .ldr-head h1 { margin: 0; font-size: clamp(28px, 5vw, 42px); font-weight: 900; line-height: 1.08; }
    .ldr-head p { max-width: 680px; margin: 10px 0 0; color: var(--ldr-muted); font-size: 14px; line-height: 1.55; }

    .ldr-alert { margin-bottom: 18px; padding: 14px 16px; border: 1px solid; border-radius: 9px; font-size: 13px; line-height: 1.5; }
    .ldr-alert-success { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
    .ldr-alert-danger { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
    .ldr-alert-danger ul { margin: 8px 0 0; padding-left: 20px; }
    .ldr-notice { margin-bottom: 20px; padding: 12px 14px; color: #92400e; font-size: 12px; line-height: 1.5; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; }

    .ldr-card { margin-bottom: 16px; background: #fff; border: 1px solid var(--ldr-line); border-radius: 10px; box-shadow: 0 12px 30px rgba(15, 23, 42, .05); overflow: hidden; }
    .ldr-card-head { display: flex; align-items: center; gap: 12px; padding: 17px 20px; border-bottom: 1px solid var(--ldr-line); }
    .ldr-step { width: 32px; height: 32px; flex: 0 0 32px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 900; background: var(--ldr-blue); border-radius: 50%; }
    .ldr-card-head h2 { margin: 0; font-size: 16px; font-weight: 900; }
    .ldr-card-copy span { display: block; margin-top: 2px; color: var(--ldr-muted); font-size: 11px; }
    .ldr-card-body { padding: 20px; }
    .ldr-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; }
    .ldr-span-2 { grid-column: 1 / -1; }
    .ldr-label { display: block; margin-bottom: 7px; color: #344054; font-size: 12px; font-weight: 800; }
    .ldr-required { color: var(--ldr-red); }
    .ldr-hint { display: block; margin-top: 6px; color: var(--ldr-muted); font-size: 11px; line-height: 1.45; }
    .ldr-field { width: 100%; min-height: 43px; padding: 10px 12px; color: #344054; font-size: 13px; background: #fff; border: 1px solid #d0d5dd; border-radius: 8px; outline: none; transition: border-color .15s ease, box-shadow .15s ease; }
    textarea.ldr-field { min-height: 100px; resize: vertical; }
    .ldr-field:focus { border-color: var(--ldr-blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, .11); }
    .ldr-field.is-invalid { border-color: var(--ldr-red); }
    .ldr-invalid { margin-top: 5px; color: var(--ldr-red); font-size: 11px; font-weight: 700; }

    .ldr-choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .ldr-choice { position: relative; display: flex; align-items: flex-start; gap: 10px; min-height: 46px; padding: 11px 12px; color: #344054; font-size: 12px; font-weight: 800; cursor: pointer; background: var(--ldr-soft); border: 1px solid var(--ldr-line); border-radius: 8px; transition: .15s ease; }
    .ldr-choice:hover { border-color: #93c5fd; }
    .ldr-choice.is-selected { color: #1d4ed8; background: var(--ldr-blue-soft); border-color: var(--ldr-blue); }
    .ldr-choice input { margin-top: 2px; accent-color: var(--ldr-blue); }
    .ldr-store-signage { display: none; margin-top: 12px; padding: 14px; background: #fff; border: 1px solid var(--ldr-line); border-radius: 9px; }
    .ldr-store-signage.is-visible { display: block; }
    .ldr-store-signage-layout { display: grid; grid-template-columns: 1fr; gap: 16px; }
    .ldr-store-signage-image { width: 100%; max-height: 260px; object-fit: contain; background: var(--ldr-soft); border: 1px solid var(--ldr-line); border-radius: 8px; }
    .ldr-store-signage-options { display: grid; gap: 9px; }
    .ldr-store-signage-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }

    .ldr-file { padding: 9px; }
    .ldr-file::file-selector-button { margin-right: 10px; padding: 7px 10px; color: #1d4ed8; font-size: 11px; font-weight: 900; cursor: pointer; background: var(--ldr-blue-soft); border: 0; border-radius: 6px; }
    .ldr-preview { display: none; gap: 8px; margin-top: 9px; padding: 10px 12px; background: var(--ldr-soft); border: 1px solid var(--ldr-line); border-radius: 8px; }
    .ldr-preview.is-visible { display: grid; }
    .ldr-preview-item { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .ldr-preview-icon { color: var(--ldr-blue); font-size: 20px; }
    .ldr-preview strong { display: block; max-width: 650px; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ldr-preview span { color: var(--ldr-muted); font-size: 10px; }

    .ldr-actions { display: flex; justify-content: flex-end; padding-top: 3px; }
    .ldr-submit { min-width: 185px; min-height: 46px; padding: 11px 20px; color: #fff; font-size: 13px; font-weight: 900; background: var(--ldr-blue); border: 0; border-radius: 8px; box-shadow: 0 12px 24px rgba(37, 99, 235, .2); }
    .ldr-submit:disabled { cursor: wait; opacity: .68; box-shadow: none; }

    @media (max-width: 640px) {
        .ldr-wrap { padding-top: 12px; }
        .ldr-head { display: block; padding: 17px; }
        .ldr-form-code { display: inline-flex; margin-top: 14px; }
        .ldr-grid, .ldr-choice-grid { grid-template-columns: 1fr; }
        .ldr-store-signage-layout { grid-template-columns: 1fr; }
        .ldr-store-signage-fields { grid-template-columns: 1fr; }
        .ldr-span-2 { grid-column: auto; }
        .ldr-card-body { padding: 16px; }
        .ldr-actions, .ldr-submit { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="ldr-page">
    <main class="ldr-wrap">
        <header class="ldr-head">
            <div>
                <h3>Layout Design Request</h3>
                <div class="mt-1 text-muted">CEO-MBD-FOR-001-000</div>
            </div>
            <a href="{{ route('layout.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Back
            </a>
        </header>

        <form action="{{ route('layout.store') }}" method="POST" enctype="multipart/form-data" id="ldrForm">
            @csrf

            <section class="ldr-card">
                <div class="ldr-card-head">
                    <span class="ldr-step">A</span>
                    <div class="ldr-card-copy">
                        <h2>Request Information</h2>
                    </div>
                </div>
                <div class="ldr-card-body">
                    <div class="ldr-notice">
                        For long-term projects, please allow a minimum of 3–7 working days before the materials are needed.
                    </div>

                    <div class="ldr-grid">
                        <div>
                            <label for="requestorFirstName" class="ldr-label">First Name <span class="ldr-required">*</span></label>
                            <input type="text" name="requestor_first_name" id="requestorFirstName" class="ldr-field {{ $errors->has('requestor_first_name') ? 'is-invalid' : '' }}" value="{{ old('requestor_first_name', $requestorFirstName) }}" autocomplete="given-name" placeholder="Juan" required>
                            @if($errors->has('requestor_first_name'))<div class="ldr-invalid">{{ $errors->first('requestor_first_name') }}</div>@endif
                        </div>
                        <div>
                            <label for="requestorLastName" class="ldr-label">Last Name <span class="ldr-required">*</span></label>
                            <input type="text" name="requestor_last_name" id="requestorLastName" class="ldr-field {{ $errors->has('requestor_last_name') ? 'is-invalid' : '' }}" value="{{ old('requestor_last_name', $requestorLastName) }}" autocomplete="family-name" placeholder="Dela Cruz" required>
                            @if($errors->has('requestor_last_name'))<div class="ldr-invalid">{{ $errors->first('requestor_last_name') }}</div>@endif
                        </div>
                        <div>
                            <label for="department" class="ldr-label">Partner Type <span class="ldr-required">*</span></label>
                            <select name="department" id="department" class="ldr-field {{ $errors->has('department') ? 'is-invalid' : '' }}" required>
                                <option value="">Select partner type</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}" @if(old('department', $requestorPartnerType) === $department) selected @endif>{{ $department }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('department'))<div class="ldr-invalid">{{ $errors->first('department') }}</div>@endif
                        </div>
                        <div>
                            <label for="requestorEmail" class="ldr-label">Email Address <span class="ldr-required">*</span></label>
                            <input type="email" name="requestor_email" id="requestorEmail" class="ldr-field {{ $errors->has('requestor_email') ? 'is-invalid' : '' }}" value="{{ old('requestor_email', $requestorEmail) }}" autocomplete="email" readonly required>
                            <span class="ldr-hint">Email is autofilled with your DMS email.</span>
                            @if($errors->has('requestor_email'))<div class="ldr-invalid">{{ $errors->first('requestor_email') }}</div>@endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head">
                    <span class="ldr-step">B</span>
                    <div class="ldr-card-copy">
                        <h2>Design Details</h2>
                        <span>Describe the output, purpose, and deadline.</span>
                    </div>
                </div>
                <div class="ldr-card-body">
                    <div class="ldr-grid">
                        <div class="ldr-span-2">
                            <label for="mediumType" class="ldr-label">Medium Type <span class="ldr-required">*</span></label>
                            <select name="medium_type" id="mediumType" class="ldr-field {{ $errors->has('medium_type') ? 'is-invalid' : '' }}" required>
                                <option value="">Select medium type</option>
                                @foreach($mediumTypes as $medium)
                                    <option value="{{ $medium }}" @if(old('medium_type') === $medium) selected @endif>{{ $medium }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('medium_type'))<div class="ldr-invalid">{{ $errors->first('medium_type') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <span class="ldr-label">Intended Use <span class="ldr-required">*</span></span>
                            <div class="ldr-choice-grid">
                                @foreach($intendedUses as $use)
                                    <label class="ldr-choice @if(in_array($use, old('intended_use', []))) is-selected @endif">
                                        <input type="checkbox" name="intended_use[]" value="{{ $use }}" @if(in_array($use, old('intended_use', []))) checked @endif>
                                        <span>{{ $use }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if($errors->has('intended_use'))<div class="ldr-invalid">{{ $errors->first('intended_use') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <span class="ldr-label">Marketing Collateral Type <span class="ldr-required">*</span></span>
                            <div class="ldr-choice-grid">
                                @foreach($collateralTypes as $collateral)
                                    <label class="ldr-choice @if(in_array($collateral, old('marketing_collateral_type', []))) is-selected @endif">
                                        <input type="checkbox" name="marketing_collateral_type[]" value="{{ $collateral }}" @if(in_array($collateral, old('marketing_collateral_type', []))) checked @endif>
                                        <span>{{ $collateral }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if($errors->has('marketing_collateral_type'))<div class="ldr-invalid">{{ $errors->first('marketing_collateral_type') }}</div>@endif
                            <div id="storeSignageDetails" class="ldr-store-signage @if(in_array('Store Signage', old('marketing_collateral_type', []))) is-visible @endif">
                                <div class="ldr-store-signage-layout">
                                    <img src="{{ asset('images/store_signage_options.jpg') }}" alt="Store Signage option reference" class="ldr-store-signage-image">
                                    <div>
                                        <span class="ldr-label">Store Signage Option <span class="ldr-required">*</span></span>
                                        <div class="ldr-store-signage-options">
                                            @foreach($storeSignageOptions as $option)
                                                <label class="ldr-choice @if(old('store_signage_option') === $option) is-selected @endif">
                                                    <input type="radio" name="store_signage_option" value="{{ $option }}" @if(old('store_signage_option') === $option) checked @endif>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if($errors->has('store_signage_option'))<div class="ldr-invalid">{{ $errors->first('store_signage_option') }}</div>@endif
                                        <div class="ldr-store-signage-fields">
                                            <div>
                                                <label for="storeSignageBusinessName" class="ldr-label">Business Name <span class="ldr-required">*</span></label>
                                                <input type="text" name="business_name" id="storeSignageBusinessName" class="ldr-field {{ $errors->has('business_name') ? 'is-invalid' : '' }}" value="{{ old('business_name') }}" maxlength="255" placeholder="e.g EMJ Minimart" data-store-signage-field>
                                                @if($errors->has('business_name'))<div class="ldr-invalid">{{ $errors->first('business_name') }}</div>@endif
                                            </div>
                                            <div>
                                                <label for="storeSignageAdTerritory" class="ldr-label">AD Territory <span class="ldr-required">*</span></label>
                                                <input type="text" name="ad_territory" placeholder="e.g Bugallon" id="storeSignageAdTerritory" class="ldr-field {{ $errors->has('ad_territory') ? 'is-invalid' : '' }}" value="{{ old('ad_territory') }}" maxlength="255" data-store-signage-field>
                                                @if($errors->has('ad_territory'))<div class="ldr-invalid">{{ $errors->first('ad_territory') }}</div>@endif
                                            </div>
                                            <div>
                                                <label for="storeSignageBusinessAddress" class="ldr-label">Business Address <span class="ldr-required">*</span></label>
                                                <input type="text" name="business_address" id="storeSignageBusinessAddress" placeholder="Enter your business address" class="ldr-field {{ $errors->has('business_address') ? 'is-invalid' : '' }}" value="{{ old('business_address') }}" maxlength="255" data-store-signage-field>
                                                @if($errors->has('business_address'))<div class="ldr-invalid">{{ $errors->first('business_address') }}</div>@endif
                                            </div>
                                            <div>
                                                <label for="storeSignageContactNumber" class="ldr-label">Contact No. <span class="ldr-required">*</span></label>
                                                <input type="tel" name="contact_number" id="storeSignageContactNumber" placeholder="09xxxxxxxxx" class="ldr-field {{ $errors->has('contact_number') ? 'is-invalid' : '' }}" value="{{ old('contact_number') }}" maxlength="11" inputmode="numeric" pattern="[0-9]{1,11}" autocomplete="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" data-store-signage-field>
                                                @if($errors->has('contact_number'))<div class="ldr-invalid">{{ $errors->first('contact_number') }}</div>@endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ldr-span-2">
                            <label for="projectDescription" class="ldr-label">Project Description <span class="ldr-required">*</span></label>
                            <textarea style="resize: none;" name="project_description" id="projectDescription" class="ldr-field {{ $errors->has('project_description') ? 'is-invalid' : '' }}" placeholder="Include the text, size, quantity, and other important details" required>{{ old('project_description') }}</textarea>
                            @if($errors->has('project_description'))<div class="ldr-invalid">{{ $errors->first('project_description') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <label for="layoutPurpose" class="ldr-label">Purpose of the Layout Request <span class="ldr-required">*</span></label>
                            <textarea style="resize: none;" name="purpose_of_layout_request" id="layoutPurpose" class="ldr-field {{ $errors->has('purpose_of_layout_request') ? 'is-invalid' : '' }}" placeholder="Indicate where and how the layout will be used" required>{{ old('purpose_of_layout_request') }}</textarea>
                            @if($errors->has('purpose_of_layout_request'))<div class="ldr-invalid">{{ $errors->first('purpose_of_layout_request') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <label for="pegSamples" class="ldr-label">Peg Samples <span class="ldr-hint d-inline">(e.g., theme, design, ideas)</span></label>
                            <input type="file" name="peg_samples[]" id="pegSamples" class="ldr-field ldr-file {{ $errors->has('peg_samples') || $errors->has('peg_samples.*') ? 'is-invalid' : '' }}" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.ppt,.pptx" multiple>
                            <span class="ldr-hint">Upload up to 6 files, maximum 30 MB each. Accepted files: images, MP4, PDF, Word, and PowerPoint.</span>
                            <div class="ldr-preview" id="pegPreview" aria-live="polite">
                            </div>
                            @if($errors->has('peg_samples'))<div class="ldr-invalid">{{ $errors->first('peg_samples') }}</div>@endif
                            @if($errors->has('peg_samples.*'))<div class="ldr-invalid">{{ $errors->first('peg_samples.*') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <label for="pegSampleLink" class="ldr-label">Upload Link <span class="ldr-hint d-inline">(optional)</span></label>
                            <input type="url" name="peg_sample_link" id="pegSampleLink" class="ldr-field {{ $errors->has('peg_sample_link') ? 'is-invalid' : '' }}" value="{{ old('peg_sample_link') }}" placeholder="https://drive.google.com/...">
                            <span class="ldr-hint">e.g., Google Drive, Canva. Make sure the MBD can access it.</span>
                            @if($errors->has('peg_sample_link'))<div class="ldr-invalid">{{ $errors->first('peg_sample_link') }}</div>@endif
                        </div>

                        <div class="ldr-span-2">
                            <label for="dateNeeded" class="ldr-label">
                                Preferred Date Needed
                                <span>
                                    <small>(optional)</small>
                                </span>
                            </label>
                            <input type="date" name="date_needed" id="dateNeeded" class="ldr-field {{ $errors->has('date_needed') ? 'is-invalid' : '' }}" value="{{ old('date_needed') }}" min="{{ date('Y-m-d') }}">
                            @if($errors->has('date_needed'))<div class="ldr-invalid">{{ $errors->first('date_needed') }}</div>@endif
                        </div>
                    </div>
                </div>
            </section>

            <div class="ldr-actions">
                <button type="submit" class="ldr-submit" id="ldrSubmit">Submit Request</button>
            </div>
        </form>
    </main>
</div>
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
                    input.closest('.ldr-choice').classList.remove('is-selected');
                });
            }
        }

        document.querySelectorAll('.ldr-choice input').forEach(function (input) {
            input.addEventListener('change', function () {
                if (this.type === 'radio') {
                    document.getElementsByName(this.name).forEach(function (radio) {
                        radio.closest('.ldr-choice').classList.toggle('is-selected', radio.checked);
                    });
                } else {
                    this.closest('.ldr-choice').classList.toggle('is-selected', this.checked);
                }

                if (this === storeSignageCheckbox) {
                    updateStoreSignageVisibility();
                }
            });
        });

        var fileInput = document.getElementById('pegSamples');
        var preview = document.getElementById('pegPreview');
        fileInput.addEventListener('change', function () {
            var files = Array.from(this.files || []);
            preview.replaceChildren();
            preview.classList.toggle('is-visible', files.length > 0);

            files.forEach(function (file) {
                var item = document.createElement('div');
                item.className = 'ldr-preview-item';

                var icon = document.createElement('i');
                icon.className = 'bi bi-paperclip ldr-preview-icon';

                var details = document.createElement('div');
                var name = document.createElement('strong');
                var size = document.createElement('span');
                name.textContent = file.name;
                size.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                details.append(name, size);
                item.append(icon, details);
                preview.append(item);
            });
        });

        updateStoreSignageVisibility();

        document.getElementById('ldrForm').addEventListener('submit', function () {
            var button = document.getElementById('ldrSubmit');
            button.disabled = true;
            button.textContent = 'Submitting…';
        });
    })();
</script>
@endsection
