<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user?->isVendor()) {
            $vendor = $user->vendor;
            abort_unless($vendor, 403, 'No vendor profile found for your account.');
            $summary  = $vendor->earnings();
            $requests = $vendor->payoutRequests()->latest()->paginate(15);
            return view('admin.payouts.index', [
                'isVendor' => true,
                'vendor'   => $vendor,
                'summary'  => $summary,
                'requests' => $requests,
            ]);
        }

        $query = PayoutRequest::with('vendor', 'processor')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }
        $requests = $query->paginate(15)->withQueryString();
        $vendors  = Vendor::orderBy('name')->get(['id', 'name']);

        return view('admin.payouts.index', [
            'isVendor' => false,
            'requests' => $requests,
            'vendors'  => $vendors,
        ]);
    }

    /** Vendor submits a payout request, capped at their available balance. */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user?->isVendor(), 403);
        $vendor = $user->vendor;
        abort_unless($vendor, 403, 'No vendor profile found for your account.');

        $available = $vendor->earnings()['available'];

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . max(1, $available),
            'method' => 'nullable|string|max:60',
            'note'   => 'nullable|string|max:1000',
        ], [
            'amount.max' => 'You can request at most your available balance.',
        ]);

        if ($available < 1) {
            return back()->with('error', 'You have no available balance to withdraw yet.');
        }

        PayoutRequest::create([
            'vendor_id' => $vendor->id,
            'amount'    => round((float) $data['amount'], 2),
            'status'    => PayoutRequest::PENDING,
            'method'    => $data['method'] ?? null,
            'note'      => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Payout request submitted. The admin will review it shortly.');
    }

    public function approve(Request $request, PayoutRequest $payout)
    {
        return $this->transition($request, $payout, PayoutRequest::APPROVED, 'Payout request approved.');
    }

    public function reject(Request $request, PayoutRequest $payout)
    {
        return $this->transition($request, $payout, PayoutRequest::REJECTED, 'Payout request rejected.');
    }

    public function pay(Request $request, PayoutRequest $payout)
    {
        $request->validate(['reference' => 'nullable|string|max:120']);
        $payout->reference = $request->reference;
        return $this->transition($request, $payout, PayoutRequest::PAID, 'Payout marked as paid.');
    }

    private function transition(Request $request, PayoutRequest $payout, string $status, string $msg)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $payout->fill([
            'status'       => $status,
            'admin_note'   => $request->input('admin_note', $payout->admin_note),
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ])->save();

        return back()->with('success', $msg);
    }
}
