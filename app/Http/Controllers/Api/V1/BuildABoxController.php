<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoxOptionResource;
use App\Http\Resources\ProductResource;
use App\Models\BoxOption;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/** Everything the Build-a-Box UI needs to render: available sizes/prices and the eligible flavour list. */
class BuildABoxController extends Controller
{
    use ApiResponses;

    public function options(): JsonResponse
    {
        $boxOptions = BoxOption::query()->active()->orderBy('sort_order')->get();

        $eligibleProducts = Product::query()
            ->with(['images'])
            ->where('status', ProductStatus::Active->value)
            ->where('product_type', ProductType::Standard->value)
            ->orderBy('name')
            ->get();

        return $this->success([
            'box_options' => BoxOptionResource::collection($boxOptions),
            'eligible_products' => ProductResource::collection($eligibleProducts),
        ]);
    }
}
