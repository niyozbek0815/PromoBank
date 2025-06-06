<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;


class SendToQueue

{
    public static function send($data, $queue)
    {
        $connection = new AMQPStreamConnection('promobank_rabbitmq', 5672, 'admin', 'admin');
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $payload = json_encode($data);
        $message = new AMQPMessage($payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, '', $queue);
        Log::error(message: $queue . ' push to Notificato-service ' . $payload);

        $channel->close();
        $connection->close();
    }
}
