@extends('admin.layouts.app')
@section('title', "Promoaksiya qo'shish")
@push('scripts')

    <script src="{{secure_asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            const url = "{{ route('admin.promotion_products.promotion_data',$shop['id'], false) }}"; // serverdan malumot olish

            if ($.fn.DataTable.isDataTable('#promotion-products-table')) {
                $('#promotion-products-table').DataTable().destroy();
            }

            $('#promotion-products-table').DataTable({
                processing: true,
                serverSide: true, // Chunki shop bo‘yicha filterlangan data keladi
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
                        data: 'promotion_name',
                        name: 'promotion_name'
                    },
                    {
                        data: 'shop_name',
                        name: 'shop_name'
                    },
                    {
                        data: 'status_label',
                        name: 'status_label',
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
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
                        filename: 'promotion_products_list',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: 'promotion_products_list',
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
    </script>
@endpush

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Xarid cheki skaneri uchun do‘konni tahrirlash</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotion_shops.update', $shop['id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- 🔽 Promotion tanlash --}}
                        <div class="mb-3">
                            <label class="form-label">Promotion</label>

                            {{-- Agar bu do‘kon ma’lum bir promotion’ga biriktirilgan bo‘lsa readonly bo‘lsin --}}
                            @if (!empty($shop['promotion_id']))
                                <select class="form-select" disabled>
                                    @foreach ($promotions as $promotion)
                                        <option value="{{ $promotion['id'] }}"
                                            {{ $shop['promotion_id'] == $promotion['id'] ? 'selected' : '' }}>
                                            {{ $promotion['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="promotion_id" value="{{ $shop['promotion_id'] }}">
                            @else
                                <select name="promotion_id" class="form-select select2-single" required>
                                    <option value="">-- Tanlang --</option>
                                    @foreach ($promotions as $promotion)
                                        <option value="{{ $promotion['id'] }}"
                                            {{ old('promotion_id', $shop['promotion_id']) == $promotion['id'] ? 'selected' : '' }}>
                                            {{ $promotion['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error('promotion_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 🏪 Do‘kon nomi --}}
                        <div class="mb-3">
                            <label class="form-label">Do‘kon nomi</label>
                            <input type="text" class="form-control" name="name" placeholder="Masalan: Mega Market"
                                value="{{ old('name', $shop['name']) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="text-muted">Do‘kon nomi xarid chekida qanday bo‘lsa, shunday kiriting.</small>
                        </div>

                        {{-- 📍 Manzil --}}
                        <div class="mb-3">
                            <label class="form-label">Manzil</label>
                            <textarea class="form-control" name="adress" rows="3"
                                placeholder="Masalan: Toshkent sh., Chilonzor tumani, Qatortol ko‘chasi, 12-uy" required>{{ old('adress', $shop['adress']) }}</textarea>
                            @error('adress')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="text-muted">Manzil xarid chekidagi ma’lumot bilan bir xil bo‘lishi kerak.</small>
                        </div>

                        {{-- 🔘 Submit --}}
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary ms-2">Yangilash</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Promotion o'tqaziladigan filiallar</h4>
                    <div>
                        <a href="{{ route('admin.promotion_products.create', ['shop_id' => $shop['id']]) }}"
                            class="btn btn-outline-success ms-3">
                            <i class="ph-plus-circle me-1"></i> Filial qo'shish
                        </a>
                        {{-- <button type="button" class="btn btn-outline-success ms-3" data-bs-toggle="modal"
                                            data-bs-target="#socialMediaModal">
                                            <i class="ph-plus-circle me-1"></i> Sozlamalar
                                        </button> --}}
                    </div>
                </div>
                <div class="card-body">
                    <table id="promotion-products-table" class="table datatable-button-init-basic">
                        <thead>
                            <tr>
                                       <th>ID</th>
            <th>Mahsulot nomi</th>
            <th>Aksiya</th>
            <th>Do‘kon</th>
            <th>Holat</th>
            <th>Yaratilgan</th>
            <th>Amallar</th>
                            </tr>
                        </thead>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection
