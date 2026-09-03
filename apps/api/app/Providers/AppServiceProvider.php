<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Media\CloudinaryImageEnhancer;
use App\Services\Media\CloudinaryImageStorage;
use App\Services\Media\CloudinaryVideoStorage;
use App\Services\Media\FakeImageEnhancer;
use App\Services\Media\FakeImageStorage;
use App\Services\Media\FakeVideoStorage;
use App\Services\Media\RemoveBgImageEnhancer;
use App\Services\Revalidation\FakeFrontendRevalidator;
use App\Services\Revalidation\NextRevalidator;
use App\Support\Contracts\FrontendRevalidator;
use App\Support\Contracts\ImageEnhancer;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageStorage::class, static function ($app): ImageStorage {
            if ($app->environment('testing') || config('media.driver') === 'fake') {
                return new FakeImageStorage;
            }

            return CloudinaryImageStorage::fromConfig();
        });

        $this->app->singleton(VideoStorage::class, static function ($app): VideoStorage {
            if ($app->environment('testing') || config('media.driver') === 'fake') {
                return new FakeVideoStorage;
            }

            return CloudinaryVideoStorage::fromConfig();
        });

        $this->app->singleton(ImageEnhancer::class, static function ($app): ImageEnhancer {
            if ($app->environment('testing') || config('media.driver') === 'fake') {
                return new FakeImageEnhancer;
            }

            $cloudinary = new CloudinaryImageEnhancer(
                cloudName: (string) config('media.cloudinary.cloud_name', 'default')
            );

            // Sans clé remove.bg, on reste sur la transformation Cloudinary :
            // le service doit rester démarrable même quand l'intégration
            // payante n'est pas encore ouverte (écart E6).
            if ((string) config('media.removebg.api_key', '') === '') {
                return $cloudinary;
            }

            return RemoveBgImageEnhancer::fromConfig($cloudinary);
        });

        $this->app->singleton(FrontendRevalidator::class, static function ($app): FrontendRevalidator {
            if ($app->environment('testing') || config('services.frontend.driver') === 'fake') {
                return new FakeFrontendRevalidator;
            }

            return new NextRevalidator;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::preventLazyLoading(! $this->app->isProduction());

        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for API endpoints.
     */
    private function configureRateLimiting(): void
    {
        // Login rate limiter: 5 per minute per IP AND 10 per hour per email
        RateLimiter::for('login', static function (Request $request): array {
            $email = (string) $request->input('email', '');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(10)->by($email !== '' ? Str::lower($email) : (string) $request->ip()),
            ];
        });

        // Car events tracking rate limiter: 60 per minute per IP
        RateLimiter::for('car-events', static function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Media upload signature rate limiter: 30 per minute per IP
        RateLimiter::for('upload-signature', static function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
