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

                {{-- <!-- Main -->
                <li class="nav-item-header pt-0">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Main</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li> --}}

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="ph-gauge"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- Users --}}
                <li class="nav-item nav-item-submenu {{ request()->routeIs('admin.users.*') ? 'nav-item-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="ph-users"></i>
                        <span>Foydalanuvchilar</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ request()->routeIs('admin.users.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="ph-users-three"></i> Ro'yxat
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Promoaksiyalar --}}
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.company.*') || request()->routeIs('admin.promotion.*') || request()->routeIs('admin.promocode.*') || request()->routeIs('admin.prize.*') || request()->routeIs('admin.promotion_shops.*') || request()->routeIs('admin.promotion_products.*') || request()->routeIs('admin.seles_receipts.*') || request()->routeIs('admin.socialcompany.*') ? 'nav-item-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->routeIs('admin.company.*') || request()->routeIs('admin.promotion.*') || request()->routeIs('admin.promocode.*') || request()->routeIs('admin.prize.*') || request()->routeIs('admin.promotion_shops.*') || request()->routeIs('admin.promotion_products.*') || request()->routeIs('admin.seles_receipts.*') ? 'active' : '' }}">
                        <i class="ph-briefcase"></i>
                        <span>Promoaksiyalar</span>
                    </a>
                    <ul
                        class="nav-group-sub collapse {{ request()->routeIs('admin.company.*') || request()->routeIs('admin.promotion.*') || request()->routeIs('admin.promocode.*') || request()->routeIs('admin.prize.*') || request()->routeIs('admin.promotion_shops.*') || request()->routeIs('admin.promotion_products.*') || request()->routeIs('admin.seles_receipts.*') || request()->routeIs('admin.socialcompany.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.company.index') }}"
                                class="nav-link {{ request()->routeIs('admin.company.*') ? 'active' : '' }}">
                                <i class="ph-buildings"></i> Kompaniyalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion.index') }}"
                                class="nav-link {{ request()->routeIs('admin.promotion.*') ? 'active' : '' }}">
                                <i class="ph-megaphone-simple"></i> Promoaksiyalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promocode.index') }}"
                                class="nav-link {{ request()->routeIs('admin.promocode.*') ? 'active' : '' }}">
                                <i class="ph-barcode"></i> Promocodelar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.prize.index') }}"
                                class="nav-link {{ request()->routeIs('admin.prize.*') ? 'active' : '' }}">
                                <i class="ph-gift"></i> Sovg'alar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion_shops.index') }}"
                                class="nav-link {{ request()->routeIs('admin.promotion_shops.*') ? 'active' : '' }}">
                                <i class="ph-storefront"></i> Do‘konlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.promotion_products.index') }}"
                                class="nav-link {{ request()->routeIs('admin.promotion_products.*') ? 'active' : '' }}">
                                <i class="ph-package"></i> Mahsulotlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.seles_receipts.index') }}"
                                class="nav-link {{ request()->routeIs('admin.seles_receipts.*') ? 'active' : '' }}">
                                <i class="ph-receipt"></i> Cheklar
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Banner sozlamalari --}}
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.banners.*') ? 'nav-item-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <i class="ph-image-square"></i>
                        <span>Bannerlar</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ request()->routeIs('admin.banners.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.banners.index') }}"
                                class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                                <i class="ph-device-mobile"></i> Mobil
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Bildirishnomalar --}}
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.notifications.*') ? 'nav-item-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="ph-bell-simple"></i>
                        <span>Bildirishnomalar</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ request()->routeIs('admin.notifications.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.notifications.create') }}"
                                class="nav-link {{ request()->routeIs('admin.notifications.create') ? 'active' : '' }}">
                                <i class="ph-paper-plane-tilt"></i> Yuborish
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.notifications.index') }}"
                                class="nav-link {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
                                <i class="ph-clock-counter-clockwise"></i> Tarixi
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Website sozlamalari --}}
                <li
                    class="nav-item nav-item-submenu {{ request()->routeIs('admin.portfolio.*') || request()->routeIs('admin.sponsors.*') || request()->routeIs('admin.benefits.*') || request()->routeIs('admin.forsponsor.*') || request()->routeIs('admin.socials.*') || request()->routeIs('admin.downloads.*') || request()->routeIs('admin.contacts.*') || request()->routeIs('admin.abouts.*') || request()->routeIs('admin.settings.*') ? 'nav-item-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ request()->routeIs('admin.portfolio.*') || request()->routeIs('admin.sponsors.*') || request()->routeIs('admin.benefits.*') || request()->routeIs('admin.forsponsor.*') || request()->routeIs('admin.socials.*') || request()->routeIs('admin.downloads.*') || request()->routeIs('admin.contacts.*') || request()->routeIs('admin.abouts.*') || request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="ph-gear-six"></i>
                        <span>Website</span>
                    </a>
                    <ul
                        class="nav-group-sub collapse {{ request()->routeIs('admin.portfolio.*') || request()->routeIs('admin.sponsors.*') || request()->routeIs('admin.benefits.*') || request()->routeIs('admin.forsponsor.*') || request()->routeIs('admin.socials.*') || request()->routeIs('admin.downloads.*') || request()->routeIs('admin.contacts.*') || request()->routeIs('admin.abouts.*') || request()->routeIs('admin.settings.*') ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.portfolio.index') }}"
                                class="nav-link {{ request()->routeIs('admin.portfolio.*') ? 'active' : '' }}">
                                <i class="ph-image-square"></i> Portfolio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.sponsors.index') }}"
                                class="nav-link {{ request()->routeIs('admin.sponsors.*') ? 'active' : '' }}">
                                <i class="ph-handshake"></i> Homiylar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.benefits.index') }}"
                                class="nav-link {{ request()->routeIs('admin.benefits.*') ? 'active' : '' }}">
                                <i class="ph-star"></i> Foydalanuvchilar uchun
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.forsponsor.index') }}"
                                class="nav-link {{ request()->routeIs('admin.forsponsor.*') ? 'active' : '' }}">
                                <i class="ph-medal"></i> Homiylar uchun
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.socials.index') }}"
                                class="nav-link {{ request()->routeIs('admin.socials.*') ? 'active' : '' }}">
                                <i class="ph-share-network"></i> Ijtimoiy tarmoqlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.downloads.index') }}"
                                class="nav-link {{ request()->routeIs('admin.downloads.*') ? 'active' : '' }}">
                                <i class="ph-download-simple"></i> Yuklab olish
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.contacts.index') }}"
                                class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                                <i class="ph-envelope-simple"></i> Kontakt
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.abouts.index') }}"
                                class="nav-link {{ request()->routeIs('admin.abouts.*') ? 'active' : '' }}">
                                <i class="ph-info"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}"
                                class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <i class="ph-sliders"></i> Sozlamalar
                            </a>
                        </li>
                    </ul>
                </li>
                @php
                    $settingsRoutes = ['admin.settings.messages.*'];

                    $isSettingsActive = collect($settingsRoutes)->contains(fn($route) => request()->routeIs($route));
                @endphp

                <li class="nav-item nav-item-submenu {{ $isSettingsActive ? 'nav-item-open' : '' }}">
                    <a href="#" class="nav-link {{ $isSettingsActive ? 'active' : '' }}">
                        <i class="ph-gear-six"></i>
                        <span>Sozlamalar</span>
                    </a>
                    <ul class="nav-group-sub collapse {{ $isSettingsActive ? 'show' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.messages.index') }}"
                                class="nav-link {{ request()->routeIs('admin.settings.messages*') ? 'active' : '' }}">
                             <i class="ph ph-chat-dots"></i> Default xabarlar
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
