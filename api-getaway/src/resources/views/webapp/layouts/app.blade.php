<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PromoBank')</title>

    {{-- Agar public/assets ishlatadigan bo'lsang (CDN/GET) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}"> --}}
    <link rel="stylesheet"
        href="{{ asset('assets/css/main.css') }}?v={{ filemtime(public_path('assets/css/main.css')) }}">

</head>

<body>
    @include('webapp.layouts.navbar')

    <main>
        @yield('content')
    </main>

    @include('webapp.layouts.footer')
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
    <div class="scrollTop"><i class="fa-solid fa-up"></i></div>
    <div id="globalLoader"
        style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
     background:rgba(255,255,255,0.7);z-index:9999;align-items:center;justify-content:center;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    {{-- Scripts --}}
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.js') }}"></script>
    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script>
      window.tokenReadyPromise = new Promise(async (resolve, reject) => {
        try {
            const tg = window.Telegram?.WebApp;
            if (!tg) return resolve(); // Telegram bo‘lmasa token talab qilinmaydi

            const initData = tg.initData;

            const resp = await fetch('/api/webapp/auth', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ initData })
            });

            if (!resp.ok) {
                console.error('Auth failed', await resp.text());
                return resolve(); // Token bo‘lmasa — sahifa token talab qilmaydigan holatda ishlaydi
            }

            const data = await resp.json();
            document.cookie = `webapp_token=${data.access_token}; path=/; secure; samesite=lax`;
            window.__ACCESS_TOKEN__ = data.access_token;

            resolve(); // TOKEN TAYYOR!

        } catch (e) {
            console.error("Token ololmadi", e);
            resolve(); // baribir sahifa ishlashiga ruxsat beramiz
        }
    });

        function goBackWithToken(promotionId) {
            event.preventDefault();

            // Tokenni olish (avval window ichidan, keyin cookie'dan)
            const token = window.__ACCESS_TOKEN__ || getCookie('webapp_token');
            if (!token) {
                Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi", "Token mavjud emas yoki muddati tugagan.", "error");
                return false;
            }

            // Token bilan qaytish yo‘li
            const url = `/webapp/promotions/${promotionId}?token=${encodeURIComponent(token)}`;
            window.location.href = url;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }
    </script>
    @yield('scripts')

</body>

</html>
