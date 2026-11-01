<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmEmployee;
use App\Models\HrmAttendance;
use App\Models\HrmAttendanceBreak;
use Illuminate\Http\Request;

class ClockController extends Controller
{
    /** Resolve (or create) the employee record for the logged-in user. */
    private function myEmployee(): HrmEmployee
    {
        $user = auth()->user();
        $emp = HrmEmployee::where('user_id', $user->id)->first();
        if (!$emp && $user->email) {
            $emp = HrmEmployee::whereNull('user_id')->where('email', $user->email)->first();
            if ($emp) { $emp->update(['user_id' => $user->id]); }
        }
        if (!$emp) {
            $emp = HrmEmployee::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'employment_type' => 'full_time',
                'status'          => 'active',
                'base_salary'     => 0,
                'join_date'       => now()->toDateString(),
            ]);
        }
        return $emp;
    }

    private function todayAttendance(HrmEmployee $emp): ?HrmAttendance
    {
        return HrmAttendance::where('employee_id', $emp->id)->whereDate('date', now()->toDateString())->first();
    }

    public function index()
    {
        if (auth()->user()->isMasterAdmin()) {
            return redirect()->route('admin.hrm.attendance.index');
        }
        $employee   = $this->myEmployee();
        $attendance = $this->todayAttendance($employee);
        $openBreak  = $attendance?->openBreak();
        $recent     = HrmAttendance::where('employee_id', $employee->id)->orderByDesc('date')->take(10)->get();
        return view('admin.hrm.clock.index', compact('employee', 'attendance', 'openBreak', 'recent'));
    }

    public function clockIn()
    {
        $emp = $this->myEmployee();
        $att = $this->todayAttendance($emp);
        if ($att && $att->check_in) {
            return back()->with('error', 'You have already clocked in today.');
        }
        HrmAttendance::updateOrCreate(
            ['employee_id' => $emp->id, 'date' => now()->toDateString()],
            ['check_in' => now()->format('H:i:s'), 'status' => 'present']
        );
        return back()->with('success', 'Clocked in at ' . now()->format('h:i A') . '.');
    }

    public function clockOut()
    {
        $emp = $this->myEmployee();
        $att = $this->todayAttendance($emp);
        if (!$att || !$att->check_in) {
            return back()->with('error', 'You have not clocked in yet.');
        }
        if ($att->check_out) {
            return back()->with('error', 'You have already clocked out today.');
        }
        // Auto-close any open break before clocking out.
        if ($open = $att->openBreak()) {
            $open->update(['break_end' => now()]);
        }
        $att->update(['check_out' => now()->format('H:i:s')]);
        return back()->with('success', 'Clocked out at ' . now()->format('h:i A') . '.');
    }

    public function breakStart()
    {
        $emp = $this->myEmployee();
        $att = $this->todayAttendance($emp);
        if (!$att || !$att->check_in || $att->check_out) {
            return back()->with('error', 'You must be clocked in to start a break.');
        }
        if ($att->openBreak()) {
            return back()->with('error', 'A break is already running.');
        }
        HrmAttendanceBreak::create(['attendance_id' => $att->id, 'break_start' => now()]);
        return back()->with('success', 'Break started.');
    }

    public function breakEnd()
    {
        $emp = $this->myEmployee();
        $att = $this->todayAttendance($emp);
        $open = $att?->openBreak();
        if (!$open) {
            return back()->with('error', 'No break is currently running.');
        }
        $open->update(['break_end' => now()]);
        return back()->with('success', 'Break ended.');
    }
}
