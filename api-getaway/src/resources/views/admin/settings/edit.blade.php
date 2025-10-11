@extends('admin.layouts.app')

@section('title', "Umumiy sozlamalarni tahrirlash")

@push('scripts')
    {{-- FilePond CSS & JS --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginImagePreview
    );

    document.querySelectorAll('.filepond-image').forEach(input => {
        const pond = FilePond.create(input, {
            allowMultiple: false,
            maxFileSize: '512KB',
            acceptedFileTypes: ['image/*'],
            storeAsFile: true,
        });

        // 🔗 Preview <img> ni topish (data-preview-for orqali)
        const previewImg = document.querySelector(`img[data-preview-for="${input.name}"]`);

        if (previewImg) {
            pond.on('addfile', () => {
                previewImg.style.display = 'none';
            });
            pond.on('removefile', () => {
                previewImg.style.display = 'block';
            });
        }
    });
});
</script>
@endpush

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">Umumiy sozlamalarni tahrirlash</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Logos --}}
            <div class="row">
           <div class="col-lg-6 mb-3">
    <label class="form-label fw-bold">Navbar logotipi</label>
    <input type="file" name="navbar_logo" class="filepond-image">
    @if(!empty($settings['navbar_logo']))
        <img src="{{ asset($settings['navbar_logo']) }}"
             class="img-thumbnail mt-2"
             height="60"
             data-preview-for="navbar_logo">
    @endif
</div>

<div class="col-lg-6 mb-3">
    <label class="form-label fw-bold">Footer logotipi</label>
    <input type="file" name="footer_logo" class="filepond-image">
    @if(!empty($settings['footer_logo']))
        <img src="{{ asset($settings['footer_logo']) }}"
             class="img-thumbnail mt-2"
             height="60"
             data-preview-for="footer_logo">
    @endif
</div>
            </div>

            {{-- Hero Title --}}
            <div class="row">
                @foreach(['uz'=>'O‘zbekcha','ru'=>'Ruscha','kr'=>'Krill'] as $lang=>$label)
                    <div class="col-lg-4 mb-3">
                        <label class="form-label fw-bold">Hero Title ({{ $label }})</label>
                        <input type="text" class="form-control" name="hero_title[{{ $lang }}]" required
                               value="{{ old('hero_title.'.$lang, $settings['hero_title'][$lang] ?? '') }}">
                    </div>
                @endforeach
            </div>

            {{-- Footer Description --}}
            <div class="row">
                @foreach(['uz'=>'O‘zbekcha','ru'=>'Ruscha','kr'=>'Krill'] as $lang=>$label)
                    <div class="col-lg-4 mb-3">
                        <label class="form-label fw-bold">Footer Tavsifi ({{ $label }})</label>
                        <input type="text" class="form-control" name="footer_description[{{ $lang }}]" required
                               value="{{ old('footer_description.'.$lang, $settings['footer_description'][$lang] ?? '') }}">
                    </div>
                @endforeach
            </div>

            {{-- Footer Bottom --}}
            <div class="row">
                @foreach(['uz'=>'O‘zbekcha','ru'=>'Ruscha','kr'=>'Krill'] as $lang=>$label)
                    <div class="col-lg-4 mb-3">
                        <label class="form-label fw-bold">Footer Pastki qismi ({{ $label }})</label>
                        <input type="text" class="form-control" name="footer_bottom[{{ $lang }}]" required
                               value="{{ old('footer_bottom.'.$lang, $settings['footer_bottom'][$lang] ?? '') }}">
                    </div>
                @endforeach
            </div>

            {{-- Default language select --}}
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="form-label fw-bold">Asosiy til</label>
                    <select class="form-select" name="languages[default]" required>
                        @foreach($settings['languages']['available'] ?? [] as $lang)
                            <option value="{{ $lang }}"
                                {{ ($settings['languages']['default'] ?? 'uz') === $lang ? 'selected' : '' }}>
                                {{ strtoupper($lang) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Tillar orasidan asosiy tilni tanlang</small>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Yangilash</button>
            </div>
        </form>
    </div>
</div>
@endsection
