@extends('admin.layouts.app')

@section('title', 'Platforma Promo Sozlamalari')

@push('styles')
<style>
    .lang-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: 0.2s ease-in-out;
    }
    .lang-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .form-label {
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Platforma Promo Sozlamalarini Tahrirlash</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.settings.platform-promoball.update', $settings['id']) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- 🟢 Scanner uchun promobal --}}
            <div class="mb-4">
                <label class="form-label">Scanner orqali beriladigan promobal soni</label>
                <input type="number" name="scanner_points"
                       value="{{ old('scanner_points', $settings['scanner_points'] ?? 0) }}"
                       min="0" class="form-control" required>
                <small class="text-muted">
                    Foydalanuvchi chekni skanerlaganda avtomatik oladigan promobal miqdori.
                </small>
            </div>

            {{-- 🟠 Referal bosqichlari --}}
            <div class="mb-4">
                <label class="form-label">Referal boshlangan paytda beriladigan promobal</label>
                <input type="number" name="refferal_start_points"
                       value="{{ old('refferal_start_points', $settings['refferal_start_points'] ?? 0) }}"
                       min="0" class="form-control" required>
                <small class="text-muted">
                    Foydalanuvchi birinchi marta referal orqali ro‘yxatdan o‘tganda oladigan promobal.
                </small>
            </div>

            <div class="mb-4">
                <label class="form-label">Referal ro‘yxatdan o‘tganida (tasdiqlanganda) beriladigan promobal</label>
                <input type="number" name="refferal_registered_points"
                       value="{{ old('refferal_registered_points', $settings['refferal_registered_points'] ?? 0) }}"
                       min="0" class="form-control" required>
                <small class="text-muted">
                    Referal foydalanuvchi ro‘yxatdan o‘tib faol holatga o‘tganda, uni taklif qilgan foydalanuvchiga beriladigan promobal.
                </small>
            </div>

            {{-- 🌍 Yutuq xabarlari (multi-language) --}}
            <div class="row">
                @foreach ([
                    'uz' => 'O‘zbekcha',
                    'ru' => 'Русский',
                    'en' => 'English',
                    'kr' => 'Кириллча',
                ] as $lang => $label)
                    <div class="col-lg-6 mb-4">
                        <div class="card lang-card h-100 border shadow-sm">
                            <div class="card-body">
                                <label class="form-label fw-semibold mb-2">
                                    Yutuq xabari ({{ $label }})
                                </label>
                                <textarea name="win_message[{{ $lang }}]" rows="3" class="form-control"
                                          placeholder="Masalan: Siz :promo promobal oldingiz. Yana urinib ko‘ring!" required>{{ old('win_message.' . $lang, $settings['win_message'][$lang] ?? '') }}</textarea>

                                <small class="text-muted d-block mt-2">
                                    Xabarda <code>:promo</code> tokeni avtomatik ravishda foydalanuvchining yutgan promobal soni bilan almashtiriladi.
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 🔘 Submit buttons --}}
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    Bekor qilish
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph-check-circle me-1"></i> Saqlash
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
