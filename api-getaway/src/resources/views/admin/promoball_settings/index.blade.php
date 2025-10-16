@extends('admin.layouts.app')

@section('title', 'Platforma Promo Sozlamalari')

@push('scripts')
<style>
    /* .lang-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 20px;
        transition: 0.2s ease-in-out;
    }

    .lang-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .lang-title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 10px;
        color: #3b3b3b;
    }

    .form-label {
        font-weight: 500;
    } */
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

            {{-- 🟢 Default Points --}}
            <div class="mb-4">
                <label class="form-label">Default promobal soni</label>
             <input
    type="number"
    name="default_points"
    value="{{ old('default_points', $settings['default_points'] ?? 0) }}"
    min="0"
    class="form-control"
    required
>
                <small class="text-muted">
                    Foydalanuvchi har doim oladigan promobal miqdori.
                </small>
            </div>

          {{-- 🌍 Win Messages --}}
<div class="row">
    @foreach ([
        'uz' => 'O‘zbekcha',
        'ru' => 'Русский',
        'en' => 'English',
        'kr' => 'Кириллча'
    ] as $lang => $label)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold mb-2">
                        Yutuq xabari ({{ $label }})
                    </label>
<textarea
    name="win_message[{{ $lang }}]"
    rows="3"
    class="form-control"
    placeholder="Masalan: Siz :promo promobal oldingiz. Yana urinib ko‘ring!"
    required>{{ old('win_message.' . $lang, $settings['win_message'][$lang] ?? '') }}</textarea>

                    <small class="text-muted d-block mt-2">
                        Xabarda <code>:promo</code> tokeni avtomatik ravishda ball soni bilan almashtiriladi.
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
