@extends('frontend.layouts.app')

@section('title', 'PromoBank')

@section('content')

<section class="hero" id="hero" data-relative>
        <div class="scene">
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/1.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/2.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/3.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/4.png') }}" alt="" />
            </div>
        </div>

        <div class="content" data-content>
            <div class="hero-inner container">
                <h1 class="title">{{ $settings['hero_title'] }}</h1>
            </div>
        </div>

        <div id="stars">
            <div class="img-wrap star">
       x         <img src="{{ asset('assets/image/hero/star-1.png') }}" alt="star" />
            </div>
            <div class="img-wrap star">
                <img src="{{ asset('assets/image/hero/star-2.png') }}" alt="star" />
            </div>
            <div class="img-wrap star">
                <img src="{{ asset('assets/image/hero/star-3.png') }}" alt="star" />
            </div>
            <div class="img-wrap star">
                <img src="{{ asset('assets/image/hero/star-4.webp') }}" alt="star" />
            </div>
        </div>

        <div id="back">
            <div class="img-wrap spin">
                <img src="{{ asset('assets/image/hero/rotate.png') }}" alt="back" />
            </div>
        </div>
    </section>
    <section class="promo-download" data-relative>
        <div class="scene">
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/3.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/5.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/6.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/4.png') }}" alt="" />
            </div>
        </div>

        <div class="content" data-content>
            {{-- 📲 Download Section --}}
            <div class="download-section" id="download">
                <div class="container-sm">
                    <div class="download-text">
                        <p class="sub-title">{{ $download['subtitle'] }}</p>
                        <h2 class="section-title">{{ $download['title'] }}</h2>
                        <p class="description">{{ $download['description'] }}</p>

                        <div class="store-buttons">
                            @foreach ($download['links'] as $link)
                                @if ($link['type'] === 'googleplay' && !empty($link['url']))
                                    <a href="{{ $link['url'] }}" target="_blank" class="store-btn playstore">
                                        <i class="fa-brands fa-google-play"></i>
                                        <div class="text">
                                            <small>{{ __('messages.download_googleplay_small') }}</small>
                                            <strong>{{ __('messages.download_googleplay') }}</strong>
                                        </div>
                                    </a>
                                @elseif($link['type'] === 'appstore' && !empty($link['url']))
                                    <a href="{{ $link['url'] }}" target="_blank" class="store-btn appstore">
                                        <i class="fa-brands fa-app-store-ios"></i>
                                        <div class="text">
                                            <small>{{ __('messages.download_appstore_small') }}</small>
                                            <strong>{{ __('messages.download_appstore') }}</strong>
                                        </div>
                                    </a>
                                @elseif($link['type'] === 'telegram' && !empty($link['url']))
                                    <a href="{{ $link['url'] }}" target="_blank" class="store-btn telegram">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <div class="text">
                                            <small>{{ __('messages.download_telegram_small') }}</small>
                                            <strong>{{ __('messages.download_telegram') }}</strong>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="download-image">
                        <img src="{{ asset($download['image']) }}" alt="PromoBank App Preview">
                    </div>
                </div>
            </div>

            {{-- 🎉 Promos Section --}}
            <div class="container-sm" id="promo">
                <p class="sub-title">{{ __('messages.promo_subtitle') }}</p>
                <h2 class="section-title">{{ __('messages.promo_title') }}</h2>
                <div class="promo-card">
                    @foreach ($promos as $promo)
                        <a href="{{ route('promotion.show', parameters: $promo['id']) }}" class="promo-item">
                            <div class="img-wrap">
                                <img src="{{ asset($promo['banner']) }}" alt="{{ $promo['name'] }}">
                            </div>
                            <p class="item-title">{{ $promo['name'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <section class="benefit" id="benefit" data-relative>
        <div class="scene">
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/7.png') }}" alt="" />
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/6.png') }}" alt="" />
            </div>
        </div>

        <div class="content" data-content>
            <div class="container-sm">
                <p class="sub-title">{{ __('messages.benefit_subtitle') }}</p>
                <h2 class="section-title">{{ __('messages.benefit_title') }}</h2>

                <div class="benefit-card">
                    @foreach ($benefits as $benefit)
                        <div class="benefit-item">
                            <div class="img-wrap">
                                <img src="{{ asset($benefit['img']) }}" alt="{{ $benefit['title'] }}">
                            </div>
                            <div>
                                <h4 class="item-title">{{ $benefit['title'] }}</h4>
                                <p class="item-description">{{ $benefit['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="portfolio" id="portfolio">
        <div class="container-sm">
            <p class="sub-title">{{ __('messages.portfolio_subtitle') }}</p>
            <h2 class="section-title">{{ __('messages.portfolio_title') }}</h2>

            <div class="portfolio-card">
                @foreach ($portfolios as $portfolio)
                    <div class="portfolio-item">
                        <div class="img-wrap">
                            <img src="{{ asset($portfolio['img']) }}" alt="{{ $portfolio['title'] }}">
                        </div>
                        <div class="text-wrap">
                            <p class="sub-title">{{ $portfolio['subtitle'] }}</p>
                            <h4 class="item-title">{{ $portfolio['title'] }}</h4>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="for-sponsors" id="for-sponsors" data-relative>
        <div class="scene">
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/2.png') }}" alt="scene" />
            </div>
        </div>

        <div class="content" data-content>
            <div class="container-sm">
                <p class="sub-title">{{ __('messages.for_sponsors_subtitle') }}</p>
                <h2 class="section-title">{{ __('messages.for_sponsors_title') }}</h2>

                <div class="for-sponsors-card">
                    @foreach ($forSponsors as $item)
                        @php
                            $isSvg = \Illuminate\Support\Str::endsWith($item['img'], '.svg');
                        @endphp

                        <div class="for-sponsors-item">
                            <div class="{{ $isSvg ? 'svg-wrap' : 'img-wrap' }}">
                                <img src="{{ asset($item['img']) }}" alt="{{ $item['title'] }}">
                            </div>
                            <div class="text-wrap">
                                <h4 class="item-title">{{ $item['title'] }}</h4>
                                <p class="item-description">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <section class="sponsors-about" data-relative>
        <div class="scene">
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/8.png') }}" alt="scene">
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/1.png') }}" alt="scene">
            </div>
            <div class="img-wrap innerAnimated">
                <img src="{{ asset('assets/image/scene/5.png') }}" alt="scene">
            </div>
        </div>
        <div class="content" data-content>
            {{-- Sponsors Section --}}
            <div class="sponsors-card" id="sponsors">
                <div class="container-sm">
                    <p class="sub-title">{{ __('messages.sponsors_subtitle') }}</p>
                    <h2 class="section-title">{{ __('messages.sponsors_title') }}</h2>
                </div>

                <div class="sponsors-list owl-carousel">
                    @foreach ($sponsors as $sponsor)
                        <div class="sponsor-item">
                            <a href="{{ $sponsor['url'] }}">
                                <img src="{{ asset($sponsor['img']) }}" alt="{{ $sponsor['alt'] }}">
                            </a>
                        </div>
                    @endforeach
                </div>

                {{-- About Section --}}
                <div class="container-sm" id="about">
                    <div class="about-card">
                        <div class="about-text">
                            <p class="sub-title">{{ $about['subtitle'] }}</p>
                            <h2 class="section-title">{{ $about['title'] }}</h2>
                            <p class="description">{{ $about['description'] }}</p>

                            <ul class="list-wrap">
                                @foreach ($about['list'] as $item)
                                    <li><i class="fa-solid fa-badge-check"></i> {{ $item }}</li>
                                @endforeach
                            </ul>

                        </div>

                        <div class="about-image">
                            <img src="{{ asset($about['image']) }}" alt="PromoBank App Preview">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
