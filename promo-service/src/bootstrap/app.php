<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ✅ API response helper
        $errorResponse = function (string $message, $errors = null, int $status = 400) {
            return response()->json([
                'success'   => false,
                'message'   => $message,
                'data'      => null,
                'errors'    => $errors,
                'new_token' => null,
            ], $status);
        };

        // ➤ Only handle for API
        $onlyApi = fn($request) => $request->is('api/*');

        $exceptions->renderable(function (ValidationException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Validatsiya xatoligi', $e->errors(), 422);
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Unauthenticated', null, 401);
        });

        $exceptions->renderable(function (AuthorizationException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Ruxsat etilmagan', null, 403);
        });

        $exceptions->renderable(function (TokenExpiredException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Token muddati tugagan', null, 401);
        });

        $exceptions->renderable(function (TokenInvalidException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Token noto‘g‘ri', null, 401);
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Resurs topilmadi', null, 404);
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Ushbu metodga ruxsat yo‘q', null, 405);
        });

        $exceptions->renderable(function (HttpException $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse($e->getMessage() ?: 'HTTP xatolik', null, $e->getStatusCode());
        });

        // ❗️Fallback for unknown exceptions (api only)
        $exceptions->renderable(function (Throwable $e, $request) use ($errorResponse, $onlyApi) {
            if (! $onlyApi($request)) {
                return null;
            }

            return $errorResponse('Ichki server xatosi', null, 500);
        });
    })->create();