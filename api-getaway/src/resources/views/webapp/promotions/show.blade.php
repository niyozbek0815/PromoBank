@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
        $types = array_column($promotion['participation_type'], 'type');
        $progress_bar = $promotion['progress_bar'];
        // dd($progress_bar);
        $hasSecretNumberType = in_array('secret_number', $types, true);
        // dd($types);
    @endphp

    <section class="banner">
        <div class="content">

            @if (!$hasSecretNumberType)
                <div class="container">
                    <img src="{{ $promotion['banner'] ?? asset('assets/image/default-banner.jpg') }}"
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
                                <img src="{{ $promotion['company']['logo']['url'] ?? asset('assets/image/default-logo.png') }}"
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

                                {{-- @if (in_array('telegram', $platforms))
                                <a href="https://t.me/Niyozbek0815" class="method-btn" target="_blank">
                                    <i class="fa-brands fa-telegram"></i>
                                    <span>{{ __('messages.telegram') }}</span>
                                </a>
                            @endif --}}

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
                        {!! $promotion['description'] ?? '<p class="text-muted">Hech qanday tavsif mavjud emas</p>' !!}
                    </div>
                </div>
            @else
                <div class="container-sm secret-number">

                    <div class="promotion-header">
                        <div class="name-company">
                            <p class="sub-title">{{ __('messages.promo_subtitle') }}</p>
                            <h2 class="section-title">{{ $promotion['name'] }}</h2>
                            <div class="promotion-dates">
                                <div class="start"><span class="date-label">{{ __('messages.start') }}</span>
                                    <span class="date-value">
                                        {{ \Carbon\Carbon::parse($promotion['start_date'])->format('d.m.Y') }}</span>
                                </div>
                                <div><span class="date-label">{{ __('messages.end') }}</span>
                                    <span class="date-value">
                                        {{ \Carbon\Carbon::parse($promotion['end_date'])->format('d.m.Y') }}</span>
                                </div>
                                @if (!empty($promotion['offer']))
                                    <a href="{{ $promotion['offer'] }}" target="_blank" class="offer-link">
                                        <i class="fa-regular fa-file-lines"></i> {{ __('messages.offer') }}
                                    </a>
                                @endif

                            </div>
                        </div>


                        <form id="secretNumberForm" class="participation-methods" onsubmit="submitSecretNumber(event)">

                            <input type="number" id="secretNumber" name="secret-number" min="2" step="1"
                                placeholder="{{ __('messages.secret_number_placeholder') }}" required>
                            <button type="submit">
                                {{ __('messages.submit') }}
                            </button>
                        </form>
                        <div class="rating-card">
                            <div class="total-points">
                                <div class="points-info">
                                    <h3 id="totalPoints">{{ $progress_bar['all_points'] }}
                                        <span>{{ __('messages.ball_text') }}</span>
                                    </h3>
                                </div>
                                <div class="today-points">{{ __('messages.today') }}:
                                    +{{ $progress_bar['today_poinst'] }}</div>
                            </div>

                            <div class="progress-container">
                                <ul>
                                    <li data-ball="{{ $progress_bar['step_0_threshold'] }}"></li>
                                    <li data-ball="{{ $progress_bar['step_1_threshold'] }}"></li>
                                    <li data-ball="{{ $progress_bar['step_2_threshold'] }}"></li>
                                    <li data-ball="{{ $progress_bar['daily_points'] }}"></li>
                                </ul>
                                <div class="progress-bar" id="progressBar"></div>
                            </div>
<a href="#" id="top100Btn" class="rating-btn" onclick="openRatingPage(event, {{ $promotion['id'] }})">
    <i class="fa-solid fa-ranking-star"></i> {{ __('messages.top_100') }}
