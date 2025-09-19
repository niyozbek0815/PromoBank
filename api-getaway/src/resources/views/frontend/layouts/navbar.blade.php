@php
    // Agar hozirgi route frontend.home bo‘lsa, linklar faqat #id bo‘ladi
    $isHome = Route::currentRouteName() === 'frontend.home';
    $homeUrl = $isHome ? '' : route('frontend.home');
@endphp

<div class="menu-overlay" id="menuOverlay">
    <div class="container">
        <div class="overlay-navbar">
            <div class="nav_logo me-5">
                <img src="{{ asset($settings['navbar_logo']) }}" alt="PromoBank logo">
            </div>
            <div class="close-menu" id="closeMenu">
                <i class="fa-sharp fa-regular fa-xmark"></i>
            </div>
        </div>
        <div class="menu">
            <a href="{{ $isHome ? '#' : $homeUrl.'#' }}" class="nav-link active">
                {{ __('messages.home') }}
            </a>
            <a href="{{ $homeUrl }}#download" class="nav-link">{{ __('messages.download') }}</a>
            <a href="{{ $homeUrl }}#promo" class="nav-link">{{ __('messages.promos') }}</a>
            <a href="{{ $homeUrl }}#benefit" class="nav-link">{{ __('messages.benefit') }}</a>
            <a href="{{ $homeUrl }}#portfolio" class="nav-link">{{ __('messages.portfolio') }}</a>
            <a href="{{ $homeUrl }}#for-sponsors" class="nav-link">{{ __('messages.for_sponsors') }}</a>
            <a href="{{ $homeUrl }}#sponsors" class="nav-link">{{ __('messages.sponsors') }}</a>
            <a href="{{ $homeUrl }}#about" class="nav-link">{{ __('messages.about') }}</a>

            <div class="social-links">
                @foreach ($socialLinks as $social)
                    <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                        <i class="fa-brands fa-{{ $social['type'] }}"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="navbar">
    <div class="container">
        <div class="nav_logo me-5">
            <img src="{{ asset($settings['navbar_logo']) }}" alt="PromoBank logo">
        </div>
        <div class="nav-links flex-row">
            <a href="{{ $isHome ? '#' : $homeUrl.'#' }}" class="nav-link active">
                {{ __('messages.home') }}
            </a>
            <a href="{{ $homeUrl }}#download" class="nav-link">{{ __('messages.download') }}</a>
            <a href="{{ $homeUrl }}#promo" class="nav-link">{{ __('messages.promos') }}</a>
            <a href="{{ $homeUrl }}#about" class="nav-link">{{ __('messages.about') }}</a>
        </div>
        <div class="nav-right">
            <div class="social-links">
                @foreach ($socialLinks as $social)
                    <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                        <i class="fa-brands fa-{{ $social['type'] }}"></i>
                    </a>
                @endforeach
            </div>
            <button class="btn btn_bars"><i class="fa-regular fa-bars"></i></button>
        </div>
    </div>
</div>
