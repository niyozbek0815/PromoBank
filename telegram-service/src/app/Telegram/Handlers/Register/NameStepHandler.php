<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\NormalizeTextService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;

class NameStepHandler
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function ask($chatId)
    {
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_name'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
        return;
    }

    protected function processName($update, $service)
    {
        $messageText = $update->getMessage()?->getText();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        if (!preg_match('/^[\p{L}\s]{3,}$/u', $messageText)) {
            $this->sender->handle(
                [
                    'chat_id' => $chatId,
                    'text' => $this->translator->get($chatId, 'invalid_name_format'),
                ]
            );
            return;
        }
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'name_received'),
        ]);
        app($service)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'name' => app(NormalizeTextService::class)->normalizeText($messageText),
            'state' => 'waiting_for_phone2',
        ]);
        return app(Phone2StepHandler::class)->ask($chatId);
    }

    public function handle($update)
    {
        return $this->processName($update, RegisterService::class);
    }

    public function handleUpdate($update)
    {
        return $this->processName($update, UserUpdateService::class);
    }
}