</a>
                        </div>
                        <div class="company-card">
                            <div class="company-header">
                                <img src="{{ $promotion['company']['logo']['url'] ?? asset('assets/image/default-logo.png') }}"
                                    alt="Company Logo" class="company-logo">
                                <div class="company-details">
                                    <h5 class="company-name">
                                        {{ $promotion['company']['name'][$locale] ?? $promotion['company']['name']['uz'] }}
                                    </h5>
                                    <p class="company-address"><i class="fa-solid fa-location-dot"></i></i>
                                        {{ $promotion['company']['region'] ?? '' }},
                                        {{ $promotion['company']['address'] ?? '' }}</p>
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
                        {!! $promotion['description'] ?? '<p class="text-muted">Hech qanday tavsif mavjud emas</p>' !!}
                    </div>
                </div>
            @endif

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
    <div id="scannerModal" class="scannerModal">
        <div class="modal-content">
            <h4>{{ __('messages.scanner_title') }}</h4>
            <div id="scannerVideoWrapper"></div>
            <p class="hint">{{ __('messages.scanner_hint') }}</p>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="closeScannerModal()">
                    {{ __('messages.scanner_cancel') }}
                </button>
            </div>
        </div>
    </div>

    <div id="receiptModal" class="scannerModal">
        <div class="modal-content">
            <h4>{{ __('messages.receipt_title') }}</h4>
            <div id="receiptVideoWrapper"></div>
            <p class="hint">{{ __('messages.receipt_hint') }}</p>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="closeReceiptModal()">
                    {{ __('messages.scanner_cancel') }}
                </button>

            </div>
        </div>
    </div>
    <div id="codeModal" class="scannerModal" style="display:none;">
        <div class="modal-content">
            <h4>{{ __('messages.code_title') }}</h4>
            <form id="codeForm" onsubmit="submitCode(event)">
                <input type="text" id="manualCode" placeholder="{{ __('messages.code_placeholder') }}" required>
                <div class="d-flex justify-content-end gap-2 mt-2">
                    <button type="button" class="btn btn-secondary" onclick="closeCodeModal()">
                        {{ __('messages.scanner_cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="appModal" class="scannerModal">
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
                    @endif
                @endforeach
            </div>
            <button type="button" class="btn-close-modal mt-3" onclick="closeAppModal()">
                {{ __('messages.close') }}
            </button>
        </div>
    </div>

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

@section('scripts')
<script>
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    function openRatingPage(event, promoId) {
        event.preventDefault();

        // Tokenni olish
        const token = window.__ACCESS_TOKEN__ || getCookie('webapp_token');
        if (!token) {
            Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi",
                      "Token mavjud emas yoki muddati tugagan.", "error");
            return;
        }

        // Hard refresh bilan token query orqali yuborish
        const targetUrl = `/webapp/promotions/${promoId}/rating?token=${encodeURIComponent(token)}`;
        window.location.href = targetUrl;
    }
</script>
    <script>
        const CSRF_TOKEN = "{{ csrf_token() }}";

        if ({{ $hasSecretNumberType }}) {
            document.addEventListener("DOMContentLoaded", function() {
                // Agar progress_bar mavjud bo'lsa
                @if ($progress_bar)
                    const allPoints = {{ $progress_bar['daily_points'] }};
                    const liElements = document.querySelectorAll('.progress-container ul li');

                    // Har bir step markerining left pozitsiyasini aniqlash
                    liElements.forEach(li => {
                        const stepPoints = parseFloat(li.dataset.ball) || 0;

                        // Step foizini hisoblash
                        let percent = (stepPoints / allPoints) * 100;

                        // left CSS ni o'rnatish, markerni markazi hisobga olingan holda
                        li.style.left = `calc(${percent}% - 7px)`;
                    });

                    // Bugungi ball progress barini o'rnatish
                    const totalBall = {{ $progress_bar['today_poinst'] }};
                    const maxBall = {{ $progress_bar['daily_points'] }};
                    const progressPercent = Math.min((totalBall / maxBall) * 100, 100); // 100% dan oshmasligi

                    const progressBarEl = document.getElementById('progressBar');
                    if (progressBarEl) {
                        progressBarEl.style.width = progressPercent + '%';
                    }
                @endif
            });


            document.addEventListener("DOMContentLoaded", function() {
                const submitBtn = document.querySelector("#secretNumberForm button[type='submit']");
                if (!submitBtn) return;

                const originalText = submitBtn.innerHTML; // Asl holat (Yuborish)
                const iconHtml = `<i class="fa-solid fa-paper-plane"></i>`; // Ikonka holati

                function updateButton() {
                    if (window.innerWidth <= 600) {
                        // Faqat icon ko‘rsatish
                        if (submitBtn.innerHTML !== iconHtml) {
                            submitBtn.innerHTML = iconHtml;
                            submitBtn.style.padding = "12px 16px"; // biroz ixcham
                        }
                    } else {
                        // Asl holatga qaytarish
                        if (submitBtn.innerHTML !== originalText) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.style.padding = "12px 20px";
                        }
                    }
                }

                // Dastlab chaqiramiz
                updateButton();

                // Resize paytida ham tekshirib turamiz
                window.addEventListener("resize", updateButton);
            });

            document.addEventListener("DOMContentLoaded", function() {
                const secretForm = document.getElementById("secretNumberForm");
                if (!secretForm) return;

                secretForm.addEventListener("submit", async function(e) {

                    e.preventDefault(); // Formani default submitini to‘xtatamiz

                    const input = document.getElementById("secretNumber");
                    const value = input.value.trim();
                    if (!value) {
                        Swal.fire("❌ Xatolik", "Iltimos, sirli raqamni kiriting", "error");
                        return;
                    }
                    const token = window.__ACCESS_TOKEN__;
                    if (!token) {
                        Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi",
                            "Token mavjud emas yoki muddati tugagan.", "error");
                        return;
                    }

                    // Backendga yuborish URL
                    const promotionId = {{ $promotion['id'] }};
                    const url = "{{ secure_url('api/webapp/promotions') }}/" + promotionId +
                        "/secret-number"; // Agar token ishlatilsa

                    try {
                        // Loader ko‘rsatish (agar kerak bo‘lsa)
                        if (typeof showLoader === "function") showLoader();

                        const response = await fetch(url, {
                            method: "POST",
                            headers: {
                                "Authorization": `Bearer ${token}`,
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-Locale": document.getElementById('languageSwitcher').value,
                                "X-CSRF-TOKEN": CSRF_TOKEN,
                            },
                            body: JSON.stringify({
                                secret_number: value
                            })
                        });
                        const status = response.status;
                        let data = {};
                        try {
                            data = await response.json();
                        } catch (e) {}

                        hideLoader?.();

                        if (status === 422) {
                            // Validation xatoliklari
                            const errors = [];
                            if (data.message) errors.push(data.message);
                            if (data.errors) {
                                for (const key in data.errors) {
                                    if (Array.isArray(data.errors[key])) errors.push(...data.errors[
                                        key]);
                                    else errors.push(data.errors[key]);
                                }
                            }
                            await showMessagesSequential(errors.length ? errors : [
                                "Ma’lumotni tekshiring"
                            ], "error", "❌ Xatolik");
                            return;
                        }

                        if (status === 404) {
                            await showMessagesSequential([data.message || "Resurs topilmadi"],
                                "warning", "⚠️ Topilmadi");
                            return;
                        }

                        if (status >= 500) {
                            await showMessagesSequential([data.message || "Server xatoligi yuz berdi"],
                                "error", "❌ Ichki xatolik");
                            return;
                        }

                        // Muvaffaqiyatli holat
                        if (response.ok) {
                            const successMsg = data.message || "✅ Sirli raqam qabul qilindi!";
                            await showMessagesSequential([successMsg], "success", "✅ Muvaffaqiyatli");
                            input.value = "";
                        }

                    } catch (err) {
                        if (typeof hideLoader === "function") hideLoader();
                        Swal.fire("❌ Xatolik", `So'rov yuborilmadi: ${err.message}`, "error");
                        console.error(err);
                    }
                });

                // Helper function: Swal bilan xabarlarni ketma-ket ko‘rsatish
                async function showMessagesSequential(messages = [], icon = "info", title = "📣 E'lon") {
                    if (!Array.isArray(messages)) messages = [messages];
                    for (const msg of messages) {
                        if (!msg) continue;
                        await Swal.fire({
                            title: title,
                            text: msg,
                            icon: icon,
                            confirmButtonText: "OK"
                        });
                    }
                }
            });
        }
        // Backenddan keladigan umumiy ball
    </script>
    <script>
        const promotionId = {{ $promotion['id'] }};
        let scannerQrCode = null;
        let receiptQrCode = null;


        function openAppModal(e) {
            e.preventDefault();
            document.getElementById('appModal').style.display = 'flex';
        }

        function closeAppModal() {
            document.getElementById('appModal').style.display = 'none';
        }

        function openSmsModal(e) {
            e.preventDefault();
            document.getElementById('smsModal').style.display = 'flex';
        }

        function closeSmsModal() {
            document.getElementById('smsModal').style.display = 'none';
        }

        function openCodeModal(event) {
            event.preventDefault();
            document.getElementById('codeModal').style.display = 'flex';
        }

        function closeCodeModal() {
            document.getElementById('codeModal').style.display = 'none';
        }

        function submitCode(e) {
            e.preventDefault();
            const code = document.getElementById("manualCode").value.trim();
            if (!code) {
                Swal.fire("❌ Xatolik", "Iltimos, promo kodni kiriting", "error");
                return;
            }

            closeCodeModal(); // modalni yopamiz
            const url = "{{ secure_url('api/webapp/promotions') }}/" + promotionId + "/promocode";

            sendToServer(url, code, "promocode");
        }

        function showLoader() {
            const el = document.getElementById("globalLoader");
            if (el) el.style.display = "flex";
        }

        function hideLoader() {
            const el = document.getElementById("globalLoader");
            if (el) el.style.display = "none";
        }
        async function sendToServer(url, qrValue, type = "code") {
            if (!token) {
                Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi", "Token mavjud emas yoki muddati tugagan.", "error");
                return;
            }

            const payload = {
                promocode: qrValue
            };

            try {
                showLoader();

                const resp = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Locale": document.getElementById('languageSwitcher').value,

                    },
                    body: JSON.stringify(payload)
                });

                const statusCode = resp.status;
                let serverData = {};
                try {
                    serverData = await resp.json();
                } catch (e) {
                    serverData = {};
                } finally {
                    hideLoader();
                }

                // --- 🔴 Agar 422 qaytsa (Laravel validation yoki custom error) ---
                if (statusCode === 422) {
                    const errors = [];

                    if (serverData.message) errors.push(serverData.message);
                    if (serverData.errors) {
                        for (const key in serverData.errors) {
                            if (Array.isArray(serverData.errors[key])) {
                                errors.push(...serverData.errors[key]);
                            } else {
                                errors.push(serverData.errors[key]);
                            }
                        }
                    }

                    await showMessagesSequential(
                        errors.length ? errors : ["Ma’lumotni tekshiring"],
                        "error",
                        "❌ Xatolik"
                    );
                    return;
                }

                // --- ❌ Boshqa muvaffaqiyatsiz statuslar ---
                const isSuccess = resp.ok && serverData.status && ["success", "win", "pending"].includes(serverData
                    .status);
                if (!isSuccess) {
                    const errMsg = serverData.message || "Xatolik, birozdan so‘ng qayta urinib ko‘ring";
                    await showMessagesSequential(errMsg, "error", "❌ Xatolik");
                    return;
                }

                // --- ✅ Muvaffaqiyatli holat ---
                if (serverData.message) {
                    await showMessagesSequential(serverData.message, "success", "✅ Muvaffaqiyatli");
                }

                if (type === "code" && serverData.data) {
                    const receipt = serverData.data;
                    const receiptHtml = `
    <div style="font-family: monospace; max-width: 360px; margin: 0 auto; border: 1px dashed #999; padding: 15px; background: #fafafa; color: #000;">
        <h3 style="text-align:center; margin:0; font-size:16px; font-weight:bold; color:#1a73e8;">${receipt.name ?? ''}</h3>
        <p style="text-align:center; margin:2px 0; font-size:12px; color:#555;">${receipt.address ?? ''}</p>
        <hr style="border:0; border-top:1px dashed #ccc; margin:6px 0;">
        <p style="margin:2px 0; font-size:12px;"><b>Chek ID:</b> ${receipt.chek_id ?? '-'}</p>
        <p style="margin:2px 0; font-size:12px;"><b>NKM:</b> ${receipt.nkm_number ?? '-'}</p>
        <p style="margin:2px 0; font-size:12px;"><b>SN:</b> ${receipt.sn ?? '-'}</p>
        <p style="margin:2px 0; font-size:12px;"><b>Sana:</b> ${receipt.check_date ?? '-'}</p>
        <hr style="border:0; border-top:1px dashed #ccc; margin:6px 0;">
        <div style="font-size:12px;">
            ${(receipt.products || []).map(p => `
                                            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px dotted #eee;">
                                                <div style="flex:1; text-align:left;">${p.name ?? ''}</div>
                                                <div style="flex:0 0 90px; text-align:right;">
                                                    <div style="font-size:11px; color:#888;">x${p.count ?? 1}</div>
                                                    <div style="font-size:12px; font-weight:bold; color:#2e7d32;">
                                                        ${(Number(p.summa) || 0).toLocaleString()} so'm
                                                    </div>
                                                </div>
                                            </div>
                                        `).join("")}
        </div>
        <hr style="border:0; border-top:1px dashed #ccc; margin:6px 0;">
        <p style="text-align:right; font-size:14px; font-weight:bold; margin:4px 0; color:#d32f2f;">
            Jami: ${(Number(receipt.summa) || 0).toLocaleString()} so'm
        </p>
        <p style="text-align:right; font-size:12px; margin:0; color:#444;">QQS: ${receipt.qqs_summa ?? '-'}</p>
    </div>
`;

                    Swal.fire({
                        title: "✅ Muvaffaqiyatli",
                        html: receiptHtml,
                        icon: "success",
                        width: 400,
                        showConfirmButton: true,
                    });
                }

            } catch (e) {
                hideLoader();
                Swal.fire("❌ Xatolik", `Load failed: ${e.message}\nURL: ${url}`, "error");
            }
        }

        // --- Yangi element yaratish ---
        function createScannerElement(wrapperId, prefix) {
            const wrapper = document.getElementById(wrapperId);
            wrapper.innerHTML = "";
            const newId = prefix + "_" + Date.now();
            const div = document.createElement("div");
            div.id = newId;
            div.style.width = "100%";
            div.style.height = "100%";
            wrapper.appendChild(div);
            return newId;
        }

        // --- Scanner boshlash helper ---
        async function startQrScanner(elId, onDecoded) {
            const qr = new Html5Qrcode(elId);
            const qrBoxSize = Math.floor(Math.min(window.innerWidth, window.innerHeight) * 0.8);

            await qr.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: {
                        width: qrBoxSize,
                        height: qrBoxSize
                    },
                    aspectRatio: 1.0
                },
                (decodedText) => {
                    onDecoded(decodedText, qr);
                },
                (err) => {
                    if (typeof err === "string" &&
                        (err.includes("No barcode") || err.includes("No MultiFormat Readers"))) {
                        return;
                    }
                    console.warn("Scanner error:", err);
                }
            ).catch(e => console.error("Scanner start error:", e));

            return qr;
        }

        // --- Promo Scanner Modal ---
        async function openScannerModal(e) {
            e?.preventDefault();
            document.getElementById("scannerModal").style.display = "flex";

            const elId = createScannerElement("scannerVideoWrapper", "scannerVideo");
            scannerQrCode = await startQrScanner(elId, (decodedText, qr) => {
                qr.pause(); // kod topilganda pauza

                Swal.fire({
                    title: "📦 Promo kod topildi!",
                    html: `<b>${decodedText}</b><br>Sizning promo kodingiz`,
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonText: "Yuborish",
                    cancelButtonText: "Bekor qilish"
                }).then((result) => {
                    if (result.isConfirmed) {
                        closeScannerModal(); // scanner to‘xtaydi
                        sendToServer("{{ secure_url('api/webapp/promotions') }}/" + promotionId +
                            "/promocode", decodedText, "promocode");
                    } else {
                        qr.resume(); // scanner qayta ishlashni davom ettiradi
                    }
                });
            });
        }

        async function closeScannerModal() {
            document.getElementById("scannerModal").style.display = "none";
            if (scannerQrCode) {
                await scannerQrCode.stop().catch(() => {});
                await scannerQrCode.clear().catch(() => {});
                scannerQrCode = null;
            }
        }

        // --- Receipt Scanner Modal ---
        async function openReceiptModal(e) {
            e?.preventDefault();
            document.getElementById("receiptModal").style.display = "flex";
            const wrapper = document.getElementById("receiptVideoWrapper");
            if (!wrapper) {
                alert("Receipt wrapper element topilmadi");
                return;
            }


            const elId = createScannerElement("receiptVideoWrapper", "receiptVideo");
            receiptQrCode = await startQrScanner(elId, (decodedText, qr) => {
                qr.pause();

                Swal.fire({
                    title: "Check kodingiz topildi!",
                    html: `<b>${decodedText}</b><br>Sizning check kodingiz`,
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonText: "Yuborish",
                    cancelButtonText: "Bekor qilish"
                }).then((result) => {
                    if (result.isConfirmed) {
                        closeReceiptModal();
                        sendToServer("{{ secure_url('api/webapp/promotions') }}/" + promotionId +
                            "/receipt", decodedText, "code");
                    } else {
                        qr.resume();
                    }
                });
            });
        }

        async function closeReceiptModal() {
            document.getElementById("receiptModal").style.display = "none";
            if (receiptQrCode) {
                await receiptQrCode.stop().catch(() => {});
                await receiptQrCode.clear().catch(() => {});
                receiptQrCode = null;
            }
        }
        async function showMessagesSequential(messages = [], icon = "info", title = "📣 E'lon") {
            if (!Array.isArray(messages)) messages = [messages];
            for (const msg of messages) {
                if (!msg) continue;
                await Swal.fire({
                    title: title,
                    text: msg,
                    icon: icon,
                    confirmButtonText: "OK"
                });
            }
        }
    </script>

@endsection
