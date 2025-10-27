<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Get user's organization
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        $organization = Organization::with(['creator', 'users'])->find($user->organization_id);

        return response()->json([
            'success' => true,
            'data' => $organization,
        ]);
    }

    /**
     * Create organization (buyer only)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'buyer') {
            return response()->json([
                'success' => false,
                'message' => 'Only buyers can create organizations',
            ], 403);
        }

        if ($user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'User already belongs to an organization',
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'inn' => 'required|string|max:20',
            'kpp' => 'nullable|string|max:20',
            'ogrn' => 'required|string|max:50',
            'address_legal' => 'required|string',
            'address_actual' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'comment' => 'nullable|string',
        ]);

        $organization = Organization::create(array_merge($validated, [
            'created_by' => $user->id,
        ]));

        $user->organization_id = $organization->id;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $organization,
        ], 201);
    }

    /**
     * Update organization (buyer only)
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'buyer' || $user->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'inn' => 'required|string|max:20',
            'kpp' => 'nullable|string|max:20',
            'ogrn' => 'required|string|max:50',
            'address_legal' => 'required|string',
            'address_actual' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'comment' => 'nullable|string',
        ]);

        $organization->update($validated);

        return response()->json([
            'success' => true,
            'data' => $organization,
        ]);
    }

    /**
     * Generate invite link for employees
     */
    public function generateInvite(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'buyer' || !$user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only buyers with organizations can generate invites',
            ], 403);
        }

        $token = Str::random(32);
        $expiresAt = now()->addDays(7);

        $invite = Invite::create([
            'organization_id' => $user->organization_id,
            'token' => $token,
            'created_by' => $user->id,
            'expires_at' => $expiresAt,
        ]);

        $url = config('app.frontend_url') . '/join?token=' . $token;

        return response()->json([
            'success' => true,
            'data' => [
                'invite_url' => $url,
                'token' => $token,
                'expires_at' => $expiresAt->toISOString(),
            ],
        ]);
    }
}
