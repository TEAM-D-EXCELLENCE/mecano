<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Posts;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', 'string', Rule::enum(PostStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de l\'article est obligatoire.',
            'body.required' => 'Le corps de l\'article est obligatoire.',
            'service_id.exists' => 'Le service sélectionné n\'existe pas.',
            'cover_media_id.exists' => 'Le média de couverture sélectionné n\'existe pas.',
        ];
    }
}
