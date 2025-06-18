<?php
// Fayl: app/Telegram/Services/UserSessionService.php

namespace App\Telegram\Services;

use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    public function bindChatToUser(string $chatId, string $phone, $name)
    {
        $baseUrl = config('services.urls.auth_service');
        Log::info("url=" . $baseUrl);
        $response = $this->forwarder->forward(
            'POST',
            $baseUrl,
            '/user_check_bot',
            ['phone' => $phone, 'chat_id' => $chatId]
        );

        if (! $response->successful()) {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }
        $data  = $response->json('data');
        $user  = $data['user'] ?? null;
        $exist = $data['exist'] ?? false;
        if ($exist && $user && isset($user['id'])) {
            // ✅ Mavjud user – Redisga yozamiz
            $this->put($chatId, [
                'user_id' => $user['id'],
                'phone'   => $user['phone'],
                'name'    => $user['name'],
                'lang'    => $user['lang'],
                'state'   => 'completed',
            ]);
            return true;
        }
        $initialData = [
            'phone' => $phone,
            'name'  => $name,
            'state' => 'waiting_for_phone2', // birinchi step flag
        ];

        Log::info("intial:", $initialData);
        app(RegisterService::class)->mergeToCache($chatId, $initialData);

        return false;
    }
}
