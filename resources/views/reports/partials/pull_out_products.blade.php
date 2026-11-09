@foreach($products as $product)
    <div class="mb-2">
        <strong>{{ $replacement ? $product['replacement_name'] : $product['name'] }}</strong>
        <small class="d-block text-muted">{{ $replacement ? $product['replacement_sku'] : $product['sku'] }}</small>
        @if($replacement)
            <small>{{ number_format($product['approved']) }} approved · {{ number_format($product['confirmed']) }} received</small>
        @else
            <small>{{ number_format($product['qty']) }} requested · {{ number_format($product['open']) }} awaiting release</small>
        @endif
    </div>
@endforeach
