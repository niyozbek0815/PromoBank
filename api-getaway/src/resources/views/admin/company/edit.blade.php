@extends('admin.layouts.app')

@section('title', 'Company edit')

@push('scripts')
    @php
        $social_types = $data['select_types'];
        $data = $data['data'];
    @endphp
    <script src="{{ asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
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
        document.addEventListener('DOMContentLoaded', function() {
            const allPanels = document.querySelectorAll('.table-panel');
            let currentlyOpen = document.querySelector('#collapse-promos');

            // Sahifa yuklanganda collapse-promos ni ko‘rsatamiz va unga tegishli tugmani active qilamiz
            if (currentlyOpen) {
                const defaultInstance = bootstrap.Collapse.getOrCreateInstance(currentlyOpen);
                defaultInstance.show();

                // Default aktiv tugma topiladi va unga active qo‘yiladi
                document.querySelectorAll('.collapse-toggler').forEach(btn => {
                    const targetId = btn.getAttribute('data-target');
                    if (targetId === '#collapse-promos') {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            // Collapse togglerlar uchun event
            document.querySelectorAll('.collapse-toggler').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const target = document.querySelector(targetId);

                    // Boshqa panel ochiq bo‘lsa, yopiladi
                    if (currentlyOpen && currentlyOpen !== target) {
                        const currentInstance = bootstrap.Collapse.getOrCreateInstance(
                            currentlyOpen);
                        currentInstance.hide();
                    }

                    const targetInstance = bootstrap.Collapse.getOrCreateInstance(target);

                    if (!target.classList.contains('show')) {
                        targetInstance.show();
                        currentlyOpen = target;

                        // Barcha tugmalardan active olib tashlaymiz
                        document.querySelectorAll('.collapse-toggler').forEach(btn => btn.classList
                            .remove('active'));
                        this.classList.add('active');
                    } else {
                        targetInstance.hide();
                        currentlyOpen = null;
                        this.classList.remove('active');
                    }
                });
            });
        });
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        const companyId = $('#company-id').val();
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#social-table')) {
                $('#social-table').DataTable().destroy();
            }
            const companyId = $('#company-id').val(); // Yoki: $('#company-data').data('id')

            $('#social-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/socialcompany/' + companyId + '/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },

                    {
                        data: 'url',
                        name: 'url'
                    },

                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
            });
        });

        $('#socialMediaForm').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = form.serialize();

            $.ajax({
                url: '/admin/socialcompany',
                method: 'POST',
                data: formData,
                success: function(res) {
                    $('#socialMediaModal').modal('hide');
                    toastr.success('Ijtimoiy tarmoq saqlandi');
                    $('#social-table').DataTable().ajax.reload(null, false);
                    form[0].reset();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Saqlashda xatolik yuz berdi!';
                    toastr.error(msg);
                }
            });
        });
        $(document).on('click', '#social-table .delete-user', function(e) {
            e.preventDefault();
            const companyId = $(this).data('id');
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal social linkni o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/socialcompany/' + companyId + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            toastr.success(res.message || 'social link o‘chirildi!');
                            $('#social-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            toastr.error('O‘chirishda xatolik yuz berdi!');
                        }
                    });
                }
            });
        });

        const socialTypes = @json($social_types); // PHPdagi $social_types ni JavaScriptga uzatamiz

        $(document).ready(function() {
            const $select = $('#social-type-id');
            $select.empty(); // Eski optionlarni tozalaymiz
            $select.append('<option value="">Tanlang...</option>'); // Default option

            socialTypes.forEach(function(type) {
                $select.append(`<option value="${type.id}">${type.name}</option>`);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#promo-table')) {
                $('#promo-table').DataTable().destroy();
            }
            const companyId = $('#company-id').val(); // Yoki: $('#company-data').data('id')
            $('#promo-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/promotion/' + companyId + '/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_public',
                        name: 'is_public',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                buttons: [{
                        extend: 'copy',
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- all sahifalarni oladi
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: companyId + '-idli company promoaksiyalari',
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: companyId + '-idli company promoaksiyalari',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ]
            });
        });
        // 1 marta ro'yxatdan o'tgan click handler
        $(document).on('click', '#promo-table .change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let status = $this.data('status');
            let url = $this.data('url') || '/admin/promotion/' + userId + '/status';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    status: status
                },
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#promo-table').DataTable().ajax.reload(null, false);

                    let newStatus = status == 1 ? 0 : 1;
                    $this.data('status', newStatus);
                    $this.find('i').toggleClass('ph-toggle-left ph-toggle-right');
                    $this.text(newStatus == 1 ? 'Nofaol qilish' : 'Faollashtirish');
                },
                error: function() {
                    toastr.error('Statusni o‘zgartirishda xatolik yuz berdi!');
                }
            });
        });
        $(document).on('click', '#promo-table .change-public', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let isPublic = $this.data('public'); // <-- BU YERNI TO‘G‘RILADIK
            let url = $this.data('url') || '/admin/promotion/' + userId + '/public';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    is_public: isPublic
                },
                success: function(res) {
                    toastr.success(res.message || 'Ommaviylik yangilandi!');
                    $('#promo-table').DataTable().ajax.reload(null, false);

                    // Status toggle
                    let newPublic = isPublic == 1 ? 0 : 1;
                    $this.data('public', newPublic);

                    $this.find('i')
                        .toggleClass('ph-eye ph-eye-slash');

                    $this.text(newPublic == 1 ? 'Maxfiy qilish' : 'Ommaviy qilish');
                },
                error: function() {
                    toastr.error('Ommaviylikni o‘zgartirishda xatolik yuz berdi!');
                }
            });
        });
        $(document).on('click', '#promo-table .delete-user', function(e) {
            e.preventDefault();
            const companyId = $(this).data('id');
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal promoaksiyani o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/promotion/' + companyId + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            toastr.success(res.message || 'promoaksiya o‘chirildi!');
                            $('#promo-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            toastr.error('O‘chirishda xatolik yuz berdi!');
                        }
                    });
                }
            });
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company edit</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.company.update', $data['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Nomi ({{ $label }})</label>
                                    <input type="text" name="name[{{ $lang }}]"
                                        value="{{ $data['name'][$lang] ?? '' }}" class="form-control" required
                                        maxlength="255">
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="company-id" value="{{ $data['id'] }}">
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Sarlavha ({{ $label }})</label>
                                    <input type="text" name="title[{{ $lang }}]"
                                        value="{{ $data['title'][$lang] ?? '' }}" class="form-control" maxlength="255">
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            @foreach (['uz' => 'O‘zbekcha', 'ru' => 'Русский', 'kr' => 'Krillcha'] as $lang => $label)
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label">Tavsif ({{ $label }})</label>
                                    <textarea name="description[{{ $lang }}]" class="form-control" rows="2">{{ $data['description'][$lang] ?? '' }}</textarea>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">Company Logo</label>
                                <div class="mb-2">
                                    @if (!empty($data['logo']))
                                        <img src="{{ $data['logo'] }}" alt="Company logosi" id="company-logo-preview"
                                            class="img-thumbnail" style="max-width: 130px; height: 130px;">
                                    @else
                                        <img src="{{ asset('adminpanel/assets/images/default-logo.png') }}"
                                            alt="Default logo" class="img-thumbnail"
                                            style="max-width: 130px; height: 120px;" id="company-logo-preview">
                                    @endif
                                </div>
                                <input type="file" name="logo" class="form-control">
                                <div class="form-text text-muted">Ruxsat etilgan formatlar: gif, png, jpg. Maksimal hajm:
                                    2Mb</div>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="{{ $data['email'] }}"
                                            class="form-control" maxlength="255" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Hudud</label>
                                        <input type="text" name="region" value="{{ $data['region'] }}"
                                            class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Manzil</label>
                                        <input type="text" name="address" value="{{ $data['address'] }}"
                                            class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Javobgar shaxs</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">Tanlang...</option>
                                            @foreach ($clients['clients'] ?? [] as $client)
                                                <option value="{{ $client['id'] }}"
                                                    {{ $data['user_id'] == $client['id'] ? 'selected' : '' }}>
                                                    {{ $client['name'] }} ({{ $client['email'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mb-3 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status"
                                                value="1" {{ $data['status'] == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label ms-2" for="status" id="status-label">
                                                @if ($data['status'] == '1')
                                                    <i class="ph ph-check-circle text-success"></i> Faol
                                                @else
                                                    <i class="ph ph-x-circle text-danger"></i> Faol emas
                                                @endif
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
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">social link ma'lumotlari</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-social">
                            <i class="ph ph-share-network me-1"></i> Ijtimoiy tarmoqlar
                        </button>
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-promos">
                            <i class="ph ph-ticket me-1"></i> Promoaksiyalar
                        </button>
                        <button type="button" class="btn btn-outline-success collapse-toggler"
                            data-target="#collapse-settings">
                            <i class="ph ph-gear me-1"></i> Sozlamalar
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="collapse table-panel" id="collapse-social">
                        <div class="border rounded p-3">
                            <div class="page-header-content d-flex justify-content-between align-items-center">
                                <h4 class="page-title mb-0">Ijtimoiy Tarmoqlar</h4>
                                <button type="button" class="btn btn-outline-success ms-3" data-bs-toggle="modal"
                                    data-bs-target="#socialMediaModal">
                                    <i class="ph-plus-circle me-1"></i> Create or Update
                                </button>
                            </div>
                            <table id="social-table" class="table datatable-button-init-basic">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Platforma</th>
                                        <th>URL</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>
                    </div>

                    <div class="collapse table-panel" id="collapse-promos">
                        <div class="border rounded p-3">

                            <div class="page-header-content d-flex justify-content-between align-items-center">
                                <h4 class="page-title mb-0">PromoAksiyalar</h4>
                                <a href="{{ route('admin.promotion.create', ['company_id' => $data['id']]) }}"
                                    class="btn btn-outline-success ms-3">
                                    <i class="ph-plus-circle me-1"></i> Yangi promoaksiya
                                </a>
                            </div>
                            <table id="promo-table" class="table datatable-button-init-basic">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nomi</th>
                                        <th>Sarlavha</th>
                                        <th>Tavsif</th>
                                        <th>Faollik</th>
                                        <th>Ommaviylik</th>
                                        <th>Boshlanish</th>
                                        <th>Tugash</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>
                    </div>

                    <div class="collapse table-panel" id="collapse-settings">
                        <div class="border rounded p-3">
                            <h6 class="mb-3">Sozlamalar</h6>
                            <ul class="list-group">
                                <li class="list-group-item">Tillar: uz, ru, kr</li>
                                <li class="list-group-item">Timezone: Asia/Tashkent</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Ijtimoiy tarmoq qo‘shish/yangilash modali -->
        <div class="modal fade" id="socialMediaModal" tabindex="-1" aria-labelledby="socialMediaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('admin.socialcompany.store', $data['id']) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="social-id">
                    <input type="hidden" name="company_id" id="social-company-id" value="{{ $data['id'] }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="socialMediaModalLabel">Ijtimoiy tarmoqni qo'shish yoki saqlash
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Yopish"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Platforma</label>
                                <select id="social-type-id" class="form-select" name="type_id" required>
                                    <option value="">Tanlang...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">URL</label>
                                <input type="url" class="form-control" name="url" id="social-url" required
                                    maxlength="255">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Bekor qilish</button>
                            <button type="submit" class="btn btn-primary">Saqlash</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


    </div>
@endsection
