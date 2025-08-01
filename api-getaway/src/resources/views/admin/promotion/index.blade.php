@extends('admin.layouts.app')

@section('title', 'Promotion')
@section('header-actions')
    <a href="{{ route('admin.promotion.create') }}" class="btn btn-primary ms-3">
        <i class="ph-plus-circle me-1"></i> Yangi promoaksiya
    </a>
@endsection
@push('scripts')
    <!-- Toastr CSS -->
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
            if ($.fn.DataTable.isDataTable('#promo-table')) {
                $('#promo-table').DataTable().destroy();
            }
            $('#promo-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/promotion/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'company_name',
                        name: 'company.name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'platform_names',
                        name: 'platform_names'
                    },
                    {
                        data: 'participant_types',
                        name: 'participant_types'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_public',
                        name: 'is_public',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
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
                                page: 'all' // <-- all sahifalarni oladi
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: "Promoaksiyalar jadvali",
                        exportOptions: {
                            modifier: {
                                page: 'all' // <-- faqat ko‘rinayotgan emas, hammasini oladi
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: "Promoaksiyalar jadvali",
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
        // 1 marta ro'yxatdan o'tgan click handler
        $(document).on('click', '#promo-table .change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let status = $this.data('status');
            let url = $this.data('url') || '/admin/promotion/' + userId + '/status';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    status: status
                },
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#promo-table').DataTable().ajax.reload(null, false);

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
        $(document).on('click', '#promo-table .change-public', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let isPublic = $this.data('public'); // <-- BU YERNI TO‘G‘RILADIK
            let url = $this.data('url') || '/admin/promotion/' + userId + '/public';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    is_public: isPublic
                },
                success: function(res) {
                    toastr.success(res.message || 'Ommaviylik yangilandi!');
                    $('#promo-table').DataTable().ajax.reload(null, false);

                    // Status toggle
                    let newPublic = isPublic == 1 ? 0 : 1;
                    $this.data('public', newPublic);

                    $this.find('i')
                        .toggleClass('ph-eye ph-eye-slash');

                    $this.text(newPublic == 1 ? 'Maxfiy qilish' : 'Ommaviy qilish');
                },
                error: function() {
                    toastr.error('Ommaviylikni o‘zgartirishda xatolik yuz berdi!');
                }
            });
        });
        $(document).on('click', '#promo-table .delete-user', function(e) {
            e.preventDefault();
            const companyId = $(this).data('id');
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal promoaksiyani o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/promotion/' + companyId + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            toastr.success(res.message || 'promoaksiya o‘chirildi!');
                            $('#promo-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            toastr.error('O‘chirishda xatolik yuz berdi!');
                        }
                    });
                }
            });
        });
    </script>
@endpush
@section('content')

    <div class="page-header-content d-lg-flex">
        <div class="d-flex">
            <h4 class="page-title mb-0">Promoaksiyalar</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="promo-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th>Sarlavha</th>
                        <th>Tavsif</th>
                        <th>Kompaniya</th>
                        <th>Platformalar</th>
                        <th>Ishtirok etish turlari</th>
                        <th>Faollik</th>
                        <th>Ommaviylik</th>
                        <th>Boshlanish</th>
                        <th>Tugash</th>
                        <th>Actions</th>
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
