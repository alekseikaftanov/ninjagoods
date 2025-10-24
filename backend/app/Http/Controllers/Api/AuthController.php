<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Авторизация пользователя через Telegram ID
     */
    public function telegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $request->telegram_id],
            [
                'name' => $request->name,
                'phone' => $request->phone,
            ]
        );

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }
}
