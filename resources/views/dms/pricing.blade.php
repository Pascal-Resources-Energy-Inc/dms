@extends('layouts.dashboard')

@section('title', 'Tactical Pricing')

@section('content')
    <div class="module-header">
        <div>
            <h1>Tactical Pricing</h1>
            <p>Current product SRP, dealer price, and mega dealer price from the CRM product master.</p>
        </div>
    </div>

    <article class="panel card border-0">
        <div class="table-wrap">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Source</th><th>SKU</th><th>Product</th><th>SRP</th><th>Dealer</th><th>Mega Dealer</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><span class="status-pill">{{ $product->source }}</span></td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>P{{ number_format($product->price, 2) }}</td>
                            <td>P{{ number_format($product->dealer_price, 2) }}</td>
                            <td>P{{ number_format($product->mega_dealer_price, 2) }}</td>
                            <td>{{ $product->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection


