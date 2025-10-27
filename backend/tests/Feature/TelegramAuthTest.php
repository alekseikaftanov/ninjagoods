<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class TelegramAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_auth_creates_new_user(): void
    {
        $telegramData = [
            'id' => 123456789,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'auth_date' => time(),
            'hash' => 'test_hash', // In production this should be properly signed
        ];

        $response = $this->postJson('/api/b2b/auth/telegram', $telegramData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'telegram_id' => 123456789,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
        ]);
    }

    public function test_set_role_as_buyer(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 123456,
            'role' => null,
        ]);

        $token = $this->generateTestToken($user);

        $response = $this->postJson('/api/b2b/auth/role', [
            'role' => 'buyer',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'buyer',
        ]);
    }

    public function test_set_role_as_employee(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 123456,
            'role' => null,
        ]);

        $token = $this->generateTestToken($user);

        $response = $this->postJson('/api/b2b/auth/role', [
            'role' => 'employee',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'employee',
        ]);
    }

    public function test_get_current_user(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 123456,
            'role' => 'buyer',
        ]);

        $token = $this->generateTestToken($user);

        $response = $this->getJson('/api/b2b/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
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

