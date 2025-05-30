<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SmsPromoSender
{
    public static function send($data)
    {
        $connection = new AMQPStreamConnection('promobank_rabbitmq', 5672, 'admin', 'admin');
        $channel = $connection->channel();
        $channel->queue_declare('promo_sms_queue', false, true, false, false);
        $payload = json_encode($data);
        $message = new AMQPMessage($payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, '', 'promo_sms_queue');
        $channel->close();
        $connection->close();
    }
}
