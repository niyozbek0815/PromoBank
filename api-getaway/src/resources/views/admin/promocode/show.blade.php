@extends('admin.layouts.app')
@section('title', 'Promocode tafsilotlari')

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
                                <span class="text-muted">🎯 Aksiya nomi:</span>
                                <span class="fw-semibold">{{ $promocode['promotion_name'] ?? '—' }}</span>
                            </li>

                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">🧩 Avlod turi:</span>
                                <span class="fw-semibold text-capitalize">{{ $promocode['generation_type'] }}</span>
                            </li>

                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">📅 Yaratilgan sana:</span>
                                <span>{{ \Carbon\Carbon::parse($promocode['created_at'])->format('d.m.Y H:i') }}</span>
                            </li>

                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">♻️ Holati:</span>
                                @if ($promocode['is_used'])
                                    <span class="badge bg-danger">Foydalanilgan</span>
                                @else
                                    <span class="badge bg-success">Yangi</span>
                                @endif
                            </li>

                            @if ($promocode['is_used'])
                                <li class="d-flex justify-content-between border-bottom py-2">
                                    <span class="text-muted">🕒 Foydalanilgan vaqti:</span>
                                    <span>{{ \Carbon\Carbon::parse($promocode['used_at'])->format('d.m.Y H:i') }}</span>
                                </li>
                            @endif

                            <li class="d-flex justify-content-between pt-2">
                                <span class="text-muted">🆔 ID:</span>
                                <span>#{{ $promocode['id'] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- 🔹 O‘ng — Collapse paneli --}}
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-semibold text-primary mb-0">Promocode ma'lumotlari
                            </h4>

                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-success collapse-toggler active"
                                    data-target="#collapse-promocode">
                                    <i class="ph ph-ticket me-1"></i> Harakatlar
                                </button>

                                <button type="button" class="btn btn-outline-success collapse-toggler"
                                    data-target="#collapse-prize">
                                    <i class="ph ph-gift me-1"></i> Sovg‘alar
                                </button>
                            </div>
                        </div>

                        {{-- 🔹 Collapse 1 — Harakatlar --}}
                        <div class="collapse table-panel show" id="collapse-promocode">
                            <div class="border rounded p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-semibold mb-0">🎟️ Promocode harakatlari</h5>
                                    <a href="#" class="btn btn-outline-success">
                                        <i class="ph-plus-circle me-1"></i> Yangi harakat qo‘shish
                                    </a>
                                </div>

                                <table id="promocode-table"
                                    class="table table-bordered table-striped table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Harakat turi</th>
                                            <th>Amal sanasi</th>
                                            <th>Foydalanuvchi</th>
                                            <th>Izoh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- DataTables orqali to‘ldiriladi --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 🔹 Collapse 2 — Sovg‘alar --}}
                        <div class="collapse table-panel" id="collapse-prize">
                            <div class="border rounded p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-semibold mb-0">🎁 Promocode bilan bog‘liq sovg‘alar</h5>
                                    <a href="#" class="btn btn-outline-success">
                                        <i class="ph-plus-circle me-1"></i> Yangi sovg‘a
                                    </a>
                                </div>

                                <table id="prizes-table"
                                    class="table table-bordered table-striped table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#ID</th>
                                            <th>Kategoriya</th>
                                            <th>Sovg‘a nomi</th>
                                            <th>Berilgan</th>
                                            <th>Sana</th>
                                            <th>Amallar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- DataTables orqali to‘ldiriladi --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const togglers = document.querySelectorAll('.collapse-toggler');

    togglers.forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();

            const targetSelector = btn.dataset.target;
            const targetEl = document.querySelector(targetSelector);
            const currentInstance = bootstrap.Collapse.getOrCreateInstance(targetEl);

            // 🔹 Boshqa barcha collapselarni yopamiz
            document.querySelectorAll('.collapse.table-panel').forEach(collapseEl => {
                if (collapseEl !== targetEl) {
                    const otherInstance = bootstrap.Collapse.getOrCreateInstance(collapseEl);
                    otherInstance.hide();
                }
            });

            // 🔹 Barcha togglerlardan active holatini olib tashlaymiz
            togglers.forEach(b => b.classList.remove('active'));

            // 🔹 Agar bosilgan collapse allaqachon ochiq bo‘lsa — yopamiz
            if (targetEl.classList.contains('show')) {
                currentInstance.hide();
            } else {
                currentInstance.show();
                btn.classList.add('active');
            }
        });
    });
});
</script>
@endpush
