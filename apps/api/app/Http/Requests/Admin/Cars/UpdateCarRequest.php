<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cars;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCarRequest extends FormRequest
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
        $currentYear = (int) date('Y');

        return [
            'brand_id' => ['sometimes', 'integer', 'exists:brands,id'],
            'model' => ['sometimes', 'string', 'max:120'],
            'year' => ['sometimes', 'integer', 'min:1950', 'max:'.($currentYear + 1)],
            'mileage_km' => ['sometimes', 'integer', 'min:0'],
            'price_xaf' => ['sometimes', 'integer', 'min:0'],
            'fuel' => ['sometimes', 'string', Rule::enum(FuelType::class)],
            'transmission' => ['sometimes', 'string', Rule::enum(TransmissionType::class)],
            'color' => ['sometimes', 'string', 'max:40'],
            'condition' => ['sometimes', 'string', Rule::enum(VehicleCondition::class)],
            'description' => ['nullable', 'string'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'brand_id.exists' => 'La marque sélectionnée est introuvable.',
            'model.max' => 'Le modèle ne peut pas dépasser 120 caractères.',
            'year.min' => 'L\'année ne peut pas être antérieure à 1950.',
            'year.max' => 'L\'année ne peut pas être supérieure à l\'année prochaine.',
            'mileage_km.min' => 'Le kilométrage doit être un nombre positif.',
            'price_xaf.min' => 'Le prix doit être un nombre positif.',
        ];
    }
}
