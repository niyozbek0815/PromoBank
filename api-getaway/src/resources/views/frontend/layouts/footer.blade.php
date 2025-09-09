<section class="footer">
    <div class="container">
        <!-- ✅ Logo va Social -->
        <div class="logo">
            <div class="img-wrap">
                <img src="{{ asset('assets/image/hero/logo.svg') }}" class="footer-logo" alt="PromoBank logo">
            </div>
            <p class="item-description">{{ $heroTitle }}</p>

            <div class="social-links">
                @foreach ($socialLinks as $social)
                    @switch($social['type'])
                        @case('instagram')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-instagram"></i></a>
                        @break

                        @case('facebook')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-facebook"></i></a>
                        @break

                        @case('telegram')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-telegram"></i></a>
                        @break

                        @case('youtube')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-youtube"></i></a>
                        @break

                        @case('appstore')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-app-store-ios"></i></a>
                        @break

                        @case('googleplay')
                            <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"><i
                                    class="fa-brands fa-google-play"></i></a>
                        @break
                    @endswitch
                @endforeach
            </div>
        </div>

        <!-- ✅ Sahifalar -->
        <div class="pages">
            <h4 class="footer-title">Sahifalar</h4>
            <ul>
                <li><a href="#home">Bosh sahifa</a></li>
                <li><a href="#download">Yuklab olish</a></li>
                <li><a href="#promo">Aksiyalar</a></li>
                <!-- <li><a href="#benefit">Foydalanuvchilar uchun</a></li> -->
                <li><a href="#portfolio">Loyihalar</a></li>
                <!-- <li><a href="#for-sponsors">Homiylarimiz uchun</a></li> -->
                <!-- <li><a href="#sponsors">Homiylar</a></li> -->
                <li><a href="#about">Biz haqimizda</a></li>
                <!-- <li><a href="#contact">Aloqa</a></li> -->
            </ul>
        </div>

        <!-- ✅ Aloqa -->
        <div class="connect" id="contact">
            <h4 class="footer-title">Aloqa ma’lumotlari</h4>
            <ul>
                @foreach ($contacts as $contact)
                    <li>
                        @php
                            $icon = 'fa-solid fa-link'; // default icon
                            if (Str::startsWith($contact['url'], 'https://maps.google')) {
                                $icon = 'fa-solid fa-location-dot';
                            } elseif (Str::startsWith($contact['url'], 'tel:')) {
                                $icon = 'fa-solid fa-phone';
                            } elseif (Str::startsWith($contact['url'], 'mailto:')) {
                                $icon = 'fa-solid fa-envelope';
                            }
                        @endphp

                        <i class="{{ $icon }}"></i>
                        <a href="{{ $contact['url'] }}" target="_blank">{{ $contact['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- ✅ Footer bottom -->
    <div class="footer-bottom">
        <div class="container">
            <p>© {{ now()->year }} PromoBank. Barcha huquqlar himoyalangan.</p>
            <div class="footer-links">
                <div class="footer-links-inner">
                    <a href="/privacy-policy">Privacy Policy</a>
                    <a href="/terms">Ommaviy Oferta</a>
                    <a href="/support">Support</a>
                </div>
                <select id="languageSwitcher">
                    <option value="uz">🇺🇿 O‘zbekcha</option>
                    <option value="ru">🇷🇺 Русский</option>
                    <option value="en">🇬🇧 English</option>
                </select>
            </div>
        </div>
    </div>
</section>
