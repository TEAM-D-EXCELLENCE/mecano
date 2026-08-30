<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_check_returns_ok_with_valid_timestamp(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'timestamp' => $response->json('timestamp'),
            ]);

        // Validate timestamp is valid ISO-8601
        $timestamp = $response->json('timestamp');
        $this->assertIsString($timestamp);
        $date = date_create($timestamp);
        $this->assertInstanceOf(DateTimeInterface::class, $date);
    }

    public function test_health_check_does_not_leak_internal_details(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        $this->assertArrayNotHasKey('version', $data);
        $this->assertArrayNotHasKey('php', $data);
        $this->assertArrayNotHasKey('laravel', $data);
        $this->assertArrayNotHasKey('database', $data);
        $this->assertArrayNotHasKey('debug', $data);
    }

    public function test_health_check_returns_503_when_database_fails(): void
    {
        DB::shouldReceive('connection->getPdo')
            ->once()
            ->andThrow(new Exception('Database connection refused'));

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'unhealthy',
            ]);
    }
}
