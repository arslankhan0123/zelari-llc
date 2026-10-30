<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\QrTools;

class QrToolsController extends Controller
{
    public function index()
    {
        if (!QrTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'QR and Barcode are currently unavailable. Please check back later.');
        }

        return view('qrtools.index', ['tools' => QrTools::all()]);
    }

    public function show(string $slug)
    {
        if (!QrTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'QR and Barcode are currently unavailable. Please check back later.');
        }
        $tools = QrTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('qrtools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('qrtools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
