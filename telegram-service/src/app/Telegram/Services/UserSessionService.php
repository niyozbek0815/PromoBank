<?php
// Fayl: app/Telegram/Services/UserSessionService.php

namespace App\Telegram\Services;

use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Cache;

class UserSessionService
{
    protected string $prefix = 'tg_user:';
    public function __construct(private FromServiceRequest $forwarder)
    {
        $this->forwarder = $forwarder;
    }

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
        $baseUrl  = config('services.urls.auth_service');
        $response = $this->forwarder->forward(
            'POST',
            $baseUrl,
            '/users_for_sms',
            ['phone' => $phone, 'chat_id' => $chatId]
        );

        if ($response->successful()) {
            $user = data_get($response->json(), 'data.user');
            if (! $user) {
                logger()->error('User ID mavjud emas, lekin response successful', [
                    'response' => $response->json(),
                    'base_url' => $baseUrl,
                ]);
            }

        } else {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }

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
            'phone'   => $phone,
            'lang'    => "uz",
            'state'   => 'waiting_for_region',
        ]);
        return false;
    }
}
