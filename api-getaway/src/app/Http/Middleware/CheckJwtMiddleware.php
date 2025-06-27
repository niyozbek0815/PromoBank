<?php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckJwtMiddleware
{
    public function handle(Request $request, Closure $next, $requiredRoles = null): Response
    {
        // 1. Token mavjudligini tekshirish
        $token = Session::get('jwt_token');
        if (! $token) {
            Log::warning('No token in session');
            return redirect()->route('admin.login')->withErrors(['error' => 'Please login.']);
        }

                                                              // 2. User roli sessiondan olish
        $userRoles = collect(Session::get('user_roles', [])); // E.g: ['admin']

        // 3. Token verify qilish kerakmi?
        try {
            if ($this->shouldVerifyToken()) {
                Log::info('Token verification required');
                $this->verifyTokenWithAuthService($token, $userRoles);
            }
        } catch (\Exception $e) {
            Log::error('Auth service error during token verify', [
                'message' => $e->getMessage(),
            ]);
            // Session::flush();
            return redirect()->route('admin.login')->withErrors(['error' => 'Session expired or auth service error. Please login again.']);
        }

        if (! empty($requiredRoles)) {
            $allowedRoles = collect(explode(',', $requiredRoles))->map(fn($r) => trim($r));
        } else {
            $allowedRoles = collect(['super_admin', 'admin', 'content_manager', 'developer', 'client']);
        }

        if ($userRoles->intersect($allowedRoles)->isEmpty()) {
            Log::warning('Access denied. Required roles: ' . $allowedRoles->implode(', ') . '. User roles: ' . $userRoles->implode(', '));
            return redirect()->route('admin.login')->withErrors(['error' => 'Access denied: insufficient permissions.']);
        }
        Log::info('Access granted', [
            'required_roles' => $allowedRoles->implode(', '),
            'user_roles'     => $userRoles->implode(', '),
        ]);

        return $next($request);
    }

    protected function verifyTokenWithAuthService(string $token, &$userRoles): void
    {
        $url = config('services.urls.auth_service') . '/front/verify';

        $response = Http::withToken($token)->get($url);

        if (! $response->ok()) {
            Log::error('Token verification failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            // Session::flush();
            throw new \Exception('Auth service returned error');
        }
        $responseData = $response->json();
        Log::info('Token verified successfully', [
            'response' => $responseData,
        ]);
        Session::put('jwt_token', $responseData['token']);
        if (isset($responseData['roles'])) {
            Session::put('user_roles', $responseData['roles']);
            $userRoles = collect($responseData['roles']);
        }
        Session::put('token_last_verified_at', now());
    }
    protected function shouldVerifyToken(): bool
    {
        $lastVerifiedAt = Session::get('token_last_verified_at');

        if (! $lastVerifiedAt) {
            return false;
        }

        $minutesDiff = Carbon::parse($lastVerifiedAt)->diffInMinutes();

        Log::info('Token verification check', [
            'last_verified_at' => $lastVerifiedAt,
            'diff_minutes'     => $minutesDiff,
        ]);

        return $minutesDiff >= 1;
    }
}