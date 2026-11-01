<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\VendorNotify;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $products = Product::with('vendor')
            ->when(in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('approval_status', $status))
            ->whereNotNull('vendor_id')
            ->latest('submitted_at')->latest()
            ->paginate(15)->withQueryString();

        $counts = [
            'pending'  => Product::where('approval_status', 'pending')->count(),
            'approved' => Product::where('approval_status', 'approved')->whereNotNull('submitted_at')->count(),
            'rejected' => Product::where('approval_status', 'rejected')->count(),
        ];

        return view('admin.approvals.index', compact('products', 'counts', 'status'));
    }

    public function approve(Product $product)
    {
        $product->update([
            'approval_status' => 'approved',
            'approval_note'   => null,
            'status'          => 'active',   // now visible on storefront
        ]);

        if ($product->vendor) {
            VendorNotify::send(
                $product->vendor,
                'Your product was approved',
                "Good news! Your product \"{$product->name}\" has been approved and is now live on the store.\n\nYou can manage it from your vendor dashboard."
            );
            \App\Support\Automation::fire('product.approved', [
                'product'   => $product->name,
                'vendor_id' => $product->vendor_id,
            ]);
        }

        return back()->with('success', 'Product approved and published.');
    }

    public function reject(Request $request, Product $product)
    {
        $data = $request->validate(['approval_note' => 'nullable|string|max:500']);
        $product->update([
            'approval_status' => 'rejected',
            'approval_note'   => $data['approval_note'] ?? 'Not approved.',
            'status'          => 'draft',
        ]);

        if ($product->vendor) {
            $note = $product->approval_note ?: 'Not approved.';
            VendorNotify::send(
                $product->vendor,
                'Your product needs changes',
                "Your product \"{$product->name}\" was not approved.\n\nReason: {$note}\n\nPlease update it in your vendor dashboard and resubmit."
            );
            \App\Support\Automation::fire('product.rejected', [
                'product'   => $product->name,
                'vendor_id' => $product->vendor_id,
                'reason'    => $note,
            ]);
        }

        return back()->with('success', 'Product rejected.');
    }
}
