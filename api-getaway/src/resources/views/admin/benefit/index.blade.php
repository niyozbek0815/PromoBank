@extends('admin.layouts.app')

@section('title', "Foydalanuvchilar uchun afzalliklar ro'yhati")

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            const url = "{{ route('admin.benefits.data', [], false) }}";

            if ($.fn.DataTable.isDataTable('#benefits-table')) {
                $('#benefits-table').DataTable().destroy();
            }

            $('#benefits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    type: "GET",
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'description', name: 'description' },
                    { data: 'image', name: "image" },
                    { data: 'position', name: 'position' },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [[4, 'asc']], // default: position
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'excel',
                        filename: 'benefits_list',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'csv',
                        filename: 'benefits_list',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'print',
                        exportOptions: { modifier: { page: 'all' } }
                    }
                ]
            });

            // 🗑 O‘chirish
            $(document).on('click', '#benefits-table .delete-user', function(e) {
                e.preventDefault();
                const benefitId = $(this).data('id');
                Swal.fire({
                    title: 'Ishonchingiz komilmi?',
                    text: "Bu amal foydani o‘chiradi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ha, o‘chir!',
                    cancelButtonText: 'Bekor qilish'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/benefits/' + benefitId + '/delete',
                            method: 'POST',
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                toastr.success(res.message || 'Benefit o‘chirildi!');
                                $('#benefits-table').DataTable().ajax.reload(null, false);
                            },
                            error: function() {
                                toastr.error('O‘chirishda xatolik yuz berdi!');
                            }
                        });
                    }
                });
            });

            // 🔄 Status o‘zgartirish
            $(document).on('click', '#benefits-table .change-status', function(e) {
                e.preventDefault();

                let $this = $(this);
                let benefitId = $this.data('id');
                let status = $this.data('status');
                let url = $this.data('url') || '/admin/benefits/' + benefitId + '/status';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: status
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Status yangilandi!');
                        $('#benefits-table').DataTable().ajax.reload(null, false);

                        let newStatus = status == 1 ? 0 : 1;
                        $this.data('status', newStatus);
                        $this.find('i').toggleClass('ph-toggle-left ph-toggle-right');
                        $this.text(newStatus == 1 ? 'Nofaol qilish' : 'Faollashtirish');
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
            <h4 class="mb-0">Foydalar jadvali</h4>
            <a href="{{ route('admin.benefits.create') }}" class="btn btn-primary">
                <i class="ph-plus me-1"></i> Yangi foyda qo‘shish
            </a>
        </div>
        <div class="card-body">
            <table id="benefits-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sarlavha</th>
                        <th>Ta'rif</th>
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
            $(function() {
                toastr.error(@json(session('error')));
            });
        </script>
    @endif
@endsection
