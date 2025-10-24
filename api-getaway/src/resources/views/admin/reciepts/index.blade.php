@extends('admin.layouts.app')

@section('title', 'Check skaneri')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
         $(document).ready(function() {
            const url = "/admin/sales-receipts/data";

            if ($.fn.DataTable.isDataTable('#receipts-table')) {
                $('#receipts-table').DataTable().destroy();
            }

            $('#receipts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    type: "GET",
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'chek_id',
                        name: 'chek_id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                     {
                        data: 'address',
                        address: 'address'
                    },
                    {
                        data: 'nkm_number',
                        name: 'nkm_number'
                    },
                    {
                        data: 'sn',
                        name: 'sn'
                    },

                    {
                        data: 'payment_type',
                        name: 'payment_type'
                    },
                    {
                        data: 'qqs_summa',
                        name: 'qqs_summa',
                        searchable: false
                    },
                    {
                        data: 'summa',
                        name: 'summa',
                        searchable: false
                    },
                    {
                        data: 'lat',
                        name: 'lat',
                        searchable: false
                    },
                    {
                        data: 'long',
                        name: 'long',
                        searchable: false
                    },
                           { data: 'user_info', name: 'user_info', orderable: false, searchable: true }, // yangi ustun


                    {
                        data: 'manual_count',
                        name: 'manual_count',
                        searchable: false
                    },
                    {
                        data: 'prize_count',
                        name: 'prize_count',
                        searchable: false
                    },
                       {
                        data: 'check_date',
                        name: 'check_date',
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
                    }
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
                        filename: 'promotion_receipts_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_receipts_list',
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


    @php($locale = app()->getLocale())
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Cheklar jadvali</h4>
        </div>
        <div class="card-body">
            <table id="receipts-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Chek ID</th>
                        <th>Do‘kon nomi</th>
                        <th>Manzil</th>
                        <th>NKM raqami</th>
                        <th>SN</th>
                        <th>To‘lov turi</th>
                        <th>QQS summa</th>
                        <th>Umumiy summa</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Foydalanuvchi</th>
                        <th>Manual count</th>
                        <th>Prize count</th>
                        <th>Chek sanasi</th>
                        <th>Yaratilgan</th>
                        <th>Amallar</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>
    @if (session('error'))
        <script>
            $(function() {
                toastr.error(@json(session('error')));
            });
        </script>
    @endif
@endsection
