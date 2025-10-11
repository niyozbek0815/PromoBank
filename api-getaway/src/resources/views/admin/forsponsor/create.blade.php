@extends('admin.layouts.app')

@section('title', "Yangi ForSponsor qo'shish")

@push('scripts')
    {{-- FilePond CSS & Plugins --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview
            );

            const imageInput = document.querySelector('.filepond-image');
            if (imageInput) {
                FilePond.create(imageInput, {
                    allowMultiple: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    instantUpload: false,
                    acceptedFileTypes: ['image/*'],
                    labelIdle: 'ForSponsor rasmini yuklang yoki tanlang',
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Yangi ForSponsor qo'shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.forsponsor.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Title fields --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Кириллча'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">
                                        Sarlavha ({{ $label }}) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="title[{{ $lang }}]"
                                           class="form-control @error("title.$lang") is-invalid @enderror"
                                           value="{{ old("title.$lang") }}" required>
                                    @error("title.$lang")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ForSponsor sarlavhasini {{ $label }} tilida kiriting</small>
                                </div>
                            @endforeach
                        </div>

                        {{-- Description fields --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Кириллча'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">
                                        Tavsif ({{ $label }}) <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="description[{{ $lang }}]" rows="4"
                                              class="form-control @error("description.$lang") is-invalid @enderror"
                                              required>{{ old("description.$lang") }}</textarea>
                                    @error("description.$lang")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ForSponsor haqida tavsifni {{ $label }} tilida yozing</small>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            {{-- Image upload --}}
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">
                                    ForSponsor rasmi <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="image" class="filepond-image" required
                                       data-max-file-size="512KB" accept="image/*" />
                                <div class="form-text text-muted">
                                    <strong>Formatlar:</strong> jpg, jpeg, png, svg <br>
                                    <strong>Max hajm:</strong> 512KB
                                </div>
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                {{-- Position --}}
                                <div class="mb-3">
                                    <label class="form-label">Tartib raqami <span class="text-danger">*</span></label>
                                    <input type="number" name="position"
                                           class="form-control @error('position') is-invalid @enderror"
                                           value="{{ old('position', 0) }}" min="0" required>
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ForSponsor chiqarilish tartibi (0 = yuqorida)</small>
                                </div>

                                {{-- Status --}}
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                               id="statusSwitch" {{ old('status', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusSwitch">
                                            <strong>Faol ForSponsor</strong><br>
                                            <small class="text-muted">Belgilansa, foydalanuvchilarga ko‘rinadi</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.forsponsor.index') }}" class="btn btn-outline-secondary">
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
