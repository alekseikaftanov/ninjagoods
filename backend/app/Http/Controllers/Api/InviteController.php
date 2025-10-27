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
     * Join restaurant using invite token
     */
    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|exists:invites,token',
        ]);

        $invite = Invite::where('token', $request->token)->with('restaurant')->first();

        if (!$invite->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invite expired or already used',
            ], 400);
        }

        $user = $request->user();

        if (!$user->isEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only employees can join restaurants',
            ], 400);
        }

        $restaurant = $invite->restaurant;

        // Check if already a member
        if ($restaurant->hasEmployee($user)) {
            return response()->json([
                'success' => false,
                'message' => 'User already belongs to this restaurant',
            ], 400);
        }

        // Add user to restaurant
        $restaurant->addEmployee($user);

        // Mark invite as used
        $invite->markAsUsed();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Successfully joined restaurant',
                'restaurant' => $restaurant,
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

        $invite = Invite::where('token', $request->token)->with('restaurant')->first();

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
                'restaurant' => $invite->restaurant,
            ],
        ]);
    }

    /**
     * Generate invite for restaurant (only owner)
     */
    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isBuyer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only buyers can generate invites',
            ], 403);
        }

        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'expires_in_days' => 'nullable|integer|min:1|max:30',
        ]);

        $restaurant = \App\Models\Restaurant::findOrFail($request->restaurant_id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not own this restaurant',
            ], 403);
        }

        $invite = Invite::create([
            'restaurant_id' => $restaurant->id,
            'token' => \Illuminate\Support\Str::random(32),
            'created_by' => $user->id,
            'expires_at' => now()->addDays($request->expires_in_days ?? 7),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'invite' => $invite,
                'link' => config('app.frontend_url') . '/join?token=' . $invite->token,
            ],
        ], 201);
    }

    /**
     * Get all invites for restaurant
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isBuyer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only buyers can view invites',
            ], 403);
        }

        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        $restaurant = \App\Models\Restaurant::findOrFail($request->restaurant_id);

        if (!$restaurant->isOwnedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not own this restaurant',
            ], 403);
        }

        $invites = $restaurant->invites()->get();

        return response()->json([
            'success' => true,
            'data' => $invites,
        ]);
    }
}
