<?php

declare(strict_types=1);

namespace App\Actions\Admin\Services;

use App\Http\Requests\Admin\Services\CreateServiceRequest;
use App\Models\Service;
use Illuminate\Support\Str;

final readonly class CreateService
{
    public function handle(CreateServiceRequest $request): Service
    {
        $title = (string) $request->validated('title');
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Service::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        /** @var Service $service */
        $service = Service::query()->create([
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $request->validated('excerpt'),
            'description' => $request->validated('description'),
            'icon' => $request->validated('icon'),
            'price_from_xaf' => $request->validated('price_from_xaf'),
            'is_active' => $request->boolean('is_active', true),
            'position' => (int) $request->validated('position', 0),
        ]);

        return $service;
    }
}
