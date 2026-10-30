<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\RandomTools;

class RandomToolsController extends Controller
{
    public function index()
    {
        if (!RandomTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Random Tools are currently unavailable. Please check back later.');
        }

        return view('randomtools.index', ['tools' => RandomTools::all()]);
    }

    public function show(string $slug)
    {
        if (!RandomTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Random Tools are currently unavailable. Please check back later.');
        }
        $tools = RandomTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('randomtools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('randomtools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
