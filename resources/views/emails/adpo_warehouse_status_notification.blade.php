<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ADPO Warehouse Status Notification</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#111827;">
    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <div style="padding:22px 24px; border-bottom:1px solid #edf0f5; background:#fcfcfd;">
            <div style="font-size:12px; font-weight:bold; color:#dc2626; text-transform:uppercase;">Warehouse Notification</div>
            <h2 style="margin:8px 0 0; font-size:22px; color:#111827;">ADPO Status Requires Action</h2>
        </div>

        <div style="padding:24px;">
            <p style="margin-top:0;">An AD purchase order has been moved to <strong>{{ $order->status }}</strong>.</p>

            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:8px 0; color:#667085; width:170px;">PO Number</td>
                    <td style="padding:8px 0; font-weight:bold;">{{ $order->po_number }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Previous Status</td>
                    <td style="padding:8px 0;">{{ $oldStatus ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">New Status</td>
                    <td style="padding:8px 0; font-weight:bold; color:#dc2626;">{{ $order->status }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Business</td>
                    <td style="padding:8px 0;">{{ $order->business_name ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Delivery Address</td>
                    <td style="padding:8px 0;">{{ $order->delivery_address ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#667085;">Total</td>
                    <td style="padding:8px 0; font-weight:bold;">PHP {{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @if($order->remarks)
                    <tr>
                        <td style="padding:8px 0; color:#667085;">Remarks</td>
                        <td style="padding:8px 0;">{{ $order->remarks }}</td>
                    </tr>
                @endif
            </table>

            @if($order->status === 'For Verification' && $order->verificationItems->isNotEmpty())
                <div style="margin-top:22px; padding:16px; border:1px solid #ddd6fe; border-radius:8px; background:#faf5ff;">
                    <div style="margin-bottom:10px; color:#6d28d9; font-size:12px; font-weight:bold; letter-spacing:.04em; text-transform:uppercase;">Crate / Refill Verification Submitted by AD</div>
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr>
                                <th align="left" style="padding:8px; border-bottom:1px solid #ddd6fe; color:#475467;">Product</th>
                                <th align="center" style="padding:8px; border-bottom:1px solid #ddd6fe; color:#475467;">Ordered</th>
                                <th align="center" style="padding:8px; border-bottom:1px solid #ddd6fe; color:#475467;">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->verificationItems as $item)
                                <tr>
                                    <td style="padding:8px; border-bottom:1px solid #eeeaf9;">{{ $item->product_name }}</td>
                                    <td align="center" style="padding:8px; border-bottom:1px solid #eeeaf9;">{{ number_format($item->ordered_qty) }}</td>
                                    <td align="center" style="padding:8px; border-bottom:1px solid #eeeaf9; color:#5b21b6; font-weight:bold;">{{ number_format($item->submitted_qty) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p style="margin:11px 0 0; color:#667085; font-size:12px;">The supporting verification files are attached to this email.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
