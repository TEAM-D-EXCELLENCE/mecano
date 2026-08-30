<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\RevalidateFrontend;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RevalidateFrontendTest extends TestCase
{
    #[Test]
    public function it_sends_signed_hmac_post_request_to_frontend_revalidate_endpoint(): void
    {
        Http::fake([
            'http://localhost:3000/api/revalidate' => Http::response(['revalidated' => true], 200),
        ]);

        Config::set('services.frontend.revalidate_url', 'http://localhost:3000/api/revalidate');
        Config::set('services.frontend.revalidate_secret', 'secret-key-123');

        $job = new RevalidateFrontend(['car:corolla-42', 'cars', 'home']);
        $job->handle();

        Http::assertSent(function (Request $request) {
            $secret = 'secret-key-123';
            $body = $request->body();
            $data = json_decode($body, true);

            $expectedSignature = hash_hmac('sha256', $body, $secret);

            return $request->url() === 'http://localhost:3000/api/revalidate'
                && $request->method() === 'POST'
                && $request->header('X-Revalidate-Signature')[0] === $expectedSignature
                && isset($data['tags'])
                && $data['tags'] === ['car:corolla-42', 'cars', 'home']
                && isset($data['timestamp']);
        });
    }

    #[Test]
    public function it_logs_alert_when_job_fails_permanently(): void
    {
        Log::shouldReceive('alert')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, 'Revalidation webhook failed permanently')
                    && $context['tags'] === ['car:corolla-42'];
            });

        $job = new RevalidateFrontend(['car:corolla-42']);
        $job->failed(new \RuntimeException('Connection refused'));
    }
}
