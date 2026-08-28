<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ReviewController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        // ReviewResource nests the product as a full ProductResource, which
        // reads product_type/status (enum casts) — a partial select that
        // omits them silently nulls those attributes and crashes `$enum->value`.
        $query = Review::query()->with(['user:id,first_name,last_name', 'product:id,name,slug,product_type,status']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->success([
            'items' => ReviewResource::collection($reviews->items()),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Review $review): JsonResponse
    {
        $request->validate(['status' => ['required', new Enum(ReviewStatus::class)]]);

        $review->update(['status' => $request->input('status')]);

        return $this->success(new ReviewResource($review->fresh()), 'Review status updated.');
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return $this->success(null, 'Review deleted successfully.');
    }
}
