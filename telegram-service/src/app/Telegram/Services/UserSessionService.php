<?php
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
            now()->addDays(7)
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
        $baseUrl = config('services.urls.auth_service');
        $lang    = Cache::store('redis')->get("tg_lang:$chatId", 'uz');
        Log::info("url=" . $lang);
        $response = $this->forwarder->forward(
            'POST',
            $baseUrl,
            '/bot/user_check',
            ['phone' => $phone, 'chat_id' => $chatId, 'lang' => $lang]
        );

        if (! $response->successful()) {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }
        $data  = $response->json();
        $user  = $data['user'] ?? null;
        $exist = $data['exist'] ?? false;
        if ($exist && $user && isset($user['id'])) {
            // ✅ Mavjud user – Redisga yozamiz
            Log::info("Qaytgan user malumotlari: ", ['user' => $user]);
            $userData          = collect($user)->except(['created_at', 'updated_at'])->toArray();
            $userData['state'] = 'completed';
            $this->put($chatId, $userData);
            return true;
        }
        $initialData = [
            'phone' => $phone,
            'state' => 'waiting_for_phone2', // birinchi step flag
        ];

        // Log::info(message: "intial:", $initialData);
        app(RegisterService::class)->mergeToCache($chatId, $initialData);

        return false;
    }
}
