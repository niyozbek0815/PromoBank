<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sms\SmsPromocodeRequest;
use App\Jobs\ProcessSmsPromoJob;
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
        $correlationId = (string) Str::uuid();
        ProcessSmsPromoJob::dispatch($req['phone'], $req['promocode'], $req['short_phone'], $correlationId, now())
            ->onQueue('promo_queue');
        return $this->successResponse([
            'status' => 'accepted',
            'correlation_id' => $correlationId
        ], 'accepted');
    }
}
