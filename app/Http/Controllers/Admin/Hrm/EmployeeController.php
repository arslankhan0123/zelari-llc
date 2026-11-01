<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmEmployee;
use App\Models\HrmDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $q = HrmEmployee::with('department')->latest();
        if ($s = $request->get('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }
        if ($d = $request->get('department')) { $q->where('department_id', $d); }
        if ($st = $request->get('status')) { $q->where('status', $st); }

        $employees   = $q->paginate(15)->withQueryString();
        $departments = HrmDepartment::orderBy('name')->get();
        return view('admin.hrm.employees.index', compact('employees', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('hrm', 'public');
        }
        HrmEmployee::create($data);
        return back()->with('success', 'Employee added.');
    }

    public function edit(HrmEmployee $employee)
    {
        $departments = HrmDepartment::orderBy('name')->get();
        return view('admin.hrm.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, HrmEmployee $employee)
    {
        $data = $this->validateData($request, $employee->id);
        if ($request->hasFile('photo')) {
            if ($employee->photo) Storage::disk('public')->delete($employee->photo);
            $data['photo'] = $request->file('photo')->store('hrm', 'public');
        }
        $employee->update($data);
        return redirect()->route('admin.hrm.employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(HrmEmployee $employee)
    {
        if ($employee->photo) Storage::disk('public')->delete($employee->photo);
        $employee->delete();
        return back()->with('success', 'Employee removed.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'            => 'required|string|max:120',
            'code'            => ['nullable', 'string', 'max:40', Rule::unique('hrm_employees', 'code')->ignore($id)],
            'email'           => 'nullable|email|max:160',
            'phone'           => 'nullable|string|max:40',
            'department_id'   => 'nullable|exists:hrm_departments,id',
            'designation'     => 'nullable|string|max:120',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'join_date'       => 'nullable|date',
            'base_salary'     => 'required|numeric|min:0',
            'status'          => 'required|in:active,on_leave,terminated',
            'address'         => 'nullable|string|max:500',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }
}
