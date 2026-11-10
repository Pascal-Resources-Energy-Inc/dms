@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('design/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('design/assets/css/forms.css') }}">
<link rel="stylesheet" href="{{ asset('design/assets/css/products-catalog.css') }}">
<link rel="stylesheet" href="{{ asset('design/assets/css/product-form.css') }}">
@endsection

@section('content')
    @php
        $totalProducts = $products->count();
        $activeProducts = $products->where('status', 'Activate')->count();
        $bundleProducts = $products->filter(function ($product) {
            return (($product->item_type ?? optional($product->item)->item_type) === 'bundle')
                || collect($product->bundle_product_ids ?? [])->filter()->isNotEmpty();
        })->count();
    @endphp
    <section class="welcome product-page" aria-labelledby="productPageTitle">
        <div class="product-head">
            <div>
                <div class="product-kicker"><i class="bi bi-box-seam"></i> Catalog</div>
                <h1 class="product-title" id="productPageTitle">Products</h1>
                <p class="product-copy">Your catalog, pricing, and availability in one place.</p>
            </div>
            <div class="product-actions">
                <a href="{{ route('products.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-grid-3x3-gap"></i>
                    Price Matrix
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg"></i>
                    Add Product
                </button>
            </div>
        </div>

        <div class="product-summary" aria-label="Product summary">
            <div class="product-tile is-total">
                <span class="product-tile-icon"><i class="bi bi-box-seam"></i></span>
                <div>
                    <span>Total Products</span>
                    <strong>{{ number_format($totalProducts) }}</strong>
                </div>
            </div>
            <div class="product-tile is-active">
                <span class="product-tile-icon active"><i class="bi bi-check2-circle"></i></span>
                <div>
                    <span>Active</span>
                    <strong>{{ number_format($activeProducts) }}</strong>
                </div>
            </div>
            <div class="product-tile is-bundle">
                <span class="product-tile-icon bundle"><i class="bi bi-boxes"></i></span>
                <div>
                    <span>Bundles</span>
                    <strong>{{ number_format($bundleProducts) }}</strong>
                </div>
            </div>
        </div>

        <div class="product-panel">
            <div class="product-panel-head">
                <div class="product-list-heading">
                    <span class="product-list-icon"><i class="bi bi-grid-3x3-gap"></i></span>
                    <div>
                        <h2 class="product-list-title">Product catalog</h2>
                        <p class="product-list-copy">Compare prices and manage your products.</p>
                    </div>
                </div>
                <span class="product-result-count" id="productResultCount" aria-live="polite">{{ number_format($totalProducts) }} product{{ $totalProducts === 1 ? '' : 's' }}</span>
            </div>
            <div class="product-toolbar" role="search" aria-label="Filter products">
                <div class="product-search">
                    <label for="productSearch" class="visually-hidden">Search products</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="productSearch" placeholder="Search by name, SKU, or description" autocomplete="off">
                </div>
                <div class="product-status-filter">
                    <label for="productStatusFilter">Status</label>
                    <select id="productStatusFilter">
                        <option value="">All statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <button type="button" class="product-reset" id="productResetFilters" hidden>Clear filters</button>
            </div>
            <div class="product-table-wrap">
                <table class="table align-middle product-table" id="productsTable" aria-label="Product catalog and prices in Philippine pesos">
                    <thead>
                        <tr>
                            <th scope="col">Product</th>
                            <th scope="col" class="product-price-column">Price</th>
                            <th scope="col" class="product-price-column">Mega dealer</th>
                            <th scope="col" class="product-price-column">Dealer</th>
                            <th scope="col" class="product-status-column">Status</th>
                            <th scope="col" class="product-action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $productsById = $products->keyBy('id');
                        @endphp
                        @foreach ($products as $product)
                        @php
                            $bundleProductIds = collect($product->bundle_product_ids ?? [])->filter()->values();
                            $isBundle = ($product->item_type ?? optional($product->item)->item_type) === 'bundle' || $bundleProductIds->isNotEmpty();
                            $bundlePreviewProducts = $bundleProductIds
                                ->map(function ($productId) use ($productsById) {
                                    return $productsById->get((int) $productId);
                                })
                                ->filter()
                                ->take(4);
                        @endphp
                        <tr id="row-{{ $product->id }}">
                            <td class="product-identity" data-order="{{ $product->product_name }}">
                                <div class="product-identity-layout">
                                <div class="{{ $isBundle ? 'product-image-cell is-bundle' : 'product-image-cell' }}">
                                    @if($isBundle && $bundlePreviewProducts->isNotEmpty())
                                        <div class="bundle-card-image">
                                            <div class="bundle-card-grid">
                                                @foreach($bundlePreviewProducts as $bundlePreviewProduct)
                                                    @php
                                                        $bundlePreviewImage = $bundlePreviewProduct->product_image && file_exists(public_path('uploads/products/' . $bundlePreviewProduct->product_image))
                                                            ? asset('uploads/products/' . $bundlePreviewProduct->product_image)
                                                            : asset('images/logo_nya.png');
                                                    @endphp
                                                    <img src="{{ $bundlePreviewImage }}" alt="{{ $bundlePreviewProduct->product_name }}">
                                                @endforeach
                                            </div>
                                            <span class="bundle-card-badge">
                                                <i class="bi bi-boxes"></i>
                                            </span>
                                            @if($bundleProductIds->count() > 4)
                                                <span class="bundle-card-count">+{{ $bundleProductIds->count() - 4 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="product-image-frame">
                                            @if($product->product_image && file_exists(public_path('uploads/products/' . $product->product_image)))
                                                <img src="{{ asset('uploads/products/' . $product->product_image) }}" alt="{{ $product->product_name }}">
                                            @else
                                                <span class="product-image-empty">No Image</span>
                                            @endif

                                            @if($isBundle)
                                                <span class="product-image-badge">
                                                    <i class="bi bi-boxes"></i> Bundle
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                    <div class="product-details">
                                        <div class="product-name">{{ $product->product_name }}</div>
                                        @if(filled($product->description))
                                            <div class="product-description">{{ $product->description }}</div>
                                        @endif
                                        <div class="product-meta">
                                            <span class="product-sku">{{ strtoupper($product->sku) }}</span>
                                            @if($isBundle)<span class="product-type-label">Bundle</span>@endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="product-price-column" data-label="Price" data-order="{{ $product->price }}"><span class="product-price">₱{{ number_format($product->price, 2) }}</span></td>
                            <td class="product-price-column" data-label="Mega dealer" data-order="{{ $product->mega_dealer_price ?? $product->price }}"><span class="product-price">₱{{ number_format($product->mega_dealer_price ?? $product->price, 2) }}</span></td>
                            <td class="product-price-column" data-label="Dealer" data-order="{{ $product->dealer_price ?? $product->price }}"><span class="product-price">₱{{ number_format($product->dealer_price ?? $product->price, 2) }}</span></td>

                            <td class="product-status-column" data-search="{{ $product->status === 'Activate' ? 'Active' : 'Inactive' }}">
                                @if($product->status == 'Activate')
                                    <span class="product-status active"><i class="bi bi-circle-fill" aria-hidden="true"></i> Active</span>
                                @else
                                    <span class="product-status inactive"><i class="bi bi-circle-fill" aria-hidden="true"></i> Inactive</span>
                                @endif
                            </td>

                            <td class="product-action-column">
                                @if($product->is_new === 1)
                                    <button type="button" class="btn btn-sm btn-outline-primary product-edit-button edit-btn"
                                        aria-label="Edit {{ $product->product_name }}"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->product_name }}"
                                        data-description="{{ $product->description }}"
                                        data-sku="{{ $product->sku }}"
                                        data-price="{{ $product->price }}"
                                        data-mega-dealer-price="{{ $product->mega_dealer_price ?? $product->price }}"
                                        data-dealer-price="{{ $product->dealer_price ?? $product->price }}"
                                        data-client-price="{{ $product->client_price ?? $product->price }}"
                                        data-deposit="{{ $product->deposit }}"
                                        data-status="{{ $product->status }}"
                                        data-item-type="{{ $isBundle ? 'bundle' : 'product' }}"
                                        data-bundle-product-ids='@json($bundleProductIds->values())'
                                        data-image="{{ $product->product_image && file_exists(public_path('uploads/products/' . $product->product_image)) ? asset('uploads/products/' . $product->product_image) : asset('images/logo_nya.png') }}">
                                        <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                                    </button>
                                @else
                                    <a class="product-matrix-link" href="{{ route('products.create') }}" aria-label="Manage {{ $product->product_name }} in Price Matrix">Price matrix <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade product-form-modal" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content product-modal">
                <div class="modal-header product-modal-header">
                    <div class="product-modal-heading">
                        <span class="product-modal-icon"><i class="bi bi-box-seam"></i></span>
                        <div>
                        <span class="product-modal-kicker" id="addProductModalKicker">Catalog item</span>
                        <h5 id="addProductModalTitle" class="mb-0">Add Product</h5>
                        <small id="addProductModalSubtitle" class="text-muted">Create a single product for your catalog.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="productForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if ($newProductCount >= $maxNewProducts)
                            <div class="alert alert-warning">
                                You have reached the limit of {{ $maxNewProducts }} new products for this AD user.
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="product-create-grid">
                            <div class="product-create-wide">
                                <input type="hidden" name="selected_item_type" id="selected_item_type" value="{{ old('selected_item_type', 'product') }}">
                                <div class="bundle-switch-panel">
                                    <div>
                                        <div class="bundle-switch-title">
                                            <i class="bi bi-boxes" aria-hidden="true"></i> Create a bundle
                                        </div>
                                        <div class="bundle-switch-copy">Combine existing products into one catalog item.</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input js-bundle-toggle" type="checkbox" role="switch" id="bundleToggle" {{ old('selected_item_type') === 'bundle' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="bundleToggle">Enable bundle</label>
                                    </div>
                                </div>
                            </div>
                            <div class="bundle-image-column">
                                <div class="bundle-visual-shell">
                                    <div class="product-image-heading">Product image <span>Optional</span></div>
                                    <div class="avatar-wrapper product-image-preview mx-auto mb-2" id="productImagePreviewFrame">
                                        <img id="avatar" src="{{ asset('images/logo_nya.png') }}" alt="Preview">
                                    </div>

                                    <label for="inputImage" class="product-image-upload" id="inputImageLabel">
                                        <i class="ti ti-upload" aria-hidden="true"></i>
                                        <span class="product-image-upload-copy"><strong>Upload image</strong><small>JPG, PNG or GIF · Max 2MB</small></span>
                                    </label>

                                    <input type="file" name="product_image" id="inputImage" class="product-image-input" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="uploadImage(this)" aria-label="Upload product image">

                                    {{-- <small id="productImageHelp" class="d-block text-muted mt-1">
                                        Optional override. JPG, PNG, or GIF (Max: 2MB)
                                    </small> --}}
                                    <div id="bundleImagePreview" class="bundle-image-preview d-none mt-3"></div>
                                </div>
                            </div>
                            <div class="bundle-details-column">
                                <section class="product-field-section" aria-labelledby="productDetailsHeading">
                                    <h6 id="productDetailsHeading">Product details</h6>
                                    <div class="product-details-grid">
                                    <div>
                                        <label for="product_name" id="productNameLabel" class="form-label">Product Name&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" id="product_name" name="product_name" class="form-control" value="{{ old('product_name') }}" placeholder="Product Name" data-uppercase required>
                                    </div>
                                    <div>
                                        <label for="sku" class="form-label">SKU&nbsp;<span class="text-danger">*</span></label>
                                        <input type="text" id="sku" name="sku" class="form-control" value="{{ old('sku') }}" required placeholder="Enter stock keeping unit" data-uppercase>
                                    </div>
                                    <div class="product-create-wide">
                                        <label for="description" id="descriptionLabel" class="form-label">Product Description</label>
                                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Product Description" data-uppercase>{{ old('description') }}</textarea>
                                    </div>
                                    </div>
                                </section>
                                <section class="product-field-section" aria-labelledby="productPricingHeading">
                                    <div class="product-section-heading"><h6 id="productPricingHeading">Pricing</h6><span>Philippine peso (PHP)</span></div>
                                    <div class="product-pricing-grid">
                                    <div>
                                        <label for="price" id="priceLabel" class="form-label">Price (PHP)&nbsp;<span class="text-danger">*</span></label>
                                        <input type="number" id="price" name="price" class="form-control" value="{{ old('price') }}" step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                    <div>
                                        <label for="mega_dealer_price" class="form-label">Mega dealer price</label>
                                        <input type="number" id="mega_dealer_price" name="mega_dealer_price" class="form-control" value="{{ old('mega_dealer_price') }}" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label for="dealer_price" class="form-label">Dealer price</label>
                                        <input type="number" id="dealer_price" name="dealer_price" class="form-control" value="{{ old('dealer_price') }}" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    {{-- <div class="col-md-3">
                                        <label for="client_price" class="form-label">End User Price (PHP)</label>
                                        <input type="number" id="client_price" name="client_price" class="form-control" value="{{ old('client_price') }}" step="0.01" min="0" placeholder="0.00">
                                    </div> --}}
                                    {{-- <div class="col-md-6">
                                        <label for="deposit" class="form-label-optional">Deposit (PHP)</label>
                                        <input type="number" id="deposit" name="deposit" class="form-control" value="{{ old('deposit') }}" step="0.01" min="0" placeholder="0.00">
                                    </div> --}}
                                    </div>
                                </section>
                                <div class="product-status-field">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="Activate" {{ old('status', 'Activate') === 'Activate' ? 'selected' : '' }}>Active</option>
                                            <option value="Deactivate" {{ old('status') === 'Deactivate' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                            </div>

                            <div class="product-create-wide d-none" id="bundleProductsWrap">
                                <div class="bundle-builder-layout">
                                    <div class="bundle-products-panel">
                                        <div class="bundle-products-head">
                                            <div>
                                                <label class="form-label mb-0">Products in this bundle&nbsp;<span class="text-danger">*</span></label>
                                                <span class="d-block text-muted small">Choose existing products to combine.</span>
                                            </div>
                                            <span class="bundle-selection-count" aria-live="polite">
                                                <span id="bundleSelectedCount">0</span> selected · PHP <span id="bundleTotal">0.00</span>
                                            </span>
                                        </div>
                                        <div class="bundle-search-wrap">
                                            <i class="bi bi-search"></i>
                                            <input type="search" id="bundleProductSearch" class="form-control form-control-sm" placeholder="Search product name or SKU">
                                        </div>
                                        <div class="bundle-products-list">
                                            @forelse($bundleableProducts as $product)
                                                @php
                                                    $productImage = $product->product_image && file_exists(public_path('uploads/products/' . $product->product_image))
                                                        ? asset('uploads/products/' . $product->product_image)
                                                        : asset('images/logo_nya.png');
                                                    $bundlePrice = $product->client_price ?? $product->price ?? 0;
                                                @endphp
                                                <div class="bundle-product-row js-bundle-row" role="button" tabindex="0" aria-pressed="false" data-checkbox-id="bundleProduct{{ $product->id }}" data-search="{{ strtolower($product->product_name . ' ' . ($product->sku ?: '')) }}">
                                                    <input type="checkbox"
                                                        class="form-check-input mt-0 bundle-product-check js-bundle-product"
                                                        id="bundleProduct{{ $product->id }}"
                                                        name="bundle_product_ids[]"
                                                        value="{{ $product->id }}"
                                                        data-name="{{ $product->product_name }}"
                                                        data-price="{{ $bundlePrice }}"
                                                        data-mega-dealer-price="{{ $product->mega_dealer_price ?? 0 }}"
                                                        data-dealer-price="{{ $product->dealer_price ?? 0 }}"
                                                        data-image-src="{{ $productImage }}"
                                                        {{ in_array($product->id, old('bundle_product_ids', [])) ? 'checked' : '' }}
                                                        hidden>
                                                    <span class="bundle-selected-indicator">
                                                        <i class="bi bi-check"></i>
                                                    </span>
                                                    <img src="{{ $productImage }}" alt="{{ $product->product_name }}">
                                                    <span class="bundle-product-main">
                                                        <span class="d-block fw-bold">{{ $product->product_name }}</span>
                                                        <span class="d-block text-muted small">{{ $product->sku ?: 'No SKU' }}</span>
                                                    </span>
                                                    <span class="bundle-product-price">PHP {{ number_format($bundlePrice, 2) }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted small">No existing products are available.</div>
                                            @endforelse
                                        </div>
                                        <small id="productEmptyMessage" class="text-muted {{ $bundleableProducts->isEmpty() ? '' : 'd-none' }}">No existing products are available for this bundle.</small>
                                        <small id="bundleNoSearchResults" class="text-muted d-none">No products match your search.</small>
                                    </div>
                                    <div class="bundle-summary-panel">
                                        <div class="bundle-summary-title">
                                            <i class="bi bi-receipt"></i>
                                            Bundle Summary
                                        </div>
                                        <div class="bundle-summary-line">
                                            <span>Items</span>
                                            <strong><span id="bundleSummaryCount">0</span></strong>
                                        </div>
                                        <div class="bundle-summary-line">
                                            <span>Subtotal</span>
                                            <strong>PHP <span id="bundleSubtotal">0.00</span></strong>
                                        </div>
                                        <div class="bundle-summary-line">
                                            <span>Bundle price</span>
                                            <strong>PHP <span id="bundleFinalPrice">0.00</span></strong>
                                        </div>
                                        <div class="bundle-summary-line bundle-summary-savings">
                                            <span>Savings</span>
                                            <strong>PHP <span id="bundleSavings">0.00</span></strong>
                                        </div>
                                        <div id="bundleEmptySelection" class="bundle-empty-selection">
                                            Select products to build the preview and totals.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer product-modal-footer">
                        <span class="product-required-note"><span aria-hidden="true">*</span> Required fields</span>
                        <button type="button" class="btn btn-light product-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addProductSubmitButton" class="btn btn-success product-modal-submit" {{ $newProductCount >= $maxNewProducts ? 'disabled' : '' }}><i class="bi bi-check2-circle"></i> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<div class="modal fade product-form-modal" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content product-modal">
            <div class="modal-header">
                <div>
                    <h5 id="editProductModalTitle" class="mb-0">Edit Product</h5>
                    <small id="editProductModalSubtitle" class="text-muted">Update a single product in your catalog.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close edit product"></button>
            </div>
            <form id="editProductForm">
                @csrf
                <input type="hidden" id="edit_id">
                <input type="hidden" id="edit_selected_item_type" name="selected_item_type" value="product">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <div class="bundle-switch-panel">
                                <div>
                                    <div class="bundle-switch-title">
                                        <i class="bi bi-boxes"></i> Bundle
                                    </div>
                                    <div class="bundle-switch-copy">Turn this on to edit this item as a bundle from existing products.</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input js-edit-bundle-toggle" type="checkbox" role="switch" id="editBundleToggle">
                                    <label class="form-check-label fw-bold" for="editBundleToggle">Bundle</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center mb-2">
                            <div class="bundle-visual-shell">
                                <div class="avatar-wrapper product-image-preview mx-auto mb-2">
                                    <img id="edit_preview" alt="Preview">
                                </div>

                                <label for="edit_image" class="product-image-upload">
                                    <i class="bi bi-upload" aria-hidden="true"></i>
                                    <span class="product-image-upload-copy"><strong>Upload image</strong><small>JPG, PNG or GIF · Max 2MB</small></span>
                                </label>

                                <input type="file" name="product_image" id="edit_image" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="uploadImage2(this)" style="display: none">

                                <small id="editProductImageHelp" class="d-block text-muted mt-1">
                                    JPG, PNG (Max: 2MB)
                                </small>
                                <div id="editBundleImagePreview" class="bundle-image-preview d-none mt-3"></div>
                            </div>
                        </div>
                        <div class="col-lg-8 mb-2">
                            <label for="edit_name" id="editProductNameLabel" class="form-label">Product Name&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" class="form-control mb-2" placeholder="Product Name" data-uppercase required>
                            <label for="edit_description" id="editDescriptionLabel" class="form-label">Product Description</label>
                            <textarea type="text" id="edit_description" class="form-control mb-2" rows="3" data-uppercase></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_sku" class="form-label">Stock Keeping Unit (SKU)&nbsp;<span class="text-danger">*</span></label>
                            <input type="text" id="edit_sku" class="form-control mb-2" required placeholder="Enter Stock Keeping Unit" data-uppercase>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_price" id="editPriceLabel" class="form-label">Price (PHP)&nbsp;<span class="text-danger">*</span></label>
                            <input type="number" id="edit_price" class="form-control mb-2" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_mega_dealer_price" class="form-label">Mega Dealer Price (PHP)</label>
                            <input type="number" id="edit_mega_dealer_price" class="form-control mb-2" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_dealer_price" class="form-label">Dealer Price (PHP)</label>
                            <input type="number" id="edit_dealer_price" class="form-control mb-2" step="0.01" min="0" placeholder="0.00">
                        </div>
                        {{-- <div class="col-md-6">
                            <label for="edit_client_price" class="form-label">End User Price (PHP)</label>
                            <input type="number" id="edit_client_price" class="form-control mb-2" step="0.01" min="0" placeholder="0.00">
                        </div> --}}
                        {{-- <div class="col-md-6">
                            <label for="edit_deposit" class="form-label-optional">Deposit (PHP)</label>
                            <input type="number" id="edit_deposit" class="form-control mb-2" step="0.01" min="0" placeholder="0.00">
                        </div> --}}
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select id="edit_status" name="status" class="form-control">
                                <option value="Activate">Activate</option>
                                <option value="Deactivate">Deactivate</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3 d-none" id="editBundleProductsWrap">
                            <div class="bundle-builder-layout">
                                <div class="bundle-products-panel">
                                    <div class="bundle-products-head">
                                        <div>
                                            <label class="form-label mb-0">Products in this bundle&nbsp;<span class="text-danger">*</span></label>
                                            <span class="d-block text-muted small">Choose existing products to combine.</span>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            <span id="editBundleSelectedCount">0</span> items / PHP <span id="editBundleTotal">0.00</span>
                                        </span>
                                    </div>
                                    <div class="bundle-search-wrap">
                                        <i class="bi bi-search"></i>
                                        <input type="search" id="editBundleProductSearch" class="form-control form-control-sm" placeholder="Search product name or SKU">
                                    </div>
                                    <div class="bundle-products-list">
                                        @forelse($bundleableProducts as $product)
                                            @php
                                                $productImage = $product->product_image && file_exists(public_path('uploads/products/' . $product->product_image))
                                                    ? asset('uploads/products/' . $product->product_image)
                                                    : asset('images/logo_nya.png');
                                                $bundlePrice = $product->client_price ?? $product->price ?? 0;
                                            @endphp
                                            <div class="bundle-product-row js-edit-bundle-row" role="button" tabindex="0" aria-pressed="false" data-checkbox-id="editBundleProduct{{ $product->id }}" data-product-id="{{ $product->id }}" data-search="{{ strtolower($product->product_name . ' ' . ($product->sku ?: '')) }}">
                                                <input type="checkbox"
                                                    class="form-check-input mt-0 bundle-product-check js-edit-bundle-product"
                                                    id="editBundleProduct{{ $product->id }}"
                                                    name="bundle_product_ids[]"
                                                    value="{{ $product->id }}"
                                                    data-name="{{ $product->product_name }}"
                                                    data-price="{{ $bundlePrice }}"
                                                    data-mega-dealer-price="{{ $product->mega_dealer_price ?? 0 }}"
                                                    data-dealer-price="{{ $product->dealer_price ?? 0 }}"
                                                    data-image-src="{{ $productImage }}"
                                                    hidden>
                                                <span class="bundle-selected-indicator">
                                                    <i class="bi bi-check"></i>
                                                </span>
                                                <img src="{{ $productImage }}" alt="{{ $product->product_name }}">
                                                <span class="bundle-product-main">
                                                    <span class="d-block fw-bold">{{ $product->product_name }}</span>
                                                    <span class="d-block text-muted small">{{ $product->sku ?: 'No SKU' }}</span>
                                                </span>
                                                <span class="bundle-product-price">PHP {{ number_format($bundlePrice, 2) }}</span>
                                            </div>
                                        @empty
                                            <div class="text-muted small">No existing products are available.</div>
                                        @endforelse
                                    </div>
                                    <small id="editBundleNoSearchResults" class="text-muted d-none">No products match your search.</small>
                                </div>
                                <div class="bundle-summary-panel">
                                    <div class="bundle-summary-title">
                                        <i class="bi bi-receipt"></i>
                                        Bundle Summary
                                    </div>
                                    <div class="bundle-summary-line">
                                        <span>Items</span>
                                        <strong><span id="editBundleSummaryCount">0</span></strong>
                                    </div>
                                    <div class="bundle-summary-line">
                                        <span>Subtotal</span>
                                        <strong>PHP <span id="editBundleSubtotal">0.00</span></strong>
                                    </div>
                                    <div class="bundle-summary-line">
                                        <span>Bundle price</span>
                                        <strong>PHP <span id="editBundleFinalPrice">0.00</span></strong>
                                    </div>
                                    <div class="bundle-summary-line bundle-summary-savings">
                                        <span>Savings</span>
                                        <strong>PHP <span id="editBundleSavings">0.00</span></strong>
                                    </div>
                                    <div id="editBundleEmptySelection" class="bundle-empty-selection">
                                        Select products to build the preview and totals.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer product-modal-footer">
                    <button type="button" class="btn btn-light product-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="editProductSubmitButton" class="btn btn-success product-modal-submit">Update Product</button>
                </div>

            </form>
        </div>
    </div>
</div>


@endsection

@section('js')
    <script src="{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <script>
        $(document).ready(function () {

            if ($.fn.DataTable) {
                const productTable = $('#productsTable').DataTable({
                    pageLength: 10,
                    autoWidth: false,
                    order: [[0, 'asc']],
                    columnDefs: [
                        { orderable: false, searchable: false, targets: [5] }
                    ],
                    dom: 'rt<"product-table-footer"lip>',
                    pagingType: 'simple_numbers',
                    language: {
                        lengthMenu: '_MENU_ per page',
                        info: '_START_–_END_ of _TOTAL_ products',
                        infoEmpty: '0 products',
                        infoFiltered: '',
                        emptyTable: 'No products yet. Add a product to start your catalog.',
                        zeroRecords: 'No products match your filters. Try another name or SKU.',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        }
                    },
                    drawCallback: function () {
                        const info = this.api().page.info();
                        const count = info.recordsDisplay;
                        const label = count === info.recordsTotal ? count : count + ' of ' + info.recordsTotal;
                        $('#productResultCount').text(label + (info.recordsTotal === 1 ? ' product' : ' products'));
                    }
                });

                function filterProducts() {
                    const status = $('#productStatusFilter').val();
                    const search = $('#productSearch').val();
                    productTable.search(search).column(4).search(status ? '^' + status + '$' : '', true, false).draw();
                    document.getElementById('productResetFilters').hidden = !status && !search;
                }
                $('#productSearch').on('input', filterProducts);
                $('#productStatusFilter').on('change', filterProducts);
                $('#productResetFilters').on('click', function () {
                    $('#productSearch, #productStatusFilter').val('');
                    filterProducts();
                    $('#productSearch').trigger('focus');
                });
            }

            const editModal = new bootstrap.Modal(
                document.getElementById('editProductModal')
            );
            const editBundleToggle = document.getElementById('editBundleToggle');
            const editBundleProductsWrap = document.getElementById('editBundleProductsWrap');
            const editBundleProductInputs = document.querySelectorAll('.js-edit-bundle-product');
            const editBundleImagePreview = document.getElementById('editBundleImagePreview');
            const editBundleProductSearch = document.getElementById('editBundleProductSearch');
            const editBundleNoSearchResults = document.getElementById('editBundleNoSearchResults');
            const editSelectedItemTypeInput = document.getElementById('edit_selected_item_type');
            const editPriceInput = document.getElementById('edit_price');
            const editClientPriceInput = document.getElementById('edit_client_price');
            const editDealerPriceInput = document.getElementById('edit_dealer_price');
            const editMegaDealerPriceInput = document.getElementById('edit_mega_dealer_price');
            const editModeEls = {
                title: document.getElementById('editProductModalTitle'),
                subtitle: document.getElementById('editProductModalSubtitle'),
                submit: document.getElementById('editProductSubmitButton'),
                nameLabel: document.getElementById('editProductNameLabel'),
                descriptionLabel: document.getElementById('editDescriptionLabel'),
                priceLabel: document.getElementById('editPriceLabel'),
                imageHelp: document.getElementById('editProductImageHelp'),
                selectedCount: document.getElementById('editBundleSelectedCount'),
                total: document.getElementById('editBundleTotal'),
                summaryCount: document.getElementById('editBundleSummaryCount'),
                subtotal: document.getElementById('editBundleSubtotal'),
                finalPrice: document.getElementById('editBundleFinalPrice'),
                savings: document.getElementById('editBundleSavings'),
                emptySelection: document.getElementById('editBundleEmptySelection')
            };
            let editUploadedProductImagePreviewSrc = '';
            let currentEditProductId = null;

            function editSelectedItemType() {
                return editBundleToggle && editBundleToggle.checked ? 'bundle' : 'product';
            }

            function editSelectedBundleProducts() {
                return Array.from(editBundleProductInputs).filter(function(input) {
                    return input.checked;
                });
            }

            function editFormatMoney(value) {
                const amount = Number.isFinite(value) ? value : 0;
                return amount.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function setEditModeText(type) {
                const isBundle = type === 'bundle';

                if (editModeEls.title) editModeEls.title.textContent = isBundle ? 'Edit Bundle' : 'Edit Product';
                if (editModeEls.subtitle) editModeEls.subtitle.textContent = isBundle ? 'Update bundle details and included products.' : 'Update a single product in your catalog.';
                if (editModeEls.submit) editModeEls.submit.textContent = isBundle ? 'Update Bundle' : 'Update Product';
                if (editModeEls.nameLabel) editModeEls.nameLabel.innerHTML = (isBundle ? 'Bundle Name' : 'Product Name') + '&nbsp;<span class="text-danger">*</span>';
                if (editModeEls.descriptionLabel) editModeEls.descriptionLabel.textContent = isBundle ? 'Bundle Description' : 'Product Description';
                if (editModeEls.priceLabel) editModeEls.priceLabel.innerHTML = (isBundle ? 'Bundle Price (PHP)' : 'Price (PHP)') + '&nbsp;<span class="text-danger">*</span>';
                if (editModeEls.imageHelp) editModeEls.imageHelp.textContent = isBundle ? 'Optional override. If empty, a combined bundle image will be generated.' : 'JPG, PNG (Max: 2MB)';
            }

            function updateEditBundleImagePreview() {
                if (!editBundleImagePreview) return;

                const selectedImages = editSelectedBundleProducts()
                    .map(function(input) { return input.dataset.imageSrc || ''; })
                    .filter(function(image) { return image.length > 0; });
                const allPreviewImages = editUploadedProductImagePreviewSrc
                    ? [editUploadedProductImagePreviewSrc].concat(selectedImages)
                    : selectedImages;
                const previewImages = allPreviewImages.slice(0, 8);

                editBundleImagePreview.classList.toggle('d-none', editSelectedItemType() !== 'bundle' || previewImages.length === 0);

                if (previewImages.length === 0) {
                    editBundleImagePreview.innerHTML = '';
                    return;
                }

                editBundleImagePreview.innerHTML = '<div class="bundle-image-preview-head"><strong>Bundle preview</strong><span>' + allPreviewImages.length + ' image' + (allPreviewImages.length === 1 ? '' : 's') + '</span></div><div class="bundle-image-preview-grid"></div>';
                const grid = editBundleImagePreview.querySelector('.bundle-image-preview-grid');

                previewImages.forEach(function(image) {
                    const img = document.createElement('img');
                    img.src = image;
                    img.alt = 'Bundle image';
                    grid.appendChild(img);
                });
                if (allPreviewImages.length > previewImages.length) {
                    const more = document.createElement('div');
                    more.className = 'bundle-preview-more';
                    more.textContent = '+' + (allPreviewImages.length - previewImages.length);
                    more.setAttribute('aria-label', (allPreviewImages.length - previewImages.length) + ' more bundle images');
                    grid.appendChild(more);
                }
            }

            function updateEditBundleSummary() {
                const selectedProducts = editSelectedBundleProducts();
                const clientTotal = selectedProducts.reduce(function(total, input) {
                    return total + parseFloat(input.dataset.price || 0);
                }, 0);
                const dealerTotal = selectedProducts.reduce(function(total, input) {
                    return total + parseFloat(input.dataset.dealerPrice || 0);
                }, 0);
                const megaDealerTotal = selectedProducts.reduce(function(total, input) {
                    return total + parseFloat(input.dataset.megaDealerPrice || 0);
                }, 0);

                if (editSelectedItemType() === 'bundle') {
                    if (editPriceInput && (!editPriceInput.value || Number(editPriceInput.dataset.autoBundlePrice) === 1)) {
                        editPriceInput.value = clientTotal.toFixed(2);
                        editPriceInput.dataset.autoBundlePrice = '1';
                    }
                }

                const finalPrice = parseFloat(editPriceInput ? editPriceInput.value : 0) || 0;
                const savings = Math.max(clientTotal - finalPrice, 0);

                if (editModeEls.selectedCount) editModeEls.selectedCount.textContent = selectedProducts.length;
                if (editModeEls.summaryCount) editModeEls.summaryCount.textContent = selectedProducts.length;
                if (editModeEls.total) editModeEls.total.textContent = editFormatMoney(clientTotal);
                if (editModeEls.subtotal) editModeEls.subtotal.textContent = editFormatMoney(clientTotal);
                if (editModeEls.finalPrice) editModeEls.finalPrice.textContent = editFormatMoney(finalPrice);
                if (editModeEls.savings) editModeEls.savings.textContent = editFormatMoney(savings);
                if (editModeEls.emptySelection) editModeEls.emptySelection.classList.toggle('d-none', selectedProducts.length > 0);

                editBundleProductInputs.forEach(function(input) {
                    const row = input.closest('.js-edit-bundle-row');
                    if (row) {
                        row.classList.toggle('is-selected', input.checked);
                        row.setAttribute('aria-pressed', input.checked ? 'true' : 'false');
                    }
                });

                if (editSelectedItemType() === 'bundle') {
                    if (editClientPriceInput && (!editClientPriceInput.value || Number(editClientPriceInput.dataset.autoBundlePrice) === 1)) {
                        editClientPriceInput.value = (parseFloat(editPriceInput ? editPriceInput.value : 0) || clientTotal).toFixed(2);
                        editClientPriceInput.dataset.autoBundlePrice = '1';
                    }

                    if (editDealerPriceInput && dealerTotal > 0 && (!editDealerPriceInput.value || Number(editDealerPriceInput.dataset.autoBundlePrice) === 1)) {
                        editDealerPriceInput.value = dealerTotal.toFixed(2);
                        editDealerPriceInput.dataset.autoBundlePrice = '1';
                    }

                    if (editMegaDealerPriceInput && megaDealerTotal > 0 && (!editMegaDealerPriceInput.value || Number(editMegaDealerPriceInput.dataset.autoBundlePrice) === 1)) {
                        editMegaDealerPriceInput.value = megaDealerTotal.toFixed(2);
                        editMegaDealerPriceInput.dataset.autoBundlePrice = '1';
                    }
                }

                updateEditBundleImagePreview();
            }

            function filterEditBundleProductRows() {
                const query = editBundleProductSearch ? editBundleProductSearch.value.trim().toLowerCase() : '';
                let visibleRows = 0;

                document.querySelectorAll('.js-edit-bundle-row').forEach(function(row) {
                    const isCurrentProduct = parseInt(row.dataset.productId || 0, 10) === currentEditProductId;
                    const matches = !isCurrentProduct && (!query || (row.dataset.search || '').indexOf(query) !== -1);
                    row.classList.toggle('d-none', !matches);
                    if (matches) visibleRows++;
                });

                if (editBundleNoSearchResults) {
                    editBundleNoSearchResults.classList.toggle('d-none', visibleRows > 0 || !query);
                }
            }

            function updateEditProductMode() {
                const type = editSelectedItemType();

                if (editSelectedItemTypeInput) editSelectedItemTypeInput.value = type;
                if (editBundleProductsWrap) editBundleProductsWrap.classList.toggle('d-none', type !== 'bundle');
                setEditModeText(type);
                filterEditBundleProductRows();
                updateEditBundleSummary();
            }

            window.refreshEditBundlePreviewFromUpload = function(src) {
                editUploadedProductImagePreviewSrc = src || '';
                updateEditBundleImagePreview();
            };

            // OPEN EDIT MODAL
            $(document).on('click', '.edit-btn', function () {

                $('#edit_id').val($(this).data('id'));
                currentEditProductId = parseInt($(this).data('id'), 10);
                $('#edit_name').val($(this).data('name'));
                $('#edit_description').val($(this).data('description'));
                $('#edit_sku').val($(this).data('sku'));
                $('#edit_price').val($(this).data('price'));
                $('#edit_mega_dealer_price').val($(this).data('mega-dealer-price'));
                $('#edit_dealer_price').val($(this).data('dealer-price'));
                $('#edit_client_price').val($(this).data('client-price'));
                $('#edit_deposit').val($(this).data('deposit'));
                $('#edit_status').val($(this).data('status'));
                $('#edit_preview').attr('src', $(this).data('image'));
                $('#edit_image').val('');
                editUploadedProductImagePreviewSrc = '';

                const itemType = $(this).data('item-type') === 'bundle' ? 'bundle' : 'product';
                const bundleProductIds = JSON.parse($(this).attr('data-bundle-product-ids') || '[]').map(function(id) {
                    return parseInt(id, 10);
                });

                if (editBundleToggle) {
                    editBundleToggle.checked = itemType === 'bundle';
                }

                editBundleProductInputs.forEach(function(input) {
                    const inputProductId = parseInt(input.value, 10);
                    input.checked = inputProductId !== currentEditProductId && bundleProductIds.indexOf(inputProductId) !== -1;
                });

                if (editPriceInput) {
                    delete editPriceInput.dataset.autoBundlePrice;
                }

                if (editClientPriceInput) {
                    delete editClientPriceInput.dataset.autoBundlePrice;
                }

                if (editDealerPriceInput) {
                    delete editDealerPriceInput.dataset.autoBundlePrice;
                }

                if (editMegaDealerPriceInput) {
                    delete editMegaDealerPriceInput.dataset.autoBundlePrice;
                }

                updateEditProductMode();

                editModal.show();
            });

            if (editBundleToggle) {
                editBundleToggle.addEventListener('change', updateEditProductMode);
            }

            if (editBundleProductSearch) {
                editBundleProductSearch.addEventListener('input', filterEditBundleProductRows);
            }

            if (editPriceInput) {
                editPriceInput.addEventListener('input', function() {
                    if (editSelectedItemType() === 'bundle') {
                        delete editPriceInput.dataset.autoBundlePrice;
                        if (editClientPriceInput && (!editClientPriceInput.value || Number(editClientPriceInput.dataset.autoBundlePrice) === 1)) {
                            editClientPriceInput.value = editPriceInput.value;
                            editClientPriceInput.dataset.autoBundlePrice = '1';
                        }
                        updateEditBundleSummary();
                    } else {
                        if (editMegaDealerPriceInput && !editMegaDealerPriceInput.value) {
                            editMegaDealerPriceInput.value = editPriceInput.value;
                        }

                        if (editDealerPriceInput && !editDealerPriceInput.value) {
                            editDealerPriceInput.value = editPriceInput.value;
                        }

                        if (editClientPriceInput && !editClientPriceInput.value) {
                            editClientPriceInput.value = editPriceInput.value;
                        }
                    }
                });
            }

            if (editClientPriceInput) {
                editClientPriceInput.addEventListener('input', function() {
                    delete editClientPriceInput.dataset.autoBundlePrice;
                });
            }

            if (editDealerPriceInput) {
                editDealerPriceInput.addEventListener('input', function() {
                    delete editDealerPriceInput.dataset.autoBundlePrice;
                });
            }

            if (editMegaDealerPriceInput) {
                editMegaDealerPriceInput.addEventListener('input', function() {
                    delete editMegaDealerPriceInput.dataset.autoBundlePrice;
                });
            }

            document.querySelectorAll('.js-edit-bundle-row').forEach(function(row) {
                const toggleEditBundleRow = function() {
                    const checkbox = document.getElementById(row.dataset.checkboxId);

                    if (!checkbox) return;

                    checkbox.checked = !checkbox.checked;
                    updateEditBundleSummary();
                };

                row.addEventListener('click', toggleEditBundleRow);
                row.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleEditBundleRow();
                    }
                });
            });

            editBundleProductInputs.forEach(function(input) {
                input.addEventListener('change', updateEditBundleSummary);
            });


            // UPDATE PRODUCT AJAX
            $('#editProductForm').submit(function (e) {
                e.preventDefault();

                let id = $('#edit_id').val();

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('product_name', $('#edit_name').val());
                formData.append('description', $('#edit_description').val());
                formData.append('sku', $('#edit_sku').val());
                formData.append('price', $('#edit_price').val());
                formData.append('selected_item_type', editSelectedItemType());
                formData.append('client_price', $('#edit_client_price').val() || $('#edit_price').val());
                formData.append('dealer_price', $('#edit_dealer_price').val() || $('#edit_price').val());
                formData.append('mega_dealer_price', $('#edit_mega_dealer_price').val() || $('#edit_price').val());
                formData.append('status', $('#edit_status').val());

                if (editSelectedItemType() === 'bundle') {
                    const selectedBundleProducts = editSelectedBundleProducts();

                    selectedBundleProducts.forEach(function(input) {
                        formData.append('bundle_product_ids[]', input.value);
                    });
                }

                let image = $('#edit_image')[0].files[0];
                if (image) {
                    formData.append('product_image', image);
                }

                $.ajax({
                    url: '/products/' + id,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Product updated successfully!'
                        }).then(() => {
                            location.reload();
                        });
                    },

                    error: function (xhr) {

                        let msg = 'Update failed.';

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(e => e[0])
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: msg
                        });
                    }
                });
            });

        });
    </script>

    <script>
        const addProductForm = document.getElementById('productForm');
        const itemSelect = document.getElementById('item_id');
        const existingItemWrap = document.getElementById('existingItemWrap');
        const existingItemLabel = document.getElementById('existingItemLabel');
        const bundleProductsWrap = document.getElementById('bundleProductsWrap');
        const bundleProductInputs = document.querySelectorAll('.js-bundle-product');
        const bundleSelectedCount = document.getElementById('bundleSelectedCount');
        const bundleSummaryCount = document.getElementById('bundleSummaryCount');
        const bundleTotal = document.getElementById('bundleTotal');
        const bundleSubtotal = document.getElementById('bundleSubtotal');
        const bundleFinalPrice = document.getElementById('bundleFinalPrice');
        const bundleSavings = document.getElementById('bundleSavings');
        const bundleEmptySelection = document.getElementById('bundleEmptySelection');
        const bundleImagePreview = document.getElementById('bundleImagePreview');
        const bundleToggle = document.getElementById('bundleToggle');
        const bundleProductSearch = document.getElementById('bundleProductSearch');
        const bundleNoSearchResults = document.getElementById('bundleNoSearchResults');
        const selectedItemTypeInput = document.getElementById('selected_item_type');
        const itemEmptyMessage = document.getElementById('itemEmptyMessage');
        const productEmptyMessage = document.getElementById('productEmptyMessage');
        const addProductModalTitle = document.getElementById('addProductModalTitle');
        const addProductModalSubtitle = document.getElementById('addProductModalSubtitle');
        const addProductModalKicker = document.getElementById('addProductModalKicker');
        const addProductModalContent = document.querySelector('#addProductModal .product-modal');
        const addProductSubmitButton = document.getElementById('addProductSubmitButton');
        const inputImageLabel = document.getElementById('inputImageLabel');
        const productImagePreviewFrame = document.getElementById('productImagePreviewFrame');
        const productNameLabel = document.getElementById('productNameLabel');
        const descriptionLabel = document.getElementById('descriptionLabel');
        const priceLabel = document.getElementById('priceLabel');
        const productImageHelp = document.getElementById('productImageHelp');
        const priceInput = document.getElementById('price');
        const megaDealerPriceInput = document.getElementById('mega_dealer_price');
        const dealerPriceInput = document.getElementById('dealer_price');
        const clientPriceInput = document.getElementById('client_price');
        let uploadedProductImagePreviewSrc = '';
        const allItemOptions = itemSelect
            ? Array.from(itemSelect.options)
                .filter(function(option) {
                    return option.value;
                })
                .map(function(option) {
                    return {
                        value: option.value,
                        text: option.textContent.trim(),
                        type: option.dataset.type || 'product',
                        name: option.dataset.name || '',
                        description: option.dataset.description || '',
                        price: option.dataset.price || '',
                        sku: option.dataset.sku || '',
                        image: option.dataset.image || '',
                        selected: option.selected
                    };
                })
            : [];

        function selectedItemType() {
            return bundleToggle && bundleToggle.checked ? 'bundle' : 'product';
        }

        function resetProductFields() {
            const avatar = document.getElementById('avatar');
            const defaultImage = '{{ asset('images/logo_nya.png') }}';

            setFieldValue('product_name', '');
            setFieldValue('description', '');
            setFieldValue('sku', '');
            setFieldValue('price', '');
            setFieldValue('mega_dealer_price', '');
            setFieldValue('dealer_price', '');
            setFieldValue('client_price', '');
            setFieldValue('deposit', '');

            if (avatar) {
                avatar.src = defaultImage;
            }
        }

        function setFieldValue(id, value) {
            const field = document.getElementById(id);
            if (field) {
                field.value = value || '';
            }
        }

        function setAvatar(image) {
            const avatar = document.getElementById('avatar');
            const defaultImage = '{{ asset('images/logo_nya.png') }}';

            if (avatar) {
                avatar.src = image || defaultImage;
            }
        }

        function filterExistingItems() {
            if (!itemSelect) return;

            const type = selectedItemType();
            const currentValue = itemSelect.value;
            const matchingItems = allItemOptions.filter(function(item) {
                return item.type === type;
            });

            itemSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = type === 'bundle' ? 'Select existing bundle' : 'Select existing product';
            itemSelect.appendChild(placeholder);

            matchingItems.forEach(function(item) {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                option.dataset.type = item.type;
                option.dataset.name = item.name;
                option.dataset.description = item.description;
                option.dataset.price = item.price;
                option.dataset.sku = item.sku;
                option.dataset.image = item.image;

                if (item.value === currentValue) {
                    option.selected = true;
                }

                itemSelect.appendChild(option);
            });

            if (itemSelect.selectedIndex < 0 || itemSelect.options[itemSelect.selectedIndex].value === '') {
                itemSelect.value = '';
                resetProductFields();
            }

            if (itemEmptyMessage) {
                itemEmptyMessage.classList.toggle('d-none', matchingItems.length > 0);
            }

        }

        function fillProductFromItem() {
            if (!itemSelect || !itemSelect.value) {
                resetProductFields();
                return;
            }

            const selected = itemSelect.options[itemSelect.selectedIndex];
            const currentType = selectedItemType();

            if ((selected.dataset.type || 'product') !== currentType) {
                itemSelect.value = '';
                resetProductFields();
                return;
            }

            setFieldValue('product_name', selected.dataset.name);
            setFieldValue('description', selected.dataset.description);
            setFieldValue('sku', selected.dataset.sku);
            setFieldValue('price', selected.dataset.price);
            setFieldValue('mega_dealer_price', selected.dataset.price);
            setFieldValue('dealer_price', selected.dataset.price);
            setFieldValue('client_price', selected.dataset.price);
            setAvatar(selected.dataset.image);
        }

        function selectedBundleProducts() {
            return Array.from(bundleProductInputs).filter(function(input) {
                return input.checked;
            });
        }

        function formatMoney(value) {
            const amount = Number.isFinite(value) ? value : 0;
            return amount.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setBundleModeText(type) {
            const isBundle = type === 'bundle';

            if (addProductModalContent) {
                addProductModalContent.classList.toggle('is-bundle-mode', isBundle);
            }

            if (addProductModalKicker) {
                addProductModalKicker.textContent = isBundle ? 'Bundle builder' : 'Catalog item';
            }

            if (addProductModalTitle) {
                addProductModalTitle.textContent = isBundle ? 'Add Bundle' : 'Add Product';
            }

            if (addProductModalSubtitle) {
                addProductModalSubtitle.textContent = isBundle
                    ? 'Combine existing products and set one bundle price.'
                    : 'Create a single product for your catalog.';
            }

            if (addProductSubmitButton) {
                addProductSubmitButton.textContent = isBundle ? 'Save Bundle' : 'Save Product';
            }

            if (productNameLabel) {
                productNameLabel.innerHTML = (isBundle ? 'Bundle Name' : 'Product Name') + '&nbsp;<span class="text-danger">*</span>';
            }

            if (descriptionLabel) {
                descriptionLabel.textContent = isBundle ? 'Bundle Description' : 'Product Description';
            }

            if (priceLabel) {
                priceLabel.innerHTML = (isBundle ? 'Bundle Price (PHP)' : 'Price (PHP)') + '&nbsp;<span class="text-danger">*</span>';
            }

            if (productImageHelp) {
                productImageHelp.textContent = isBundle
                    ? 'Optional override. If empty, a combined bundle image will be generated.'
                    : 'Optional override. JPG, PNG, or GIF (Max: 2MB)';
            }
        }

        function updateBundleImagePreview() {
            if (!bundleImagePreview) {
                return;
            }

            const selectedImages = selectedBundleProducts()
                .map(function(input) {
                    return input.dataset.imageSrc || '';
                })
                .filter(function(image) {
                    return image.length > 0;
                });
            const allPreviewImages = uploadedProductImagePreviewSrc
                ? [uploadedProductImagePreviewSrc].concat(selectedImages)
                : selectedImages;
            const previewImages = allPreviewImages.slice(0, 8);

            bundleImagePreview.classList.toggle('d-none', selectedItemType() !== 'bundle' || previewImages.length === 0);

            if (previewImages.length === 0) {
                bundleImagePreview.innerHTML = '';
                return;
            }

            bundleImagePreview.innerHTML = '<div class="bundle-image-preview-head"><strong>Bundle preview</strong><span>' + allPreviewImages.length + ' image' + (allPreviewImages.length === 1 ? '' : 's') + '</span></div><div class="bundle-image-preview-grid"></div>';
            const grid = bundleImagePreview.querySelector('.bundle-image-preview-grid');

            previewImages.forEach(function(image) {
                const img = document.createElement('img');
                img.src = image;
                img.alt = 'Bundle image';
                grid.appendChild(img);
            });
            if (allPreviewImages.length > previewImages.length) {
                const more = document.createElement('div');
                more.className = 'bundle-preview-more';
                more.textContent = '+' + (allPreviewImages.length - previewImages.length);
                more.setAttribute('aria-label', (allPreviewImages.length - previewImages.length) + ' more bundle images');
                grid.appendChild(more);
            }
        }

        function updateBundleSummary() {
            const selectedProducts = selectedBundleProducts();
            const clientTotal = selectedProducts.reduce(function(total, input) {
                return total + parseFloat(input.dataset.price || 0);
            }, 0);
            const dealerTotal = selectedProducts.reduce(function(total, input) {
                return total + parseFloat(input.dataset.dealerPrice || 0);
            }, 0);
            const megaDealerTotal = selectedProducts.reduce(function(total, input) {
                return total + parseFloat(input.dataset.megaDealerPrice || 0);
            }, 0);
            if (selectedItemType() === 'bundle') {
                if (priceInput && (!priceInput.value || Number(priceInput.dataset.autoBundlePrice) === 1)) {
                    priceInput.value = clientTotal.toFixed(2);
                    priceInput.dataset.autoBundlePrice = '1';
                }
            }

            const finalPrice = parseFloat(priceInput ? priceInput.value : 0) || 0;
            const savings = Math.max(clientTotal - finalPrice, 0);

            if (bundleSelectedCount) {
                bundleSelectedCount.textContent = selectedProducts.length;
            }

            if (bundleSummaryCount) {
                bundleSummaryCount.textContent = selectedProducts.length;
            }

            if (bundleTotal) {
                bundleTotal.textContent = formatMoney(clientTotal);
            }

            if (bundleSubtotal) {
                bundleSubtotal.textContent = formatMoney(clientTotal);
            }

            if (bundleFinalPrice) {
                bundleFinalPrice.textContent = formatMoney(finalPrice);
            }

            if (bundleSavings) {
                bundleSavings.textContent = formatMoney(savings);
            }

            if (bundleEmptySelection) {
                bundleEmptySelection.classList.toggle('d-none', selectedProducts.length > 0);
            }

            bundleProductInputs.forEach(function(input) {
                const row = input.closest('.js-bundle-row');
                if (row) {
                    row.classList.toggle('is-selected', input.checked);
                    row.setAttribute('aria-pressed', input.checked ? 'true' : 'false');
                }
            });

            if (selectedItemType() === 'bundle') {
                if (clientPriceInput && (!clientPriceInput.value || Number(clientPriceInput.dataset.autoBundlePrice) === 1)) {
                    clientPriceInput.value = (parseFloat(priceInput ? priceInput.value : 0) || clientTotal).toFixed(2);
                    clientPriceInput.dataset.autoBundlePrice = '1';
                }

                if (dealerPriceInput && dealerTotal > 0 && (!dealerPriceInput.value || Number(dealerPriceInput.dataset.autoBundlePrice) === 1)) {
                    dealerPriceInput.value = dealerTotal.toFixed(2);
                    dealerPriceInput.dataset.autoBundlePrice = '1';
                }

                if (megaDealerPriceInput && megaDealerTotal > 0 && (!megaDealerPriceInput.value || Number(megaDealerPriceInput.dataset.autoBundlePrice) === 1)) {
                    megaDealerPriceInput.value = megaDealerTotal.toFixed(2);
                    megaDealerPriceInput.dataset.autoBundlePrice = '1';
                }
            } else {
                if (priceInput) {
                    delete priceInput.dataset.autoBundlePrice;
                }
            }

            updateBundleImagePreview();
        }

        function filterBundleProductRows() {
            const query = bundleProductSearch ? bundleProductSearch.value.trim().toLowerCase() : '';
            let visibleRows = 0;

            document.querySelectorAll('.js-bundle-row').forEach(function(row) {
                const matches = !query || (row.dataset.search || '').indexOf(query) !== -1;
                row.classList.toggle('d-none', !matches);

                if (matches) {
                    visibleRows++;
                }
            });

            if (bundleNoSearchResults) {
                bundleNoSearchResults.classList.toggle('d-none', visibleRows > 0 || !query);
            }
        }

        function updateAddProductMode() {
            const type = selectedItemType();

            if (selectedItemTypeInput) {
                selectedItemTypeInput.value = type;
            }

            setBundleModeText(type);

            if (type === 'bundle') {
                if (existingItemWrap) {
                    existingItemWrap.classList.add('d-none');
                }

                if (existingItemLabel) {
                    existingItemLabel.innerHTML = 'Existing Product Item&nbsp;<span class="text-danger">*</span>';
                }

                if (bundleProductsWrap) {
                    bundleProductsWrap.classList.remove('d-none');
                }

                if (itemSelect) {
                    itemSelect.required = false;
                    itemSelect.value = '';
                }

                if (productEmptyMessage) {
                    productEmptyMessage.classList.toggle('d-none', bundleProductInputs.length > 0);
                }

                filterBundleProductRows();
                updateBundleSummary();
                return;
            }

            if (existingItemWrap) {
                existingItemWrap.classList.remove('d-none');
            }

            if (existingItemLabel) {
                existingItemLabel.innerHTML = 'Existing Product Item&nbsp;<span class="text-danger">*</span>';
            }

            if (bundleProductsWrap) {
                bundleProductsWrap.classList.add('d-none');
            }

            bundleProductInputs.forEach(function(input) {
                input.checked = false;
            });

            updateBundleSummary();

            if (itemSelect) {
                itemSelect.required = true;
            }

            if (itemSelect) {
                filterExistingItems();
                fillProductFromItem();
            }
        }

        if (itemSelect) {
            itemSelect.addEventListener('change', fillProductFromItem);
        }

        if (bundleToggle) {
            bundleToggle.addEventListener('change', updateAddProductMode);
        }

        if (bundleProductSearch) {
            bundleProductSearch.addEventListener('input', filterBundleProductRows);
        }

        if (priceInput) {
            priceInput.addEventListener('input', function() {
                if (selectedItemType() === 'bundle') {
                    delete priceInput.dataset.autoBundlePrice;
                    if (clientPriceInput && (!clientPriceInput.value || Number(clientPriceInput.dataset.autoBundlePrice) === 1)) {
                        clientPriceInput.value = priceInput.value;
                        clientPriceInput.dataset.autoBundlePrice = '1';
                    }
                    updateBundleSummary();
                } else {
                    if (dealerPriceInput && !dealerPriceInput.value) {
                        dealerPriceInput.value = priceInput.value;
                    }

                    if (clientPriceInput && !clientPriceInput.value) {
                        clientPriceInput.value = priceInput.value;
                    }

                    if (megaDealerPriceInput && !megaDealerPriceInput.value) {
                        megaDealerPriceInput.value = priceInput.value;
                    }
                }
            });
        }

        if (clientPriceInput) {
            clientPriceInput.addEventListener('input', function() {
                delete clientPriceInput.dataset.autoBundlePrice;
            });
        }

        if (dealerPriceInput) {
            dealerPriceInput.addEventListener('input', function() {
                delete dealerPriceInput.dataset.autoBundlePrice;
            });
        }

        if (megaDealerPriceInput) {
            megaDealerPriceInput.addEventListener('input', function() {
                delete megaDealerPriceInput.dataset.autoBundlePrice;
            });
        }

        document.querySelectorAll('.js-bundle-row').forEach(function(row) {
            const toggleBundleRow = function() {
                const checkbox = document.getElementById(row.dataset.checkboxId);

                if (!checkbox) {
                    return;
                }

                checkbox.checked = !checkbox.checked;
                updateBundleSummary();
            };

            row.addEventListener('click', toggleBundleRow);
            row.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleBundleRow();
                }
            });
        });

        bundleProductInputs.forEach(function(input) {
            input.addEventListener('change', updateBundleSummary);
        });

        updateAddProductMode();

        let isSavingProduct = false;

        addProductForm.addEventListener('submit', function (e) {
            if (isSavingProduct) {
                e.preventDefault();
                return;
            }

            if (!addProductForm.checkValidity()) {
                addProductForm.reportValidity();
                e.preventDefault();
                return;
            }

            if (selectedItemType() === 'bundle' && selectedBundleProducts().length === 0) {
                e.preventDefault();
                Swal.fire('Select Products', 'Please select at least one existing product to include in this bundle.', 'error');
                return;
            }

            isSavingProduct = true;
            if (addProductSubmitButton) {
                addProductSubmitButton.disabled = true;
                addProductSubmitButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Saving ' + (selectedItemType() === 'bundle' ? 'Bundle' : 'Product') + '...</span>';
            }
        });

        function uploadImage(input) {
            const file = input.files[0];

            if (!file) {
                uploadedProductImagePreviewSrc = '';
                if (inputImageLabel) inputImageLabel.querySelector('strong').textContent = 'Upload image';
                if (productImagePreviewFrame) productImagePreviewFrame.classList.remove('is-selected');
                updateBundleImagePreview();
                return;
            }

            if (!file.type.startsWith('image/')) {
                Swal.fire('Error', 'Invalid image file.', 'error');
                input.value = '';
                uploadedProductImagePreviewSrc = '';
                updateBundleImagePreview();
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                Swal.fire('Error', 'Image must be less than 2MB.', 'error');
                input.value = '';
                uploadedProductImagePreviewSrc = '';
                updateBundleImagePreview();
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                uploadedProductImagePreviewSrc = e.target.result;
                document.getElementById('avatar').src = e.target.result;
                if (inputImageLabel) inputImageLabel.querySelector('strong').textContent = file.name;
                if (productImagePreviewFrame) productImagePreviewFrame.classList.add('is-selected');
                updateBundleImagePreview();
            };

            reader.readAsDataURL(file);
        }

        function uploadImage2(input) {
            const file = input.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                Swal.fire('Error', 'Invalid image file.', 'error');
                input.value = '';
                if (window.refreshEditBundlePreviewFromUpload) {
                    window.refreshEditBundlePreviewFromUpload('');
                }
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                Swal.fire('Error', 'Image must be less than 2MB.', 'error');
                input.value = '';
                if (window.refreshEditBundlePreviewFromUpload) {
                    window.refreshEditBundlePreviewFromUpload('');
                }
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('edit_preview').src = e.target.result;
                if (window.refreshEditBundlePreviewFromUpload) {
                    window.refreshEditBundlePreviewFromUpload(e.target.result);
                }
            };

            reader.readAsDataURL(file);
        }

    </script>


    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}"
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const addProductModal = document.getElementById('addProductModal');
                if (addProductModal) {
                    new bootstrap.Modal(addProductModal).show();
                }
            });
        </script>
    @endif
@endsection
