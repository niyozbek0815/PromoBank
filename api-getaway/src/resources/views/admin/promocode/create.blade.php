@extends('admin.layouts.app')
@section('title', 'Company add')
@push('scripts')
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            const url = "{{ route('admin.promocode.generatedata', $promotion_id, false) }}";
            if ($.fn.DataTable.isDataTable('#generate-table')) {
                $('#generate-table').DataTable().destroy();
            }

            $('#generate-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    type: "GET",
                    dataSrc: function(json) {
                        console.log("Returned data:", json);
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error("DataTable AJAX error", xhr.responseText);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'count',
                        name: 'count'
                    },

                    {
                        data: 'used_count',
                        name: 'used_count'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'actions',
                        name: 'actions'
                    },
                ]
            });
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            @php
                $user = Session::get('user');
            @endphp
            <div class="card border shadow-sm rounded-3">

                <div class="card-header  border-bottom">
                    <h5 class="mb-0 fw-semibold">🎛️ Promocode boshqaruvi</h5>
                    <p class="text-muted mb-0 small">Yangi kodlar yaratish yoki mavjud kodlarni fayldan yuklash</p>
                </div>

                <div class="card-body">
                    <div class="row">
                        @php
                            $buttonClass =
                                'btn w-100 d-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 shadow-sm transition-all border-0';
                        @endphp

                        {{-- ✅ Avtomatik generatsiya formasi --}}
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form action="{{ route('admin.promocode.generate', $promotion_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="created_by_user_id"
                                        value="{{ Session::get('user')['id'] }}">

                                    <h6 class="fw-bold mb-3">⚙️ Avtomatik generatsiya</h6>

                                    <div class="mb-3">
                                        <label class="form-label">🎯 Nechta promocode yaratilsin?</label>
                                        <input type="number" name="count" class="form-control" required min="1"
                                            max="100000" placeholder="Masalan: 500">
                                        <div class="form-text text-muted">Maksimal 100000 ta kod yaratishingiz mumkin.
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">

                                        <a href="{{ route('admin.promocode.settings.form', ['promotion' => $promotion_id]) }}"
                                            name="action" value="settings" class="{{ $buttonClass }} btn-outline-primary">
                                            <i class="ph ph-gear-six"></i> Sozlamalar
                                        </a>
                                        <button type="submit" name="action" value="generate"
                                            class="{{ $buttonClass }} btn-primary">
                                            <i class="ph ph-lightning"></i> Kodlarni generatsiya qilish
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ✅ Excel import formasi --}}
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form action="{{ route('admin.promocode.import', $promotion_id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <h6 class="fw-bold mb-3">📥 Excel fayldan import qilish</h6>
                                    <input type="hidden" name="created_by_user_id"
                                        value="{{ Session::get('user')['id'] }}">

                                    <div class="mb-3">
                                        <label class="form-label">📄 Excel fayl (.xlsx yoki .csv)</label>
                                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv"
                                            required>
                                        <div class="form-text text-muted">
                                            Fayl faqat 1 ustunli bo‘lishi kerak. Ustunda faqat
                                            <strong>promocode</strong> lar bo‘lishi lozim.
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <a href="{{ asset('namuna/promo-default.xlsx') }}"
                                            class="{{ $buttonClass }} btn-outline-success" download>
                                            <i class="ph ph-download-simple"></i> Namuna fayl
                                        </a>
                                        <button type="submit" class="{{ $buttonClass }} btn-success">
                                            <i class="ph ph-upload-simple"></i> Exceldan yuklash
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Promoaksiya ma'lumotlari</h5>
                </div>

                <div class="card-body">
                    <div class="border rounded p-3">
                        <div class="page-header-content d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">PromoCodelar jadvali</h4>
                        </div>
                        <table id="generate-table" class="table datatable-button-init-basic">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Name</th>
                                    <th>Yaratilgan Kodlar</th>
                                    <th>Foydalanilgan</th>
                                    <th>Type</th>
                                    <th>Yaratilgan sana</th>
                                    <th>Yaratgan foydalanuvchi</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
