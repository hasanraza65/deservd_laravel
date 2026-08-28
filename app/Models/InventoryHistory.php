<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'type',
        'quantity_change',
        'quantity_after',
        'reference_type',
        'reference_id',
        'caused_by',
        'note',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function causedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caused_by');
    }
}
