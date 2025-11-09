@extends('admin.layouts.app')
@section('title', "Promoaksiya qo'shish")
@push('scripts')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

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
    {{-- <script src="{{ asset('adminpanel/assets/js/filepond/filepond.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/filepond/filepond-plugin-file-validate-type.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/filepond/filepond-plugin-file-validate-size.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/filepond/filepond-plugin-image-preview.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/filepond/filepond-plugin-image-exif-orientation.js') }}">
    </script>
    <script src="{{ asset('adminpanel/assets/js/filepond/filepond-plugin-file-poster.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/ckeditor.js') }}"></script> --}}

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
                    allowImagePreview: true,
                    imagePreviewHeight: 120,
                    allowVideoPreview: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    allowReorder: false,
                    instantUpload: false,
                    acceptedFileTypes: [
                        'application/pdf',
                        'application/msword', // .doc
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.oasis.opendocument.text', // .odt
                        'application/rtf', // .rtf
                        'text/plain' // .txt
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

$(document).ready(function() {
    const $participantsType = $('#participantsType');
    const $platforms = $('select[name="platforms[]"]');
    const $strategyWrapper = $('#strategyWrapper');
    const $winningStrategy = $strategyWrapper.find('select');

    const $timeInputWrapper = $('#timeInputWrapper');
    const $pointsInputWrapper = $('#pointsInputWrapper');
    const $shortNumberSeconds = $('#shortNumberSeconds');
    const $shortNumberPoints = $('#shortNumberPoints');

    $participantsType.on('change', function() {
        const selectedOptions = $participantsType.find('option:selected').map(function() {
            return $(this).text().toLowerCase();
        }).get();

        const hasShortNumber = selectedOptions.some(opt => opt.includes('short number'));

        if (hasShortNumber) {
            // Faqat Short Number tanlanganlarni faol qilamiz
            $participantsType.find('option').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes('short number')) {
                    $(this).prop('selected', true).prop('disabled', false);
                } else {
                    $(this).prop('selected', false).prop('disabled', true);
                }
            });
            $participantsType.multiselect('rebuild');

            // Winning strategy faqat delayed bo‘lsin
            $winningStrategy.val('delayed');
            $winningStrategy.find('option').each(function() {
                if ($(this).val() !== 'delayed') {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
            $winningStrategy.prop('required', true);
            $winningStrategy.select2();

            // ⏱ Short number sekund va ball maydonlari ko‘rsatiladi
            $timeInputWrapper.removeClass('d-none');
            $pointsInputWrapper.removeClass('d-none');
            $shortNumberSeconds.attr('required', 'required');
            $shortNumberPoints.attr('required', 'required');

            // Platformalarni faqat Telegram qilamiz
            $platforms.find('option').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes('telegram')) {
                    $(this).prop('selected', true).prop('disabled', false);
                } else {
                    $(this).prop('selected', false).prop('disabled', true);
                }
            });
            $platforms.multiselect('rebuild');
        } else {
            // Short number tanlanmagan — barcha variantlarni tiklash
            $participantsType.find('option').prop('disabled', false);
            $participantsType.multiselect('rebuild');

            $winningStrategy.find('option').prop('disabled', false);
            $winningStrategy.val('');
            $winningStrategy.prop('required', true);
            $winningStrategy.select2();

            // Short number maydonlarini yashiramiz
            $timeInputWrapper.addClass('d-none');
            $pointsInputWrapper.addClass('d-none');
            $shortNumberSeconds.removeAttr('required');
            $shortNumberPoints.removeAttr('required');

            // Platformalarni tiklash
            $platforms.find('option').prop('disabled', false);
            $platforms.multiselect('rebuild');
        }
    });

    // Sahifa yuklanganda tekshirish (edit form uchun)
    $participantsType.trigger('change');
});  </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Promoaksiya qo'shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            @php
                                $languages = [
                                    'uz' => 'O‘zbekcha',
                                    'ru' => 'Русский',
                                    'kr' => 'Krillcha',
                                    'en' => 'English',
                                ];
                                $fields = [
                                    'name' => [
                                        'label' => 'Nomi',
                                        'type' => 'text',
                                        'extra' => 'Aksiya nomini :lang tilida kiriting (masalan, "Bahor aksiyasi").',
                                    ],
                                    'title' => [
                                        'label' => 'Sarlavha',
                                        'type' => 'text',
                                        'extra' =>
                                            'Sarlavha foydalanuvchilarga ko‘rinadigan qisqa tanishtiruv bo‘lib xizmat qiladi.',
                                    ],
                                    'description' => [
                                        'label' => 'Tavsif',
                                        'type' => 'textarea',
                                        'extra' =>
                                            'Aksiya haqida batafsil ma’lumot yozing: qanday ishtirok etiladi, yutuqlar va qoidalar.',
                                    ],
                                ];
                            @endphp

                            @foreach ($fields as $field => $meta)
                                @foreach ($languages as $lang => $label)
                                    <div class="col-lg-3 mb-3">
                                        <label class="form-label fw-bold">
                                            {{ $meta['label'] }} ({{ $label }}) <span class="text-danger">*</span>
                                        </label>

                                        @if ($meta['type'] === 'text')
                                            <input type="text" name="{{ $field }}[{{ $lang }}]"
                                                class="form-control" value="{{ old("$field.$lang") }}" required>
                                        @elseif ($meta['type'] === 'textarea')
                                            <textarea name="{{ $field }}[{{ $lang }}]" class="form-control ckeditor" rows="6" required>{{ old("$field.$lang") }}</textarea>
                                        @endif

                                        <small
                                            class="text-muted">{{ str_replace(':lang', $label, $meta['extra']) }}</small>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                        <div class="row">


                            <div class="col-lg-4 mb-3">
                                <label class="form-label">
                                    Kampaniya <span class="text-danger">*</span>
                                </label>
                                <select name="company_id" class="form-select"
                                    {{ isset($selectedCompanyId) ? 'readonly disabled' : '' }} required>
                                    <option value="">Tanlang...</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company['id'] }}"
                                            {{ isset($selectedCompanyId) && $selectedCompanyId == $company['id'] ? 'selected' : '' }}>
                                            {{ $company['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Ushbu aksiya qaysi kompaniyaga tegishli ekanligini belgilang.
                                </small>
                                @if (isset($selectedCompanyId))
                                    <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                                @endif
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">
                                    Boshlanish sanasi <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="start_date" class="form-control" required>
                                <small class="text-muted">
                                    Aksiya rasmiy boshlanadigan sana va vaqtni kiriting.
                                </small>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">
                                    Tugash sanasi <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="end_date" class="form-control" required>
                                <small class="text-muted">
                                    Aksiya tugaydigan sana va vaqtni belgilang.
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">
                                    Ishtirok etish turlari uslublarini tanlang <span class="text-danger">*</span>
                                </label>
                                <select name="participants_type[]" id="participantsType" class="form-control multiselect"
                                    multiple required data-non-selected-text="Please choose">
                                    @foreach ($partisipants_type as $name => $id)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Foydalanuvchi aksiyada qanday ishtirok etishini belgilang (QR,
                                    kod, qisqa raqam chek va h.k.).</small>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">
                                    Aksiya o'tkaziladigan platformalarni tanlang <span class="text-danger">*</span>
                                </label>
                                <select name="platforms[]" class="form-control multiselect" multiple required
                                    data-non-selected-text="Please choose">
                                    @foreach ($platforms as $name => $id)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Aksiya qaysi platformalarda (web, telegram, sms) o'tkazilishini
                                    tanlang.</small>
                            </div>

                            <div class="col-lg-4 mb-3" id="strategyWrapper">
                                <label class="form-label">
                                    Yutuqni berish strategiyasi <span class="text-danger">*</span>
                                </label>
                                <select name="winning_strategy"
                                    class="form-control @error('winning_strategy') is-invalid @enderror" required>
                                    <option value="" disabled
                                        {{ old('winning_strategy') === null ? 'selected' : '' }}>
                                        -- Strategiyani tanlang --
                                    </option>
                                    <option value="immediate"
                                        {{ old('winning_strategy') === 'immediate' ? 'selected' : '' }}>
                                        🎁 Har bir promokod yutuq olib keladi (tez yutuq)
                                    </option>
                                    <option value="delayed" {{ old('winning_strategy') === 'delayed' ? 'selected' : '' }}>
                                        🕒 Promokodlar ro'yxatga olinadi, oxirida sovrin beriladi
                                    </option>
                                    <option value="hybrid" {{ old('winning_strategy') === 'hybrid' ? 'selected' : '' }}>
                                        ⚖️ Aralash — ba'zilari yutadi, ba'zilari keyinchalik o'ynaydi
                                    </option>
                                </select>
                                @error('winning_strategy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">
                                    Aksiya davomida promokodlar qanday tarzda yutuqqa aylanishini belgilang.
                                </small>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-3 d-none" id="timeInputWrapper">
                                <label class="form-label fw-bold">
                                    Qisqa raqamni qabul qilish oralig‘i (soniya) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="short_number_seconds" id="shortNumberSeconds"
                                    class="form-control" min="1" step="1" placeholder="Masalan: 30, 45, 90 …"
                                    required>
                                <small class="text-muted d-block mt-1">
                                    Promokod shu sekund oralig‘ida faqat qabul qilinadi. 0 dan katta istalgan son
                                    kiritilishi mumkin.
                                </small>
                            </div>
                            <div class="col-lg-4 mb-3 d-none" id="pointsInputWrapper">
    <label class="form-label fw-bold">
        Qisqa raqamga beriladigan ball <span class="text-danger">*</span>
    </label>
    <input type="number" name="short_number_points" id="shortNumberPoints"
        class="form-control" min="1" step="1"
        value="{{ old('short_number_points', $promotion['short_number_points'] ?? 1) }}"
        placeholder="Masalan: 1, 5, 10 …" required>
    <small class="text-muted d-block mt-1">
        Foydalanuvchi ushbu qisqa raqamni yuborganda unga shu miqdorda ball beriladi. 0 dan katta istalgan son kiriting.
    </small>
</div>
                            <div class="col-lg-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                        id="statusSwitch">
                                    <label class="form-check-label" for="statusSwitch">
                                        <strong>Promoaksiya faolligi:</strong><br>
                                        <small class="text-muted">Belgilansa, foydalanuvchilar uchun faol
                                            bo‘ladi</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_public" value="1"
                                        id="publicSwitch">
                                    <label class="form-check-label" for="publicSwitch">
                                        <strong>Ommaviylik:</strong><br>
                                        <small class="text-muted">Belgilansa, aksiyani barcha ko‘ra oladi</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {{-- Oferta hujjati --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label fw-bold">Ommaviy oferta hujjatini yuklang <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="offer_file" class="filepond-offer" required
                                    data-max-file-size="2MB" />

                                <div class="form-text text-muted">
                                    <strong>Ruxsat etilgan formatlar:</strong> .pdf, .doc, .docx, .odt, .rtf, .txt <br>
                                    <strong>Maksimal hajm:</strong> 2 MB
                                </div>
                            </div>

                            {{-- Banner --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label fw-bold">Promoaksiya banneri <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="media_preview" class="filepond-banner" required
                                    data-max-file-size="512KB" accept="image/*,video/mp4,video/webm" />

                                <div class="form-text text-muted">
                                    Bu rasm yoki video promoaksiyaning tashqi ko‘rinishidir.<br>
                                    <strong>Ruxsat etilgan formatlar:</strong> jpg, jpeg, png, gif, mp4, webm. <br>
                                    <strong>Maksimal hajm:</strong> 512 KB
                                </div>
                            </div>

                            {{-- User --}}
                            @php $user = Session::get('user'); @endphp
                            <input type="hidden" name="created_by_user_id" value="{{ $user['id'] }}" required>

                            {{-- Galereya --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label fw-bold">Promoaksiya Media Galereyasi <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="media_gallery[]" class="filepond-gallery" multiple required
                                    data-max-file-size="235MB" data-max-files="10"
                                    accept="image/*,video/mp4,video/webm" />

                                <div class="form-text text-muted mt-1">
                                    Promoaksiyani tushuntiruvchi qo‘shimcha media.<br>
                                    <strong>Ruxsat etilgan formatlar:</strong> jpg, jpeg, png, gif, mp4, webm. <br>
                                    <strong>Maksimal fayl soni:</strong> 10 ta. <br>
                                    <strong>Har bir fayl hajmi:</strong> 235 MB gacha.
                                </div>
                            </div>
                        </div>


                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.promotion.index') }}" class="btn btn-outline-secondary">
                                <i class="ph-arrow-circle-left me-1"></i> Bekor qilish
                            </a>
                            <button type="reset" class="btn btn-outline-warning">
                                <i class="ph-arrow-clockwise me-1"></i> Tozalash
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-paper-plane-tilt me-1"></i> Saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
