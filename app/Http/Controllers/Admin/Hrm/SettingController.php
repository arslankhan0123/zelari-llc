<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmSetting;
use App\Models\HrmLeaveType;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = HrmSetting::pluck('value', 'key')->all();
        $leaveTypes = HrmLeaveType::orderBy('name')->get();
        return view('admin.hrm.settings.index', compact('settings', 'leaveTypes'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hrm_company_name'  => 'nullable|string|max:120',
            'hrm_work_days'     => 'nullable|string|max:60',
            'hrm_work_start'    => 'nullable|string|max:10',
            'hrm_work_end'      => 'nullable|string|max:10',
            'hrm_payroll_cycle' => 'nullable|in:monthly,biweekly,weekly',
            'hrm_currency'      => 'nullable|string|max:10',
        ]);
        HrmSetting::setMany($data);
        return back()->with('success', 'HR settings saved.');
    }

    public function storeLeaveType(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:80',
            'annual_quota' => 'required|integer|min:0|max:365',
            'paid'         => 'nullable|boolean',
        ]);
        HrmLeaveType::create(['name' => $data['name'], 'annual_quota' => $data['annual_quota'], 'paid' => (bool) $request->input('paid'), 'active' => true]);
        return back()->with('success', 'Leave type added.');
    }

    public function destroyLeaveType(HrmLeaveType $leaveType)
    {
        $leaveType->delete();
        return back()->with('success', 'Leave type removed.');
    }
}
