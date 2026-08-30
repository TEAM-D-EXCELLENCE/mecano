<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'mecanicien@garage.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mecanicien@garage.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'mecanicien@garage.com',
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_revokes_previous_tokens_ensuring_single_active_token(): void
    {
        $user = User::factory()->create([
            'email' => 'mecanicien@garage.com',
            'password' => Hash::make('secret123'),
        ]);

        // First login
        $firstResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'mecanicien@garage.com',
            'password' => 'secret123',
        ]);
        $firstResponse->assertOk();
        $firstToken = $firstResponse->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        // Second login
        $secondResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'mecanicien@garage.com',
            'password' => 'secret123',
        ]);
        $secondResponse->assertOk();
        $secondToken = $secondResponse->json('token');

        $this->assertNotEquals($firstToken, $secondToken);
        // Only 1 token must remain in database (previous revoked)
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'mecanicien@garage.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mecanicien@garage.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Identifiants incorrects.',
                ],
            ]);
    }

    public function test_login_fails_when_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@garage.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Identifiants incorrects.',
                ],
            ]);
    }

    public function test_login_validation_errors_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => [
                        'email',
                        'password',
                    ],
                ],
            ])
            ->assertJson([
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                ],
            ]);
    }

    public function test_login_rate_limiting_blocks_after_max_attempts(): void
    {
        User::factory()->create([
            'email' => 'mecanicien@garage.com',
            'password' => Hash::make('secret123'),
        ]);

        // 5 allowed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'mecanicien@garage.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mecanicien@garage.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'error' => [
                    'code' => 'RATE_LIMITED',
                ],
            ]);
    }
}
