<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create([
            'email' => 'mecanicien@garage.com',
        ]);

        $token = $user->createToken('admin-session')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->withToken($token)
            ->postJson('/api/v1/auth/logout');

        $response->assertNoContent();

        // Token must be removed from database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reset in-memory auth state to verify fresh request
        $this->app['auth']->forgetGuards();

        // Subsequent authenticated request with revoked token must fail
        $subsequentResponse = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $subsequentResponse->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
            ]);
    }

    public function test_logout_fails_when_unauthenticated(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Non authentifié ou jeton invalide.',
                ],
            ]);
    }
}
