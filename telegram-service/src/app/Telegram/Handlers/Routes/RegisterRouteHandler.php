<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\PhoneStepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use App\Telegram\Services\RegisterService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class RegisterRouteHandler
{

    public function handle(Update $update)
    {
        $text   = $update->getMessage()?->getText() ?? $update->getCallbackQuery()?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        Log::info("data:" . $text);
        $data = app(RegisterService::class)->get($chatId);

        switch ($data['state']) {
            case 'waiting_for_phone':
                return app(PhoneStepHandler::class)->handle($update);
            case 'waiting_for_phone2':
                return app(Phone2StepHandler::class)->handle($update);
            case 'waiting_for_gender':
                return app(GenderStepHandler::class)->handle($chatId, $text);
            case 'waiting_for_region':
                return app(RegionStepHandler::class)->handle($update);
            case 'waiting_for_district':
                return app(DistrictStepHandler::class)->handle($update);
            case 'waiting_for_birthdate':
                return app(BirthdateStepHandler::class)->handle($update);
            case 'completed':
                return app(abstract :RegisterService::class)->finalizeUserRegistration($update);
            default:
                return app(PhoneStepHandler::class)->handle($update);
        }
    }
}