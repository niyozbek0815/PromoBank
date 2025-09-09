@extends('admin.layouts.app')
@section('title', 'Notification tahrirlash')

@push('scripts')
    {{-- === FilePond kutubxonalari === --}}
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
    {{-- === Select2 kutubxonalari === --}}
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <link href="{{ asset('adminpanel/assets/css/select2.min.css') }}" rel="stylesheet" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === FilePond ===
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );
            const inputElement = document.getElementById('mediaInput');
            const pond = FilePond.create(inputElement, {
                storeAsFile: true,
                allowMultiple: false,
                maxFileSize: '20MB',
                acceptedFileTypes: ['image/*', 'image/gif'],
                labelIdle: '📂 <span class="filepond--label-action">Faylni tanlang</span> yoki tashlang',
                credits: false
            });

            // --- eski preview boshqaruvi ---
            const oldPreview = document.getElementById('oldPreview');

            pond.on('addfile', () => {
                if (oldPreview) oldPreview.style.display = 'none';
            });

            pond.on('removefile', () => {
                if (oldPreview) oldPreview.style.display = 'block';
            });

            // === Platforma select2 ===
            $('#types').select2({
                width: '100%',
                placeholder: "Platformalarni tanlang"
            });

            // === Users select2 (AJAX) ===
            $('#users').select2({
                placeholder: "Foydalanuvchilarni qidirish...",
                width: '100%',
                ajax: {
                    url: "/admin/notifications/users",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term,
                        page: params.page || 1,
                        per_page: 20
                    }),
                    processResults: (data, params) => ({
                        results: data.data.map(item => ({
                            id: item.id,
                            text: item.text
                        })),
                        pagination: {
                            more: data.current_page < data.last_page
                        }
                    })
                }
            });

            // Oldindan tanlangan users
            @if (!empty($selectedUsers))
                let selectedData = @json($selectedUsers);
                selectedData.forEach(item => {
                    let option = new Option(item.text, item.id, true, true);
                    $('#users').append(option).trigger('change');
                });
            @endif

            // === Link type boshqaruvi ===
            const promotionUrls = @json($promotionUrls);
            const gameUrls = @json($gameUrls);
            const currentLink = @json(old('link', $notification['link']));

            function setSelectOptions(select, options, selected = null, forceDefault = false) {
                select.innerHTML = "";

                // Default option
                const defaultOption = new Option("Tanlang...", "", true, false);
                defaultOption.disabled = true;
                select.appendChild(defaultOption);

                options.forEach(opt => {
                    const val = String(opt.value ?? opt.id ?? "");
                    let label = opt.label;
                    try {
                        label = JSON.parse(opt.label).uz ?? opt.label;
                    } catch (e) {}

                    // Agar forceDefault true bo‘lsa — backenddagi selected ni ham e’tiborsiz qoldiramiz
                    const isSelected = !forceDefault && (val === selected);
                    const option = new Option(label, val, isSelected, isSelected);
                    select.appendChild(option);
                });

                // Agar default bo‘lishi kerak bo‘lsa — "Tanlang..." turib qoladi
                if (forceDefault || !selected) {
                    select.value = "";
                }
            }

            function toggleLinkInput() {
                const type = document.getElementById('link_type').value;
                const urlSelectWrapper = document.getElementById('url_select_wrapper');
                const urlInputWrapper = document.getElementById('url_input_wrapper');
                const urlSelect = document.getElementById('url_select');
                const urlInput = document.getElementById('url_input');

                const backendType = @json($notification['link_type'] ?? null);
                const backendLink = @json($notification['link'] ?? null);

                if (type === 'url') {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.remove('d-none');

                    urlInput.disabled = false;
                    urlSelect.disabled = true;

                    urlInput.value = (backendType === 'url') ? (backendLink || "") : "";
                } else if (type === 'promotion') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');

                    urlInput.disabled = true;
                    urlSelect.disabled = false;

                    const forceDefault = backendType !== 'promotion';
                    setSelectOptions(urlSelect, promotionUrls, backendLink, forceDefault);
                } else if (type === 'game') {
                    urlInputWrapper.classList.add('d-none');
                    urlSelectWrapper.classList.remove('d-none');

                    urlInput.disabled = true;
                    urlSelect.disabled = false;

                    const forceDefault = backendType !== 'game';
                    setSelectOptions(urlSelect, gameUrls, backendLink, forceDefault);
                } else {
                    urlSelectWrapper.classList.add('d-none');
                    urlInputWrapper.classList.add('d-none');

                    urlInput.disabled = true;
                    urlSelect.disabled = true;
                }
            }



            document.getElementById('link_type').addEventListener('change', toggleLinkInput);
            toggleLinkInput();

            // === Target type toggle ===
            const targetType = document.getElementById('target_type');

            function toggleTargetInput() {
                ['platform', 'users', 'excel'].forEach(t => document.getElementById(t + '_wrapper').classList.add(
                    'd-none'));
                if (targetType.value) document.getElementById(targetType.value + '_wrapper').classList.remove(
                    'd-none');
            }
            targetType.addEventListener('change', toggleTargetInput);
            toggleTargetInput();
        });
    </script>
