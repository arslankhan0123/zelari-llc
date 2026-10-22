<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\SourcingRequestReceived;
use App\Models\Setting;
use App\Models\SourcingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SourcingController extends Controller
{
    public function create()
    {
        return view('shop.sourcing');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|max:150',
            'phone'           => 'nullable|string|max:40',
            'company'         => 'nullable|string|max:120',
            'source_country'  => 'nullable|string|max:80',
            'deliver_to'      => 'nullable|string|max:120',
            'budget'          => 'nullable|string|max:60',
            'needed_by'       => 'nullable|date|after_or_equal:today',
            'notes'           => 'nullable|string|max:2000',

            'items'                  => 'required|array|min:1|max:25',
            'items.*.name'           => 'required|string|max:160',
            'items.*.quantity'       => 'required|integer|min:1|max:1000000',
            'items.*.unit'           => 'nullable|string|max:30',
            'items.*.target_price'   => 'nullable|string|max:40',
            'items.*.reference_url'  => 'nullable|url|max:400',
            'items.*.specs'          => 'nullable|string|max:500',
        ], [
            'items.required'        => 'Add at least one product you want sourced.',
            'items.*.name.required' => 'Every product needs a name.',
        ]);

        $sourcing = DB::transaction(function () use ($data, $request) {
            $sourcing = SourcingRequest::create(collect($data)->except('items')->all() + [
                'user_id' => $request->user()?->id,
                'ip'      => $request->ip(),
            ]);

            foreach (array_values($data['items']) as $i => $item) {
                $sourcing->items()->create($item + ['position' => $i]);
            }

            return $sourcing->load('items');
        });

        $this->notifyAdmin($sourcing);

        $done = "Request {$sourcing->reference} received. Our sourcing team will come back to you with a quote.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $done, 'reference' => $sourcing->reference]);
        }

        return back()->with('success', $done);
    }

    /** Email the request to the admin over the SMTP saved in Settings. Never breaks the form. */
    private function notifyAdmin(SourcingRequest $sourcing): void
    {
        $to = trim((string) Setting::get('mail_admin_address'));

        try {
            if ($to === '') {
                $sourcing->update(['email_error' => 'No admin notification email set in Settings.']);
                return;
            }
            if (!Setting::applyMailConfig()) {
                $sourcing->update(['email_error' => 'SMTP is not configured in Settings.']);
                return;
            }

            Mail::to($to)->send(new SourcingRequestReceived($sourcing));

            $sourcing->update(['emailed' => true, 'email_error' => null]);
            Setting::recordMailResult(null);
        } catch (\Throwable $e) {
            report($e);
            $sourcing->update(['email_error' => mb_substr($e->getMessage(), 0, 255)]);
            Setting::recordMailResult('Sourcing request ' . $sourcing->reference . ': ' . $e->getMessage());
        }
    }
}
