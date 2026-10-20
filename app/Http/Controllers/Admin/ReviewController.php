<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user', 'order'])->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn ($q) => $q->where('author_name', 'like', "%$s%")
                ->orWhere('author_email', 'like', "%$s%")
                ->orWhere('title', 'like', "%$s%")
                ->orWhere('body', 'like', "%$s%")
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%$s%")));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        $reviews = $query->paginate(10)->withQueryString();

        $stats = [
            'pending'  => Review::where('status', Review::PENDING)->count(),
            'approved' => Review::where('status', Review::APPROVED)->count(),
            'rejected' => Review::where('status', Review::REJECTED)->count(),
            'average'  => round((float) Review::where('status', Review::APPROVED)->avg('rating'), 1),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /** Approve → the review becomes visible on the product page. */
    public function approve(Request $request, Review $review)
    {
        $review->update([
            'status'      => Review::APPROVED,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'admin_note'  => null,
        ]);

        return back()->with('success', "Review by {$review->author_name} is now live on the product page.");
    }

    /** Reject → stays hidden; the note is for staff only. */
    public function reject(Request $request, Review $review)
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:255']);

        $review->update([
            'status'      => Review::REJECTED,
            'approved_at' => null,
            'approved_by' => $request->user()->id,
            'admin_note'  => $data['admin_note'] ?? null,
        ]);

        return back()->with('success', "Review by {$review->author_name} was rejected and stays hidden.");
    }

    /** Back to pending, e.g. after an approval by mistake. */
    public function unpublish(Review $review)
    {
        $review->update(['status' => Review::PENDING, 'approved_at' => null]);

        return back()->with('success', 'Review moved back to pending and hidden from the site.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
