@extends('admin.layouts.app')
@section('title', "Sovg'ani tahrirlash")

@push('scripts')
    <style>
        .strategy-card {
            transition: all 0.3s ease;
        }

        .strategy-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.2rem rgba(0, 128, 0, 0.2);
        }

        .rule-tabs {
            -webkit-overflow-scrolling: touch;
        }

        .rule-forms-container {
            position: relative;
            min-height: 320px;
            /* Yoki sizda eng baland formaning balandligiga qarab */
        }

        .rule-form {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: opacity 0.2s ease-in-out;
        }

        .rule-form.d-none {
            opacity: 0;
            pointer-events: none;
        }

        .rule-form.active {
            opacity: 1;
            pointer-events: auto;
        }

        .rule-forms-container {
            position: relative;
            min-height: 320px;
        }

        .rule-form {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
            z-index: 0;
        }

        .rule-form.active {
            opacity: 1;
            pointer-events: auto;
            z-index: 1;
        }

        .rule-tabs {
            scrollbar-width: thin;
            scrollbar-color: #0d6efd20 #f1f1f1;
            /* For Firefox */
            scroll-behavior: smooth;
        }

        /* Chrome, Edge, Safari */
        .rule-tabs::-webkit-scrollbar {
            height: 8px;
        }

        .rule-tabs::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .rule-tabs::-webkit-scrollbar-thumb {
            background-color: #0d6efd60;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .rule-tabs::-webkit-scrollbar-thumb:hover {
            background-color: #0d6efd;
        }
    </style>
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_multiselect.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>


    <script>
        function confirmDelete(ruleId) {
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Ushbu qoida qiymati o‘chirilsinmi?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + ruleId).submit();
                } else {
                    toastr.info('O‘chirish bekor qilindi');
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const allPanels = document.querySelectorAll('.table-panel');
            let currentlyOpen = document.querySelector('#collapse-smart');
            if (currentlyOpen) {
                const defaultInstance = bootstrap.Collapse.getOrCreateInstance(currentlyOpen);
                defaultInstance.show();

                // Default aktiv tugma topiladi va unga active qo‘yiladi
                document.querySelectorAll('.collapse-toggler').forEach(btn => {
                    const targetId = btn.getAttribute('data-target');
                    if (targetId === '#collapse-smart') {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }
            document.querySelectorAll('.collapse-toggler').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const target = document.querySelector(targetId);

                    // Boshqa panel ochiq bo‘lsa, yopiladi
                    if (currentlyOpen && currentlyOpen !== target) {
                        const currentInstance = bootstrap.Collapse.getOrCreateInstance(
                            currentlyOpen);
                        currentInstance.hide();
                    }

                    const targetInstance = bootstrap.Collapse.getOrCreateInstance(target);

                    if (!target.classList.contains('show')) {
                        targetInstance.show();
                        currentlyOpen = target;
                        document.querySelectorAll('.collapse-toggler').forEach(btn => btn.classList
                            .remove('active'));
                        this.classList.add('active');
                    } else {
                        targetInstance.hide();
                        currentlyOpen = null;
                        this.classList.remove('active');
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.rule-tab');
            const forms = document.querySelectorAll('.rule-form');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const ruleId = this.dataset.ruleId;

                    // Toggle active class for tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Toggle active class for forms
                    forms.forEach(form => {
                        form.classList.remove('active');
                        form.classList.add('d-none');
                    });

                    const activeForm = document.getElementById(`rule-form-${ruleId}`);
                    if (activeForm) {
                        activeForm.classList.remove('d-none');
                        activeForm.classList.add('active');
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.rule-tab');
            const forms = document.querySelectorAll('.rule-form');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const ruleId = this.dataset.ruleId;

                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    forms.forEach(form => form.classList.remove('active'));

                    const activeForm = document.getElementById(`rule-form-${ruleId}`);
                    if (activeForm) {
                        activeForm.classList.add('active');
                    }
                });
            });
        });
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            const promotionId = "{{ $prize['id'] ?? ($prize->id ?? 'unknown') }}";
            const url = "{{ route('admin.promocode.prizedata', $prize['id'], false) }}";
            // const promotionId = "3";
            // const url = "{{ route('admin.promocode.promocodedata', 3, false) }}";
            if ($.fn.DataTable.isDataTable('#promocode-table')) {
                $('#promocode-table').DataTable().destroy();
            }

            $('#promocode-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [{
                        data: 'id',
                        name: 'promo_codes.id'
                    },
                    {
                        data: 'promocode',
                        name: 'promo_codes.promocode'
                    },
                    {
                        data: 'is_used',
                        name: 'promo_codes.is_used'
                    },
                    {
                        data: 'used_at',
                        name: 'promo_codes.used_at',
                        searchable: false
                    },
                    {
                        data: 'generation_name',
                        name: 'promo_codes.generation_id',
                        searchable: false
                    },
                    {
                        data: 'platform',
                        name: 'platform_name',
                        searchable: false

                    }, // ✅ ALIAS nomi
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                buttons: [{
                        extend: 'copy',
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- all sahifalarni oladi
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: promotionId + '-promotion_promocodelari', // dinamik nom
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: promotionId + '-promotion_promocodelari',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ]
            });

        });
    </script>
