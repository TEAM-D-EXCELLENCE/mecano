<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\CarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RecordCarEventRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(CarEventType::class)],
            'referer' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type d\'événement est obligatoire.',
            'type.enum' => 'Le type d\'événement doit être "view" ou "whatsapp_click".',
            'referer.max' => 'Le referer ne peut pas dépasser 255 caractères.',
        ];
    }
}
