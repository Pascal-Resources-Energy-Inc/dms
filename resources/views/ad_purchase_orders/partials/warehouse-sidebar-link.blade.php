@php
    $deskUser = $sidebarUser ?? auth()->user();
    $deskWarehouse = strtolower(trim((string) $deskUser->warehouse));
    $plantDesk = $deskWarehouse === 'lubao';
    $deskPermission = $plantDesk ? 'purchase_orders.plant' : 'purchase_orders.region_v';
    $deskRoute = $plantDesk ? 'warehouse-ad-purchase-orders.plant' : 'warehouse-ad-purchase-orders.region-v';
@endphp
@if($deskUser->role === 'Admin' && in_array($deskWarehouse, ['lubao', 'guinobatan'], true) && \App\Support\ModulePermissions::allows($deskUser, $deskPermission))
    @php
        $pendingDeskOrders = \App\AdPurchaseOrder::forWarehouse($deskWarehouse)->where('status', 'Pending')->count();
    @endphp
    <div class="nav-item">
        <a href="{{ route($deskRoute) }}" class="nav-link {{ in_array(Route::currentRouteName(), [$deskRoute, 'ad-purchase-orders.index', 'ad-purchase-orders.show']) ? 'active' : '' }} position-relative">
            <div class="nav-icon position-relative">
                <i class="ti ti-receipt"></i>
                @if($pendingDeskOrders > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" aria-label="{{ $pendingDeskOrders }} pending orders">{{ $pendingDeskOrders > 99 ? '99+' : $pendingDeskOrders }}</span>
                @endif
            </div>
            <span class="nav-text">{{ $plantDesk ? 'Plant Orders' : 'Purchase Orders' }}</span>
        </a>
    </div>
@endif
