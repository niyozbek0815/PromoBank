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
        $data = Cache::store('redis')->get($this->prefix . $chatId);
        // ✅ Agar $data string bo‘lsa, decode qilamiz, aks holda o‘zini qaytaramiz
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }

        return is_array($data) ? $data : [];
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
            '/user_check',
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
        if ($exist && $user) {
            // ✅ Mavjud user – Redisga yozamiz
            Log::info("Qaytgan user malumotlari: ", ['user' => $user]);
            $user['state'] = 'completed';
            $this->put($chatId, $user);
            return true;
        }
        $initialData = [
            'phone' => $phone,
            'state' => 'waiting_for_name', // birinchi step flag
        ];

        // Log::info(message: "intial:", $initialData);
        app(RegisterService::class)->mergeToCache($chatId, $initialData);

        return false;
    }
}
