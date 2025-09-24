<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class VerifyWebAppToken
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['message' => 'Missing Bearer token'], 419);
            }

            // ✅ Tokenni faqat parse & validate qilamiz
            $payload = JWTAuth::setToken($token)->getPayload();
            $claims = $payload->toArray();

            $authUser = [
                'id' => $claims['sub'] ?? null,
                'chat_id' => $claims['chat_id'] ?? null,
                'claims' => $claims,
            ];

            // Requestga merge qilamiz
            $request->merge(input: [
                'auth_user' => $authUser,
            ]);

            return $next($request);

        } catch (TokenExpiredException $e) {
            Log::warning('JWT expired', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Token expired'], 419);

        } catch (TokenInvalidException $e) {
            Log::warning('JWT invalid', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Token invalid'], 419);

        } catch (JWTException $e) {
            Log::warning('JWT missing or malformed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Token malformed'], 419);
        }
    }
}
