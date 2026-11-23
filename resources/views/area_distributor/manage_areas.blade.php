@php
    $manageBusinessTypes = \App\Services\DistributorProjectAreas::BUSINESS_TYPES;
    $manageMainName = trim((string) $ad->business_name);
    $manageMainType = (string) $ad->business_type;
    $manageOfferRoute = $ad instanceof \App\ProvincialDistributor ? 'pds.areas.offer-sheet' : 'ad.areas.offer-sheet';
@endphp
<div class="modal fade manage-area-modal" id="manageAreaModal-{{ $ad->id }}" tabindex="-1" aria-labelledby="manageAreaTitle-{{ $ad->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable manage-area-dialog">
        <div class="modal-content manage-area-shell">
            <form id="areaForm-{{ $ad->id }}" class="manage-area-form" method="POST" enctype="multipart/form-data"
                  action="{{ route($updateAreasRoute ?? 'update.areas', $ad->id) }}"
                  data-main-name="{{ $manageMainName }}" data-main-type="{{ $manageMainType }}" data-today="{{ now()->format('Y-m-d') }}">
                @csrf
                <div class="modal-header manage-area-header">
                    <div><h4 class="modal-title" id="manageAreaTitle-{{ $ad->id }}">Manage Awarded Areas</h4><p>{{ $ad->name }}</p></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body manage-area-body">
                    <div class="manage-area-errors" role="alert" tabindex="-1" hidden></div>
                    <div class="manage-area-toolbar">
                        <div><strong>Awarded areas <span class="manage-area-count">{{ $ad->areas->count() }}</span></strong><p>Manage business details and documents for each area.</p></div>
                        <button type="button" class="btn btn-outline-primary manage-area-add"><span aria-hidden="true">+</span> Add area</button>
                    </div>
                    <div class="manage-area-main"><span>Main business: <strong>{{ $manageMainName ?: 'Not set' }}</strong></span><span>Business type: <strong>{{ $manageMainType ?: 'Not set' }}</strong></span></div>
                    <div class="manage-area-rows">
                        @foreach($ad->areas as $index => $area)
                            @include('area_distributor.partials.manage-area-row', ['manageIndex' => $index, 'manageArea' => $area])
                        @endforeach
                    </div>
                    <div class="manage-area-empty" {{ $ad->areas->count() ? 'hidden' : '' }}><strong>No awarded areas yet</strong><p>Select Add area to get started.</p></div>
                    <template class="manage-area-template">
                        @include('area_distributor.partials.manage-area-row', ['manageIndex' => '__INDEX__', 'manageArea' => null])
                    </template>
                </div>
                <div class="modal-footer manage-area-footer">
                    <span class="manage-area-save-hint">Changes apply when you save.</span>
                    <div><button type="button" class="btn btn-light manage-area-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success manage-area-save">Save Areas</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if (empty($GLOBALS['manageAreaAssetsRendered'])): ?>
<?php $GLOBALS['manageAreaAssetsRendered'] = true; ?>
@include('area_distributor.partials.manage-area-assets')
<?php endif; ?>
