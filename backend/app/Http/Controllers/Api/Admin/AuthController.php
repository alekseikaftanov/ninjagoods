<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // Логируем попытку входа
        Log::info('Admin login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Для MVP, простая проверка
        if ($request->email === 'admin@ninjagoods.com' && $request->password === 'admin123') {
            $token = 'admin_token_' . time();
            
            // Логируем успешный вход
            Log::info('Admin login successful', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'token_generated' => true,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => ['id' => 1, 'email' => $request->email, 'name' => 'Admin'],
            ]);
        }
        
        // Логируем неудачную попытку входа
        Log::warning('Admin login failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'reason' => 'Invalid credentials',
            'timestamp' => now()
        ]);
        
        return response()->json(['success' => false, 'message' => 'Неверные учетные данные'], 401);
    }

    public function logout(): JsonResponse
    {
        // Логируем выход
        Log::info('Admin logout', [
            'timestamp' => now()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Успешный выход из системы']);
    }
}