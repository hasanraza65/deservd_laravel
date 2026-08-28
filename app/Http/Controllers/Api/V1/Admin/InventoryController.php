<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ApiResponses;

    /** Products at or below their low-stock threshold, plus a snapshot of every product's stock. */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->select(['id', 'name', 'sku', 'stock_quantity', 'low_stock_threshold']);

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0);
        }

        if ($request->boolean('out_of_stock_only')) {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->orderBy('stock_quantity')->paginate($request->integer('per_page', 30));

        return $this->success([
            'items' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function history(Request $request, Product $product): JsonResponse
    {
        $history = $product->inventoryHistories()
            ->with('causedBy:id,first_name,last_name')
            ->latest()
            ->paginate($request->integer('per_page', 30));

        return $this->success([
            'items' => $history->items(),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'total' => $history->total(),
            ],
        ]);
    }
}
