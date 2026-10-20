<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $query = Manufacturer::withCount('products')->ordered();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('slug', 'like', "%$s%")
                ->orWhere('country', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('featured')) {
            $query->where('featured', $request->featured === 'yes');
        }

        $manufacturers = $query->paginate(10)->withQueryString();

        return view('admin.manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('admin.manufacturers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $data['logo'] = $request->file('logo')->store('manufacturers', 'public');
        $data['slug'] = $this->uniqueSlug($data['name']);

        Manufacturer::create($data);

        return redirect()->route('admin.manufacturers.index')
            ->with('success', 'Manufacturer created successfully!');
    }

    public function edit(Manufacturer $manufacturer)
    {
        $manufacturer->loadCount('products');
        return view('admin.manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, Manufacturer $manufacturer)
    {
        $data = $this->validated($request, $manufacturer);

        // Replace the logo: store the new file first, then remove the old one
        if ($request->hasFile('logo')) {
            $old = $manufacturer->logo;
            $data['logo'] = $request->file('logo')->store('manufacturers', 'public');
            if ($old && $old !== $data['logo']) {
                Storage::disk('public')->delete($old);
            }
        } else {
            unset($data['logo']);
        }

        $data['slug'] = $manufacturer->name !== $data['name']
            ? $this->uniqueSlug($data['name'], $manufacturer->id)
            : $manufacturer->slug;

        $manufacturer->update($data);

        return redirect()->route('admin.manufacturers.index')
            ->with('success', 'Manufacturer updated successfully!');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        // Products keep working; their manufacturer_id is set to null by the FK
        $manufacturer->delete(); // the model's "deleted" hook removes the logo file

        return redirect()->route('admin.manufacturers.index')
            ->with('success', 'Manufacturer deleted successfully!');
    }

    /* ── helpers ───────────────────────────────────────────── */

    private function validated(Request $request, ?Manufacturer $existing): array
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'logo'             => [$existing && $existing->hasLogo() ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'description'      => 'nullable|string|max:5000',
            'website'          => 'nullable|url|max:255',
            'country'          => 'nullable|string|max:80',
            'email'            => 'nullable|email|max:150',
            'phone'            => 'nullable|string|max:40',
            'established_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'status'           => 'required|in:active,inactive',
        ]);

        $data['featured']   = $request->boolean('featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $i    = 1;
        while (Manufacturer::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
