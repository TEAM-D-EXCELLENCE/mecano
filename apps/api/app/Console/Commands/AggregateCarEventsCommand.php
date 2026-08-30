<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Cars\AggregateCarEvents;
use Illuminate\Console\Command;

final class AggregateCarEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cars:aggregate-events
                            {--retention-months=12 : Number of months to retain detailed events before purging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate views and WhatsApp clicks into cars table counters and purge expired event logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionMonths = (int) $this->option('retention-months');

        $this->info("Starting car events aggregation (retention: {$retentionMonths} months)...");

        $job = new AggregateCarEvents(retentionMonths: $retentionMonths);
        $result = $job->handle();

        $this->info('Aggregation completed successfully:');
        $this->line(" - Cars synchronized: {$result['cars_updated']}");
        $this->line(" - Expired events purged: {$result['events_purged']}");

        return Command::SUCCESS;
    }
}
