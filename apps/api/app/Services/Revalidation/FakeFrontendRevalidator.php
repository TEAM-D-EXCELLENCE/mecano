<?php

declare(strict_types=1);

namespace App\Services\Revalidation;

use App\Support\Contracts\FrontendRevalidator;
use PHPUnit\Framework\Assert;

final class FakeFrontendRevalidator implements FrontendRevalidator
{
    /**
     * @var list<string>
     */
    private array $revalidatedTags = [];

    /**
     * @param array<int, string> $tags
     */
    public function revalidate(array $tags): void
    {
        foreach ($tags as $tag) {
            if ($tag !== '' && ! in_array($tag, $this->revalidatedTags, true)) {
                $this->revalidatedTags[] = $tag;
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getRevalidatedTags(): array
    {
        return $this->revalidatedTags;
    }

    public function hasRevalidated(string $tag): bool
    {
        return in_array($tag, $this->revalidatedTags, true);
    }

    /**
     * @param string|list<string> $tags
     */
    public function assertRevalidated(string|array $tags): void
    {
        $tags = is_array($tags) ? $tags : [$tags];

        foreach ($tags as $tag) {
            Assert::assertTrue(
                $this->hasRevalidated($tag),
                "Expected tag [{$tag}] was not revalidated. Revalidated tags were: [" . implode(', ', $this->revalidatedTags) . '].'
            );
        }
    }

    public function assertNothingRevalidated(): void
    {
        Assert::assertEmpty(
            $this->revalidatedTags,
            'Expected no tags to be revalidated, but found: [' . implode(', ', $this->revalidatedTags) . '].'
        );
    }

    public function reset(): void
    {
        $this->revalidatedTags = [];
    }
}
