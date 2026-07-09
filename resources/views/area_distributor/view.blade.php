@extends('layouts.header')

@section('css')
<style>
    .welcome {
        margin-top: 20px;
    }

    .page-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .page-title {
        margin: 0;
        color: #344054;
        font-size: 18px;
        font-weight: 700;
    }

    .section-card {
        border: 1px solid #e7ebf0;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(16, 24, 40, .04);
    }

    .section-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f5;
        background: #fff;
    }

    .section-card .card-header h5 {
        margin: 0;
        color: #344054;
        font-size: 15px;
        font-weight: 700;
    }

    .section-card .card-body {
        padding: 16px;
    }

    .profile-avatar {
        width: 112px;
        height: 112px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #eef7fb;
    }

    .profile-name {
        font-size: 18px;
        font-weight: 700;
        margin-top: 10px;
        color: #5BC2E7;
    }

    .detail-list {
        display: grid;
        gap: 10px;
    }

    .detail-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #475467;
        line-height: 1.4;
        word-break: break-word;
    }

    .detail-item i {
        color: #5BC2E7;
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .info-item {
        padding: 12px;
        border: 1px solid #eef1f5;
        border-radius: 8px;
        background: #fbfdfe;
    }

    .info-label {
        color: #667085;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .info-value {
        color: #344054;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .area-list {
        display: grid;
        gap: 10px;
    }

    .area-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        border: 1px solid #e8f1f5;
        border-radius: 8px;
        background: #fbfdfe;
    }

    .icon-title {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .icon-box {
        width: 34px;
        height: 34px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: #eef7fb;
        color: #176b87;
    }

    .item-title {
        color: #344054;
        font-weight: 700;
        line-height: 1.2;
        word-break: break-word;
    }

    .item-meta {
        color: #98a2b3;
        font-size: 12px;
        margin-top: 2px;
    }

    .area-date-pill {
        flex: 0 0 auto;
        padding: 6px 9px;
        border-radius: 999px;
        background: #eef7fb;
        color: #176b87;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .empty-state {
        display: grid;
        gap: 6px;
        place-items: center;
        padding: 26px;
        color: #98a2b3;
        text-align: center;
    }

    .empty-state i {
        color: #5BC2E7;
        font-size: 30px;
    }

    .disengagement-card {
        overflow: hidden;
        border: 1px solid #fee4e2;
        border-radius: 8px;
        background: #fff;
    }

    .disengagement-card + .disengagement-card {
        margin-top: 10px;
    }

    .disengagement-date {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #fee4e2;
        background: #fff5f5;
    }

    .disengagement-date-label {
        display: block;
        color: #b42318;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .disengagement-date-value {
        color: #7a271a;
        font-size: 15px;
        font-weight: 800;
    }

    .disengagement-body {
        padding: 12px 14px;
    }

    @media (max-width: 575.98px) {
        .page-toolbar,
        .area-card,
        .disengagement-date {
            align-items: flex-start;
            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .store-picture-card {
        overflow: hidden;
        border: 1px solid #e7ebf0;
        border-radius: 8px;
        background: #fff;
    }

    .store-picture-frame {
        min-height: 240px;
        background: #f8fafc;
    }

    .store-picture-frame img {
        display: block;
        width: 100%;
        height: 250px;
        object-fit: cover;
    }

    .store-picture-empty {
        min-height: 240px;
        display: grid;
        place-items: center;
        padding: 26px;
        color: #98a2b3;
        text-align: center;
    }

    .store-picture-empty i {
        display: block;
        margin-bottom: 8px;
        color: #5BC2E7;
        font-size: 42px;
    }

    .store-picture-caption {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-top: 1px solid #eef1f5;
    }

    .store-picture-caption strong {
        display: block;
        color: #344054;
        font-size: 13px;
    }

    .store-picture-caption small {
        color: #98a2b3;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
<section class="welcome">
    <div class="page-toolbar">
        <h5 class="page-title">Area Distributor Details</h5>
        <a href="{{ route('ads') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card section-card">
                <div class="card-header">
                    <h5>Partner Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img
                            src="{{ $ad->avatar ? asset($ad->avatar) : asset('design/assets/images/profile/user-1.png') }}"
                            alt="Avatar Image"
                            class="profile-avatar mx-auto"
                        >
                        <div class="profile-name">{{ $ad->name }}</div>
                        <div class="text-muted small">{{ $ad->store_code ?? 'No partner code' }}</div>

                        <div class="mt-2">
                            @if($ad->status == 'Active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="detail-list">
                        <div class="detail-item">
                            <i class="bi bi-telephone"></i>
                            <span>{{ strtoupper($ad->contact_number ?? '-') }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-envelope"></i>
                            <span>{{ strtoupper($ad->email_address ?? optional($ad->userAds)->email ?? '-') }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-facebook"></i>
                            <span>{{ strtoupper($ad->facebook ?? '-') }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ strtoupper($ad->address ?? '-') }}</span>
                        </div>
                        @if(!empty($ad->attachment))
                            <div class="detail-item">
                                <i class="bi bi-paperclip"></i>
                                <a href="{{ asset($ad->attachment) }}" target="_blank" rel="noopener noreferrer">
                                    View attachment
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card section-card">
                <div class="card-body">
                    <div class="store-picture-card mt-2">
                        <div class="store-picture-frame">
                            @if(!empty($ad->store_picture))
                                <a href="{{ asset($ad->store_picture) }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ asset($ad->store_picture) }}" alt="{{ $ad->business_name ?: 'Store' }} picture">
                                </a>
                            @else
                                <div class="store-picture-empty">
                                    <div>
                                        <i class="bi bi-shop"></i>
                                        <strong>No store picture uploaded</strong>
                                        <div class="small">Add one from the edit partner modal.</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="store-picture-caption">
                            <div>
                                <strong>Store Picture</strong>
                                <small>{{ $ad->business_name ?: 'Partner storefront' }}</small>
                            </div>
                            @if(!empty($ad->store_picture))
                                <a href="{{ asset($ad->store_picture) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrows-fullscreen"></i> Open
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card section-card">
                <div class="card-header">
                    <h5>Business Details</h5>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Business Name</div>
                            <div class="info-value">{{ $ad->business_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Business Type</div>
                            <div class="info-value">{{ strtoupper($ad->business_type ?? '-') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">TIN</div>
                            <div class="info-value">{{ strtoupper($ad->tin ?? '-') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Region</div>
                            <div class="info-value">{{ strtoupper($ad->location_region ?? '-') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Withholding Tax</div>
                            <div class="info-value">{{ strtoupper($ad->withholding_tax ? 'Enabled' : 'Disabled') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Latitude</div>
                            <div class="info-value">{{ $ad->latitude ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Longitude</div>
                            <div class="info-value">{{ $ad->longitude ?? '-' }}</div>
                        </div>
                    </div>

                    
                </div>
            </div>

            <div class="card section-card mt-3">
                <div class="card-header">
                    <h5>Awarded Areas</h5>
                    <span class="badge badge-info">{{ $ad->areas->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="area-list">
                        @forelse($ad->areas as $area)
                            <div class="area-card">
                                <div class="icon-title">
                                    <span class="icon-box"><i class="bi bi-map"></i></span>
                                    <div>
                                        <div class="item-title">{{ $area->area_name }}</div>
                                        <div class="item-meta">{{ $area->project_type ?? 'Awarded Area' }}</div>
                                    </div>
                                </div>
                                <span class="area-date-pill">
                                    Joined: {{ $area->joining_date ? date('M d, Y', strtotime($area->joining_date)) : 'No date' }}
                                </span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <span>No awarded areas found.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($ad->trashedAreas->isNotEmpty())
                <div class="card section-card mt-3">
                    <div class="card-header">
                        <h5>Disengagement Date</h5>
                        <span class="badge badge-danger">{{ $ad->trashedAreas->count() }}</span>
                    </div>
                    <div class="card-body">
                        @foreach($ad->trashedAreas as $area)
                            <div class="disengagement-card">
                                <div class="disengagement-date">
                                    <div>
                                        <span class="disengagement-date-label">Disengagement Date</span>
                                        <div class="disengagement-date-value">
                                            {{ optional($area->deleted_at)->format('M d, Y') ?? 'No date recorded' }}
                                        </div>
                                    </div>
                                    <span class="badge badge-danger">Disengaged</span>
                                </div>
                                <div class="disengagement-body">
                                    <div class="item-title">{{ $area->area_name }}</div>
                                    <div class="item-meta">
                                        {{ $area->project_type ?? 'Awarded Area' }}
                                        @if($area->joining_date)
                                            &middot; Joined {{ date('M d, Y', strtotime($area->joining_date)) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
