@extends('admin.layouts.app')
@section('title', 'Promocode yaratish')


@php
    use Illuminate\Support\Facades\Session;
    $buttonClass = 'btn w-100 d-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 shadow-sm border-0 transition-all';
    $userId = Session::get('user')['id'] ?? null;
@endphp

@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">
            <div class="card border shadow-sm rounded-3">
                         <div class="card-header border-bottom">
                <h5 class="mb-0 fw-semibold">Voucher boshqaruvi</h5>
                <p class="text-muted mb-0 small">Yangi voucher yaratish yoki mavjud voucherlarni import qilish</p>
            </div>

                <div class="card-body">
                    <div class="row">
                        {{-- 🟢 Manual Promocode --}}
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" action="{{ route('admin.bot.ontv.store', ) }}">
                                    @csrf
                                  <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

                                <h6 class="fw-bold mb-3">Voucher qo‘lda yaratish</h6>

                                <div class="mb-3">
                                    <label class="form-label">Voucher kodi</label>
                                    <input type="text" name="voucher_code" class="form-control" placeholder="Masalan: V1234X" required>
                                </div>

                                <div class="d-flex flex-column gap-2 mt-3">
                                    <button type="submit" class="{{ $buttonClass }} btn-primary">
                                        <i class="ph ph-lightning"></i> Yaratish
                                    </button>
                                </div>
                                </form>
                            </div>
                        </div>



                        {{-- 🟡 Excel Import --}}
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 d-flex flex-column justify-content-between">
                                <form method="POST" action="{{ route('admin.bot.ontv.import') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="created_by_user_id" value="{{ $userId }}">

                                    <h6 class="fw-bold mb-3">Excel fayldan import</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Excel fayl (.xlsx yoki .csv)</label>
                                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                                        <small class="text-muted">
                                        Fayl faqat 1 ustun <strong>vaucher ustuni</strong> (faqat voucher kodlar)dan iborat bo‘lishi kerak.
                                        </small>
                                    </div>


                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <button type="submit" class="{{ $buttonClass }} btn-success">
                                            <i class="ph ph-upload-simple"></i> Exceldan yuklash
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
