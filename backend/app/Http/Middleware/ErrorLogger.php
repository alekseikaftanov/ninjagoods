<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ErrorLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Логируем ошибки клиента (4xx) и сервера (5xx)
            if ($response->getStatusCode() >= 400) {
                Log::error('API Error Response', [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'status_code' => $response->getStatusCode(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request_body' => $request->all(),
                    'response_content' => $response->getContent(),
                    'timestamp' => now()
                ]);
            }
            
            return $response;
        } catch (\Exception $e) {
            // Логируем исключения
            Log::error('API Exception', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_body' => $request->all(),
                'timestamp' => now()
            ]);
            
            throw $e;
        }
    }
}
