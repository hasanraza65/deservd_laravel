<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores image *paths* on the `product_images` disk, never binary data in the
 * database — that disk is configured (config/filesystems.php) to write
 * directly under public/images/products, a real static-file directory the
 * webserver already serves, with no `storage:link` symlink involved.
 * Everything routes through this one disk name, so swapping to S3 later
 * (per the brief's "prepare for cloud storage") is a one-line config change,
 * not a rewrite of upload logic.
 */
class ProductImageService
{
    private const DISK = 'product_images';
    // Empty: the disk's own root already points at public/images/products,
    // so files land there directly instead of a further nested subfolder.
    private const DIRECTORY = '';

    /** @param  UploadedFile[]  $files */
    public function attach(Product $product, array $files, ?int $primaryIndex = null): void
    {
        $nextSortOrder = (int) $product->images()->max('sort_order') + 1;
        $hasPrimaryAlready = $product->images()->where('is_primary', true)->exists();

        foreach (array_values($files) as $index => $file) {
            $path = $file->store(self::DIRECTORY, self::DISK);

            $isPrimary = $primaryIndex !== null
                ? $index === $primaryIndex
                : (!$hasPrimaryAlready && $index === 0);

            ProductImage::create([
                'product_id' => $product->id,
                'disk' => self::DISK,
                'path' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $nextSortOrder + $index,
            ]);

            if ($isPrimary) {
                $hasPrimaryAlready = true;
            }
        }
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk($image->disk)->delete($image->path);
        $wasPrimary = $image->is_primary;
        $productId = $image->product_id;

        $image->delete();

        if ($wasPrimary) {
            ProductImage::where('product_id', $productId)->oldest('sort_order')->first()?->update(['is_primary' => true]);
        }
    }

    public function makePrimary(ProductImage $image): void
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    /** @param  int[]  $orderedImageIds */
    public function reorder(Product $product, array $orderedImageIds): void
    {
        foreach ($orderedImageIds as $position => $imageId) {
            ProductImage::where('product_id', $product->id)->where('id', $imageId)->update(['sort_order' => $position]);
        }
    }
}
