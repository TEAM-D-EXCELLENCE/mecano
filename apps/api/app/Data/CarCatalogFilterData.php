<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use Illuminate\Http\Request;

final readonly class CarCatalogFilterData
{
    public const int DEFAULT_PER_PAGE = 20;

    public const int MAX_PER_PAGE = 50;

    public function __construct(
        public ?string $marque = null,
        public ?int $prixMin = null,
        public ?int $prixMax = null,
        public ?int $anneeMin = null,
        public ?int $anneeMax = null,
        public ?FuelType $carburant = null,
        public ?TransmissionType $transmission = null,
        public bool $inclureVendus = false,
        public string $tri = 'recent',
        public int $page = 1,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Clamp per_page to MAX_PER_PAGE (50) without error
        $rawPerPage = $request->integer('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $rawPerPage > 0 ? min($rawPerPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;

        // Parse carburant safely (ignore unknown values)
        $fuelInput = $request->query('carburant');
        $carburant = is_string($fuelInput) ? FuelType::tryFrom($fuelInput) : null;

        // Parse transmission safely (ignore unknown values)
        $transmissionInput = $request->query('transmission');
        $transmission = is_string($transmissionInput) ? TransmissionType::tryFrom($transmissionInput) : null;

        // Parse boolean inclure_vendus safely
        $inclureVendus = filter_var($request->query('inclure_vendus', false), FILTER_VALIDATE_BOOLEAN);

        // Parse sort
        $rawTri = (string) $request->query('tri', 'recent');
        $validTri = in_array($rawTri, ['recent', 'prix_asc', 'prix_desc', 'km_asc'], true) ? $rawTri : 'recent';

        return new self(
            marque: $request->filled('marque') ? (string) $request->query('marque') : null,
            prixMin: $request->filled('prix_min') ? $request->integer('prix_min') : null,
            prixMax: $request->filled('prix_max') ? $request->integer('prix_max') : null,
            anneeMin: $request->filled('annee_min') ? $request->integer('annee_min') : null,
            anneeMax: $request->filled('annee_max') ? $request->integer('annee_max') : null,
            carburant: $carburant,
            transmission: $transmission,
            inclureVendus: $inclureVendus,
            tri: $validTri,
            page: max(1, $request->integer('page', 1)),
            perPage: $perPage,
        );
    }
}
