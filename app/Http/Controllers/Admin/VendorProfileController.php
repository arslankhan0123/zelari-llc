<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorProfileController extends Controller
{
    private function myVendor(): Vendor
    {
        $vid = auth()->user()->vendorId();
        abort_unless($vid, 403, 'No vendor profile for your account.');
        return Vendor::findOrFail($vid);
    }

    public function edit()
    {
        $vendor = $this->myVendor();
        return view('admin.store.edit', compact('vendor'));
    }

    public function update(Request $request)
    {
        $vendor = $this->myVendor();
        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'nullable|email|max:160',
            'phone'           => 'nullable|string|max:40',
            'city'            => 'nullable|string|max:120',
            'address'         => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'return_policy'   => 'nullable|string|max:3000',
            'shipping_policy' => 'nullable|string|max:3000',
            'payout_method'   => 'nullable|string|max:40',
            'bank_name'       => 'nullable|string|max:120',
            'account_name'    => 'nullable|string|max:120',
            'account_number'  => 'nullable|string|max:60',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        foreach (['logo', 'banner'] as $img) {
            if ($request->hasFile($img)) {
                if ($vendor->$img) Storage::disk('public')->delete($vendor->$img);
                $data[$img] = $request->file($img)->store('vendors', 'public');
            }
        }

        // Vendors cannot change their own commission, status, plan or shipping rates here.
        $vendor->update($data);
        return back()->with('success', 'Store settings saved.');
    }
}
