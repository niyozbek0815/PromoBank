@extends('admin.layouts.app')

@section('title', 'Companies')

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
            if ($.fn.DataTable.isDataTable('#companies-table')) {
                $('#companies-table').DataTable().destroy();
            }
            $('#companies-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/company/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
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
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'region',
                        name: 'region'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
            });
        });
        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let status = $this.data('status');

            $.ajax({
                url: '/admin/company/' + userId + '/status',
                method: 'POST',
                data: {
                    status: status
                },
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#companies-table').DataTable().ajax.reload(null, false);

                    // Toggle status and text/icon
                    let newStatus = status == 1 ? 0 : 1;
                    $this.data('status', newStatus);
                    if (status == 1) {
                        $this.find('i').removeClass('ph-toggle-left').addClass('ph-toggle-right');
                        $this.text('Nofaol qilish');
                    } else {
                        $this.find('i').removeClass('ph-toggle-right').addClass('ph-toggle-left');
                        $this.text('Faollashtirish');
                    }
                },
                error: function() {
                    toastr.error('Statusni o‘zgartirishda xatolik yuz berdi!');
                }
            });
        });
        $(document).on('click', '.delete-user', function(e) {
            e.preventDefault();
            const companyId = $(this).data('id');
            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal kompaniyani o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/company/' + companyId + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            toastr.success(res.message || 'Kompaniya o‘chirildi!');
                            $('#companies-table').DataTable().ajax.reload(null, false);
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
@section('header-actions')
    <a href="{{ route('admin.company.create') }}" class="btn btn-primary ms-3">
        <i class="ph-plus-circle me-1"></i> Yangi Kompaniya
    </a>
@endsection
@section('content')
    <div class="page-header-content d-flex justify-content-between align-items-center">
        <h4 class="page-title mb-0">Companies</h4>
    </div>
    @php($locale = app()->getLocale())
    <div class="card">
        <div class="card-body">
            <table id="companies-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Region</th>
                        <th>Address</th>
                        <th class="text-center">Actions</th>
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
