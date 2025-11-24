<?php
namespace App\Telegram\Services;

use App\Services\FromServiceRequest;
use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\LanguageHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class UserUpdateService
{
    protected string $prefix = 'tg_user_update:';

    public function __construct(protected Translator $translator, protected SendMessages $sender, protected FromServiceRequest $forwarder)
    {
    }
    public function mergeToCache(string $chatId, array $newData)
    {
        $existing = Cache::store('bot')->get(
            $this->prefix . $chatId,
        );
        $data = $existing ? json_decode($existing, true) : [];
        $merged = array_merge($data, $newData);
        $existing = Cache::store('bot')->set(
            $this->prefix . $chatId,
            json_encode($merged),
        );
    }
    public function get(string $chatId)
    {
        $existing = Cache::store('bot')->get($this->prefix . $chatId);
        $data = $existing ? json_decode($existing, true) : [];
        return $data;
    }
    public function forget(string $chatId)
    {
        Cache::store('bot')->forget($this->prefix . $chatId);
    }
    public function finalizeUserRegistration(Update $update)
    {
        $chatId = $update->getMessage()?->getChat()?->getId();
        $required = $this->get($chatId);

        $fields = [
            'lang' => fn() => Cache::store('bot')->get("tg_lang:$chatId", 'uz'),
            'region_id' => fn() => $required['region_id'] ?? null,
            'name' => fn() => $required['name'] ?? null,
            // 'phone2' => fn() => $required['phone2'] ?? null,
            'gender' => fn() => $required['gender'] ?? null,
            'birthdate' => fn() => $required['birthdate'] ?? null,
        ];
        $data = [];
        foreach ($fields as $key => $getter) {
            $data[$key] = $getter();
        }

        $handlers = [
            'lang' => LanguageHandler::class,
            // 'phone2' => Phone2StepHandler::class,
            'gender' => GenderStepHandler::class,
            'region_id' => RegionStepHandler::class,
            //'district_id' => fn() => DistrictStepHandler::class, // agar kerak bo‘lsa
            'birthdate' => BirthdateStepHandler::class,
        ];

        foreach ($handlers as $key => $handler) {
            if (empty($data[$key])) {
                return app($handler)->ask($chatId);
            }
        }

        return $this->registerUserAndFinalize($chatId, $data);
    }
    protected function registerUserAndFinalize($chatId, $data)
    {
        $data['chat_id'] = (string) $chatId;

        $response = $this->forwarder->forward(
            'POST',
            config('services.urls.auth_service'),
            '/user_update',
            $data
        );
        Log::info('UserUpdateService response', ['response' => $response->body()]);
        $this->forget($chatId);
        $replyMarkup = [
            'keyboard' => [
                [['text' => $this->translator->get($chatId, 'open_main_menu')]],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful() && $user = $response->json('user')) {
            $user['state'] = 'completed';
            app(UserSessionService::class)->put($chatId, $user);
            return $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'profile_update_success'),
                'reply_markup' => json_encode($replyMarkup),
            ]);
        }


        return $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'error_retry_later'),
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

}
