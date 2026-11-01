<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmEmployee;
use App\Models\HrmDepartment;
use App\Models\HrmLeave;
use App\Models\HrmAttendance;
use App\Models\HrmPayslip;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $stats = [
            'employees'    => HrmEmployee::count(),
            'active'       => HrmEmployee::where('status', 'active')->count(),
            'departments'  => HrmDepartment::count(),
            'present_today'=> HrmAttendance::whereDate('date', $today)->where('status', 'present')->count(),
            'pending_leave'=> HrmLeave::where('status', 'pending')->count(),
            'payroll_month'=> (float) HrmPayslip::where('period', now()->format('Y-m'))->sum('net'),
        ];
        $recentLeaves = HrmLeave::with('employee', 'type')->latest()->take(6)->get();
        $byDept = HrmDepartment::withCount('employees')->orderByDesc('employees_count')->take(8)->get();

        return view('admin.hrm.index', compact('stats', 'recentLeaves', 'byDept'));
    }
}
