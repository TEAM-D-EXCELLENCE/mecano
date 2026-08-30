<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use App\Enums\EnhancementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RequestEnhancementRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(EnhancementType::class)],
            'params' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
