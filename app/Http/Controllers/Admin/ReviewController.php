<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::query()->with(['user:id,first_name,last_name', 'product:id,name,slug,product_type,status']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reviews.index', ['reviews' => $reviews]);
    }

    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:pending,approved,rejected']]);
        $review->update(['status' => $request->input('status')]);

        return back()->with('status', 'Review status updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted successfully.');
    }
}
