@extends('admin.layouts.app')
@section('title', "Promoaksiya qo'shish")
@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>

@endpush
@section('content')
 <div class="tab-content flex-1 order-2 order-lg-1">
    <div class="tab-pane fade show active" id="settings">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Xarid cheki skaneri uchun do'kon qo'shish</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.promotion_shops.store') }}" method="POST">
                    @csrf

                    {{-- 🔽 Promotion tanlash --}}
                    <div class="mb-3">
                        <label class="form-label">Promotion</label>

                        @if (!empty($selected_promotion))
                            {{-- Agar oldindan tanlangan bo‘lsa — readonly (disabled) --}}
                            <select class="form-select" disabled>
                                @foreach ($promotions as $promotion)
                                    <option value="{{ $promotion['id'] }}"
                                        {{ $selected_promotion == $promotion['id'] ? 'selected' : '' }}>
                                        {{ $promotion['name'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Yashirin input — serverga yuboriladi --}}
                            <input type="hidden" name="promotion_id" value="{{ $selected_promotion }}">
                        @else
                            {{-- Oddiy select --}}
                            <select name="promotion_id" class="form-select select2-single" required>
                                <option value="">-- Tanlang --</option>
                                @foreach ($promotions as $promotion)
                                    <option value="{{ $promotion['id'] }}"
                                        {{ old('promotion_id') == $promotion['id'] ? 'selected' : '' }}>
                                        {{ $promotion['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @error('promotion_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- 🏪 Do‘kon nomi --}}
                    <div class="mb-3">
                        <label class="form-label">Do‘kon nomi</label>
                        <input type="text"
                               class="form-control"
                               name="name"
                               placeholder="Masalan: Mega Market"
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="text-muted">Do‘kon nomi xarid chekida qanday bo‘lsa, aynan shunday kiriting.</small>
                    </div>

                    {{-- 📍 Manzil --}}
                    <div class="mb-3">
                        <label class="form-label">Manzil</label>
                        <textarea class="form-control"
                                  name="adress"
                                  rows="3"
                                  placeholder="Masalan: Toshkent sh., Chilonzor tumani, Qatortol ko‘chasi, 12-uy"
                                  required>{{ old('adress') }}</textarea>
                        @error('adress')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="text-muted">Manzil xarid chekidagi ma’lumot bilan bir xil bo‘lishi kerak.</small>
                    </div>

                    {{-- 🔘 Submit --}}
                    <div class="d-flex justify-content-end">
                        <a href="
                         @if (!empty($selected_promotion))
                            {{ route('admin.promotion.edit', $selected_promotion) }}
                            @else
                                {{ route('admin.promotion_shops.index') }}
                            @endif
                        " class="btn btn-outline-secondary">Bekor qilish</a>
                        <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
