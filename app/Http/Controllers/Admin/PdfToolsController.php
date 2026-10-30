<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PdfTools;
use Illuminate\Http\Request;

class PdfToolsController extends Controller
{
    public function index()
    {
        return view('admin.pdftools.index', [
            'tools' => PdfTools::all(),
            'enabled' => PdfTools::enabled(),
        ]);
    }

    public function master(Request $request)
    {
        $request->validate(['enabled' => 'required|in:0,1']);
        Setting::set('pdftools_enabled', $request->input('enabled'));

        return response()->json(['ok' => true, 'enabled' => $request->input('enabled') === '1']);
    }

    public function toggle(Request $request)
    {
        $request->validate(['slug' => 'required|string', 'live' => 'required|in:0,1']);
        $slug = $request->input('slug');
        abort_unless(isset(config('pdftools', [])[$slug]), 404);

        $ov = PdfTools::overrides();
        $ov[$slug] = $request->input('live') === '1' ? 'live' : 'soon';
        Setting::set('pdftools_overrides', json_encode($ov));

        return response()->json(['ok' => true, 'slug' => $slug, 'live' => $ov[$slug] === 'live']);
    }
}
