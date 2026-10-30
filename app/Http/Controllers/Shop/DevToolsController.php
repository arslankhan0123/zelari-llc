<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\DevTools;

class DevToolsController extends Controller
{
    public function index()
    {
        if (!DevTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Developer Tools are currently unavailable. Please check back later.');
        }

        return view('devtools.index', ['tools' => DevTools::all()]);
    }

    public function show(string $slug)
    {
        if (!DevTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Developer Tools are currently unavailable. Please check back later.');
        }
        $tools = DevTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('devtools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('devtools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
