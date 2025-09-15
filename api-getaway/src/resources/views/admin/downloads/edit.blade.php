@extends('admin.layouts.app')

@section('title', 'Download malumotlarini tahrirlash')

@push('scripts')
    {{-- FilePond CSS & Plugins --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

    <style>
        .preview-container {
            width: 100%;
            max-height: 300px;
            background: #0000001d;
            border: 10px solid #ddd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            min-height: 200px;
            position: relative;
        }

        .preview-img {
            max-height: 100%;
            height: 270px;
            width: auto;
            display: block;
            margin: auto;
            object-fit: contain;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );

            const imageInput = document.querySelector('.filepond-image');
            if (imageInput) {
                const pond = FilePond.create(imageInput, {
                    allowMultiple: false,
                    maxFiles: 1,
                    maxFileSize: '512KB',
                    acceptedFileTypes: ['image/*'],
                    storeAsFile: true,
                });

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
            <h5 class="mb-0">Download malumotlarini tahrirlash</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.downloads.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Title fields --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Sarlavha ({{ $label }})</label>
                            <input type="text" class="form-control" name="title[{{ $lang }}]"
                                value="{{ old('title.' . $lang, $download['title'][$lang] ?? '') }}" required>
                        </div>
                    @endforeach
                </div>

                {{-- Subtitle fields --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Subtitle ({{ $label }})</label>
                            <input type="text" class="form-control" name="subtitle[{{ $lang }}]" required
                                value="{{ old('subtitle.' . $lang, $download['subtitle'][$lang] ?? '') }}">
                        </div>
                    @endforeach
                </div>

                {{-- Description fields --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Description ({{ $label }})</label>
                            <textarea class="form-control" name="description[{{ $lang }}]" rows="3">{{ old('description.' . $lang, $download['description'][$lang] ?? '') }}</textarea>
                        </div>
                    @endforeach
                </div>

                {{-- Image --}}
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-bold">Rasm</label>
                        <input type="file" name="image" class="filepond-image" data-max-file-size="512KB"
                            accept="image/*">

                        @if (!empty($download['image']))
                            <div id="oldPreview" class="preview-container mt-2">
                                <img src="{{ asset($download['image']) }}" alt="Download Image" class="preview-img">
                            </div>
                        @endif
                    </div>
                    <div class="col-6">

                            <h6 class="fw-bold">Yuklab olish havolalari</h6>
                       @php
    $defaultLinks = [
        'googleplay' => 'Google Play',
        'appstore'   => 'App Store',
        'telegram'   => 'Telegram',
    ];
@endphp

@foreach ($defaultLinks as $type => $label)
    <div class="col-12 mb-3">
        <label class="form-label">{{ $label }}</label>
        <input type="text" class="form-control" name="links[{{ $type }}]" required
               value="{{ old('links.' . $type, $download['links'][$type] ?? '') }}">
    </div>
@endforeach


                    </div>

                    {{-- Status --}}

                </div>

                {{-- Download links --}}


                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.downloads.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
                    <button type="submit" class="btn btn-primary">Yangilash</button>
                </div>
            </form>
        </div>
    </div>
@endsection
