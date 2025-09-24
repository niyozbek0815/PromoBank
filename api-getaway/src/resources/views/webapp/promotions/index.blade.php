@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
    @endphp
    <section class="promotion" id="promotion">
        <div class="container-sm" id="promo">
            <div class="promo-card">
                @foreach ($promos as $promo)
                    <a href="{{ route('webapp.promotions.show', $promo['id']) }}" class="promo-item">
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


