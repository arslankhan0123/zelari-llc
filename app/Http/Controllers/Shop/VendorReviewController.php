<?php
namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class VendorReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $vendor = Vendor::where('slug', $slug)->where('status', Vendor::APPROVED)->firstOrFail();
        $data = $request->validate([
            'author_name'  => 'required|string|max:120',
            'author_email' => 'nullable|email|max:160',
            'rating'       => 'required|integer|min:1|max:5',
            'body'         => 'nullable|string|max:1500',
        ]);

        // One review per email per vendor (soft guard).
        if (!empty($data['author_email'])
            && VendorReview::where('vendor_id', $vendor->id)->where('author_email', $data['author_email'])->exists()) {
            return back()->with('vreview_error', 'You have already reviewed this store.');
        }

        VendorReview::create($data + ['vendor_id' => $vendor->id, 'status' => 'approved']);
        return back()->with('vreview_success', 'Thanks for rating this store!');
    }
}
