<?php

namespace App\Telegram\Services;

use App\Jobs\StartAndRefferralJob;
use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Queue;

class StartService
{
    public function __construct(private FromServiceRequest $forwarder)
    {
        $this->forwarder = $forwarder;
    }

    public function handle($chatId, $username, $referrerId)
    {
        $payload = ['chat_id' => $chatId, 'username' => $username, 'referrer_id' => $referrerId];
        $response = $this->forwarder->forward('POST', config('services.urls.auth_service'), '/bot_start', $payload);
        if (!$response->successful()) {
            return false;
        }
        $data = $response->json();
        if ($data['exist']) {
            return true;
        }

        $me = $data['user'];
        $referrerUser = $data['referrer_user'] ?? null;
        $new = $data['new_user'] ?? false;
        if ($new && $referrerUser) {
            Queue::connection('rabbitmq')->push(new StartAndRefferralJob(
                $chatId,
                $username,
                $referrerUser,
                $me
            ));
        }
        return false;
    }
}
