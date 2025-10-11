@extends('admin.layouts.app')

@section('title', 'Homiylar ro‘yxati')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        $(document).ready(function() {
            const url = "{{ route('admin.sponsors.data', [], false) }}"; // serverdan malumot olish
            if ($.fn.DataTable.isDataTable('#sponsors-table')) {
                $('#sponsors-table').DataTable().destroy();
            }

            $('#sponsors-table').DataTable({
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
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'image',
                        name: "image"
                    },
                    {
                        data: 'url',
                        name: 'url'
                    },
                    {
                        data: 'weight',
                        name: 'weight'
                    },
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
                order: [
                    [3, 'asc']
                ], // default: weight
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
                        filename: 'sponsors_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'sponsors_list',
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
            $(document).on('click', '#sponsors-table .delete-user', function(e) {
                e.preventDefault();
                const sponsorId = $(this).data('id');
                Swal.fire({
                    title: 'Ishonchingiz komilmi?',
                    text: "Bu amal homiylarni o‘chiradi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ha, o‘chir!',
                    cancelButtonText: 'Bekor qilish'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/sponsors/' + sponsorId + '/delete',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                toastr.success(res.message ||
                                    'promoaksiya o‘chirildi!');
                                $('#sponsors-table').DataTable().ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                toastr.error('O‘chirishda xatolik yuz berdi!');
                            }
                        });
                    }
                });
            });

        $(document).on('click', '#sponsors-table .change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let status = $this.data('status');
            let url = $this.data('url') || '/admin/sponsors/' + userId + '/status';

$.ajax({
    url: url,
    method: 'POST',
    data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        status: status
    },
    success: function(res) {
        toastr.success(res.message || 'Status yangilandi!');
        $('#sponsors-table').DataTable().ajax.reload(null, false);

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
            <h4 class="mb-0">Homiylar jadvali</h4>
            <a href="{{ route('admin.sponsors.create') }}" class="btn btn-primary">
                <i class="ph-plus me-1"></i> Yangi homiy qo‘shish
            </a>
        </div>
        <div class="card-body">
            <table id="sponsors-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th>Image</th>
                        <th>URL</th>
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
