<p>Hello,</p>
@if($recipientType === 'approver')
<p>A Return and Refund request requires your approval.</p>
@else
<p>The warehouse has received and confirmed your Return and Refund request.</p>
@endif
<p><strong>Reference:</strong> {{ $movement->reference_no }}<br>
@if($movement->ris_number)<strong>RIS #:</strong> {{ $movement->ris_number }}<br>@endif
@if($movement->warehouse_reference_no)<strong>SIRR #:</strong> {{ $movement->warehouse_reference_no }}<br>@endif
<strong>Status:</strong> {{ $movement->approval_status }}</p>
<table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse;">
    <thead><tr><th align="left">Product</th><th align="left">Quantity</th></tr></thead>
    <tbody>
        @foreach($products as $product)
            <tr><td>{{ $product->item_name }} ({{ $product->sku }})</td><td>{{ number_format($product->warehouse_received_qty ?: $product->qty) }} pcs</td></tr>
        @endforeach
    </tbody>
</table>
@if($recipientType === 'approver')
<p><a href="{{ route('return-refunds.index') }}">Open Return and Refund requests</a></p>
@endif
