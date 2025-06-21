<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Handlers\Welcome;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserSessionService;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class PhoneStepHandler
{
    public function __construct(
        protected Translator $translator,
        protected UserSessionService $userSession
    ) {}
    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_phone'),
            'reply_markup' => json_encode([
                'keyboard'          => [[
                    ['text' => $this->translator->get($chatId, 'share_phone_button'), 'request_contact' => true],
                ]],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $chatId  = $message?->getChat()?->getId();
        $phone   = $message?->getContact()?->getPhoneNumber();
        $contact = $message?->getContact();

        // ✅ Faqat contact kelganini tekshirish
        if (! $contact) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'only_contact_allowed'),
            ]);
            return;
        }
        $phone = str_starts_with($phone, '+') ? $phone : '+' . $phone;

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'phone_received'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
        $name = trim(
            ($message?->getFrom()?->getFirstName() ?? '') . ' ' .
            ($message?->getFrom()?->getLastName() ?? '')
        ) ?: 'Telegram User';

        if ($this->userSession->bindChatToUser($chatId, $phone, $name)) {
            app(RegisterService::class)->forget($chatId);
            return app(Welcome::class)->handle($chatId);
        }

        return app(Phone2StepHandler::class)->ask($chatId);
    }

}
