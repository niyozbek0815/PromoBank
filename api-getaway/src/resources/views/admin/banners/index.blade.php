@extends('admin.layouts.app')

@section('title', 'Banners')

@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#banner-table')) {
                $('#banner-table').DataTable().destroy();
            }

            $('#banner-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/banners/data',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'banner_type', name: 'banner_type' },
                    { data: 'url', name: 'url' },
                    { data: 'media', name: 'media', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                buttons: [
                    { extend: 'copy', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'excel', filename: "Bannerlar", exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'csv', filename: "Bannerlar", exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'print', exportOptions: { modifier: { page: 'all' } } }
                ]
            });
        });

        // 🔹 Statusni o‘zgartirish
        $(document).on('click', '#banner-table .change-status', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: '/admin/banners/' + id + '/status',
                method: 'POST',
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#banner-table').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    toastr.error('Statusni o‘zgartirishda xatolik yuz berdi!');
                }
            });
        });

        // 🔹 Bannerni o‘chirish
        $(document).on('click', '#banner-table .delete-user', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal bannerni o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/banners/' + id + '/delete',
                        method: 'POST',
                        success: function(res) {
                            toastr.success(res.message || 'Banner o‘chirildi!');
                            $('#banner-table').DataTable().ajax.reload(null, false);
                        },
                        error: function() {
                            toastr.error('O‘chirishda xatolik yuz berdi!');
                        }
                    });
                }
            });
        });
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="page-title mb-0">Banners</h4>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary ms-3">
                <i class="ph-plus-circle me-1"></i> Yangi Banner
            </a>
        </div>
        <div class="card-body">
            <table id="banner-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sarlavha</th>
                        <th>Turi</th>
                        <th>URL</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th>Yaratilgan</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
