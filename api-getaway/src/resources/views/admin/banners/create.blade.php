@extends('admin.layouts.app')
@section('title', "Promoaksiya qo'shish")
@push('scripts')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    {{-- JS --}}
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_multiselect.js') }}"></script>

    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.js"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== CKEditor Init =====
            document.querySelectorAll('.ckeditor').forEach(el => {
                ClassicEditor.create(el).then(editor => {
                    editor.model.document.on('change:data', () => {
                        el.value = editor.getData();
                    });
                }).catch(console.error);
            });

            // ===== FilePond Plugins =====
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview,
                FilePondPluginFilePoster
            );

            // ===== FilePond Default Config =====
            const defaultFilePondConfig = {
                storeAsFile: true,
                allowMultiple: false,
                allowReorder: false,
                instantUpload: false,
                maxFiles: 1,
                maxFileSize: '20MB',
                acceptedFileTypes: ['image/*', 'video/mp4', 'video/webm', 'image/gif'],
                labelIdle: '📂 <span class="filepond--label-action">Faylni tanlang</span> yoki bu yerga tashlang',
                credits: false
            };

            document.querySelectorAll('input[type="file"].filepond').forEach(input => {
                FilePond.create(input, defaultFilePondConfig);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bannerTypeSelect = document.getElementById('banner_type');
            const urlSelectWrapper = document.getElementById('url_select_wrapper');
            const urlInputWrapper = document.getElementById('url_input_wrapper');
            const urlSelect = document.getElementById('url_select');
            const urlInput = document.getElementById('url_input');

            const promotionUrls = @json($promotionUrls);
            const gameUrls = @json($gameUrls);

            function setSelectOptions(select, options, placeholder = "Tanlang...") {
                select.innerHTML = "";

                const defaultOption = document.createElement('option');
                defaultOption.value = "";
                defaultOption.textContent = placeholder;
                defaultOption.disabled = true;
                defaultOption.selected = true;
                select.appendChild(defaultOption);

                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = String(opt.value ?? opt.id ?? "");

                    let label = opt.label;
                    try {
                        const parsed = JSON.parse(opt.label);
                        label = parsed.uz ?? parsed.en ?? parsed.kr ?? opt.label;
                    } catch (e) {}
                    option.textContent = label;
                    select.appendChild(option);
                });
            }

            bannerTypeSelect.addEventListener('change', function () {
                const type = this.value;

                if (type === 'url') {
                    urlSelectWrapper.classList.add('d-none');
                    urlSelect.removeAttribute('required');
                    urlSelect.removeAttribute('name');

                    urlInputWrapper.classList.remove('d-none');
                    urlInput.setAttribute('required', 'required');
                    urlInput.setAttribute('name', 'url');
                } else if (type === 'promotion') {
                    urlInputWrapper.classList.add('d-none');
                    urlInput.removeAttribute('required');
                    urlInput.removeAttribute('name');

                    urlSelectWrapper.classList.remove('d-none');
                    urlSelect.setAttribute('required', 'required');
                    urlSelect.setAttribute('name', 'url');
                    setSelectOptions(urlSelect, promotionUrls, "Promoaksiya tanlang...");
                } else if (type === 'game') {
                    urlInputWrapper.classList.add('d-none');
                    urlInput.removeAttribute('required');
                    urlInput.removeAttribute('name');

                    urlSelectWrapper.classList.remove('d-none');
                    urlSelect.setAttribute('required', 'required');
                    urlSelect.setAttribute('name', 'url');
                    setSelectOptions(urlSelect, gameUrls, "O‘yin tanlang...");
                } else {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.add('d-none');
                    urlSelect.removeAttribute('required');
                    urlSelect.removeAttribute('name');
                    urlInput.removeAttribute('required');
                    urlInput.removeAttribute('name');
                }
            });
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
                <form action="{{ route('admin.banners.data') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- TITLE (multi-language) --}}
                    <div class="row">
                        @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Sarlavha ({{ $label }})</label>
                                <input type="text" name="title[{{ $lang }}]" class="form-control"
                                       value="{{ old("title.$lang") }}" required>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Media fayl ({{ $label }})</label>
                                <input type="file" name="media[{{ $lang }}]" class="filepond"
                                       data-lang="{{ $label }}" required />
                            </div>
                        @endforeach
                    </div>

                    {{-- Banner type --}}
                    <div class="mb-3">
                        <label class="form-label">Banner turi</label>
                        <select name="banner_type" id="banner_type" class="form-select" required>
                            <option value="">Tanlang...</option>
                            <option value="promotion" {{ old('banner_type') == 'promotion' ? 'selected' : '' }}>Promo Aksiya</option>
                            <option value="url" {{ old('banner_type') == 'url' ? 'selected' : '' }}>Tashqi link</option>
                            <option value="game" {{ old('banner_type') == 'game' ? 'selected' : '' }}>O'yin</option>
                            <option value="news" disabled>News</option>
                        </select>
                    </div>

                    {{-- URL select --}}
                    <div class="mb-3 d-none" id="url_select_wrapper">
                        <label class="form-label">URL</label>
                        <select id="url_select" class="form-select"></select>
                    </div>

                    {{-- URL input --}}
                    <div class="mb-3 d-none" id="url_input_wrapper">
                        <label class="form-label">URL</label>
                        <input type="text" id="url_input" class="form-control"
                               value="{{ old('url') }}">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
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
