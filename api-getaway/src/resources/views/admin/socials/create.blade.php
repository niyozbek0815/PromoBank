@extends('admin.layouts.app')

@section('title', "Yangi ijtimoiy tarmoq qo'shish")

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Yangi ijtimoiy tarmoq qo'shish</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.socials.store') }}" method="POST">
                        @csrf

                        {{-- Type --}}
                   @php
    $types = [
        'instagram',
        'facebook',
        'telegram',
        'youtube',
        'appstore',
        'googleplay',
    ];
@endphp

<div class="mb-3">
    <label class="form-label">
        Turi (masalan: facebook, instagram, telegram) <span class="text-danger">*</span>
    </label>
    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
        <option value="">-- Tanlang --</option>
        @foreach($types as $type)
            <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                {{ ucfirst($type) }}
            </option>
        @endforeach
    </select>
    @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Platforma turini tanlang (masalan: facebook)</small>
</div>

                        {{-- URL --}}
                        <div class="mb-3">
                            <label class="form-label">URL <span class="text-danger">*</span></label>
                            <input type="url" name="url"
                                   class="form-control @error('url') is-invalid @enderror"
                                   value="{{ old('url') }}" required maxlength="1024">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">To‘liq ijtimoiy tarmoq havolasini kiriting</small>
                        </div>

                        {{-- Label fields --}}
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Кириллча'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Label ({{ $label }})</label>
                                    <input type="text" name="label[{{ $lang }}]"
                                           class="form-control @error("label.$lang") is-invalid @enderror"
                                           value="{{ old("label.$lang") }}">
                                    @error("label.$lang")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Ixtiyoriy: {{ $label }} tilida ko‘rinadigan nom</small>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                {{-- Position --}}
                                <div class="mb-3">
                                    <label class="form-label">Tartib raqami <span class="text-danger">*</span></label>
                                    <input type="number" name="position"
                                           class="form-control @error('position') is-invalid @enderror"
                                           value="{{ old('position', 0) }}" min="0" required>
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Ijtimoiy tarmoq chiqarilish tartibi (0 = yuqorida)</small>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                {{-- Status --}}
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="status" value="0">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"
                                               id="statusSwitch" {{ old('status', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusSwitch">
                                            <strong>Faol social link</strong><br>
                                            <small class="text-muted">Belgilansa, foydalanuvchilarga ko‘rinadi</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.socials.index') }}" class="btn btn-outline-secondary">
                                <i class="ph-arrow-circle-left me-1"></i> Bekor qilish
                            </a>
                            <button type="reset" class="btn btn-outline-warning">
                                <i class="ph-arrow-clockwise me-1"></i> Tozalash
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-paper-plane-tilt me-1"></i> Saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
