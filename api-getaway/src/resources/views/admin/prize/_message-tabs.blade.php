<ul class="nav nav-tabs mb-2">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#{{ md5($prefix) }}-uz">O'zbek</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#{{ md5($prefix) }}-ru">Русский</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#{{ md5($prefix) }}-kr">Кирилл</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="{{ md5($prefix) }}-uz">
        <textarea class="form-control mb-2" name="{{ $prefix }}[uz]" rows="3" placeholder="O'zbek tilidagi xabar">{{ old($prefix . '.uz') }}</textarea>
    </div>
    <div class="tab-pane fade" id="{{ md5($prefix) }}-ru">
        <textarea class="form-control mb-2" name="{{ $prefix }}[ru]" rows="3" placeholder="Сообщение на русском">{{ old($prefix . '.ru') }}</textarea>
    </div>
    <div class="tab-pane fade" id="{{ md5($prefix) }}-kr">
        <textarea class="form-control mb-2" name="{{ $prefix }}[kr]" rows="3" placeholder="Кирилл хабар">{{ old($prefix . '.kr') }}</textarea>
    </div>
</div>
