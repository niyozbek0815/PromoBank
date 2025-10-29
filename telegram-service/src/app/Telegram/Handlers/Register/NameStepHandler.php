<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Telegram\Bot\Laravel\Facades\Telegram;

class NameStepHandler
{
    public function __construct(
        protected Translator $translator
    ) {}

    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_name'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
    }

    protected function processName($update, $service)
    {
        $messageText = $update->getMessage()?->getText();
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        if (! preg_match('/^[\p{L}\s]{3,}$/u', $messageText)) {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_name_format'),
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    =>  $this->translator->get($chatId, 'name_received'),
        ]);

        app($service)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'name'    => $messageText,
            'state'   => 'waiting_for_phone2',
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
