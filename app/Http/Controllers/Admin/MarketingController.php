<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\MarketingPlan;
use App\Support\Newsletter;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        return view('admin.marketing.index', [
            'plan' => MarketingPlan::latest(),
            'feeds' => [
                'google' => url('/feed/google.xml'),
                'meta'   => url('/feed/meta.csv'),
            ],
            'nl' => [
                'enabled'   => Newsletter::enabled(),
                'frequency' => Newsletter::frequency(),
                'count'     => Newsletter::count(),
                'last_sent' => Setting::get('newsletter_last_sent', null),
            ],
        ]);
    }

    public function plan()
    {
        @set_time_limit(60);
        MarketingPlan::generate();
        return redirect()->route('admin.marketing.index', ['planned' => 1]);
    }

    public function email(Request $request)
    {
        $request->validate([
            'enabled'   => 'required|in:0,1',
            'frequency' => 'required|in:weekly,biweekly,monthly',
        ]);
        Setting::set('newsletter_enabled', $request->input('enabled'));
        Setting::set('newsletter_frequency', $request->input('frequency'));
        return redirect()->route('admin.marketing.index', ['saved' => 1]);
    }

    public function send()
    {
        @set_time_limit(300);
        $n = Newsletter::sendCampaign();
        return redirect()->route('admin.marketing.index', ['sent' => $n]);
    }

    public function test(Request $request)
    {
        $email = optional($request->user())->email;
        $ok = $email ? Newsletter::sendTest($email) : false;
        return redirect()->route('admin.marketing.index', ['tested' => $ok ? 1 : 0]);
    }
}
