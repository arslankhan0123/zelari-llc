<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->get();
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'segment' => 'required|in:all,new,repeat,vip,lapsed',
            'channel' => 'required|in:email,sms,push',
            'subject' => 'nullable|string|max:160',
            'body'    => 'nullable|string|max:5000',
        ]);
        Campaign::create($data + ['status' => 'draft']);
        return back()->with('success', 'Campaign saved as draft.');
    }

    public function send(Campaign $campaign)
    {
        $recipients = Customer::whereNotNull('email');
        // (Segment targeting is simplified; "all" sends to everyone with an email.)
        $emails = $recipients->pluck('email')->filter()->unique()->values();

        $sent = 0;
        if ($campaign->channel === 'email' && Setting::applyMailConfig()) {
            foreach ($emails as $to) {
                try {
                    Mail::raw($campaign->body ?? '', fn ($m) => $m->to($to)->subject($campaign->subject ?: $campaign->name));
                    $sent++;
                } catch (\Throwable $e) { /* skip bad address */ }
            }
        }

        $campaign->update([
            'status'     => 'sent',
            'recipients' => $campaign->channel === 'email' ? $sent : $emails->count(),
            'sent_at'    => now(),
        ]);

        $msg = $campaign->channel === 'email'
            ? "Campaign sent to {$sent} recipient(s)."
            : "SMS/Push needs a provider — recorded {$emails->count()} target(s) only.";
        return back()->with('success', $msg);
    }
}
