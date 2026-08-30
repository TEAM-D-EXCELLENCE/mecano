<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Brands;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:60', 'unique:brands,slug'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la marque est obligatoire.',
            'name.max' => 'Le nom de la marque ne peut pas dépasser 80 caractères.',
            'slug.unique' => 'Ce slug de marque est déjà utilisé.',
            'slug.max' => 'Le slug ne peut pas dépasser 60 caractères.',
            'logo_url.url' => "L'URL du logo doit être une URL valide.",
            'position.integer' => 'La position doit être un entier positif.',
            'is_active.boolean' => 'Le statut actif doit être un booléen.',
        ];
    }
}
