<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->select(['id', 'name', 'sku', 'stock_quantity', 'low_stock_threshold']);

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0);
        }

        if ($request->boolean('out_of_stock_only')) {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->orderBy('stock_quantity')->paginate(30)->withQueryString();

        return view('admin.inventory.index', ['products' => $products]);
    }

    public function history(Product $product): View
    {
        $history = $product->inventoryHistories()->with('causedBy:id,first_name,last_name')->latest()->paginate(30);

        return view('admin.inventory.history', ['product' => $product, 'history' => $history]);
    }
}