@endpush

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notification tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.notifications.update', $notification['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- === Sarlavha === --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label>Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]" class="form-control"
                                        value="{{ old("title.$lang", $notification['title'][$lang] ?? '') }}" required>
                                </div>
                            @endforeach
                        </div>

                        {{-- === Matn === --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label>Matn ({{ $label }})</label>
                                    <textarea name="text[{{ $lang }}]" class="form-control" rows="3" required>
                                    {{ old("text.$lang", $notification['text'][$lang] ?? '') }}

                                </textarea>
                                </div>
                            @endforeach
                        </div>

                        {{-- === Media === --}}
                        {{-- === Media === --}}
                        <div class="mb-3">
                            <label>Notification rasmi</label>
                            <input type="file" name="media" class="filepond" id="mediaInput" />

                            @if ($notification['image'])
                                <div id="oldPreview" class="preview-container mt-3">
                                    <img src="{{ $notification['image'] }}" alt="Notification Image" class="preview-img">
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            {{-- Scheduled at (agar o‘tmagan bo‘lsa) --}}
                            @php
                                use Carbon\Carbon;

                                $scheduledAt = !empty($notification['scheduled_at'])
                                    ? Carbon::parse($notification['scheduled_at'])
                                    : null;

                                $showScheduledInput =
                                    $scheduledAt && $scheduledAt->greaterThan(Carbon::now()->addMinutes(10));
                            @endphp

                            @if ($showScheduledInput)
                                <div class="col-6 mb-3">
                                    <label>Yuborish vaqti</label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control"
                                        value="{{ old('scheduled_at', $scheduledAt?->format('Y-m-d\TH:i')) }}">
                                </div>
                            @endif

                            {{-- Target type --}}
                            <div class="col-6 mb-3">
                                <label>Qabul qiluvchilar turi</label>
                                <select name="target_type" id="target_type" class="form-select" required>
                                    @foreach (['platform' => 'Platformalar', 'users' => 'Foydalanuvchilar', 'excel' => 'Excel'] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('target_type', $notification['target_type'] ?? null) === $val ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Platform --}}
                            <div class="col-6 mb-3 d-none" id="platform_wrapper">
                                <label>Platformalar</label>

                                <select name="type[]" id="types" class="form-select" multiple>
                                    @foreach (['ios', 'android', 'web', 'telegram'] as $opt)
                                        <option value="{{ $opt }}"
                                            {{ in_array($opt, old('type', $notification['platforms'] ? collect($notification['platforms'])->pluck('platform')->toArray() : [])) ? 'selected' : '' }}>
                                            {{ ucfirst($opt) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Users --}}
                            <div class="col-6 mb-3 d-none" id="users_wrapper">
                                <label>Foydalanuvchilar</label>
                                <select name="users[]" id="users" class="form-select" multiple></select>
                            </div>

                            {{-- Excel --}}
                            <div class="col-6 mb-3 d-none" id="excel_wrapper">
                                <label>Excel fayl</label>
                                <input type="file" name="excel_file" id="excel_file" class="form-control"
                                    accept=".xls,.xlsx,.csv" />
                            </div>

                            {{-- Link type --}}
                            <div class="col-6 mb-3">
                                <label>Link turi</label>
                                <select name="link_type" id="link_type" class="form-select" required>
                                    @foreach (['game', 'promotion', 'url', 'message'] as $lt)
                                        <option value="{{ $lt }}"
                                            {{ old('link_type', $notification['link_type'] ?? null) === $lt ? 'selected' : '' }}>
                                            {{ ucfirst($lt) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Link select --}}
                            <div class="col-6 mb-3 d-none" id="url_select_wrapper">
                                <label>Link</label>
                                <select id="url_select" name="link" class="form-select">
                                    <option value="" selected>Tanlang...</option>
                                </select>
                            </div>

                            {{-- Custom URL --}}
                            <div class="col-6 mb-3 d-none" id="url_input_wrapper">
                                <label>URL</label>
                                <input type="text" id="url_input" name="link" class="form-control"
                                    value="{{ old('link', $notification['link'] ?? '') }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
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
