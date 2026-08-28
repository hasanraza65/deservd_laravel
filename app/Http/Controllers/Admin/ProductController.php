<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\InventoryService;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductImageService $imageService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Product::query()->with(['category', 'images']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('admin.products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images'], $data['primary_image_index']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        if (!empty($images)) {
            $this->imageService->attach($product, $images, $request->integer('primary_image_index'));
        }

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'images', 'inventoryHistories' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.products.edit', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product->update($data);

        if (!empty($images)) {
            $this->imageService->attach($product, $images);
        }

        return back()->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted successfully.');
    }

    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $this->imageService->delete($image);

        return back()->with('status', 'Image deleted successfully.');
    }

    public function makeImagePrimary(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        $this->imageService->makePrimary($image);

        return back()->with('status', 'Primary image updated.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:0'], 'note' => ['nullable', 'string', 'max:255']]);

        $this->inventoryService->adjustTo($product, $request->integer('quantity'), $request->user(), $request->input('note'));

        return back()->with('status', 'Stock updated.');
    }
}
