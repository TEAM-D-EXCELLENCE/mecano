<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Media\PurgeOrphanUploads;
use App\Support\Contracts\ImageStorage;
use App\Support\Contracts\VideoStorage;
use Illuminate\Console\Command;

final class PurgeOrphanUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:purge-orphans {--hours=24 : Purge uploads unconfirmed for more than this number of hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge unconfirmed orphan media uploads from database and external storage providers (Cloudinary / R2).';

    /**
     * Execute the console command.
     */
    public function handle(ImageStorage $imageStorage, VideoStorage $videoStorage): int
    {
        $hours = (int) $this->option('hours');

        $this->info("Purging orphan uploads older than {$hours} hours...");

        $job = new PurgeOrphanUploads($hours);
        $count = $job->handle($imageStorage, $videoStorage);

        $this->info("Done. {$count} orphan media upload(s) purged.");

        return self::SUCCESS;
    }
}
