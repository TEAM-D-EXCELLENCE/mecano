<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Cars;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateCarRequest extends FormRequest
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
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'model' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'min:1950', 'max:'.($currentYear + 1)],
            'mileage_km' => ['required', 'integer', 'min:0'],
            'price_xaf' => ['required', 'integer', 'min:0'],
            'fuel' => ['required', 'string', Rule::enum(FuelType::class)],
            'transmission' => ['required', 'string', Rule::enum(TransmissionType::class)],
            'color' => ['required', 'string', 'max:40'],
            'condition' => ['required', 'string', Rule::enum(VehicleCondition::class)],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::enum(CarStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'brand_id.required' => 'La marque est obligatoire.',
            'brand_id.exists' => 'La marque sélectionnée est introuvable.',
            'model.required' => 'Le modèle est obligatoire.',
            'model.max' => 'Le modèle ne peut pas dépasser 120 caractères.',
            'year.required' => 'L\'année de mise en circulation est obligatoire.',
            'year.min' => 'L\'année ne peut pas être antérieure à 1950.',
            'year.max' => 'L\'année ne peut pas être supérieure à l\'année prochaine.',
            'mileage_km.required' => 'Le kilométrage est obligatoire.',
            'mileage_km.min' => 'Le kilométrage doit être un nombre positif.',
            'price_xaf.required' => 'Le prix en FCFA est obligatoire.',
            'price_xaf.min' => 'Le prix doit être un nombre positif.',
            'fuel.required' => 'Le type de carburant est obligatoire.',
            'transmission.required' => 'Le type de transmission est obligatoire.',
            'color.required' => 'La couleur est obligatoire.',
            'condition.required' => 'L\'état du véhicule est obligatoire.',
        ];
    }
}
