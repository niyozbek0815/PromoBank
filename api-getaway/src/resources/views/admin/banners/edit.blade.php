@extends('admin.layouts.app')
@section('title', 'Banner tahrirlash')

@push('scripts')
    {{-- FilePond & CKEditor --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
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
            // CKEditor
            document.querySelectorAll('.ckeditor').forEach(el => {
                ClassicEditor.create(el).then(editor => {
                    editor.model.document.on('change:data', () => {
                        el.value = editor.getData();
                    });
                }).catch(console.error);
            });

            // FilePond setup
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview,
                FilePondPluginFilePoster
            );
            const defaultConfig = {
                storeAsFile: true,
                allowMultiple: false,
                allowReorder: false,
                instantUpload: false,
                maxFiles: 1,
                maxFileSize: '20MB',
                acceptedFileTypes: ['image/*', 'video/mp4', 'video/webm', 'image/gif'],
                labelIdle: '📂 <span class="filepond--label-action">Faylni tanlang</span> yoki tashlang',
                credits: false,
                allowImagePreview: true,
            };

            document.querySelectorAll('input.filepond').forEach(input => {
                const pond = FilePond.create(input, {
                    ...defaultConfig
                });

                pond.on('init', () => {
                    // Boshlang‘ichda qo‘l preview bor bo‘lsa → FilePond preview yashirilsin
                    const root = pond.element;
                    const fpPreview = root.querySelector('.filepond--list');
                    if (fpPreview) fpPreview.style.display = 'none';
                });

                pond.on('addfile', () => {
                    const lang = input.dataset.lang;
                    const manualPreview = document.getElementById('preview-' + lang);
                    if (manualPreview) manualPreview.style.display = 'none';

                    // FilePond previewni ko‘rsatamiz
                    const root = pond.element;
                    const fpPreview = root.querySelector('.filepond--list');
                    if (fpPreview) fpPreview.style.display = 'block';
                });

                pond.on('removefile', () => {
                    const lang = input.dataset.lang;
                    const manualPreview = document.getElementById('preview-' + lang);
                    if (manualPreview) manualPreview.style.display = 'block';

                    // FilePond previewni yana yashiramiz
                    const root = pond.element;
                    const fpPreview = root.querySelector('.filepond--list');
                    if (fpPreview) fpPreview.style.display = 'none';
                });
            });
            // Banner type toggle
            const bannerTypeSelect = document.getElementById('banner_type');
            const urlSelectWrapper = document.getElementById('url_select_wrapper');
            const urlInputWrapper = document.getElementById('url_input_wrapper');
            const urlSelect = document.getElementById('url_select');
            const urlInput = document.getElementById('url_input');
            const promotionUrls = @json($promotionUrls);
            const gameUrls = @json($gameUrls);
            const currentType = "{{ $bannerData['banner_type'] }}";
            const currentUrl = @json($bannerData['url']);

            function setOptions(select, options, selectedValue, placeholder) {
                select.innerHTML = "";
                const def = document.createElement('option');
                def.value = "";
                def.textContent = placeholder;
                def.disabled = true;
                def.selected = !selectedValue;
                select.appendChild(def);

                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = String(opt.value);
                    let label = opt.label;
                    try {
                        label = JSON.parse(opt.label).uz ?? opt.label;
                    } catch (e) {}
                    option.textContent = label;
                    if (String(opt.value) === String(selectedValue)) option.selected = true;
                    select.appendChild(option);
                });
            }

            function toggleUrlField(type, value) {
                if (type === 'url') {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.remove('d-none');
                    urlInput.value = value ?? "";
                    urlInput.name = 'url';
                    urlSelect.removeAttribute('name');
                } else if (type === 'promotion') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');
                    setOptions(urlSelect, promotionUrls, value, "Promoaksiya tanlang...");
                    urlSelect.name = 'url';
                    urlInput.removeAttribute('name');
                } else if (type === 'game') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');
                    setOptions(urlSelect, gameUrls, value, "O‘yin tanlang...");
                    urlSelect.name = 'url';
                    urlInput.removeAttribute('name');
                } else {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.add('d-none');
                    urlSelect.removeAttribute('name');
                    urlInput.removeAttribute('name');
                }
            }

            bannerTypeSelect.addEventListener('change', e => toggleUrlField(e.target.value));
            toggleUrlField(currentType, currentUrl); // init
        });
    </script>
@endpush

@section('content')
    <div class="tab-content flex-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Banner tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.banners.update', $bannerData['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf @method('PUT')

                        {{-- TITLE --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]" class="form-control"
                                        value="{{ old("title.$lang", $bannerData['title'][$lang] ?? '') }}" required>
                                </div>
                            @endforeach
                        </div>

                        {{-- MEDIA --}}
                        <div class="row">
                            @foreach (['uz' => 'banners_uz', 'ru' => 'banners_ru', 'kr' => 'banners_kr'] as $lang => $key)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Media ({{ strtoupper($lang) }})</label>

                                    {{-- qo‘l preview --}}
                                    @if (!empty($bannerData[$key]['full_url'] ?? ($bannerData[$key]['url'] ?? '')))
                                        <div class="manual-preview mb-2" id="preview-{{ $lang }}">
                                            @php
                                                $url = $bannerData[$key]['full_url'] ?? $bannerData[$key]['url'];
                                            @endphp
                                            @if (preg_match('/\.(mp4|webm)$/i', $url))
                                                <video src="{{ $url }}" class="w-100 rounded border"
                                                    controls></video>
                                            @else
                                                <img src="{{ $url }}" class="img-fluid rounded border"
                                                    alt="preview">
                                            @endif
                                        </div>
                                    @endif

                                    <input type="file" class="filepond" name="media[{{ $lang }}]"
                                        data-lang="{{ $lang }}">
                                </div>
                            @endforeach
                        </div>

                        {{-- TYPE --}}
                        <div class="mb-3">
                            <label class="form-label">Banner turi</label>
                            <select name="banner_type" id="banner_type" class="form-select" required>
                                <option value="">Tanlang...</option>
                                <option value="promotion"
                                    {{ $bannerData['banner_type'] == 'promotion' ? 'selected' : '' }}>
                                    Promo
                                    Aksiya</option>
                                <option value="url" {{ $bannerData['banner_type'] == 'url' ? 'selected' : '' }}>Tashqi
                                    link
                                </option>
                                <option value="game" {{ $bannerData['banner_type'] == 'game' ? 'selected' : '' }}>O'yin
                                </option>
                                <option value="news" disabled>News</option>
                            </select>
                        </div>

                        {{-- URL / SELECT --}}
                        <div class="mb-3 d-none" id="url_select_wrapper">
                            <label class="form-label">URL</label>
                            <select id="url_select" class="form-select"></select>
                        </div>
                        <div class="mb-3 d-none" id="url_input_wrapper">
                            <label class="form-label">URL</label>
                            <input type="text" id="url_input" class="form-control"
                                value="{{ old('url', $bannerData['url']) }}">
                        </div>

                        {{-- STATUS --}}
                        <div class="mb-3">
                            <label class="form-label">Holati</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $bannerData['status'] ? 'selected' : '' }}>Aktiv</option>
                                <option value="0" {{ !$bannerData['status'] ? 'selected' : '' }}>Deaktiv</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Bekor qilish</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-floppy-disk me-1"></i> Yangilash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
