@extends('layouts.dashboard')

@section('title', 'Inventory')

@section('content')
    <div class="module-header">
        <div>
            <h1>Inventory</h1>
            <p>Products, prices, stock movement, and reorder visibility from both CRM databases.</p>
        </div>
        <form class="search-form form-inline" method="GET" action="{{ route('dms.inventory') }}">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Search SKU, item, reference">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <section class="mini-grid">
        <div class="mini-panel card border-0"><span class="small-muted">Total Stock</span><strong>{{ number_format($summary['total_stock']) }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Low Stock</span><strong>{{ $summary['low_stock_count'] }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Out of Stock</span><strong>{{ $summary['out_of_stock_count'] }}</strong></div>
        <div class="mini-panel card border-0"><span class="small-muted">Fill Rate</span><strong>{{ $summary['fill_rate'] }}%</strong></div>
    </section>

    <section class="two-col" style="margin-top: 12px;">
        <article class="panel card border-0">
            <h2 class="panel-title">Products & Price List</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>SKU</th><th>Product</th><th>SRP</th><th>Dealer</th><th>Mega Dealer</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td><span class="status-pill">{{ $product->source }}</span></td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>P{{ number_format($product->price, 2) }}</td>
                                <td>P{{ number_format($product->dealer_price, 2) }}</td>
                                <td>P{{ number_format($product->mega_dealer_price, 2) }}</td>
                                <td>{{ $product->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel card border-0">
            <h2 class="panel-title">Inventory Movements</h2>
            <div class="table-wrap">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Source</th><th>SKU</th><th>Item</th><th>Type</th><th>Qty</th><th>From</th><th>To</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td><span class="status-pill">{{ $movement->source }}</span></td>
                                <td>{{ $movement->sku }}</td>
                                <td>{{ $movement->item_name }}</td>
                                <td>{{ ucfirst($movement->movement_type) }}</td>
                                <td>{{ number_format($movement->qty) }}</td>
                                <td>{{ $movement->from_area }}</td>
                                <td>{{ $movement->to_area }}</td>
                                <td>{{ $movement->transfer_date }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8">No inventory movements found yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection


