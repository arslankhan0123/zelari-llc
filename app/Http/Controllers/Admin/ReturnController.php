<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $vid = auth()->user()->vendorId();
        $query = ReturnRequest::with('order')->latest()
            ->when($vid, fn ($q) => $q->whereHas('order.items', fn ($i) => $i->where('vendor_id', $vid)));
        if ($request->filled('status')) $query->where('status', $request->status);
        $requests = $query->paginate(15)->withQueryString();
        return view('admin.returns.index', compact('requests'));
    }

    public function show(ReturnRequest $return)
    {
        if ($vid = auth()->user()->vendorId()) {
            abort_unless(optional($return->order)->items()->where('vendor_id', $vid)->exists(), 403);
        }
        $return->load('order.items', 'processor');
        return view('admin.returns.show', compact('return'));
    }

    public function update(Request $request, ReturnRequest $return)
    {
        if ($vid = auth()->user()->vendorId()) {
            abort_unless(optional($return->order)->items()->where('vendor_id', $vid)->exists(), 403);
        }
        $data = $request->validate([
            'status'     => 'required|in:requested,approved,rejected,completed',
            'amount'     => 'nullable|numeric|min:0',
            'admin_note' => 'nullable|string|max:2000',
        ]);
        $return->fill([
            'status'       => $data['status'],
            'amount'       => $data['amount'] ?? $return->amount,
            'admin_note'   => $data['admin_note'] ?? $return->admin_note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ])->save();

        // Completing a refund marks the order refunded.
        if ($data['status'] === ReturnRequest::COMPLETED && $return->order && !auth()->user()->vendorId()) {
            $return->order->forceFill(['payment_status' => 'refunded'])->save();
        }

        return back()->with('success', 'Return request updated.');
    }
}
