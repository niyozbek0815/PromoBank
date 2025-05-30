<?php

namespace Common\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class PromoSmsReceived implements ShouldQueue // bu event queue'da ishlaydi
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $phone;
    public  $promocode;
    public  $correlation_id;
    public  $short_phone; // optional field
    public  $received_at;

    public function __construct(array $data)
    {
        $this->phone = $data['phone'];
        $this->promocode = $data['promocode'];
        $this->short_phone = $data['short_phone'] ?? ''; // optional field
        $this->correlation_id = $data['correlation_id'];
        $this->received_at = $data['received_at'];
    }

    public function viaQueue()
    {
        // RabbitMQ'da shu nomdagi queue ga tushadi
        return 'promo.sms.receive';
    }
}
