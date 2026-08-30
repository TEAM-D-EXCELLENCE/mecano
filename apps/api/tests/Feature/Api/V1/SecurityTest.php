<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Security headers
    // -------------------------------------------------------------------------

    public function test_health_endpoint_returns_security_headers(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_login_endpoint_returns_security_headers(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'none@example.com',
            'password' => 'wrong',
        ]);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_auth_endpoints_return_security_headers_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    // -------------------------------------------------------------------------
    // APP_DEBUG=false : no stack trace / internal data leakage
    // -------------------------------------------------------------------------

    public function test_api_does_not_expose_stack_trace_on_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);

        // Must follow the standard error envelope
        $response->assertJsonStructure([
            'error' => ['code', 'message', 'details'],
        ]);

        // Must NOT contain any debug traces
        $content = $response->getContent();
        $this->assertStringNotContainsString('Illuminate\\', (string) $content);
        $this->assertStringNotContainsString('Stack trace', (string) $content);
        $this->assertStringNotContainsString('vendor/', (string) $content);
        $this->assertStringNotContainsString('APP_KEY', (string) $content);
    }

    public function test_not_found_route_returns_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/this-route-does-not-exist');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Ressource introuvable.',
                    'details' => null,
                ],
            ]);

        // Ensure no internal path leakage
        $this->assertStringNotContainsString('vendor/', (string) $response->getContent());
    }

    public function test_method_not_allowed_returns_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/auth/login'); // GET instead of POST

        $response->assertStatus(405)
            ->assertJson([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // CORS : allowed_headers restricted (no wildcard)
    // -------------------------------------------------------------------------

    public function test_cors_preflight_accepts_declared_headers(): void
    {
        $response = $this->call(
            'OPTIONS',
            '/api/v1/auth/login',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => config('app.frontend_url', 'http://localhost:3000'),
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
            ]
        );

        // Laravel CORS middleware returns 204 on preflight
        $this->assertContains($response->getStatusCode(), [200, 204]);
    }

    public function test_user_resource_does_not_expose_password(): void
    {
        $user = User::factory()->create([
            'email' => 'mecanicien@garage.com',
            'password' => 'secret-hashed-value',
        ]);

        $token = $user->createToken('admin-session')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/auth/me');

        $response->assertOk();

        $content = (string) $response->getContent();
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('remember_token', $content);
    }
}
