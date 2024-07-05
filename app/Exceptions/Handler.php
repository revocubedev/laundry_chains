<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $e) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->validator->errors()->first(),
                ], 400);
            }

            if (
                $e instanceof TokenExpiredException ||
                $e instanceof TokenBlacklistedException
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token has expired'
                ], 401);
            }

            if ($e instanceof TokenInvalidException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token is invalid'
                ], 401);
            }

            if ($e instanceof NotFoundException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 404);
            }

            if ($e instanceof BadRequestException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 400);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if (env('APP_ENV') === 'production') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Internal Server Error',
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        });
    }
}
