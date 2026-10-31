<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected function vendor()
    {
        return auth()->user()->vendor;
    }

    /** Vendor must have an approved store to manage products. */
    protected function guardApproved()
    {
        $v = $this->vendor();
        if (!$v || !$v->isApproved()) {
            abort(redirect()->route('vendor.dashboard')
                ->with('error', 'Your store must be approved before you can manage products.'));
        }
        return $v;
    }

    protected function owned(Product $product, $vendor)
    {
        abort_unless($product->vendor_id === $vendor->id, 403);
    }

    public function index(Request $request)
    {
        $vendor = $this->guardApproved();
        $query = $vendor->products()->latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('approval')) {
            $query->where('approval_status', $request->approval);
        }
        $products = $query->paginate(12)->withQueryString();
        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $this->guardApproved();
        $categories = Category::orderBy('name')->get();
        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $vendor = $this->guardApproved();
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'nullable|exists:categories,id',
            'sku'               => 'nullable|string|unique:products,sku|max:100',
            'purchase_price'    => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'quantity'          => 'required|integer|min:0',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'feature_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $base = Str::slug($data['name']); $slug = $base; $i = 1;
        while (Product::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
        $data['slug'] = $slug;

        if ($request->hasFile('feature_image')) {
            $data['feature_image'] = $request->file('feature_image')->store('products/feature', 'public');
        }

        $data['vendor_id']       = $vendor->id;
        $data['status']          = 'draft';       // hidden from storefront until approved
        $data['approval_status'] = 'pending';
        $data['submitted_at']    = now();

        Product::create($data);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product submitted for approval.');
    }

    public function edit(Product $product)
    {
        $vendor = $this->guardApproved();
        $this->owned($product, $vendor);
        $categories = Category::orderBy('name')->get();
        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = $this->guardApproved();
        $this->owned($product, $vendor);
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'nullable|exists:categories,id',
            'sku'               => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'purchase_price'    => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'quantity'          => 'required|integer|min:0',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'feature_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('feature_image')) {
            if ($product->feature_image) Storage::disk('public')->delete($product->feature_image);
            $data['feature_image'] = $request->file('feature_image')->store('products/feature', 'public');
        }

        $product->update($data);

        return redirect()->route('vendor.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $vendor = $this->guardApproved();
        $this->owned($product, $vendor);
        if ($product->feature_image) Storage::disk('public')->delete($product->feature_image);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }
}
