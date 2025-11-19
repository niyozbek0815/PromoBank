@extends('admin.layouts.app')

@section('title', "Bog'lanish malumotlari ro'yxati")

@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            const url = "{{ route('admin.contacts.data', [], false) }}";

            if ($.fn.DataTable.isDataTable('#contacts-table')) {
                $('#contacts-table').DataTable().destroy();
            }

            const table = $('#contacts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url, type: "GET" },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'type', name: 'type' },
                    { data: 'url', name: 'url' },
                    { data: 'label', name: 'label' },
                    { data: 'position', name: 'position' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                order: [[4, 'asc']], // default: position
                buttons: [
                    { extend: 'copy', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'excel', filename: 'contacts_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'csv', filename: 'contacts_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'print', exportOptions: { modifier: { page: 'all' } } }
                ]
            });

            // 🗑 O‘chirish
            $(document).on('click', '#contacts-table .delete-user', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Ishonchingiz komilmi?',
                    text: "Bu amal kontakt yozuvini o‘chiradi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ha, o‘chir!',
                    cancelButtonText: 'Bekor qilish'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/contacts/' + id + '/delete',
                            method: 'POST',
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                toastr.success(res.success || 'Kontakt o‘chirildi!');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                toastr.error('O‘chirishda xatolik yuz berdi!');
                            }
                        });
                    }
                });
            });

            // 🔄 Status o‘zgartirish
            $(document).on('click', '#contacts-table .change-status', function(e) {
                e.preventDefault();
                let $this = $(this);
                let id = $this.data('id');
                let status = $this.data('status');
                let url = $this.data('url') || '/admin/contacts/' + id + '/status';

                $.ajax({
                    url,
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content'), status },
                    success: function(res) {
                        toastr.success(res.message || 'Status yangilandi!');
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        toastr.error('Statusni o‘zgartirishda xatolik yuz berdi!');
                    }
                });
            });
        });
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-0">Bog'lanish malumotlari jadvali</h4>
            <a href="{{ route('admin.contacts.create') }}" class="btn btn-primary">
                <i class="ph-plus me-1"></i> Qo‘shish
            </a>
        </div>
        <div class="card-body">
            <table id="contacts-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Turi</th>
                        <th>URL</th>
                        <th>Label (uz)</th>
                        <th>Tartib</th>
                        <th>Status</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Xatoliklarni ko‘rsatish --}}
    @if (session('error'))
        <script>
            $(function() { toastr.error(@json(session('error'))); });
        </script>
    @endif
@endsection
