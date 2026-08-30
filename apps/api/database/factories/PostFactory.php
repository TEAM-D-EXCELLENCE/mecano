<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(4, true),
            'cover_media_id' => null,
            'service_id' => Service::factory(),
            'author_id' => User::factory(),
            'status' => PostStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function published(): self
    {
        return $this->state(fn () => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDays(2),
        ]);
    }

    public function draft(): self
    {
        return $this->state(fn () => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }
}
