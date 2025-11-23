<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\MainBack;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\ProfilSettings;
use App\Telegram\Handlers\Refferral;
use App\Telegram\Handlers\Register\UpdateStartHandler;
use App\Telegram\Handlers\SocialMedia;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Cache;

class AuthenticatedRouteHandler
{

    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle($update)
    {
        $message = $update->getMessage()?->getText();
        $callbackQuery = $update->getCallbackQuery();
        $data = $callbackQuery?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $callbackQuery?->getMessage()?->getChat()?->getId();
        $triggers = [
            $this->translator->get($chatId, 'menu_profile'),
            "/menu",
            $this->translator->get($chatId, 'open_main_menu'),
        ];

        if (in_array($message, $triggers, true)) {
            app(Menu::class)->handle($chatId);
            return;
        }
        if ($callbackQuery) {
            app(SendMessages::class)->answerCallback([
                'callback_query_id' => $callbackQuery->getId(),
                'text' => '',
                'show_alert' => false,
            ]);
        }
        $callbackHandlers = [
            'back_to_main_menu' => MainBack::class,
            'menu_profile' => ProfilSettings::class,
            'menu_social' => SocialMedia::class,
            'menu_referral' => Refferral::class,
        ];

        if (isset($callbackHandlers[$data])) {
            return app($callbackHandlers[$data])->handle($update);
        }
        if ($data === 'menu_referral') {
            app(Refferral::class)->handle($update);
        }
        if ($data === 'edit_profile') {
            Cache::store('bot')->forget('tg_user:' . $chatId);
            Cache::store('bot')->forget('tg_user_update:' . $chatId);
            return app(UpdateStartHandler::class)->handle($chatId);
        }

    }
}