       @php
           // Agar hozirgi route frontend.home bo‘lsa, linklar faqat #id bo‘ladi
           $isHome = Route::currentRouteName() === 'frontend.home';
           $homeUrl = $isHome ? '' : route('frontend.home');
           $typeIcons = [
               'telegram' => 'fa-brands fa-telegram',
               'facebook' => 'fa-brands fa-facebook',
               'instagram' => 'fa-brands fa-instagram',
               'youtube' => 'fa-brands fa-youtube',
               'linkedin' => 'fa-brands fa-linkedin',
               'whatsapp' => 'fa-brands fa-whatsapp',
               'tiktok' => 'fa-brands fa-tiktok',
               'appstore' => 'fa-brands fa-app-store-ios',
               'googleplay' => 'fa-brands fa-google-play',
               'email' => 'fa-solid fa-envelope',
               'phone' => 'fa-solid fa-phone',
               'address' => 'fa-solid fa-location-dot',
           ];
       @endphp
       <div class="menu-overlay" id="menuOverlay">
           <div class="container">
               <div class="overlay-navbar">
                   <div class="nav_logo me-5">
                       <img src="{{ asset($settings['navbar_logo']) }}" alt="PromoBank logo">
                   </div>
                   <div class="close-menu" id="closeMenu">
                       <i class="fa-sharp fa-regular fa-xmark"></i>
                   </div>
               </div>
               <div class="menu">
                   <div class="social-links">
                       @foreach ($socialLinks as $social)
                           <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank">
                               <i class="fa-brands fa-{{ $social['type'] }}"></i>
                           </a>
                       @endforeach
                   </div>
               </div>
           </div>
       </div>

       <div class="navbar">
           <div class="container">
               <div class="nav_logo me-5">
                   <img src="{{ asset($settings['navbar_logo']) }}" alt="PromoBank logo">
               </div>
               <div class="nav-right" style="display: flex">
                   <div class="social-links">
                       @foreach ($socialLinks as $social)
                           @php
                               $type = $social['type'] ?? 'default';
                               $icon = $typeIcons[$type] ?? 'fa-solid fa-link';
                           @endphp
                           <a href="{{ $social['url'] }}" class="btn btn_social" target="_blank"
                               title="{{ ucfirst($type) }}">
                               <i class="{{ $icon }}"></i>
                           </a>
                       @endforeach

                   </div>
                   <div>
                        @if (Route::is('webapp.promotions.show'))
                           {{-- Orqaga qaytish (faqat promotions.show sahifasida) --}}
                           <a href="{{ route('webapp.promotions.index') }}" class="btn btn_social"
                               title="{{ __('Orqaga qaytish') }}">
                               <i class="fa-solid fa-left"></i>
                           </a>
                       @elseif(Route::is('webapp.promotions.index'))
                           {{-- Games sahifasiga o‘tish (faqat promotions.index sahifasida) --}}
                           <a href="{{ route('webapp.games.index') }}" class="btn btn_social"
                               title="{{ __('Games bo‘limi') }}">
                               <i class="fa-solid fa-gamepad-modern"></i> </a>
                       @elseif(Route::is('webapp.games.index'))
                           {{-- Promotions sahifasiga qaytish (faqat games.index sahifasida) --}}
                           <a href="{{ route('webapp.promotions.index') }}" class="btn btn_social"
                               title="{{ __('Promotions bo‘limi') }}">
                               <i class="fa-solid fa-gift"></i>
                           </a>
                       @elseif(Route::is('webapp.promotions.rating'))
                          {{-- Orqaga qaytish (faqat promotions.rating sahifasida) --}}
<a href="#" class="btn btn_social" title="{{ __('Orqaga qaytish') }}" onclick="goBackWithToken({{ $promotion_id }})">
    <i class="fa-solid fa-left"></i>
</a>
                       @endif
                   </div>

               </div>
           </div>
       </div>
