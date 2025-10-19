@extends('admin.layouts.app')

@section('title', content: 'Loyiha haqida')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Loyiha haqida</h5>
            <a href="{{ route('admin.abouts.edit') }}" class="btn btn-primary">
                Tahrirlash
            </a>
        </div>

        <div class="card-body row align-items-center">
            {{-- Left: Image --}}
            <div class="col-lg-5 mb-4 text-center">
                @if (!empty($about['image']))
                    <img src="{{ asset($about['image']) }}" alt="About Image" class="img-fluid rounded shadow-sm">
                @else
                    <div class="bg-light border rounded p-5">
                        <span class="text-muted">Rasm mavjud emas</span>
                    </div>
                @endif
            </div>

            {{-- Right: Text content --}}
            <div class="col-lg-7">
                {{-- Subtitle --}}
                <div class="row">
                    <h4 class="text-secondary col-3 mb-3">
                        🇺🇿 {{ $about['subtitle']['uz'] ?? '' }}
                    </h4>
                    <h4 class="text-secondary col-3 mb-3">
                        🇷🇺 {{ $about['subtitle']['ru'] ?? '' }}
                    </h4>
                    <h4 class="text-secondary col-3 mb-3">
                        🇰🇷 {{ $about['subtitle']['kr'] ?? '' }}
                    </h4>
                    <h4 class="text-secondary col-3 mb-3">
                        🇬🇧 {{ $about['subtitle']['en'] ?? '' }}
                    </h4>
                </div>

                {{-- Title --}}
                <h2 class="fw-bold mb-3">
                    🇺🇿 {{ $about['title']['uz'] ?? '' }} <br>
                    🇷🇺 {{ $about['title']['ru'] ?? '' }} <br>
                    🇰🇷 {{ $about['title']['kr'] ?? '' }} <br>
                    🇬🇧 {{ $about['title']['en'] ?? '' }}
                </h2>

                {{-- Description --}}
                <p class="mb-4">
                    🇺🇿 {{ $about['description']['uz'] ?? '' }} <br>
                    🇷🇺 {{ $about['description']['ru'] ?? '' }} <br>
                    🇰🇷 {{ $about['description']['kr'] ?? '' }} <br>
                    🇬🇧 {{ $about['description']['en'] ?? '' }}
                </p>
                <div class="row">
                    @if (!empty($about['list']))
                        @foreach (['uz' => '🇺🇿', 'ru' => '🇷🇺', 'kr' => '🇰🇷', 'en' => '🇬🇧'] as $lang => $flag)
                            <div class="col-md-3 mb-3">
                                <h6 class="fw-bold mb-2">{{ $flag }}</h6>
                                <ul class="list-unstyled ms-2">
                                    @foreach ($about['list'][$lang] ?? [] as $item)
                                        <li>- {{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
