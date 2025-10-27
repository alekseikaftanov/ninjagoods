<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Invite;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_create_organization(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
            'organization_id' => null,
        ]);

        $token = $this->generateTestToken($user);

        $orgData = [
            'name' => 'Test Restaurant',
            'legal_name' => 'ООО Тест',
            'inn' => '1234567890',
            'kpp' => '123456789',
            'ogrn' => '1234567890123',
            'address_legal' => 'Москва, ул. Тестовая, 1',
            'address_actual' => 'Москва, ул. Тестовая, 1',
            'phone' => '+79999999999',
            'email' => 'test@example.com',
            'comment' => 'Test comment',
        ];

        $response = $this->postJson('/api/b2b/organization', $orgData, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Restaurant',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'organization_id' => Organization::first()->id,
        ]);
    }

    public function test_buyer_can_generate_invite(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'role' => 'buyer',
            'organization_id' => $organization->id,
        ]);

        $token = $this->generateTestToken($user);

        $response = $this->postJson('/api/b2b/organization/invite', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'invite_url',
                    'token',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('invites', [
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);
    }

    public function test_employee_can_join_via_invite(): void
    {
        $organization = Organization::factory()->create();
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'organization_id' => $organization->id,
        ]);

        $invite = Invite::create([
            'organization_id' => $organization->id,
            'token' => 'test_token_123',
            'created_by' => $buyer->id,
            'expires_at' => now()->addDays(7),
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'organization_id' => null,
        ]);

        $token = $this->generateTestToken($employee);

        $response = $this->postJson('/api/b2b/invites/join', [
            'token' => 'test_token_123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseMissing('invites', [
            'token' => 'test_token_123',
            'used_at' => null,
        ]);
    }

    private function generateTestToken(User $user): string
    {
        $payload = [
            'iss' => url('/'),
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $user->id,
            'telegram_id' => $user->telegram_id,
            'role' => $user->role,
        ];

        return \Firebase\JWT\JWT::encode($payload, config('app.key'), 'HS256');
    }
}

