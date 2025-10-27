<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Join organization using invite token
     */
    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|exists:invites,token',
        ]);

        $invite = Invite::where('token', $request->token)->first();

        if (!$invite->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invite expired or already used',
            ], 400);
        }

        $user = $request->user();

        if ($user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'User already belongs to an organization',
            ], 400);
        }

        // Link user to organization
        $user->organization_id = $invite->organization_id;
        $user->role = 'employee';
        $user->save();

        // Mark invite as used
        $invite->markAsUsed();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Successfully joined organization',
                'organization' => $user->organization,
            ],
        ]);
    }

    /**
     * Validate invite token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|exists:invites,token',
        ]);

        $invite = Invite::where('token', $request->token)->with('organization')->first();

        if (!$invite->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invite expired or already used',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => true,
                'organization' => $invite->organization,
            ],
        ]);
    }
}
