@php
    $personnelNavigation = \App\Support\ModulePermissions::personnelNavigation($sidebarUser);
    $personnelLinks = $personnelNavigation->flatMap(function ($group) { return $group['links']; })->keyBy('key');
    $personnelRoute = Route::currentRouteName();
    $personnelLinkIsActive = function ($link) use ($personnelRoute) {
        return $personnelRoute && ($personnelRoute === $link['route'] || in_array($personnelRoute, array_merge(...array_values($link['routes'])), true));
    };
    $personnelLabels = [
        'transactions.customer_overview' => 'Customer Transaction',
        'products.catalog' => 'Products', 'charges.records' => 'Charges and Discount',
        'distributors.records' => 'Area Distributor', 'dealers.mega' => 'Mega Dealer', 'dealers.records' => 'Dealer',
        'reports.other_charges' => 'Other Charges Transactions',
        'forms.borrowing' => 'Borrowing Marketing Collateral', 'forms.layout' => 'Layout Design Requests',
        'forms.training' => 'Sign Up Sheet | Repair Training Course',
    ];
    // Match the distributor portal's menu order, groups, labels, and icons.
    $personnelMenus = [
        ['icon' => 'layout-dashboard', 'links' => ['dashboard.overview']],
        ['icon' => 'chart-bar', 'links' => ['transactions.customer_overview']],
        ['id' => 'suppliesMenu', 'label' => 'Inventory and Pricing', 'icon' => 'shopping-cart-plus',
            'links' => ['products.catalog', 'charges.records', 'inventory_transfers.transfers']],
        ['id' => 'partnersMenu', 'label' => 'Partners', 'icon' => 'id-badge-2',
            'links' => ['distributors.records', 'dealers.mega', 'dealers.records', 'customers.records']],
        ['icon' => 'building-store', 'links' => ['orders.sales_orders'], 'badge' => $pendingOrdersCount ?? 0],
        ['icon' => 'user-plus', 'links' => ['orders.guest']],
        ['icon' => 'receipt', 'links' => ['purchase_orders.adpo']],
        ['icon' => 'building-warehouse', 'links' => ['purchase_orders.provincial']],
        ['id' => 'formsMenu', 'label' => 'Forms', 'icon' => 'clipboard',
            'links' => collect(config('user_access_modules.forms.modules'))->pluck('key')->all()],
        ['id' => 'reportsMenu', 'label' => 'Reports', 'icon' => 'clipboard-data',
            'links' => array_values(array_unique(array_merge([
                'reports.sales', 'reports.aging', 'reports.other_charges', 'inventory.stock',
                'reports.pull_out', 'reports.return_refund',
            ], collect(config('user_access_modules.reports.modules'))->pluck('key')->all())))],
    ];
    $personnelPdPendingCount = 0;
    if ($sidebarUser->role === \App\User::ROLE_PROVINCIAL_DISTRIBUTOR
        && $personnelLinks->has('purchase_orders.provincial')
        && $sidebarUser->assigned_distributor_user_id && $sidebarUser->assignedDistributorProfile()) {
        $personnelPdPendingCount = \App\AdPurchaseOrder::where('status', 'Pending')
            ->where('order_source', 'provincial_distributor')
            ->where('provincial_distributor_id', $sidebarUser->assigned_distributor_user_id)->count();
    }
@endphp
<div class="nav-section-title">HOME</div>
@if($personnelLinks->isEmpty())
    <p class="text-muted p-3 mb-0">No modules assigned. Please contact your administrator.</p>
@else
    @foreach($personnelMenus as $menu)
        @php
            $menuLinks = collect($menu['links'])->map(function ($key) use ($personnelLinks) { return $personnelLinks->get($key); })->filter();
            $menuActive = $menuLinks->contains($personnelLinkIsActive);
        @endphp
        @if($menuLinks->isNotEmpty())
            <div class="nav-item">
                @if(isset($menu['id']))
                    <a href="#{{ $menu['id'] }}" class="nav-link" data-bs-toggle="collapse" data-bs-target="#{{ $menu['id'] }}"
                        aria-controls="{{ $menu['id'] }}" aria-expanded="{{ $menuActive ? 'true' : 'false' }}">
                        <div class="nav-icon"><i class="ti ti-{{ $menu['icon'] }}"></i></div>
                        <span class="nav-text">{{ $menu['label'] }}</span>
                        <i class="ti ti-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ $menuActive ? 'show' : '' }}" id="{{ $menu['id'] }}">
                        <ul class="nav flex-column ms-3">
                            @foreach($menuLinks as $link)
                                <li class="nav-item">
                                    <a href="{{ $link['route'] ? route($link['route']) : $link['url'] }}"
                                        class="nav-link {{ $personnelLinkIsActive($link) ? 'active' : '' }}" style="font-size: 14px"
                                        @if(!$link['route']) target="_blank" rel="noopener noreferrer" @endif>
                                        {{ $personnelLabels[$link['key']] ?? $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    @php
                        $link = $menuLinks->first();
                        $menuBadge = $link['key'] === 'purchase_orders.provincial' ? $personnelPdPendingCount : ($menu['badge'] ?? 0);
                    @endphp
                    <a href="{{ route($link['route']) }}" class="nav-link {{ $personnelLinkIsActive($link) ? 'active' : '' }}">
                        <div class="nav-icon position-relative">
                            <i class="ti ti-{{ $menu['icon'] }}"></i>
                            @if($menuBadge > 0)<span class="badge rounded-pill bg-danger nav-badge">{{ $menuBadge > 99 ? '99+' : $menuBadge }}</span>@endif
                        </div>
                        <span class="nav-text">{{ $personnelLabels[$link['key']] ?? $link['label'] }}</span>
                    </a>
                @endif
            </div>
        @endif
    @endforeach
@endif
