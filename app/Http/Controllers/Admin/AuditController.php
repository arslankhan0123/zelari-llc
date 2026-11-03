<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('model'))  $query->where('model', $request->model);
        $logs   = $query->paginate(30)->withQueryString();
        $models = AuditLog::select('model')->distinct()->pluck('model');
        return view('admin.audit.index', compact('logs', 'models'));
    }
}
