<?php
namespace App\Telegram\Services;

use App\Jobs\RegisteredReferralJob;
use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class UserSessionService
{
    protected string $prefix = 'tg_user:';
    protected string $baseUrl;

    public function __construct(private FromServiceRequest $forwarder)
    {
        $this->forwarder = $forwarder;
        $this->baseUrl = config('services.urls.auth_service'); // runtime qiymat
    }

    public function exists(string $chatId): bool
    {
        $cacheKey = $this->prefix . $chatId;
        // Cache::store('bot')->forget($cacheKey);
        if (Cache::store('bot')->has($cacheKey)) {
            return true;
        }
        $response = $this->forwarder->forward(
            'POST',
            $this->baseUrl,
            '/user_exists',
            ['chat_id' => $chatId]
        );
        if (!$response->successful()) {
            Log::warning("Auth service bilan aloqa bo'lmadi", [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;
        }
        $data = $response->json();
        $user = $data['user'] ?? null;
        $exist = $data['exist'] ?? false;
        if ($exist && $user) {
            $this->put($chatId, $user);
            if (!empty($user['lang'])) {
                Cache::store('bot')->put(
                    "tg_lang:$chatId",
                    $user['lang'],
                    now()->addDays(7)
                );
            }
            return true;
        }
        return false;
    }

    public function put(string $chatId, array $data)
    {
        Cache::store('bot')->put(
            $this->prefix . $chatId,
            $data,
            604800
        );
    }

    public function get(string $chatId)
    {
        $data = Cache::store('bot')->get($this->prefix . $chatId);
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }

        return is_array($data) ? $data : [];
    }

    public function forget($chatId)
    {
        Cache::store('bot')->forget($this->prefix . $chatId);
    }

    public function bindChatToUser(string $chatId, string $phone, string $username): bool
    {
        $lang = Cache::store('bot')->get("tg_lang:$chatId", 'uz');
        $response = $this->forwarder->forward(
            'POST',
            $this->baseUrl,
            '/user_check',
            [
                'phone' => $phone,
                'chat_id' => $chatId,
                'lang' => $lang,
            ]
        );
        if (!$response->successful()) {
            logger()->error('Userni olishda xatolik', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        $data = $response->json();
        $user = $data['user'] ?? null;
        $exist = $data['exist'] ?? false;
        if ($exist && $user) {
            $user['state'] = 'completed';
            $this->put($chatId, $user);
            Queue::connection('rabbitmq')->push(
                new RegisteredReferralJob(
                    $chatId,
                    $user['id'],
                    $username
                )
            );
            return true;
        }
        app(RegisterService::class)->mergeToCache($chatId, [
            'phone' => $phone,
            'state' => 'waiting_for_name',
        ]);
        return false;
    }
}
