<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Services;

use Illuminate\Foundation\Http\FormRequest;

final class CreateServiceRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:60'],
            'price_from_xaf' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'position' => ['integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la prestation est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 150 caractères.',
            'excerpt.max' => 'L\'extrait ne peut pas dépasser 300 caractères.',
            'price_from_xaf.min' => 'Le prix ne peut pas être négatif.',
        ];
    }
}
