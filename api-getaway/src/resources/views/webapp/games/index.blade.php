@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
    @endphp
    <style>

    </style>
    <section class="games" id="games">

        <div class="container-sm ">
   <div class="games-card">
             <h3 class="section-title"> Tez kunda yangi o‘yinlarni kutib qoling!</h3>
            <p class="description">
                Qiziqarli va sovrinli o‘yinlar yaqin orada siz uchun taqdim etiladi.
                Hozircha mobil ilovamizni yuklab oling va yangiliklardan xabardor bo‘ling.
            </p>
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
                        @endif
                    @endforeach
                </div>
   </div>
        </div>
    </section>
@endsection
