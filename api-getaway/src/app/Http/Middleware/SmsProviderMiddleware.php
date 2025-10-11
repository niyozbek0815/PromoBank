<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsProviderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. SMS provider yuborayotgan token
        $incomingToken = $request->header('X-Sms-Provider-Token');
        // 2. Sizning kutgan tokeningiz .env fayldan olinadi
        $expectedToken = config('services.sms_provider.token');
        // 3. Token mos kelmasa — xatolik
        if (!$incomingToken || $incomingToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized SMS provider'], 403);
        }

        return $next($request);
    }
}
