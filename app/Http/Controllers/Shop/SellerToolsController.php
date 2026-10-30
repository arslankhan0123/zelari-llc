<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\SellerTools;

class SellerToolsController extends Controller
{
    public function index()
    {
        if (!SellerTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Seller Tools are currently unavailable. Please check back later.');
        }

        return view('sellertools.index', ['tools' => SellerTools::all()]);
    }

    public function show(string $slug)
    {
        if (!SellerTools::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Seller Tools are currently unavailable. Please check back later.');
        }
        $tools = SellerTools::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('sellertools.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('sellertools.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
