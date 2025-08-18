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
                        <i class="ph-gauge"></i> {{-- Dashboard uchun statistik ikonka --}}
                        <span>Dashboard</span>
                    </a>
                </li>
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.users.index') ? ' nav-item-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.index') ? ' active' : '' }}">
                        <i class="ph-users"></i>
                        <span>Foydalanuvchilar</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ request()->routeIs('admin.users.index') ? ' show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link{{ request()->routeIs('admin.users.index') ? ' active' : '' }}">
                                <i class="ph-users-three"></i> Foydalanuvchilar ro'yxati
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link">
                        <i class="ph-briefcase"></i> {{-- Promoaksiyalar bo‘limi uchun --}}
                        <span>Promoaksiyalar</span>
                    </a>
                    <ul class="nav-group-sub collapse">
                        <li class="nav-item">
                            <a href="{{ route('admin.company.index') }}"
                                class="nav-link{{ request()->routeIs('admin.company.index') ? ' active' : '' }}">
                                <i class="ph-buildings"></i> {{-- Kompaniyalar --}}
                                Kompaniyalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion.index') }}"
                                class="nav-link{{ request()->routeIs('admin.promotion.index') ? ' active' : '' }}">
                                <i class="ph-megaphone-simple"></i> {{-- Promoaksiyalar --}}
                                Promoaksiyalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promocode.index') }}"
                                class="nav-link{{ request()->routeIs('admin.promocode.index') ? ' active' : '' }}">
                                <i class="ph-barcode"></i> {{-- Promocodelar --}}
                                Promocodelar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.prize.index') }}"
                                class="nav-link{{ request()->routeIs('admin.prize.index') ? ' active' : '' }}">
                                Sovg'alar
                                Sovg'alar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion_shops.index') }}"
                                class="nav-link{{ request()->routeIs('admin.promotion_shops.index') ? ' active' : '' }}">
                                <i class="ph-storefront"></i> {{-- Do‘konlar --}}
                                Promotion shop
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion_products.index') }}"
                                class="nav-link{{ request()->routeIs('admin.promotion_products.index') ? ' active' : '' }}">
                                <i class="ph-package"></i> {{-- Mahsulotlar --}}
                                Promotion products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.seles_receipts.index') }}"
                                class="nav-link{{ request()->routeIs('admin.seles_receipts.index') ? ' active' : '' }}">
                                <i class="ph-receipt"></i> {{-- Chek skanerlari --}}
                                Check scannerlari
                            </a>
                        </li>
                    </ul>
                </li>

            <li class="nav-item nav-item-submenu">
    <a href="#" class="nav-link">
        <i class="ph-image-square"></i> {{-- Banner sozlamalari --}}
        <span>Banner sozlamalari</span>
    </a>
    <ul class="nav-group-sub collapse">
        <li class="nav-item">
            <a href="{{ route('admin.banners.index') }}"
               class="nav-link{{ request()->routeIs('admin.banners.index') ? ' active' : '' }}">
                <i class="ph-device-mobile"></i> {{-- Mobil banner --}}
                Mobile
            </a>
        </li>
    </ul>
</li>

<li class="nav-item nav-item-submenu">
    <a href="#" class="nav-link">
        <i class="ph-bell-simple"></i> {{-- Bildirishnomalar --}}
        <span>Bildirishnomalar</span>
    </a>
    <ul class="nav-group-sub collapse">
        <li class="nav-item">
            <a href="{{ route('admin.notifications.create') }}"
               class="nav-link{{ request()->routeIs('admin.notifications.create') ? ' active' : '' }}">
                <i class="ph-paper-plane-tilt"></i> {{-- Yuborish --}}
                Yuborish
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.notifications.index') }}"
               class="nav-link{{ request()->routeIs('admin.notifications.index') ? ' active' : '' }}">
                <i class="ph-clock-counter-clockwise"></i> {{-- Tarix --}}
                Tarixi
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
