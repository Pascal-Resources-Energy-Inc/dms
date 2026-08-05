@extends('layouts.header')

@section('css')
<style>
    .adpo-show { max-width: 1100px; margin: 0 auto; }
    .receipt { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
    .receipt-head { display: flex; justify-content: space-between; gap: 18px; padding: 24px; border-bottom: 1px solid #edf0f5; }
    .receipt-logo { height: 48px; width: auto; object-fit: contain; }
    .receipt-title { margin: 10px 0 0; color: #111827; font-size: 22px; font-weight: 800; }
    .meta-grid { display: grid; grid-template-columns: repeat(4, minmax(120px, 1fr)); gap: 12px; padding: 18px 24px; border-bottom: 1px solid #edf0f5; }
    .meta-item span { display: block; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .meta-item strong { display: block; margin-top: 4px; color: #111827; word-wrap: break-word; }
    .status-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 800; background: #fff7ed; color: #c2410c; }
    .receipt-body { padding: 24px; }
    .item-thumb { width: 58px; height: 58px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 8px; background: #f8fafc; }
    .total-card { max-width: 360px; margin-left: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
    @media (max-width: 768px) {
        .receipt-head { flex-direction: column; }
        .meta-grid { grid-template-columns: repeat(2, minmax(120px, 1fr)); }
        .table-wrap { overflow-x: auto; }
        .table { min-width: 760px; }
    }
</style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('ad-purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to ADPO
        </a>
        @if(auth()->user()->role === 'Admin')
            <form action="{{ route('ad-purchase-orders.updateStatus', $order->id) }}" method="POST" class="d-flex gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm">
                    @foreach(['Pending','For Delivery','For Verification','Completed','Cancelled'] as $status)
                        <option value="{{ $status }}" @if($order->status === $status) selected @endif>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Update</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="receipt">
        <div class="receipt-head">
            <div>
                <img src="{{ asset('images/logo_mo.png') }}" class="receipt-logo" alt="Gaz Lite">
                <h4 class="receipt-title">Area Distributor Purchase Order</h4>
                <div class="text-muted">(ADPO)</div>
            </div>
            <div class="text-md-end">
                <div class="text-muted small">PO Number</div>
                <h5 class="fw-bold mb-2">{{ $order->po_number }}</h5>
                <span class="status-pill">{{ $order->status }}</span>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-item"><span>Business</span><strong>{{ $order->business_name }}</strong></div>
            <div class="meta-item"><span>Territory</span><strong>{{ $order->authorized_territory ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Phone</span><strong>{{ $order->phone_number ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Email</span><strong>{{ strtoupper($order->email_address) ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Shipping</span><strong>{{ strtoupper(ucwords(str_replace('_', ' ', $order->shipping_type))) }}</strong></div>
            <div class="meta-item"><span>Payment</span><strong>{{ strtoupper(ucwords(str_replace('_', ' ', $order->payment_method))) }}</strong></div>
            <div class="meta-item"><span>Voucher Code</span><strong>{{ strtoupper($order->voucher_code) ?: 'N/A' }}</strong></div>
            {{-- <div class="meta-item"><span>Uniform Size</span><strong>{{ $order->uniform_size ?: 'N/A' }}</strong></div> --}}
            <div class="meta-item"><span>Delivery Address</span><strong>{{ strtoupper($order->delivery_address) ?: 'N/A' }}</strong></div>
            <div class="meta-item"><span>Submitted</span><strong>{{ optional($order->submitted_at ?: $order->created_at)->format('M d, Y h:i A') }}</strong></div>
        </div>

        <div class="receipt-body">
            <div class="table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $imagePath = $item->product_image && file_exists(public_path('uploads/products/' . $item->product_image))
                                    ? asset('uploads/products/' . $item->product_image)
                                    : asset('design/assets/images/products/empty-shopping-bag.gif');
                                $colorBreakdown = $item->color_breakdown ? json_decode($item->color_breakdown, true) : [];
                                $sizeBreakdown = $item->size_breakdown ? json_decode($item->size_breakdown, true) : [];
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $imagePath }}" class="item-thumb" alt="{{ $item->product_name }}">
                                        <div>
                                            <div class="fw-bold">{{ $item->product_name }}</div>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</small>
                                            @if(!empty($colorBreakdown))
                                                <div class="small text-muted mt-1">
                                                    Colors:
                                                    @foreach($colorBreakdown as $color => $qty)
                                                        <span class="badge bg-light text-dark border">{{ ucfirst($color) }}: {{ $qty }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(!empty($sizeBreakdown))
                                                <div class="small text-muted mt-1">
                                                    Sizes:
                                                    @foreach($sizeBreakdown as $size => $qty)
                                                        <span class="badge bg-light text-dark border">{{ $size }}: {{ $qty }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">PHP {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">PHP {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($order->remarks)
                <div class="alert alert-light border mt-3">
                    <strong>Remarks:</strong> {{ $order->remarks }}
                </div>
            @endif

            <div class="total-card mt-3">
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>PHP {{ number_format($order->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><strong>PHP {{ number_format($order->delivery_fee, 2) }}</strong></div>
                @if((float) ($order->rebate_amount ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-2"><span>Rebate Voucher</span><strong>- PHP {{ number_format($order->rebate_amount, 2) }}</strong></div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fs-5"><span>Total</span><strong>PHP {{ number_format($order->total_amount, 2) }}</strong></div>
            </div>
        </div>
    </div>
@endsection
