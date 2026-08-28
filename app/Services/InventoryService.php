<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InventoryService
{
    /**
     * Decrement stock for a product already locked with lockForUpdate() by the
     * caller (see OrderService — every product touched by an order is locked
     * once, up front, so two simultaneous checkouts can never both pass a
     * stock check for the last unit).
     */
    public function decrement(Product $product, int $quantity, string $type, ?Model $reference = null, ?User $causedBy = null, ?string $note = null): void
    {
        if ($product->stock_quantity < $quantity) {
            throw new InsufficientStockException($product->name, $quantity, $product->stock_quantity);
        }

        $product->stock_quantity -= $quantity;
        $product->save();

        $this->recordHistory($product, -$quantity, $type, $reference, $causedBy, $note);
    }

    public function increment(Product $product, int $quantity, string $type, ?Model $reference = null, ?User $causedBy = null, ?string $note = null): void
    {
        $product->stock_quantity += $quantity;
        $product->save();

        $this->recordHistory($product, $quantity, $type, $reference, $causedBy, $note);
    }

    /** Admin-facing absolute stock correction (e.g. a physical recount). */
    public function adjustTo(Product $product, int $newQuantity, ?User $causedBy = null, ?string $note = null): void
    {
        $change = $newQuantity - $product->stock_quantity;
        $product->stock_quantity = $newQuantity;
        $product->save();

        $this->recordHistory($product, $change, 'adjustment', null, $causedBy, $note);
    }

    private function recordHistory(Product $product, int $change, string $type, ?Model $reference, ?User $causedBy, ?string $note): void
    {
        InventoryHistory::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity_change' => $change,
            'quantity_after' => $product->stock_quantity,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'caused_by' => $causedBy?->id,
            'note' => $note,
        ]);
    }
}
