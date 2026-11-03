<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $vid = auth()->user()->vendorId();
        $query = SupportTicket::with('assignee')->withCount('replies')->latest()
            ->when($vid, fn ($q) => $q->whereHas('order.items', fn ($i) => $i->where('vendor_id', $vid)));
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        $tickets = $query->paginate(15)->withQueryString();
        return view('admin.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $staff = User::orderBy('name')->get(['id', 'name']);
        return view('admin.tickets.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'        => 'required|string|max:200',
            'customer_name'  => 'nullable|string|max:120',
            'customer_email' => 'required|email|max:160',
            'priority'       => 'required|in:low,normal,high,urgent',
            'due_at'         => 'nullable|date',
            'body'           => 'nullable|string|max:5000',
        ]);
        $ticket = SupportTicket::create([
            'ref'            => 'TKT-' . strtoupper(Str::random(6)),
            'subject'        => $data['subject'],
            'customer_name'  => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'],
            'priority'       => $data['priority'],
            'due_at'         => $data['due_at'] ?? null,
            'status'         => 'open',
        ]);
        if (!empty($data['body'])) {
            $ticket->replies()->create([
                'user_id' => auth()->id(), 'author_name' => auth()->user()->name,
                'body' => $data['body'], 'is_staff' => true,
            ]);
            $ticket->forceFill(['first_responded_at' => now()])->save();
        }
        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(SupportTicket $ticket)
    {
        if ($vid = auth()->user()->vendorId()) {
            abort_unless(optional($ticket->order)->items()->where('vendor_id', $vid)->exists(), 403);
        }
        $ticket->load('replies', 'assignee', 'order');
        $staff  = User::orderBy('name')->get(['id', 'name']);
        $canned = CannedReply::orderBy('title')->get();
        return view('admin.tickets.show', compact('ticket', 'staff', 'canned'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        if ($vid = auth()->user()->vendorId()) {
            abort_unless(optional($ticket->order)->items()->where('vendor_id', $vid)->exists(), 403);
        }
        $data = $request->validate([
            'body'   => 'required|string|max:5000',
            'status' => 'nullable|in:open,pending,closed',
        ]);
        $ticket->replies()->create([
            'user_id' => auth()->id(), 'author_name' => auth()->user()->name,
            'body' => $data['body'], 'is_staff' => true,
        ]);
        if (!$ticket->first_responded_at) $ticket->first_responded_at = now();
        if (!empty($data['status'])) $ticket->status = $data['status'];
        $ticket->save();
        return back()->with('success', 'Reply sent.');
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        if ($vid = auth()->user()->vendorId()) {
            abort_unless(optional($ticket->order)->items()->where('vendor_id', $vid)->exists(), 403);
        }
        $data = $request->validate([
            'status'      => 'required|in:open,pending,closed',
            'priority'    => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_at'      => 'nullable|date',
        ]);
        $ticket->update($data);
        return back()->with('success', 'Ticket updated.');
    }
}
