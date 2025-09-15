@extends('admin.layouts.app')

@section('title', "Bog'lanish malumotlarini tahrirlash")

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Bog'lanish malumotlarini tahrirlash</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.contacts.update', $contact['id']) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Type --}}
                @php
                    $types = [
                        'address',
                        'phone',
                        'email',
                        'whatsapp',
                        'telegram',
                        'linkedin',
                        'facebook',
                        'instagram',
                    ];
                @endphp

                <div class="mb-3">
                    <label class="form-label">
                        Turi <span class="text-danger">*</span>
                    </label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- Tanlang --</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}"
                                {{ old('type', $contact['type'] ?? '') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Kontakt turini tanlang (masalan: phone, email, address)</small>
                </div>

                {{-- URL / Value --}}
                <div class="mb-3">
                    <label class="form-label">Qiymati <span class="text-danger">*</span></label>
                    <input type="text" name="url"
                           class="form-control @error('url') is-invalid @enderror"
                           value="{{ old('url', $contact['url'] ?? '') }}" required maxlength="1024">
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Telefon raqam, email yoki havolani kiriting</small>
                </div>

                {{-- Label fields --}}
                <div class="row">
                    @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Кирillcha'] as $lang => $label)
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Label ({{ $label }})</label>
                            <input type="text" name="label[{{ $lang }}]"
                                   class="form-control @error("label.$lang") is-invalid @enderror"
                                   value="{{ old("label.$lang", $contact['label'][$lang] ?? '') }}">
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
                                   value="{{ old('position', $contact['position'] ?? 0) }}" min="0" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kontakt chiqarilish tartibi</small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        {{-- Status --}}
                        <div class="mb-3 form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1"
                                   id="statusSwitch"
                                   {{ old('status', $contact['status'] ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">Faollik</label>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary">
                        Bekor qilish
                    </a>
                    <button type="submit" class="btn btn-primary">Yangilash</button>
                </div>
            </form>
        </div>
    </div>
@endsection
