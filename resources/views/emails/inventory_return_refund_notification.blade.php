<p>Hello,</p>
@if($recipientType === 'approver')
<p>A Return and Refund request requires your approval.</p>
@else
<p>The warehouse has received and confirmed your Return and Refund request.</p>
@endif
<p><strong>Reference:</strong> {{ $movement->reference_no }}<br>
<strong>Product:</strong> {{ $movement->item_name }}<br>
<strong>Quantity:</strong> {{ number_format($movement->warehouse_received_qty ?: $movement->qty) }}<br>
@if($movement->ris_number)<strong>RIS #:</strong> {{ $movement->ris_number }}<br>@endif
@if($movement->warehouse_reference_no)<strong>Warehouse reference #:</strong> {{ $movement->warehouse_reference_no }}<br>@endif
<strong>Status:</strong> {{ $movement->approval_status }}</p>
@if($recipientType === 'approver')
<p><a href="{{ route('return-refunds.index') }}">Open Return and Refund requests</a></p>
@endif
