@extends('admin.layouts.app')

@section('title', "Portfolio ro'yhati")

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            const url = "{{ route('admin.portfolio.data', [], false) }}";

            if ($.fn.DataTable.isDataTable('#portfolio-table')) {
                $('#portfolio-table').DataTable().destroy();
            }

            const table = $('#portfolio-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url, type: "GET" },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'subtitle', name: 'subtitle' },
                    { data: 'body', name: 'body' },
                    { data: 'image', name: 'image' },
                    { data: 'position', name: 'position' },
                    { data: 'featured', name: 'featured', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                order: [[5, 'asc']], // default: position
                buttons: [
                    { extend: 'copy', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'excel', filename: 'portfolio_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'csv', filename: 'portfolio_list', exportOptions: { modifier: { page: 'all' } } },
                    { extend: 'print', exportOptions: { modifier: { page: 'all' } } }
                ]
            });

            // 🗑 O‘chirish
            $(document).on('click', '#portfolio-table .delete-user', function(e) {
                e.preventDefault();
                const portfolioId = $(this).data('id');
                Swal.fire({
                    title: 'Ishonchingiz komilmi?',
                    text: "Bu amal portfolio yozuvini o‘chiradi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ha, o‘chir!',
                    cancelButtonText: 'Bekor qilish'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/portfolio/' + portfolioId + '/delete',
                            method: 'POST',
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                toastr.success(res.message || 'Portfolio o‘chirildi!');
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
            $(document).on('click', '#portfolio-table .change-status', function(e) {
                e.preventDefault();
                let $this = $(this);
                let portfolioId = $this.data('id');
                let status = $this.data('status');
                let url = $this.data('url') || '/admin/portfolio/' + portfolioId + '/status';

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
            <h4 class="mb-0">Portfolio jadvali</h4>
            <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary">
                <i class="ph-plus me-1"></i> Yangi portfolio qo‘shish
            </a>
        </div>
        <div class="card-body">
            <table id="portfolio-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sarlavha</th>
                        <th>Qisqa sarlavha</th>
                        <th>Matn</th>
                        <th>Rasm</th>
                        <th>Tartib</th>
                        <th>Featured</th>
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
