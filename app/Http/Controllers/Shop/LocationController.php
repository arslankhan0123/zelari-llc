<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Currency;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /** POST /location — visitor picked a country from the header dropdown. */
    public function set(Request $request)
    {
        $cc = strtoupper((string) $request->input('country'));

        if (!isset(Currency::countries()[$cc])) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => 'Unknown location.'], 422)
                : back();
        }

        Currency::choose($cc);
        $cur = Currency::current();

        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => true,
                'country'  => $cc,
                'currency' => $cur['code'],
                'symbol'   => $cur['symbol'],
                'rate'     => $cur['rate'],
                'decimals' => $cur['decimals'],
            ]);
        }

        return redirect()->to(url()->previous() ?: route('home'));
    }
}
