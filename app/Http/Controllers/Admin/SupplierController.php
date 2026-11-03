<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $vid = auth()->user()->vendorId();
        $suppliers = Supplier::withCount('products')
            ->when($vid, fn ($q) => $q->where('vendor_id', $vid))
            ->orderBy('name')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000',
        ]);
        if ($vid = auth()->user()->vendorId()) { $data['vendor_id'] = $vid; }
        Supplier::create($data);
        return back()->with('success', 'Supplier added.');
    }
    public function update(Request $request, Supplier $supplier)
    {
        if ($vid = auth()->user()->vendorId()) { abort_unless($supplier->vendor_id === $vid, 403); }
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000',
        ]);
        $supplier->update($data);
        return back()->with('success', 'Supplier updated.');
    }
    public function destroy(Supplier $supplier)
    {
        if ($vid = auth()->user()->vendorId()) { abort_unless($supplier->vendor_id === $vid, 403); }
        $supplier->delete();
        return back()->with('success', 'Supplier removed.');
    }
}
