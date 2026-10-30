<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Calculators;
use Illuminate\Http\Request;

class CalculatorsController extends Controller
{
    public function index()
    {
        return view('admin.calculators.index', [
            'tools' => Calculators::all(),
            'enabled' => Calculators::enabled(),
        ]);
    }

    public function master(Request $request)
    {
        $request->validate(['enabled' => 'required|in:0,1']);
        Setting::set('calculators_enabled', $request->input('enabled'));

        return response()->json(['ok' => true, 'enabled' => $request->input('enabled') === '1']);
    }

    public function toggle(Request $request)
    {
        $request->validate(['slug' => 'required|string', 'live' => 'required|in:0,1']);
        $slug = $request->input('slug');
        abort_unless(isset(config('calculators', [])[$slug]), 404);

        $ov = Calculators::overrides();
        $ov[$slug] = $request->input('live') === '1' ? 'live' : 'soon';
        Setting::set('calculators_overrides', json_encode($ov));

        return response()->json(['ok' => true, 'slug' => $slug, 'live' => $ov[$slug] === 'live']);
    }
}
