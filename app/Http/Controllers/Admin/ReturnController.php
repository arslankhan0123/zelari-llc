<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::with('order')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        $requests = $query->paginate(15)->withQueryString();
        return view('admin.returns.index', compact('requests'));
    }

    public function show(ReturnRequest $return)
    {
        $return->load('order.items', 'processor');
        return view('admin.returns.show', compact('return'));
    }

    public function update(Request $request, ReturnRequest $return)
    {
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
        if ($data['status'] === ReturnRequest::COMPLETED && $return->order) {
            $return->order->forceFill(['payment_status' => 'refunded'])->save();
        }

        return back()->with('success', 'Return request updated.');
    }
}
