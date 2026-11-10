@extends('layouts.header')

@section('css')
<style>
    .dpo-edit-page{--primary:#0b5ed7;--ink:#172554;--muted:#64748b;--line:#dce6f0;max-width:1280px;margin:0 auto;padding-bottom:30px}
    .dpo-edit-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px 24px;margin-bottom:16px;color:#fff;background:linear-gradient(120deg,#0b3f82,#087ea4);border-radius:18px;box-shadow:0 16px 34px rgba(15,76,129,.18)}
    .dpo-edit-hero h3{margin:3px 0 4px;color:#fff;font-size:24px;font-weight:900}.dpo-edit-hero p{margin:0;opacity:.82;font-size:13px}.dpo-edit-kicker{font-size:10px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;opacity:.72}.dpo-edit-status{padding:7px 12px;color:#9a3412;font-size:11px;font-weight:900;background:#ffedd5;border:1px solid #fed7aa;border-radius:999px;white-space:nowrap}
    .dpo-edit-card{margin-bottom:15px;background:#fff;border:1px solid var(--line);border-radius:15px;box-shadow:0 7px 22px rgba(15,23,42,.045);overflow:hidden}.dpo-edit-card-head{padding:16px 18px;border-bottom:1px solid var(--line)}.dpo-edit-card-head h5{margin:0;color:var(--ink);font-size:15px;font-weight:900}.dpo-edit-card-head p{margin:3px 0 0;color:var(--muted);font-size:12px}.dpo-edit-card-body{padding:18px}
    .dpo-edit-page .form-label{margin-bottom:5px;color:#475569;font-size:11px;font-weight:900;letter-spacing:.035em;text-transform:uppercase}.dpo-edit-page .form-control,.dpo-edit-page .form-select{min-height:42px;border-color:#cbd5e1;border-radius:9px;font-size:13px}.dpo-edit-page .form-control:focus,.dpo-edit-page .form-select:focus{border-color:#38bdf8;box-shadow:0 0 0 .18rem rgba(14,165,233,.14)}
    .dpo-item{display:grid;grid-template-columns:minmax(0,1fr) 310px 130px;gap:16px;align-items:center;padding:15px 0;border-bottom:1px solid #edf2f7}.dpo-item:last-child{border-bottom:0}.dpo-item-info{display:flex;align-items:center;gap:12px;min-width:0}.dpo-item-image{width:54px;height:54px;object-fit:contain;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px}.dpo-item-name{color:var(--ink);font-size:13px;font-weight:900}.dpo-item-copy{margin-top:3px;color:var(--muted);font-size:11px}.dpo-variant-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.dpo-variant-grid label{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 9px;color:#475569;font-size:11px;font-weight:800;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px}.dpo-variant-grid input{width:68px;min-height:33px;padding:5px;text-align:center}.dpo-line-total{text-align:right}.dpo-line-total small{display:block;color:var(--muted);font-size:10px;font-weight:800;text-transform:uppercase}.dpo-line-total strong{display:block;margin-top:4px;color:#075985;font-size:14px}.dpo-zero-note{margin-top:5px;color:#b45309;font-size:10px}
    .dpo-add-products{margin-top:16px;border:1px solid #bfdbfe;border-radius:12px;background:#f8fbff;overflow:hidden}.dpo-add-products summary{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;color:#1e3a8a;font-size:13px;font-weight:900;cursor:pointer;list-style:none}.dpo-add-products summary::-webkit-details-marker{display:none}.dpo-add-products[open] summary{border-bottom:1px solid #dbeafe}.dpo-add-products-body{padding:0 15px 10px}.dpo-add-products-copy{color:#64748b;font-size:11px;font-weight:600}
    .dpo-edit-footer{position:sticky;bottom:12px;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:15px;padding:14px 16px;background:rgba(255,255,255,.96);border:1px solid var(--line);border-radius:14px;box-shadow:0 12px 35px rgba(15,23,42,.14);backdrop-filter:blur(10px)}.dpo-total-label{color:var(--muted);font-size:10px;font-weight:900;text-transform:uppercase}.dpo-total-value{color:var(--ink);font-size:21px;font-weight:900}.dpo-edit-actions{display:flex;gap:9px}.dpo-edit-actions .btn{min-height:42px;padding:9px 16px;font-weight:900;border-radius:9px}
    @media(max-width:900px){.dpo-item{grid-template-columns:1fr 230px}.dpo-line-total{grid-column:1/-1;text-align:left}.dpo-line-total small,.dpo-line-total strong{display:inline;margin-right:7px}}
    @media(max-width:650px){.dpo-edit-page{padding:0 4px 24px}.dpo-edit-hero{align-items:flex-start;flex-direction:column;padding:17px}.dpo-edit-hero h3{font-size:20px}.dpo-item{grid-template-columns:1fr}.dpo-variant-grid{grid-template-columns:1fr}.dpo-line-total{grid-column:auto}.dpo-edit-footer{align-items:stretch;flex-direction:column}.dpo-edit-actions{display:grid;grid-template-columns:1fr 1fr}.dpo-edit-actions .btn{width:100%}}
</style>
@endsection

@section('content')
<div class="container-fluid dpo-edit-page">
    <div class="mb-3">
        <a href="{{ route('ad-purchase-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to DPO</a>
    </div>

    <section class="dpo-edit-hero">
        <div>
            <div class="dpo-edit-kicker">Distributor Purchase Order</div>
            <h3>Edit {{ $order->po_number }}</h3>
            <p>Update order details and quantities while this purchase order is still pending.</p>
        </div>
        <span class="dpo-edit-status"><i class="bi bi-clock-history me-1"></i> Pending — Editable</span>
    </section>

    @if($errors->any())
        <div class="alert alert-danger border"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('ad-purchase-orders.update', $order->id) }}" method="POST" id="pendingDpoEditForm">
        @csrf
        @method('PATCH')

        <section class="dpo-edit-card">
            <div class="dpo-edit-card-head"><h5>Order Information</h5><p>The fulfillment source cannot be changed after submission.</p></div>
            <div class="dpo-edit-card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Business / Store</label><input class="form-control" value="{{ $order->business_name }}" readonly></div>
                    <div class="col-md-6"><label class="form-label">Source</label><input class="form-control" value="{{ $order->order_source === 'provincial_distributor' ? 'Provincial Distributor' : 'PREI Warehouse' }}" readonly></div>
                    <div class="col-md-6"><label class="form-label" for="editPhoneNumber">Phone Number</label><input class="form-control" id="editPhoneNumber" name="phone_number" value="{{ old('phone_number', $order->phone_number) }}" maxlength="50"></div>
                    <div class="col-md-6"><label class="form-label" for="editEmailAddress">Email Address</label><input type="email" class="form-control" id="editEmailAddress" name="email_address" value="{{ old('email_address', $order->email_address) }}"></div>
                    <div class="col-md-6">
                        <label class="form-label" for="editTerritory">Authorized Territory</label>
                        @if($territoryOptions->isNotEmpty())
                            <select class="form-select" id="editTerritory" name="authorized_territory">
                                @foreach($territoryOptions as $territory)<option value="{{ $territory }}" {{ old('authorized_territory', $order->authorized_territory) === $territory ? 'selected' : '' }}>{{ $territory }}</option>@endforeach
                            </select>
                        @else
                            <input class="form-control" id="editTerritory" name="authorized_territory" value="{{ old('authorized_territory', $order->authorized_territory) }}">
                        @endif
                    </div>
                    <div class="col-md-6"><label class="form-label" for="editShippingType">Shipping</label><select class="form-select" id="editShippingType" name="shipping_type" required>@foreach(['delivered'=>'Delivered','pickup_lubao'=>'Pick Up — Lubao Plant','pickup_guinobatan'=>'Pick Up — Guinobatan Plant'] as $value=>$label)<option value="{{ $value }}" {{ old('shipping_type', $order->shipping_type) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label" for="editDeliveryAddress">Delivery Address</label><textarea class="form-control" id="editDeliveryAddress" name="delivery_address" rows="2">{{ old('delivery_address', $order->delivery_address) }}</textarea></div>
                    <div class="col-md-4"><label class="form-label" for="editPaymentMethod">Payment Method</label><select class="form-select" id="editPaymentMethod" name="payment_method" required>@foreach(['cash'=>'Cash','gcash'=>'GCash','bank_transfer'=>'Bank Transfer','credit'=>'Credit','voucher'=>'Voucher'] as $value=>$label)<option value="{{ $value }}" {{ old('payment_method', $order->payment_method) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4" id="editBankNameWrap"><label class="form-label" for="editBankName">Bank Name</label><input class="form-control" id="editBankName" name="bank_name" value="{{ old('bank_name', $order->bank_name) }}" maxlength="255"></div>
                    <div class="col-12"><label class="form-label" for="editRemarks">Remarks</label><textarea class="form-control" id="editRemarks" name="remarks" rows="3" maxlength="2000">{{ old('remarks', $order->remarks) }}</textarea></div>
                </div>
            </div>
        </section>

        <section class="dpo-edit-card">
            <div class="dpo-edit-card-head"><h5>Ordered Products</h5><p>Set every quantity for a product to zero to remove that line. At least one product is required.</p></div>
            <div class="dpo-edit-card-body">
                @foreach($order->items as $item)
                    @php
                        $colors = collect(json_decode($item->color_breakdown ?: '[]', true) ?: []);
                        $sizes = collect(json_decode($item->size_breakdown ?: '[]', true) ?: []);
                        $imagePath = $item->product_image && file_exists(public_path('uploads/products/' . $item->product_image)) ? asset('uploads/products/' . $item->product_image) : asset('design/assets/images/products/empty-shopping-bag.gif');
                    @endphp
                    <div class="dpo-item js-dpo-line" data-price="{{ (float) $item->unit_price }}">
                        <div class="dpo-item-info">
                            <img src="{{ $imagePath }}" class="dpo-item-image" alt="{{ $item->product_name }}">
                            <div class="min-w-0"><div class="dpo-item-name">{{ $item->product_name }}</div><div class="dpo-item-copy">PHP {{ number_format($item->unit_price, 2) }} each</div><div class="dpo-zero-note">Zero quantity removes this product.</div></div>
                        </div>
                        <div>
                            @if($colors->isNotEmpty())
                                <div class="dpo-variant-grid">
                                    @foreach($colors as $color => $quantity)<label><span>{{ ucfirst($color) }}</span><input type="number" class="form-control js-edit-qty" name="items[{{ $item->id }}][colors][{{ $color }}]" value="{{ old('items.' . $item->id . '.colors.' . $color, $quantity) }}" min="0" step="1"></label>@endforeach
                                </div>
                            @elseif($sizes->isNotEmpty())
                                <div class="dpo-variant-grid">
                                    @foreach($sizes as $size => $quantity)<label><span>{{ $size }}</span><input type="number" class="form-control js-edit-qty" name="items[{{ $item->id }}][sizes][{{ $size }}]" value="{{ old('items.' . $item->id . '.sizes.' . $size, $quantity) }}" min="0" step="1"></label>@endforeach
                                </div>
                            @else
                                <label class="form-label" for="editItemQty{{ $item->id }}">Order Quantity</label>
                                <input type="number" class="form-control js-edit-qty" id="editItemQty{{ $item->id }}" name="items[{{ $item->id }}][qty]" value="{{ old('items.' . $item->id . '.qty', $item->qty) }}" min="0" step="1">
                            @endif
                        </div>
                        <div class="dpo-line-total"><small>Line Total</small><strong class="js-line-total">PHP {{ number_format($item->line_total, 2) }}</strong></div>
                    </div>
                @endforeach

                @if($availableProducts->isNotEmpty())
                    <details class="dpo-add-products">
                        <summary><span><i class="bi bi-plus-circle me-1"></i>Add Products</span><span class="dpo-add-products-copy">Enter a quantity to add a new line</span></summary>
                        <div class="dpo-add-products-body">
                            @foreach($availableProducts as $product)
                                @php
                                    $productName = $product->item ?: $product->product_name;
                                    $unitPrice = $product->distributorPurchaseOrderPrice();
                                    $isColorProduct = $product->isGazLiteStoveKit();
                                    $isSizeProduct = strpos(strtolower(trim((string) $productName)), 'gaz lite authorized retail partner uniform') !== false;
                                    $productImage = $product->item_image && file_exists(public_path('uploads/products/' . $product->item_image)) ? asset('uploads/products/' . $product->item_image) : asset('design/assets/images/products/empty-shopping-bag.gif');
                                @endphp
                                <div class="dpo-item js-dpo-line" data-price="{{ $unitPrice }}">
                                    <div class="dpo-item-info">
                                        <img src="{{ $productImage }}" class="dpo-item-image" alt="{{ $productName }}">
                                        <div class="min-w-0"><div class="dpo-item-name">{{ $productName }}</div><div class="dpo-item-copy">PHP {{ number_format($unitPrice, 2) }} each</div></div>
                                    </div>
                                    <div>
                                        @if($isColorProduct)
                                            <div class="dpo-variant-grid">
                                                @foreach($product->availableStoveKitColors() as $color => $label)<label><span>{{ $label }}</span><input type="number" class="form-control js-edit-qty" name="new_products[{{ $product->id }}][colors][{{ $color }}]" value="{{ old('new_products.' . $product->id . '.colors.' . $color, 0) }}" min="0" step="1"></label>@endforeach
                                            </div>
                                        @elseif($isSizeProduct)
                                            <div class="dpo-variant-grid">
                                                @foreach(['extra_small'=>'Extra Small','small'=>'Small','medium'=>'Medium','large'=>'Large','extra_large'=>'Extra Large'] as $sizeKey => $sizeLabel)<label><span>{{ $sizeLabel }}</span><input type="number" class="form-control js-edit-qty" name="new_products[{{ $product->id }}][sizes][{{ $sizeKey }}]" value="{{ old('new_products.' . $product->id . '.sizes.' . $sizeKey, 0) }}" min="0" step="1"></label>@endforeach
                                            </div>
                                        @else
                                            <label class="form-label" for="newProductQty{{ $product->id }}">Quantity to Add</label>
                                            <input type="number" class="form-control js-edit-qty" id="newProductQty{{ $product->id }}" name="new_products[{{ $product->id }}][qty]" value="{{ old('new_products.' . $product->id . '.qty', 0) }}" min="0" step="1">
                                        @endif
                                    </div>
                                    <div class="dpo-line-total"><small>Added Total</small><strong class="js-line-total">PHP 0.00</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </section>

        @include('ad_purchase_orders.partials.tax_signing')
        <div class="dpo-edit-footer">
            <div><div class="dpo-total-label">Updated Subtotal</div><div class="dpo-total-value" id="editDpoSubtotal">PHP {{ number_format($order->subtotal, 2) }}</div></div>
            <div class="dpo-edit-actions"><a href="{{ route('ad-purchase-orders.show', $order->id) }}" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save Changes</button></div>
        </div>
    </form>
</div>
@endsection

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentMethod = document.getElementById('editPaymentMethod');
    const bankWrap = document.getElementById('editBankNameWrap');
    const bankName = document.getElementById('editBankName');
    const subtotal = document.getElementById('editDpoSubtotal');

    function toggleConditionalFields() {
        const needsBank = paymentMethod.value === 'bank_transfer';
        bankWrap.style.display = needsBank ? '' : 'none';
        bankName.required = needsBank;
    }

    function refreshTotals() {
        let total = 0;
        document.querySelectorAll('.js-dpo-line').forEach(function (line) {
            const price = Number(line.dataset.price || 0);
            let quantity = 0;
            line.querySelectorAll('.js-edit-qty').forEach(function (input) { quantity += Math.max(0, Number(input.value || 0)); });
            const lineTotal = price * quantity;
            total += lineTotal;
            line.querySelector('.js-line-total').textContent = 'PHP ' + lineTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        });
        subtotal.textContent = 'PHP ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    paymentMethod.addEventListener('change', toggleConditionalFields);
    document.querySelectorAll('.js-edit-qty').forEach(function (input) { input.addEventListener('input', refreshTotals); });
    toggleConditionalFields();
    refreshTotals();
});
</script>
@endsection
