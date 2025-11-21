<?php
namespace App\Telegram\Services;

use App\Telegram\Handlers\Register\BirthdateStepHandler;
use App\Telegram\Handlers\Register\DistrictStepHandler;
use App\Telegram\Handlers\Register\GenderStepHandler;
use App\Telegram\Handlers\Register\LanguageHandler;
use App\Telegram\Handlers\Register\NameStepHandler;
use App\Telegram\Handlers\Register\OfertaStepHandler;
use App\Telegram\Handlers\Register\Phone2StepHandler;
use App\Telegram\Handlers\Register\PhoneStepHandler;
use App\Telegram\Handlers\Register\RegionStepHandler;
use App\Telegram\Handlers\Welcome;
use Illuminate\Support\Facades\Cache;

class RegisterRouteService
{
    public function askNextStep($chatId)
    {
        $tg_user_data = json_decode(Cache::store('bot')->get("tg_user_data:$chatId"), true) ?? [];
        return match ($tg_user_data['state'] ?? null) {
            'waiting_for_language' => app(LanguageHandler::class)->ask($chatId),
            'waiting_for_name' => app(NameStepHandler::class)->ask($chatId),
            'waiting_for_phone' => app(PhoneStepHandler::class)->ask($chatId),
            'waiting_for_phone2' => app(Phone2StepHandler::class)->ask($chatId),
            'waiting_for_region' => app(RegionStepHandler::class)->ask($chatId),
            // 'waiting_for_district' => app(DistrictStepHandler::class)->ask($chatId, $tg_user_data['region_id'] ?? null),
            'waiting_for_gender' => app(GenderStepHandler::class)->ask($chatId),
            'waiting_for_birthdate' => app(BirthdateStepHandler::class)->ask($chatId),
            'waiting_for_offer' => app(OfertaStepHandler::class)->ask($chatId),
            "complete" => app(Welcome::class)->handle($chatId),
        };
    }
}