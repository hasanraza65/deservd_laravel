<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['images'])
            ->whereHas('wishlists', fn ($q) => $q->where('user_id', $request->user()->id))
            ->get();

        return $this->success(ProductResource::collection($products));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);

        $request->user()->wishlists()->firstOrCreate(['product_id' => $request->integer('product_id')]);

        return $this->success(null, 'Added to wishlist.', 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()->where('product_id', $product->id)->delete();

        return $this->success(null, 'Removed from wishlist.');
    }
}
