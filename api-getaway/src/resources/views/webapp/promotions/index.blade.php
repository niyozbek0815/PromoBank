@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    <section class="promotion" id="promotion">
        <div class="container-sm" id="promo">
            <div class="promo-card">
                @foreach ($promos as $promo)
                    <a href="#" class="promo-item" onclick="redirectWithToken(event, {{ $promo['id'] }})">
                        <div class="img-wrap">
                            <img src="{{ asset($promo['banner']) }}" alt="{{ $promo['name'] }}">
                        </div>
                        <p class="item-title">{{ $promo['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        function redirectWithToken(event, promoId) {
            event.preventDefault();

            // Tokenni olish
            const token = window.__ACCESS_TOKEN__ || getCookie('webapp_token');
            if (!token) {
                Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi",
                    "Token mavjud emas yoki muddati tugagan.", "error");
                return false;
            }

            // Hard refresh bilan token query orqali yuborish
            window.location.href = `/webapp/promotions/${promoId}?token=${encodeURIComponent(token)}`;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }
    </script>
@endsection