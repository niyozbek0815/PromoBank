@extends('admin.layouts.app')
@section('title', "Notification qo'shish")

@push('scripts')
    {{-- FilePond --}}
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

    {{-- Select2 --}}
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <link href="{{ asset('adminpanel/assets/css/select2.min.css') }}" rel="stylesheet" />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === FilePond init ===
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );
            FilePond.create(document.querySelector('input.filepond'), {
                storeAsFile: true,
                allowMultiple: false,
                maxFileSize: '20MB',
                acceptedFileTypes: ['image/*', 'image/gif'],
                labelIdle: '📂 <span class="filepond--label-action">Faylni tanlang</span> yoki tashlang',
                credits: false
            });

            // === Select2 for platforms ===
            $('#types').select2({
                placeholder: "Platformalarni tanlang",
                width: '100%'
            });

            // === Select2 for users (AJAX + old selected + duplicate prevent) ===
  $('#users').select2({
    placeholder: "Foydalanuvchilarni qidirish...",
    width: '100%',
    ajax: {
        url: "/admin/notifications/users",
        dataType: 'json',
        delay: 250,
        data: params => ({
            q: params.term || '',
            page: params.page || 1,
            per_page: 20
        }),
        processResults: (data, params) => {
            params.page = params.page || 1;
            return {
                results: data.data.map(item => ({
                    id: item.id,   // bu yerda id = phone
                    text: item.text // bu yerda text = phone
                })),
                pagination: {
                    more: data.current_page < data.last_page
                }
            };
        },
        cache: true
    }
});


            // oldindan tanlangan userlar
       @if (!empty($selectedUsers))
    let selectedData = @json($selectedUsers);
    selectedData.forEach(function(item) {
        // backend 'id' va 'text' qaytargan bo'lsa, faqat telefon raqami chiqadi
        let option = new Option(item.text, item.id, true, true);
        $('#users').append(option).trigger('change');
    });
@endif

            // dublikatni oldini olish
            $('#users').on('select2:select', function(e) {
                let selectedIds = $(this).val() || [];
                if (selectedIds.filter((id, i, self) => self.indexOf(id) !== i).length > 0) {
                    toastr.warning('Bu foydalanuvchi allaqachon tanlangan!', 'Diqqat');
                    $(this).find(`option[value="${e.params.data.id}"]`).prop("selected", false);
                    $(this).trigger('change');
                }
            });

            // === Dynamic Link Handling ===
            const linkTypeSelect = document.getElementById('link_type');
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

            function toggleLinkInput() {
                const type = linkTypeSelect.value;
                if (type === 'url') {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.remove('d-none');
                    urlInput.setAttribute('required', 'required');
                    urlInput.setAttribute('name', 'link');
                } else if (type === 'promotion') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');
                    urlSelect.setAttribute('required', 'required');
                    urlSelect.setAttribute('name', 'link');
                    setSelectOptions(urlSelect, promotionUrls, "Promoaksiya tanlang...");
                } else if (type === 'game') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');
                    urlSelect.setAttribute('required', 'required');
                    urlSelect.setAttribute('name', 'link');
                    setSelectOptions(urlSelect, gameUrls, "O‘yin tanlang...");
                } else {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.add('d-none');
                    urlSelect.removeAttribute('required');
                    urlInput.removeAttribute('required');
                }
            }

            linkTypeSelect.addEventListener('change', toggleLinkInput);
            toggleLinkInput();

            // === Target Type Toggle ===
            const targetTypeSelect = document.getElementById('target_type');
            const platformWrapper = document.getElementById('platform_wrapper');
            const usersWrapper = document.getElementById('users_wrapper');
            const excelWrapper = document.getElementById('excel_wrapper');

            function toggleTargetInput() {
                const type = targetTypeSelect.value;
                [platformWrapper, usersWrapper, excelWrapper].forEach(el => el.classList.add('d-none'));

                if (type === 'platform') {
                    platformWrapper.classList.remove('d-none');
                    document.getElementById('types').setAttribute('required', 'required');
                } else if (type === 'users') {
                    usersWrapper.classList.remove('d-none');
                    document.getElementById('users').setAttribute('required', 'required');
                } else if (type === 'excel') {
                    excelWrapper.classList.remove('d-none');
                    document.getElementById('excel_file').setAttribute('required', 'required');
                }
            }

            targetTypeSelect.addEventListener('change', toggleTargetInput);
            toggleTargetInput();
        });
    </script>
@endpush

@section('content')
<div class="tab-content flex-1 order-2 order-lg-1">
    <div class="tab-pane fade show active">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notification platformlarga yoki userlarga yuborish</h5>
                <small>Quyidagi formani to‘ldiring va kerakli foydalanuvchilarga xabar yuboring.</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Title multi-lang --}}
                    <div class="row">
                        @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Sarlavha ({{ $label }})</label>
                                <input type="text" name="title[{{ $lang }}]" class="form-control"
                                       value="{{ old("title.$lang") }}" required>
                            </div>
                        @endforeach
                    </div>

                    {{-- Text multi-lang --}}
                    <div class="row">
                        @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Matn ({{ $label }})</label>
                                <textarea name="text[{{ $lang }}]" class="form-control" rows="3" required>{{ old("text.$lang") }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    {{-- Media --}}
                    <div class="mb-3">
                        <label class="form-label">Notification uchun rasm</label>
                        <input type="file" name="media" class="filepond" required />
                    </div>

                    {{-- Target Type --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Qabul qiluvchilar turi</label>
                            <select name="target_type" id="target_type" class="form-select" required>
                                <option value="">Tanlang...</option>
                                <option value="platform">Platformalar</option>
                                <option value="users">Tanlangan foydalanuvchilar</option>
                                <option value="excel">Excel orqali</option>
                            </select>
                        </div>

                        {{-- Platform wrapper --}}
                        <div class="col-6 d-none" id="platform_wrapper">
                            <label class="form-label">Platformalar</label>
                            <select name="type[]" id="types" class="form-select" multiple>
                                @foreach (['ios', 'android', 'web', 'telegram'] as $option)
                                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Users wrapper --}}
                        <div class="col-6 d-none" id="users_wrapper">
                            <label class="form-label">Foydalanuvchilar</label>
                            <select name="users[]" id="users" class="form-select" multiple></select>
                        </div>

                        {{-- Excel wrapper --}}
                        <div class="col-6 d-none" id="excel_wrapper">
                            <label class="form-label">Excel fayl</label>
                            <input type="file" name="excel_file" id="excel_file" class="form-control" />
                        </div>
                    </div>

                    {{-- Link Type --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Link turi</label>
                            <select name="link_type" id="link_type" class="form-select" required>
                                <option value="">Tanlang...</option>
                                @foreach (['game', 'promotion', 'url', 'message'] as $lt)
                                    <option value="{{ $lt }}">{{ ucfirst($lt) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 d-none" id="url_select_wrapper">
                            <label class="form-label">Link</label>
                            <select id="url_select" class="form-select"></select>
                        </div>

                        <div class="col-6 d-none" id="url_input_wrapper">
                            <label class="form-label">URL</label>
                            <input type="text" id="url_input" class="form-control" value="{{ old('link') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-paper-plane-tilt me-1"></i> Yuborish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
