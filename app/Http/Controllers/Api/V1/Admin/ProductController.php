<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\InventoryService;
use App\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly ProductImageService $imageService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category', 'images']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        $products = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->success([
            'items' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images'], $data['primary_image_index']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        if (!empty($images)) {
            $this->imageService->attach($product, $images, $request->integer('primary_image_index'));
        }

        return $this->success(new ProductResource($product->fresh(['category', 'images'])), 'Product created successfully.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success(new ProductResource($product->load(['category', 'images'])));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product->update($data);

        if (!empty($images)) {
            $this->imageService->attach($product, $images);
        }

        return $this->success(new ProductResource($product->fresh(['category', 'images'])), 'Product updated successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success(null, 'Product deleted successfully.');
    }

    public function deleteImage(Product $product, ProductImage $image): JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $this->imageService->delete($image);

        return $this->success(null, 'Image deleted successfully.');
    }

    public function makeImagePrimary(Product $product, ProductImage $image): JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $this->imageService->makePrimary($image);

        return $this->success(new ProductResource($product->fresh(['images'])), 'Primary image updated.');
    }

    public function reorderImages(Request $request, Product $product): JsonResponse
    {
        $request->validate(['image_ids' => ['required', 'array'], 'image_ids.*' => ['integer']]);

        $this->imageService->reorder($product, $request->input('image_ids'));

        return $this->success(new ProductResource($product->fresh(['images'])), 'Image order updated.');
    }

    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:0'], 'note' => ['nullable', 'string', 'max:255']]);

        $this->inventoryService->adjustTo($product, $request->integer('quantity'), $request->user(), $request->input('note'));

        return $this->success(new ProductResource($product->fresh()), 'Stock updated.');
    }
}
