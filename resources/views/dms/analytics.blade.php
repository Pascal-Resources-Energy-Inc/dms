@extends('layouts.dashboard')

@section('title', 'Analytics')

@section('content')
    <div class="module-header">
        <div>
            <h1>Analytics</h1>
            <p>Performance trends, sales mix, dealer productivity, and TimePay operating signals.</p>
        </div>
    </div>

    <section class="kpi-grid">
        @foreach ($dashboard['kpis'] as $kpi)
            <article class="kpi card border-0">
                <div class="kpi-head">
                    <span class="icon tone-{{ $kpi['tone'] }}">{{ strtoupper(substr($kpi['icon'], 0, 1)) }}</span>
                    <div><p class="kpi-value">{{ $kpi['value'] }}</p><div class="kpi-label">{{ $kpi['label'] }}</div></div>
                </div>
                <div class="trend-up">{{ $kpi['trend'] }}</div>
                <div class="kpi-sub">{{ $kpi['sub'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="two-col" style="margin-top: 12px;">
        <article class="panel card border-0">
            <h2 class="panel-title">Purchases vs Refills Trend</h2>
            @include('dashboards.partials.trend-chart', ['trend' => $dashboard['trend']])
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


