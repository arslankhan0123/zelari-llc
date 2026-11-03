<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use Illuminate\Http\Request;

class CannedReplyController extends Controller
{
    public function index()
    {
        $replies = CannedReply::orderBy('title')->get();
        return view('admin.tickets.canned', compact('replies'));
    }
    public function store(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'body' => 'required|string|max:5000']);
        CannedReply::create($data);
        return back()->with('success', 'Canned reply saved.');
    }
    public function update(Request $request, CannedReply $canned)
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'body' => 'required|string|max:5000']);
        $canned->update($data);
        return back()->with('success', 'Canned reply updated.');
    }
    public function destroy(CannedReply $canned)
    {
        $canned->delete();
        return back()->with('success', 'Canned reply removed.');
    }
}
