<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmLeave;
use App\Models\HrmEmployee;
use App\Models\HrmLeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $q = HrmLeave::with('employee', 'type')->latest();
        if ($st = $request->get('status')) { $q->where('status', $st); }
        $leaves    = $q->paginate(15)->withQueryString();
        $employees = HrmEmployee::where('status', '!=', 'terminated')->orderBy('name')->get(['id', 'name']);
        $types     = HrmLeaveType::where('active', true)->orderBy('name')->get();
        $counts    = [
            'pending'  => HrmLeave::where('status', 'pending')->count(),
            'approved' => HrmLeave::where('status', 'approved')->count(),
            'rejected' => HrmLeave::where('status', 'rejected')->count(),
        ];
        return view('admin.hrm.leave.index', compact('leaves', 'employees', 'types', 'counts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'   => 'required|exists:hrm_employees,id',
            'leave_type_id' => 'nullable|exists:hrm_leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'nullable|string|max:500',
        ]);
        $days = Carbon::parse($data['from_date'])->diffInDays(Carbon::parse($data['to_date'])) + 1;
        HrmLeave::create($data + ['days' => $days, 'status' => 'pending']);
        return back()->with('success', 'Leave request added.');
    }

    public function approve(HrmLeave $leave)
    {
        $leave->update(['status' => 'approved', 'decided_by' => auth()->id(), 'decided_at' => now()]);
        return back()->with('success', 'Leave approved.');
    }

    public function reject(HrmLeave $leave)
    {
        $leave->update(['status' => 'rejected', 'decided_by' => auth()->id(), 'decided_at' => now()]);
        return back()->with('success', 'Leave rejected.');
    }

    public function destroy(HrmLeave $leave)
    {
        $leave->delete();
        return back()->with('success', 'Leave request removed.');
    }
}
