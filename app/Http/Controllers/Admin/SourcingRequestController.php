<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SourcingRequest;
use Illuminate\Http\Request;

class SourcingRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SourcingRequest::with('items')->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn ($q) => $q->where('reference', 'like', "%$s%")
                ->orWhere('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('company', 'like', "%$s%")
                ->orWhereHas('items', fn ($i) => $i->where('name', 'like', "%$s%")));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(10)->withQueryString();

        $stats = [
            'new'       => SourcingRequest::where('status', SourcingRequest::NEW)->count(),
            'reviewing' => SourcingRequest::where('status', SourcingRequest::REVIEWING)->count(),
            'quoted'    => SourcingRequest::where('status', SourcingRequest::QUOTED)->count(),
            'total'     => SourcingRequest::count(),
        ];

        return view('admin.sourcing.index', compact('requests', 'stats'));
    }

    public function status(Request $request, SourcingRequest $sourcing)
    {
        $data = $request->validate(['status' => 'required|in:new,reviewing,quoted,closed']);
        $sourcing->update($data);

        return back()->with('success', "{$sourcing->reference} marked as {$data['status']}.");
    }

    public function note(Request $request, SourcingRequest $sourcing)
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $sourcing->update($data);

        return back()->with('success', 'Note saved.');
    }

    public function destroy(SourcingRequest $sourcing)
    {
        $ref = $sourcing->reference;
        $sourcing->delete();

        return back()->with('success', "{$ref} deleted.");
    }
}
