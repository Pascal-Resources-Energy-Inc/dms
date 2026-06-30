@extends('layouts.dashboard')

@section('title', 'Outlet Performance')

@section('content')
    <div class="module-header">
        <div>
            <h1>Outlet Performance</h1>
            <p>Channel productivity by outlet type and top dealer contribution.</p>
        </div>
    </div>

    <section class="two-col">
        <article class="panel card border-0">
            <h2 class="panel-title">Outlet Type Breakdown</h2>
            <div class="mini-grid">
                @foreach ($dealerTypes as $type => $count)
                    <div class="mini-panel card border-0"><span class="small-muted">{{ $type }}</span><strong>{{ number_format($count) }}</strong></div>
                @endforeach
            </div>
        </article>
        <article class="panel card border-0">
            <h2 class="panel-title">Top Outlet Performance</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Dealer</th><th>Type</th><th>Units</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach ($topDealers as $dealer)
                            <tr><td>{{ $dealer->dealer }}</td><td>{{ $dealer->outlet_type }}</td><td>{{ number_format($dealer->units) }}</td><td>P{{ number_format($dealer->sales_value, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


