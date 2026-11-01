<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        $settings = [
            'enabled'   => (string) Setting::get('loyalty_enabled', '0') === '1',
            'earn_rate' => (float) Setting::get('loyalty_earn_rate', '1'),
            'referral'  => (int) Setting::get('referral_reward_points', '100'),
        ];
        $customers = Customer::where('points_balance', '>', 0)->orderByDesc('points_balance')->take(50)->get();
        return view('admin.loyalty.index', compact('settings', 'customers'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'earn_rate' => 'required|numeric|min:0',
            'referral'  => 'required|integer|min:0',
        ]);
        Setting::set('loyalty_enabled', $request->boolean('enabled') ? '1' : '0');
        Setting::set('loyalty_earn_rate', (string) $data['earn_rate']);
        Setting::set('referral_reward_points', (string) $data['referral']);
        return back()->with('success', 'Loyalty settings saved.');
    }
}
