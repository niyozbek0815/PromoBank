@extends('admin.layouts.app')

@section('title', "Hoomiylar uchun afzallikllar ro'yxati")

@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            const url = "{{ route('admin.forsponsor.data', [], false) }}";

            if ($.fn.DataTable.isDataTable('#forsponsor-table')) {
                $('#forsponsor-table').DataTable().destroy();
            }

            const table = $('#forsponsor-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url, type: "GET" },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'description', name: 'description' },
                    { data: 'image', name: 'image', orderable: false, searchable: false },
                    { data: 'position', name: 'position' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                order: [[4, 'asc']], // default: position
                buttons: [
                    { extend: 'copy', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'excel', filename: 'for_sponsor_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'csv', filename: 'for_sponsor_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'print', exportOptions: { modifier: { page: 'all' } } }
                ]
            });

            // 🗑 O‘chirish
            $(document).on('click', '#forsponsor-table .delete-user', function(e) {
                e.preventDefault();
                const sponsorId = $(this).data('id');
                Swal.fire({
                    title: 'Ishonchingiz komilmi?',
                    text: "Bu amal forSponsor yozuvini o‘chiradi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ha, o‘chir!',
                    cancelButtonText: 'Bekor qilish'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/forsponsor/' + sponsorId + '/delete',
                            method: 'POST',
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                toastr.success(res.success || 'ForSponsor o‘chirildi!');
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
            $(document).on('click', '#forsponsor-table .change-status', function(e) {
                e.preventDefault();
                let $this = $(this);
                let sponsorId = $this.data('id');
                let status = $this.data('status');
                let url = $this.data('url') || '/admin/forsponsor/' + sponsorId + '/status';

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
            <h4 class="mb-0">Homiylar uchun afzalliklar jadvali jadvali</h4>
            <a href="{{ route('admin.forsponsor.create') }}" class="btn btn-primary">
                <i class="ph-plus me-1"></i> Yangi afzallik qo‘shish
            </a>
        </div>
        <div class="card-body">
            <table id="forsponsor-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sarlavha</th>
                        <th>Tavsif</th>
                        <th>Rasm</th>
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
