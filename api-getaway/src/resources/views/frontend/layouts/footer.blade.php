<section class="footer">
    @php
    // Agar hozirgi route frontend.home bo‘lsa, linklar faqat #id bo‘ladi
    $isHome = Route::currentRouteName() === 'frontend.home';
    $homeUrl = $isHome ? '' : route('frontend.home');
@endphp
    <div class="container">
        <!-- ✅ Logo va Social -->
        <div class="logo">
            <div class="img-wrap">
                <img src="{{ asset($settings['footer_logo']) }}" class="footer-logo" alt="PromoBank logo">
            </div>
            <p class="item-description">{{ $settings['footer_description'] }}</p>


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
            <h4 class="footer-title">{{ __('messages.pages') }}</h4>
            <ul>
                <li><a href="{{ $homeUrl}}#home">{{ __('messages.home') }}</a></li>
                <li><a href="{{ $homeUrl}}#download">{{ __('messages.download') }}</a></li>
                <li><a href="{{ $homeUrl}}#promo">{{ __('messages.promos') }}</a></li>
                <li><a href="{{ $homeUrl}}#portfolio">{{ __('messages.portfolio') }}</a></li>
                <li><a href="{{ $homeUrl}}#about">{{ __('messages.about') }}</a></li>
            </ul>
        </div>

        <!-- ✅ Aloqa -->
            <div class="connect" id="contact">
            <h4 class="footer-title">Aloqa ma’lumotlari</h4>
            <ul class="contact-list list-unstyled m-0 p-0 d-flex flex-column gap-2">
                @php
                    $typeIcons = [
                        'address' => 'fa-solid fa-location-dot',
                        'phone' => 'fa-solid fa-phone',
                        'email' => 'fa-solid fa-envelope',
                        'whatsapp' => 'fa-brands fa-whatsapp',
                        'telegram' => 'fa-brands fa-telegram',
                        'linkedin' => 'fa-brands fa-linkedin',
                        'facebook' => 'fa-brands fa-facebook',
                        'instagram' => 'fa-brands fa-instagram',
                    ];
                @endphp

                @foreach ($contacts as $contact)
                    @php
                        $type = $contact['type'] ?? 'default';
                        $icon = $typeIcons[$type] ?? 'fa-solid fa-link';
                    @endphp
                    <li class="d-flex align-items-center gap-2">
                        <i class="{{ $icon }}" style="font-size: 18px;"></i>
                        <a href="{{ $contact['url'] }}" target="_blank" class="contact-link">
                            {{ $contact['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- ✅ Footer bottom -->
    <div class="footer-bottom">
        <div class="container">
            <p>© {{ now()->year }} {{ $settings['footer_bottom'] }}</p>
            <div class="footer-links">
                <div class="footer-links-inner">
                    <a href="/privacy-policy">{{ __('messages.privacy') }}</a>
                    <a href="/terms">{{ __('messages.terms') }}</a>
                    <a href="/support">{{ __('messages.support') }}</a>
                </div>
                <select id="languageSwitcher">
                    <option value="uz" {{ app()->getLocale() === 'uz' ? 'selected' : '' }}>🇺🇿 O‘zbekcha</option>
                    <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>🇷🇺 Русский</option>
                    <option value="kr" {{ app()->getLocale() === 'kr' ? 'selected' : '' }}>🇺🇿 Ўзбекча</option>
                </select>
            </div>
        </div>
    </div>
</section>
    <div id="siteLoader" role="status" aria-live="polite" aria-label="Sahifa yuklanmoqda" data-visible>
        <div class="loader-inner" role="img" aria-hidden="true">
            <div class="loader-spinner" aria-hidden="true"></div>
            <div class="spinner">
                <i></i>
                <i></i>
                <i></i>
                <i></i>
                <i></i>
                <i></i>
                <i></i>
            </div>
        </div>
    </div>
