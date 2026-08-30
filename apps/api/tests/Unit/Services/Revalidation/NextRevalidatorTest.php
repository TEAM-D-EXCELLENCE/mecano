<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Revalidation;

use App\Jobs\RevalidateFrontend;
use App\Services\Revalidation\NextRevalidator;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class NextRevalidatorTest extends TestCase
{
    #[Test]
    public function it_dispatches_revalidate_frontend_job_with_tags(): void
    {
        Queue::fake();

        $revalidator = new NextRevalidator;
        $revalidator->revalidate(['cars', 'car:toyota-corolla-2020-1', 'home']);

        Queue::assertPushed(RevalidateFrontend::class, function (RevalidateFrontend $job) {
            return $job->tags === ['cars', 'car:toyota-corolla-2020-1', 'home'];
        });
    }

    #[Test]
    public function it_does_not_dispatch_job_when_tags_are_empty(): void
    {
        Queue::fake();

        $revalidator = new NextRevalidator;
        $revalidator->revalidate([]);

        Queue::assertNothingPushed();
    }
}
