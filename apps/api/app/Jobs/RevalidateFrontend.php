<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RevalidateFrontend implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60, 120, 300];

    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        public readonly array $tags,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tags = array_values(array_unique(array_filter($this->tags)));

        if ($tags === []) {
            return;
        }

        $url = (string) config('services.frontend.revalidate_url', 'http://localhost:3000/api/revalidate');
        $secret = (string) config('services.frontend.revalidate_secret', '');

        $payload = [
            'tags' => $tags,
            'timestamp' => now()->timestamp,
        ];

        $bodyJson = (string) json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $bodyJson, $secret);

        Http::withHeaders([
            'X-Revalidate-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout(5)
            ->withBody($bodyJson, 'application/json')
            ->post($url)
            ->throw();
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::alert('Revalidation webhook failed permanently for tags: '.implode(', ', $this->tags), [
            'tags' => $this->tags,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
