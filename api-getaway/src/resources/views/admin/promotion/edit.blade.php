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
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Promoaksiya qo'shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion.update', $promotion['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        {{-- @php
                            dd($promotion);
                        @endphp --}}

                        {{-- 🔤 Translatable Inputs --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Nomi ({{ $label }})</label>
                                    <input type="text" class="form-control" name="name[{{ $lang }}]"
                                        value="{{ old('name.' . $lang, $promotion['name'][$lang] ?? '') }}" required>
                                </div>
                            @endforeach
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" class="form-control" name="title[{{ $lang }}]"
                                        value="{{ old('title.' . $lang, $promotion['title'][$lang] ?? '') }}" required>
                                </div>
                            @endforeach
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea class="form-control ckeditor" name="description[{{ $lang }}]" rows="6" required>{{ old('description.' . $lang, $promotion['description'][$lang] ?? '') }}</textarea>
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
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Boshlanish sanasi</label>
                                <input type="datetime-local" name="start_date" class="form-control"
                                    value="{{ old('start_date', \Carbon\Carbon::parse($promotion['start_date'])->format('Y-m-d\TH:i')) }}"
                                    required>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Tugash sanasi</label>
                                <input type="datetime-local" name="end_date" class="form-control"
                                    value="{{ old('end_date', \Carbon\Carbon::parse($promotion['end_date'])->format('Y-m-d\TH:i')) }}"
                                    required>
                            </div>
                        </div>

                        {{-- 🔁 Multi-select fields --}}
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Platformalar</label>
                                <select name="platforms_new[]" class="form-control multiselect" multiple>
                                    @foreach ($platforms as $name => $id)
                                        <option value="{{ $id }}">{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Ishtirok turlari</label>
                                <select name="participants_type_new[]" class="form-control multiselect" multiple>
                                    @foreach ($partisipants_type as $name => $id)
                                        <option value="{{ $id }}">{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- <div class="row mb-4">
                            <h5 class="text-success">👥 Tanlangan ishtirok turlari</h5>
                            @foreach ($promotion['participants_type'] ?? [] as $type)
                                <div class="col-lg-6 border rounded p-3 mb-2">
                                    <input type="hidden" name="participants_type[]" value="{{ $type['id'] }}">
                                    <strong>{{ ucfirst($type['name']) }}</strong>

                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                            name="participants_enabled[{{ $type['id'] }}]"
                                            id="participants_enabled_{{ $type['id'] }}"
                                            {{ old("participants_enabled.{$type['id']}", $type['is_enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="participants_enabled_{{ $type['id'] }}">
                                            Faollashtirilgan
                                        </label>
                                    </div>

                                    <div class="mt-2">
                                        <label>Qo‘shimcha qoidalar (JSON yoki matn)</label>
                                        <textarea name="participants_rules[{{ $type['id'] }}]" rows="2" class="form-control">{{ old("participants_rules.{$type['id']}", $type['additional_rules']) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <h5 class="text-primary">🔌 Tanlangan platformalar</h5>
                            @foreach ($promotion['platforms'] ?? [] as $platform)
                                <div class="col-lg-6 border rounded p-3 mb-2">
                                    <input type="hidden" name="platforms[]" value="{{ $platform['id'] }}">
                                    <strong>{{ ucfirst($platform['name']) }}</strong>

                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                            name="platforms_enabled[{{ $platform['id'] }}]"
                                            id="platform_enabled_{{ $platform['id'] }}"
                                            {{ old("platforms_enabled.{$platform['id']}", $platform['is_enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform_enabled_{{ $platform['id'] }}">
                                            Faollashtirilgan
                                        </label>
                                    </div>

                                    <div class="mt-2">
                                        <label>SMS telefon raqami (agar kerak bo‘lsa)</label>
                                        <input type="text" class="form-control"
                                            name="platforms_phone[{{ $platform['id'] }}]"
                                            value="{{ old("platforms_phone.{$platform['id']}", $platform['phone']) }}">
                                    </div>

                                    <div class="mt-2">
                                        <label>Qo‘shimcha qoidalar (JSON yoki matn)</label>
                                        <textarea name="platforms_rules[{{ $platform['id'] }}]" rows="2" class="form-control">{{ old("platforms_rules.{$platform['id']}", $platform['additional_rules']) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div> --}}

                        {{-- ✅ Switches --}}
                        <div class="row mb-3">
                            <div class="col-lg-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1"
                                    id="statusSwitch" {{ old('status', $promotion['status']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusSwitch">Faollik</label>
                            </div>
                            <div class="col-lg-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_public" value="1"
                                    id="publicSwitch" {{ old('is_public', $promotion['is_public']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="publicSwitch">Ommaviy</label>
                            </div>
                            <div class="col-lg-4 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_prize" value="1"
                                    id="prizeSwitch" {{ old('is_prize', $promotion['is_prize']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="prizeSwitch">Yutuqli</label>
                            </div>
                        </div>

                        {{-- 🔗 Media file uploads --}}
                        <input type="hidden" name="created_by_user_id" value="{{ $promotion['created_by_user_id'] }}">

                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Oferta fayl</label>
                                <input type="file" name="offer_file" class="filepond-offer" />
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Banner</label>
                                <input type="file" name="media_preview" class="filepond-banner" />
                            </div>
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Galereya</label>
                                <input type="file" name="media_gallery[]" class="filepond-gallery" multiple />
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
    </div>
@endsection
