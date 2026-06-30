@extends('layouts.dashboard')

@section('title', 'Promotions')

@section('content')
    <div class="module-header">
        <div>
            <h1>Promotions</h1>
            <p>Rewards and raffle campaign records found in the CRM databases.</p>
        </div>
    </div>

    <section class="two-col">
        <article class="panel card border-0">
            <h2 class="panel-title">Rewards</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>Reward</th><th>Points</th><th>Stock</th><th>Expiry</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($rewards as $reward)
                            <tr>
                                <td><span class="status-pill">{{ $reward->source }}</span></td>
                                <td>{{ $reward->description ?? '' }}</td>
                                <td>{{ $reward->points_required ?? '' }}</td>
                                <td>{{ $reward->stock ?? '' }}</td>
                                <td>{{ $reward->expiry_date ?? '' }}</td>
                                <td>{{ isset($reward->is_active) && $reward->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No rewards found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
        <article class="panel card border-0">
            <h2 class="panel-title">Raffles</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>Title</th><th>Prize</th><th>Status</th><th>Start</th><th>End</th></tr></thead>
                    <tbody>
                        @forelse ($raffles as $raffle)
                            <tr>
                                <td><span class="status-pill">{{ $raffle->source }}</span></td>
                                <td>{{ $raffle->title ?? '' }}</td>
                                <td>{{ $raffle->prize ?? '' }}</td>
                                <td>{{ $raffle->status ?? '' }}</td>
                                <td>{{ $raffle->starts_at ?? '' }}</td>
                                <td>{{ $raffle->ends_at ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No raffles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


