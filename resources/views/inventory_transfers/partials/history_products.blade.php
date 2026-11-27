<div class="movement-products">
    @if($movement->out_type === 'Pull Out')
        @foreach(\App\InventoryTransfer::pullOutProducts($historyItems)->take(3) as $product)
            <div class="movement-product">
                <div><strong>{{ $product['name'] }}</strong><small>{{ $product['sku'] }}</small>
                <small>{{ number_format($product['open']) }} awaiting release · {{ number_format($product['confirmed']) }} received</small></div>
                <span class="movement-product-qty">{{ number_format($product['qty']) }} pcs</span>
            </div>
        @endforeach
    @else
        @foreach($historyItems->take(3) as $line)
            <div class="movement-product">
                <div><strong>{{ $line->item_name }}</strong><small>{{ $line->sku }}</small></div>
                <span class="movement-product-qty">{{ number_format($line->qty) }} pcs</span>
            </div>
        @endforeach
    @endif
    @php($productCount = $movement->out_type === 'Pull Out' ? \App\InventoryTransfer::pullOutProducts($historyItems)->count() : $historyItems->count())
    @if($productCount > 3)<span class="movement-product-more">+{{ $productCount - 3 }} more {{ $productCount - 3 === 1 ? 'product' : 'products' }} · open View for details</span>@endif
</div>
