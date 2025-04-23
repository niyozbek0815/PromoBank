<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CheckGuestToken
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return $this->errorResponse('Token not provided', null, 401);
        }

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $user = $this->buildUserFromPayload($payload);
            $request->merge(['auth_user' => $user]);
            return $next($request);
        } catch (TokenExpiredException $e) {
            return $this->handleExpiredToken($request, $token,  $next);
        } catch (JWTException $e) {
            return $this->errorResponse('Invalid or malformed token', $e->getMessage(), 401);
        }
    }

    protected function handleExpiredToken(Request $request, $token, Closure $next): Response
    {

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $request->bearerToken(),
                'Accept' => 'application/json',
                'X-Forwarded-For' => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ])->post(config('services.auth_service.url') . '/refresh-token', $request->all());

            $data = json_decode($res->getBody()->getContents(), true)['data'] ?? [];
            $newToken = $data['token'] ?? null;
            if (!$newToken) {
                return $this->errorResponse('Token refresh failed', null, 401);
            }

            $user = collect($data)->except('token')->toArray();
            $request->merge(['auth_user' => $user]);
            $response = $next($request);
            // Bu yerda `JsonResponse` bo‘lmasa ham ishlaydi:
            if ($response instanceof JsonResponse) {
                $original = $response->getData(true);
                $original['new_token'] = $newToken;
                return response()->json($original, $response->getStatusCode());
            }

            // fallback: agar boshqa response tipi bo‘lsa
            $content = $response->getContent();
            $json = json_decode($content, true);
            if (is_array($json)) {
                $json['new_token'] = $newToken;
                return response()->json($json, $response->getStatusCode());
            }
            return $response;
        } catch (\Throwable $e) {
            return $this->errorResponse('Token expired and cannot be refreshed', $e->getMessage(), 401);
        }
    }

    protected function buildUserFromPayload($payload): array
    {
        return [
            'auth_user_id' => $payload->get('user_id') ?? null,
            'is_guest'     => $payload->get('is_guest') ?? null,
            'phone'        => $payload->get('phone') ?? null,
            'ip'           => $payload->get('ip') ?? null,
        ];
    }
}