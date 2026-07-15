@extends('layouts.header')

@section('css')
<style>
    .items-page { display: grid; gap: 16px; }
    .items-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; }
    .items-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .items-copy { margin: 4px 0 0; color: #667085; font-size: 13px; }
    .items-panel { overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .items-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .items-filters { display: grid; grid-template-columns: minmax(240px, 1fr) 150px 150px auto auto; gap: 8px; width: min(100%, 760px); }
    .items-search { position: relative; }
    .items-search i { position: absolute; left: 11px; top: 50%; color: #98a2b3; transform: translateY(-50%); pointer-events: none; }
    .items-search .form-control { padding-left: 34px; }
    .items-table { margin: 0; }
    .items-table th { padding: 12px 14px; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #edf0f5; white-space: nowrap; }
    .items-table td { padding: 14px; border-color: #f1f3f6; vertical-align: middle; }
    .item-thumb { width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #edf0f5; border-radius: 8px; background: #f8fafc; color: #98a2b3; font-size: 20px; }
    .item-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .item-thumb.is-bundle { position: relative; padding: 5px; border-color: #bbf7d0; background: linear-gradient(135deg, #f8fafc 0%, #ecfdf5 100%); }
    .item-thumb.is-bundle img { position: relative; z-index: 1; object-fit: contain; border: 1px solid #fff; border-radius: 6px; background: #fff; box-shadow: 0 3px 8px rgba(15, 23, 42, .10); }
    .item-thumb.is-bundle::after { content: "\F3F8"; font-family: bootstrap-icons; position: absolute; right: 3px; bottom: 3px; z-index: 2; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 999px; background: #15803d; color: #fff; font-size: 9px; }
    .item-name { color: #101828; font-weight: 900; }
    .item-description { max-width: 420px; color: #667085; font-size: 12px; }
    .item-price { color: #0f766e; font-weight: 900; white-space: nowrap; }
    .item-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; border-radius: 999px; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .item-badge.active { background: #dcfce7; color: #166534; }
    .item-badge.inactive { background: #fee2e2; color: #991b1b; }
    .item-badge.ad { background: #e0f2fe; color: #075985; }
    .item-badge.regular { background: #f2f4f7; color: #475467; }
    .item-badge.bundle { background: #fef3c7; color: #92400e; }
    .item-badge.product { background: #e0f2fe; color: #075985; }
    .item-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .item-icon-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    .item-empty { padding: 52px 16px; text-align: center; color: #667085; }
    .item-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .item-form-span { grid-column: 1 / -1; }
    .item-preview { width: 78px; height: 78px; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #edf0f5; border-radius: 8px; background: #f8fafc; color: #98a2b3; }
    .item-preview img { width: 100%; height: 100%; object-fit: cover; }
    .stove-kit-colors { grid-column: 1 / -1; padding: 14px; border: 1px solid #e7eaf0; border-radius: 8px; background: #fcfcfd; }
    .stove-kit-color-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .stove-kit-color-toggle { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px; border: 1px solid #edf0f5; border-radius: 8px; background: #fff; }
    .stove-kit-color-name { color: #344054; font-size: 12px; font-weight: 800; }
    @media (max-width: 992px) {
        .items-head, .items-panel-head { align-items: stretch; flex-direction: column; }
        .items-filters { grid-template-columns: 1fr; width: 100%; }
        .items-panel { overflow-x: auto; }
        .items-table { min-width: 980px; }
    }
    @media (max-width: 576px) {
        .item-form-grid { grid-template-columns: 1fr; }
        .stove-kit-color-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('type');
@endphp

<div class="items-page">
    <div class="items-head">
        <div>
            <h4 class="items-title">Items</h4>
            <p class="items-copy">Manage master item records used for products and AD purchase orders.</p>
        </div>
        <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#itemCreateModal">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-0">
            <strong>Please check the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0">{{ session('error') }}</div>
    @endif

    <div class="items-panel">
        <div class="items-panel-head">
            <div>
                <div class="fw-bold text-dark">Item List</div>
                <div class="text-muted small">{{ number_format($items->count()) }} item(s) found</div>
            </div>
            <form method="GET" action="{{ route('items') }}" class="items-filters">
                <div class="items-search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search item or description">
                </div>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Activate" @if(request('status') === 'Activate') selected @endif>Activate</option>
                    <option value="Deactivate" @if(request('status') === 'Deactivate') selected @endif>Deactivate</option>
                </select>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="regular" @if(request('type') === 'regular') selected @endif>Regular</option>
                    <option value="ad" @if(request('type') === 'ad') selected @endif>For AD</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary" type="submit">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if($hasFilters)
                    <a href="{{ route('items') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <table class="table items-table align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Points</th>
                    <th>Type</th>
                    <th>Item Kind</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $imagePath = $item->item_image && file_exists(public_path('uploads/products/' . $item->item_image))
                            ? asset('uploads/products/' . $item->item_image)
                            : null;
                        $isBundleItem = ($item->item_type ?? 'product') === 'bundle';
                    @endphp
                    <tr>
                        <td>
                            <span class="{{ $isBundleItem ? 'item-thumb is-bundle' : 'item-thumb' }}">
                                @if($imagePath)
                                    <img src="{{ $imagePath }}" alt="{{ $item->item }}">
                                @else
                                    <i class="bi bi-image"></i>
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="item-name">{{ $item->item }}</div>
                            <div class="item-description">{{ \Illuminate\Support\Str::limit($item->item_description, 120) ?: 'No description' }}</div>
                        </td>
                        <td>
                            <div class="item-price">SRP: PHP {{ number_format($item->price, 2) }}</div>
                            <div class="small text-muted">Dealer: PHP {{ number_format($item->dealer_price ?? 0, 2) }}</div>
                            <div class="small text-muted">Mega Dealer: PHP {{ number_format($item->md_price ?? 0, 2) }}</div>
                            <div class="small text-muted">Distributor: PHP {{ number_format($item->dprice ?? 0, 2) }}</div>
                        </td>
                        <td>
                            <div class="small text-muted">Dealer: {{ number_format($item->dealer_points ?? 0) }}</div>
                            <div class="small text-muted">Customer: {{ number_format($item->customer_points ?? 0) }}</div>
                        </td>
                        <td>
                            @if($item->for_ad)
                                <span class="item-badge ad"><i class="bi bi-person-badge"></i> For AD</span>
                            @else
                                <span class="item-badge regular"><i class="bi bi-box"></i> Regular</span>
                            @endif
                        </td>
                        <td>
                            @if($isBundleItem)
                                <span class="item-badge bundle"><i class="bi bi-boxes"></i> Bundle</span>
                            @else
                                <span class="item-badge product"><i class="bi bi-box-seam"></i> Product</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status === 'Activate')
                                <span class="item-badge active"><i class="bi bi-circle-fill"></i> Activate</span>
                            @else
                                <span class="item-badge inactive"><i class="bi bi-circle-fill"></i> Deactivate</span>
                            @endif
                        </td>
                        <td>
                            <div class="item-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary item-icon-btn" data-bs-toggle="modal" data-bs-target="#itemEditModal{{ $item->id }}" title="Edit item">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger item-icon-btn" type="submit" title="Delete item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="item-empty">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <div class="fw-bold text-dark">No items found</div>
                                <div>Create an item or adjust your filters.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($items as $item)
        @include('items.partials.form-modal', [
            'item' => $item,
            'modalId' => 'itemEditModal' . $item->id,
            'title' => 'Edit Item',
            'action' => route('items.update', $item->id),
            'method' => 'PUT',
        ])
    @endforeach
</div>

@include('items.partials.form-modal', [
    'item' => null,
    'modalId' => 'itemCreateModal',
    'title' => 'Add Item',
    'action' => route('items.store'),
    'method' => 'POST',
])
@endsection
