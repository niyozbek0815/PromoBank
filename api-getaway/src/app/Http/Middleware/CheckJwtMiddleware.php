<?php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckJwtMiddleware
{
    public function handle(Request $request, Closure $next, $requiredRoles = null): Response
    {
        // 1. Token mavjudligini tekshirish
        $token = Session::get('jwt_token');
        if (! $token) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Please register to access the admin panel.']);
        }

                                                              // 2. User roli sessiondan olish
        $userRoles = collect(Session::get('user_roles', [])); // E.g: ['admin']

        // 3. Token verify qilish kerakmi?
        try {
            if ($this->shouldVerifyToken()) {
                $this->verifyTokenWithAuthService($token, $userRoles);
            }
        } catch (\Exception $e) {
            Session::flush();
            return redirect()->route('admin.login')->withErrors(['error' => 'Session expired or auth service error. Please login again.']);
        }

        if (! empty($requiredRoles)) {
            $allowedRoles = collect(explode(',', $requiredRoles))->map(fn($r) => trim($r));
        } else {
            $allowedRoles = collect(['super_admin', 'admin', 'content_manager', 'developer', 'client']);
        }

        if ($userRoles->intersect($allowedRoles)->isEmpty()) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Access denied: insufficient permissions.']);
        }

        return $next($request);
    }

    protected function verifyTokenWithAuthService(string $token, &$userRoles): void
    {
        $url = config('services.urls.auth_service') . '/front/verify';

        $response = Http::withToken($token)->get($url);

        if (! $response->ok()) {
            Session::flush();
            throw new \Exception('Auth service returned error');
        }
        $responseData = $response->json();
        Session::put('jwt_token', $responseData['token']);
        if (isset($responseData['roles'])) {
            Session::put('user_roles', $responseData['roles']);
            $userRoles = collect($responseData['roles']);
        }
        if (isset($responseData['user'])) {
            Session::put('user', $responseData['user']);
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
        return $minutesDiff >= 15;
    }
}