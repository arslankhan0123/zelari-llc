<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('products')->orderBy('name')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000',
        ]);
        Supplier::create($data);
        return back()->with('success', 'Supplier added.');
    }
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000',
        ]);
        $supplier->update($data);
        return back()->with('success', 'Supplier updated.');
    }
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Supplier removed.');
    }
}
