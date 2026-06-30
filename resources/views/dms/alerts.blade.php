@extends('layouts.dashboard')

@section('title', 'Alerts')

@section('content')
    <div class="module-header">
        <div>
            <h1>Alerts</h1>
            <p>Operational exceptions from stock, dealer network, and TimePay data.</p>
        </div>
    </div>

    <section class="two-col">
        <article class="panel card border-0">
            <h2 class="panel-title">Notifications</h2>
            <ul class="alert-list">
                @foreach ($alerts as $alert)
                    <li><span class="dot {{ $alert['tone'] }}">!</span><span>{{ $alert['text'] }}</span><span class="small-muted">{{ $alert['time'] }}</span></li>
                @endforeach
            </ul>
        </article>
        <article class="panel card border-0">
            <h2 class="panel-title">Low Stock Items</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>SKU</th><th>Item</th><th>Qty</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse ($inventory['low_stock_items'] as $item)
                            <tr><td>{{ $item['sku'] }}</td><td>{{ $item['item'] }}</td><td>{{ number_format($item['qty']) }}</td><td><span class="trend-up">Reorder</span></td></tr>
                        @empty
                            <tr><td colspan="4">No low-stock items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


