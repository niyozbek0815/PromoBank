@extends('frontend.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
    @endphp
    <section class="banner">
        <div class="content">
            <div class="container">
                <img src="{{ $promotion['banner'] ??secure_asset('assets/image/default-banner.jpg') }}"
                     alt="{{ $promotion['name'] }}" class="banner-img">
            </div>
            <div class="container-sm">
                <div class="promotion-header">
                    <div class="name-company">
                        <p class="sub-title">{{ __('messages.promo_subtitle') }}</p>
                        <h2 class="section-title">{{ $promotion['name'] }}</h2>
                        <div class="promotion-dates">
                            <div class="start">
                                <span class="date-label">{{ __('messages.start') }}</span>
                                <span class="date-value">
                                    {{ \Carbon\Carbon::parse($promotion['start_date'])->format('d.m.Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="date-label">{{ __('messages.end') }}</span>
                                <span class="date-value">
                                    {{ \Carbon\Carbon::parse($promotion['end_date'])->format('d.m.Y') }}
                                </span>
                            </div>
                            @if (!empty($promotion['offer']))
                                <a href="{{ $promotion['offer'] }}" target="_blank" class="offer-link">
                                    <i class="fa-regular fa-file-lines"></i> {{ __('messages.offer') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="company-card">
                        <div class="company-header">
                            <img src="{{ $promotion['company']['logo']['url'] ??secure_asset('assets/image/default-logo.png') }}"
                                 alt="Company Logo" class="company-logo">
                            <div class="company-details">
                                <h5 class="company-name">
                                    {{ $promotion['company']['name'][$locale] ?? $promotion['company']['name']['uz'] }}
                                </h5>
                                <p class="company-address">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $promotion['company']['region'] ?? '' }},
                                    {{ $promotion['company']['address'] ?? '' }}
                                </p>
                            </div>
                        </div>

                        @if (!empty($promotion['company']['social_media']))
                            <div class="social-links">
                                @foreach ($promotion['company']['social_media'] as $social)
                                    <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                                        <i class="fa-brands fa-{{ strtolower($social['type']) }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="participation-methods">
                        <h5 class="methods-title">{{ __('messages.methods') }}</h5>
                        <div class="methods-grid">
                            @php
                                $platforms = array_column($promotion['platforms'], 'name');
                            @endphp

                            @foreach ($promotion['participation_type'] as $method)
                                @if ($method['type'] === 'qr_code')
                                    <a href="#" class="method-btn" onclick="openScannerModal(event)">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>{{ $method['name'][$locale] ?? $method['name']['uz'] }}</span>
                                    </a>
                                @elseif($method['type'] === 'receipt_scan')
                                    <a href="#" class="method-btn" onclick="openReceiptModal(event)">
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>{{ $method['name'][$locale] ?? $method['name']['uz'] }}</span>
                                    </a>
                                @elseif($method['type'] === 'text_code')
                                    <a href="#" class="method-btn" onclick="openCodeModal(event)">
                                        <i class="fa-solid fa-text-width"></i>
                                        <span>{{ $method['name'][$locale] ?? $method['name']['uz'] }}</span>
                                    </a>
                                @endif
                            @endforeach

                            @if (in_array('telegram', $platforms))
                                <a href="https://t.me/Niyozbek0815" class="method-btn" target="_blank">
                                    <i class="fa-brands fa-telegram"></i>
                                    <span>{{ __('messages.telegram') }}</span>
                                </a>
                            @endif

                            @if (in_array('sms', $platforms))
                                <a href="#" class="method-btn" onclick="openSmsModal(event)">
                                    <i class="fa-solid fa-sms"></i>
                                    <span>{{ __('messages.sms') }}</span>
                                </a>
                            @endif

                            @if (in_array('mobile', $platforms))
                                <a href="#" class="method-btn" onclick="openAppModal(event)">
                                    <i class="fa-brands fa-android"></i>
                                    <span>{{ __('messages.mobile') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if (!empty($promotion['gallery']))
                    <div class="media-section">
                        <h3 class="media-title">{{ __('messages.gallery') }}</h3>
                        <div class="media-gallery owl-carousel">
                            @foreach ($promotion['gallery'] as $media)
                                <div class="gallery-item {{ Str::contains($media['mime_type'], 'video') ? 'video' : '' }}"
                                     data-type="{{ Str::contains($media['mime_type'], 'video') ? 'video' : 'image' }}"
                                     data-src="{{ $media['url'] }}">
                                    @if (Str::contains($media['mime_type'], 'video'))
                                        <img src="{{ $promotion['banner'] }}" alt="Video preview">
                                        <span class="overlay-icon"><i class="fa-solid fa-play"></i></span>
                                    @else
                                        <img src="{{ $media['url'] }}" alt="Image">
                                        <span class="overlay-icon"><i class="fa-regular fa-crop-simple"></i></span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <h3>{{ __('messages.extra') }}</h3>
                <div class="description">
    {!! $promotion['description'] ?? '' !!}
                </div>
            </div>
        </div>
    </section>

    @php
        $smsPlatform = collect($promotion['platforms'])->firstWhere('name', 'sms');
    @endphp

    @if ($smsPlatform)
        <div id="smsModal" class="scannerModal">
            <div class="modal-content">
                <h4>{{ __('messages.sms_title') }}</h4>
                <p class="mt-3 mb-3">
                    {!! __('messages.sms_text', ['phone' => $smsPlatform['phone'] ?? 'XXXX']) !!}
                </p>
                <button type="button" class="btn btn-secondary mt-3" onclick="closeSmsModal()">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    @endif

    {{-- QR, Receipt, App, Code modallari --}}
    @foreach (['scanner' => 'closeScannerModal', 'receipt' => 'closeReceiptModal', 'app' => 'closeAppModal', 'code' => 'closeCodeModal'] as $modal => $closeFn)
        <div id="{{ $modal }}Modal" class="scannerModal" style="{{ $modal === 'code' ? 'display:none;' : '' }}">
            <div class="modal-content">
                <h4>📱 {{ __('messages.download_title') }}</h4>
                <p class="mt-2">{{ __('messages.download_desc') }}</p>
                <div class="download-buttons">
                    @foreach ($download['links'] as $link)
                        @php
                            $type = $link['type'];
                            $url = $link['url'];
                        @endphp
                        @if ($type === 'googleplay')
                            <a href="{{ $url }}" target="_blank" class="btn-download android">
                                <i class="fa-brands fa-google-play me-2"></i> Google Play
                            </a>
                        @elseif($type === 'appstore')
                            <a href="{{ $url }}" target="_blank" class="btn-download ios">
                                <i class="fa-brands fa-apple me-2"></i> App Store
                            </a>
                        @elseif($type === 'telegram')
                            <a href="{{ $url }}" target="_blank" class="btn-download telegram">
                                <i class="fa-brands fa-telegram me-2"></i> Telegram
                            </a>
                        @endif
                    @endforeach
                </div>
                <button type="button" class="btn-close-modal mt-3" onclick="{{ $closeFn }}()">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    @endforeach

    {{-- Media viewer --}}
    <div class="media-modal" id="mediaModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body"></div>
            <button class="prev">&#10094;</button>
            <button class="next">&#10095;</button>
        </div>
    </div>
@endsection