@endpush
@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">🎁 Sovg'a ma'lumotlarini tahrirlash</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.prize.update', $prize['id']) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- 📌 Sovg'a asosiy ma'lumotlar --}}
                <div class="row">
                    <!-- Sovg'a nomi -->
                    <div class="col-6">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">🎁 Sovg‘a nomi</label>
                                <input type="text" name="name" value="{{ old('name', $prize['name']) }}"
                                    class="form-control" required>
                                <small class="form-text text-muted d-block mt-1">
                                    Sovg‘aning foydalanuvchiga ko‘rsatiladigan nomini kiriting. Masalan: <em>"iPhone 14",
                                        "Chegirma kuponi", "Maxfiy paket"</em>.
                                </small>
                            </div>

                            <!-- Sovg'a kategoriyasi -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">📂 Sovg‘a kategoriyasi</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Tanlang...</option>
                                    @foreach ($prizecategory as $category)
                                        <option value="{{ $category['id'] }}"
                                            {{ old('category_id', $prize['category_id']) == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['display_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted d-block mt-1">
                                    Sovg‘a qanday turdagi bo‘lishini belgilang: <em>avtomatik (random), qo‘l bilan (manual),
                                        smart (shartli)</em>.
                                </small>
                            </div>

                            <!-- Promotion (readonly) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">📢 Aksiya (Promotion)</label>
                                <select class="form-select" disabled>
                                    <option selected>{{ $prize['promotion']['name']['uz'] ?? 'Tanlanmagan' }}</option>
                                </select>
                                <input type="hidden" name="promotion_id" value="{{ $prize['promotion']['id'] }}">
                                <small class="form-text text-muted d-block mt-1">
                                    Bu sovg‘a qaysi aksiya (kampaniya)ga tegishli ekanligini bildiradi. Ushbu qiymat
                                    o‘zgartirilmaydi.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Tavsif -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">📝 Tavsif</label>
                        <textarea name="description" class="form-control" rows="6" required>{{ old('description', $prize['description']) }}</textarea>
                        <small class="form-text text-muted d-block mt-1">
                            Foydalanuvchiga sovg‘a haqida ko‘proq ma'lumot bering. Bu joyda sovg‘aning qanday
                            ishlatilishi, unikal xususiyatlari yoki foydalanish qoidalari haqida yozishingiz mumkin.
                        </small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label fw-semibold">🎁 Sovg‘alar soni (Umumiy miqdor)</label>
                        <input type="number" name="quantity" id="quantity"
                            class="form-control @error('quantity') is-invalid @enderror"
                            value="{{ old('quantity', $prize['quantity'] ?? 0) }}" min="0" required>
                        <small class="form-text text-muted d-block mt-1">
                            Aksiyada taqdim etiladigan <strong>umumiy sovg‘alar sonini</strong> kiriting. <br>
                            Masalan: 100 — jami 100 ta sovg‘a mavjud bo‘ladi.
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="dailyLimit" class="form-label fw-semibold">📅 Kunlik limit (ixtiyoriy)</label>
                        <input type="number" name="daily_limit" id="dailyLimit"
                            class="form-control @error('daily_limit') is-invalid @enderror"
                            value="{{ old('daily_limit', $prize['daily_limit'] ?? '') }}" min="0">
                        <small class="form-text text-muted d-block mt-1">
                            Bir kunda maksimal necha sovg‘a yutib olinishi mumkinligini belgilang. <br>
                            Agar <strong>cheklanmasin</strong> desangiz — bo‘sh qoldiring.
                        </small>
                        @error('daily_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="indexInput" class="form-label fw-semibold">
                            🏅 Sovg'a darajasi (Index)
                        </label>
                        <input type="number" name="index" id="indexInput"
                            class="form-control @error('index') is-invalid @enderror"
                            value="{{ old('index', $prize['index'] ?? '') }}" placeholder="Masalan: 1" min="1"
                            required>
                        <small class="form-text text-muted d-block mt-1">
                            Son qanchalik <strong>kichik</strong> bo‘lsa, sovg‘a shunchalik <strong>muhim</strong>
                            hisoblanadi.
                            Sovg‘alar shu qiymat asosida ustuvorlik bilan taqsimlanadi (1 - eng ustuvor).
                        </small>
                    </div>
                    @php
                        $category = $prize['category'];
                    @endphp
                    @if ($category['name'] === 'weighted_random')
                        <div class="col-md-4 mb-3">
                            <label for="probabilityWeight" class="form-label fw-semibold">
                                🎲 Yutuq ehtimoli og‘irligi (Probability weight)
                            </label>
                            <input type="number" name="probability_weight" id="probabilityWeight"
                                class="form-control @error('probability_weight') is-invalid @enderror"
                                value="{{ old('probability_weight', $prize['probability_weight'] ?? 100) }}"
                                placeholder="Masalan: 100" min="0" required>
                            <small class="form-text text-muted d-block mt-1">
                                Bu qiymat sovg‘aning tanlanish ehtimoliga ta’sir qiladi.
                                Qancha katta bo‘lsa, yutuq shunchalik yutish extimoli <strong>kichiklashib boradi</strong>
                                Masalan, 100000 — eng kam, 1 — eng ko‘p.
                            </small>
                        </div>
                    @endif

                    <div class="col-md-4 mb-3">
                        <label class="form-label">🟢 Boshlanish vaqti</label>
                        <input type="datetime-local" name="valid_from" class="form-control"
                            value="{{ old('valid_from', \Carbon\Carbon::parse($prize['valid_from'])->format('Y-m-d\TH:i')) }}">
                        <small class="form-text text-muted d-block mt-1">
                            Sovg‘aning foydalanuvchi tomonidan yutib olinishi <strong>qachondan boshlanishi</strong>
                            kerakligini bu yerda belgilang.
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">🔴 Tugash vaqti</label>
                        <input type="datetime-local" name="valid_until" class="form-control"
                            value="{{ old('valid_until', \Carbon\Carbon::parse($prize['valid_until'])->format('Y-m-d\TH:i')) }}">
                        <small class="form-text text-muted d-block mt-1">
                            Sovg‘aning foydalanuvchilarga yutib berilishi <strong>qachongacha davom etishini</strong> bu
                            yerda belgilang.
                        </small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="is_active"
                                value="1" {{ old('is_active', $prize['is_active'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActiveSwitch">🔛 Sovg‘ani
                                faollashtirish</label>
                        </div>
                        <small class="form-text text-muted d-block mt-1">
                            Agar ushbu tugma yoqilgan (<strong>faol</strong>) bo‘lsa — foydalanuvchilar bu sovg‘ani
                            <strong>yutib olishlari mumkin</strong>. <br>
                            Aks holda, sovg‘a <span class="text-danger">nofaol</span> deb hisoblanadi va <strong>pausaga
                                olinadi</strong>.
                            Uni qayta faollashtirmaguncha ishtirokchilarga berilmaydi.
                        </small>
                    </div>
                </div>


                {{-- 🔘 Submit --}}
                <div class="d-flex justify-content-end">
                    <a href="
                {{-- {{ route('admin.prize.index') }} --}}
                 "
                        class="btn btn-outline-secondary me-2">Bekor qilish</a>
                    <button type="submit" class="btn btn-primary">Yangilash</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        @php
            $platforms = $prize['promotion']['platforms'] ?? [];
            $hasSms = collect($platforms)->contains(fn($p) => $p['name'] === 'sms');
            $hasOtherPlatforms = collect($platforms)->contains(fn($p) => $p['name'] !== 'sms');
            $participationTypes = collect($prize['promotion']['participation_types'] ?? []);
            $hasSmartRandom = $category['name'] === 'smart_random';
            $hasAutoBind = $category['name'] === 'auto_bind';
            $hasReceiptScan = $participationTypes->contains(fn($type) => $type['slug'] === 'receipt_scan');
            $langs = ['uz' => "O'zbek", 'ru' => 'Русский', 'kr' => 'Кирилл'];

            // Message'larni kalit bo‘yicha yig‘amiz (platform:participant_type:message_type)
$existingMessages = collect($prize['message'] ?? [])->keyBy(
    fn($msg) => implode(':', [$msg['platform'], $msg['participant_type'], $msg['message_type']]),
);

$messageForms = [
    [
        'show' => $hasSms && $hasSmartRandom,
        'title' => '🧠 Smart Random – SMS uchun',
        'color' => 'info',
        'platform' => 'sms',
        'participant_type' => 'smart_random',
        'message_type' => 'success',
    ],
    [
        'show' => $hasSms && $hasReceiptScan,
        'title' => '📸 Receipt Scan – SMS uchun',
        'color' => 'warning',
        'platform' => 'sms',
        'participant_type' => 'receipt_scan',
        'message_type' => 'success',
    ],
    [
        'show' => $hasOtherPlatforms && $hasSmartRandom,
        'title' => '🌐 Smart Random – Platformalar uchun',
        'color' => 'info',
        'platform' => 'all',
        'participant_type' => 'smart_random',
        'message_type' => 'success',
    ],
    [
        'show' => $hasOtherPlatforms && $hasReceiptScan,
        'title' => '🌐 Receipt Scan – Platformalar uchun',
        'color' => 'warning',
        'platform' => 'all',
        'participant_type' => 'receipt_scan',
        'message_type' => 'success',
    ],
    [
        'show' => $hasOtherPlatforms,
        'title' => '🌐 Umumiy Success – Platformalar uchun',
        'color' => 'success',
        'platform' => 'all',
        'participant_type' => 'all',
        'message_type' => 'success',
    ],
    [
        'show' => $hasSms,
        'title' => '📩 Umumiy Success – SMS uchun',
        'color' => 'success',
        'platform' => 'sms',
        'participant_type' => 'all',
        'message_type' => 'success',
                ],
            ];
        @endphp
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sovg'a ma'lumotlari</h5>
            <div class="btn-group">
                <!-- Xabar sozlamalari: chat/message ikonkasi -->
                <button type="button" class="btn btn-outline-success collapse-toggler" data-target="#collapse-message">
                    <i class="ph ph-chat-centered-text me-1"></i> Xabar sozlamalari
                </button>

                <!-- Harakatlar: action/bolt/activity ikonkasi -->
                <button type="button" class="btn btn-outline-success collapse-toggler" data-target="#collapse-actions">
                    <i class="ph ph-lightning me-1"></i> Harakatlar
                </button>

                <!-- Smart shartlar: brain/settings/magic wand ikonkasi -->
                @if ($hasSmartRandom)
                    <button type="button" class="btn btn-outline-success collapse-toggler"
                        data-target="#collapse-smart">
                        <i class="ph ph-brain me-1"></i> Smart shartlar
                    </button>
                @endif
                @if ($hasAutoBind)
                    <button type="button" class="btn btn-outline-success collapse-toggler" data-target="#collapse-auto">
                        <i class="ph ph-link me-1"></i> Bog'langan promocodelar
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="collapse table-panel" id="collapse-message">
                <div class="border rounded p-4">
                    <h4 class="mb-4 fw-bold">📩 Xabar sozlamalari</h4>


                    <div class="row">
                        @foreach ($messageForms as $formIndex => $form)
                            @if ($form['show'])
                                @php
                                    $uid = 'msgform-' . $formIndex;
                                    $key = implode(':', [
                                        $form['platform'],
                                        $form['participant_type'],
                                        $form['message_type'],
                                    ]);
                                    $existing = $existingMessages[$key] ?? null;
                                    $existingId = $existing['id'] ?? null;
                                    $existingTexts = $existing['message'] ?? [];
                                @endphp

                                <div class="col-md-6 mb-3">
                                    <form method="POST" action="{{ route('admin.prize.message.store', $prize['id']) }}"
                                        class="card shadow-sm strategy-card border border-{{ $form['color'] }} rounded p-4 h-100 bg-light">
                                        @csrf

                                        {{-- Yashirin inputlar --}}
                                        <input type="hidden" name="platform" value="{{ $form['platform'] }}">
                                        <input type="hidden" name="participant_type"
                                            value="{{ $form['participant_type'] }}">
                                        <input type="hidden" name="message_type" value="{{ $form['message_type'] }}">
                                        @if ($existingId)
                                            <input type="hidden" name="id" value="{{ $existingId }}">
                                        @endif

                                        <div class="card-header bg-{{ $form['color'] }} text-white mb-3 rounded">
                                            <strong>{{ $form['title'] }}</strong>
                                        </div>

                                        <div class="card-body p-0">
                                            <ul class="nav nav-tabs mb-2" role="tablist">
                                                @foreach ($langs as $langCode => $langLabel)
                                                    <li class="nav-item">
                                                        <a class="nav-link @if ($loop->first) active @endif"
                                                            data-bs-toggle="tab"
                                                            href="#{{ $uid }}-{{ $langCode }}"
                                                            role="tab">
                                                            {{ $langLabel }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <div class="tab-content">
                                                @foreach ($langs as $langCode => $langLabel)
                                                    <div class="tab-pane fade @if ($loop->first) show active @endif"
                                                        id="{{ $uid }}-{{ $langCode }}" role="tabpanel">
                                                        <textarea class="form-control mb-2" name="message[{{ $langCode }}]" rows="3" required
                                                            placeholder="{{ $langLabel }} tilidagi xabar">{{ old("message.$langCode") ?? ($existingTexts[$langCode] ?? '') }}</textarea>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-{{ $form['color'] }}">
                                                Saqlash</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="collapse table-panel" id="collapse-actions">
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="page-title mb-0">Xarakatlar jadvali jadvali</h4>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Promocode</th>
                                <th>Foydalanilgan</th>
                                <th>Foydalanilgan vaqti</th>
                                <th>Generation</th>
                                <th>Platforma</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            @push('scripts')
            @endpush
            <!-- Platforms -->
            @if ($hasSmartRandom)

                <div class="card-body p-0" id="collapse-smart">
                    <div class="mb-3">
                        <h6 class="fw-semibold text-muted mb-0">Smart Random shartlari va yutuqli promocodelar</h6>
                    </div>
                     @php
        // ruleValues ni string key qilib olish
        $ruleValues = collect($prize['smart_random_values'] ?? [])
            ->mapWithKeys(function ($item) {
                return [(string) $item['rule_id'] => $item];
            });

        // Smart rules ni tartiblash — avval qiymati borlari, keyin qolganlar
        $sortedSmartRules = collect($smartRule)
            ->sortByDesc(function ($rule) use ($ruleValues) {
                return $ruleValues->has((string) $rule['id']) ? 1 : 0;
            })
            ->values();
    @endphp

                    <ul class="nav nav-tabs mb-3 overflow-auto flex-nowrap border-bottom rule-tabs" role="tablist"
                        style="scrollbar-width: thin;">
                        @foreach ($sortedSmartRules as $index => $rule)
                            @php
                                $hasValue = $ruleValues->has($rule['id']);
                            @endphp
                            <li class="nav-item" role="presentation">
                                <a class="nav-link @if ($index === 0) active @endif @if ($hasValue) text-primary fw-semibold @endif"
                                    id="rule-tab-{{ $rule['id'] }}" data-bs-toggle="tab"
                                    href="#rule-content-{{ $rule['id'] }}" role="tab"
                                    style="min-width: 160px; max-width: 220px; white-space: normal;">
                                    {{ $rule['label'] }}
                                    @if ($hasValue)
                                        <span class="badge bg-primary">✓</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach ($smartRule as $index => $rule)
                            @php
                                 $valueData = $ruleValues->get((string) $rule['id']) ?? [];
    $operator = $valueData['operator'] ?? '';
    $values = $valueData['values'] ?? [];
                            @endphp

                            <div class="tab-pane fade @if ($index === 0) show active @endif"
                                id="rule-content-{{ $rule['id'] }}" role="tabpanel">
                                <div class="border rounded p-3">
                                    <form method="POST"
                                        action="{{ route('admin.prize.smartrules.updateOrCreate', $prize['id']) }}">
                                        @csrf

                                        {{-- Hidden inputs --}}
                                        <input type="hidden" name="rule_id" value="{{ $rule['id'] }}">


                                        {{-- Title --}}
                                        <h6 class="fw-semibold text-primary mb-2">{{ $rule['label'] }}</h6>

                                        {{-- Description --}}
                                        @if (!empty($rule['description']))
                                            <p class="text-muted small mb-3">{{ $rule['description'] }}</p>
                                        @endif

                                        {{-- Input Section --}}
                                        <div class="row align-items-end">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label text-muted">Operator</label>
                                                @php
                                                    $accepted = $rule['accepted_operators'] ?? [];
                                                @endphp

                                                <select name="operator" required class="form-select form-select-sm">
                                                    @if (count($accepted) > 1)
                                                        <option value="" disabled @selected(empty($operator))>
                                                            -- operatorni tanlang --
                                                        </option>
                                                    @endif

                                                    @foreach ($accepted as $op)
                                                        <option value="{{ $op }}" @selected($operator === $op || (count($accepted) === 1 && empty($operator)))>
                                                            {{ $op }}
                                                            @switch($op)
                                                                @case('=')
                                                                    (teng)
                                                                @break

                                                                @case('!=')
                                                                    (teng emas)
                                                                @break

                                                                @case('>')
                                                                    (katta)
                                                                @break

                                                                @case('>=')
                                                                    (katta yoki teng)
                                                                @break

                                                                @case('<')
                                                                    (kichik)
                                                                @break

                                                                @case('<=')
                                                                    (kichik yoki teng)
                                                                @break
                                                            @endswitch
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-8 mb-3">
                                                <label class="form-label text-muted">Qiymat(lar)</label>
                                                @if ($rule['input_type'] === 'number')
                                                    <input type="number" class="form-control form-control-sm" required
                                                        name="values" value="{{ $values[0] ?? '' }}"
                                                        placeholder="Masalan: 100">
                                                @else
                                                    <textarea class="form-control form-control-sm" required name="values" rows="2"
                                                        placeholder="Masalan: A, PROMO, 1X, 2025">{{ implode(', ', $values ?? []) }}</textarea>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Footer --}}
                                        <div class="border-top pt-2 mt-3 text-muted small">
                                            Qiymatlar qoidaga muvofiq tekshiriladi.
                                        </div>
                                    </form>
                                    <div class="mt-3 d-flex justify-content-end gap-2">
                                        @if ($values)
                                            <form method="POST" id="delete-form-{{ $rule['id'] }}"
                                                action="{{ route('admin.prize.smartrules.delete', [$prize['id'], $rule['id']]) }}"
                                                class="m-0 d-inline">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete({{ $rule['id'] }})">
                                                    <i class="ph ph-trash me-1"></i> O‘chirish
                                                </button>
                                            </form>
                                        @endif

                                        <button type="submit" form="rule-form-{{ $rule['id'] }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Saqlash
                                        </button>
                                    </div>
                                </div>



                            </div>
                        @endforeach

                    </div>

                </div>
                <div class="border rounded mt-4 p-3">
                    <div class="page-header-content d-flex justify-content-between align-items-center">
                        <h4 class="page-title mb-0">Smart shartga to'gri keladigan promocodelar jadvali</h4>
                    </div>
                    <table id="promocode-table" class="table datatable-button-init-basic">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Promocode</th>
                                <th>Foydalanilgan</th>
                                <th>Foydalanilgan vaqti</th>
                                <th>Generation</th>
                                <th>Platforma</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>

                </div>
            @endif
            @if ($hasAutoBind)
                <div class="border rounded mt-4 p-3" id="collapse-auto">
                    <div class="page-header-content d-flex justify-content-between align-items-center">
                        <h4 class="page-title mb-0">Shu yuquqga bog'langan promocodelar</h4>
                    </div>
                    <table id="auto-promocode-table" class="table datatable-button-init-basic">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Promocode</th>
                                <th>Foydalanilgan</th>
                                <th>Foydalanilgan vaqti</th>
                                <th>Generation</th>
                                <th>Platforma</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endif
            <!-- Receipt -->
            <div class="collapse table-panel" id="collapse-receipt">
                <div class="border rounded p-3">
                    <h4 class="page-title mb-3">Receipt scan ma'lumotlari</h4>
                    <p class="text-muted">Bu bo‘limda receipt orqali ishtirok etish parametrlari ko‘rsatiladi.</p>
                </div>
            </div>


        </div>
    </div>
@endsection
