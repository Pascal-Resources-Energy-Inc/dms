@php($personnelNavigation = \App\Support\ModulePermissions::personnelNavigation($sidebarUser))
@forelse($personnelNavigation as $group)
    <div class="nav-section-title mt-3">{{ $group['label'] }}</div>
    @foreach($group['links'] as $link)
        <div class="nav-item">
            <a href="{{ $link['route'] ? route($link['route']) : $link['url'] }}"
                class="nav-link {{ in_array(Route::currentRouteName(), array_merge(...array_values($link['routes'])), true) ? 'active' : '' }}"
                @if(!$link['route']) target="_blank" rel="noopener noreferrer" @endif>
                <div class="nav-icon"><i class="ti ti-layout-grid"></i></div>
                <span class="nav-text">{{ $link['label'] }}</span>
            </a>
        </div>
    @endforeach
@empty
    <p class="text-muted p-3 mb-0">No modules assigned. Please contact your administrator.</p>
@endforelse
