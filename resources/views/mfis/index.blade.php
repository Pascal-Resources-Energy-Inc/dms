@extends('layouts.header')

@section('css')
<style>
    .mfi-page { display: grid; gap: 18px; }
    .mfi-head { display: flex; align-items: end; justify-content: space-between; gap: 16px; }
    .mfi-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .mfi-copy { margin: 5px 0 0; color: #667085; font-size: 13px; }
    .mfi-add-button { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 9px 14px; border: 0; border-radius: 9px; background: linear-gradient(135deg, #0877ca, #075da7); box-shadow: 0 8px 18px rgba(8, 119, 202, .2); font-weight: 800; }

    .mfi-panel { overflow: hidden; background: #fff; border: 1px solid #e4e7ec; border-radius: 13px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .mfi-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .mfi-table-wrap { overflow-x: auto; }
    .mfi-table { min-width: 790px; margin: 0; }
    .mfi-table th { padding: 12px 18px; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; background: #f8fafc; border-color: #edf0f5; }
    .mfi-table td { padding: 14px 18px; color: #344054; border-color: #eef1f5; vertical-align: middle; }
    .mfi-table tbody tr { transition: background-color .18s ease; }
    .mfi-table tbody tr:hover { background: #fbfdff; }
    .mfi-name { color: #101828; font-weight: 850; }
    .mfi-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; color: #075985; font-size: 11px; font-weight: 800; white-space: nowrap; background: #e0f2fe; border-radius: 999px; }
    .mfi-visibility-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px; border: 1px solid; border-radius: 999px; font-size: 10px; font-weight: 850; white-space: nowrap; }
    .mfi-visibility-badge.is-visible { color: #176b47; border-color: #bcebd6; background: #effbf5; }
    .mfi-visibility-badge.is-private { color: #475467; border-color: #dce3e9; background: #f8fafc; }
    .mfi-actions { display: flex; justify-content: end; gap: 6px; }
    .mfi-actions .btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    .mfi-empty { padding: 48px 20px !important; color: #667085 !important; text-align: center; }

    .mfi-modal .modal-dialog { max-width: 590px; }
    .mfi-modal .modal-content { overflow: hidden; border: 0; border-radius: 16px; box-shadow: 0 26px 80px rgba(15, 23, 42, .22); }
    .mfi-modal .modal-header { align-items: flex-start; padding: 20px 22px; border-bottom: 1px solid #e4e7ec; background: linear-gradient(180deg, #fbfdff, #f7fafc); }
    .mfi-modal-title-row { display: flex; align-items: center; gap: 12px; }
    .mfi-modal-icon { width: 42px; height: 42px; display: grid; flex: 0 0 auto; place-items: center; color: #0877ca; border: 1px solid #cce8f7; border-radius: 12px; background: #edf9ff; font-size: 18px; }
    .mfi-modal .modal-title { color: #101828; font-size: 18px; font-weight: 900; }
    .mfi-modal-subtitle { margin-top: 2px; color: #667085; font-size: 11px; line-height: 1.45; }
    .mfi-modal .modal-body { display: grid; gap: 20px; padding: 22px; }
    .mfi-modal .modal-footer { gap: 8px; padding: 15px 22px 20px; border-top: 1px solid #e4e7ec; }
    .mfi-modal .modal-footer .btn { min-width: 105px; min-height: 40px; border-radius: 9px; font-size: 12px; font-weight: 800; }
    .mfi-modal .form-label { margin-bottom: 7px; color: #344054; font-size: 12px; font-weight: 850; }
    .mfi-modal .form-control { min-height: 46px; padding: 10px 13px; border-color: #d0d5dd; border-radius: 10px; font-size: 13px; font-weight: 650; }
    .mfi-modal .form-control:focus { border-color: #0877ca; box-shadow: 0 0 0 4px rgba(8, 119, 202, .1); }
    .mfi-field-help { display: block; margin-top: 6px; color: #8492a0; font-size: 10px; line-height: 1.45; }

    .mfi-privacy-fieldset { min-width: 0; margin: 0; padding: 0; border: 0; }
    .mfi-privacy-legend { width: 100%; margin: 0; padding: 0; color: #344054; font-size: 12px; font-weight: 850; }
    .mfi-required { color: #d92d20; }
    .mfi-privacy-copy { margin: 5px 0 12px; color: #667085; font-size: 11px; line-height: 1.5; }
    .mfi-choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .mfi-choice { position: relative; min-width: 0; margin: 0; cursor: pointer; }
    .mfi-choice input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .mfi-choice-card { position: relative; min-height: 96px; display: grid; grid-template-columns: 40px minmax(0, 1fr); align-items: center; gap: 11px; padding: 14px; border: 1.5px solid #dce5ec; border-radius: 12px; background: #fff; transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease; }
    .mfi-choice:hover .mfi-choice-card { transform: translateY(-1px); border-color: #a9d6ed; background: #fbfdff; }
    .mfi-choice input:focus-visible + .mfi-choice-card { outline: 3px solid rgba(8, 119, 202, .16); outline-offset: 2px; }
    .mfi-choice-icon { width: 40px; height: 40px; display: grid; place-items: center; color: #667085; border-radius: 11px; background: #f2f4f7; font-size: 17px; }
    .mfi-choice-copy strong, .mfi-choice-copy small { display: block; }
    .mfi-choice-copy strong { color: #263b4d; font-size: 13px; font-weight: 900; }
    .mfi-choice-copy small { margin-top: 3px; color: #7b8b98; font-size: 10px; line-height: 1.4; }
    .mfi-choice-check { position: absolute; top: 8px; right: 8px; width: 19px; height: 19px; display: grid; place-items: center; color: #fff; border-radius: 50%; background: #0877ca; font-size: 10px; opacity: 0; transform: scale(.72); transition: opacity .18s ease, transform .18s ease; }
    .mfi-choice input:checked + .mfi-choice-card { border-color: #0877ca; background: #f0f9ff; box-shadow: 0 8px 18px rgba(8, 119, 202, .11); }
    .mfi-choice input:checked + .mfi-choice-card .mfi-choice-icon { color: #fff; background: #0877ca; }
    .mfi-choice input:checked + .mfi-choice-card .mfi-choice-check { opacity: 1; transform: scale(1); }
    .mfi-choice.is-no input:checked + .mfi-choice-card { border-color: #667085; background: #f8fafc; box-shadow: 0 8px 18px rgba(71, 84, 103, .09); }
    .mfi-choice.is-no input:checked + .mfi-choice-card .mfi-choice-icon,
    .mfi-choice.is-no input:checked + .mfi-choice-card .mfi-choice-check { background: #667085; }

    @media (max-width: 576px) {
        .mfi-head { align-items: stretch; flex-direction: column; }
        .mfi-add-button { width: 100%; }
        .mfi-panel { border-radius: 11px; }
        .mfi-modal .modal-dialog { margin: 10px; }
        .mfi-modal .modal-header, .mfi-modal .modal-body, .mfi-modal .modal-footer { padding-left: 16px; padding-right: 16px; }
        .mfi-choice-grid { grid-template-columns: 1fr; }
        .mfi-choice-card { min-height: 82px; }
        .mfi-modal .modal-footer { flex-wrap: nowrap; }
        .mfi-modal .modal-footer .btn { flex: 1; min-width: 0; }
    }
</style>
@endsection

@section('content')
@php
    $createVisibility = (string) old('show_customer_full_name', '0');
@endphp

<div class="mfi-page">
    <div class="mfi-head">
        <div>
            <h4 class="mfi-title">Microfinance Institutions</h4>
            <p class="mfi-copy">Manage MFI assignments and control whether customer full names are visible.</p>
        </div>
        <button class="btn btn-primary btn-sm mfi-add-button" type="button" data-bs-toggle="modal" data-bs-target="#mfiCreateModal">
            <i class="bi bi-plus-lg"></i> Add MFI
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-0"><strong>Please check the form.</strong><div>{{ $errors->first() }}</div></div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mb-0"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}</div>
    @endif

    <section class="mfi-panel">
        <div class="mfi-panel-head">
            <div>
                <div class="fw-bold text-dark">MFI list</div>
                <div class="text-muted small">{{ number_format($mfis->count()) }} MFI(s) configured</div>
            </div>
        </div>

        <div class="mfi-table-wrap">
            <table class="table mfi-table align-middle">
                <thead>
                    <tr>
                        <th>MFI</th>
                        <th>Assigned centers</th>
                        <th>MFI accounts</th>
                        <th>Customer full name</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mfis as $mfi)
                        <tr>
                            <td><span class="mfi-name">{{ $mfi->name }}</span></td>
                            <td><span class="mfi-badge"><i class="bi bi-diagram-3"></i>{{ number_format($mfi->centers_count) }} center(s)</span></td>
                            <td><span class="mfi-badge"><i class="bi bi-person-badge"></i>{{ number_format($mfi->users_count) }} account(s)</span></td>
                            <td>
                                @if($mfi->show_customer_full_name)
                                    <span class="mfi-visibility-badge is-visible"><i class="bi bi-eye"></i>Visible</span>
                                @else
                                    <span class="mfi-visibility-badge is-private"><i class="bi bi-eye-slash"></i>Hidden</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ optional($mfi->created_at)->format('M. d, Y') ?: '—' }}</td>
                            <td>
                                <div class="mfi-actions">
                                    <button class="btn btn-sm btn-outline-primary" type="button" title="Edit {{ $mfi->name }}" data-bs-toggle="modal" data-bs-target="#mfiEditModal{{ $mfi->id }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('mfis.destroy', $mfi) }}" onsubmit="return confirm('Delete {{ $mfi->name }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete {{ $mfi->name }}" {{ $mfi->centers_count || $mfi->users_count ? 'disabled' : '' }}><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mfi-empty">No MFIs have been configured yet. Add one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade mfi-modal" id="mfiCreateModal" tabindex="-1" aria-labelledby="mfiCreateTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('mfis.store') }}">
                @csrf
                <input type="hidden" name="form_context" value="create">

                <div class="modal-header">
                    <div class="mfi-modal-title-row">
                        <span class="mfi-modal-icon"><i class="bi bi-bank"></i></span>
                        <div>
                            <h5 class="modal-title mb-0" id="mfiCreateTitle">Add MFI</h5>
                            <div class="mfi-modal-subtitle">Set up the organization and its customer-name privacy.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div>
                        <label class="form-label" for="mfiName">MFI name <span class="mfi-required">*</span></label>
                        <input id="mfiName" class="form-control{{ $errors->has('name') && old('form_context') === 'create' ? ' is-invalid' : '' }}" name="name" value="{{ old('form_context') === 'create' ? old('name') : '' }}" maxlength="100" placeholder="e.g. SEDP" autocomplete="organization" required>
                        <small class="mfi-field-help">Use a short, recognizable organization name.</small>
                    </div>

                    <fieldset class="mfi-privacy-fieldset">
                        <legend class="mfi-privacy-legend">Show customers’ full names? <span class="mfi-required">*</span></legend>
                        <p class="mfi-privacy-copy">Choose what MFI accounts assigned to this organization are allowed to see.</p>
                        <div class="mfi-choice-grid">
                            <label class="mfi-choice is-yes">
                                <input type="radio" name="show_customer_full_name" value="1" {{ $createVisibility === '1' ? 'checked' : '' }} required>
                                <span class="mfi-choice-card">
                                    <span class="mfi-choice-icon"><i class="bi bi-eye"></i></span>
                                    <span class="mfi-choice-copy"><strong>Yes, show names</strong><small>Display each customer’s complete name.</small></span>
                                    <span class="mfi-choice-check"><i class="bi bi-check-lg"></i></span>
                                </span>
                            </label>
                            <label class="mfi-choice is-no">
                                <input type="radio" name="show_customer_full_name" value="0" {{ $createVisibility !== '1' ? 'checked' : '' }} required>
                                <span class="mfi-choice-card">
                                    <span class="mfi-choice-icon"><i class="bi bi-eye-slash"></i></span>
                                    <span class="mfi-choice-copy"><strong>No, keep private</strong><small>Hide full customer names from MFI accounts.</small></span>
                                    <span class="mfi-choice-check"><i class="bi bi-check-lg"></i></span>
                                </span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Save MFI</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($mfis as $mfi)
    @php
        $editingThisMfi = old('form_context') === 'edit' && (int) old('mfi_id') === (int) $mfi->id;
        $editName = $editingThisMfi ? old('name') : $mfi->name;
        $editVisibility = (string) ($editingThisMfi ? old('show_customer_full_name', '0') : ($mfi->show_customer_full_name ? '1' : '0'));
    @endphp
    <div class="modal fade mfi-modal" id="mfiEditModal{{ $mfi->id }}" tabindex="-1" aria-labelledby="mfiEditTitle{{ $mfi->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('mfis.update', $mfi) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="mfi_id" value="{{ $mfi->id }}">

                    <div class="modal-header">
                        <div class="mfi-modal-title-row">
                            <span class="mfi-modal-icon"><i class="bi bi-pencil-square"></i></span>
                            <div>
                                <h5 class="modal-title mb-0" id="mfiEditTitle{{ $mfi->id }}">Edit {{ $mfi->name }}</h5>
                                <div class="mfi-modal-subtitle">Update the organization and customer-name privacy.</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div>
                            <label class="form-label" for="mfiEditName{{ $mfi->id }}">MFI name <span class="mfi-required">*</span></label>
                            <input id="mfiEditName{{ $mfi->id }}" class="form-control{{ $errors->has('name') && $editingThisMfi ? ' is-invalid' : '' }}" name="name" value="{{ $editName }}" maxlength="100" autocomplete="organization" required>
                            <small class="mfi-field-help">Renaming updates matching center and MFI-user assignments.</small>
                        </div>

                        <fieldset class="mfi-privacy-fieldset">
                            <legend class="mfi-privacy-legend">Show customers’ full names? <span class="mfi-required">*</span></legend>
                            <p class="mfi-privacy-copy">Choose what MFI accounts assigned to {{ $mfi->name }} are allowed to see.</p>
                            <div class="mfi-choice-grid">
                                <label class="mfi-choice is-yes">
                                    <input type="radio" name="show_customer_full_name" value="1" {{ $editVisibility === '1' ? 'checked' : '' }} required>
                                    <span class="mfi-choice-card">
                                        <span class="mfi-choice-icon"><i class="bi bi-eye"></i></span>
                                        <span class="mfi-choice-copy"><strong>Yes, show names</strong><small>Display each customer’s complete name.</small></span>
                                        <span class="mfi-choice-check"><i class="bi bi-check-lg"></i></span>
                                    </span>
                                </label>
                                <label class="mfi-choice is-no">
                                    <input type="radio" name="show_customer_full_name" value="0" {{ $editVisibility !== '1' ? 'checked' : '' }} required>
                                    <span class="mfi-choice-card">
                                        <span class="mfi-choice-icon"><i class="bi bi-eye-slash"></i></span>
                                        <span class="mfi-choice-copy"><strong>No, keep private</strong><small>Hide full customer names from MFI accounts.</small></span>
                                        <span class="mfi-choice-check"><i class="bi bi-check-lg"></i></span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('javascript')
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const context = @json(old('form_context', 'create'));
    const mfiId = @json(old('mfi_id'));
    const modalId = context === 'edit' && mfiId ? 'mfiEditModal' + mfiId : 'mfiCreateModal';
    const modalElement = document.getElementById(modalId);
    if (modalElement) new bootstrap.Modal(modalElement).show();
});
</script>
@endif
@endsection
