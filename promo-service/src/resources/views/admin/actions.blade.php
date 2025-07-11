<div class="d-inline-flex align-items-center">
    <div class="dropdown">
        <a href="#" class="text-body" data-bs-toggle="dropdown">
            <i class="ph-list"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">

            @if (!empty($routes['edit']))
                <a href="{{ $routes['edit'] }}" class="dropdown-item">
                    <i class="ph-pencil-simple me-2"></i> Tahrirlash
                </a>
            @endif

            @if (!empty($routes['status']))
                <a href="#" class="dropdown-item change-status" data-id="{{ $row->id }}"
                    data-status="{{ $row->status ? '0' : '1' }}" data-url="{{ $routes['status'] }}">
                    <i class="ph-toggle-{{ $row->status ? 'left' : 'right' }} me-2"></i>
                    {{ $row->status ? 'Nofaol qilish' : 'Faollashtirish' }}
                </a>
            @endif

            @if (!empty($routes['delete']))
                <a href="#" class="dropdown-item text-danger delete-user" data-id="{{ $row->id }}"
                    data-url="{{ $routes['delete'] }}">
                    <i class="ph-trash me-2"></i> O‘chirish
                </a>
            @endif

        </div>
    </div>
</div>
