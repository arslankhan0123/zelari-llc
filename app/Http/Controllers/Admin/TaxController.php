<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $tax = [
            'enabled'   => (string) Setting::get('tax_enabled', '0') === '1',
            'rate'      => (float) Setting::get('tax_rate', '0'),
            'inclusive' => (string) Setting::get('tax_inclusive', '0') === '1',
            'label'     => (string) Setting::get('tax_label', 'Tax'),
        ];
        $categories = Category::orderBy('name')->get(['id', 'name', 'tax_exempt']);
        return view('admin.tax.index', compact('tax', 'categories'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'rate'          => 'required|numeric|min:0|max:100',
            'label'         => 'required|string|max:30',
            'exempt'        => 'array',
            'exempt.*'      => 'nullable',
        ]);

        Setting::set('tax_enabled', $request->boolean('enabled') ? '1' : '0');
        Setting::set('tax_inclusive', $request->boolean('inclusive') ? '1' : '0');
        Setting::set('tax_rate', (string) $data['rate']);
        Setting::set('tax_label', $data['label']);

        $exemptIds = array_map('intval', array_keys((array) $request->input('exempt', [])));
        Category::query()->update(['tax_exempt' => false]);
        if ($exemptIds) {
            Category::whereIn('id', $exemptIds)->update(['tax_exempt' => true]);
        }

        return back()->with('success', 'Tax settings saved.');
    }
}
