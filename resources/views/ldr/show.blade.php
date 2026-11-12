@extends('layouts.header')

@section('css')
<style>
    .ldr-show-page {
        --ldr-blue: #2563eb;
        --ldr-ink: #101828;
        --ldr-muted: #667085;
        --ldr-line: #e4e7ec;
        max-width: 1100px;
        margin: 0 auto;
        padding: 22px 0 38px;
    }
    .ldr-show-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
    .ldr-show-back { display: inline-flex; align-items: center; gap: 6px; color: #475467; font-size: 12px; font-weight: 800; text-decoration: none; }
    .ldr-show-back:hover { color: var(--ldr-blue); text-decoration: none; }
    .ldr-show-actions { display: flex; align-items: center; gap: 9px; }
    .ldr-show-status { display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; font-size: 11px; font-weight: 900; border-radius: 999px; }
    .ldr-show-status::before { content: ''; width: 6px; height: 6px; background: currentColor; border-radius: 50%; }
    .ldr-show-status.pending { color: #92400e; background: #fef3c7; }
    .ldr-show-status.approved { color: #1d4ed8; background: #dbeafe; }
    .ldr-show-status.processed { color: #166534; background: #dcfce7; }
    .ldr-show-status.declined { color: #991b1b; background: #fee2e2; }
    .ldr-show-status.cancelled { color: #475467; background: #f2f4f7; }
    .ldr-show-sheet { overflow: hidden; background: #fff; border: 1px solid var(--ldr-line); border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 23, 42, .055); }
    .ldr-show-heading { position: relative; padding: 25px 27px 23px; background: linear-gradient(135deg, #fff, #f7faff); }
    .ldr-show-heading::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 4px; background: var(--ldr-blue); }
    .ldr-show-eyebrow { margin-bottom: 5px; color: var(--ldr-blue); font-size: 10px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
    .ldr-show-heading h1 { margin: 0; color: var(--ldr-ink); font-size: 24px; font-weight: 900; }
    .ldr-show-reference { margin: 7px 0 0; color: var(--ldr-muted); font-size: 12px; }
    .ldr-show-reference strong { color: #344054; }
    .ldr-show-section { padding: 23px 27px 25px; border-top: 1px solid var(--ldr-line); }
    .ldr-show-section-head { display: flex; align-items: center; gap: 10px; margin-bottom: 19px; }
    .ldr-show-section-icon { display: grid; width: 31px; height: 31px; flex: 0 0 31px; color: var(--ldr-blue); background: #eff6ff; border-radius: 8px; place-items: center; }
    .ldr-show-section-head h2 { margin: 0; color: var(--ldr-ink); font-size: 15px; font-weight: 900; }
    .ldr-show-section-head p { margin: 2px 0 0; color: var(--ldr-muted); font-size: 11px; }
    .ldr-show-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); column-gap: 28px; row-gap: 20px; margin: 0; }
    .ldr-show-field { min-width: 0; }
    .ldr-show-field.wide { grid-column: 1 / -1; }
    .ldr-show-field.two { grid-column: span 2; }
    .ldr-show-field dt, .ldr-show-label { display: block; margin: 0 0 5px; color: var(--ldr-muted); font-size: 9px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
    .ldr-show-field dd { margin: 0; color: #344054; font-size: 13px; font-weight: 650; line-height: 1.65; overflow-wrap: anywhere; }
    .ldr-show-copy { color: #344054; font-size: 13px; font-weight: 600; line-height: 1.75; overflow-wrap: anywhere; }
    .ldr-show-copy + .ldr-show-copy-block { margin-top: 18px; }
    .ldr-show-tags { display: flex; flex-wrap: wrap; gap: 7px; }
    .ldr-show-tag { display: inline-flex; padding: 5px 9px; color: #1d4ed8; font-size: 11px; font-weight: 800; background: #eff6ff; border-radius: 999px; }
    .ldr-show-signage { background: #fbfdff; }
    .ldr-output-list { display: grid; gap: 9px; }
    .ldr-output-item { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 11px 12px; background: #f8fafc; border: 1px solid var(--ldr-line); border-radius: 9px; }
    .ldr-output-name { min-width: 0; }
    .ldr-output-name a, .ldr-show-filename { display: block; color: #1d4ed8; font-size: 12px; font-weight: 800; overflow-wrap: anywhere; text-decoration: none; }
    .ldr-output-name a:hover, .ldr-show-filename:hover { text-decoration: underline; }
    .ldr-output-name small { display: block; margin-top: 2px; color: var(--ldr-muted); font-size: 10px; }
    .ldr-show-empty { margin: 0; color: #98a2b3; font-size: 12px; }
    @media (max-width: 760px) {
        .ldr-show-page { padding-top: 13px; }
        .ldr-show-toolbar { align-items: flex-start; }
        .ldr-show-actions { flex-wrap: wrap; justify-content: flex-end; }
        .ldr-show-heading, .ldr-show-section { padding-left: 18px; padding-right: 18px; }
        .ldr-show-grid { grid-template-columns: 1fr; gap: 17px; }
        .ldr-show-field.wide, .ldr-show-field.two { grid-column: auto; }
        .ldr-show-heading h1 { font-size: 21px; }
        .ldr-output-item { align-items: flex-start; flex-direction: column; }
    }
</style>
@endsection

@section('content')
@php
    $status = $layoutRequest['status'] ?? 'Pending';
    $statusClass = strtolower(str_replace(' ', '-', $status));
    $requestorName = trim(($layoutRequest['requestor_first_name'] ?? '') . ' ' . ($layoutRequest['requestor_last_name'] ?? '')) ?: '-';
    $intendedUses = is_array($layoutRequest['intended_use'] ?? null) ? $layoutRequest['intended_use'] : [];
    $originalCollateralTypes = is_array($layoutRequest['marketing_collateral_type'] ?? null) ? $layoutRequest['marketing_collateral_type'] : [];
    $collateralTypes = $originalCollateralTypes;
    $storeSignageOptions = ['Layout file of store signage only', 'Refurbished Store Signage (P5,000)'];
    $storeSignageOption = $layoutRequest['store_signage_option'] ?? null;
    if (!$storeSignageOption) {
        $matchingOptions = array_values(array_intersect($storeSignageOptions, $originalCollateralTypes));
        $storeSignageOption = $matchingOptions[0] ?? null;
    }
    if ($storeSignageOption) {
        $collateralTypes = array_values(array_filter($collateralTypes, function ($value) use ($storeSignageOptions) {
            return $value !== 'Store Signage' && !in_array($value, $storeSignageOptions, true);
        }));
        $collateralTypes[] = $storeSignageOption;
    }
    $hasStoreSignage = in_array('Store Signage', $originalCollateralTypes, true) || $storeSignageOption
        || !empty($layoutRequest['business_name']) || !empty($layoutRequest['ad_territory'])
        || !empty($layoutRequest['business_address']) || !empty($layoutRequest['contact_number']);
    $pegSamples = $layoutRequest['peg_samples'] ?? [];
    $legacyPegSamplePath = $layoutRequest['peg_sample_path'] ?? null;
    $legacyPegSampleUrl = $layoutRequest['peg_sample_url'] ?? null;
    $legacyPegSampleName = $legacyPegSamplePath ? basename(parse_url($legacyPegSamplePath, PHP_URL_PATH)) : null;
    $outputAttachments = $layoutRequest['output_attachments'] ?? [];
@endphp

<main class="ldr-show-page">
    <div class="d-flex mb-3 justify-content-end">
        <a href="{{ route('layout.index') }}" class="ldr-show-back"><i class="ti ti-arrow-left"></i> Back to requests</a>
    </div>

    <article class="ldr-show-sheet">
        <header class="ldr-show-heading">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="ldr-show-eyebrow">Layout Design Request</div>
                    <h1>Request details</h1>
                    <p class="ldr-show-reference">Reference: <strong>{{ $layoutRequest['reference_no'] ?? '-' }}</strong></p>
                </div>

                <div>
                    <span class="ldr-show-status {{ $statusClass }}">{{ $status }}</span>
                </div>
            </div>
        </header>

        <section class="ldr-show-section">
            <div class="ldr-show-section-head">
                <span class="ldr-show-section-icon"><i class="ti ti-user"></i></span>
                <div><h2>Request information</h2><p>Requestor and scheduling details</p></div>
            </div>
            <dl class="ldr-show-grid">
                <div class="ldr-show-field"><dt>Requestor</dt><dd>{{ $requestorName }}</dd></div>
                <div class="ldr-show-field"><dt>Email</dt><dd>{{ $layoutRequest['requestor_email'] ?? '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Partner type</dt><dd>{{ $layoutRequest['department'] ?? '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Submitted</dt><dd>{{ !empty($layoutRequest['submitted_at']) ? \Carbon\Carbon::parse($layoutRequest['submitted_at'])->format('M d, Y') : '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Preferred date needed</dt><dd>{{ !empty($layoutRequest['date_needed']) ? \Carbon\Carbon::parse($layoutRequest['date_needed'])->format('M d, Y') : '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Medium type</dt><dd>{{ $layoutRequest['medium_type'] ?? '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Priority level</dt><dd>{{ $layoutRequest['priority_level'] ?? '-' }}</dd></div>
                <div class="ldr-show-field"><dt>Due date</dt><dd>{{ !empty($layoutRequest['due_date']) ? \Carbon\Carbon::parse($layoutRequest['due_date'])->format('M d, Y') : '-' }}</dd></div>
                @if(!empty($layoutRequest['additional_notes']))
                    <div class="ldr-show-field wide"><dt>Additional notes</dt><dd>{!! nl2br(e($layoutRequest['additional_notes'])) !!}</dd></div>
                @endif
            </dl>
        </section>

        <section class="ldr-show-section">
            <div class="ldr-show-section-head">
                <span class="ldr-show-section-icon"><i class="ti ti-file-description"></i></span>
                <div><h2>Project details</h2><p>Design requirements submitted with the request</p></div>
            </div>
            <dl class="ldr-show-grid">
                <div class="ldr-show-field wide"><dt>Purpose of layout request</dt><dd>{!! nl2br(e($layoutRequest['purpose_of_layout_request'] ?? '-')) !!}</dd></div>
                <div class="ldr-show-field wide"><dt>Project description</dt><dd>{!! nl2br(e($layoutRequest['project_description'] ?? '-')) !!}</dd></div>
                <div class="ldr-show-field">
                    <dt>Intended use</dt>
                    <dd class="ldr-show-tags">@forelse($intendedUses as $use)<span class="ldr-show-tag">{{ $use }}</span>@empty<span>-</span>@endforelse</dd>
                </div>
                <div class="ldr-show-field two">
                    <dt>Marketing collateral type</dt>
                    <dd class="ldr-show-tags">@forelse($collateralTypes as $collateral)<span class="ldr-show-tag">{{ $collateral }}</span>@empty<span>-</span>@endforelse</dd>
                </div>
            </dl>
        </section>

        @if($hasStoreSignage)
            <section class="ldr-show-section ldr-show-signage">
                <div class="ldr-show-section-head">
                    <span class="ldr-show-section-icon"><i class="ti ti-building-store"></i></span>
                    <div><h2>Store signage details</h2><p>Business information for the signage</p></div>
                </div>
                <dl class="ldr-show-grid">
                    <div class="ldr-show-field wide"><dt>Store signage option</dt><dd>{{ $storeSignageOption ?: '-' }}</dd></div>
                    <div class="ldr-show-field"><dt>Business name</dt><dd>{{ $layoutRequest['business_name'] ?? '-' }}</dd></div>
                    <div class="ldr-show-field"><dt>AD territory</dt><dd>{{ $layoutRequest['ad_territory'] ?? '-' }}</dd></div>
                    <div class="ldr-show-field"><dt>Contact number</dt><dd>{{ $layoutRequest['contact_number'] ?? '-' }}</dd></div>
                    <div class="ldr-show-field wide"><dt>Business address</dt><dd>{{ $layoutRequest['business_address'] ?? '-' }}</dd></div>
                </dl>
            </section>
        @endif

        <section class="ldr-show-section">
            <div class="ldr-show-section-head">
                <span class="ldr-show-section-icon"><i class="ti ti-paperclip"></i></span>
                <div><h2>Peg samples</h2><p>Reference files and links</p></div>
            </div>
            @if(!empty($pegSamples))
                <div class="ldr-output-list">
                    @foreach($pegSamples as $attachment)
                        <div class="ldr-output-item">
                            <div class="ldr-output-name">
                                @if(!empty($attachment['url']))<a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer"><i class="ti {{ !empty($attachment['is_link']) ? 'ti-link' : 'ti-paperclip' }}"></i> {{ $attachment['display_name'] ?? 'Peg sample' }}</a>@else<span>{{ $attachment['display_name'] ?? 'Peg sample' }}</span>@endif
                                <small>{{ !empty($attachment['is_link']) ? 'SHARED LINK' : strtoupper(pathinfo($attachment['display_name'] ?? '', PATHINFO_EXTENSION)) }}</small>
                            </div>
                            @if(!empty($attachment['url']))<a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link"></i> Open</a>@endif
                        </div>
                    @endforeach
                </div>
            @elseif($legacyPegSampleUrl)
                <a href="{{ $legacyPegSampleUrl }}" target="_blank" rel="noopener noreferrer" class="ldr-show-filename"><i class="ti ti-paperclip"></i> {{ $legacyPegSampleName }}</a>
            @else
                <p class="ldr-show-empty">No peg samples were attached.</p>
            @endif
        </section>

        @if(strcasecmp($status, 'Processed') === 0 || !empty($layoutRequest['issuance_date']) || !empty($layoutRequest['closing_remarks']) || !empty($outputAttachments))
            <section class="ldr-show-section">
                <div class="ldr-show-section-head">
                    <span class="ldr-show-section-icon"><i class="ti ti-circle-check"></i></span>
                    <div><h2>Issuance details</h2><p>Completed files and final remarks</p></div>
                </div>
                <dl class="ldr-show-grid">
                    <div class="ldr-show-field"><dt>Issuance date</dt><dd>{{ !empty($layoutRequest['issuance_date']) ? \Carbon\Carbon::parse($layoutRequest['issuance_date'])->format('M d, Y') : '-' }}</dd></div>
                    <div class="ldr-show-field two"><dt>Remarks</dt><dd>{!! nl2br(e($layoutRequest['closing_remarks'] ?? '-')) !!}</dd></div>
                    <div class="ldr-show-field wide">
                        <dt>Output attachments</dt>
                        <dd class="ldr-output-list">
                            @forelse($outputAttachments as $attachment)
                                @php
                                    $attachmentName = $attachment['original_name'] ?? basename($attachment['output_attachment_path'] ?? 'Attachment');
                                    $attachmentExtension = strtoupper(pathinfo($attachmentName, PATHINFO_EXTENSION)) ?: 'FILE';
                                    $attachmentBytes = (int) ($attachment['file_size'] ?? 0);
                                    $attachmentSize = $attachmentBytes >= 1048576 ? number_format($attachmentBytes / 1048576, 1) . ' MB' : ($attachmentBytes >= 1024 ? number_format($attachmentBytes / 1024, 1) . ' KB' : ($attachmentBytes > 0 ? $attachmentBytes . ' bytes' : null));
                                @endphp
                                <div class="ldr-output-item">
                                    <div class="ldr-output-name">@if(!empty($attachment['url']))<a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer"><i class="ti ti-paperclip"></i> {{ $attachmentName }}</a>@else<span>{{ $attachmentName }}</span>@endif<small>{{ $attachmentExtension }}{{ $attachmentSize ? ' · ' . $attachmentSize : '' }}</small></div>
                                    @if(!empty($attachment['url']))<a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link"></i> Open file</a>@endif
                                </div>
                            @empty
                                <span class="ldr-show-empty">No output attachments.</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </section>
        @endif
    </article>
</main>
@endsection

@section('javascript')
    @include('ldr.partials.sweet_alerts')
@endsection
