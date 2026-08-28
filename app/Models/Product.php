<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'compare_at_price',
        'weight',
        'protein_grams',
        'calories',
        'carbohydrates_grams',
        'fat_grams',
        'fiber_grams',
        'sugar_grams',
        'ingredients',
        'allergens',
        'storage_info',
        'shipping_info',
        'product_type',
        'status',
        'is_featured',
        'stock_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'status' => ProductStatus::class,
        'is_featured' => 'boolean',
        'ingredients' => 'array',
        'allergens' => 'array',
        'carbohydrates_grams' => 'decimal:2',
        'fat_grams' => 'decimal:2',
        'fiber_grams' => 'decimal:2',
        'sugar_grams' => 'decimal:2',
    ];

    // price/compare_at_price are stored in cents (see App\Support\Money) but
    // every consumer of this model — API resources, Blade admin views, order
    // calculations — should work in decimal dollars. These accessors/mutators
    // make that translation transparent at the model boundary.
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : Money::toDollars($value),
            set: fn (float|int $value) => Money::toCents($value),
        );
    }

    protected function compareAtPrice(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : Money::toDollars($value),
            set: fn (null|float|int $value) => $value === null ? null : Money::toCents($value),
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', \App\Enums\ReviewStatus::Approved);
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
