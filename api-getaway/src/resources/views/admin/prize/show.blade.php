@extends('admin.layouts.app')

@section('title', 'Qoʻlda tanlash')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    </script>
    <script>
        @php
            $category = $data['category'];
            $promotion = $data['promotion'];
        @endphp
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#manual_table')) {
                $('#manual_table').DataTable().destroy();
            }
            $('#manual_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '/admin/prize-category/{{ $promotion['id'] }}/type/{{ $category['name'] }}/data',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    @if (!in_array($category['name'], ['weighted_random', 'manual']))
                        {
                            data: 'index',
                            name: 'index'
                        },
                    @endif {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'daily_limit',
                        name: 'daily_limit'
                    },
                    @if ($category['name'] === 'weighted_random')
                        {
                            data: 'awarded_quantity',
                            name: 'awarded_quantity'
                        },
                    @endif {
                        data: 'probability_weight',
                        name: 'probability_weight'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from'
                    },
                    {
                        data: 'valid_until',
                        name: 'valid_until'
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
                ]
            });
        });
        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();

            let $this = $(this);
            let Id = $this.data('id');
            let status = $this.data('status');

            $.ajax({
                url: '/admin/prize/' + Id + '/status',
                method: 'GET',
                data: {
                    status: status
                },
                success: function(res) {
                    toastr.success(res.message || 'Status yangilandi!');
                    $('#manual_table').DataTable().ajax.reload(null, false);

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
                            $('#manual_table').DataTable().ajax.reload(null, false);
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
    <div class="card border-0 shadow-sm mb-3">
        <div
            class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

            {{-- Chap taraf: kategoriya va promoaksiya maʼlumotlari --}}
            <div class="flex-grow-1">
                <h6 class="text-primary fw-semibold mb-1">
                    {{ $category['display_name'] }}
                </h6>
                <p class="text-muted small mb-1">
                    <span class="fw-medium">{{ $promotion['name'] }}</span> aksiyasi uchun
                    <code class="text-lowercase">{{ $category['name'] }}</code> kategoriyasi
                </p>
                <div class="text-body text-muted small">
                    {!! $category['description'] !!}
                </div>
            </div>

            {{-- O‘ng taraf: zamonaviy va teng o‘lchamdagi tugmalar --}}
            <div class="d-flex flex-wrap justify-content-md-end justify-content-start gap-2">
                <a href="{{ route('admin.prize.createByCategory', ['category' => $category['name'], 'promotion' => $promotion['id']]) }}"
                    class="btn btn-outline-primary d-flex align-items-center gap-1 px-3 py-1">
                    <i class="ph-plus-circle fs-6"></i>
                    <span>Generate va Import</span>
                </a>
            </div>

        </div>
    </div>
    <div class="page-header-content d-flex justify-content-between align-items-center">
        <h4 class="page-title mb-0">Prizes</h4>
    </div>
    @php($locale = app()->getLocale())
    <div class="card">
        <div class="card-body">
            <table id="manual_table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th>Tasnifi</th>
                        @if (!in_array($category['name'], ['weighted_random', 'manual']))
                            <th>Sovg'a darajasi</th>
                        @endif
                        <th>Miqdor</th>
                        <th>Kunlik Limit</th>
                        <th>Berilganlar soni</th>
                        @if ($category['name'] === 'weighted_random')
                            <th>Yutish extimolligi</th>
                        @endif
                        <th>Boshlanish</th>
                        <th>Tugash</th>
                        <th>Status</th>
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
