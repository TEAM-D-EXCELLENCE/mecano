<?php

declare(strict_types=1);

namespace App\Services\Revalidation;

use App\Jobs\RevalidateFrontend;
use App\Support\Contracts\FrontendRevalidator;

final class NextRevalidator implements FrontendRevalidator
{
    /**
     * @param  array<int, string>  $tags
     */
    public function revalidate(array $tags): void
    {
        $filteredTags = array_values(array_unique(array_filter($tags)));

        if ($filteredTags === []) {
            return;
        }

        RevalidateFrontend::dispatch($filteredTags);
    }
}
