<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class LiveChatController extends Controller
{
    public function index()
    {
        ChatMessage::ensureTable();
        $hours = \App\Http\Controllers\Shop\ChatbotController::agentHours();
        return view('admin.chats.index', compact('hours'));
    }

    /** Save agent availability hours (shown to visitors in the chat widget). */
    public function saveHours(Request $request)
    {
        $data = $request->validate([
            'start' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'end'   => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'tz'    => ['required', 'string', 'max:50', 'in:America/New_York,America/Chicago,America/Denver,America/Los_Angeles,UTC,Europe/London,Asia/Karachi'],
        ]);
        \App\Models\Setting::setMany([
            'chat_start' => $data['start'],
            'chat_end'   => $data['end'],
            'chat_tz'    => $data['tz'],
        ]);
        return response()->json(['ok' => true, 'hours' => \App\Http\Controllers\Shop\ChatbotController::agentHours()]);
    }

    /** JSON: one row per conversation, newest activity first. */
    public function threads()
    {
        ChatMessage::ensureTable();

        $agg = ChatMessage::selectRaw("thread_token, MAX(id) as last_id, MAX(created_at) as last_at, SUM(CASE WHEN sender = 'user' AND read_by_agent = 0 THEN 1 ELSE 0 END) as unread")
            ->groupBy('thread_token')
            ->orderByDesc('last_at')
            ->limit(100)
            ->get();

        if ($agg->isEmpty()) {
            return response()->json(['threads' => []]);
        }

        $lastMsgs = ChatMessage::whereIn('id', $agg->pluck('last_id'))->get()->keyBy('thread_token');
        $contacts = ChatMessage::whereIn('thread_token', $agg->pluck('thread_token'))
            ->where('sender', 'user')->whereNotNull('email')
            ->orderByDesc('id')->get()->unique('thread_token')->keyBy('thread_token');

        $threads = $agg->map(function ($row) use ($lastMsgs, $contacts) {
            $last = $lastMsgs->get($row->thread_token);
            $who  = $contacts->get($row->thread_token);
            return [
                'token'   => $row->thread_token,
                'name'    => $who->name ?? 'Visitor',
                'email'   => $who->email ?? '',
                'last'    => $last ? mb_substr($last->message, 0, 60) : '',
                'sender'  => $last->sender ?? 'user',
                'time'    => $last ? $last->created_at->diffForHumans(null, true) : '',
                'unread'  => (int) $row->unread,
            ];
        })->values();

        return response()->json(['threads' => $threads]);
    }

    /** JSON: messages of one thread after a given id; marks user messages as read. */
    public function messages(Request $request)
    {
        ChatMessage::ensureTable();
        $token = (string) $request->query('token', '');
        $after = (int) $request->query('after', 0);
        if ($token === '') return response()->json(['messages' => []]);

        $msgs = ChatMessage::where('thread_token', $token)
            ->where('id', '>', $after)
            ->orderBy('id')->limit(200)->get();

        ChatMessage::where('thread_token', $token)
            ->where('sender', 'user')->where('read_by_agent', false)
            ->update(['read_by_agent' => true]);

        return response()->json([
            'messages' => $msgs->map(fn ($m) => [
                'id'      => $m->id,
                'sender'  => $m->sender,
                'message' => $m->message,
                'name'    => $m->name,
                'time'    => $m->created_at->format('d M, g:i A'),
            ])->values(),
        ]);
    }

    /** Agent sends a reply into a thread. */
    public function send(Request $request)
    {
        ChatMessage::ensureTable();
        $data = $request->validate([
            'token'   => 'required|string|max:64',
            'message' => 'required|string|max:2000',
        ]);

        $m = ChatMessage::create([
            'thread_token'  => $data['token'],
            'name'          => auth()->user()->name ?? 'Support',
            'email'         => null,
            'user_id'       => auth()->id(),
            'sender'        => 'agent',
            'message'       => $data['message'],
            'read_by_agent' => true,
            'read_by_user'  => false,
        ]);

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $m->id, 'sender' => 'agent', 'message' => $m->message,
                'name' => $m->name, 'time' => $m->created_at->format('d M, g:i A'),
            ],
        ]);
    }
}
