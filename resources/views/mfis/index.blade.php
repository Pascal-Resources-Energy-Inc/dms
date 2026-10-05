@extends('layouts.header')

@section('css')
<style>
    .mfi-page { display: grid; gap: 18px; }
    .mfi-head { display: flex; align-items: end; justify-content: space-between; gap: 16px; }
    .mfi-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .mfi-copy { margin: 5px 0 0; color: #667085; font-size: 13px; }
    .mfi-panel { overflow: hidden; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .mfi-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .mfi-table { margin: 0; }
    .mfi-table th { padding: 12px 18px; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; background: #f8fafc; border-color: #edf0f5; }
    .mfi-table td { padding: 14px 18px; color: #344054; border-color: #eef1f5; vertical-align: middle; }
    .mfi-name { color: #101828; font-weight: 800; }
    .mfi-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; color: #075985; font-size: 11px; font-weight: 800; background: #e0f2fe; border-radius: 999px; }
    .mfi-actions { display: flex; justify-content: end; gap: 6px; }
    .mfi-actions .btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .mfi-empty { padding: 48px 20px; color: #667085; text-align: center; }
    .mfi-modal .modal-content { border: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 24px 70px rgba(15, 23, 42, .2); }
    .mfi-modal .modal-header { padding: 18px 20px; background: #f8fafc; border-bottom: 1px solid #e4e7ec; }
    .mfi-modal .modal-body { padding: 20px; }
    .mfi-modal .modal-footer { padding: 14px 20px 20px; border-top: 1px solid #e4e7ec; }
    .mfi-modal .form-label { color: #344054; font-size: 12px; font-weight: 800; }
    .mfi-modal .form-control { min-height: 44px; border-color: #d0d5dd; border-radius: 8px; }
    .mfi-modal .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    @media (max-width: 576px) { .mfi-head { align-items: stretch; flex-direction: column; } .mfi-head .btn { min-height: 42px; } .mfi-panel { overflow-x: auto; } .mfi-table { min-width: 590px; } }
</style>
@endsection

@section('content')
<div class="mfi-page">
    <div class="mfi-head">
        <div>
            <h4 class="mfi-title">Microfinance Institutions</h4>
            <p class="mfi-copy">Manage the MFIs available for center assignments and MFI user accounts.</p>
        </div>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#mfiCreateModal"><i class="bi bi-plus-lg me-1"></i> Add MFI</button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-0"><strong>Please check the form.</strong><div>{{ $errors->first() }}</div></div>
    @endif
    @if(session('success')) <div class="alert alert-success mb-0">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger mb-0">{{ session('error') }}</div> @endif

    <section class="mfi-panel">
        <div class="mfi-panel-head"><div><div class="fw-bold text-dark">MFI list</div><div class="text-muted small">{{ number_format($mfis->count()) }} MFI(s) configured</div></div></div>
        <table class="table mfi-table align-middle">
            <thead><tr><th>MFI</th><th>Assigned centers</th><th>MFI accounts</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($mfis as $mfi)
                    <tr>
                        <td><span class="mfi-name">{{ $mfi->name }}</span></td>
                        <td><span class="mfi-badge"><i class="bi bi-diagram-3"></i>{{ number_format($mfi->centers_count) }} center(s)</span></td>
                        <td><span class="mfi-badge"><i class="bi bi-person-badge"></i>{{ number_format($mfi->users_count) }} account(s)</span></td>
                        <td class="text-muted small">{{ optional($mfi->created_at)->format('M d, Y') ?: '—' }}</td>
                        <td><div class="mfi-actions"><button class="btn btn-sm btn-outline-primary" type="button" title="Edit {{ $mfi->name }}" data-bs-toggle="modal" data-bs-target="#mfiEditModal{{ $mfi->id }}"><i class="bi bi-pencil"></i></button><form method="POST" action="{{ route('mfis.destroy', $mfi) }}" onsubmit="return confirm('Delete {{ $mfi->name }}? This cannot be undone.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" title="Delete {{ $mfi->name }}" {{ $mfi->centers_count || $mfi->users_count ? 'disabled' : '' }}><i class="bi bi-trash"></i></button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="mfi-empty">No MFIs have been configured yet. Add one to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>

<div class="modal fade mfi-modal" id="mfiCreateModal" tabindex="-1" aria-labelledby="mfiCreateTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('mfis.store') }}">@csrf<div class="modal-header"><div><h5 class="modal-title mb-1" id="mfiCreateTitle">Add MFI</h5><div class="text-muted small">Use a short, recognizable organization name.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label class="form-label" for="mfiName">MFI name <span class="text-danger">*</span></label><input id="mfiName" class="form-control" name="name" value="{{ old('name') }}" maxlength="100" placeholder="e.g. SEDP" required></div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save MFI</button></div></form></div></div></div>

@foreach($mfis as $mfi)
<div class="modal fade mfi-modal" id="mfiEditModal{{ $mfi->id }}" tabindex="-1" aria-labelledby="mfiEditTitle{{ $mfi->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('mfis.update', $mfi) }}">@csrf @method('PUT')<div class="modal-header"><div><h5 class="modal-title mb-1" id="mfiEditTitle{{ $mfi->id }}">Edit MFI</h5><div class="text-muted small">Renaming updates matching center and MFI-user assignments.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label class="form-label" for="mfiEditName{{ $mfi->id }}">MFI name <span class="text-danger">*</span></label><input id="mfiEditName{{ $mfi->id }}" class="form-control" name="name" value="{{ old('name', $mfi->name) }}" maxlength="100" required></div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div></form></div></div></div>
@endforeach
@endsection

@section('javascript')
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('mfiCreateModal')).show(); });</script>
@endif
@endsection
