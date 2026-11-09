<div class="border rounded overflow-hidden bg-white">
    @if($movement->out_type === 'Pull Out')
        @foreach(\App\InventoryTransfer::pullOutProducts($historyItems) as $product)
            <div class="px-2 py-1 border-bottom">
                <strong>{{ $product['name'] }}</strong> <small>{{ $product['sku'] }}</small>
                <small class="d-block">{{ number_format($product['qty']) }} requested · {{ number_format($product['open']) }} awaiting release · {{ number_format($product['confirmed']) }} received</small>
            </div>
        @endforeach
    @else
        @foreach($historyItems as $line)
            <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom">
                <span>{{ $line->item_name }} <small class="text-muted">{{ $line->sku }}</small></span>
                <span class="badge bg-light text-dark border">{{ number_format($line->qty) }}</span>
            </div>
        @endforeach
    @endif
</div>
