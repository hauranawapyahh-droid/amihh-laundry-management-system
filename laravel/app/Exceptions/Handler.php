<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            // Biarkan Laravel menangani validasi agar status tetap 422.
            if ($e instanceof ValidationException) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $message = $status >= 500
                ? 'Terjadi kesalahan pada server.'
                : $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        });
    }
}
