@extends('admin.layouts.app')
@section('title', "Qisqa raqamni tahrirlash")

@section('content')
<div class="tab-content flex-1 order-2 order-lg-1">
    <div class="tab-pane fade show active">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sirli raqamni tahrirlash</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.secret-number.update', $secret['id']) }}" method="POST">
                    @csrf
                    {{-- @method('PUT') --}}
                    <div class="row">
                        {{-- Promoaksiya tanlash --}}
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">
                                Qaysi promoaksiyaga tegishli <span class="text-danger">*</span>
                            </label>
                            <select name="promotion_id" id="promotionSelect" class="form-select select2-single" required>
                                <option value="">Tanlang...</option>
                                @foreach ($promotions as $promo)
                                    <option value="{{ $promo['id'] }}"
                                        {{ $secret['promotion_id'] == $promo['id'] ? 'selected' : '' }}>
                                        {{ $promo['name_uz'] ?? 'Nomi mavjud emas' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Faqat “Secret Number” turi bo‘lgan promoaksiyalar ro‘yxatida ko‘rsatiladi.
                            </small>
                        </div>

                        {{-- Qisqa raqam --}}
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">Sirli raqam <span class="text-danger">*</span></label>
                            <input type="text" name="number" maxlength="10" class="form-control"
                                   value="{{ old('number', $secret['number']) }}"
                                   placeholder="Masalan: 1234 yoki 9090" required>
                        </div>

                        {{-- Ball miqdori --}}
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">Beriladigan ball</label>
                         <input type="number" name="points" min="1" step="1" class="form-control"
       value="{{ old('points', $secret['points'] ?? '') }}"
       placeholder="Masalan: 10, 20, 50 ...">
                            <small class="text-muted">
                                Ushbu raqam yuborilganda foydalanuvchiga beriladigan ball miqdorini kiriting. Agar kiritilmasa promoaksiyadan default qiymat olinadi.
                            </small>
                        </div>

                        {{-- Boshlanish sanasi --}}
                        <div class="col-lg-6 mb-3">
                            <label class="form-label fw-bold">Sirli raqam faollashadigan vaqt <span class="text-danger">*</span></label>
                          <input type="datetime-local" name="start_at" class="form-control"
       value="{{ old('start_at', \Illuminate\Support\Carbon::parse($secret['start_at'])->format('Y-m-d\TH:i')) }}"
       required>
                            <small class="text-muted">
                                Shu vaqt kelganda raqam faollashadi va foydalanuvchilardan qabul qilish boshlanadi.
                            </small>
                        </div>
                    </div>

                    {{-- Tugmalar --}}
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-warning">
                            <i class="ph-arrow-clockwise me-1"></i> Tozalash
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph-paper-plane-tilt me-1"></i> Yangilash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.select2-single').select2({
        placeholder: "Tanlang...",
        allowClear: true
    });

    // Select2-ni backenddan kelgan qiymat bilan set qilish
    $('#promotionSelect').val('{{ $secret['promotion_id'] }}').trigger('change');
});
</script>
@endpush
