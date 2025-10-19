@extends('admin.layouts.app')

@section('title', $download['title']['uz'] ?? 'Yuklab olish')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"> Yuklab olish malumotlari</h5>
              <a href="{{ route('admin.downloads.edit') }}" class="btn btn-primary">
                </i>Tahrirlash
            </a>
        </div>
        <div class="card-body row align-items-center">

            {{-- Left: Image --}}
            <div class="col-lg-6 mb-4 text-center">
                @if (!empty($download['image']))
                    <img src="{{ asset($download['image']) }}" alt="Download Image" class="img-fluid rounded shadow-sm">
                @else
                    <div class="bg-light border rounded p-5">
                        <span class="text-muted">Rasm mavjud emas</span>
                    </div>
                @endif
            </div>

            {{-- Right: Text content --}}
            <div class="col-lg-6">
                         {{-- Subtitle --}}
         <h4 class="text-secondary mb-3">
    🇺🇿 {{ $download['subtitle']['uz'] ?? '' }} <br>
    🇷🇺 {{ $download['subtitle']['ru'] ?? '' }} <br>
    🇺🇿 {{ $download['subtitle']['kr'] ?? '' }} <br>
    🇬🇧 {{ $download['subtitle']['en'] ?? '' }}
</h4>

{{-- Title --}}
<h1 class="fw-bold mb-3">
    🇺🇿 {{ $download['title']['uz'] ?? '' }} <br>
    🇷🇺 {{ $download['title']['ru'] ?? '' }} <br>
    🇺🇿 {{ $download['title']['kr'] ?? '' }} <br>
    🇬🇧 {{ $download['title']['en'] ?? '' }}
</h1>

{{-- Description --}}
<p class="mb-4">
    🇺🇿 {{ $download['description']['uz'] ?? '' }} <br>
    🇷🇺 {{ $download['description']['ru'] ?? '' }} <br>
    🇺🇿 {{ $download['description']['kr'] ?? '' }} <br>
    🇬🇧 {{ $download['description']['en'] ?? '' }}
</p>

{{-- Links --}}
<div class="d-flex flex-wrap gap-2">
    @foreach ($download['links'] ?? [] as $link)
        @if (!empty($link['url']))
            <a href="{{ $link['url'] }}" target="_blank" class="btn btn-outline-primary">
                {{ $link['label']['uz'] ?? $link['label']['en'] ?? ucfirst($link['type']) }}
            </a>
        @endif
    @endforeach
</div>
            </div>
        </div>
    </div>


@endsection
