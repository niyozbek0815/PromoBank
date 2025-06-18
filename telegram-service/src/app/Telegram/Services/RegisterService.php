<?php
namespace App\Telegram\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegisterService
{
    protected string $prefix = 'tg_user_data:';
    public function mergeToCache(string $chatId, array $newData)
    {
        $existing = Cache::store('redis')->get(
            $this->prefix . $chatId,
        );

        $data = $existing ? json_decode($existing, true) : [];

        // Yangi ma’lumotlarni birlashtiramiz
        $merged   = array_merge($data, $newData);
        $existing = Cache::store('redis')->set(
            $this->prefix . $chatId,
            json_encode($merged)
        );
        Log::info($this->prefix . $chatId,
            ['data' => Cache::store('redis')->get(
                $this->prefix . $chatId,
            )]
        );

    }
    public function get(string $chatId)
    {
        $existing = Cache::store('redis')->get($this->prefix . $chatId);
        $data     = $existing ? json_decode($existing, true) : [];
        return $data;
    }
    public function forget(string $chatId)
    {
        Cache::store('redis')->forget($this->prefix . $chatId, );
    }
    public function getSessionStatus(string $chatId)
    {
        if (Cache::store('redis')->has('tg_user:' . $chatId)) {
            return 'authenticated';
        }

        if (Cache::store('redis')->has('tg_user_data:' . $chatId)) {
            return 'in_progress';
        }

        return 'none';
    }
    public function finalizeUserRegistration(string $chatId)
    {
        $data = json_decode(Cache::store('redis')->get("tg_user_data:$chatId"), true);

        if (! $data || ! isset($data['phone']) || ! isset($data['name'])) {
            return null; // Majburiy maydonlar yo'q
        }

        // // Userni yaratamiz
        // $user = User::create([
        //     'phone'       => $data['phone'],
        //     'chat_id'     => $data['chat_id'] ?? null,
        //     'name'        => $data['name'],
        //     'gender'      => $data['gender'] ?? null,
        //     'region_id'   => $data['region_id'] ?? null,
        //     'district_id' => $data['district_id'] ?? null,
        //     'lang'        => $data['lang'] ?? 'uz',
        //     // boshqa ustunlar
        // ]);

        // Redisdan tozalaymiz
        Cache::store('redis')->forget("tg_user_data:$chatId");

        // return $user;
    }

}