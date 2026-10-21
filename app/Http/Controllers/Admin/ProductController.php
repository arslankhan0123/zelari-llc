<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"));
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories    = Category::treeOrdered();
        $manufacturers = \App\Models\Manufacturer::active()->ordered()->get();
        return view('admin.products.create', compact('categories', 'manufacturers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // slug auto-generated server-side
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'sku' => 'nullable|string|unique:products,sku|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
            'feature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Always auto-generate slug from name, ensure uniqueness
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $i = 1;
        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }
        $validated['slug'] = $slug;

        // Feature image
        if ($request->hasFile('feature_image')) {
            $validated['feature_image'] = $request->file('feature_image')
                ->store('products/feature', 'public');
        }

        $product = Product::create($validated);

        // Gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $i => $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" created successfully!");
    }

    public function show(Product $product)
    {
        $product->load('category', 'images');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('images');
        $categories    = Category::treeOrdered();
        $manufacturers = \App\Models\Manufacturer::active()->ordered()->get();
        return view('admin.products.edit', compact('product', 'categories', 'manufacturers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // slug auto-generated server-side
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id . '|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
            'feature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Auto-regenerate slug only if name changed; exclude self from uniqueness check
        if ($product->name !== $validated['name']) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $i = 1;
            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $validated['slug'] = $slug;
        } else {
            $validated['slug'] = $product->slug;
        }

        // Feature image
        if ($request->hasFile('feature_image')) {
            if ($product->feature_image) {
                Storage::disk('public')->delete($product->feature_image);
            }
            $validated['feature_image'] = $request->file('feature_image')
                ->store('products/feature', 'public');
        } elseif ($request->boolean('remove_feature_image') && $product->feature_image) {
            Storage::disk('public')->delete($product->feature_image);
            $validated['feature_image'] = null;
        } else {
            unset($validated['feature_image']);
        }

        $product->update($validated);

        // Remove selected gallery images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imgId) {
                $img = ProductImage::find($imgId);
                if ($img && $img->product_id === $product->id) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        // New gallery images
        if ($request->hasFile('gallery_images')) {
            $sortStart = ($product->images()->max('sort_order') ?? 0) + 1;
            foreach ($request->file('gallery_images') as $i => $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $sortStart + $i,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully!");
    }

    public function destroy(Product $product)
    {
        // Delete images from storage
        if ($product->feature_image) {
            Storage::disk('public')->delete($product->feature_image);
        }
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
