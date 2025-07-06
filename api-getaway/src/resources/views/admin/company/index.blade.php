@extends('admin.layouts.app')

@section('title', 'Companies')

@push('scripts')
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/js/vendor/tables/datatables/datatables.min.js">
    </script>
    <script
        src="https://themes.kopyov.com/limitless/demo/template/assets/js/vendor/tables/datatables/extensions/buttons.min.js">
    </script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/pages/datatables_extension_buttons_init.js">
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
                serverSide: true,
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

        // Delete and status change handlers (o'zgartirmasdan qoldirdim)
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();
            let form = this;
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
                    form.submit();
                }
            });
        });
        $(document).on('click', '.delete-company', function(e) {
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

@section('content')
    <div class="page-header-content d-lg-flex">
        <div class="d-flex">
            <h4 class="page-title mb-0">Companies {{ app()->getLocale() }}</h4>
        </div>
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
