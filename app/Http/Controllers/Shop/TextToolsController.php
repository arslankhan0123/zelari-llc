<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\TextTools;

class TextToolsController extends Controller
{
    public function index()
    {
        if (!TextTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Text Tools are currently unavailable. Please check back later.');
        }

        return view('texttools.index', ['tools' => TextTools::all()]);
    }

    public function show(string $slug)
    {
        if (!TextTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Text Tools are currently unavailable. Please check back later.');
        }
        $tools = TextTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('texttools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('texttools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
