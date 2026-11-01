<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmAttendance;
use App\Models\HrmEmployee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $employees = HrmEmployee::where('status', '!=', 'terminated')->orderBy('name')->get();
        $records = HrmAttendance::whereDate('date', $date)->get()->keyBy('employee_id');

        $summary = [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'leave'   => $records->where('status', 'leave')->count(),
            'total'   => $employees->count(),
        ];
        return view('admin.hrm.attendance.index', compact('employees', 'records', 'date', 'summary'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'date'              => 'required|date',
            'rows'              => 'required|array',
            'rows.*.status'     => 'nullable|in:present,absent,leave,half_day,holiday',
            'rows.*.check_in'   => 'nullable',
            'rows.*.check_out'  => 'nullable',
        ]);
        $date = $data['date'];
        $n = 0;
        foreach ($data['rows'] as $empId => $row) {
            if (empty($row['status'])) continue;
            HrmAttendance::updateOrCreate(
                ['employee_id' => (int) $empId, 'date' => $date],
                [
                    'status'    => $row['status'],
                    'check_in'  => $row['check_in'] ?: null,
                    'check_out' => $row['check_out'] ?: null,
                ]
            );
            $n++;
        }
        return back()->with('success', "Attendance saved for {$n} employee(s) on {$date}.");
    }
}
