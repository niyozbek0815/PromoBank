<div class="menu-overlay" id="menuOverlay">
    <div class="container">
        <div class="overlay-navbar">
            <div class="nav_logo me-5">
                <img src="{{ asset('assets/image/hero/logo.svg') }}" alt="PromoBank logo">
            </div>
            <div class="close-menu" id="closeMenu">
                <i class="fa-sharp fa-regular fa-xmark"></i>
            </div>
        </div>
        <div class="menu">
            <a href="#" class="nav-link active">Bosh sahifa</a>
            <a href="#download" class="nav-link">Yuklab olish</a>
            <a href="#promo" class="nav-link">Aksiyalar</a>
            <a href="#benefit" class="nav-link">Foydalanuvchilar uchun</a>
            <a href="#portfolio" class="nav-link">Loyihalar</a>
            <a href="#for-sponsors" class="nav-link">Homiylarimiz uchun</a>
            <a href="#sponsors" class="nav-link">Homiylar</a>
            <a href="#about" class="nav-link">Biz haqimizda</a>

            <div class="social-links">
                @foreach ($socialLinks as $social)
                    @if ($social['type'] === 'instagram')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    @elseif($social['type'] === 'facebook')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                    @elseif($social['type'] === 'telegram')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                    @elseif($social['type'] === 'youtube')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    @elseif($social['type'] === 'appstore')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-app-store-ios"></i>
                        </a>
                    @elseif($social['type'] === 'googleplay')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-google-play"></i>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="navbar">
    <div class="container">
        <div class="nav_logo me-5">
            <img src="{{ asset('assets/image/hero/logo.svg') }}" alt="PromoBank logo">
        </div>
        <div class="nav-links flex-row">
            <a href="#" class="nav-link active">Bosh sahifa</a>
            <a href="#download" class="nav-link">Yuklab olish</a>
            <a href="#promo" class="nav-link">Aksiyalar</a>
            <!-- <a href="#benefit" class="nav-link">Foydalanuvchilar uchun</a> -->
            <!-- <a href="#portfolio" class="nav-link">Loyihalar</a> -->
            <!-- <a href="#for-sponsors" class="nav-link">Homiylarimiz uchun</a> -->
            <!-- <a href="#sponsors" class="nav-link">Homiylar</a> -->
            <a href="#about" class="nav-link">Biz haqimizda</a>
        </div>
        <div class="nav-right">
            <div class="social-links">
                @foreach ($socialLinks as $social)
                    @if ($social['type'] === 'instagram')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    @elseif($social['type'] === 'facebook')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                    @elseif($social['type'] === 'telegram')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                    @elseif($social['type'] === 'youtube')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    @elseif($social['type'] === 'appstore')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-app-store-ios"></i>
                        </a>
                    @elseif($social['type'] === 'googleplay')
                        <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                            <i class="fa-brands fa-google-play"></i>
                        </a>
                    @endif
                @endforeach
            </div>
            <button class="btn btn_bars"><i class="fa-regular fa-bars"></i></button>
        </div>
    </div>
</div>
