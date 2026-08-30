<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_authenticated_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Mécanicien Admin',
            'email' => 'mecanicien@garage.com',
        ]);

        $token = $user->createToken('admin-session')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Mécanicien Admin',
                    'email' => 'mecanicien@garage.com',
                    'created_at' => $user->created_at?->toIso8601String(),
                ],
            ]);
    }

    public function test_me_fails_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Non authentifié ou jeton invalide.',
                ],
            ]);
    }

    public function test_me_fails_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token-12345')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Non authentifié ou jeton invalide.',
                ],
            ]);
    }
}
