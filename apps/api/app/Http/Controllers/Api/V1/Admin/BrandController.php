<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Brands\CreateBrand;
use App\Data\CreateBrandData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brands\CreateBrandRequest;
use App\Http\Resources\Admin\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BrandController extends Controller
{
    /**
     * Display a listing of all brands for admin backoffice.
     */
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->withCount('cars')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }

    /**
     * Create a new brand in the backoffice.
     */
    public function store(CreateBrandRequest $request, CreateBrand $createBrand): JsonResponse
    {
        $brand = $createBrand->handle(CreateBrandData::fromRequest($request));

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }
}
