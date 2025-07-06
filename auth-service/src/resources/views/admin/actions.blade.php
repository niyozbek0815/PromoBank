<div class="d-inline-flex align-items-center">
    <div class="dropdown">
        <a href="#" class="text-body" data-bs-toggle="dropdown">
            <i class="ph-list"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">

            {{-- Tahrirlash --}}
            <a href="/admin/users/{{ $user->id }}/edit" class="dropdown-item">
                <i class="ph-pencil-simple me-2"></i>
                Tahrirlash
            </a>

            {{-- Statusni o‘zgartirish --}}
            <a href="#" class="dropdown-item change-status" data-id="{{ $user->id }}"
                data-status="{{ $user->status ? '0' : '1' }}">
                <i class="ph-toggle-{{ $user->status ? 'left' : 'right' }} me-2"></i>
                {{ $user->status ? 'Nofaol qilish' : 'Faollashtirish' }}
            </a>

            <a href="#" class="dropdown-item text-danger delete-user" data-id="{{ $user->id }}">
                <i class="ph-trash me-2"></i> O‘chirish
            </a>

        </div>
    </div>
</div>
