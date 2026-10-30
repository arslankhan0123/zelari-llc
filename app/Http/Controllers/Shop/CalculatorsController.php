<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Calculators;

class CalculatorsController extends Controller
{
    public function index()
    {
        if (!Calculators::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Calculators are currently unavailable. Please check back later.');
        }

        return view('calculators.index', ['tools' => Calculators::all()]);
    }

    public function show(string $slug)
    {
        if (!Calculators::enabled()) {
            return redirect()->route('home')->with('ztoast', 'Calculators are currently unavailable. Please check back later.');
        }
        $tools = Calculators::all();
        abort_unless(isset($tools[$slug]), 404);
        if (!($tools[$slug]['live'] ?? false)) {
            return redirect()->route('calculators.index')->with('toolsoon', $tools[$slug]['name']);
        }

        return view('calculators.show', ['slug' => $slug, 'tool' => $tools[$slug], 'tools' => $tools]);
    }
}
