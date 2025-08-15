@extends('admin.layouts.app')
@section('title', "Promoaksiya qo'shish")
@push('scripts')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <style>
        .strategy-card {
            transition: all 0.3s ease;
        }

        .strategy-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.2rem rgba(0, 128, 0, 0.2);
        }
    </style>
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_multiselect.js') }}"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js">
    </script>
    <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ✅ CKEditor init
            document.querySelectorAll('.ckeditor').forEach(function(el) {
                ClassicEditor
                    .create(el)
                    .then(editor => {
                        editor.model.document.on('change:data', () => {
                            el.value = editor
                                .getData(); // hidden textarea'ga qiymatni doim saqlab bor
                        });
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });

            // ✅ FilePond pluginlar ro'yxatga olinadi
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview,
            );

            // ✅ Offer file
            const offerInput = document.querySelector('.filepond-offer');
            if (offerInput) {
                FilePond.create(offerInput, {
                    allowMultiple: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    allowReorder: false,
                    instantUpload: false,
                    acceptedFileTypes: [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.oasis.opendocument.text',
                        'application/rtf',
                        'text/plain'
                    ],
                    labelIdle: 'Ommaviy ofertani bu yerga yuklang yoki tanlang',
                });
            }

            // ✅ Banner
            const bannerInput = document.querySelector('.filepond-banner');
            if (bannerInput) {
                FilePond.create(bannerInput, {
                    allowMultiple: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    allowReorder: false,
                    instantUpload: false,
                    acceptedFileTypes: ['image/*', 'video/mp4', 'video/webm'],
                });
            }

            // ✅ Galereya
            const galleryInput = document.querySelector('.filepond-gallery');
            if (galleryInput) {
                FilePond.create(galleryInput, {
                    allowMultiple: true,
                    maxFiles: 10,
                    allowReorder: true,
                    instantUpload: false,
                    storeAsFile: true,
                    allowRemove: true,
                    acceptedFileTypes: ['image/*', 'video/mp4', 'video/webm'],
                });
            }
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allPanels = document.querySelectorAll('.table-panel');
            let currentlyOpen = document.querySelector('#collapse-receipt');
            if (currentlyOpen) {
                const defaultInstance = bootstrap.Collapse.getOrCreateInstance(currentlyOpen);
                defaultInstance.show();

                // Default aktiv tugma topiladi va unga active qo‘yiladi
                document.querySelectorAll('.collapse-toggler').forEach(btn => {
                    const targetId = btn.getAttribute('data-target');
                    if (targetId === '#collapse-receipt') {
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
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            const url =
                "{{ route('admin.promotion_shops.promotion_data', $promotion['id'], false) }}"; // serverdan malumot olish

            if ($.fn.DataTable.isDataTable('#promotion-shops-table')) {
                $('#promotion-shops-table').DataTable().destroy();
            }

            $('#promotion-shops-table').DataTable({
                processing: true,
                serverSide: false, // chunki biz to'liq malumotni olishimiz mumkin
                ajax: {
                    url: url,
                    type: "GET",
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'adress',
                        name: 'adress'
                    },
                    {
                        data: 'products_count',
                        name: 'products_count',
                        searchable: false
                    },
                    {
                        data: 'promotion_name',
                        name: 'promotion_name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    },
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
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: 'promotion_shops_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_shops_list',
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
        $(document).ready(function() {
            const url = "{{ route('admin.seles_receipts.won_seles_receipts', $promotion['id'], false) }}";

            if ($.fn.DataTable.isDataTable('#promotion-receipts-table')) {
                $('#promotion-receipts-table').DataTable().destroy();
            }

            $('#promotion-receipts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    type: "GET",
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'chek_id',
                        name: 'chek_id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nkm_number',
                        name: 'nkm_number'
                    },
                    {
                        data: 'sn',
                        name: 'sn'
                    },

                    {
                        data: 'payment_type',
                        name: 'payment_type'
                    },
                    {
                        data: 'qqs_summa',
                        name: 'qqs_summa',
                        searchable: false
                    },
                    {
                        data: 'summa',
                        name: 'summa',
                        searchable: false
                    },
                    {
                        data: 'lat',
                        name: 'lat',
                        searchable: false
                    },
                    {
                        data: 'long',
                        name: 'long',
                        searchable: false
                    },
                           { data: 'user_info', name: 'user_info', orderable: false, searchable: true }, // yangi ustun


                    {
                        data: 'manual_count',
                        name: 'manual_count',
                        searchable: false
                    },
                    {
                        data: 'prize_count',
                        name: 'prize_count',
                        searchable: false
                    },
                       {
                        data: 'check_date',
                        name: 'check_date',
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                buttons: [{
                        extend: 'copy',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: 'promotion_receipts_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_receipts_list',
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
        $(document).ready(function() {
            const promotionId = "{{ $promotion['id'] ?? ($promotion->id ?? 'unknown') }}";
            const url = "{{ route('admin.promocode.promocodedata', $promotion['id'], false) }}";
            if ($.fn.DataTable.isDataTable('#promocode-table')) {
                $('#promocode-table').DataTable().destroy();
            }

            $('#promocode-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: url,
                    type: "GET",
                },
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
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Promoaksiyalarni taxrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion.update', $promotion['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        {{-- 🔤 Translatable Inputs --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Nomi ({{ $label }})</label>
                                    <input type="text" class="form-control" name="name[{{ $lang }}]"
                                        value="{{ old('name.' . $lang, $promotion['name'][$lang] ?? '') }}" required>
                                    <small class="text-muted">Aksiya nomini {{ $label }} tilida kiriting (masalan,
                                        "Bahor aksiyasi").</small>
                                </div>
                            @endforeach

                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" class="form-control" name="title[{{ $lang }}]"
                                        value="{{ old('title.' . $lang, $promotion['title'][$lang] ?? '') }}" required>
                                    <small class="text-muted">Sarlavha foydalanuvchilarga ko‘rinadigan qisqa tanishtiruv
                                        bo‘lib xizmat qiladi.</small>
                                </div>
                            @endforeach

                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea class="form-control ckeditor" name="description[{{ $lang }}]" rows="6" required>{{ old('description.' . $lang, $promotion['description'][$lang] ?? '') }}</textarea>
                                    <small class="text-muted">Aksiya haqida batafsil ma’lumot yozing: qanday ishtirok
                                        etiladi, yutuqlar va qoidalar.</small>
                                </div>
                            @endforeach
                        </div>

                        {{-- 📦 Selection Inputs --}}
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Kampaniya</label>
                                <select name="company_id" class="form-select" required>
                                    <option value="">Tanlang...</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company['id'] }}"
                                            {{ old('company_id', $promotion['company_id']) == $company['id'] ? 'selected' : '' }}>
                                            {{ $company['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Ushbu aksiya qaysi kompaniyaga tegishli ekanligini
                                    belgilang.</small>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Boshlanish sanasi</label>
                                <input type="datetime-local" name="start_date" class="form-control"
                                    value="{{ old('start_date', \Carbon\Carbon::parse($promotion['start_date'])->format('Y-m-d\TH:i')) }}"
                                    required>
                                <small class="text-muted">Aksiya rasmiy boshlanadigan sana va vaqtni kiriting.</small>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Tugash sanasi</label>
                                <input type="datetime-local" name="end_date" class="form-control"
                                    value="{{ old('end_date', \Carbon\Carbon::parse($promotion['end_date'])->format('Y-m-d\TH:i')) }}"
                                    required>
                                <small class="text-muted">Aksiya tugaydigan sana va vaqtni belgilang.</small>
                            </div>
                        </div>

                        {{-- 🔁 Multi-select fields --}}
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Aksiya o'tqaziladigan platformalarni tanlang</label>
                                <select name="platforms_new[]" class="form-control multiselect" multiple>
                                    <option value="" disabled selected>-- Platformani tanlang --</option>
                                    @foreach ($platforms as $name => $id)
                                        <option value="{{ $id }}">{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Aksiya qaysi platformalarda (web, telegram, sms) o'tkazilishini
                                    tanlang.</small>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">ishtirok etish turlari uslublarini tanlang</label>
                                <select name="participants_type_new[]" class="form-control multiselect" multiple>
                                    <option value="" disabled selected>-- Uslubni tanlang --</option>

                                    @foreach ($partisipants_type as $name => $id)
                                        <option value="{{ $id }}">{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Foydalanuvchi aksiyada qanday ishtirok etishini belgilang (QR,
                                    kod, chek va h.k.).</small>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label fw-semibold">Yutuqni berish strategiyasi</label>
                                <select name="winning_strategy"
                                    class="form-control select2-single @error('winning_strategy') is-invalid @enderror"
                                    required>
                                    <option value="" disabled
                                        {{ old('winning_strategy', $promotion['winning_strategy'] ?? '') === null ? 'selected' : '' }}>
                                        -- Strategiyani tanlang --
                                    </option>
                                    <option value="immediate"
                                        {{ old('winning_strategy', $promotion['winning_strategy'] ?? '') === 'immediate' ? 'selected' : '' }}>
                                        🎁 Har bir promokod yutuq olib keladi (tez yutuq)
                                    </option>
                                    <option value="delayed"
                                        {{ old('winning_strategy', $promotion['winning_strategy'] ?? '') === 'delayed' ? 'selected' : '' }}>
                                        🕒 Promokodlar ro'yxatga olinadi, oxirida sovrin beriladi
                                    </option>
                                    <option value="hybrid"
                                        {{ old('winning_strategy', $promotion['winning_strategy'] ?? '') === 'hybrid' ? 'selected' : '' }}>
                                        ⚖️ Aralash — ba'zilari yutadi, ba'zilari keyinchalik o'ynaydi
                                    </option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Aksiya davomida promokodlar qanday tarzda yutuqqa aylanishini belgilang.
                                </small>
                            </div>
                        </div>

                        {{-- ✅ Switches --}}
                        <div class="row mb-3">
                            <div class="col-lg-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1"
                                    id="statusSwitch" {{ old('status', $promotion['status']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusSwitch">Faollik</label>
                                <small class="text-muted d-block">Aksiya faollashtirilgan bo‘lsa, foydalanuvchilar uni
                                    ko‘rishlari mumkin.</small>
                            </div>
                            <div class="col-lg-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_public" value="1"
                                    id="publicSwitch" {{ old('is_public', $promotion['is_public']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="publicSwitch">Ommaviy</label>
                                <small class="text-muted d-block">Aksiyani ommaga ko‘rsatish uchun belgilang.</small>
                            </div>
                            {{-- <div class="col-lg-4 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_prize" value="1"
                                        id="prizeSwitch" {{ old('is_prize', $promotion['is_prize']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prizeSwitch">Yutuqli</label>
                                    <small class="text-muted d-block">Agar aksiya yutuqli bo‘lsa, ushbu tugmani yoqing.</small>
                                </div> --}}
                        </div>

                        {{-- 🔗 Media file uploads --}}
                        <input type="hidden" name="created_by_user_id" value="{{ $promotion['created_by_user_id'] }}">

                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Oferta fayl</label>
                                <input type="file" name="offer_file" class="filepond-offer" />
                                <small class="text-muted">Aksiya shartlarini PDF/DOC formatida yuklang. Bu
                                    foydalanuvchilarga ko‘rsatiladi.</small>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Banner</label>
                                <input type="file" name="media_preview" class="filepond-banner" />
                                <small class="text-muted">Aksiyaga mos banner rasmi (vizual reklama) yuklang.</small>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Galereya</label>
                                <input type="file" name="media_gallery[]" class="filepond-gallery" multiple />
                                <small class="text-muted">Aksiya uchun bir nechta qo‘shimcha rasmlar yuklang
                                    (ixtiyoriy).</small>
                            </div>
                        </div>

                        {{-- 🔘 Submit --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.promotion.index') }}" class="btn btn-outline-secondary">Bekor
                                qilish</a>
                            <button type="submit" class="btn btn-primary">Yangilash</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            @php
                $hasPromoType = collect($promotion['participants_type'] ?? [])
                    ->pluck('name')
                    ->intersect(['QR code', 'Text code'])
                    ->isNotEmpty();
                $hasReceiptType = collect($promotion['participants_type'] ?? [])
                    ->pluck('name')
                    ->intersect(['Receipt scan'])
                    ->isNotEmpty();
                $hasPrize = in_array($promotion['winning_strategy'], ['immediate', 'hybrid']);

            @endphp
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Promoaksiya ma'lumotlari</h5>
                <div class="btn-group">

                    @if (!empty($promotion['platforms']))
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-platform">
                            <i class="ph ph-share-network me-1"></i> Faol platformalar
                        </button>
                    @endif

                    @if (!empty($promotion['participants_type']))
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-type">
                            <i class="ph ph-users-three me-1"></i> Faol Ishtirok turlari
                        </button>
                    @endif
                    @if ($hasPromoType)
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-promocode">
                            <i class="ph ph-ticket me-1"></i> Promocodelar
                        </button>
                    @endif

                    @if ($hasReceiptType)
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-receipt">
                            <i class="ph ph-receipt me-1"></i> Receipt scan
                        </button>
                    @endif

                    @if ($hasPrize)
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-prize">
                            <i class="ph ph-gift me-1"></i> Sovg'alar
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="collapse table-panel" id="collapse-platform">
                    <div class="p-3">
                        <div class="page-header-content d-flex justify-content-between align-items-center mb-3">
                            <h4 class="page-title mb-0">Tanlangan platformalar</h4>
                        </div>
                        <div class="row g-3">

                            @foreach ($promotion['platforms'] ?? [] as $platform)
                                <div class="col-lg-6">
                                    <form
                                        action="{{ route('admin.promotion.platform.update', ['promotion' => $promotion['id'], 'platform' => $platform['id']]) }}"
                                        method="POST"
                                        class="card shadow-sm  strategy-card border border-success border rounded shadow-sm p-4 h-100 bg-light">
                                        @csrf
                                        <input type="hidden" name="platform_id" value="{{ $platform['id'] }}">
                                        <input type="hidden" name="promotion_id" value="{{ $promotion['id'] }}">

                                        <div class="mb-3">
                                            <h5 class="fw-semibold mb-1">{{ ucfirst($platform['name']) }}</h5>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_enabled"
                                                    id="platform_enabled_{{ $platform['id'] }}"
                                                    {{ old("platforms_enabled.{$platform['id']}", $platform['is_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="platform_enabled_{{ $platform['id'] }}">
                                                    Faollashtirilgan
                                                </label>
                                            </div>
                                        </div>

                                        @if (strtolower($platform['name']) === 'sms')
                                            <div class="mb-3">
                                                <label class="form-label">📱 SMS telefon raqami</label>
                                                <input type="text" class="form-control" name="phone"
                                                    value="{{ old("platforms_phone.{$platform['id']}", $platform['phone']) }}"
                                                    placeholder="+99890xxxxxxx">
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label">📝 Qo‘shimcha qoidalar (JSON yoki matn)</label>
                                            <textarea name="additional_rules" rows="3" class="form-control"
                                                placeholder='{"limit": 3, "allowed_time": "09:00-18:00"}'>{{ old("platforms_rules.{$platform['id']}", $platform['additional_rules']) }}</textarea>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                Saqlash
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="collapse table-panel" id="collapse-type">
                    <div class=" p-3 shadow-sm">
                        <div class="page-header-content d-flex justify-content-between align-items-center mb-3">
                            <h4 class="page-title mb-0">👥 Tanlangan ishtirok turlari</h4>
                        </div>
                        <div class="row g-3">
                            @foreach ($promotion['participants_type'] ?? [] as $type)
                                <div class="col-lg-6">
                                    <form
                                        action="{{ route('admin.promotion.participant-type.update', ['promotion' => $promotion['id'], 'participant_type' => $type['id']]) }}"
                                        method="POST"
                                        class="card shadow-sm  strategy-card border border-success border rounded shadow-sm p-4 h-100 bg-light">
                                        @csrf

                                        <input type="hidden" name="participant_type_id" value="{{ $type['id'] }}">
                                        <input type="hidden" name="promotion_id" value="{{ $promotion['id'] }}">

                                        <div class="mb-3">
                                            <h5 class="fw-semibold mb-1">{{ ucfirst($type['name']) }}</h5>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_enabled"
                                                    id="participant_enabled_{{ $type['id'] }}"
                                                    {{ old("participants_enabled.{$type['id']}", $type['is_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="participant_enabled_{{ $type['id'] }}">
                                                    Faollashtirilgan
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">📝 Qo‘shimcha qoidalar (JSON yoki matn)</label>
                                            <textarea name="additional_rules" rows="3" class="form-control"
                                                placeholder='{"limit": 5, "allowed_time": "08:00-22:00"}'>{{ old("participants_rules.{$type['id']}", $type['additional_rules']) }}</textarea>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                Saqlash
                                            </button>

                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if ($hasPromoType)
                    <div class="collapse table-panel" id="collapse-promocode">
                        <div class="border rounded p-3">
                            <div class="page-header-content d-flex justify-content-between align-items-center">
                                <h4 class="page-title mb-0">PromoCodelar jadvali</h4>
                                <div>
                                    <a href="{{ route('admin.promocode.create', ['promotion_id' => $promotion['id']]) }}"
                                        class="btn btn-outline-success ms-3">
                                        <i class="ph-plus-circle me-1"></i> Generate va Import
                                    </a>
                                    {{-- <button type="button" class="btn btn-outline-success ms-3" data-bs-toggle="modal"
                                            data-bs-target="#socialMediaModal">
                                            <i class="ph-plus-circle me-1"></i> Sozlamalar
                                        </button> --}}
                                </div>
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
                    </div>
                @endif
                @if ($hasReceiptType)
                    <div class="collapse table-panel" id="collapse-receipt">
                        <div class="border rounded p-3 mb-4">
                            <div class="page-header-content d-flex justify-content-between align-items-center">
                                <h4 class="page-title mb-0">Promotion o'tqaziladigan filiallar</h4>
                                <div>
                                    <a href="{{ route('admin.promotion_shops.create', ['promotion_id' => $promotion['id']]) }}"
                                        class="btn btn-outline-success ms-3">
                                        <i class="ph-plus-circle me-1"></i> Filial qo'shish
                                    </a>
                                    {{-- <button type="button" class="btn btn-outline-success ms-3" data-bs-toggle="modal"
                                            data-bs-target="#socialMediaModal">
                                            <i class="ph-plus-circle me-1"></i> Sozlamalar
                                        </button> --}}
                                </div>
                            </div>
                            <table id="promotion-shops-table" class="table datatable-button-init-basic">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Do‘kon nomi</th>
                                        <th>Manzil</th>
                                        <th>Mahsulotlar soni</th>
                                        <th>Aksiya nomi</th>
                                        <th>Yaratilgan vaqti</th>
                                        <th>Amallar</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>
                        <div class="border rounded p-3 mb-4">
                            <div class="page-header-content ">
                                <h4 class="page-title mb-0">Yutuqli harid cheklari</h4>
                                <small>Bu yerda user tomonidan scaner qilingan yutuqli harid cheklari ko'rsatiladi. bu
                                    cheklar ichida kamida bitta manual yoki extimollik nazaryasi bilan beriladigan yutuq
                                    mavjud.</small>
                            </div>
                            <hr>
                            <table id="promotion-receipts-table" class="table datatable-button-init-basic">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Chek ID</th>
                                        <th>Do‘kon nomi</th>
                                        <th>NKM raqami</th>
                                        <th>SN</th>
                                        <th>To‘lov turi</th>
                                        <th>QQS summa</th>
                                        <th>Umumiy summa</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Foydalanuvchi</th>
                                        <th>Manual count</th>
                                        <th>Prize count</th>
                                        <th>Chek sanasi</th>
                                        <th>Yaratilgan</th>
                                        <th>Amallar</th>
                                    </tr>
                                </thead>

                            </table>

                        </div>
                    </div>
                @endif
                @if ($hasPrize && !empty($prizeCategories))
                    <div class="collapse table-panel" id="collapse-prize">
                        <div class="border rounded p-3">
                            <div class="page-header-content d-flex justify-content-between align-items-center mb-3">
                                <h4 class="page-title mb-0">🎁 Yutuq strategiyalari</h4>
                            </div>

                            <div class="row g-4">
                                @foreach ($prizeCategories as $category)
                                    <div class="col-xl-6 col-lg-6">
                                        <div
                                            class="card strategy-card shadow-sm border border-success rounded p-4 h-100 bg-light">
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <h5 class="fw-semibold mb-1">
                                                        {{ $category['display_name'] }}</h5>
                                                    <p class="text-muted small mb-3">
                                                        {!! $category['description'] !!} </p>
                                                    </p>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                                    <div>
                                                        <span
                                                            class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-warning">
                                                            {{ $category['name'] }}
                                                        </span>
                                                        <span
                                                            class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-info">
                                                            {{ $category['prize_count'] }} ta sovg‘a
                                                        </span>
                                                    </div>
                                                    <a href="{{ route('admin.prize-category.show', ['promotion' => $promotion['id'], 'type' => $category['name']]) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="ph ph-eye me-1"></i> Ko‘rish
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
