<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\MainBack;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\ProfilSettings;
use App\Telegram\Handlers\Refferral;
use App\Telegram\Handlers\Register\UpdateStartHandler;
use App\Telegram\Handlers\SocialMedia;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class AuthenticatedRouteHandler
{

    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle($update)
    {

        $message = $update->getMessage()?->getText();
        $getData = $update->getCallbackQuery()?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        if ($message === $this->translator->get($chatId, 'menu_profile')) {

            app(Menu::class)->handle($chatId);
        }
        if ($message === "/menu" || $message === $this->translator->get($chatId, 'open_main_menu')) {
            Log::info("User opened main menu", ['chat_id' => $chatId]);
            app(Menu::class)->handle($chatId);
        }
        if ($getData === 'back_to_main_menu') {
            app(MainBack::class)->handle($update);
        }
        if ($getData === 'menu_profile') {
            app(abstract: ProfilSettings::class)->handle($update);
        }
        if ($getData === 'menu_social') {
            app(abstract: SocialMedia::class)->handle($update);
        }
        if ($getData === 'menu_social') {
            app(abstract: SocialMedia::class)->handle($update);
        }
        if ($getData === 'menu_referral') {
            app(Refferral::class)->handle($update);
        }
        if ($getData === 'edit_profile') {
            // Cache::store('bot')->forget('tg_user_data:' . $chatId);
            // Cache::store('bot')->forget('tg_user:' . $chatId);
            // Cache::store('bot')->forget('tg_user_update:' . $chatId);

            return app(UpdateStartHandler::class)->handle($chatId);
        }

    }
}
