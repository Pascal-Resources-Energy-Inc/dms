@php
    $canSubmitExtension = $canSubmitExtension ?? false;
    $minimumDate = $canSubmitExtension ? max(\Carbon\Carbon::parse($accountability['expected_return_at'])->addDay()->toDateString(), today()->toDateString()) : null;
    $maximumDate = $canSubmitExtension ? \Carbon\Carbon::parse($accountability['released_at'])->addDays(40)->toDateString() : null;
    $extensionRetry = (string) old('extension_accountability_id') === (string) $accountability['id'];
@endphp
<div class="modal fade bmc-extension-modal" id="bmcExtension{{ $accountability['id'] }}" tabindex="-1" aria-labelledby="bmcExtensionTitle{{ $accountability['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content border-0" method="POST" action="{{ route('bmc.extension', $accountability['id']) }}">
            @csrf
            <input type="hidden" name="extension_accountability_id" value="{{ $accountability['id'] }}">
            <div class="modal-header rounded-top" style="background: #3993d1; color: #fff;">
                <div>
                    <h5 class="modal-title text-white" id="bmcExtensionTitle{{ $accountability['id'] }}">Request Extension</h5>
                    <small>{{ $accountability['accountability_ref'] ?? '-' }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body">
                @if($extensionRetry && $errors->any())
                    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <dl>
                    <dt class="text-muted">Reference No.</dt>
                    <dd>{{ $accountability['accountability_ref'] ?? '-' }}</dd>
                    <dt class="text-muted">Current expected return date</dt>
                    <dd>{{ !empty($accountability['expected_return_at']) ? \Carbon\Carbon::parse($accountability['expected_return_at'])->format('M d, Y') : '-' }}</dd>
                </dl>
                @if($canSubmitExtension)
                <div class="form-group mb-3">
                    <label for="bmcExtensionDate{{ $accountability['id'] }}">Requested return date</label>
                    <input type="date" class="form-control" id="bmcExtensionDate{{ $accountability['id'] }}" name="requested_return_date" value="{{ $extensionRetry ? old('requested_return_date') : '' }}" min="{{ $minimumDate }}" max="{{ $maximumDate }}" required>
                    <small class="form-text text-muted">Date can be extended until: {{ \Carbon\Carbon::parse($accountability['released_at'])->addDays(40)->format('M d, Y') }}</small>
                </div>
                <div class="form-group">
                    <label for="bmcExtensionReason{{ $accountability['id'] }}">Reason for extension</label>
                    <textarea class="form-control" style="resize: none;" id="bmcExtensionReason{{ $accountability['id'] }}" name="reason" rows="3" maxlength="2000" placeholder="Specify your reason..." required>{{ $extensionRetry ? old('reason') : '' }}</textarea>
                </div>
                @elseif(collect($accountability['extensions'] ?? [])->contains(function ($extension) { return ($extension['status'] ?? '') === 'pending'; }))
                    <p class="text-muted mb-0">An extension request is awaiting review.</p>
                @endif
                <section class="border rounded mt-3">
                    <h6 class="bg-light border-bottom p-3 mb-0">Extension Request History</h6>
                    <div class="p-3">
                        @forelse($accountability['extensions'] ?? [] as $extension)
                            <div class="@if(!$loop->first) border-top pt-3 mt-3 @endif">
                                <div><strong>Status:</strong> {{ ucfirst($extension['status'] ?? 'Unknown') }}</div>
                                <div><strong>Requested return date:</strong> {{ !empty($extension['requested_return_date']) ? \Carbon\Carbon::parse($extension['requested_return_date'])->format('M d, Y') : '-' }}</div>
                                <div style="white-space: pre-wrap; overflow-wrap: anywhere;"><strong>Reason:</strong> {{ ($extension['reason'] ?? null) ?: '-' }}</div>
                                @if(!empty($extension['remarks']))<div style="white-space: pre-wrap; overflow-wrap: anywhere;"><strong>Review remarks:</strong> {{ $extension['remarks'] }}</div>@endif
                            </div>
                        @empty
                            <span class="text-muted">No extension requests yet.</span>
                        @endforelse
                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                @if($canSubmitExtension)<button type="submit" class="btn btn-primary">Submit request</button>@endif
            </div>
        </form>
    </div>
</div>
