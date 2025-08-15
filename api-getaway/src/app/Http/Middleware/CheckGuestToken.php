<?php
namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckGuestToken
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  string|null  $guestCheck  'true', 'false', yoki null
     */
    public function handle(Request $request, Closure $next, $guestCheck = null): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $this->errorResponse('Token not provided', ['token' => ['Token not provided']], 401);
        }
        Log::info('Checking guest token:', ['token' => $token]);

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $user    = $this->buildUserFromPayload($payload);
            Log::info('User authenticated', ['user' => $user]);

            // Asosiy tekshiruvlar
            if (! $this->validateUserPayload($user)) {
                return $this->errorResponse('Invalid token payload', ['token' => ['Missing required fields']], 401);
            }

            // guestCheck parametri bo‘yicha qo‘shimcha tekshiruv
            if ($guestCheck === 'true' && $user['is_guest'] !== true) {
                return $this->errorResponse('Only guest users allowed', ['token' => ['Only guest users allowed']], 401);
            }
            if ($guestCheck === 'false' && $user['is_guest'] !== false) {
                return $this->errorResponse('Only registered users allowed', ['token' => ['Only registered users allowed']], 401);
            }

            $request->merge(['auth_user' => $user]);
            return $next($request);

        } catch (TokenExpiredException $e) {
            return $this->handleExpiredToken($request, $token, $next, $guestCheck);
        } catch (JWTException $e) {
            return $this->errorResponse('Invalid or malformed token', ['token' => ["Invalid or malformed token"]], 401);
        }
    }

    protected function handleExpiredToken(Request $request, $token, Closure $next, $guestCheck = null): Response
    {
        try {
            $res = Http::withHeaders([
                'Authorization'   => 'Bearer ' . $request->bearerToken(),
                'Accept'          => 'application/json',
                'X-Forwarded-For' => $request->ip(),
                'User-Agent'      => $request->userAgent(),
            ])->post(config('services.urls.auth_service') . '/refresh-token', $request->all());
            $data     = json_decode($res->getBody()->getContents(), true)['data'] ?? [];
            $newToken = $data['token'] ?? null;
            if (! $newToken) {
                return $this->errorResponse('Token refresh failed', ['token' => ['Token refresh failed']], 401);
            }

            $user = collect($data)->except('token')->toArray();
            Log::info('User authenticated after refresh', ['user' => $user]);

            // Asosiy tekshiruvlar
            if (! $this->validateUserPayload($user)) {
                return $this->errorResponse('Invalid token payload', ['token' => ['Missing required fields']], 401);
            }

            // guestCheck parametri bo‘yicha qo‘shimcha tekshiruv
            if ($guestCheck === 'true' && $user['is_guest'] !== true) {
                return $this->errorResponse('Only guest users allowed', ['token' => ['Only guest users allowed']], 401);
            }
            if ($guestCheck === 'false' && $user['is_guest'] !== false) {
                return $this->errorResponse('Only registered users allowed', ['token' => ['Only registered users allowed']], 401);
            }

            $request->merge(['auth_user' => $user]);
            $response = $next($request);

            // Agar JsonResponse bo‘lsa yangi token qo‘shamiz
            if ($response instanceof JsonResponse) {
                $original              = $response->getData(true);
                $original['new_token'] = $newToken;
                return response()->json($original, $response->getStatusCode());
            }

            // fallback
            $content = $response->getContent();
            $json    = json_decode($content, true);
            if (is_array($json)) {
                $json['new_token'] = $newToken;
                return response()->json($json, $response->getStatusCode());
            }

            return $response;
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Token expired and cannot be refreshed',
                ['token' => ['Token expired and cannot be refreshed']],
                401
            );
        }
    }
    protected function buildUserFromPayload($payload): array
    {
        return [
            'id'       => $payload->get('user_id') ?? null,
            'is_guest' => $payload->get('is_guest') ?? null,
            'phone'    => $payload->get('phone') ?? null,
            'ip'       => $payload->get('ip') ?? null,
        ];
    }

    protected function validateUserPayload($user): bool
    {
        return isset($user['id'], $user['is_guest'], $user['phone'], $user['ip']);
    }
}
