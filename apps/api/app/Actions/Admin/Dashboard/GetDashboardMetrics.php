<?php

declare(strict_types=1);

namespace App\Actions\Admin\Dashboard;

use App\Enums\CarStatus;
use App\Enums\PostStatus;
use App\Models\Car;
use App\Models\IntegrationQuota;
use App\Models\Post;
use App\Models\Service;

final readonly class GetDashboardMetrics
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        // 1. Inventory counts
        $statusCounts = Car::query()
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status');

        $availableCars = (int) ($statusCounts->get(CarStatus::Available->value) ?? 0);
        $reservedCars = (int) ($statusCounts->get(CarStatus::Reserved->value) ?? 0);
        $soldCars = (int) ($statusCounts->get(CarStatus::Sold->value) ?? 0);
        $draftCars = (int) ($statusCounts->get(CarStatus::Draft->value) ?? 0);
        $totalCars = $availableCars + $reservedCars + $soldCars + $draftCars;

        // 2. Aggregate views and WhatsApp clicks
        $totalViews = (int) Car::query()->sum('views_count');
        $totalWhatsappClicks = (int) Car::query()->sum('whatsapp_clicks_count');
        $conversionRate = $totalViews > 0
            ? round(($totalWhatsappClicks / $totalViews) * 100, 2)
            : 0.0;

        // 3. Average days to sell (for sold cars having both published_at and sold_at)
        $soldCarsWithDates = Car::query()
            ->where('status', CarStatus::Sold)
            ->whereNotNull('published_at')
            ->whereNotNull('sold_at')
            ->get(['published_at', 'sold_at']);

        $averageDaysToSell = $soldCarsWithDates->isNotEmpty()
            ? round((float) $soldCarsWithDates->avg(fn (Car $car) => $car->published_at->diffInDays($car->sold_at)), 1)
            : null;

        // 4. Top performing cars by WhatsApp clicks (indicator of purchase intent)
        $topCarsByClicks = Car::query()
            ->with(['brand', 'mainPhoto'])
            ->orderByDesc('whatsapp_clicks_count')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get()
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'slug' => $car->slug,
                'brand' => $car->brand?->name,
                'model' => $car->model,
                'year' => $car->year,
                'price_xaf' => $car->price_xaf,
                'status' => $car->status->toArray(),
                'whatsapp_clicks_count' => $car->whatsapp_clicks_count,
                'views_count' => $car->views_count,
                'main_photo_url' => $car->mainPhoto?->published_url ?? $car->mainPhoto?->url,
            ]);

        // 5. Top performing cars by Views
        $topCarsByViews = Car::query()
            ->with(['brand', 'mainPhoto'])
            ->orderByDesc('views_count')
            ->orderByDesc('whatsapp_clicks_count')
            ->limit(5)
            ->get()
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'slug' => $car->slug,
                'brand' => $car->brand?->name,
                'model' => $car->model,
                'year' => $car->year,
                'price_xaf' => $car->price_xaf,
                'status' => $car->status->toArray(),
                'views_count' => $car->views_count,
                'whatsapp_clicks_count' => $car->whatsapp_clicks_count,
                'main_photo_url' => $car->mainPhoto?->published_url ?? $car->mainPhoto?->url,
            ]);

        // 6. Workshop & Content summaries
        $totalServices = Service::query()->count();
        $activeServices = Service::query()->where('is_active', true)->count();
        $totalPosts = Post::query()->count();
        $publishedPosts = Post::query()->where('status', PostStatus::Published)->count();

        // 7. Remove.bg Quotas
        $period = now()->format('Y-m');
        $quota = IntegrationQuota::query()
            ->where('provider', 'removebg')
            ->where('period', $period)
            ->first();

        $removeBgQuota = [
            'period' => $period,
            'used' => $quota?->used ?? 0,
            'limit' => $quota?->limit ?? 50,
            'available' => max(0, ($quota?->limit ?? 50) - ($quota?->used ?? 0)),
        ];

        return [
            'overview' => [
                'total_cars' => $totalCars,
                'available_cars' => $availableCars,
                'reserved_cars' => $reservedCars,
                'sold_cars' => $soldCars,
                'draft_cars' => $draftCars,
            ],
            'engagement' => [
                'total_views' => $totalViews,
                'total_whatsapp_clicks' => $totalWhatsappClicks,
                'conversion_rate_percentage' => $conversionRate,
                'average_days_to_sell' => $averageDaysToSell,
            ],
            'top_cars_by_whatsapp_clicks' => $topCarsByClicks,
            'top_cars_by_views' => $topCarsByViews,
            'workshop_and_content' => [
                'total_services' => $totalServices,
                'active_services' => $activeServices,
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
            ],
            'quotas' => [
                'removebg' => $removeBgQuota,
            ],
        ];
    }
}
