<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use App\Http\Requests\Admin\Cars\UpdateCarRequest;

final readonly class UpdateCarData
{
    public function __construct(
        public ?int $brandId = null,
        public ?string $model = null,
        public ?int $year = null,
        public ?int $mileageKm = null,
        public ?int $priceXaf = null,
        public ?FuelType $fuel = null,
        public ?TransmissionType $transmission = null,
        public ?string $color = null,
        public ?VehicleCondition $condition = null,
        public ?string $description = null,
        public ?bool $isFeatured = null,
    ) {}

    public static function fromRequest(UpdateCarRequest $request): self
    {
        return new self(
            brandId: $request->has('brand_id') ? (int) $request->validated('brand_id') : null,
            model: $request->has('model') ? (string) $request->validated('model') : null,
            year: $request->has('year') ? (int) $request->validated('year') : null,
            mileageKm: $request->has('mileage_km') ? (int) $request->validated('mileage_km') : null,
            priceXaf: $request->has('price_xaf') ? (int) $request->validated('price_xaf') : null,
            fuel: $request->has('fuel') ? FuelType::from((string) $request->validated('fuel')) : null,
            transmission: $request->has('transmission') ? TransmissionType::from((string) $request->validated('transmission')) : null,
            color: $request->has('color') ? (string) $request->validated('color') : null,
            condition: $request->has('condition') ? VehicleCondition::from((string) $request->validated('condition')) : null,
            description: $request->has('description') ? $request->validated('description') : null,
            isFeatured: $request->has('is_featured') ? (bool) $request->validated('is_featured') : null,
        );
    }
}
