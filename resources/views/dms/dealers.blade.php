@extends('layouts.dashboard')

@section('title', 'Dealer Management')

@section('content')
    <div class="module-header">
        <div>
            <h1>Dealer Management</h1>
            <p>Dealer and area distributor master list synced from `admin_crms` and `admin_crms2`.</p>
        </div>
        <form class="search-form form-inline" method="GET" action="{{ route('dms.dealers') }}">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Search dealer, area, city">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <section class="two-col">
        <article class="panel wide">
            <h2 class="panel-title">Dealers</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>Store</th><th>Owner</th><th>Type</th><th>Status</th><th>Area</th><th>City</th><th>Contact</th></tr></thead>
                    <tbody>
                        @forelse ($dealers as $dealer)
                            <tr>
                                <td><span class="status-pill">{{ $dealer->source }}</span></td>
                                <td>{{ $dealer->store_name ?: $dealer->name }}</td>
                                <td>{{ $dealer->name }}</td>
                                <td>{{ $dealer->store_type }}</td>
                                <td>{{ $dealer->status }}</td>
                                <td>{{ $dealer->area }}</td>
                                <td>{{ $dealer->location_city }}</td>
                                <td>{{ $dealer->number ?: $dealer->email_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8">No dealers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel card border-0">
            <h2 class="panel-title">Area Distributors</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>Name</th><th>Business</th><th>Status</th><th>City</th></tr></thead>
                    <tbody>
                        @forelse ($areas as $area)
                            <tr>
                                <td><span class="status-pill">{{ $area->source }}</span></td>
                                <td>{{ $area->name }}</td>
                                <td>{{ $area->business_name }}</td>
                                <td>{{ $area->status }}</td>
                                <td>{{ $area->location_city }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No area distributors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


