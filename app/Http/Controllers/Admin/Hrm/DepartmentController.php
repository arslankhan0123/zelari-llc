<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmDepartment;
use App\Models\HrmEmployee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = HrmDepartment::with('parent', 'head')->withCount('employees')->orderBy('name')->get();
        $employees   = HrmEmployee::orderBy('name')->get(['id', 'name']);
        return view('admin.hrm.departments.index', compact('departments', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:120',
            'code'             => 'nullable|string|max:40',
            'parent_id'        => 'nullable|exists:hrm_departments,id',
            'head_employee_id' => 'nullable|exists:hrm_employees,id',
            'description'      => 'nullable|string|max:500',
        ]);
        HrmDepartment::create($data);
        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, HrmDepartment $department)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:120',
            'code'             => 'nullable|string|max:40',
            'parent_id'        => 'nullable|exists:hrm_departments,id',
            'head_employee_id' => 'nullable|exists:hrm_employees,id',
            'description'      => 'nullable|string|max:500',
        ]);
        if ((int) ($data['parent_id'] ?? 0) === $department->id) { $data['parent_id'] = null; }
        $department->update($data);
        return back()->with('success', 'Department updated.');
    }

    public function destroy(HrmDepartment $department)
    {
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}
