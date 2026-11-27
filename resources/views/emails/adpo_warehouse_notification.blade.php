<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Area Distributor Purchase Order</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#111827;">
    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <div style="padding:22px 24px; border-bottom:1px solid #edf0f5; background:#fcfcfd;">
            <div style="font-size:12px; font-weight:bold; color:#dc2626; text-transform:uppercase;">Warehouse Notification</div>
            <h2 style="margin:8px 0 0; font-size:22px; color:#111827;">New AD Purchase Order</h2>
        </div>

        <div style="padding:24px;">
            <p style="margin-top:0;">A distributor purchase order has been submitted to {{ $order->source_label }} and needs {{ $order->source_label === 'PREI Plant' ? 'Lubao plant' : 'Guinobatan warehouse' }} attention.</p>

            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:8px 0; color:#667085;">Business</td>
                    <td style="padding:8px 0;">{{ $order->business_name ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Territory</td>
                    <td style="padding:8px 0;">{{ $order->authorized_territory ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085; width:170px;">Phone</td>
                    <td style="padding:8px 0;">{{ $order->phone_number ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Email</td>
                    <td style="padding:8px 0;">{{ $order->email_address ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Shipping</td>
                    <td style="padding:8px 0;">{{ strtoupper(str_replace('_', ' ', $order->shipping_type ?: 'N/A')) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Payment</td>
                    <td style="padding:8px 0;">{{ strtoupper(str_replace('_', ' ', $order->payment_method ?: 'N/A')) }}{{ $order->bank_name ? ' - ' . strtoupper($order->bank_name) : '' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Voucher Code</td>
                    <td style="padding:8px 0;">{{ $order->voucher_code ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Delivery Address</td>
                    <td style="padding:8px 0;">{{ $order->delivery_address ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Submitted</td>
                    <td style="padding:8px 0;">{{ optional($order->submitted_at ?: $order->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</td>
                </tr>
            </table>

            <h3 style="margin:22px 0 10px; font-size:16px;">Items</h3>
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr>
                        <th align="left" style="padding:8px; background:#f8fafc; border:1px solid #edf0f5;">Product</th>
                        <th align="center" style="padding:8px; background:#f8fafc; border:1px solid #edf0f5;">Order Qty</th>
                        <th align="center" style="padding:8px; background:#f8fafc; border:1px solid #edf0f5;">Unit Price</th>
                        <th align="right" style="padding:8px; background:#f8fafc; border:1px solid #edf0f5;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $colorBreakdown = $item->color_breakdown ? json_decode($item->color_breakdown, true) : [];
                            $sizeBreakdown = $item->size_breakdown ? json_decode($item->size_breakdown, true) : [];
                        @endphp
                        <tr>
                            <td style="padding:8px; border:1px solid #edf0f5;">
                                <div style="font-weight:bold; color:#111827;">{{ $item->product_name }}</div>
                                @if($item->description)
                                    <div style="margin-top:3px; color:#667085; font-size:12px;">{{ $item->description }}</div>
                                @endif
                                @if(!empty($colorBreakdown))
                                    <div style="margin-top:6px; color:#475467; font-size:12px;">
                                        <strong>Colors:</strong>
                                        @foreach($colorBreakdown as $color => $qty)
                                            {{ ucfirst($color) }}: {{ $qty }}@if(!$loop->last) &nbsp; @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($sizeBreakdown))
                                    <div style="margin-top:6px; color:#475467; font-size:12px;">
                                        <strong>Sizes:</strong>
                                        @foreach($sizeBreakdown as $size => $qty)
                                            {{ $size }}: {{ $qty }}@if(!$loop->last) &nbsp; @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td align="center" style="padding:8px; border:1px solid #edf0f5;">{{ number_format($item->qty) }}</td>
                            <td align="right" style="padding:8px; border:1px solid #edf0f5;">PHP {{ number_format($item->unit_price, 2) }}</td>
                            <td align="right" style="padding:8px; border:1px solid #edf0f5;">PHP {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table style="width:280px; margin:18px 0 0 auto; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:7px 0; color:#667085;">Subtotal</td>
                    <td align="right" style="padding:7px 0; font-weight:bold;">PHP {{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:7px 0; color:#667085;">Delivery Fee</td>
                    <td align="right" style="padding:7px 0; font-weight:bold;">PHP {{ number_format($order->delivery_fee, 2) }}</td>
                </tr>
                @if((float) ($order->rebate_amount ?? 0) > 0)
                    <tr>
                        <td style="padding:7px 0; color:#667085;">Rebate Voucher</td>
                        <td align="right" style="padding:7px 0; font-weight:bold; color:#dc2626;">- PHP {{ number_format($order->rebate_amount, 2) }}</td>
                    </tr>
                @endif
                @if((float) ($order->withholding_tax ?? 0) > 0)
                    <tr>
                        <td style="padding:7px 0; color:#667085;">Less: EWT</td>
                        <td align="right" style="padding:7px 0; font-weight:bold; color:#dc2626;">- PHP {{ number_format($order->withholding_tax, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:10px 0 0; border-top:1px solid #edf0f5; color:#111827; font-weight:bold;">Total</td>
                    <td align="right" style="padding:10px 0 0; border-top:1px solid #edf0f5; color:#111827; font-weight:bold;">PHP {{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
