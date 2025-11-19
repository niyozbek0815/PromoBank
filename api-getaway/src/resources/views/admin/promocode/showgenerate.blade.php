@extends('admin.layouts.app')
@section('title', 'Promocode')
@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

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
            let generateId = "{{ $generate_id }}";
            const url = "{{ route('admin.promocode.generate.promocodedata', $generate_id, false) }}";
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
                        name: 'promo_codes.id'
                    },
                    {
                        data: 'promocode',
                        name: 'promo_codes.promocode'
                    },
                    {
                        data: 'is_used',
                        name: 'promo_codes.is_used'
                    },
                    {
                        data: 'used_at',
                        name: 'promo_codes.used_at',
                    },
                    {
                        data: 'generation_name',
                        name: 'promo_codes.generation_id',
                    },
                    {
                        data: 'platform',
                        name: 'platform_name',
                        searchable: false

                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
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
                        filename: generateId + '-idli generatsiya promocodelar',
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: generateId + '-idli generatsiya promocodelar',
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
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            @php
                $user = Session::get('user');
            @endphp
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Promocodelar qo'shish tarixi</h5>
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
                                    <th>Promocode</th>
                                    <th>Foydalanilgan</th>
                                    <th>Foydalanilgan vaqti</th>
                                    <th>Generation</th>
                                    <th>Platforma</th>
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
