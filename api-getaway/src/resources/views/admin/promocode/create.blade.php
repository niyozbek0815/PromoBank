@extends('admin.layouts.app')
@section('title', 'Promocode yaratish')
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
            const promotioId="{$promotion_id}";
            const url = "{{ route('admin.promocode.generatedata', $promotion_id, false) }}";
            if ($.fn.DataTable.isDataTable('#generate-table')) {
                $('#generate-table').DataTable().destroy();
            }

            $('#generate-table').DataTable({
                processing: true,
                serverSide: false,
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
                        name: 'name',
                        searchable: false,
                    },
                    {
                        data: 'count',
                        name: 'count',
                        searchable: false,

                    },

                    {
                        data: 'used_count',
                        name: 'used_count',
                        searchable: false,

                    },
                    {
                        data: 'type',
                        name: 'type',
                        searchable: false,

                    },

                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false,

                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        searchable: false,

                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,

                    },
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
                        filename: promotioId + '-idli promotion promocodelari',
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: promotioId + '-idli promotion promocodelari',
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
    </script>
@endpush

@php
    use Illuminate\Support\Facades\Session;
    $buttonClass = 'btn w-100 d-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 shadow-sm border-0 transition-all';
    $userId = Session::get('user')['id'] ?? null;
@endphp

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card border shadow-sm rounded-3">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-semibold">Promocode boshqaruvi</h5>
                    <p class="text-muted mb-0 small">Yangi kodlar yaratish yoki mavjud kodlarni import qilish</p>
                </div>

                <div class="card-body">
                    <div class="row">
                        {{-- 🟢 Manual Promocode --}}
                        <div class="col-md-4 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" action="{{ route('admin.promocode.store', $promotion_id) }}">
                                    @csrf
                                    <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

                                    <h6 class="fw-bold mb-3">Promocode qo‘lda yaratish</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Promocodeni kiriting</label>
                                        <input type="text" name="promocode" class="form-control" placeholder="Masalan: E12334D" required>
                                        <small class="text-muted">Yaratishdan oldin sozlamalarni tekshiring.</small>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <a href="{{ route('admin.promocode.settings.form', ['promotion' => $promotion_id]) }}"
                                           class="{{ $buttonClass }} btn-outline-primary">
                                           <i class="ph ph-gear-six"></i> Sozlamalar
                                        </a>
                                        <button type="submit" name="action" value="generate"
                                                class="{{ $buttonClass }} btn-primary">
                                            <i class="ph ph-lightning"></i> Generatsiya qilish
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- 🟣 Auto Generation --}}
                        <div class="col-md-4 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" action="{{ route('admin.promocode.generate', $promotion_id) }}">
                                    @csrf
                                    <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

                                    <h6 class="fw-bold mb-3">Avtomatik generatsiya</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Promocodelar soni</label>
                                        <input type="number" name="count" class="form-control" min="1" max="10000" placeholder="Masalan: 500" required>
                                        <small class="text-muted">Maksimal 10 000 ta kod yaratish mumkin.</small>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <a href="{{ route('admin.promocode.settings.form', ['promotion' => $promotion_id]) }}"
                                           class="{{ $buttonClass }} btn-outline-primary">
                                           <i class="ph ph-gear-six"></i> Sozlamalar
                                        </a>
                                        <button type="submit" name="action" value="generate"
                                                class="{{ $buttonClass }} btn-primary">
                                            <i class="ph ph-lightning"></i> Generatsiya qilish
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- 🟡 Excel Import --}}
                        <div class="col-md-4 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" action="{{ route('admin.promocode.import', $promotion_id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

                                    <h6 class="fw-bold mb-3">Excel fayldan import</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Excel fayl (.xlsx yoki .csv)</label>
                                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                                        <small class="text-muted">
                                            Fayl faqat <strong>1 ustun</strong> (faqat kodlar)dan iborat bo‘lishi kerak, maksimal 10 000 qator.
                                        </small>
                                    </div>

                                    <div class="form-check my-3">
                                        <input type="checkbox" class="form-check-input" name="settings_rules" id="settings_rules" value="1">
                                        <label class="form-check-label" for="settings_rules">
                                            Generatsiya shartlarini qo‘llash
                                            <a href="{{ route('admin.promocode.settings.form', ['promotion' => $promotion_id]) }}"
                                               class="ms-2 text-primary text-decoration-underline" target="_blank">
                                               Sozlamalarni ko‘rish
                                            </a>
                                        </label>
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

            {{-- 📊 Jadval --}}
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Promocodelar yartish tarixi jadvali</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3">
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
