@extends('admin.layouts.app')

@section('title', 'ONTV Voucherlar')

@push('scripts')
<script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
<script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
<script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#voucher-table')) {
        $('#voucher-table').DataTable().destroy();
    }

    $('#voucher-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/admin/1/data',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { data: 'user', name: 'user', orderable: false, searchable: false },
            { data: 'assigned', name: 'assigned', orderable: false, searchable: false },
            { data: 'used', name: 'used', orderable: false, searchable: false },
            { data: 'valid', name: 'valid', orderable: false, searchable: false },
            { data: 'expires_at', name: 'expires_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        buttons: [
            { extend: 'copy', exportOptions: { modifier: { page: 'all' } } },
            { extend: 'excel', filename: "ONTv Voucherlar", exportOptions: { modifier: { page: 'all' } } },
            { extend: 'csv', filename: "ONTv Voucherlar", exportOptions: { modifier: { page: 'all' } } },
            { extend: 'print', exportOptions: { modifier: { page: 'all' } } },
        ],
    });

    // Voucher delete
    $(document).on('click', '#voucher-table .delete-user', function(e) {
        e.preventDefault();
        const voucherId = $(this).data('id');

        Swal.fire({
            title: 'Ishonchingiz komilmi?',
            text: "Bu amal voucherni o‘chiradi!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ha, o‘chir!',
            cancelButtonText: 'Bekor qilish'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/voucher/' + voucherId + '/delete',
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        toastr.success(res.message || 'Voucher o‘chirildi!');
                        $('#voucher-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('O‘chirishda xatolik yuz berdi!');
                    }
                });
            }
        });
    });
});
</script>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="page-title mb-0">ONTV Voucherlar</h4>
        <a href="{{ route('admin.bot.ontv.create') }}" class="btn btn-primary ms-3">
            <i class="ph-plus-circle me-1"></i> Yangi voucher qo‘shish
        </a>
    </div>

    <div class="card-body">
        <table id="voucher-table" class="table datatable-button-init-basic">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kod</th>
                    <th>Foydalanuvchi</th>
                    <th>Berilgan</th>
                    <th>Ishlatilgan</th>
                    <th>Amalda</th>
                    <th>Muddati</th>
                    <th>Harakatlar</th>
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
