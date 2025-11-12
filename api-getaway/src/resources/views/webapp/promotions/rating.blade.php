@extends('webapp.layouts.app')

@section('title', 'PromoBank')

@section('content')
    @php
        $locale = app()->getLocale();
    // dd($data);
    @endphp

    <section class="rating" >
        <div class="container-sm">
            <div class="rating-header">
                <h2 class="section-title">{{ __('messages.rating_title') }}</h2>
                <p class="sub-title" id="countdown">00:00:00</p>
                <p class="description" >    {{ __('messages.daily_refresh', ['time' => $refresh_time]) }}</p>
                   <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>F.I.SH</th>
                        <th>Ball</th>
                    </tr>
                </thead>
                <tbody id="ratingTable">
       @foreach($users as $user)
                    <tr @if(isset($my_rank['user_id']) && $user['user_id'] === $my_rank['user_id']) class="me-row" @endif>
                        <td>{{ $user['rank'] }}</td>
                        <td>{{ $user['name'] }}</td>
                        <td>{{ $user['total_points'] }}</td>
                    </tr>
                @endforeach

                {{-- Agar foydalanuvchi TOP 100 ichida bo‘lmasa --}}
                @if(isset($my_rank['user_id']) && !collect($users)->pluck('user_id')->contains($my_rank['user_id']))
                    <tr class="me-row">
                        <td>{{ $my_rank['rank'] }}</td>
                        <td>{{ $my_rank['name'] }}</td>
                        <td>{{ $my_rank['total_points'] }}</td>
                    </tr>
                @endif
                </tbody>
            </table>

            </div>


        </div>
    </section>
@endsection

@section('scripts')
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Countdown logikasi (sening mavjud koding)
    const refreshTime = "{{ $refresh_time }}";
    const countdownElement = document.getElementById('countdown');

    function updateCountdown() {
        const now = new Date();
        const [hours, minutes] = refreshTime.split(':').map(Number);
        let target = new Date();
        target.setHours(hours, minutes, 0, 0);
        if (target <= now) target.setDate(target.getDate() + 1);

        const diff = target - now;
        const h = Math.floor(diff / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);
        countdownElement.textContent =
            `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    // ✅ Jadvaldagi "me-row" ni markazga olib kelish
    const meRow = document.querySelector('.rating tr.me-row');
    if (meRow) {
        // Loader yopilganidan keyin ishga tushadi (masalan 500ms kechikish bilan)
        setTimeout(() => {
            meRow.scrollIntoView({
                behavior: 'smooth',
                block: 'center' // markazga olib keladi
            });
        }, 500);
    }
});
</script>
@endsection
@endsection
