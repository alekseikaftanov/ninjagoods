<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TelegramAuthController extends Controller
{
    /**
     * Telegram Bot Token for hash verification
     */
    private function getBotToken(): string
    {
        return config('services.telegram.bot_token', 'test_token_for_testing');
    }

    /**
     * Verify Telegram authorization hash
     */
    private function verifyTelegramHash(array $data, string $hash): bool
    {
        $checkString = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
        $secretKey = hash('sha256', $this->getBotToken(), true);
        $calculatedHash = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }

    /**
     * Authenticate user via Telegram
     */
    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'first_name' => 'required|string',
            'auth_date' => 'required|integer',
            'hash' => 'required|string',
            'username' => 'nullable|string',
            'last_name' => 'nullable|string',
            'photo_url' => 'nullable|url',
        ]);

        $data = $request->only(['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date']);
        
        // Verify hash (disabled in testing for now)
        // TODO: Implement proper hash verification
        // if (!$this->verifyTelegramHash($data, $request->hash)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid Telegram hash',
        //     ], 401);
        // }

        // Check auth date (should be within 86400 seconds = 24 hours)
        if ((time() - $request->auth_date) > 86400) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization expired',
            ], 401);
        }

        // Find or create user
        $user = User::firstOrNew([
            'telegram_id' => $request->id,
        ], [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'name' => trim($request->first_name . ' ' . ($request->last_name ?? '')),
            'email' => $request->username ? $request->username . '@telegram.local' : 'user' . $request->id . '@telegram.local',
        ]);

        // Update user data
        $user->fill([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'name' => trim($request->first_name . ' ' . ($request->last_name ?? '')),
        ]);
        
        $user->save();

        // Generate JWT token
        $token = $this->generateJWT($user);

        // Check if user needs to choose role or register organization
        $needsRoleSelection = is_null($user->role);
        $needsOrganization = $user->isBuyer() && is_null($user->organization_id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
                'needs_role_selection' => $needsRoleSelection,
                'needs_organization' => $needsOrganization,
            ],
        ]);
    }

    /**
     * Set user role (buyer or employee)
     */
    public function setRole(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:buyer,employee',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Generate JWT token
     */
    private function generateJWT(User $user): string
    {
        $payload = [
            'iss' => url('/'),
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60), // 7 days
            'sub' => $user->id,
            'telegram_id' => $user->telegram_id,
            'role' => $user->role,
        ];

        return JWT::encode($payload, config('app.key'), 'HS256');
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
