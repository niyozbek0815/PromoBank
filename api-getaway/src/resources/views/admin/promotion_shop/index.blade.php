@extends('admin.layouts.app')
@section('title', "Do'konlar")
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
            const url = "{{ secure_url(route('admin.promotion_shops.data', [], false)) }}";
            if ($.fn.DataTable.isDataTable('#promotion-shops-table')) {
                $('#promotion-shops-table').DataTable().destroy();
            }

            $('#promotion-shops-table').DataTable({
                processing: true,
                serverSide: false, // chunki biz to'liq malumotni olishimiz mumkin
                ajax: {
                    url: url,
                    type: "GET",
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
                        data: 'adress',
                        name: 'adress'
                    },
                    {
                        data: 'products_count',
                        name: 'products_count',
                        searchable: false
                    },
                    {
                        data: 'promotion_name',
                        name: 'promotion_name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
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
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: 'promotion_shops_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_shops_list',
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
        });  </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Do'konlarlar jadvali</h5>
                </div>
                <div class="card-body">
                            <table id="promotion-shops-table" class="table datatable-button-init-basic">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Do‘kon nomi</th>
                                        <th>Manzil</th>
                                        <th>Mahsulotlar soni</th>
                                        <th>Aksiya nomi</th>
                                        <th>Yaratilgan vaqti</th>
                                        <th>Amallar</th>
                                    </tr>
                                </thead>
                            </table>
                </div>
            </div>
        </div>

    </div>
@endsection
