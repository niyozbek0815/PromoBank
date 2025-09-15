@extends('admin.layouts.app')

@section('title', "Yangi homiy qo'shish")

@push('scripts')
    {{-- FilePond CSS & Plugins --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js">
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview
            );

            // Homiy logotipi uchun FilePond
            const logoInput = document.querySelector('.filepond-logo');
            if (logoInput) {
                FilePond.create(logoInput, {
                    allowMultiple: false,
                    storeAsFile: true,
                    maxFiles: 1,
                    instantUpload: false,
                    acceptedFileTypes: ['image/*'],
                    labelIdle: 'Homiy logotipini yuklang yoki tanlang',
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
                    <h5 class="mb-0">Yangi homiy qo'shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sponsors.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            {{-- Translatable fields --}}
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Кириллча'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">
                                        Homiy nomi ({{ $label }})
                                    </label>
                                    <input type="text" name="name[{{ $lang }}]"
                                        class="form-control @error("name.$lang") is-invalid @enderror"
                                        value="{{ old("name.$lang") }}" >
                                    @error("name.$lang")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Homiy nomini {{ $label }} tilida kiriting</small>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            {{-- Logo upload --}}
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">
                                    Homiy logotipi <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="logo" class="filepond-logo" required
                                    data-max-file-size="512KB" accept="image/*" />
                                <div class="form-text text-muted">
                                    <strong>Formatlar:</strong> jpg, png, svg <br>
                                    <strong>Max hajm:</strong> 512 KB
                                </div>
                                @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                     <div class=" mb-3">
                                <label class="form-label">Homiy veb-sayti  <span class="text-danger">*</span></label>
                                <input type="url" name="url" class="form-control @error('url') is-invalid @enderror"
                                    value="{{ old('url') }}" placeholder="https://company.com" required>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Homiy kompaniya rasmiy saytini yoki sahifasini kiriting</small>
                            </div>

                            <div class=" mb-3">
                                <label class="form-label">Tartib raqami  <span class="text-danger">*</span></label>
                                <input type="number" name="weight"
                                    class="form-control @error('weight') is-invalid @enderror"
                                    value="{{ old('weight', 0) }}" min="0" required>
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Homiy ko‘rsatilish tartibi (0 = yuqorida)</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                        id="statusSwitch" {{ old('status', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusSwitch">
                                        <strong>Faol homiy</strong><br>
                                        <small class="text-muted">Belgilansa, homiy foydalanuvchilarga ko‘rinadi</small>
                                    </label>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.sponsors.index') }}" class="btn btn-outline-secondary">
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
