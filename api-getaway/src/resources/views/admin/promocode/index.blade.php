@extends('admin.layouts.app')
@section('title', 'Promoacode')
@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            const url = "{{ secure_url(route('admin.promocode.data', [], false)) }}";
            if ($.fn.DataTable.isDataTable('#promocode-table')) {
                $('#promocode-table').DataTable().destroy();
            }

            $('#promocode-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: url,
                    type: "GET",
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
                        searchable: false
                    },
                    {
                        data: 'promotion_name',
                        name: 'promotion_name',
                        searchable: true
                    },
                    {
                        data: 'generation_name',
                        name: 'promo_codes.generation_id',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
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
                        filename: 'promocodelari', // dinamik nom
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promocodelari',
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Promocodelar jadvali</h5>
                </div>
                <div class="card-body">
                    <table id="promocode-table" class="table datatable-button-init-basic">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Promocode</th>
                                <th>Foydalanilgan</th>
                                <th>Foydalanilgan vaqti</th>
                                <th>Promotion</th>
                                <th>Generation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
