<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
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
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_phone2'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => $this->translator->get($chatId, 'next'), 'callback_data' => 'next:phone2'],
                    ],
                ],
            ]),
        ]);

        $messageId = $response->getMessageId();
        Cache::store('redis')->put("tg_phone2_msg:$chatId", $messageId, now()->addMinutes(10));
    }

    public function handle(Update $update)
    {
        $callback = $update->getCallbackQuery();
        $message  = $update->getMessage();
        $chatId   = $message?->getChat()?->getId() ?? $callback?->getMessage()?->getChat()?->getId();
        $phone2   = null;
        if ($callback) {
            $data   = $callback->getData();
            $phone2 = $data === 'next:phone2' ? null : $data;

            // Callback orqali kelgan eski tugmani o‘chirish
            if ($msgId = $callback->getMessage()?->getMessageId()) {
                Telegram::deleteMessage([
                    'chat_id'    => $chatId,
                    'message_id' => $msgId,
                ]);
            }
        }

        // Foydalanuvchi matn yuborgan holat
        elseif ($text = $message?->getText()) {
                                                         // Probellar va boshqa belgilarni olib tashlaymiz
            $cleaned = preg_replace('/\D+/', '', $text); // faqat raqamlar qoldiriladi

            if (! preg_match('/^998\d{9}$/', $cleaned)) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text'    => $this->translator->get($chatId, 'invalid_phone2_format'),
                ]);
                return;
            }

            $phone2 = '+' . $cleaned;

            $phone2 = $text;

            if ($storedMsgId = Cache::store('redis')->pull("tg_phone2_msg:$chatId")) {
                Telegram::deleteMessage([
                    'chat_id'    => $chatId,
                    'message_id' => $storedMsgId,
                ]);
            }
            Telegram::sendMessage([
                'chat_id'      => $chatId,
                'text'         => $this->translator->get($chatId, 'phone2_received'),
                'reply_markup' => json_encode(['remove_keyboard' => true]),
            ]);
        }

        app(RegisterService::class)->mergeToCache($chatId, [
            'phone2' => $phone2,
            'state'  => 'waiting_for_gender',
        ]);

        return app(GenderStepHandler::class)->ask($chatId);
    }
}
