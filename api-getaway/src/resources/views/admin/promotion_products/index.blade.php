@extends('admin.layouts.app')
@section('title', 'Promoaksiya mahsulotlari')
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
            const url ="{{ route('admin.promotion_products.data', [], false) }}"; // serverdan malumot olish

            if ($.fn.DataTable.isDataTable('#promotion-products-table')) {
                $('#promotion-products-table').DataTable().destroy();
            }

            $('#promotion-products-table').DataTable({
                processing: true,
                serverSide: true, // Chunki shop bo‘yicha filterlangan data keladi
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
                        data: 'promotion_name',
                        name: 'promotion_name'
                    },
                    {
                        data: 'shop_name',
                        name: 'shop_name'
                    },
                    {
                        data: 'status_label',
                        name: 'status_label',
                        searchable: false
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
                        filename: 'promotion_products_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_products_list',
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
                    <h5 class="mb-0">Bannerlar jadvali(Mobile)</h5>
                </div>
                <div class="card-body">
                    <table id="promotion-products-table" class="table datatable-button-init-basic">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mahsulot nomi</th>
                                <th>Aksiya</th>
                                <th>Do‘kon</th>
                                <th>Holat</th>
                                <th>Yaratilgan</th>
                                <th>Amallar</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
