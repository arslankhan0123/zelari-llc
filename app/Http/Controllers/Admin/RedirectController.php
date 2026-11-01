<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::latest()->get();
        return view('admin.redirects.index', compact('redirects'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_path' => 'required|string|max:255|unique:redirects,from_path',
            'to_path'   => 'required|string|max:255',
            'code'      => 'required|in:301,302',
        ]);
        Redirect::create($data + ['enabled' => true]);
        return back()->with('success', 'Redirect added.');
    }
    public function update(Request $request, Redirect $redirect)
    {
        $data = $request->validate([
            'from_path' => 'required|string|max:255|unique:redirects,from_path,' . $redirect->id,
            'to_path'   => 'required|string|max:255',
            'code'      => 'required|in:301,302',
            'enabled'   => 'nullable|boolean',
        ]);
        $redirect->update(['from_path' => $data['from_path'], 'to_path' => $data['to_path'], 'code' => $data['code'], 'enabled' => $request->boolean('enabled')]);
        return back()->with('success', 'Redirect updated.');
    }
    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        return back()->with('success', 'Redirect removed.');
    }
}
