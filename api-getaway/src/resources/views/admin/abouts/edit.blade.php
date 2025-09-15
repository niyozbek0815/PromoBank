@extends('admin.layouts.app')

@section('title', 'About malumotlarini tahrirlash')

@push('scripts')
    {{-- FilePond CSS --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
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

        .preview-container img {
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

            const inputElement = document.querySelector('input.filepond-image');
            const oldPreview = document.getElementById('oldPreview');

            if (inputElement) {
                const pond = FilePond.create(inputElement, {
   allowMultiple: false,
                    maxFiles: 1,
                    maxFileSize: '512KB',
                    acceptedFileTypes: ['image/*'],
                    storeAsFile: true,
                });

                // Fayl qo‘shilganda eski preview yashirish
                pond.on('addfile', () => {
                    if (oldPreview) oldPreview.style.display = 'none';
                });

                // Fayl o‘chirilganda eski preview qaytarish
                pond.on('removefile', () => {
                    if (oldPreview) oldPreview.style.display = 'flex';
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">About malumotlarini tahrirlash</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.abouts.update')}}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Title ({{ $label }})</label>
                            <input type="text" class="form-control" name="title[{{ $lang }}]"
                                value="{{ old('title.' . $lang, $about['title'][$lang] ?? '') }}" required>
                        </div>
                    @endforeach
                </div>

                {{-- Subtitle --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Subtitle ({{ $label }})</label>
                            <input type="text" class="form-control" name="subtitle[{{ $lang }}]"
                                value="{{ old('subtitle.' . $lang, $about['subtitle'][$lang] ?? '') }}" required>
                        </div>
                    @endforeach
                </div>

                {{-- Description --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label fw-bold">Description ({{ $label }})</label>
                            <textarea class="form-control" name="description[{{ $lang }}]" rows="3">{{ old('description.' . $lang, $about['description'][$lang] ?? '') }}</textarea>
                        </div>
                    @endforeach
                </div>

                {{-- List --}}
            <div class="row">
    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krill'] as $lang => $label)
        <div class="col-lg-4 mb-3">
            <label class="form-label fw-bold">List Items ({{ $label }})</label>
            @php
                $list = $about['list'][$lang] ?? [];
                for ($i = count($list); $i < 6; $i++) {
                    $list[$i] = '';
                }
            @endphp
            <div class="list-items">
                @foreach ($list as $i => $item)
                    <input type="text" class="form-control mb-1" name="list_{{ $lang }}[]"
                        value="{{ old('list_' . $lang . '.' . $i, $item) }}" required>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

                {{-- Image --}}
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label fw-bold">Rasm</label>
                        <input type="file" name="about_image" class="filepond-image" data-max-file-size="512KB" accept="image/*">
                        @if (!empty($about['image']))
                            <div id="oldPreview" class="preview-container mt-2">
                                <img src="{{ asset($about['image']) }}" alt="About Image">
                            </div>
                        @endif
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.abouts.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
                    <button type="submit" class="btn btn-primary">Yangilash</button>
                </div>

            </form>
        </div>
    </div>
@endsection
