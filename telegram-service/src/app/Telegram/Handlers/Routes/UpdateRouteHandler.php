<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\LanguageHandler;
use App\Telegram\Handlers\Register\NameStepHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class UpdateRouteHandler
{
    public function handle(Update $update)
    {
        $text = $update->getMessage()?->getText() ?? $update->getCallbackQuery()?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $data = app(UserUpdateService::class)->get($chatId);

        switch ($data['state']) {
            case 'waiting_for_language':
                return app(LanguageHandler::class)->handleUpdate($update);
            case 'waiting_for_name':
                return app(NameStepHandler::class)->handleUpdate($update);

            case 'waiting_for_phone2':
                return app(Phone2StepHandler::class)->handleUpdate($update);
            case 'waiting_for_gender':
                return app(GenderStepHandler::class)->handleUpdate($chatId, $text);
            case 'waiting_for_region':
                return app(RegionStepHandler::class)->handleUpdate($update);
            case 'waiting_for_district':
                return app(DistrictStepHandler::class)->handleUpdate($update);
            case 'waiting_for_birthdate':
                return app(BirthdateStepHandler::class)->handleUpdate($update);

            case 'complete':
                return app(UserUpdateService::class)->finalizeUserRegistration($update);
            default:
                return app(LanguageHandler::class)->ask($chatId);
        }
    }
}