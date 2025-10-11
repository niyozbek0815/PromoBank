@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
    @endphp

    <section class="banner">
        <div class="content">
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
                    <p>{{ $promotion['description'] ?? '' }}</p>
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
                        {{ __('messages.scanner_submit') }}
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
                    @elseif($type === 'telegram')
                        <a href="{{ $url }}" target="_blank" class="btn-download telegram">
                            <i class="fa-brands fa-telegram me-2"></i> Telegram
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
    const token = window.__ACCESS_TOKEN__;
    if (!token) {
        Swal.fire("❌ Ro‘yxatdan o‘tish xatoligi", "Token mavjud emas yoki muddati tugagan", "error");
        return;
    }

    const payload = { promocode: qrValue };

    try {
        showLoader();

        const resp = await fetch(url, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json",
                "Accept": "application/json"
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
        if (!resp.ok || serverData.status === "failed" || serverData.status === "error") {
            await showMessagesSequential(
                serverData.message || "Xatolik, birozdan so‘ng qayta urinib ko‘ring",
                "error",
                "❌ Xatolik"
            );
            return;
        }

        // --- ✅ Muvaffaqiyatli holat ---
        if (serverData.message) {
            await showMessagesSequential(serverData.message, "success", "✅ Muvaffaqiyatli");
        }

if (type === "receipt" && serverData.data) {
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
