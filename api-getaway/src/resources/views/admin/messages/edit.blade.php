@extends('admin.layouts.app')
@section('title', 'Default Xabarni Tahrirlash')

@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/form_layouts.js') }}"></script>
@endpush

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Default xabarni tahrirlash</h5>
                    <span class="text-muted fs-sm">
                        Bu sahifada siz tizim tomonidan foydalanuvchiga yuboriladigan <strong>avtomatik xabar
                            matnlarini</strong> tahrirlashingiz mumkin.
                    </span>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.settings.messages.update', $message['id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Meta info --}}
                        <div class="row">
                            {{-- Scope --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label fw-bold">Qo‘llanish sohasi (Scope) <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['scope_type']) }}</option>
                                </select>
                                <input type="hidden" name="scope_type" value="{{ $message['scope_type'] }}">
                                <small class="form-text text-muted">
                                    <strong>Platform</strong> — umumiy tizim uchun.<br>
                                    <strong>Promotion</strong> — faqat ma’lum aksiya uchun.<br>
                                    <strong>Prize</strong> — faqat sovrin darajasidagi xabar.
                                </small>
                            </div>

                            {{-- Type --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label fw-bold">Xabar turi (Type) <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['type']) }}</option>
                                </select>
                                <input type="hidden" name="type" value="{{ $message['type'] }}">
                                <small class="form-text text-muted">
                                    <strong>Promo</strong> — promokodlar uchun.<br>
                                    <strong>Receipt</strong> — cheklar (xarid) uchun.
                                </small>
                            </div>

                            {{-- Status --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label fw-bold">Holat (Status) <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['status']) }}</option>
                                </select>
                                <input type="hidden" name="status" value="{{ $message['status'] }}">
                                <small class="form-text text-muted">
                                    @switch($message['status'])
                                        @case('claim')
                                            Bu holatda foydalanuvchi <strong>ilgari ro‘yxatdan o‘tgan</strong> promokod yoki chekni
                                            kiritgan.
                                        @break

                                        @case('pending')
                                            Promokod yoki chek <strong>qabul qilindi</strong>, natija e’lon qilinishi kutilmoqda.
                                        @break

                                        @case('invalid')
                                            Foydalanuvchi kiritgan ma’lumot <strong>noto‘g‘ri yoki o‘qilmadi</strong>.
                                        @break

                                        @case('win')
                                            Foydalanuvchi <strong>yutgan</strong> holat — tizim sovrinni bildiradi.
                                        @break

                                        @case('lose')
                                            Foydalanuvchi <strong>yutqazgan</strong> holat.
                                        @break

                                        @case('fail')
                                            <strong>Tizim xatosi</strong> yoki xabar yuborilmadi.
                                        @break
                                    @endswitch
                                </small>
                            </div>

                            {{-- Channel --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label fw-bold">Kanal (Channel) <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ strtoupper($message['channel']) }}</option>
                                </select>
                                <input type="hidden" name="channel" value="{{ $message['channel'] }}">
                                <small class="form-text text-muted">
                                    <strong>Telegram</strong> — bot orqali yuboriladi.<br>
                                    <strong>SMS</strong> — foydalanuvchiga matnli xabar sifatida.<br>
                                    <strong>Mobile / Web</strong> — ilova yoki sayt interfeysida ko‘rsatiladi.
                                </small>
                            </div>
                        </div>

                        {{-- Dynamic placeholder info --}}
                        <div class="alert alert-info border-start border-info border-3">
                            <i class="ph-info me-1"></i>
                            Xabar matnida quyidagi dinamik belgilarni ishlatish mumkin:
                             <ul class="mb-0 mt-1">
        <li><code>:code</code> — foydalanuvchining kiritgan <strong>promokodi</strong></li>
        <li><code>:prize</code> — foydalanuvchi yutgan <strong>sovrin nomi</strong> yoki <strong>chek ID</strong> raqami
        </li>
                        </div>

                        {{-- Dynamic message input fields --}}
                        <div class="row">
                            @php $isMultiLang = is_array($message['message']); @endphp

                            {{-- Single-language (SMS) --}}
                            @unless ($isMultiLang)
                                <div class="mb-3 col-12">
                                    <label class="form-label fw-bold">Xabar matni <span class="text-danger">*</span></label>
                                    <input type="text" name="message" class="form-control"
                                        value="{{ old('message', $message['message']) }}"
                                        placeholder="Masalan: Promokod noto‘g‘ri. Iltimos, qaytadan urinib ko‘ring." required>
                                    <small class="form-text text-muted">
                                        SMS xabarlar uchun qisqa, aniq va 160 belgidan oshmaydigan matn yozing.
                                    </small>
                                    @error('message')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            @else
                                {{-- Multilingual version --}}
                                @foreach ($message['message'] as $lang => $text)
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">
                                            Xabar matni ({{ strtoupper($lang) }}) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="message[{{ $lang }}]" class="form-control"
                                            value="{{ old("message.$lang", $text) }}"
                                            placeholder="Masalan: {{ strtoupper($lang) }} tilidagi foydalanuvchi uchun matn"
                                            required>
                                    </div>
                                @endforeach
                                <small class="form-text text-muted">
                                    Har bir til uchun alohida matn kiriting. Agar biror til bo‘sh qolsa, tizim
                                    <strong>UZ</strong> tilidagi matndan foydalanadi.
                                </small>
                            @endunless
                        </div>

                        {{-- Example preview --}}
                        <div class="alert alert-secondary mt-3">
                            <strong>Namunaviy natija:</strong><br>
                            @php
                                $example = is_array($message['message'])
                                    ? $message['message']['uz'] ?? reset($message['message'])
                                    : $message['message'];
                                $example = str_replace(
                                    [':code', ':id', ':prize'],
                                    ['A1B2C3', 'CHK12345', 'Powerbank #WJR'],
                                    $example,
                                );
                            @endphp
                            <span class="text-muted">{{ $example }}</span>
                        </div>

                        {{-- Action buttons --}}
                        <div class="d-flex justify-content-end border-top pt-3">
                            {{-- <a href="{{ route('admin.settings.messages.index') }}" class="btn btn-outline-secondary">
                                Bekor qilish
                            </a> --}}
                            <button type="submit" class="btn btn-primary ms-2">
                                <i class="ph-check-circle me-1"></i> Yangilash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
