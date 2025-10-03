@extends('admin.layouts.app')
@section('title', 'Default Xabarni Tahrirlash')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
@endpush

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Default xabarni tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.messages.update', $message['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-4">
                                <label class="form-label">Scope turi <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['scope_type']) }}</option>
                                </select>
                                <input type="hidden" name="scope_type" value="{{ $message['scope_type'] }}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Xabar turi <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['type']) }}</option>
                                </select>
                                <input type="hidden" name="type" value="{{ $message['type'] }}">
                            </div>
                            <div class="mb-3 col-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" disabled>
                                    <option selected>{{ ucfirst($message['status']) }}</option>
                                </select>
                                <input type="hidden" name="status" value="{{ $message['status'] }}">
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ph-info me-1"></i>
                            Xabar matnida <code>:code</code> qo‘shing.
                            Bu joy avtomatik ravishda <strong>Promokod</strong> yoki <strong>Chek ID</strong> bilan
                            to‘ldiriladi.
                        </div>
                        <div class="row">
                            @foreach ($message['message'] as $lang => $text)
                                <div class="mb-3 col-6">
                                    <label class="form-label text-muted">
                                        Xabar matni( {{ strtoupper($lang) }}) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="message[{{ $lang }}]" class="form-control"
                                        value="{{ old("message.$lang", $text) }}" required>
                                    @error("message.$lang")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.settings.messages.index') }}" class="btn btn-outline-secondary">Bekor
                                qilish</a>
                            <button type="submit" class="btn btn-primary ms-2">Yangilash</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
