<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent')->withCount('children')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('slug', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('parent')) {
            if ($request->parent === 'top') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent);
            }
        }

        $categories  = $query->paginate(10)->withQueryString();
        $allParents  = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'allParents'));
    }

    public function create()
    {
        $parents = Category::treeOrdered();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            // slug is auto-generated server-side
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Image → storage/app/public/categories (same disk as product images)
        $validated['image'] = $request->file('image')->store('categories', 'public');

        // Always auto-generate slug from name, ensure uniqueness
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $i = 1;
        while (\App\Models\Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }
        $validated['slug'] = $slug;

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        $category->load('parent', 'children');
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parents = Category::treeOrdered(true, $category->id);
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            // slug is auto-generated server-side (excluded from update to preserve existing)
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            // required only when the category has no image yet
            'image'       => [$category->hasImage() ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Replace image: store the new file first, then remove the old one
        if ($request->hasFile('image')) {
            $old = $category->image;
            $validated['image'] = $request->file('image')->store('categories', 'public');
            if ($old && $old !== $validated['image']) {
                Storage::disk('public')->delete($old);
            }
        } else {
            unset($validated['image']);
        }

        // Auto-regenerate slug only if name changed; exclude self from uniqueness check
        if ($category->name !== $validated['name']) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $i = 1;
            while (\App\Models\Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $validated['slug'] = $slug;
        } else {
            $validated['slug'] = $category->slug;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        $category->children()->update(['parent_id' => null]);
        $category->delete(); // model's "deleted" hook removes the image file

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
