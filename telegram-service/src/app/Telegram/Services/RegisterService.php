<?php
namespace App\Telegram\Services;

use App\Jobs\RegisteredReferralJob;
use App\Services\FromServiceRequest;
use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\LanguageHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use App\Telegram\Handlers\Welcome;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
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
        $key = $this->prefix . $chatId;

        // Mavjud ma'lumotni olish
        $existing = Cache::store("bot")->get($key);
        $data = $existing ? json_decode($existing, true) : [];

        // Birlashtirish
        $merged = array_merge($data, $newData);

        // 1 soatga cachega yozishs
        Cache::store("bot")->put(
            $key,
            json_encode($merged),
            864000
        );
    }
    public function get(string $chatId)
    {
        $existing = Cache::store("bot")->get($this->prefix . $chatId);
        $data = $existing ? json_decode($existing, true) : [];
        return $data;
    }
    public function forget(string $chatId)
    {
        Cache::store("bot")->forget($this->prefix . $chatId);
    }


    public function finalizeUserRegistration(Update $update)
    {
        $chatId = $update->getMessage()?->getChat()?->getId();
        $required = $this->get($chatId);
        // $fields = ['region_id', 'district_id', 'name', 'phone2', 'gender', 'birthdate'];
        $fields = ['region_id', 'name', 'phone2', 'gender', 'birthdate'];

        $lang = Cache::store("bot")->get("tg_lang:$chatId", 'uz');
        $data = ['lang' => $lang, 'chat_id' => (string) $chatId, 'phone' => $required['phone'], 'name' => $required['name']];
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();


        // 🔹 Foydalanuvchi va chat obyektlarini olish
        $from = $message?->getFrom() ?? $callback?->getFrom();
        $chat = $message?->getChat() ?? $callback?->getMessage()?->getChat();

        // 🔹 Username olishda eng ishonchli usul
        $username = $chat?->get('first_name')
            ?? $chat?->first_name
            ?? $chat?->get('username')
            ?? $chat?->username
            ?? $from?->get('username')
            ?? $from?->username
            ?? $from?->get('first_name')
            ?? $from?->first_name
            ?? null;

        foreach ($fields as $field) {
            $data[$field] = $required[$field];
        }


        return match (true) {
            empty($data['lang']) => app(LanguageHandler::class)->ask($chatId),
            !array_key_exists('phone2', $data) => app(Phone2StepHandler::class)->ask($chatId),
            empty($data['gender']) => app(GenderStepHandler::class)->ask($chatId),
            empty($data['region_id']) => app(RegionStepHandler::class)->ask($chatId),
            // empty($data['district_id']) => app(DistrictStepHandler::class)->ask($chatId, $data['region_id'] ?? null),
            empty($data['birthdate']) => app(BirthdateStepHandler::class)->ask($chatId),
            default => $this->registerUserAndFinalize($chatId, $data, $username),
        };
    }
    protected function registerUserAndFinalize($chatId, $data, $username)
    {

        $baseUrl = config('services.urls.auth_service');
        $data['chat_id'] = (string) $chatId;
        $response = $this->forwarder->forward('POST', $baseUrl, '/user_create', $data);

        if (!$response instanceof \Illuminate\Http\Client\Response || !$response->successful()) {
            // logger()->error('Userni olishda xatolik', [
            //     'status' => $response->status(),
            //     'body'   => $response->body(),
            // ]);
            return;
        }


        $user = $response->json('user');

        if ($user) {
            $user['state'] = 'completed';
            Queue::connection('rabbitmq')->push(new RegisteredReferralJob(
                $chatId,
                $user['id'],
                $username
            ));
            app(UserSessionService::class)->put(
                $chatId,
                $user
            );
            $this->forget($chatId);
            return app(Welcome::class)->handle($chatId);
        }

        return;
    }

}
