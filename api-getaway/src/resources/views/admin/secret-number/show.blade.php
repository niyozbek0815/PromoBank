@extends('admin.layouts.app')
@section('title', 'Sirli raqam tafsilotlari')

@php
    use Carbon\Carbon;

    $secret = $data ?? [];
    // dd($secret);
    $entries = $secret['entries'] ?? [];
    $actions = $secret['actions'] ?? [];

    $fields = [
        'ID' => $secret['id'] ?? '—',
        'Promo aksiya' => $secret['promotion']['name_uz'] ?? '—',
        'Sirli raqam' => $secret['number'] ?? '—',
        'Ball' => $secret['points'] ?? '0',
        'Faollashgan vaqti' => isset($secret['start_at'])
            ? Carbon::parse($secret['start_at'])->format('d.m.Y H:i')
            : '—',
        'Yaratilgan' => isset($secret['created_at'])
            ? Carbon::parse($secret['created_at'])->format('d.m.Y H:i')
            : '—',
        'Yangilangan' => isset($secret['updated_at'])
            ? Carbon::parse($secret['updated_at'])->format('d.m.Y H:i')
            : '—',
        'Entries soni' => count($entries),
    ];
@endphp

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mx-auto">
        <div class="card-body pt-4">
            <div class="row g-4">

                {{-- Chap panel --}}
                <div class="col-lg-4">
                    <div class="border rounded shadow-sm p-3 mb-4" style="font-size: 0.9rem;">
                        <h5 class="fw-bold text-center mb-3">🧾 Sirli raqam tafsilotlari</h5>
                        <ul class="list-unstyled mb-3">
                            @foreach ($fields as $label => $value)
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    {!! "<span class='text-muted'>$label:</span> $value" !!}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- O‘ng panel --}}
                <div class="col-lg-8 col-md-7 col-12">

                    {{-- Entries --}}
                    <div class="border rounded p-3 shadow-sm mb-4">
                        <h5 class="fw-semibold mb-3">📥 Foydalanuvchi Entries</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Foydalanuvchi</th>
                                        <th>Foydalanuvchi ID</th>
                                        <th>Yuborilgan raqam</th>
                                        <th>Berilgan ball</th>
                                        <th>Qabul qilingan</th>
                                        <th>Yaratilgan</th>
                                        <th>Yangilangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($entries as $entry)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $entry['user_name'] ?? 'Noma\'lum' }}</td>
                                            <td>{{ $entry['user_id'] ?? '—' }}</td>
                                            <td>{{ $entry['user_input'] ?? '—' }}</td>
                                            <td>{{ $entry['points_awarded'] ?? 0 }}</td>
                                            <td>{{ $entry['is_accepted'] ? 'Ha' : 'Yo‘q' }}</td>
                                            <td>{{ isset($entry['created_at']) ? Carbon::parse($entry['created_at'])->format('d.m.Y H:i') : '—' }}</td>
                                            <td>{{ isset($entry['updated_at']) ? Carbon::parse($entry['updated_at'])->format('d.m.Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Entries topilmadi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if(!empty($actions))
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
                                            <td>{{ ucfirst(str_replace('_', ' ', $a['action'] ?? '—')) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $a['status'] ?? '—')) }}</td>
                                            <td>{{ $a['message'] ?? '—' }}</td>
                                            <td>{{ $a['user'] ?? '—' }}</td>
                                            <td>{{ $a['prize_name'] ?? '—' }}</td>
                                            <td>{{ $a['promotion_name'] ?? '—' }}</td>
                                            <td>{{ $a['platform_name'] ?? '—' }}</td>
                                            <td>{{ $a['shop_name'] ?? '—' }}</td>
                                            <td>{{ isset($a['created_at']) ? Carbon::parse($a['created_at'])->format('d.m.Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-3">Hech qanday amal topilmadi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer border-0 bg-transparent d-flex justify-content-center gap-3 py-4">
            <button class="btn btn-primary px-4" onclick="window.print()">
                <i class="ph-printer me-2"></i> Chop etish
            </button>
        </div>
    </div>
</div>
@endsection
