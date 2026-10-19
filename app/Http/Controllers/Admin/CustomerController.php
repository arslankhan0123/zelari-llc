<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller {

    public function index(Request $request) {
        $query = Customer::withCount('orders')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%")->orWhere('phone','like',"%$s%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);

        $customers = $query->paginate(10)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function create() {
        return view('admin.customers.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:customers,email',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status'  => 'required|in:active,inactive',
        ]);
        Customer::create($data);
        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer) {
        $customer->load('orders');
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer) {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer) {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:customers,email,'.$customer->id,
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status'  => 'required|in:active,inactive',
        ]);
        $customer->update($data);
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer) {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
