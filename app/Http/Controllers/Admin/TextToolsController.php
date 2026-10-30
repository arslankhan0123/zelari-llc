<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\TextTools;
use Illuminate\Http\Request;

class TextToolsController extends Controller
{
    public function index()
    {
        return view('admin.texttools.index', [
            'tools' => TextTools::all(),
            'enabled' => TextTools::enabled(),
        ]);
    }

    public function master(Request $request)
    {
        $request->validate(['enabled' => 'required|in:0,1']);
        Setting::set('texttools_enabled', $request->input('enabled'));

        return response()->json(['ok' => true, 'enabled' => $request->input('enabled') === '1']);
    }

    public function toggle(Request $request)
    {
        $request->validate(['slug' => 'required|string', 'live' => 'required|in:0,1']);
        $slug = $request->input('slug');
        abort_unless(isset(config('texttools', [])[$slug]), 404);

        $ov = TextTools::overrides();
        $ov[$slug] = $request->input('live') === '1' ? 'live' : 'soon';
        Setting::set('texttools_overrides', json_encode($ov));

        return response()->json(['ok' => true, 'slug' => $slug, 'live' => $ov[$slug] === 'live']);
    }
}
