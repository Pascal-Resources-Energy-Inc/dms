<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Incomplete ADPO Verification</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, sans-serif; color:#111827;">
    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <div style="padding:22px 24px; border-bottom:1px solid #fde68a; background:#fffbeb;">
            <div style="font-size:12px; font-weight:bold; color:#b45309; text-transform:uppercase;">Warehouse verification notice</div>
            <h2 style="margin:8px 0 0; font-size:22px; color:#111827;">Crate / Refill Submission Is Incomplete</h2>
        </div>

        <div style="padding:24px;">
            <p style="margin-top:0;">Hello {{ $order->business_name ?: optional($order->ad)->name ?: 'Area Distributor' }},</p>
            <p>Warehouse reviewed your verification submission for PO <strong>{{ $order->po_number }}</strong>. Please update the quantities below and resubmit your verification details.</p>

            <table style="width:100%; border-collapse:collapse; font-size:14px; margin-top:18px;">
                <thead>
                    <tr style="background:#fffbeb;">
                        <th align="left" style="padding:9px; border:1px solid #fde68a; color:#92400e;">Product</th>
                        <th align="center" style="padding:9px; border:1px solid #fde68a; color:#92400e;">Ordered</th>
                        <th align="center" style="padding:9px; border:1px solid #fde68a; color:#92400e;">AD Submitted</th>
                        <th align="center" style="padding:9px; border:1px solid #fde68a; color:#92400e;">Warehouse Verified</th>
                        <th align="center" style="padding:9px; border:1px solid #fde68a; color:#92400e;">Still Needed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incompleteItems as $item)
                        <tr>
                            <td style="padding:9px; border:1px solid #fde68a;">{{ data_get($item, 'product_name') }}</td>
                            <td align="center" style="padding:9px; border:1px solid #fde68a;">{{ number_format(data_get($item, 'ordered_qty', 0)) }}</td>
                            <td align="center" style="padding:9px; border:1px solid #fde68a;">{{ number_format(data_get($item, 'ad_submitted_qty', 0)) }}</td>
                            <td align="center" style="padding:9px; border:1px solid #fde68a;">{{ number_format(data_get($item, 'warehouse_qty', 0)) }}</td>
                            <td align="center" style="padding:9px; border:1px solid #fde68a; color:#b45309; font-weight:bold;">{{ number_format(data_get($item, 'missing_qty', 0)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(filled($warehouseRemarks))
                <div style="margin-top:20px; padding:14px; border-left:4px solid #f59e0b; background:#fffbeb; color:#78350f;">
                    <strong>Warehouse remarks</strong><br>
                    {!! nl2br(e($warehouseRemarks)) !!}
                </div>
            @endif

            @if(collect(json_decode($order->warehouse_verification_proofs ?: '[]', true) ?: [])->isNotEmpty())
                <p style="margin:18px 0 0; color:#475467; font-size:13px;">Warehouse supporting files are attached to this email.</p>
            @endif

            <p style="margin:22px 0 0; color:#475467; font-size:13px;">Your purchase order remains under warehouse verification. Open the ADPO status screen to correct the submitted quantities or add supporting files, then save it again.</p>
        </div>
    </div>
</body>
</html>
