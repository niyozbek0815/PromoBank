    <section class="footer footer-webapp">
        @php
            // Agar hozirgi route frontend.home bo‘lsa, linklar faqat #id bo‘ladi
            $isHome = Route::currentRouteName() === 'frontend.home';
            $homeUrl = $isHome ? '' : route('frontend.home');
        @endphp
        <div class="footer-bottom">
            <div class="container">
                <select id="languageSwitcher">
                    <option value="uz" {{ app()->getLocale() === 'uz' ? 'selected' : '' }}>🇺🇿 O‘zbekcha
                    </option>
                    <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>🇷🇺 Русский
                    </option>
                    <option value="kr" {{ app()->getLocale() === 'kr' ? 'selected' : '' }}>🇺🇿 Ўзбекча
                    </option>
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 English</option>

                </select>
            </div>
        </div>
    </section>
