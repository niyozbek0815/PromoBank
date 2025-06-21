<?php
namespace App\Telegram\Services;

use App\Services\FromServiceRequest;
use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use App\Telegram\Handlers\Start\StartHandler;
use App\Telegram\Handlers\Welcome;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class RegisterService
{
    protected string $prefix = 'tg_user_data:';
    public function __construct(private FromServiceRequest $forwarder)
    {
        $this->forwarder = $forwarder;
    }
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
        Log::info("mergeToCache" . $this->prefix . $chatId,
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
    public function finalizeUserRegistration(Update $update)
    {
        $chatId = $update->getMessage()?->getChat()?->getId();

        $required = $this->get($chatId);
        $fields   = ['region_id', 'district_id', 'name', 'phone2', 'gender', 'birthdate'];
        $lang     = Cache::store('redis')->get("tg_lang:$chatId", 'uz');
        $data     = ['lang' => $lang, 'chat_id' => (string) $chatId, 'phone' => $required['phone'], 'name' => $required['name']];

        foreach ($fields as $field) {
            $data[$field] = $required[$field];
        }

        Log::info("Foydalanuvchi ro‘yxatga olish yakunlanmoqda", ['chat_id' => $chatId, 'data' => $data]);

        return match (true) {
            empty($data['lang']) => app(StartHandler::class)->ask($chatId),
            ! array_key_exists('phone2', $data) => app(Phone2StepHandler::class)->ask($chatId),
            empty($data['gender']) => app(GenderStepHandler::class)->ask($chatId),
            empty($data['region_id']) => app(RegionStepHandler::class)->ask($chatId),
            empty($data['district_id']) => app(DistrictStepHandler::class)->ask($chatId, $data['region_id'] ?? null),
            empty($data['birthdate']) => app(BirthdateStepHandler::class)->ask($chatId),
            default => $this->registerUserAndFinalize($chatId, $data),
        };
    }protected function registerUserAndFinalize($chatId, $data)
    {
        Log::info("User create request yuborilmoqda", ['chat_id' => $chatId, 'data' => $data]);

        $baseUrl         = config('services.urls.auth_service');
        $data['chat_id'] = (string) $chatId;
        $response        = $this->forwarder->forward('POST', $baseUrl, '/bot/user_create', $data);

        if (! $response instanceof \Illuminate\Http\Client\Response  || ! $response->successful()) {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }

        Log::info("Auth servisidan javob", context: ['response' => $response->json()]);

        $user = $response->json('user');

        if ($user && isset($user['id'])) {
            app(UserSessionService::class)->put($chatId, [
                'user_id' => $user['id'],
                'phone'   => $user['phone'],
                'name'    => $user['name'],
                'lang'    => $user['lang'],
                'state'   => 'completed',
            ]);
            $this->forget($chatId);
            Log::info("User session saqlandi va ro‘yxat yakunlandi", ['chat_id' => $chatId]);
            return app(Welcome::class)->handle($chatId);
        }

        return Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => "🎉 Ro‘yxatdan o‘tish yakunlandi!",
        ]);
    }

}
