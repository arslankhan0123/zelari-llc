<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Vendor;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        $default    = (float) Setting::get('commission_default_rate', '10');
        $categories = Category::orderBy('name')->get(['id', 'name', 'commission_rate']);
        $vendors    = Vendor::orderBy('name')->get(['id', 'name', 'slug', 'commission_rate']);

        return view('admin.commissions.index', compact('default', 'categories', 'vendors'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'default_rate'     => 'required|numeric|min:0|max:100',
            'category_rates'   => 'array',
            'category_rates.*' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('commission_default_rate', (string) $request->default_rate);

        foreach ((array) $request->input('category_rates', []) as $catId => $rate) {
            $cat = Category::find($catId);
            if (!$cat) {
                continue;
            }
            $cat->commission_rate = ($rate === null || $rate === '') ? null : (float) $rate;
            $cat->save();
        }

        return back()->with('success', 'Commission rules updated.');
    }
}
