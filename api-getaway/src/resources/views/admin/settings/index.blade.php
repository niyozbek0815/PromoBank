@extends('admin.layouts.app')

@section('title', "Umumiy sozlamalar")

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Umumiy sozlamalar</h5>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-primary ">
                </i>Tahrirlash
            </a>
        </div>
        <div class="card-body">

            <div class="row">
                {{-- Navbar Logo --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Navbar logotipi</h6>
                    <div class="border rounded p-3 bg-light">
                        @if(!empty($settings['navbar_logo']))
                            <img src="{{ asset($settings['navbar_logo']) }}" alt="Navbar logotipi" class="img-thumbnail" height="60">
                        @else
                            <span class="text-muted">Logotip yuklanmagan</span>
                        @endif
                    </div>
                </div>

                {{-- Footer Logo --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Footer logotipi</h6>
                    <div class="border rounded p-3 bg-light">
                        @if(!empty($settings['footer_logo']))
                            <img src="{{ asset($settings['footer_logo']) }}" alt="Footer logotipi" class="img-thumbnail" height="60">
                        @else
                            <span class="text-muted">Logotip yuklanmagan</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Hero Title --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Asosiy sarlavha (Hero Title)</h6>
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-1">🇺🇿 {{ $settings['hero_title']['uz'] ?? 'O‘zbekcha matn kiritilmagan' }}</p>
                        <p class="mb-1">🇷🇺 {{ $settings['hero_title']['ru'] ?? 'Ruscha matn kiritilmagan' }}</p>
                        <p class="mb-0">🇺🇿 {{ $settings['hero_title']['kr'] ?? 'Krillcha matn kiritilmagan' }}</p>
                    </div>
                </div>

                {{-- Footer Description --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Footer tavsifi</h6>
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-1">🇺🇿 {{ $settings['footer_description']['uz'] ?? 'O‘zbekcha tavsif kiritilmagan' }}</p>
                        <p class="mb-1">🇷🇺 {{ $settings['footer_description']['ru'] ?? 'Ruscha tavsif kiritilmagan' }}</p>
                        <p class="mb-0">🇺🇿 {{ $settings['footer_description']['kr'] ?? 'Krillcha tavsif kiritilmagan' }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Footer Bottom --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Footer pastki qismi</h6>
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-0">
                            🇺🇿 {{ $settings['footer_bottom']['uz'] ?? 'Matn kiritilmagan' }} <br>
                            🇷🇺 {{ $settings['footer_bottom']['ru'] ?? 'Текст не задан' }} <br>
                            🇺🇿 {{ $settings['footer_bottom']['kr'] ?? 'Матн киритилмаган' }}
                        </p>
                    </div>
                </div>

                {{-- Languages --}}
                <div class="col-6 mb-4">
                    <h6 class="fw-bold text-secondary">Tillar</h6>
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-1">
                            Mavjud tillar: {{ implode(', ', $settings['languages']['available'] ?? []) }}
                        </p>
                        <p class="mb-0">
                            Asosiy til: <span class="badge bg-primary">{{ $settings['languages']['default'] ?? 'Belgilanmagan' }}</span>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
