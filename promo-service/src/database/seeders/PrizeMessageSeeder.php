<?php
namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeMessage;
use Illuminate\Database\Seeder;

class PrizeMessageSeeder extends Seeder
{
    public function run(): void
    {
        $platforms        = ['sms', 'all'];
        $participantTypes = ['receipt_scan', 'smart_random','all'];
        $messageTypes     = ['success' ];
        $languages        = ['uz', 'ru', 'kr'];

        Prize::all()->each(function ($prize) use ($platforms, $participantTypes, $messageTypes, $languages) {
            foreach ($platforms as $platform) {
                foreach ($participantTypes as $participantType) {
                    foreach ($messageTypes as $messageType) {

                        // Har bir til uchun translatable xabar
                        $translatedMessage = [];
                        foreach ($languages as $lang) {
                            $translatedMessage[$lang] = "{$prize->name} | {$platform} | {$participantType} | {$messageType} ({$lang})";
                        }

                        // Kombinatsiyani yaratish yoki mavjudini saqlash
                        PrizeMessage::firstOrCreate([
                            'prize_id'         => $prize->id,
                            'platform'         => $platform,
                            'participant_type' => $participantType,
                            'message_type'     => $messageType,
                        ],
                        [
                            'message' => $translatedMessage,
                        ]);
                    }
                }
            }
        });
    }
}
