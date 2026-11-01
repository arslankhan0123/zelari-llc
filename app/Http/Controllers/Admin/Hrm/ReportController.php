<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmEmployee;
use App\Models\HrmDepartment;
use App\Models\HrmLeave;
use App\Models\HrmAttendance;
use App\Models\HrmPayslip;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $headcount = [
            'total'      => HrmEmployee::count(),
            'active'     => HrmEmployee::where('status', 'active')->count(),
            'on_leave'   => HrmEmployee::where('status', 'on_leave')->count(),
            'terminated' => HrmEmployee::where('status', 'terminated')->count(),
        ];
        $byType = HrmEmployee::selectRaw('employment_type, COUNT(*) c')->groupBy('employment_type')->pluck('c', 'employment_type')->all();
        $byDept = HrmDepartment::withCount('employees')->orderByDesc('employees_count')->get();

        [$yy, $mm] = array_pad(explode('-', $month), 2, now()->format('m'));
        $attendance = HrmAttendance::whereYear('date', (int) $yy)->whereMonth('date', (int) $mm)
            ->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status')->all();

        $leaveByStatus = HrmLeave::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status')->all();

        $payroll = [
            'count' => HrmPayslip::where('period', $month)->count(),
            'net'   => (float) HrmPayslip::where('period', $month)->sum('net'),
            'paid'  => (float) HrmPayslip::where('period', $month)->where('status', 'paid')->sum('net'),
        ];

        return view('admin.hrm.reports.index', compact('month', 'headcount', 'byType', 'byDept', 'attendance', 'leaveByStatus', 'payroll'));
    }

    public function export(Request $request)
    {
        $rows = HrmEmployee::with('department')->orderBy('name')->get();
        $csv = "Code,Name,Email,Phone,Department,Designation,Type,Join Date,Base Salary,Status\n";
        foreach ($rows as $e) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', [
                $e->code, $e->name, $e->email, $e->phone, optional($e->department)->name,
                $e->designation, $e->employment_type, optional($e->join_date)->format('Y-m-d'),
                $e->base_salary, $e->status,
            ])) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hrm-employees.csv"',
        ]);
    }
}
