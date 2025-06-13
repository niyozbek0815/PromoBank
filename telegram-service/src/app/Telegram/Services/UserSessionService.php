<?php
// Fayl: app/Telegram/Services/UserSessionService.php

namespace App\Telegram\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class UserSessionService
{
    protected string $prefix = 'tg_user:';

    public function exists(string $chatId)
    {
        return Cache::store('redis')->has($this->prefix . $chatId);
    }

    public function put(string $chatId, array $data)
    {
        Cache::store('redis')->put(
            $this->prefix . $chatId,
            $data,
            now()->addMinutes(1)
        );
    }

    public function get(string $chatId)
    {
        return Cache::store('redis')->get($this->prefix . $chatId);
    }

    public function clear($chatId)
    {
        Cache::store('redis')->forget($this->prefix . $chatId);
    }

    public function bindChatToUser(string $chatId, string $phone)
    {
        // $user = User::where('phone', $phone)->first();

        // if ($user) {
        //     $user->chat_id = $chatId;
        //     $user->save();
        // } else {
        //     $user = User::create([
        //         'phone' => $phone,
        //         'chat_id' => $chatId,
        //         'name' => 'Telegram',
        //         'lang' => 'uz',
        //     ]);
        // }

        // $this->put($chatId, [
        //     'user_id' => $user->id,
        //     'phone' => $user->phone,
        //     'lang' => $user->lang,
        //     'state' => 'waiting_for_region',
        // ]);
        $this->put($chatId, [
            'user_id' => "User",
            'phone' => $phone,
            'lang' => "uz",
            'state' => 'waiting_for_region',
        ]);
        return false;
    }
}
