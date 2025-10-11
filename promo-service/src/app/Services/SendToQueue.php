<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;


class SendToQueue
{
    protected static $connection = null;
    protected static $channel = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            self::$connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', 'promobank_rabbitmq'),
                env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'admin'),
                env('RABBITMQ_PASSWORD', 'admin'),
                env('RABBITMQ_VHOST', '/')
            );
            self::$channel = self::$connection->channel();
        }
        return [self::$connection, self::$channel];
    }

    public static function send($data, $queue = null)
    {
        [, $channel] = self::getConnection();

        $queueName = $queue ?? config('rabbitmq.queue');

        $channel->queue_declare($queueName, false, true, false, false);

        $payload = json_encode($data);
        $message = new AMQPMessage($payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, '', $queueName);

        Log::info("Queue '$queueName' push to notification-service: $payload");
    }

    public static function close()
    {
        if (self::$channel) {
            self::$channel->close();
        }
        if (self::$connection) {
            self::$connection->close();
        }
    }
}
