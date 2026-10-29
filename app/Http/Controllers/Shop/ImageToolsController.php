<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

class ImageToolsController extends Controller
{
    public function index()
    {
        if (!\App\Support\ImageTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Image Tools are currently unavailable. Please check back later.');
        }
        return view('imagetools.index', ['tools' => \App\Support\ImageTools::all()]);
    }

    public function show(string $slug)
    {
        if (!\App\Support\ImageTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Image Tools are currently unavailable. Please check back later.');
        }
        $tools = \App\Support\ImageTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('imgtools.index')->with('toolsoon', $tools[$slug]['name']);
        }
        return view('imagetools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
