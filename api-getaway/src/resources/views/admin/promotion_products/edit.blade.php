@extends('admin.layouts.app')
@section('title', "Promoaksiya Do'konlari uchun productni tahrirlash")

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
                    <h5 class="mb-0">Aksiya mahsulotini tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion_products.update', $product['id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- 🔽 Promotion (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Aksiya <span class="text-danger">*</span></label>
                            <select class="form-select" disabled>
                                @foreach ($promotions as $promotion)
                                    <option value="{{ $promotion['id'] }}"
                                        {{ $promotion['id'] == $product['promotion_id'] ? 'selected' : '' }}>
                                        {{ $promotion['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="promotion_id" value="{{ $product['promotion_id'] }}">
                        </div>

                        {{-- 🏪 Shop (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Do‘kon <span class="text-danger">*</span></label>
                            <select class="form-select" disabled>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop['id'] }}"
                                        {{ $shop['id'] == $product['shop_id'] ? 'selected' : '' }}>
                                        {{ $shop['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="shop_id" value="{{ $product['shop_id'] }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mahsulot nomi <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   name="name"
                                   value="{{ old('name', $product['name']) }}"
                                   placeholder="Masalan: Coca-Cola 1.5L"
                                   required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="text-muted">Mahsulot nomini chekda qanday ko‘rsatilgan bo‘lsa, shunday yozing.</small>
                        </div>

                        {{-- ✅ Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', $product['status']) == 1 ? 'selected' : '' }}>Faol</option>
                                <option value="0" {{ old('status', $product['status']) == 0 ? 'selected' : '' }}>Nofaol</option>
                            </select>
                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 🔘 Submit --}}
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.promotion_shops.edit', $product['shop_id']) }}"
                               class="btn btn-outline-secondary">Bekor qilish</a>
                            <button type="submit" class="btn btn-primary ms-2">Yangilash</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
