<?php

namespace App\Console\Commands;

use App\Jobs\TestJob;
use Illuminate\Console\Command;
use App\Jobs\SmsSenderJob;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Support\Facades\Log;

class ConsumeAllNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-all-notifications';
    protected $description = 'Listen to multiple RabbitMQ queues (SMS, Email, Telegram)';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     $connection = new AMQPStreamConnection('promobank_rabbitmq', 5672, 'admin', 'admin');
    //     $channel = $connection->channel();
    //     $queues = ['sms_message_sender'];
    //     $channel->queue_declare($queues[0], false, true, false, false);
    //     $channel->basic_consume('sms_message_sender', '', false, false, false, false, function ($msg) {
    //         try {
    //             $data = json_decode($msg->body, true);

    //             // Laravel job format bo‘lsa — skip qilamiz
    //             if (isset($data['job']) && isset($data['data'])) {
    //                 Log::warning('Laravel job format already detected — skipping dispatch', ['payload' => $data]);
    //             } else {
    //                 Log::info('Dispatching new SmsSenderJob', ['payload' => $data]);
    //                 Queue::connection('rabbitmq')->pushOn('sms_message_sender', job: new SmsSenderJob($data));
    //                 TestJob::dispatch(['message' => 'Hello from RabbitMQ'])->onConnection('rabbitmq')->onQueue('sms_message_sender');
    //             }

    //             $msg->ack();
    //         } catch (\Throwable $e) {
    //             Log::error('Exception in sms_message_sender callback', [
    //                 'error' => $e->getMessage(),
    //                 'trace' => $e->getTraceAsString(),
    //             ]);
    //             $msg->nack(false, false); // optionally reject the message
    //         }
    //     });

    //     while ($channel->is_consuming()) {
    //         $channel->wait();
    //     }
    // }
    public function handle()
    {
        $connection = new AMQPStreamConnection('promobank_rabbitmq', 5672, 'admin', 'admin');
        $channel = $connection->channel();

        $channel->queue_declare('sms_message_sender', false, true, false, false);

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            Log::info('Dispatching new SmsSenderJob', ['payload' => $data]);

            Queue::connection('rabbitmq')->push(job: new SmsSenderJob($data));
            $msg->ack();
        };

        $channel->basic_consume('sms_message_sender', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}