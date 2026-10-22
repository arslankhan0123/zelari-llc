<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('subject', 'like', "%$s%")
                ->orWhere('message', 'like', "%$s%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->paginate(10)->withQueryString();

        $stats = [
            'new'      => ContactMessage::where('status', ContactMessage::NEW)->count(),
            'read'     => ContactMessage::where('status', ContactMessage::READ)->count(),
            'replied'  => ContactMessage::where('status', ContactMessage::REPLIED)->count(),
            'total'    => ContactMessage::count(),
        ];

        return view('admin.messages.index', compact('messages', 'stats'));
    }

    /** Opening a message marks it read, so the sidebar badge only counts what nobody has seen. */
    public function show(Request $request, ContactMessage $message)
    {
        $message->markRead($request->user()?->id);

        return response()->json([
            'id'         => $message->id,
            'name'       => $message->name,
            'email'      => $message->email,
            'subject'    => $message->subject_line,
            'message'    => $message->message,
            'status'     => $message->status,
            'emailed'    => $message->emailed,
            'emailError' => $message->email_error,
            'received'   => $message->created_at->format('d M Y, H:i'),
        ]);
    }

    public function status(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'status' => 'required|in:new,read,replied,archived',
        ]);

        $message->update([
            'status'  => $data['status'],
            'read_at' => $data['status'] === ContactMessage::NEW ? null : ($message->read_at ?: now()),
            'read_by' => $data['status'] === ContactMessage::NEW ? null : ($message->read_by ?: $request->user()?->id),
        ]);

        return back()->with('success', "Message from {$message->name} marked as {$data['status']}.");
    }

    public function note(Request $request, ContactMessage $message)
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $message->update($data);

        return back()->with('success', 'Note saved.');
    }

    public function destroy(ContactMessage $message)
    {
        $name = $message->name;
        $message->delete();

        return back()->with('success', "Message from {$name} deleted.");
    }
}
