<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Car;
use App\Models\IntegrationQuota;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoint_requires_auth(): void
    {
        $this->getJson('/api/v1/admin/dashboard')->assertStatus(401);
    }

    public function test_dashboard_returns_aggregated_metrics(): void
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('admin')->plainTextToken;

        $toyota = Brand::factory()->create(['name' => 'Toyota']);

        // 1. Create Cars in different statuses
        Car::factory()->available()->create([
            'brand_id' => $toyota->id,
            'model' => 'Corolla 2020',
            'views_count' => 100,
            'whatsapp_clicks_count' => 20,
        ]);

        Car::factory()->available()->create([
            'brand_id' => $toyota->id,
            'model' => 'Yaris 2019',
            'views_count' => 50,
            'whatsapp_clicks_count' => 5,
        ]);

        Car::factory()->reserved()->create([
            'brand_id' => $toyota->id,
            'views_count' => 80,
            'whatsapp_clicks_count' => 10,
        ]);

        Car::factory()->sold()->create([
            'brand_id' => $toyota->id,
            'published_at' => now()->subDays(10),
            'sold_at' => now(),
            'views_count' => 200,
            'whatsapp_clicks_count' => 40,
        ]);

        Car::factory()->draft()->create([
            'brand_id' => $toyota->id,
            'views_count' => 0,
            'whatsapp_clicks_count' => 0,
        ]);

        // 2. Create Services & Posts
        $service1 = Service::factory()->active()->create();
        $service2 = Service::factory()->active()->create();
        $service3 = Service::factory()->active()->create();
        $service4 = Service::factory()->inactive()->create();

        Post::factory()->published()->count(2)->create(['service_id' => $service1->id]);
        Post::factory()->draft()->count(2)->create(['service_id' => $service2->id]);

        // 3. Set Quota
        $period = now()->format('Y-m');
        IntegrationQuota::query()->create([
            'provider' => 'removebg',
            'period' => $period,
            'used' => 15,
            'limit' => 50,
            'updated_at' => now(),
        ]);

        // Request dashboard
        $response = $this->withToken($token)->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.overview.total_cars', 5)
            ->assertJsonPath('data.overview.available_cars', 2)
            ->assertJsonPath('data.overview.reserved_cars', 1)
            ->assertJsonPath('data.overview.sold_cars', 1)
            ->assertJsonPath('data.overview.draft_cars', 1)
            ->assertJsonPath('data.engagement.total_views', 430)
            ->assertJsonPath('data.engagement.total_whatsapp_clicks', 75)
            ->assertJsonPath('data.engagement.conversion_rate_percentage', 17.44)
            ->assertJsonPath('data.engagement.average_days_to_sell', 10)
            ->assertJsonPath('data.workshop_and_content.total_services', 4)
            ->assertJsonPath('data.workshop_and_content.active_services', 3)
            ->assertJsonPath('data.workshop_and_content.total_posts', 4)
            ->assertJsonPath('data.workshop_and_content.published_posts', 2)
            ->assertJsonPath('data.quotas.removebg.used', 15)
            ->assertJsonPath('data.quotas.removebg.limit', 50)
            ->assertJsonPath('data.quotas.removebg.available', 35);

        // Top cars by WhatsApp clicks should have the sold car first (40 clicks)
        $topClicks = $response->json('data.top_cars_by_whatsapp_clicks');
        $this->assertCount(5, $topClicks);
        $this->assertEquals(40, $topClicks[0]['whatsapp_clicks_count']);
    }
}
