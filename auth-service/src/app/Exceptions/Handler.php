<?php
namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    public function register(): void
    {
        // Common reportables
        $this->reportable(function (Throwable $e) {
            // log($e); // optional
        });
    }

    // ✅ Laravel 11 uchun override qilingan method
    protected function invalidJson($request, ValidationException $exception)
    {
        return $this->errorResponse(
            'Validatsiya xatoligi',
            $exception->errors(),
            $exception->status
        );
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {

            return match (true) {
                $exception instanceof TokenExpiredException =>
                $this->errorResponse('Token expired', null, 401),

                $exception instanceof TokenInvalidException =>
                $this->errorResponse('Invalid token', null, 401),

                $exception instanceof AuthenticationException =>
                $this->errorResponse('Unauthenticated', null, 401),

                $exception instanceof AuthorizationException =>
                $this->errorResponse('Forbidden', null, 403),

                $exception instanceof NotFoundHttpException =>
                $this->errorResponse('Resource not found', null, 404),

                $exception instanceof MethodNotAllowedHttpException =>
                $this->errorResponse('Method not allowed', null, 405),

                $exception instanceof HttpException =>
                $this->errorResponse($exception->getMessage(), null, $exception->getStatusCode()),

                default =>
                $this->errorResponse('Internal server error', null, 500),
            };
        }

        return parent::render($request, $exception);
    }
}