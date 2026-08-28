<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponses;

    /** Approved reviews for a product — public, no auth required. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);

        $reviews = Review::query()
            ->with('user:id,first_name')
            ->where('product_id', $request->integer('product_id'))
            ->approved()
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return $this->success([
            'items' => ReviewResource::collection($reviews->items()),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /** A logged-in customer submits a review — it starts Pending and is not public until an admin approves it. */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = Review::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => ReviewStatus::Pending,
        ]);

        return $this->success(new ReviewResource($review), 'Review submitted and awaiting approval.', 201);
    }
}
