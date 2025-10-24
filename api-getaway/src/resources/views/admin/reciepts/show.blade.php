@extends('admin.layouts.app')
@section('title', 'Chek tafsilotlari')
@php
    use Carbon\Carbon;

    $receipt = $data['receipt'] ?? [];
    $products = $receipt['products'] ?? [];
    $promoCodes = $data['promo_codes'] ?? [];
    $actions = $data['actions'] ?? [];
        $fields = [
        'ID' => isset($receipt['id']) ? '#' . $receipt['id'] : '—',
        'Chek ID' => $receipt['chek_id'] ?? '—',
        'Do‘kon' => $receipt['name'] ?? '—',
        'Manzil' => $receipt['address'] ?? '—',
        'NKM raqami' => $receipt['nkm_number'] ?? '—',
        'SN' => $receipt['sn'] ?? '—',
        'Chek sanasi' => isset($receipt['check_date'])
            ? optional(Carbon::parse($receipt['check_date']))->format('d.m.Y H:i')
            : '—',
        'Summa' => number_format((float)($receipt['summa'] ?? 0), 0, '.', ' ') . ' so‘m',
        'QQS' => number_format((float)($receipt['qqs_summa'] ?? 0), 2, '.', ' '),
        'Latitude' => $receipt['lat'] ?? '—',
        'Longitude' => $receipt['long'] ?? '—',
        'To‘lov turi' => $receipt['payment_type'] ?? '—',
        'Yaratilgan' => isset($receipt['created_at'])
            ? optional(Carbon::parse($receipt['created_at']))->format('d.m.Y H:i')
            : '—',
        'Yangilangan' => isset($receipt['updated_at'])
            ? optional(Carbon::parse($receipt['updated_at']))->format('d.m.Y H:i')
            : '—',
        'Manual kodlar' => '<span class="badge bg-secondary">'
            . ((int)($receipt['manual_count'] ?? 0))
            . '</span>',
        'Sovrinli kodlar' => '<span class="badge bg-success">'
            . ((int)($receipt['prize_count'] ?? 0))
            . '</span>',
        'Foydalanuvchi ID' => $receipt['user_id'] ?? '—',
        'Foydalanuvchi ismi' => $receipt['user_cache']['name'] ?? '—',
        'Telefon' => $receipt['user_cache']['phone'] ?? '—',
    ];
@endphp

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mx-auto">
            <div class="card-body pt-4">
                <div class="row g-4 align-items-start">

                    {{-- 🔹 Chap panel — Chek tafsilotlari --}}
                <div class="col-lg-4">
    <div class="border rounded shadow-sm p-3 mb-4" style="font-size: 0.9rem;">
        <h5 class="fw-bold text-center mb-3">🧾 Chek tafsilotlari</h5>

        <ul class="list-unstyled mb-3">
            @foreach ($fields as $label => $value)
                <li class="d-flex justify-content-between py-1 border-bottom">
                    {!! "<span class='text-muted'>$label:</span> $value" !!}
                </li>
            @endforeach
        </ul>

        <h5 class="fw-semibold mb-2 text-center">🛒 Mahsulotlar</h5>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Soni</th>
                        <th>Summasi</th>
                        <th>Qo‘shilgan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p['name'] ?? '—' }}</td>
                            <td>{{ $p['count'] ?? '—' }}</td>
                            <td>{{ number_format((float)($p['summa'] ?? 0), 0, '.', ' ') }}</td>
                            <td>
                                {{ isset($p['created_at'])
                                    ? optional(Carbon::parse($p['created_at']))->format('d.m.Y H:i')
                                    : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Mahsulot topilmadi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


                    {{-- 🔹 O‘ng panel — Mahsulotlar va Aksiya ma’lumotlari --}}
                    <div class="col-lg-8 col-md-7 col-12">
                        {{-- Mahsulotlar --}}


                    <div class="border rounded p-3 shadow-sm mb-4">
    <h5 class="fw-semibold mb-3">🎁 Sovrinlar / Promo kodlar</h5>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sovg‘a nomi</th>
                    <th>Aksiya nomi</th>
                    <th>Platforma</th>
                    <th>Mahsulot</th>
                    <th>Foydalanuvchi ID</th>
                    <th>Yaratilgan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promoCodes as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p['prize_name'] ?? '—' }}</td>
                        <td>{{ $p['promotion_name'] ?? '—' }}</td>
                        <td>{{ $p['platform_name'] ?? '—' }}</td>
                        <td>{{ $p['product_name'] ?? '—' }}</td>
                        <td>{{ $p['user_id'] ?? '—' }}</td>
                        <td>
                            {{ $p['created_at'] ? \Carbon\Carbon::parse($p['created_at'])->format('d.m.Y H:i') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Promo kod topilmadi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

                        <div class="border rounded p-3 shadow-sm">
                            <h5 class="fw-semibold mb-3">📜 Amalga oshirilgan harakatlar</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Harakat</th>
                    <th>Status</th>
                    <th>Xabar</th>
                    <th>Foydalanuvchi ID</th>
                    <th>Sovg‘a nomi</th>
                    <th>Aksiya nomi</th>
                    <th>Platforma</th>
                    <th>Do‘kon</th>
                    <th>Vaqt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actions as $a)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                     <td>
    {{ ucfirst(str_replace('_', ' ', $a['action'] ?? '—')) }}
</td>
<td>
        {{ ucfirst(str_replace('_', ' ', $a['status'] ?? '—')) }}

</td>
                        <td>{{ $a['message'] ?? '—' }}</td>
                        <td>{{ $a['user'] ?? '—' }}</td>
                        <td>{{ $a['prize_name'] ?? '—' }}</td>
                        <td>{{ $a['promotion_name'] ?? '—' }}</td>
                        <td>{{ $a['platform_name'] ?? '—' }}</td>
                        <td>{{ $a['shop_name'] ?? '—' }}</td>
                        <td>
                            {{ $a['created_at'] ? \Carbon\Carbon::parse($a['created_at'])->format('d.m.Y H:i') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            Hech qanday amal topilmadi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 🔹 Footer --}}
            <div class="card-footer border-0 bg-transparent d-flex justify-content-center gap-3 py-4">
                {{-- <a href="{{ route('admin.sales-receipts.index') }}" class="btn btn-outline-secondary px-4">
                <i class="ph-arrow-left me-2"></i> Orqaga
            </a> --}}
                <button class="btn btn-primary px-4" onclick="window.print()">
                    <i class="ph-printer me-2"></i> Chop etish
                </button>
            </div>
        </div>
    </div>
@endsection
