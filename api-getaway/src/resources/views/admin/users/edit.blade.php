@extends('admin.layouts.app')

@section('title', "Foydalanuvchi ma'lumotlarini tahrirlash")

@push('scripts')

    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#region').on('change', function() {
                let regionId = $(this).val();
                let $districtSelect = $('#district');

                $districtSelect.html('<option>Yuklanmoqda...</option>');

                if (regionId) {
                    $.ajax({
                        url: `/admin/region/${regionId}/districts`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            let options = '<option value="">Tanlang</option>';
                            if (res && res.data && res.data.districts) {
                                $.each(res.data.districts, function(id, name) {
                                    options += `<option value="${id}">${name}</option>`;
                                });
                            }
                            $districtSelect.html(options);
                        },
                        error: function() {
                            $districtSelect.html('<option value="">Xatolik</option>');
                        }
                    });
                } else {
                    $districtSelect.html('<option value="">Tanlang</option>');
                }
            });
        });
        $(document).on('change', 'input[name="image"]', function(evt) {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#user-avatar-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Foydalanuvchi ma'lumotlarini tahrirlash</h5>
                </div>


                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user['id']) }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

              {{-- F.I.Sh va Email --}}
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">F.I.Sh <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ $user['name'] }}"
               class="form-control" maxlength="255">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ $user['email'] }}"
               class="form-control" maxlength="255">
    </div>
</div>

{{-- Telefon raqamlar --}}
<div class="row">
    <div class="col-lg-6 mb-3">
        <label class="form-label">Telefon <span class="text-danger">*</span></label>
        <input type="text" name="phone" value="{{ $user['phone'] }}"
               class="form-control" maxlength="50">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="form-label">Qo‘shimcha telefon</label>
        <input type="text" name="phone2" value="{{ $user['phone2'] }}"
               class="form-control" maxlength="50">
    </div>
</div>

{{-- Region, District, Tug‘ilgan sana --}}
<div class="row">
    <div class="col-lg-4 mb-3">
        <label class="form-label">Viloyat</label>
        <select name="region_id" id="region" class="form-select">
            <option value="">Tanlang</option>
            @foreach ($region as $r)
                <option value="{{ $r['id'] }}"
                    {{ $user['region_id'] == $r['id'] ? 'selected' : '' }}>
                    {{ $r['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tuman</label>
        <select name="district_id" id="district" class="form-select">
            <option value="">Tanlang</option>
            @foreach ($districts ?? [] as $d)
                <option value="{{ $d['id'] }}"
                    {{ $user['district_id'] == $d['id'] ? 'selected' : '' }}>
                    {{ $d['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 mb-3">
        <label class="form-label">Tug‘ilgan sana</label>
        <input type="date" name="birthdate" value="{{ $user['birthdate'] }}"
               class="form-control" placeholder="YYYY-MM-DD">
    </div>
</div>

{{-- Avatar va boshqa ma’lumotlar --}}
<div class="row">
    <div class="mb-3 col-lg-4">
        <label class="form-label">Avatar</label>
        <div class="mb-2">
            @if (!empty($user['avatar']))
                <img src="{{ $user['avatar'] }}" alt="Foydalanuvchi avatori"
                     id="user-avatar-preview" class="img-thumbnail"
                     style="max-width: 120px; height: 120px;">
            @else
                <img src="{{ asset('adminpanel/assets/images/user.jpg') }}" alt="Default avatar"
                     class="img-thumbnail" style="max-width: 120px; height: 120px;"
                     id="user-avatar-preview">
            @endif
        </div>
        <input type="file" name="image" class="form-control"
               accept=".jpg,.jpeg,.png,.gif">
        <div class="form-text text-muted">
            Ruxsat etilgan formatlar: gif, png, jpg. Maksimal hajm: 2Mb
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row">
            <div class="col-lg-6 mb-3">
                <label class="form-label">Jinsi</label>
                <select name="gender" class="form-select">
                    <option value="">Tanlanmagan</option>
                    <option value="M" {{ $user['gender'] === 'M' ? 'selected' : '' }}>Erkak</option>
                    <option value="F" {{ $user['gender'] === 'F' ? 'selected' : '' }}>Ayol</option>
                    <option value="U" {{ $user['gender'] === 'U' ? 'selected' : '' }}>Noma’lum</option>
                </select>
            </div>
            <div class="mb-3 col-lg-6">
                <label class="form-label">Roli</label>
                <select multiple="multiple" data-placeholder="Tanlangan rollar"
                        class="form-control form-control-select2-icons" disabled>
                    @foreach ($allRoles as $role)
                        <option value="{{ $role['name'] }}"
                            {{ in_array($role['name'], $roles) ? 'selected' : '' }}>
                            {{ ucfirst($role['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-3">
                <label class="form-label">Chat ID</label>
                <input type="text" name="chat_id" readonly value="{{ $user['chat_id'] }}"
                       class="form-control" maxlength="50">
            </div>
            <div class="col-lg-6 mt-2 d-flex align-items-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_guest"
                           name="is_guest" {{ $user['is_guest'] ? 'checked' : '' }} disabled>
                    <label class="form-check-label ms-2" for="is_guest">
                        <i class="ph ph-user-circle"></i> Mehmon foydalanuvchi
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

                        {{-- Submit tugma --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Saqlash</button>
                        </div>
                    </form>
                </div>
            </div>
{{-- User Devices Card --}}
{{-- User Devices Table --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Foydalanuvchi qurilmalari</h5>
    </div>
    <div class="card-body p-0">
        @if (!empty($devices) && count($devices))
            <div class="table-responsive">
                <table class="table table-dark table-sm table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Qurilma nomi</th>
                            <th>Turi</th>
                            <th>Telefon</th>
                            <th>IP manzil</th>
                            <th>Guest</th>
                            <th>So‘nggi aktivlik</th>
                            <th>Qo‘shilgan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $index => $device)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $device['device_name'] ?? 'Nomaʼlum qurilma' }}</td>
                                <td>{{ ucfirst($device['device_type']) }}</td>
                                <td>{{ $device['phone'] ?? '-' }}</td>
                                <td>{{ $device['ip_address'] ?? '-' }}</td>
                                <td>{{ $device['is_guest'] ? 'Ha' : 'Yo‘q' }}</td>
                                <td>
                                    {{ $device['last_activity']
                                        ? \Carbon\Carbon::createFromTimestamp($device['last_activity'])->format('d.m.Y H:i')
                                        : '-' }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($device['created_at'])->format('d.m.Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-secondary text-center mb-0">
                Qurilmalar topilmadi
            </div>
        @endif
    </div>
</div>
        </div>
    </div>
@endsection
