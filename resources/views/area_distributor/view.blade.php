@extends('layouts.header')

@section('css')
<style>
    .welcome {
        margin-top: 20px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #f1f3f5;
    }

    .profile-name {
        font-size: 18px;
        font-weight: 600;
        margin-top: 10px;
        color: #5BC2E7;
    }

    .detail-list p {
        margin-bottom: 10px;
        color: #555;
    }

    .detail-list i {
        color: #5BC2E7;
        margin-right: 8px;
    }

    .info-label {
        color: #6c757d;
        font-size: 12px;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .info-value {
        font-weight: 600;
        margin-bottom: 16px;
    }

    .area-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0 6px 8px 0;
        padding: 8px 10px;
        border-radius: 8px;
        background: #eef7fb;
        color: #176b87;
        font-size: 13px;
        font-weight: 600;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
<section class="welcome">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Area Distributor Details</h5>
        <a href="{{ route('ads') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header" style="padding-bottom: 0px">
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
                        <p><i class="bi bi-telephone"></i>{{ strtoupper($ad->contact_number ?? '-') }}</p>
                        <p><i class="bi bi-envelope"></i>{{ strtoupper($ad->email_address ?? optional($ad->userAds)->email ?? '-') }}</p>
                        <p><i class="bi bi-facebook"></i>{{ strtoupper($ad->facebook ?? '-') }}</p>
                        <p><i class="bi bi-geo-alt"></i>{{ strtoupper($ad->address ?? '-') }}</p>
                        @if(!empty($ad->attachment))
                            <p>
                                <i class="bi bi-paperclip"></i>
                                <a href="{{ asset($ad->attachment) }}" target="_blank" rel="noopener noreferrer">
                                    View attachment
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Business Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Business Name</div>
                            <div class="info-value">{{ $ad->business_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Business Type</div>
                            <div class="info-value">{{ strtoupper($ad->business_type ?? '-') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Region</div>
                            <div class="info-value">{{ strtoupper($ad->location_region ?? '-') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Withholding Tax</div>
                            <div class="info-value">{{ strtoupper($ad->withholding_tax ? 'Enabled' : 'Disabled') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Latitude</div>
                            <div class="info-value">{{ $ad->latitude ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Longitude</div>
                            <div class="info-value">{{ $ad->longitude ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Awarded Areas</h5>
                </div>
                <div class="card-body">
                    @forelse($ad->areas as $area)
                        <span class="area-badge">
                            <i class="bi bi-map"></i>
                            {{-- {{ $area->project_type ?? 'Area' }}: {{ $area->area_name }} --}}
                            {{ $area->area_name }}
                            @if($area->joining_date)
                                ({{ date('M d, Y', strtotime($area->joining_date)) }})
                            @endif
                        </span>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> No awarded areas found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
