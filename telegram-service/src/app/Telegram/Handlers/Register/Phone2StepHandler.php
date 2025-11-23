<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class Phone2StepHandler
{
    protected Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function ask($chatId)
    {
        $response = Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_phone2'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => $this->translator->get($chatId, 'next'), 'callback_data' => 'next:phone2'],
                    ],
                ],
            ]),
        ]);
        Cache::store('bot')->put("tg_phone2_msg:$chatId", $response->getMessageId(), now()->addMinutes(30));
    }

    protected function process(Update $update, $cacheService)
    {
        $callback = $update->getCallbackQuery();
        $message = $update->getMessage();
        $chatId = $message?->getChat()?->getId() ?? $callback?->getMessage()?->getChat()?->getId();
        $phone2 = null;

        if ($callback) {
            $data = $callback->getData();
            $phone2 = $data === 'next:phone2' ? null : $data;
            if ($msgId = $callback->getMessage()?->getMessageId()) {
                Telegram::deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $msgId,
                ]);
            }
        } elseif ($text = $message?->getText()) {
            $cleaned = preg_replace('/\D+/', '', $text);
            if (!preg_match('/^998\d{9}$/', $cleaned)) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $this->translator->get($chatId, 'invalid_phone2_format'),
                ]);
                return null;
            }
            $phone2 = '+' . $cleaned;
            if ($storedMsgId = Cache::store('bot')->pull("tg_phone2_msg:$chatId")) {
                Telegram::deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $storedMsgId,
                ]);
                Cache::store('bot')->forget("tg_phone2_msg:$chatId");
            }
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'phone2_received'),
                'reply_markup' => json_encode(['remove_keyboard' => true]),
            ]);
        }

        app($cacheService)->mergeToCache($chatId, [
            'phone2' => $phone2,
            'state' => 'waiting_for_gender',
        ]);
        return app(GenderStepHandler::class)->ask($chatId);
    }

    public function handle(Update $update)
    {
        return $this->process($update, RegisterService::class);
    }

    public function handleUpdate(Update $update)
    {
        return $this->process($update, UserUpdateService::class);
    }
}