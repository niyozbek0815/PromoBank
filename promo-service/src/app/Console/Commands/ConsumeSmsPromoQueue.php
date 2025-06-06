<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Jobs\ProcessSmsPromoJob;
use Illuminate\Support\Facades\Queue;

class ConsumeSmsPromoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-sms-promo';
    protected $description = 'Consume SMS promo events from RabbitMQ';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('promobank_rabbitmq', 5672, 'admin', 'admin');
        $channel = $connection->channel();

        $channel->queue_declare('promo_sms_queue', false, true, false, false);

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            Queue::connection('rabbitmq')->push(new ProcessSmsPromoJob($data));
            $msg->ack();
        };

        $channel->basic_consume('promo_sms_queue', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
