@extends('admin.layouts.app')
@section('title', 'Promocode tafsilotlari')

@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 🔹 Collapse toggling
            const togglers = document.querySelectorAll('.collapse-toggler');
            togglers.forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    const target = document.querySelector(btn.dataset.target);
                    const current = bootstrap.Collapse.getOrCreateInstance(target);

                    document.querySelectorAll('.collapse.table-panel').forEach(el => {
                        if (el !== target) bootstrap.Collapse.getOrCreateInstance(el)
                    .hide();
                    });

                    togglers.forEach(b => b.classList.remove('active'));
                    if (target.classList.contains('show')) current.hide();
                    else {
                        current.show();
                        btn.classList.add('active');
                    }
                });
            });

            // 🔹 Promocode actions data
            const actionsData = @json($promocode['actions'] ?? []);
            if ($.fn.DataTable.isDataTable('#promocode-table')) {
                $('#promocode-table').DataTable().clear().destroy();
            }
            // 🔹 DataTable: barcha ustunlarni chiqaradi
            $('#promocode-table').DataTable({
                data: actionsData,
                processing: true,
                serverSide: false,

                columns: [{
                        data: 'id',
                        title: '#ID'
                    },
                    {
                        data: 'user_id',
                        title: 'User ID'
                    },
                    {
                        data: 'prize_id',
                        title: 'Prize ID',
                        render: val => val ?? '—'
                    },
                    {
                        data: 'platform',
                        title: 'Platform nomi',
                        render: val => val?.name ? `<span class="text-capitalize">${val.name}</span>` :
                            '—'
                    },
                    {
                        data: 'action',
                        title: 'Harakat turi',
                        render: val => val ?? '—'
                    },
                    {
                        data: 'status',
                        title: 'Holat',
                        render: val => val ?? '—'
                    },
                    {
                        data: 'attempt_time',
                        title: 'Vaqt',
                        render: val => val ? new Date(val).toLocaleString('uz-UZ') : '—'
                    },
                    {
                        data: 'message',
                        title: 'Xabar',
                        render: val => val ?? '—'
                    },

                    {
                        data: 'prize',
                        title: 'Sovg‘a ma\'lumoti',
                        render: val => val ? JSON.stringify(val) : '—'
                    },
                ],
                buttons: [{
                        extend: 'copy',
                        text: 'Nusxa olish',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        filename: 'promocode_actions',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        filename: 'promocode_actions',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Chop etish',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mx-auto">
            <div class="card-body pt-4">
                <div class="row g-4 align-items-start">
                    {{-- 🔹 Chap — Promocode tafsiloti --}}
                    <div class="col-lg-4 col-md-5 col-12">
                        <div class="text-center pb-0">
                            <h4 class="fw-semibold text-primary mb-3">
                                <i class="ph ph-ticket me-2"></i> Promocode tafsilotlari
                            </h4>
                        </div>
                 <div class="p-4 border rounded-4 bg-light-subtle shadow-sm h-100 mx-auto" style="max-width: 400px;">
    <h3 class="fw-bold text-center mb-4 text-primary">
        {{ $promocode['promocode'] }}
    </h3>

    <ul class="list-unstyled mb-0 small">
        <li class="d-flex justify-content-between border-bottom py-2">
            <span class="text-muted">ID:</span>
            <span>#{{ $promocode['id'] }}</span>
        </li>

        <li class="d-flex justify-content-between border-bottom py-2">
            <span class="text-muted">Aksiya nomi:</span>
            <span class="fw-semibold">{{ $promocode['promotion_name'] ?? '—' }}</span>
        </li>

        <li class="d-flex justify-content-between border-bottom py-2">
            <span class="text-muted">Yaratilishi:</span>
            <span class="fw-semibold text-capitalize">{{ $promocode['generation_type'] }}</span>
        </li>

        <li class="d-flex justify-content-between border-bottom py-2">
            <span class="text-muted">Yaratilgan:</span>
            <span>{{ \Carbon\Carbon::parse($promocode['created_at'])->format('d.m.Y H:i') }}</span>
        </li>

        <li class="d-flex justify-content-between border-bottom py-2">
            <span class="text-muted">Holati:</span>
            @if ($promocode['is_used'])
                <span class="badge bg-danger">Foydalanilgan</span>
            @else
                <span class="badge bg-success">Yangi</span>
            @endif
        </li>

        @if ($promocode['is_used'])
            <li class="d-flex justify-content-between border-bottom py-2">
                <span class="text-muted">Foydalanilgan vaqti:</span>
                <span>{{ \Carbon\Carbon::parse($promocode['used_at'])->format('d.m.Y H:i') }}</span>
            </li>

            {{-- 🔹 Code Users chiqishi --}}
            @if(!empty($promocode['code_users']))
                <li class="pt-3">
                    <div class="fw-semibold text-primary mb-2">👤 Foydalanuvchi ma’lumotlari:</div>
                    @foreach($promocode['code_users'] as $user)
                        <div class="border rounded-3 p-2 mb-2 shadow-sm">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">User ID:</span>
                                <span class="fw-semibold">#{{ $user['user_id'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Platforma:</span>
                                <span class="fw-semibold">
                                    {{ $user['platform']['name'] ?? '—' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Sovg‘a:</span>
                                <span class="fw-semibold text-success">
                                    {{ $user['prize']['name'] ?? '—' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Sana:</span>
                                <span>
                                    {{ \Carbon\Carbon::parse($user['created_at'])->format('d.m.Y H:i') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </li>
            @endif
        @endif
    </ul>
</div>
                    </div>

                    {{-- 🔹 O‘ng — DataTable --}}
                    <div class="col-lg-8 col-md-7 col-12">
                        <div class="border rounded p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">🎟️ Promocode harakatlari</h5>
                            </div>
                            <table id="promocode-table" class="table datatable-button-init-basic">
                                <thead class="table-light">
                                    <tr>
                                        <th>#ID</th>
                                        <th>User ID</th>
                                        <th>Prize ID</th>
                                        <th>Platform</th>
                                        <th>Harakat turi</th>
                                        <th>Status</th>
                                        <th>Vaqt</th>
                                        <th>Xabar</th>
                                        <th>Sovg‘a ma'lumotlari</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🔹 Footer --}}
            <div class="card-footer border-0 bg-transparent d-flex justify-content-center gap-3 py-4">
                <a href="{{ route('admin.promocode.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="ph-arrow-left me-2"></i> Orqaga
                </a>
                <button class="btn btn-primary px-4" onclick="window.print()">
                    <i class="ph-printer me-2"></i> Chop etish
                </button>
            </div>
        </div>
    </div>
@endsection
