<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderMediaRequest extends FormRequest
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
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'media_ids.required' => 'La liste des identifiants de médias (media_ids) est obligatoire.',
            'media_ids.array' => 'Les identifiants doivent être sous forme de tableau.',
            'media_ids.*.exists' => 'Un des médias spécifiés n\'existe pas.',
        ];
    }
}
