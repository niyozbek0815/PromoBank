@extends('admin.layouts.app')

@section('title', 'Notifications')

@push('scripts')
    {{-- === DataTables kutubxonasi === --}}
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#notifications_table')) {
                $('#notifications_table').DataTable().destroy();
            }

            $('#notifications_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ secure_url(route('admin.notifications.data', [], false)) }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'text', name: 'text' },
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                      { data: 'link_type', name: 'link_type' },
                    { data: 'link', name: 'link' },
                    { data: 'recipients', name: 'recipients', orderable: false, searchable: false },
                    { data: 'platforms', name: 'platforms' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'scheduled_at', name: 'scheduled_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 20,
                responsive: true,

            });
        });

        // Delete notification
        $(document).on('click', '.delete-user', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal notificationni o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/admin/notifications/${id}/delete`, {_token: $('meta[name="csrf-token"]').attr('content')})
                        .done(res => {
                            toastr.success(res.message || 'O‘chirildi!');
                            $('#notifications_table').DataTable().ajax.reload(null, false);
                        })
                        .fail(() => toastr.error('O‘chirishda xatolik yuz berdi!'));
                }
            });
        });

        // Re-send notification
        $(document).on('click', '.resent-notification', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.post(`/admin/notifications/${id}/resent`, {_token: $('meta[name="csrf-token"]').attr('content')})
                .done(res => toastr.success(res.message || 'Qayta yuborildi!'))
                .fail(() => toastr.error('Yuborishda xatolik yuz berdi!'));
        });
    </script>
@endpush

@section('content')
    <div class="page-header-content d-flex justify-content-between align-items-center">
        <h4 class="page-title mb-0">Notifications</h4>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="ph-plus me-1"></i> Yangi qo‘shish
        </a>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <table id="notifications_table" class="table datatable-button-init-basic w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sarlavha</th>
                        <th>Matn</th>
                        <th>Rasm</th>
                        <th>Link Type</th>
                        <th>Link</th>
                        {{-- <th>Kimlarga yuborilayabdi</th> --}}
                        <th>Recipients</th>
                        <th>Platforms</th>
                        <th>Status</th>
                        <th>Rejalashtirilgan</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
