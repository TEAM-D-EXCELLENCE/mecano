<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use App\Enums\MediaRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ConfirmMediaUploadRequest extends FormRequest
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
            'storage_key' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::enum(MediaRole::class)],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'bytes' => ['nullable', 'integer', 'min:1'],
            'mime' => ['nullable', 'string', 'max:60'],
            'alt' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'storage_key.required' => 'La clé de stockage (storage_key) est obligatoire.',
            'role.required' => 'Le rôle du média est obligatoire.',
            'role.enum' => 'Le rôle du média doit être l\'une des valeurs suivantes : main, gallery, video_interior, video_exterior.',
        ];
    }
}
