@extends('admin.layouts.app')

@section('title', 'Homiyni tahrirlash')

@push('scripts')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <style>
        .preview-container {
            width: 100%;
            max-height: 300px;
            background: #2d2d2d;
            border: 15px solid #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-height: 200px;
            position: relative;
        }

        .preview-img {
            max-height: 100%;
            height: 270px;
            width: auto;
            display: block;
            border-radius: 10px;
            margin: auto;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }

        /* Asiryarklashib boradigan qoraroq shadow yuqori qismida */
        .preview-container::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 80px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.6) 100%);
            z-index: 3;
            pointer-events: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview,
            );
            const logoInput = document.querySelector('.filepond-logo');
            if (logoInput) {
                const pond = FilePond.create(logoInput, {
                    allowMultiple: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    acceptedFileTypes: ['image/*'],
                    labelIdle: 'Homiy logotipini yuklang yoki tanlang',
                });

                // --- eski preview boshqaruvi ---
                const oldPreview = document.getElementById('oldPreview');
                pond.on('addfile', () => {
                    if (oldPreview) oldPreview.style.display = 'none';
                });
                pond.on('removefile', () => {
                    if (oldPreview) oldPreview.style.display = 'block';
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Homiyni tahrirlash</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sponsors.update', $sponsor['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Nomi ({{ $label }})</label>
                            <input type="text" class="form-control" name="name[{{ $lang }}]"
                                value="{{ old('name.' . $lang, $sponsor['name'][$lang] ?? '') }}" >
                            <small class="text-muted">Homiy nomini {{ $label }} tilida kiriting.</small>
                        </div>
                    @endforeach
                </div>
                {{-- 🖼 Logo --}}
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Homiy logotipi</label>
                        <input type="file" name="logo" class="filepond-logo" data-max-file-size="5MB"
                            accept="image/*">

                        @if (!empty($sponsor['logo']))
                            <div id="oldPreview" class="preview-container mt-3">
                                <img src="{{ asset($sponsor['logo']) }}" alt="Sponsor Logo" class="preview-img">
                            </div>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label">Homiy URL manzili</label>
                            <input type="url" class="form-control" name="url"
                                value="{{ old('url', $sponsor['url'] ?? '') }}" placeholder="https://example.com">
                            <small class="text-muted">Homiy sayt yoki ijtimoiy tarmoq sahifasi.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tartib raqami</label>
                            <input type="number" class="form-control" name="weight"
                                value="{{ old('weight', $sponsor['weight'] ?? 0) }}" required>
                            <small class="text-muted">Homiylarni chiqarish tartibini belgilang.</small>
                        </div>

                        <div class="mb-3 form-check form-switch mt-4">
                            <input type="hidden" name="status" value="0"> {{-- Default qiymat --}}
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch"
                                {{ old('status', $sponsor['status'] ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">Faollik</label>
                            <small class="text-muted d-block">Agar yoqilgan bo‘lsa, homiy foydalanuvchilarga
                                ko‘rsatiladi.</small>
                        </div>
                    </div>
                </div>

                {{-- 🔘 Submit --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('admin.sponsors.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
                    <button type="submit" class="btn btn-primary">Yangilash</button>
                </div>
            </form>
        </div>
    </div>
@endsection
