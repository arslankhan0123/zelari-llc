<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::with('methods')->orderBy('is_rest')->orderBy('sort')->get();
        return view('admin.shipping.index', compact('zones'));
    }

    private function parseCountries(?string $raw): array
    {
        if (!$raw) {
            return [];
        }
        return collect(preg_split('/[\s,]+/', strtoupper($raw)))
            ->filter(fn ($c) => strlen($c) === 2)->unique()->values()->all();
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:80',
            'countries' => 'nullable|string',
            'is_rest'   => 'nullable|boolean',
        ]);
        ShippingZone::create([
            'name'      => $data['name'],
            'countries' => $this->parseCountries($data['countries'] ?? null),
            'is_rest'   => $request->boolean('is_rest'),
            'enabled'   => true,
            'sort'      => (int) (ShippingZone::max('sort') + 1),
        ]);
        return back()->with('success', 'Shipping zone added.');
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:80',
            'countries' => 'nullable|string',
            'is_rest'   => 'nullable|boolean',
            'enabled'   => 'nullable|boolean',
        ]);
        $zone->update([
            'name'      => $data['name'],
            'countries' => $this->parseCountries($data['countries'] ?? null),
            'is_rest'   => $request->boolean('is_rest'),
            'enabled'   => $request->boolean('enabled'),
        ]);
        return back()->with('success', 'Shipping zone updated.');
    }

    public function destroyZone(ShippingZone $zone)
    {
        $zone->delete();
        return back()->with('success', 'Shipping zone removed.');
    }

    public function storeMethod(Request $request, ShippingZone $zone)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:80',
            'rate'      => 'required|numeric|min:0',
            'free_over' => 'nullable|numeric|min:0',
        ]);
        $zone->methods()->create([
            'name'      => $data['name'],
            'rate'      => $data['rate'],
            'free_over' => $data['free_over'] ?? null,
            'enabled'   => true,
            'sort'      => (int) ($zone->methods()->max('sort') + 1),
        ]);
        return back()->with('success', 'Shipping method added.');
    }

    public function updateMethod(Request $request, ShippingMethod $method)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:80',
            'rate'      => 'required|numeric|min:0',
            'free_over' => 'nullable|numeric|min:0',
            'enabled'   => 'nullable|boolean',
        ]);
        $method->update([
            'name'      => $data['name'],
            'rate'      => $data['rate'],
            'free_over' => $data['free_over'] ?? null,
            'enabled'   => $request->boolean('enabled'),
        ]);
        return back()->with('success', 'Shipping method updated.');
    }

    public function destroyMethod(ShippingMethod $method)
    {
        $method->delete();
        return back()->with('success', 'Shipping method removed.');
    }
}
