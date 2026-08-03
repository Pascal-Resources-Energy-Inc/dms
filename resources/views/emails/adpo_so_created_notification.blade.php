<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ADPO Sales Order Created</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#111827;">
    <div style="max-width:720px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <div style="padding:22px 24px; border-bottom:1px solid #dbeafe; background:#eff6ff;">
            <div style="font-size:12px; font-weight:bold; color:#1d4ed8; text-transform:uppercase;">AD Purchase Order Notification</div>
            <h2 style="margin:8px 0 0; font-size:22px; color:#111827;">Sales Order Created</h2>
            <p style="margin:7px 0 0; color:#475467; font-size:14px;">{{ $order->po_number }} has been moved to <strong>SO Created</strong>.</p>
        </div>

        <div style="padding:24px;">
            <h3 style="margin:0 0 10px; font-size:15px; color:#111827;">Order Information</h3>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr><td style="padding:8px 0; color:#667085; width:180px;">PO Number</td><td style="padding:8px 0; font-weight:bold;">{{ $order->po_number ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">SO Number</td><td style="padding:8px 0; font-weight:bold;">{{ $order->so_number ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Previous Status</td><td style="padding:8px 0;">{{ $oldStatus ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Current Status</td><td style="padding:8px 0; font-weight:bold; color:#1d4ed8;">{{ $order->status }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Business Name</td><td style="padding:8px 0;">{{ $order->business_name ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Territory</td><td style="padding:8px 0;">{{ $order->authorized_territory ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Contact</td><td style="padding:8px 0;">{{ $order->phone_number ?: 'N/A' }}{{ $order->email_address ? ' · ' . $order->email_address : '' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Delivery Address</td><td style="padding:8px 0;">{{ $order->delivery_address ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Shipping Type</td><td style="padding:8px 0;">{{ strtoupper(str_replace('_', ' ', $order->shipping_type ?: 'N/A')) }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Payment Method</td><td style="padding:8px 0;">{{ strtoupper(str_replace('_', ' ', $order->payment_method ?: 'N/A')) }}{{ $order->bank_name ? ' · ' . $order->bank_name : '' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Payment Reference</td><td style="padding:8px 0;">{{ $order->reference_no ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Payment Date</td><td style="padding:8px 0;">{{ optional($order->payment_date)->format('M d, Y') ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Delivery Date</td><td style="padding:8px 0;">{{ optional($order->delivery_date)->format('M d, Y') ?: 'N/A' }}</td></tr>
                <tr><td style="padding:8px 0; color:#667085;">Remarks</td><td style="padding:8px 0;">{{ $order->remarks ?: 'N/A' }}</td></tr>
            </table>

            <h3 style="margin:24px 0 10px; font-size:15px; color:#111827;">Ordered Items</h3>
            <table style="width:100%; border-collapse:collapse; font-size:13px; border:1px solid #e5e7eb;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:9px; border-bottom:1px solid #e5e7eb; text-align:left;">Product</th>
                        <th style="padding:9px; border-bottom:1px solid #e5e7eb; text-align:left;">SKU</th>
                        <th style="padding:9px; border-bottom:1px solid #e5e7eb; text-align:right;">Qty</th>
                        <th style="padding:9px; border-bottom:1px solid #e5e7eb; text-align:right;">Unit Price</th>
                        <th style="padding:9px; border-bottom:1px solid #e5e7eb; text-align:right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td style="padding:9px; border-bottom:1px solid #eef2f7;">{{ $item->product_name ?: 'N/A' }}</td>
                            <td style="padding:9px; border-bottom:1px solid #eef2f7;">{{ $item->sku ?: 'N/A' }}</td>
                            <td style="padding:9px; border-bottom:1px solid #eef2f7; text-align:right;">{{ number_format($item->qty) }}</td>
                            <td style="padding:9px; border-bottom:1px solid #eef2f7; text-align:right;">PHP {{ number_format($item->unit_price, 2) }}</td>
                            <td style="padding:9px; border-bottom:1px solid #eef2f7; text-align:right;">PHP {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="padding:12px; text-align:center; color:#667085;">No item information available.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <table style="width:100%; margin-top:14px; border-collapse:collapse; font-size:14px;">
                <tr><td style="padding:5px 0; text-align:right; color:#667085;">Total Quantity:</td><td style="padding:5px 0 5px 16px; text-align:right; font-weight:bold; width:150px;">{{ number_format($order->total_qty) }}</td></tr>
                <tr><td style="padding:5px 0; text-align:right; color:#667085;">Order Total:</td><td style="padding:5px 0 5px 16px; text-align:right; font-size:16px; font-weight:bold; color:#1d4ed8;">PHP {{ number_format($order->total_amount, 2) }}</td></tr>
            </table>

            <p style="margin:24px 0 0; color:#667085; font-size:13px;">Proof-of-payment files, if available, are attached to this email.</p>
        </div>
    </div>
</body>
</html>
