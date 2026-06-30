@extends('layouts.dashboard')

@section('title', 'Sales / Sell Through')

@section('content')
    <div class="module-header">
        <div>
            <h1>Sales / Sell Through</h1>
            <p>Dealer purchases, refills, item mix, and top outlet contribution.</p>
        </div>
    </div>

    <section class="mini-grid">
        <div class="mini-panel card border-0"><span class="small-muted">Units</span><strong>{{ number_format($dashboard['sales']['units']) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Sales Value</span><strong>P{{ number_format($dashboard['sales']['value'], 2) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Refills</span><strong>{{ number_format($dashboard['sales']['refills']) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Cash Rate</span><strong>{{ $dashboard['sales']['cash_rate'] }}%</strong></div>
    </section>

    <section class="two-col" style="margin-top: 12px;">
        <article class="panel card border-0">
            <h2 class="panel-title">Top Dealers</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Dealer</th><th>Type</th><th>Units</th><th>Sales Value</th></tr></thead>
                    <tbody>
                        @foreach ($topDealers as $dealer)
                            <tr><td>{{ $dealer->dealer }}</td><td>{{ $dealer->outlet_type }}</td><td>{{ number_format($dealer->units) }}</td><td>P{{ number_format($dealer->sales_value, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
        <article class="panel card border-0">
            <h2 class="panel-title">Sales by Item</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Item</th><th>Units</th><th>Sales Value</th></tr></thead>
                    <tbody>
                        @foreach ($salesByItem as $item)
                            <tr><td>{{ $item['item'] }}</td><td>{{ number_format($item['units']) }}</td><td>P{{ number_format($item['sales_value'], 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


