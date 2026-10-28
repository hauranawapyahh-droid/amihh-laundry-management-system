<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            Log::error('JWT middleware error', [
                'exception' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau tidak ditemukan',
            ], 401);
        }

        return $next($request);
    }
    
}
