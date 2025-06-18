<?php
namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class CompleteRegistrationHandler
{
    public function handle($chatId)
    {
        $data = [
            'region_id'   => Cache::store('redis')->pull("tg_reg_data:$chatId:region_id"),
            'district_id' => Cache::store('redis')->pull("tg_reg_data:$chatId:district_id"),
            'name'        => Cache::store('redis')->pull("tg_reg_data:$chatId:name"),
            'phone2'      => Cache::store('redis')->pull("tg_reg_data:$chatId:phone2"),
            'gender'      => Cache::store('redis')->pull("tg_reg_data:$chatId:gender"),
        ];

        // Auth servicega HTTP orqali yuborish yoki DB da yangilash
        // User::where('chat_id', $chatId)->update($data);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => "✅ Ro'yxatdan muvaffaqiyatli o'tdingiz!",
        ]);

    }
}
