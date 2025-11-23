<?php

namespace App\Telegram\Services;

use Illuminate\Support\Facades\Cache;

class SessionStatusService
{
    public function __construct(
        private UserSessionService $userSessionService
    ) {
    }

    /**
     * Chat ID bo‘yicha sessiya holatini aniqlaydi.
     */
    public function getStatus(string $chatId): string
    {
        // 1) Register jarayoni ichida
        if (Cache::store("bot")->has('tg_user_data:' . $chatId)) {
            return 'in_register';
        }

        // 2) Update jarayoni ichida
        if (Cache::store("bot")->has('tg_user_update:' . $chatId)) {
            return 'in_update';
        }

        // 3) User mavjud bo‘lsa (UserSessionService orqali)
        if ($this->userSessionService->exists($chatId)) {
            return 'authenticated';
        }

        // 4) Hech qanday sessiya yo‘q
        return 'none';
    }
}