<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\ColorTools;

class ColorToolsController extends Controller
{
    public function index()
    {
        if (!ColorTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Color Tools are currently unavailable. Please check back later.');
        }

        return view('colortools.index', ['tools' => ColorTools::all()]);
    }

    public function show(string $slug)
    {
        if (!ColorTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Color Tools are currently unavailable. Please check back later.');
        }
        $tools = ColorTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('colortools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('colortools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
