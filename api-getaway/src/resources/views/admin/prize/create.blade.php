@extends('admin.layouts.app')
@section('title', 'Sovgʻa yaratish')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).on('change', '#is_active', function() {
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

@php
    use Illuminate\Support\Facades\Session;
    $buttonClass =
        'btn w-100 d-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 shadow-sm border-0 transition-all';
    $userId = Session::get('user')['id'] ?? null;
@endphp

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-semibold">Sovgʻa yaratish</h5>
                    <p class="text-muted mb-0 small">
                        {{ $promotion['name']['uz'] }} aksiyasi uchun <code>{{ $category['display_name'] }}</code>
                        kategoriyasi asosida yangi sovgʻa qo‘shish
                    </p>
                </div>

                <div class="card-body">
                    <div class="row row align-items-stretch gx-5">
                        {{-- 🟢 Asosiy ma’lumotlar --}}
                        <div class="col-md-12 mb-4">
                            <div class="row border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" class="row"
                                    action="{{ route('admin.prize.storeByCategory', ['category' => $category['name'], 'promotion' => $promotion['id']]) }}">
                                    @csrf

                                    {{-- 🔒 Yashirin maydonlar --}}
                                    <input type="hidden" class="col-4" name="promotion_id" value="{{ $promotion['id'] }}">
                                    <input type="hidden" class="col-4" name="category_id" value="{{ $category['id'] }}">
                                    <input type="hidden" class="col-4" name="created_by_user_id"
                                        value="{{ $userId }}">

                                    {{-- 🟩 1. Asosiy maʼlumotlar --}}
                                    <h6 class="fw-bold mb-3">Asosiy maʼlumotlar</h6>

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Sovgʻa nomi <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control"
                                                placeholder="Masalan: Powerbank 10 000 mAh" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tartib indeksi</label>
                                            <input type="number" name="index" class="form-control" min="1"
                                                placeholder="Masalan: 1">
                                            <small class="text-muted">Agar kiritilmasa, avtomatik oxiriga
                                                joylashtiriladi.</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tavsif</label>
                                            <textarea name="description" class="form-control" rows="5" placeholder="Sovg‘aning qisqacha tavsifi..."></textarea>
                                        </div>
                                    </div>






                                    {{-- 🟨 2. Miqdor va cheklovlar --}}
                                    <h6 class="fw-bold mb-3">Miqdor va cheklovlar</h6>

                                    <div class="mb-3 col-4">
                                        <label class="form-label">Umumiy miqdor <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" class="form-control" min="1"
                                            placeholder="Masalan: 100" required>
                                    </div>

                                    <div class="mb-3 col-4">
                                        <label class="form-label">Kunlik limit</label>
                                        <input type="number" name="daily_limit" class="form-control" min="0"
                                            placeholder="Masalan: 10">
                                    </div>

                                    @if ($category['name'] === 'weighted_random')
                                        <div class="mb-3 col--4">
                                            <label class="form-label">Yutish ehtimolligi (%)</label>
                                            <input type="number" name="probability_weight" class="form-control"
                                                min="0" max="100" placeholder="Masalan: 25">
                                            <small class="text-muted">Ehtimollik 0–100 oralig‘ida kiritiladi.</small>
                                        </div>
                                    @endif

                                    <div class="mb-3 col-4">
                                        <label class="form-label">Berilgan sovg‘alar soni (faqat statistik)</label>
                                        <input type="number" name="awarded_quantity" class="form-control" min="0"
                                            value="0" readonly>
                                        <small class="text-muted">Tizim tomonidan avtomatik yangilanadi.</small>
                                    </div>

                                    {{-- 🕒 3. Amal qilish muddati --}}
                                    <h6 class="fw-bold mb-3">Amal qilish muddati</h6>

                                    <div class="mb-3 col-4">
                                        <label class="form-label">Boshlanish sanasi</label>
                                        <input type="datetime-local" name="valid_from" class="form-control">
                                    </div>

                                    <div class="mb-3 col-4">
                                        <label class="form-label">Tugash sanasi</label>
                                        <input type="datetime-local" name="valid_until" class="form-control">
                                        <div class="small text-muted mb-3">
                                            Agar sanalar kiritilmasa, sovgʻa muddatsiz amal qiladi.
                                        </div>
                                    </div>



                                    {{-- 🟢 4. Holat --}}
                                    <div class="form-check mt-3">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                            value="1" checked>
                                        <label class="form-check-label" id="status-label" for="is_active">
                                            <i class="ph ph-check-circle text-success"></i> Faol
                                        </label>
                                    </div>

                                    {{-- 💾 5. Tugmalar --}}
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('admin.prize-category.show', ['promotion' => $promotion['id'], 'type' => $category['name']]) }}"
                                            class="{{ $buttonClass }} btn-outline-secondary w-auto">
                                            Bekor qilish
                                        </a>

                                        <button type="submit" class="{{ $buttonClass }} btn-primary w-auto">
                                            Saqlash
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

            <div class="col-md-4 mb-4">
    {{-- <div class="border rounded p-4  d-flex flex-column justify-content-between">
        <form method="POST" class="w-100"
              action="{{ route('admin.prize.importByCategory', ['category' => $category['name'], 'promotion' => $promotion['id']]) }}"
              enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

            <h6 class="fw-bold mb-3">Excel fayldan import</h6>

            <div class="mb-3">
                <label class="form-label">Excel fayl (.xlsx yoki .csv)</label>
                <input type="file" name="prize_file" class="form-control" accept=".xlsx,.csv" required>
                <small class="text-muted d-block mt-1">
                    Fayl faqat <strong>namunadegidek formatda</strong> bo‘lishi kerak,<br>
                    maksimal <strong>5 000 ta sovg'a bo'lishi kerak</strong>.
                </small>
            </div>

            <div class="d-flex flex-column gap-2 mt-3">
                <a href="{{ asset('namuna/promo-default.xlsx') }}"
                   class="{{ $buttonClass }} btn-outline-success" download>
                    <i class="ph ph-download-simple"></i> Namuna fayl
                </a>

                <button type="submit" class="{{ $buttonClass }} btn-success">
                    <i class="ph ph-upload-simple"></i> Exceldan yuklash
                </button>
            </div>
        </form>
    </div> --}}
</div>
                    </div> {{-- row --}}
                </div>
            </div>
        </div>
    </div>
@endsection
