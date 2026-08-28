<x-admin-layout title="Reviews">
    <div class="mb-5 flex gap-2">
        @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('admin.reviews.index', array_filter(['status' => $value])) }}"
               class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wide {{ request('status', '') === $value ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($reviews as $review)
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <div class="mb-2 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-cocoa-900">{{ $review->product->name ?? 'Unknown product' }}</p>
                        <p class="text-xs text-cocoa-500">by {{ $review->user->first_name ?? 'Customer' }} {{ $review->user->last_name ?? '' }} · {{ $review->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        <x-badge :tone="['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$review->status->value]">{{ $review->status->value }}</x-badge>
                    </div>
                </div>
                @if ($review->title)
                    <p class="font-bold text-cocoa-800">{{ $review->title }}</p>
                @endif
                <p class="text-sm text-cocoa-700">{{ $review->content }}</p>

                <div class="mt-3 flex gap-2">
                    @unless ($review->status->value === 'approved')
                        <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="approved">
                            <x-btn type="submit" variant="primary">Approve</x-btn>
                        </form>
                    @endunless
                    @unless ($review->status->value === 'rejected')
                        <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <x-btn type="submit" variant="outline">Reject</x-btn>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">
                        @csrf @method('DELETE')
                        <x-btn type="submit" variant="ghost">Delete</x-btn>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-cocoa-500">No reviews to show.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
</x-admin-layout>
