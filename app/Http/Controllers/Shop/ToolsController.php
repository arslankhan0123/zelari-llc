<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index', ['tools' => config('pdftools', [])]);
    }

    public function show(string $slug)
    {
        $tools = config('pdftools', []);
        abort_unless(isset($tools[$slug]), 404);
        return view('tools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
