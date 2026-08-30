<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Posts;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'excerpt' => ['sometimes', 'nullable', 'string', 'max:300'],
            'body' => ['sometimes', 'string'],
            'service_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
            'cover_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', 'string', Rule::enum(PostStatus::class)],
        ];
    }
}
