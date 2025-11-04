<?php
namespace App\Telegram\Handlers\Register;

use App\Jobs\RegisterPrizeJob;
use App\Telegram\Services\RegisterRouteService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserSessionService;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\FileUpload\InputFile;
class PhoneStepHandler
{
    public function __construct(
        protected Translator $translator,
        protected UserSessionService $userSession
    ) {
    }
    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_phone'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        ['text' => $this->translator->get($chatId, 'share_phone_button'), 'request_contact' => true],
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();

        $chatId = $message?->getChat()?->getId();
        $phone = $message?->getContact()?->getPhoneNumber();
        $contact = $message?->getContact();
        $from = $message?->getFrom() ?? $callback?->getFrom();
        $chat = $message?->getChat() ?? $callback?->getMessage()?->getChat();

        $username = $chat?->get('first_name')
            ?? $chat?->first_name
            ?? $chat?->get('username')
            ?? $chat?->username
            ?? $from?->get('username')
            ?? $from?->username
            ?? $from?->get('first_name')
            ?? $from?->first_name

            ?? null;
        // ✅ Faqat contact kelganini tekshirish
        if (!$contact) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'invalid_phone_format'),
            ]);
            return;
        }
        $phone = str_starts_with($phone, '+') ? $phone : '+' . $phone;

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'phone_received'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);

        if ($this->userSession->bindChatToUser($chatId, $phone, $username)) {
            app(RegisterService::class)->forget($chatId);

            app(AlreadyRegisterStepHandler::class)->handle($chatId);
            return;
        }

        return app(RegisterRouteService::class)->askNextStep($chatId);
    }

}
