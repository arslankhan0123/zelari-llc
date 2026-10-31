<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
        return back()->with('success', 'Product rejected.');
    }
}
