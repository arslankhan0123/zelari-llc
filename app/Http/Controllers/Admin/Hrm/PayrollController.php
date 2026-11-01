<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmPayslip;
use App\Models\HrmEmployee;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $period   = $request->get('period', now()->format('Y-m'));
        $payslips = HrmPayslip::with('employee')->where('period', $period)->get();
        $totals = [
            'count'      => $payslips->count(),
            'gross'      => (float) $payslips->sum(fn ($p) => (float) $p->basic + (float) $p->allowances),
            'deductions' => (float) $payslips->sum('deductions'),
            'net'        => (float) $payslips->sum('net'),
            'paid'       => (float) $payslips->where('status', 'paid')->sum('net'),
        ];
        return view('admin.hrm.payroll.index', compact('payslips', 'period', 'totals'));
    }

    /** Create draft payslips for all active employees who don't yet have one this period. */
    public function generate(Request $request)
    {
        $data = $request->validate(['period' => 'required|date_format:Y-m']);
        $period = $data['period'];
        $existing = HrmPayslip::where('period', $period)->pluck('employee_id')->all();
        $n = 0;
        foreach (HrmEmployee::where('status', 'active')->get() as $emp) {
            if (in_array($emp->id, $existing)) continue;
            $basic = (float) $emp->base_salary;
            HrmPayslip::create([
                'employee_id' => $emp->id, 'period' => $period,
                'basic' => $basic, 'allowances' => 0, 'deductions' => 0, 'net' => $basic, 'status' => 'draft',
            ]);
            $n++;
        }
        return back()->with('success', "Generated {$n} payslip(s) for {$period}.");
    }

    public function update(Request $request, HrmPayslip $payslip)
    {
        $data = $request->validate([
            'basic'      => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'note'       => 'nullable|string|max:500',
        ]);
        $data['net'] = round($data['basic'] + $data['allowances'] - $data['deductions'], 2);
        $payslip->update($data);
        return back()->with('success', 'Payslip updated.');
    }

    public function markPaid(HrmPayslip $payslip)
    {
        $payslip->update(['status' => 'paid', 'paid_at' => now()]);
        return back()->with('success', 'Payslip marked as paid.');
    }

    public function destroy(HrmPayslip $payslip)
    {
        $payslip->delete();
        return back()->with('success', 'Payslip removed.');
    }
}
