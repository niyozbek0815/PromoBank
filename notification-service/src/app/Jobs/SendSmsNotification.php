<?php

namespace App\Jobs;

use App\Services\SmsSendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class SendSmsNotification implements ShouldQueue

{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;

    public $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }


    /**
     * Execute the job.
     */
    public function handle(SmsSendService $smsSendService)
    {
        Log::info('SMS message received Sms Sender Job:', ['phone' => $this->phone, 'message' => $this->message]);
        $message_id = 1;
        $smsSendService->sendMessage($this->message, $this->phone, $message_id);
    }
}
