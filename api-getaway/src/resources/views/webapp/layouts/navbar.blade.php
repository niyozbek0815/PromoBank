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
               <div class="nav-right">
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
               </div>
           </div>
       </div>
