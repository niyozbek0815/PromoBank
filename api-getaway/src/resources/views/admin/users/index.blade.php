@extends('admin.layouts.app')

@section('title', 'Users')

@push('scripts')
    <!-- Toastr CSS -->
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
            if ($.fn.DataTable.isDataTable('#users-table')) {
                $('#users-table').DataTable().destroy();
            }
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,

                ajax: '/admin/users/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'phone2',
                        name: 'phone2'
                    },
                    {
                        data: 'chat_id',
                        name: 'chat_id'
                    },
                    {
                        data: 'gender',
                        name: 'gender'

                    },
                    {
                        data: 'birthdate',
                        name: 'birthdate'
                    },

                    {
                        data: 'is_guest',
                        name: 'is_guest',
                        render: function(data) {
                            return data ? 'Ha' : 'Yo‘q';
                        }
                    },
                    {
                        data: 'region',
                        name: 'region'
                    },
                    {
                        data: 'district',
                        name: 'district'
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: false
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
                    }
                ],
                // language: {
                //     "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/uz.json"
                // }
            });
        });
        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let userId = $this.data('id');
            let status = $this.data('status');

            $.ajax({
                url: '/admin/users/' + userId + '/status',
                method: 'POST',
                data: {
                    status: status
                },
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#users-table').DataTable().ajax.reload(null, false);

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
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal foydalanuvchini o‘chiradi!",
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
        $(document).on('click', '.delete-user', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');

            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Bu amal foydalanuvchini o‘chiradi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ha, o‘chir!',
                cancelButtonText: 'Bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/users/' + userId + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            toastr.success(res.message || 'Foydalanuvchi o‘chirildi!');
                            $('#users-table').DataTable().ajax.reload(null, false);
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
            <h4 class="page-title mb-0">Users</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="users-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Qo‘shimcha tel</th>
                        <th>Chat ID</th>
                        <th>Jinsi</th>
                        <th>Tug‘ilgan sana</th>
                        <th>Mehmonmi</th>
                        <th>Viloyat</th>
                        <th>Tuman</th>
                        <th>Rollar</th>
                        <th>Status</th>
                        <th class="text-center">Amallar</th>
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
