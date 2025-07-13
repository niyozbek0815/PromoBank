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
                    allowRemove: true,
                    acceptedFileTypes: ['image/*', 'video/mp4', 'video/webm'],
                });
            }
        });
    </script>
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
                            {{-- Translatable fields --}}
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Nomi ({{ $label }})</label>
                                    <input type="text" name="name[{{ $lang }}]" class="form-control" required>
                                </div>
                            @endforeach
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]" class="form-control" required>
                                </div>
                            @endforeach
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea name="description[{{ $lang }}]" class="form-control ckeditor" rows="6"></textarea>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Kampaniya</label>
                                <select name="company_id" class="form-select" required>
                                    <option value="">Tanlang...</option>

                                    @foreach ($companies as $company)
                                        <option value="{{ $company['id'] }}">{{ $company['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Boshlanish sanasi</label>
                                <input type="datetime-local" name="start_date" class="form-control">
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Tugash sanasi</label>
                                <input type="datetime-local" name="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            {{-- JSON fields --}}

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Aksiya qanday usullarda amalga oshiriladi</label>
                                <select name="participants_type[]" class="form-control multiselect" multiple="multiple"
                                    required" data-non-selected-text="Please choose">

                                    @foreach ($partisipants_type as $name => $id)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Aksiya amal qiladigan platformalar</label>
                                <select name="platforms[]" class="form-control multiselect" multiple="multiple" required"
                                    data-non-selected-text="Please choose">

                                    @foreach ($platforms as $name => $id)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 mb-3 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                        id="statusSwitch" checked>
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
                            <div class="col-lg-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_prize" value="1"
                                        id="prizeSwitch">
                                    <label class="form-check-label" for="prizeSwitch">
                                        <strong>Yutuqli aksiya:</strong><br>
                                        <small class="text-muted">
                                            Belgilansa, foydalanuvchi ishtirok etganda sovrin yutib olish imkoniyati bo‘ladi
                                        </small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Banner (yagona fayl) --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">Ommaviy oferta hujjatini yuklang</label>
                                <input type="file" name="offer_file" class="filepond-offer" required
                                    data-max-file-size="5MB" />

                                <div class="form-text text-muted">
                                    <strong>Ruxsat etilgan formatlar:</strong> .pdf, .doc, .docx <br>
                                    <strong>Maksimal hajm:</strong> 5 MB
                                </div>
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">Promoaksiya banneri </label>
                                <input type="file" name="media_preview" class="filepond-banner" required
                                    data-max-file-size="5MB" accept="image/*,video/mp4,video/webm" />

                                <div class="form-text text-muted">
                                    Bu rasm yoki video promoaksiyaning tashqi ko‘rinishidir.<br>
                                    <strong>Ruxsat etilgan formatlar:</strong> jpg, png, gif, mp4, webm. <br>
                                    <strong>Maksimal hajm:</strong> 5 MB.
                                </div>
                            </div>
                            @php
                                $user = Session::get('user');
                            @endphp
                            <input type="text" name="created_by_user_id" value="{{ $user['id'] }}" hidden
                                class="form-control" required maxlength="255">
                            {{-- Galereya (bir nechta fayl) --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">Promoaksiya Media Galereyasi (Bir nechta media)</label>
                                <input type="file" name="media_gallery[]" class="filepond-gallery" multiple required
                                    data-max-file-size="20MB" data-max-files="10"
                                    accept="image/*,video/mp4,video/webm" />

                                <div class="form-text text-muted mt-1">
                                    Bu media fayllar promoaksiyani batafsil tushuntiradi (ko‘rsatmalar, shartlar,
                                    misollar).<br>
                                    <strong>Ruxsat etilgan formatlar:</strong> jpg, png, gif, mp4, webm.<br>
                                    <strong>Maksimal fayl soni:</strong> 10 ta. Har bir fayl hajmi: 20 MB gacha.
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
