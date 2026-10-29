<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

class ToolsController extends Controller
{
    public function index()
    {
        if (!\App\Support\PdfTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'PDF Tools are currently unavailable. Please check back later.');
        }
        return view('tools.index', ['tools' => \App\Support\PdfTools::all()]);
    }

    public function show(string $slug)
    {
        if (!\App\Support\PdfTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'PDF Tools are currently unavailable. Please check back later.');
        }
        $tools = \App\Support\PdfTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('tools.index')->with('toolsoon', $tools[$slug]['name']);
        }
        return view('tools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
