<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Enums\MediaKind;
use App\Models\Media;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class PurgeOrphanUploads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $olderThanHours = 24,
    ) {}

    /**
     * Execute the job: purge unconfirmed uploads older than the specified duration.
     *
     * @return int Number of purged orphan uploads
     */
    public function handle(ImageStorage $imageStorage, VideoStorage $videoStorage): int
    {
        $threshold = now()->subHours($this->olderThanHours);

        $orphans = Media::query()
            ->whereNull('confirmed_at')
            ->where('created_at', '<=', $threshold)
            ->get();

        $purgedCount = 0;

        foreach ($orphans as $media) {
            try {
                if ($media->kind === MediaKind::Photo) {
                    $imageStorage->delete($media->storage_key);
                } else {
                    $videoStorage->delete($media->storage_key);
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to delete orphan media from storage: {$media->storage_key}", [
                    'error' => $e->getMessage(),
                ]);
            }

            $media->delete();
            $purgedCount++;
        }

        if ($purgedCount > 0) {
            Log::info("Purged {$purgedCount} orphan media uploads older than {$this->olderThanHours} hours.");
        }

        return $purgedCount;
    }
}
