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
    @include('frontend.layouts.navbar')

    <main>
        @yield('content')
    </main>

    @include('frontend.layouts.footer')
	<div class="scrollTop"><i class="fa-solid fa-up"></i></div>

    {{-- Scripts --}}
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.js') }}"></script>\
        <script src="https://unpkg.com/html5-qrcode" defer></script>

    <script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>

</body>

</html>
