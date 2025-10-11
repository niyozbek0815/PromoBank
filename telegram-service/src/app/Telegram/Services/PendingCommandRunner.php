<?php
namespace App\Telegram\Services;

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class PendingCommandRunner
{
    public function run(string $chatId, $text)
    {
        Log::info("Bot pending command ishlamoqda: " . $text);
        // Cache::store('redis')->forget("tg_pending:$chatId");

        $chat = new Chat(['id' => $chatId, 'type' => 'private']);
        // Yangi update yasaymiz
        $message = new Message([
            'message_id' => time(),
            'chat'       => ['id' => $chatId],
            'text'       => $text,
            'date'       => time(),
        ]);
        $update = new Update([
            'update_id' => time(),
            'message'   => $message,
        ]);

        Telegram::addUpdate($update);

        // Laravelga request soxtasini yuboramiz
        // request()->replace([
        //     'message' => [
        //         'chat' => ['id' => $chatId],
        //         'text' => $text,
        //     ]
        // ]);
        $request = request();
        $request->attributes->set('__internal_update', $update);

        app(TelegramBotController::class)->handle(request());
    }
}
