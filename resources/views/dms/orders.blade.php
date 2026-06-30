@extends('layouts.dashboard')

@section('title', 'Orders')

@section('content')
    <div class="module-header">
        <div>
            <h1>Orders</h1>
            <p>Customer and dealer order details from live CRM order tables.</p>
        </div>
        <form class="search-form form-inline" method="GET" action="{{ route('dms.orders') }}">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Search transaction, SKU, item, customer">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <article class="panel card border-0">
        <div class="table-wrap">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Source</th><th>Transaction</th><th>Item</th><th>SKU</th><th>Qty</th><th>Price</th><th>Payment</th><th>Delivery</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="status-pill">{{ $order->source }}</span></td>
                            <td>{{ $order->transaction_id }}</td>
                            <td>{{ $order->item }}</td>
                            <td>{{ $order->sku }}</td>
                            <td>{{ number_format($order->qty) }}</td>
                            <td>P{{ number_format($order->price, 2) }}</td>
                            <td>{{ $order->payment_method }}</td>
                            <td>{{ $order->delivery_type }}</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->date ?: $order->completed_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
@endsection


