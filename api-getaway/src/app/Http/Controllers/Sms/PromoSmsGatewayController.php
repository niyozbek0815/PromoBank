<?php

namespace App\Http\Controllers\Sms;

use App\Events\PromoSmsReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sms\SmsPromocodeRequest;
use App\Services\SmsPromoSender;
use Illuminate\Support\Str;

class PromoSmsGatewayController extends Controller
{
    public function __construct(protected SmsPromoSender $smsPromoSender)
    {
        $this->smsPromoSender = $smsPromoSender;
    }

    public function receive(SmsPromocodeRequest $request)
    {
        $req = $request->validated();
        $correlationId = (string) Str::uuid(); // for tracing
        $this->smsPromoSender->send([
            'phone' => $req['phone'],
            'promocode' => $req['promocode'],
            'short_phone' => $req['short_phone'],
            'correlation_id' => $correlationId,
            'received_at' => now()->toISOString(),
        ]);
        return response()->json([
            'status' => 'accepted',
            'correlation_id' => $correlationId
        ]);
    }
}
