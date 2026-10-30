<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\WheelEntry;
use App\Models\Setting;
use App\Support\Marketing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponsController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::orderByDesc('id')->paginate(20);
        $entries = WheelEntry::with('coupon')->orderByDesc('id')->paginate(20, ['*'], 'entries');
        $stats = [
            'coupons'  => Coupon::count(),
            'active'   => Coupon::where('active', true)->count(),
            'redeemed' => (int) Coupon::sum('used_count'),
            'emails'   => WheelEntry::distinct('email')->count('email'),
            'spins'    => WheelEntry::count(),
        ];

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'entries' => $entries,
            'stats'   => $stats,
            'wheel'   => [
                'enabled'    => Marketing::wheelEnabled(),
                'title'      => Setting::get('wheel_title', 'Win a discount!'),
                'subtitle'   => Setting::get('wheel_subtitle', 'Enter your email and spin the wheel for an exclusive coupon code.'),
                'expiryDays' => (int) Setting::get('wheel_expiry_days', 7),
                'minSpend'   => (float) Setting::get('wheel_min_spend', 0),
                'spinLimit'  => \App\Support\Marketing::spinLimit(),
            ],
            'segments' => Marketing::segments(),
            'isCustomSegments' => \App\Models\Setting::get('wheel_segments', '') !== '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'         => 'nullable|string|max:40',
            'type'         => 'required|in:percent,fixed,freeship',
            'value'        => 'required_unless:type,freeship|nullable|numeric|min:0',
            'min_spend'    => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'expires_at'   => 'nullable|date',
        ]);

        $code = strtoupper(trim($data['code'] ?? '')) ?: Marketing::uniqueCode('PROMO');
        if (Coupon::whereRaw('UPPER(code)=?', [$code])->exists()) {
            return back()->with('error', 'A coupon with that code already exists.');
        }

        Coupon::create([
            'code'         => $code,
            'type'         => $data['type'],
            'value'        => $data['type'] === 'freeship' ? 0 : (float) ($data['value'] ?? 0),
            'min_spend'    => (float) ($data['min_spend'] ?? 0),
            'max_discount' => $data['max_discount'] !== null && $data['max_discount'] !== '' ? (float) $data['max_discount'] : null,
            'usage_limit'  => $data['usage_limit'] ?? null,
            'expires_at'   => $data['expires_at'] ?? null,
            'active'       => true,
            'source'       => 'manual',
        ]);

        return back()->with('success', 'Coupon ' . $code . ' created.');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['active' => !$coupon->active]);
        return response()->json(['ok' => true, 'active' => $coupon->active]);
    }

    public function destroy(Coupon $coupon)
    {
        // remove the related wheel entries too, so no orphaned rows are left behind
        $coupon->entries()->delete();
        $coupon->delete();
        return back()->with('success', 'Coupon and its related data were deleted.');
    }

    public function destroyEntry(WheelEntry $entry)
    {
        $entry->delete();
        return back()->with('success', 'Wheel entry removed.');
    }

    public function wheel(Request $request)
    {
        $data = $request->validate([
            'enabled'     => 'required|in:0,1',
            'title'       => 'nullable|string|max:120',
            'subtitle'    => 'nullable|string|max:255',
            'expiry_days' => 'nullable|integer|min:1|max:365',
            'min_spend'   => 'nullable|numeric|min:0',
            'spin_limit'  => 'required|in:once,daily,unlimited',
        ]);

        Setting::setMany([
            'wheel_enabled'     => $data['enabled'],
            'wheel_title'       => $data['title'] ?? 'Win a discount!',
            'wheel_subtitle'    => $data['subtitle'] ?? '',
            'wheel_expiry_days' => (string) ($data['expiry_days'] ?? 7),
            'wheel_min_spend'   => (string) ($data['min_spend'] ?? 0),
            'wheel_spin_limit'  => $data['spin_limit'],
        ]);

        return back()->with('success', 'Prize wheel settings saved.');
    }

    public function segments(Request $request)
    {
        if ($request->boolean('reset')) {
            Setting::set('wheel_segments', ''); // empty → falls back to the built-in defaults
            return back()->with('success', 'Prize wheel reset to the default segments.');
        }

        $data = $request->validate([
            'segments'           => 'required|array|min:2|max:12',
            'segments.*.label'   => 'required|string|max:30',
            'segments.*.type'    => 'required|in:percent,fixed,freeship,none',
            'segments.*.value'   => 'nullable|numeric|min:0|max:100000',
            'segments.*.weight'  => 'required|numeric|min:0|max:1000',
            'segments.*.color'   => 'required|string|max:9',
        ]);

        $segs = array_map(fn ($x) => [
            'label'  => trim($x['label']),
            'type'   => $x['type'],
            'value'  => in_array($x['type'], ['freeship', 'none'], true) ? 0 : (float) ($x['value'] ?? 0),
            'weight' => (float) $x['weight'],
            'color'  => $x['color'],
        ], $data['segments']);

        Setting::set('wheel_segments', json_encode(array_values($segs)));

        return back()->with('success', 'Prize segments saved.');
    }

    public function exportEmails()
    {
        $rows = WheelEntry::orderByDesc('id')->get(['email', 'prize_label', 'created_at']);
        $csv  = "email,prize,date\n";
        foreach ($rows as $r) {
            $csv .= '"' . str_replace('"', '""', $r->email) . '","' . str_replace('"', '""', $r->prize_label) . '","' . $r->created_at . "\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wheel-emails.csv"',
        ]);
    }
}
