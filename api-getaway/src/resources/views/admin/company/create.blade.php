@extends('admin.layouts.app')
@section('title', 'Company add')
@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <!-- FilePond JS -->
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );

            const inputElement = document.querySelector('input.filepond-logo');
            const oldPreview = document.getElementById('oldLogoPreview');

            if (inputElement) {
                const pond = FilePond.create(inputElement, {
                    allowMultiple: false,
                    maxFiles: 1,
                    maxFileSize: '512KB',
                    acceptedFileTypes: ['image/*'],
                    storeAsFile: true, // Faylni form-data orqali yuborish uchun
                    labelIdle: 'Rasmni bu yerga tashlang yoki <span class="filepond--label-action">tanlang</span>',
                });

                // Yangi fayl yuklanganda eski preview yashirish
                pond.on('addfile', () => {
                    if (oldPreview) oldPreview.style.display = 'none';
                });

                // Fayl o‘chirib tashlanganda eski preview qaytarish
                pond.on('removefile', () => {
                    if (oldPreview) oldPreview.style.display = 'flex';
                });
            }
        });
    </script>
    <script>
        $(document).on('change', 'input[name="logo"]', function(evt) {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#company-logo-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        $(document).on('change', '#status', function() {
            let checked = $(this).is(':checked');
            let label = $('#status-label');
            if (checked) {
                label.html('<i class="ph ph-check-circle text-success"></i> Faol');
            } else {
                label.html('<i class="ph ph-x-circle text-danger"></i> Faol emas');
            }
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            @php
                $user = Session::get('user');
            @endphp
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Foydalanuvchi ma'lumotlarini tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.company.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        @php
                            $languages = [
                                'uz' => 'O‘zbekcha',
                                'ru' => 'Русский',
                                'kr' => 'Krillcha',
                                'en' => 'English',
                            ];
                        @endphp

                        <div class="row">
                            @foreach ($languages as $lang => $label)
                                <div class="col-lg-3 mb-3">
                                    <label class="form-label">
                                        Nomi ({{ $label }}) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name[{{ $lang }}]" class="form-control" required
                                        maxlength="255">
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            @foreach ($languages as $lang => $label)
                                <div class="col-lg-3 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]" class="form-control"
                                        maxlength="255">
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            @foreach ($languages as $lang => $label)
                                <div class="col-lg-3 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea name="description[{{ $lang }}]" class="form-control" rows="2" maxlength="1000"></textarea>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label fw-bold">
                                    Logo <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="logo" class="filepond-logo" required
                                    accept="image/png,image/jpeg,image/jpg,image/gif" data-max-file-size="512KB">

                                @if (!empty($company['logo']['url'] ?? null))
                                    <div id="oldLogoPreview" class="preview-container mt-2 d-flex">
                                        <img src="{{ $company['logo']['url'] }}" alt="Company Logo" class="img-thumbnail"
                                            style="max-width: 130px; height: 130px;">
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" name="email" class="form-control" required maxlength="255">
                                    </div>

                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">
                                            Hudud <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="region" class="form-control" required maxlength="255">
                                        <input type="hidden" name="created_by_user_id" value="{{ $user['id'] }}">
                                    </div>

                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">
                                            Manzil <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="address" class="form-control" required maxlength="255">
                                    </div>

                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">
                                            Javobgar shaxs <span class="text-danger">*</span>
                                        </label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">Tanlang...</option>
                                            @foreach ($clients?? [] as $client)
                                                <option value="{{ $client['id'] }}">
                                                    {{ $client['name'] }} ({{ $client['email'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mb-3 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status"
                                                value="1" checked required>
                                            <label class="form-check-label ms-2" for="status" id="status-label">
                                                Faol <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center gap-2">

                            <a href="{{ route('admin.company.index') }}" class="btn btn-outline-secondary">
                                <i class="ph-arrow-circle-left me-1"></i> Bekor qilish
                            </a>

                            <button type="reset" class="btn btn-outline-warning">
                                <i class="ph-arrow-clockwise me-1"></i> Yangilash
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
