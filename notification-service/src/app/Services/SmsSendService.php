<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsSendService
{
    private $username;
    private $password;
    private $endpoint;

    public function __construct()
    {
        $this->username = config('services.promobanksms.username');
        $this->password = config('services.promobanksms.password');
        $this->endpoint = config('services.promobanksms.endpoint');
    }
    public function sendMessage(string $message, string $phone, int $message_id)
    {
        Log::warning(message: 'Sms service ishladi');

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->withBasicAuth($this->username, $this->password)
            ->retry(3, 100)
            ->post($this->endpoint, [
                "messages" => [
                    [
                        "recipient" =>  $phone,
                        "message-id" => $message_id,
                        "sms" => [
                            "originator" => "Promobank",
                            "content" => [
                                "text" => $message
                            ],
                        ],
                    ],
                ],
            ]);
        if ($response->successful()) {
            Log::info('Sms yuborildi', ['phone' => $phone, 'message' => $message]);
        } else {
            Log::warning(message: 'Sms yuborilmadi');
        }
    }
}
