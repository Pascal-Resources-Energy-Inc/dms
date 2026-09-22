@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.40.0/tabler-icons.min.css">
<style>
    .aar-page{--aar:#2f6fa4;--aar-dark:#17324d;--aar-muted:#667085;--aar-line:#e4e7ec;display:grid;gap:14px;padding:18px 12px 32px;background:var(--background)}
    .aar-hero,.aar-card,.aar-stat{background:#fff;border:1px solid var(--aar-line);border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.06)}
    .aar-hero{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:18px;border-left:5px solid var(--aar)}
    .aar-eyebrow{margin-bottom:3px;color:var(--aar);font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.aar-hero h3{margin:0;color:var(--aar-dark);font-size:23px;font-weight:900}.aar-hero p{margin:4px 0 0;color:var(--aar-muted);font-size:13px}
    .aar-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.aar-stat{display:flex;align-items:center;gap:12px;padding:14px}.aar-stat i{display:grid;place-items:center;width:42px;height:42px;color:var(--aar);font-size:21px;background:#eef5ff;border-radius:8px}.aar-stat small,.aar-stat strong{display:block}.aar-stat small{color:var(--aar-muted);font-size:11px;font-weight:900;text-transform:uppercase}.aar-stat strong{color:var(--aar-dark);font-size:21px;font-weight:900}
    .aar-filter{padding:14px}.aar-filter label{margin-bottom:5px;color:#344054;font-size:12px;font-weight:800}.aar-filter .form-control{min-height:39px;border-color:#d0d5dd;border-radius:7px;font-size:13px}.aar-filter .form-control:focus{border-color:var(--aar);box-shadow:0 0 0 .18rem rgba(47,111,164,.13)}.aar-filter-actions{display:flex;gap:8px}.aar-filter-actions .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:39px;font-weight:800}
    .aar-report{overflow:hidden}.aar-report-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 16px;border-bottom:1px solid var(--aar-line)}.aar-report-head h4{margin:0;color:var(--aar-dark);font-size:16px;font-weight:900}.aar-report-head p{margin:3px 0 0;color:var(--aar-muted);font-size:12px}.aar-count{padding:5px 9px;color:#075985;font-size:11px;font-weight:900;background:#e0f2fe;border-radius:999px;white-space:nowrap}
    .aar-table-wrap{overflow:auto}.aar-table{width:100%;min-width:1550px;margin:0;border-collapse:collapse;font-size:12px}.aar-table th{padding:10px 9px;color:#475467;font-size:10px;font-weight:900;letter-spacing:.04em;text-transform:uppercase;background:#f8fafc;border-bottom:1px solid var(--aar-line);white-space:nowrap}.aar-table td{padding:10px 9px;color:#344054;border-bottom:1px solid #eef1f4;vertical-align:middle}.aar-table tbody tr:hover{background:#f8fbff}.aar-code{color:#1f5c91;font-weight:900;white-space:nowrap}.aar-partner{color:#17324d;font-weight:800}.aar-area{min-width:225px;color:#17324d;font-weight:800}.aar-meta{display:block;margin-top:3px;color:#667085;font-size:10px;font-weight:700}.aar-address{min-width:175px;white-space:normal}.aar-status{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:10px;font-weight:900}.aar-status.active{color:#166534;background:#dcfce7}.aar-status.disengaged{color:#991b1b;background:#fee2e2}.aar-empty{padding:42px!important;color:var(--aar-muted)!important;text-align:center}.aar-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px}.aar-pagination .pagination{margin:0}.aar-pagination nav{overflow:auto}.aar-mobile{display:none}
    @media(max-width:991px){.aar-table{min-width:1300px}.aar-hero{align-items:flex-start;flex-direction:column}.aar-stats{grid-template-columns:1fr 1fr}}
    @media(max-width:767px){.aar-page{padding:12px 8px 24px}.aar-hero{padding:15px}.aar-hero h3{font-size:20px}.aar-stats{grid-template-columns:1fr}.aar-filter-actions{margin-top:6px}.aar-filter-actions .btn{flex:1}.aar-table-wrap{display:none}.aar-mobile{display:grid;gap:10px;padding:12px;background:#f8fafc}.aar-mobile-card{padding:14px;background:#fff;border:1px solid var(--aar-line);border-radius:9px;box-shadow:0 2px 7px rgba(15,23,42,.04)}.aar-mobile-title{display:flex;justify-content:space-between;gap:8px;margin-bottom:10px}.aar-mobile-title strong{color:var(--aar-dark);font-size:14px}.aar-mobile-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.aar-mobile-grid div{min-width:0}.aar-mobile-grid small{display:block;margin-bottom:2px;color:var(--aar-muted);font-size:9px;font-weight:900;text-transform:uppercase}.aar-mobile-grid span{display:block;overflow-wrap:anywhere;color:#344054;font-size:12px;font-weight:700}.aar-mobile-wide{grid-column:1/-1}.aar-pagination{align-items:flex-start;flex-direction:column}.aar-pagination nav{width:100%}}
</style>
@endsection

@section('content')
@php
    $awardGroups = $awards->getCollection()->groupBy('partner_code');
@endphp
<div class="container-fluid aar-page">
    <section class="aar-hero">
        <div><div class="aar-eyebrow">Reports</div><h3>AD Awarded Areas Report</h3><p>Track every awarded-area assignment, partner detail, and disengagement status.</p></div>
    </section>

    <section class="aar-stats">
        <article class="aar-stat"><i class="ti ti-map-2"></i><div><small>Assignments</small><strong>{{ number_format($summary['assignments']) }}</strong></div></article>
        <article class="aar-stat"><i class="ti ti-users"></i><div><small>Partners</small><strong>{{ number_format($summary['partners']) }}</strong></div></article>
        <article class="aar-stat"><i class="ti ti-user-off"></i><div><small>Disengaged</small><strong>{{ number_format($summary['disengaged']) }}</strong></div></article>
    </section>

    <section class="aar-card aar-filter">
        <form method="GET" action="{{ route('ad-awarded-areas') }}">
            <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-2"><label for="award-search">Search</label><input id="award-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Partner code, partner, business, or awarded area"></div>
                <div class="col-lg-2 col-md-3 mb-2"><label for="award-project">AD package</label><select id="award-project" class="form-control" name="project_type"><option value="">All packages</option>@foreach($projectTypes as $type)<option value="{{ $type }}" {{ $projectType === $type ? 'selected' : '' }}>{{ $type }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-3 mb-2"><label for="award-status">Status</label><select id="award-status" class="form-control" name="assignment_status"><option value="all" {{ $assignmentStatus === 'all' ? 'selected' : '' }}>All assignments</option><option value="active" {{ $assignmentStatus === 'active' ? 'selected' : '' }}>Active</option><option value="disengaged" {{ $assignmentStatus === 'disengaged' ? 'selected' : '' }}>Disengaged</option></select></div>
                <div class="col-lg-2 col-md-3 mb-2"><label for="award-per-page">Rows per page</label><select id="award-per-page" class="form-control" name="per_page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-9 mb-2"><div class="aar-filter-actions"><button class="btn btn-primary" type="submit"><i class="ti ti-filter"></i>Apply</button><a class="btn btn-outline-secondary" href="{{ route('ad-awarded-areas') }}"><i class="ti ti-refresh"></i>Reset</a></div></div>
            </div>
        </form>
    </section>

    <section class="aar-card aar-report">
        <div class="aar-report-head"><div><h4>Awarded-area assignments</h4><p>Includes active and disengaged awards; a soft-deleted award displays its Disengagement Date.</p></div><span class="aar-count">{{ number_format($awards->total()) }} results</span></div>
        <div class="aar-table-wrap"><table class="aar-table"><thead><tr><th>Partner Code</th><th>Date Started</th><th>Partner Name</th><th>Business / Store Name</th><th>Awarded Area #</th><th>Awarded Area</th><th>Province</th><th>Region</th><th>PH Area</th><th>Delivery Address</th><th>Contact #</th><th>AD Package</th><th>Disengagement Date</th></tr></thead><tbody>
            @forelse($awardGroups as $partnerCode => $partnerAwards)
                @foreach($partnerAwards->values() as $awardIndex => $award)
                    <tr>
                        @if($awardIndex === 0)
                            <td rowspan="{{ $partnerAwards->count() }}"><span class="aar-code">{{ $partnerCode }}</span><span class="aar-meta">{{ number_format($partnerAwards->count()) }} awarded {{ $partnerAwards->count() === 1 ? 'area' : 'areas' }}</span></td>
                            <td rowspan="{{ $partnerAwards->count() }}">{{ $award->partner_started_display }}</td>
                            <td rowspan="{{ $partnerAwards->count() }}" class="aar-partner">{{ $award->partner_name }}</td>
                            <td rowspan="{{ $partnerAwards->count() }}">{{ $award->business_name }}</td>
                        @endif
                        <td><span class="aar-code">{{ $award->award_reference }}</span></td><td class="aar-area">{{ $award->area_name }}<span class="aar-meta">{{ $award->awarded_date ? 'Awarded: ' . $award->awarded_date_display : 'No award date' }}</span></td><td>{{ $award->province }}</td><td>{{ $award->region }}</td><td>{{ $award->ph_area }}</td>
                        @if($awardIndex === 0)
                            <td rowspan="{{ $partnerAwards->count() }}" class="aar-address">{{ $award->delivery_address }}</td>
                            <td rowspan="{{ $partnerAwards->count() }}">{{ $award->contact_number }}</td>
                        @endif
                        <td>{{ $award->project_type }}</td><td>@if($award->disengagement_date)<span class="aar-status disengaged">{{ $award->disengagement_date_display }}</span>@else<span class="aar-status active">Active</span>@endif</td>
                    </tr>
                @endforeach
            @empty<tr><td colspan="13" class="aar-empty">No awarded-area assignments match the selected filters.</td></tr>@endforelse
        </tbody></table></div>
        <div class="aar-mobile">@forelse($awards as $award)<article class="aar-mobile-card"><div class="aar-mobile-title"><strong>{{ $award->partner_name }}</strong><span class="aar-status {{ $award->disengagement_date ? 'disengaged' : 'active' }}">{{ $award->disengagement_date ? 'Disengaged' : 'Active' }}</span></div><div class="aar-mobile-grid"><div><small>Partner code</small><span>{{ $award->partner_code }}</span></div><div><small>Awarded area #</small><span>{{ $award->award_reference }}</span></div><div class="aar-mobile-wide"><small>Awarded area</small><span>{{ $award->area_name }}</span></div><div><small>Date started</small><span>{{ $award->partner_started_display }}</span></div><div><small>AD package</small><span>{{ $award->project_type }}</span></div><div><small>Location</small><span>{{ $award->province }} · {{ $award->region }}</span></div><div><small>PH area</small><span>{{ $award->ph_area }}</span></div><div class="aar-mobile-wide"><small>Business / contact</small><span>{{ $award->business_name }} · {{ $award->contact_number }}</span></div></div></article>@empty<div class="aar-empty">No awarded-area assignments match the selected filters.</div>@endforelse</div>
        @if($awards->hasPages())<div class="aar-pagination"><small class="text-muted">Showing {{ number_format($awards->firstItem()) }}–{{ number_format($awards->lastItem()) }} of {{ number_format($awards->total()) }}</small>{{ $awards->links() }}</div>@endif
    </section>
</div>
@endsection
