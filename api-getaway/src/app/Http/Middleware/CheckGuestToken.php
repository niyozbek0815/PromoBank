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

    public function handle(Request $request, Closure $next, $guestCheck = null): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $this->errorResponse('Token not provided', ['token' => ['Token not provided']], 401);
        }

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $user    = $this->buildUserFromPayload($payload);

            return $this->validateAccess($request, $next, $user, $guestCheck);

        } catch (TokenExpiredException $e) {
            return $this->handleExpiredToken($request, $next, $guestCheck, $token);
        } catch (JWTException $e) {
            return $this->errorResponse('Invalid or malformed token', ['token' => ['Invalid or malformed token']], 401);
        }
    }

    private function handleExpiredToken(Request $request, Closure $next, ?string $guestCheck, string $token): Response
    {
        try {
            $res = Http::withHeaders([
                'Authorization'   => "Bearer {$token}",
                'Accept'          => 'application/json',
                'X-Forwarded-For' => $request->ip(),
                'User-Agent'      => $request->userAgent(),
            ])->post(config('services.urls.auth_service') . '/refresh-token', $request->all());

            $data     = $res->json('data', []);
            $newToken = $data['token'] ?? null;

            if (! $newToken) {
                return $this->errorResponse('Token refresh failed', ['token' => ['Token refresh failed']], 401);
            }

            $user     = collect($data)->except('token')->toArray();
            $response = $this->validateAccess($request, $next, $user, $guestCheck);

            // agar JsonResponse bo‘lsa yangi tokenni qo‘shamiz
            if ($response instanceof JsonResponse) {
                $original              = $response->getData(true);
                $original['new_token'] = $newToken;
                return response()->json($original, $response->getStatusCode());
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

    private function validateAccess(Request $request, Closure $next, array $user, ?string $guestCheck): Response
    {
        if (! $this->validateUserPayload($user)) {
            return $this->errorResponse('Invalid token payload', ['token' => ['Missing required fields']], 401);
        }

        if ($guestCheck === 'true' && $user['is_guest'] !== true) {
            return $this->errorResponse('Only guest users allowed', ['token' => ['Only guest users allowed']], 401);
        }

        if ($guestCheck === 'false' && $user['is_guest'] !== false) {
            return $this->errorResponse('Only registered users allowed', ['token' => ['Only registered users allowed']], 401);
        }

        Log::info('User authenticated', ['user' => $user]);
    $request->merge(['auth_user' => $user]);
        return $next($request);
    }

    private function buildUserFromPayload($payload): array
    {
        return [
            'id'       => $payload->get('user_id'),
            'is_guest' => $payload->get('is_guest'),
            'phone'    => $payload->get('phone'),
            'ip'       => $payload->get('ip'),
        ];
    }

    private function validateUserPayload(array $user): bool
    {
        return isset($user['id'], $user['is_guest'], $user['phone'], $user['ip']);
    }
}
