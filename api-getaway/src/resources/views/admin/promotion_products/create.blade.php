@extends('admin.layouts.app')
@section('title', "Promoaksiya Do'konlari uchun product qo'shish")
@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_multiselect.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const promotionSelect = document.getElementById('promotionSelect');
            const shopSelect = document.getElementById('shopSelect');

            if (promotionSelect) {
                promotionSelect.addEventListener('change', function() {
                    const promotionId = this.value;

                    // Barcha do'konlarni qayta ko'rsatishdan oldin tozalaymiz
                    Array.from(shopSelect.options).forEach(option => {
                        if (!option.value) return; // "-- Tanlang --"
                        option.style.display = (option.dataset.promotion === promotionId) ?
                            'block' : 'none';
                    });

                    // Filterdan keyin shop selectni default holatga qaytaramiz
                    shopSelect.value = '';
                });
            }
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aksiya mahsuloti qo‘shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion_products.store') }}" method="POST">
                        @csrf

                        {{-- 🏪 Do‘kon tanlash --}}
                        {{-- 🎯 Promotion tanlash --}}
                        <div class="mb-3">
                            <label class="form-label">Aksiya</label>

                            @if (!empty($selected_promotion))
                                {{-- Readonly bo'lsa --}}
                                <select class="form-select" disabled>
                                    @foreach ($promotions as $promotion)
                                        <option value="{{ $promotion['id'] }}"
                                            {{ $promotion['id'] == $selected_promotion ? 'selected' : '' }}>
                                            {{ $promotion['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="promotion_id" value="{{ $selected_promotion }}">
                            @else
                                {{-- Tanlanadigan --}}
                                <select name="promotion_id" id="promotionSelect" class="form-select" required>
                                    <option value="">-- Tanlang --</option>
                                    @foreach ($promotions as $promotion)
                                        <option value="{{ $promotion['id'] }}">
                                            {{ $promotion['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error('promotion_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 🏪 Shop tanlash --}}
                        <div class="mb-3">
                            <label class="form-label">Do‘kon</label>

                            @if (!empty($selected_shop))
                                {{-- Readonly --}}
                                <select class="form-select" disabled>
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop['id'] }}"
                                            {{ $selected_shop == $shop['id'] ? 'selected' : '' }}>
                                            {{ $shop['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="shop_id" value="{{ $selected_shop }}">
                            @else
                                {{-- Tanlanadigan --}}
                                <select name="shop_id" id="shopSelect" class="form-select" required>
                                    <option value="">-- Tanlang --</option>
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop['id'] }}" data-promotion="{{ $shop['promotion_id'] }}">
                                            {{ $shop['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error('shop_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 📦 Mahsulot nomi --}}
                        <div class="mb-3">
                            <label class="form-label">Mahsulot nomi</label>
                            <input type="text" class="form-control" name="name" placeholder="Masalan: Coca-Cola 1.5L"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="text-muted">Mahsulot nomini chekda qanday ko‘rsatilgan bo‘lsa, shunday
                                yozing.</small>
                        </div>

                        {{-- ✅ Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Faol</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Nofaol</option>
                            </select>
                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 🔘 Submit --}}
                        <div class="d-flex justify-content-end">
                            <a href="
                               @if (!empty($selected_shop)) {{ route('admin.promotion_shops.edit', $selected_shop) }}
                            @else
                                {{ route('admin.promotion_shops.index') }} @endif

                         "
                                class="btn btn-outline-secondary">Bekor qilish</a>
                            <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
