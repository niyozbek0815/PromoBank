<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- Sidebar header -->
        <div class="sidebar-section">
            <div class="sidebar-section-body d-flex justify-content-center">
                <h5 class="sidebar-resize-hide flex-grow-1 my-auto">Navigation</h5>

                <div>
                    <button type="button"
                        class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                        <i class="ph-arrows-left-right"></i>
                    </button>

                    <button type="button"
                        class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none">
                        <i class="ph-x"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- /sidebar header -->


        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <!-- Main -->
                <li class="nav-item-header pt-0">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Main</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link{{ request()->routeIs('admin.dashboard') ? ' active' : '' }}">
                        <i class="ph-house"></i>
                        <span>
                            Dashboard
                            {{-- <span class="d-block fw-normal opacity-50">No pending orders</span> --}}
                        </span>
                    </a>
                </li>
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.users.index') ? ' nav-item-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.index') ? ' active' : '' }}">
                        <i class="ph-users"></i>
                        <span>User pages</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ request()->routeIs('admin.users.index') ? ' show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link{{ request()->routeIs('admin.users.index') ? ' active' : '' }}">
                                User list
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link">
                        <i class="ph-layout"></i>
                        <span>Companies</span>
                    </a>
                    <ul class="nav-group-sub collapse">
                        <li class="nav-item">
                            <a href="{{ route('admin.company.index') }}"
                                class="nav-link{{ request()->routeIs('admin.company.index') ? ' active' : '' }}">
                                Kompaniyalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion.index') }}"
                                class="nav-link{{ request()->routeIs('admin.company.index') ? ' active' : '' }}">
                                Promoaksiyalar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->

</div>
