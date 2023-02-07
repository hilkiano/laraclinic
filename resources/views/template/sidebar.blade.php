<div class="d-flex d-lg-flex d-md-none d-sm-none d-none p-3 bg-white flex-column" style="width: 280px; min-height: calc(100vh - 80px)">
    <ul class="nav nav-pills flex-column mb-auto position-fixed" style="width: calc(280px - 2em);">
        @foreach ($menus["data"] as $menu)
        @if ($menu->route)
        <li class="nav-item mb-1">
            <a id="sidebarItem" href="{{ $menu->is_active ? '#' : $menu->route }}" class="nav-link {{ $menu->is_active ? 'active' : 'link-dark' }}">
                <i class="bi {{ $menu->icon }} me-2"></i>
                {{ $menu->label }}
            </a>
        </li>
        @elseif (count($menu->child) > 0)
        <li class="nav-item">
            <div style="cursor:initial" class="nav-link link-dark">
                <i class="bi {{ $menu->icon }} me-2"></i>
                {{ $menu->label }}
            </div>
        </li>
        <div class="mb-1">
            <ul class="nav nav-pills flex-column w-100 ps-2 pe-2 mt-2">
                @foreach ($menu->child as $child)
                <li class="mb-1">
                    <a id="sidebarChildItem" class="nav-link ps-3 py-1 {{ $child->is_active ? 'active' : 'link-dark' }}" href="{{ $child->is_active ? '#' : $child->route }}">
                        <i class="bi {{ $child->icon }} me-2"></i>
                        {{ $child->label }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @endforeach
    </ul>
</div>

<!-- Mobile Sidebar -->


<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="width: 280px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">Menu</h5>
    </div>
    <div class="offcanvas-body p-0 pb-3 d-flex flex-column">
        <ul class="nav nav-pills flex-column mb-auto w-100">
            @foreach ($menus["data"] as $menu)
            @if ($menu->route)
            <li class="nav-item mb-1">
                <a href="{{ $menu->is_active ? '#' : $menu->route }}" class="nav-link {{ $menu->is_active ? 'active' : 'link-dark' }}">
                    <i class="bi {{ $menu->icon }} me-2"></i>
                    {{ $menu->label }}
                </a>
            </li>
            @elseif (count($menu->child) > 0)
            <li class="nav-item">
                <div style="cursor:initial" class="nav-link link-dark">
                    <i class="bi {{ $menu->icon }} me-2"></i>
                    {{ $menu->label }}
                </div>
            </li>
            <div class="mb-1">
                <ul class="nav nav-pills flex-column w-100 ps-2 pe-2 mt-2">
                    @foreach ($menu->child as $child)
                    <li>
                        <a class="nav-link ps-3 py-1 {{ $child->is_active ? 'active' : 'link-dark' }}" href="{{ $child->is_active ? '#' : $child->route }}">
                            <i class="bi {{ $child->icon }} me-2"></i>
                            {{ $child->label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @endforeach
        </ul>
    </div>
</div>