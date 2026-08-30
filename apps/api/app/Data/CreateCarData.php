<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleCondition;
use App\Http\Requests\Admin\Cars\CreateCarRequest;

final readonly class CreateCarData
{
    public function __construct(
        public int $brandId,
        public string $model,
        public int $year,
        public int $mileageKm,
        public int $priceXaf,
        public FuelType $fuel,
        public TransmissionType $transmission,
        public string $color,
        public VehicleCondition $condition,
        public ?string $description = null,
        public bool $isFeatured = false,
        public CarStatus $status = CarStatus::Draft,
    ) {}

    public static function fromRequest(CreateCarRequest $request): self
    {
        return new self(
            brandId: (int) $request->validated('brand_id'),
            model: (string) $request->validated('model'),
            year: (int) $request->validated('year'),
            mileageKm: (int) $request->validated('mileage_km'),
            priceXaf: (int) $request->validated('price_xaf'),
            fuel: FuelType::from((string) $request->validated('fuel')),
            transmission: TransmissionType::from((string) $request->validated('transmission')),
            color: (string) $request->validated('color'),
            condition: VehicleCondition::from((string) $request->validated('condition')),
            description: $request->validated('description'),
            isFeatured: (bool) ($request->validated('is_featured') ?? false),
            status: $request->filled('status')
                ? CarStatus::from((string) $request->validated('status'))
                : CarStatus::Draft,
        );
    }
}
