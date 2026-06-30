@extends('layouts.dashboard')

@section('title', 'Purchases')

@section('content')
    <div class="module-header">
        <div>
            <h1>Purchases</h1>
            <p>Area distributor purchase orders, payment method, status, delivery date, and total amount.</p>
        </div>
        <form class="search-form form-inline" method="GET" action="{{ route('dms.purchases') }}">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Search PO, reference, business, territory">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <article class="panel card border-0">
        <div class="table-wrap">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Source</th><th>PO Number</th><th>Reference</th><th>Business</th><th>Territory</th><th>Qty</th><th>Total</th><th>Payment</th><th>Status</th><th>Delivery</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="status-pill">{{ $order->source }}</span></td>
                            <td>{{ $order->po_number }}</td>
                            <td>{{ $order->reference_no }}</td>
                            <td>{{ $order->business_name }}</td>
                            <td>{{ $order->authorized_territory }}</td>
                            <td>{{ number_format($order->total_qty) }}</td>
                            <td>P{{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ $order->payment_method }}</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->delivery_date }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10">No purchase orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
@endsection


