@extends('admin.layouts.app')
@section('title', 'Promocode sozlamalari')
@push('scripts')
    <script src="{{secure_asset('adminpanel/assets/js/select2.min.js') }}"></script>
    <script src="{{secure_asset('adminpanel/assets/js/form_layouts.js') }}"></script>
    <script>
        $(document).on('change', 'input[name="logo"]', function(evt) {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#company-logo-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        $(document).on('change', '#status', function() {
            let checked = $(this).is(':checked');
            let label = $('#status-label');
            if (checked) {
                label.html('<i class="ph ph-check-circle text-success"></i> Faol');
            } else {
                label.html('<i class="ph ph-x-circle text-danger"></i> Faol emas');
            }
        });
    </script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            @php
                $user = Session::get('user');
                // dd($settings);
            @endphp
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Promocode sozlamalari</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promocode.settings.update', $promotion_id) }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            {{-- Promocode uzunligi --}}
                            <div class="col-md-4">
                                <label class="form-label">Promocode uzunligi</label>
                                <input type="number" name="length" class="form-control"
                                    value="{{ old('length', $settings['length'] ?? 8) }}" placeholder="Masalan: 8">
                                <div class="form-text">
                                    Kodda nechta belgidan iborat bo‘lishini tanlang (masalan: 8).
                                </div>
                            </div>

                            {{-- Belgilar to‘plami --}}
                            <div class="col-md-4">
                                <label class="form-label">Belgilar to‘plami</label>
                                <input type="text" name="charset" id="charset" class="form-control"
                                    value="{{ old('charset', $settings['charset'] ?? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890') }}"
                                    placeholder="Masalan: ABCDEFG123456">
                                <div class="form-text">
                                    Promocodlar qanday belgilar orqali yaratiladi? Harflar va raqamlar yozing.<br>
                                    Masalan: <code>ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789</code>
                                </div>
                            </div>

                            {{-- Istisno belgilar --}}
                            <div class="col-md-4">
                                <label class="form-label">Istisno belgilar</label>
                                <input type="text" name="exclude_chars" class="form-control"
                                    value="{{ old('exclude_chars', $settings['exclude_chars'] ?? '') }}">
                                <div class="form-text">
                                    Qanday belgilar coddan chiqarib tashlansin?<br>
                                    Masalan: <code>O0Il</code> (0 va O, 1 va l bir-biriga o‘xshashligi uchun)
                                </div>
                            </div>

                            {{-- Prefix (Kod boshida bo‘ladigan qism) --}}
                            <div class="col-md-6">
                                <label class="form-label">Prefix</label>
                                <input type="text" name="prefix" class="form-control"
                                    value="{{ old('prefix', $settings['prefix'] ?? '') }}" placeholder="Masalan: PROMO">
                                <div class="form-text">
                                    Promocod boshiga avtomatik qo‘shiladigan qism.<br>
                                    Masalan: <code>SUMMER</code> → <code>SUMMER-8H5K93</code>
                                </div>
                            </div>

                            {{-- Suffix (Kod oxirida bo‘ladigan qism) --}}
                            <div class="col-md-6">
                                <label class="form-label">Suffix</label>
                                <input type="text" name="suffix" id="suffix" class="form-control"
                                    value="{{ old('suffix', isset($settings) && $settings['suffix'] !== null ? $settings['suffix'] : '') }}"
                                    placeholder="Masalan: 2025">
                                <div class="form-text">
                                    Promocod oxiriga avtomatik qo‘shiladigan qism.<br>
                                    Masalan: <code>-UZ</code> → <code>8H5K93-UZ</code>
                                </div>
                            </div>

                            {{-- Barcha aksiyalar orasida unique bo‘lishi --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_unique" id="is_unique" class="form-check-input"
                                        {{ old('is_unique', $settings['s_unique'] ?? true) ? 'checked' : '' }}> <label
                                        class="form-check-label" for="unique_across_all_promotions">
                                        Barcha aksiyalar orasida unique bo‘lsinmi?
                                    </label>
                                    <div class="form-text">
                                        Agar belgilansa, ushbu promocodlar boshqa aksiyalardagi codlar bilan
                                        takrorlanmasligi kafolatlanadi.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                            <a href="
                            @if (isset($settings)) {{ route('admin.promocode.create', $promotion_id) }}
                            @else
                                                        {{ route('admin.promotion.edit', $promotion_id) }} @endif
                             "
                                class="btn btn-outline-secondary">
                                <i class="ph-arrow-circle-left me-1"></i> Orqaga
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-paper-plane-tilt me-1"></i> Saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
