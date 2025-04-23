<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {

            // ✅ Validation Errors
            if ($exception instanceof ValidationException) {
                return $this->errorResponse(
                    'Validation error',
                    $exception->errors(),
                    422
                );
            }

            // ✅ JWT Token Expired
            if ($exception instanceof TokenExpiredException) {
                return $this->errorResponse(
                    'Token expired and cannot be refreshed',
                    null,
                    401
                );
            }

            // ✅ JWT Token Invalid
            if ($exception instanceof TokenInvalidException) {
                return $this->errorResponse(
                    'Invalid token',
                    null,
                    401
                );
            }

            // ✅ Unauthenticated
            if ($exception instanceof AuthenticationException) {
                return $this->errorResponse(
                    'Unauthenticated',
                    null,
                    401
                );
            }

            // ✅ Forbidden (Authorization)
            if ($exception instanceof AuthorizationException) {
                return $this->errorResponse(
                    'Forbidden',
                    null,
                    403
                );
            }

            // ✅ Not Found
            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse(
                    'Not Found',
                    null,
                    404
                );
            }

            // ✅ Method Not Allowed
            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse(
                    'Method Not Allowed',
                    null,
                    405
                );
            }

            // ✅ Generic HTTP Exception
            if ($exception instanceof HttpException) {
                return $this->errorResponse(
                    $exception->getMessage(),
                    null,
                    $exception->getStatusCode()
                );
            }

            // ✅ Fallback
            return $this->errorResponse(
                'Internal server error',
                null,
                500
            );
        }

        // Non-JSON request (default Laravel render)
        return parent::render($request, $exception);
    }
}