@extends('admin.layouts.app')
@section('title', 'Company add')
@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script>
        $(document).on('change', 'input[name="logo"]', function(evt) {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#company-logo-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        $(document).on('change', '#status', function() {
            let checked = $(this).is(':checked');
            let label = $('#status-label');
            if (checked) {
                label.html('<i class="ph ph-check-circle text-success"></i> Faol');
            } else {
                label.html('<i class="ph ph-x-circle text-danger"></i> Faol emas');
            }
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            @php
                $user = Session::get('user');
            @endphp
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Foydalanuvchi ma'lumotlarini tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.company.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Nomi ({{ $label }})</label>
                                    <input type="text" name="name[{{ $lang }}]" required class="form-control"
                                        maxlength="255">
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]" class="form-control" required
                                        maxlength="255">
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea name="description[{{ $lang }}]" class="form-control" rows="2" required></textarea>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">Company Logo</label>
                                <div class="mb-2">
                                    <img src="{{ asset('adminpanel/assets/images/default-logo.png') }}" alt="Default logo"
                                        class="img-thumbnail" style="max-width: 130px; height: 130px;"
                                        id="company-logo-preview">
                                </div>
                                <input type="file" name="logo" required class="form-control">
                                <div class="form-text text-muted">Ruxsat etilgan formatlar: gif, png, jpg. Maksimal hajm:
                                    2Mb</div>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="" class="form-control"
                                            maxlength="255" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Hudud</label>
                                        <input type="text" name="region" value="" class="form-control" required
                                            maxlength="255">
                                        <input type="text" name="created_by_user_id" value="{{ $user['id'] }}" hidden
                                            class="form-control" required maxlength="255">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Manzil</label>
                                        <input type="text" name="address" value="" class="form-control" required
                                            maxlength="255">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Javobgar shaxs</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">Tanlang...</option>
                                            @foreach ($clients['clients'] ?? [] as $client)
                                                <option value="{{ $client['id'] }}">
                                                    {{ $client['name'] }} ({{ $client['email'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mb-3 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status"
                                                required value="1" checked>
                                            <label class="form-check-label ms-2" for="status" id="status-label">
                                                Faol
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center gap-2">

                            <a href="{{ route('admin.company.index') }}" class="btn btn-outline-secondary">
                                <i class="ph-arrow-circle-left me-1"></i> Bekor qilish
                            </a>

                            <button type="reset" class="btn btn-outline-warning">
                                <i class="ph-arrow-clockwise me-1"></i> Yangilash
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
