<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::withCount('products')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('slug', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->paginate(12)->withQueryString();

        $stats = [
            'total'     => Vendor::count(),
            'approved'  => Vendor::where('status', Vendor::APPROVED)->count(),
            'pending'   => Vendor::where('status', Vendor::PENDING)->count(),
            'suspended' => Vendor::where('status', Vendor::SUSPENDED)->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->loadCount('products');
        $products = $vendor->products()->latest()->take(12)->get();
        return view('admin.vendors.show', compact('vendor', 'products'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'admin_notes'     => 'nullable|string|max:2000',
        ]);
        $vendor->update($data);
        return back()->with('success', 'Vendor updated.');
    }

    public function approve(Vendor $vendor)   { return $this->setStatus($vendor, Vendor::APPROVED,  'Vendor approved.'); }
    public function suspend(Vendor $vendor)   { return $this->setStatus($vendor, Vendor::SUSPENDED, 'Vendor suspended.'); }
    public function reject(Vendor $vendor)    { return $this->setStatus($vendor, Vendor::REJECTED,  'Vendor rejected.'); }
    public function reactivate(Vendor $vendor){ return $this->setStatus($vendor, Vendor::APPROVED,  'Vendor reactivated.'); }

    protected function setStatus(Vendor $vendor, string $status, string $msg)
    {
        $vendor->status = $status;
        if ($status === Vendor::APPROVED && !$vendor->approved_at) {
            $vendor->approved_at = now();
        }
        $vendor->save();
        return back()->with('success', $msg);
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->slug === 'zelari') {
            return back()->with('error', 'The house vendor cannot be deleted.');
        }
        // detach products back to house vendor so catalog stays valid
        $house = Vendor::where('slug', 'zelari')->value('id');
        $vendor->products()->update(['vendor_id' => $house]);
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted; products moved to house store.');
    }
}
