@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}?v=20261123-2">
@endsection

@section('content')
@php
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('type');
@endphp
<div class="items-page">
    <header class="items-head">
        <div>
            <div class="items-eyebrow">Catalog</div>
            <h1 class="items-title">Items</h1>
            <p class="items-copy">Manage item details, price tiers, and availability.</p>
        </div>
        <button class="btn btn-primary items-add" type="button" data-bs-toggle="modal" data-bs-target="#itemCreateModal">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Add item
        </button>
    </header>

    @if($errors->any())
        <div class="alert alert-danger mb-0" role="alert"><strong>Please check the form.</strong> {{ $errors->first() }}</div>
    @endif
    @if(session('success'))<div class="alert alert-success mb-0" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger mb-0" role="alert">{{ session('error') }}</div>@endif

    <section class="items-panel" aria-labelledby="items-list-title">
        <div class="items-panel-heading">
            <div><h2 id="items-list-title">Item list</h2><p>Prices in Philippine pesos (PHP).</p></div>
            <span class="items-count">{{ number_format($items->count()) }} {{ $items->count() === 1 ? 'item' : 'items' }}</span>
        </div>
        <form method="GET" action="{{ route('items') }}" class="items-filters">
            <div class="items-search">
                <label for="items-search">Search items</label>
                <input id="items-search" type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Item name or description">
            </div>
            <div>
                <label for="items-status">Status</label>
                <select id="items-status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="Activate" @if(request('status') === 'Activate') selected @endif>Active</option>
                    <option value="Deactivate" @if(request('status') === 'Deactivate') selected @endif>Inactive</option>
                </select>
            </div>
            <div>
                <label for="items-type">Availability</label>
                <select id="items-type" name="type" class="form-select">
                    <option value="">All items</option>
                    <option value="regular" @if(request('type') === 'regular') selected @endif>Regular</option>
                    <option value="ad" @if(request('type') === 'ad') selected @endif>For AD</option>
                </select>
            </div>
            <div class="items-filter-actions">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filter</button>
                @if($hasFilters)<a href="{{ route('items') }}" class="btn btn-outline-secondary">Clear</a>@endif
            </div>
        </form>
        <div class="items-table-wrap" role="region" aria-label="Items and prices" tabindex="0">
            <table class="table items-table">
                <thead><tr><th scope="col">Item</th><th scope="col">Pricing (PHP)</th><th scope="col">Points</th><th scope="col">Availability</th><th scope="col" class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $imagePath = $item->item_image && file_exists(public_path('uploads/products/' . $item->item_image))
                                ? asset('uploads/products/' . $item->item_image) : null;
                            $isBundleItem = ($item->item_type ?? 'product') === 'bundle';
                        @endphp
                        <tr>
                            <td class="item-identity-cell">
                                <div class="item-identity">
                                    <span class="item-thumb">
                                        @if($imagePath)<img src="{{ $imagePath }}" alt="" loading="lazy">@else<i class="bi bi-box-seam" aria-hidden="true"></i>@endif
                                    </span>
                                    <div><div class="item-name">{{ $item->item }}</div><div class="item-description">{{ \Illuminate\Support\Str::limit($item->item_description, 120) ?: 'No description' }}</div></div>
                                </div>
                            </td>
                            <td class="item-pricing-cell">
                                <div class="item-price-matrix" role="table" aria-label="{{ $item->item }} prices in PHP">
                                    <div class="item-price-row item-price-head" role="row"><span role="columnheader">Price tier</span><span role="columnheader">Standard</span><span role="columnheader" class="item-price-vismin">VisMin</span></div>
                                    @foreach(\App\Item::PRICE_TIERS as $priceField => $priceLabel)
                                        @php $visminValue = $item->{'vismin_' . $priceField}; @endphp
                                        <div class="item-price-row" role="row">
                                            <span role="rowheader">{{ $priceLabel }}</span>
                                            <span role="cell">₱{{ number_format($item->{$priceField} ?? 0, 2) }}</span>
                                            <span role="cell" class="item-price-vismin {{ $visminValue === null ? 'is-unset' : '' }}" @if($visminValue === null) title="Uses the standard {{ $priceLabel }} price" @endif>{{ $visminValue === null ? 'Not set' : '₱' . number_format($visminValue, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="item-points-cell">
                                <span class="item-mobile-label">Points</span>
                                <dl class="item-points"><div><dt>Dealer</dt><dd>{{ number_format($item->dealer_points ?? 0) }}</dd></div><div><dt>Customer</dt><dd>{{ number_format($item->customer_points ?? 0) }}</dd></div></dl>
                            </td>
                            <td class="item-availability-cell">
                                <span class="item-mobile-label">Availability</span>
                                <div class="item-badges">
                                    <span class="item-badge {{ $item->status === 'Activate' ? 'active' : 'inactive' }}">{{ $item->status === 'Activate' ? 'Active' : 'Inactive' }}</span>
                                    <span class="item-badge">{{ $item->for_ad ? 'For AD' : 'Regular' }}</span>
                                    <span class="item-badge {{ $isBundleItem ? 'bundle' : '' }}">{{ $isBundleItem ? 'Bundle' : 'Product' }}</span>
                                </div>
                            </td>
                            <td class="item-actions-cell">
                                <div class="item-actions">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#itemEditModal{{ $item->id }}" aria-label="Edit {{ $item->item }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</button>
                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger item-delete" type="submit" aria-label="Delete {{ $item->item }}" title="Delete item"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="items-empty-row"><td colspan="5"><div class="item-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No items found</strong><span>{{ $hasFilters ? 'Try another search or clear your filters.' : 'Add an item to start your catalog.' }}</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@foreach($items as $item)
    @include('items.partials.form-modal', [
        'item' => $item,
        'modalId' => 'itemEditModal' . $item->id,
        'title' => 'Edit item',
        'action' => route('items.update', $item->id),
        'method' => 'PUT',
    ])
@endforeach
@include('items.partials.form-modal', [
    'item' => null,
    'modalId' => 'itemCreateModal',
    'title' => 'Add item',
    'action' => route('items.store'),
    'method' => 'POST',
])
@endsection
