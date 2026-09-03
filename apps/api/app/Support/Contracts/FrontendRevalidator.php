<?php

declare(strict_types=1);

namespace App\Support\Contracts;

interface FrontendRevalidator
{
    /**
     * Revalidate cache for the given ISR tags.
     *
     * @param  array<int, string>  $tags
     */
    public function revalidate(array $tags): void;
}
