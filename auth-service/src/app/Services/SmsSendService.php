<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsSendService
{
    private $username;
    private $password;
    private $endpoint;

    public function __construct()
    {
        $this->username = config('services.playmobile.username');
        $this->password = config('services.playmobile.password');
        $this->endpoint = config('services.playmobile.endpoint');
    }
    public function sendMessage(string $message, string $phone, int $message_id)
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->withBasicAuth($this->username, $this->password)
            ->retry(3, 100)
            ->post($this->endpoint, [
                "messages" => [
                    [
                        "recipient" =>  $phone,
                        "message-id" => $message_id,
                        "sms" => [
                            "originator" => "MAGNET",
                            "content" => [
                                "text" => "Magnet. Sizning ro'yhatdan o'tish kodingiz:" . $message . "  Your registration code:" . $message,
                            ],
                        ],
                    ],
                ],
            ]);
        if ($response->successful()) {
            return  [
                'status' => 'success',
            ];
        } else {
            return ['status' => 'failed'];
        }
    }
}