<?php

declare(strict_types=1);

namespace Tests\Feature\Queries;

use App\Data\CarCatalogFilterData;
use App\Enums\CarStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Models\Brand;
use App\Models\Car;
use App\Queries\CarCatalogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class CarCatalogQueryTest extends TestCase
{
    use RefreshDatabase;

    private CarCatalogQuery $catalogQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogQuery = new CarCatalogQuery;
    }

    public function test_drafts_are_strictly_never_returned(): void
    {
        $brand = Brand::factory()->create();

        // 1 Draft
        Car::factory()->draft()->for($brand)->create(['model' => 'DraftCar']);
        // 1 Available
        Car::factory()->available()->for($brand)->create(['model' => 'AvailableCar']);

        $filters = new CarCatalogFilterData(inclureVendus: true);
        $result = $this->catalogQuery->paginate($filters);

        $this->assertCount(1, $result->items());
        $this->assertSame('AvailableCar', $result->items()[0]->model);
    }

    public function test_filter_by_brand_slug(): void
    {
        $toyota = Brand::factory()->create(['slug' => 'toyota']);
        $peugeot = Brand::factory()->create(['slug' => 'peugeot']);

        Car::factory()->available()->for($toyota)->create(['model' => 'Yaris']);
        Car::factory()->available()->for($peugeot)->create(['model' => '208']);

        $filters = new CarCatalogFilterData(marque: 'toyota');
        $result = $this->catalogQuery->paginate($filters);

        $this->assertCount(1, $result->items());
        $this->assertSame('Yaris', $result->items()[0]->model);
    }

    public function test_filter_by_price_and_year_range(): void
    {
        $brand = Brand::factory()->create();

        Car::factory()->available()->for($brand)->create(['price_xaf' => 5000000, 'year' => 2015]);
        Car::factory()->available()->for($brand)->create(['price_xaf' => 12000000, 'year' => 2020]);
        Car::factory()->available()->for($brand)->create(['price_xaf' => 25000000, 'year' => 2023]);

        // Filter price 6M to 15M
        $priceFilters = new CarCatalogFilterData(prixMin: 6000000, prixMax: 15000000);
        $priceResult = $this->catalogQuery->paginate($priceFilters);
        $this->assertCount(1, $priceResult->items());
        $this->assertSame(12000000, $priceResult->items()[0]->price_xaf);

        // Filter year >= 2020
        $yearFilters = new CarCatalogFilterData(anneeMin: 2020);
        $yearResult = $this->catalogQuery->paginate($yearFilters);
        $this->assertCount(2, $yearResult->items());
    }

    public function test_filter_by_fuel_and_transmission(): void
    {
        $brand = Brand::factory()->create();

        Car::factory()->available()->for($brand)->create([
            'fuel' => FuelType::Diesel,
            'transmission' => TransmissionType::Automatique,
            'model' => 'DieselAuto',
        ]);

        Car::factory()->available()->for($brand)->create([
            'fuel' => FuelType::Essence,
            'transmission' => TransmissionType::Manuelle,
            'model' => 'EssenceManu',
        ]);

        $filters = new CarCatalogFilterData(
            carburant: FuelType::Diesel,
            transmission: TransmissionType::Automatique
        );
        $result = $this->catalogQuery->paginate($filters);

        $this->assertCount(1, $result->items());
        $this->assertSame('DieselAuto', $result->items()[0]->model);
    }

    public function test_sold_cars_excluded_by_default_and_included_when_requested(): void
    {
        $brand = Brand::factory()->create();

        Car::factory()->available()->for($brand)->create(['status' => CarStatus::Available]);
        Car::factory()->sold()->for($brand)->create(['status' => CarStatus::Sold]);

        // Default: sold is excluded
        $defaultFilters = new CarCatalogFilterData;
        $defaultResult = $this->catalogQuery->paginate($defaultFilters);
        $this->assertCount(1, $defaultResult->items());

        // When inclure_vendus = true: sold is included
        $includeSoldFilters = new CarCatalogFilterData(inclureVendus: true);
        $includeSoldResult = $this->catalogQuery->paginate($includeSoldFilters);
        $this->assertCount(2, $includeSoldResult->items());
    }

    public function test_sorting_options(): void
    {
        $brand = Brand::factory()->create();

        Car::factory()->available()->for($brand)->create(['price_xaf' => 10000000, 'mileage_km' => 50000]);
        Car::factory()->available()->for($brand)->create(['price_xaf' => 5000000, 'mileage_km' => 100000]);
        Car::factory()->available()->for($brand)->create(['price_xaf' => 20000000, 'mileage_km' => 20000]);

        // Sort prix_asc
        $ascFilters = new CarCatalogFilterData(tri: 'prix_asc');
        $ascResult = $this->catalogQuery->paginate($ascFilters);
        $this->assertSame(5000000, $ascResult->items()[0]->price_xaf);
        $this->assertSame(20000000, $ascResult->items()[2]->price_xaf);

        // Sort km_asc
        $kmFilters = new CarCatalogFilterData(tri: 'km_asc');
        $kmResult = $this->catalogQuery->paginate($kmFilters);
        $this->assertSame(20000, $kmResult->items()[0]->mileage_km);
    }

    public function test_per_page_clamping_in_dto(): void
    {
        $request = Request::create('/api/v1/cars?per_page=150', 'GET');
        $dto = CarCatalogFilterData::fromRequest($request);

        $this->assertSame(CarCatalogFilterData::MAX_PER_PAGE, $dto->perPage);
    }
}
