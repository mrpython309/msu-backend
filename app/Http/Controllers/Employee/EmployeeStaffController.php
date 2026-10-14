<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveEmployeeStaffRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeStaffController extends Controller
{
    public function index(): View
    {
        $employees = User::query()
            ->with(['designation', 'companies'])
            ->where('id', '!=', 1)
            ->where(function ($q): void {
                $q->where('is_user', '!=', 'yes')
                    ->orWhereNull('is_user');
            })
            ->orderByDesc('id')
            ->paginate(50);

        return view('employee-staff.index', compact('employees'));
    }

    public function create(): View
    {
        $designations = Designation::where('status', 'active')->get();
        $companies = Company::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();

        return view('employee-staff.create', compact('designations', 'companies', 'departments'));
    }

    public function store(SaveEmployeeStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_user'] = 'no';
        unset($data['role_id'], $data['password']);

        $companiesId = $data['company_id'] ?? [];
        unset($data['company_id']);

        $branchIds = $data['branch_id'] ?? [];
        unset($data['branch_id']);

        $departmentIds = $data['department_id'] ?? [];
        unset($data['department_id']);

        $user = User::create($data);

        if (is_array($branchIds) && count($branchIds) > 0) {
            $user->branches()->sync($branchIds);
        }

        if (is_array($departmentIds) && count($departmentIds) > 0) {
            $user->departments()->sync($departmentIds);
        }

        if (is_array($companiesId) && count($companiesId) > 0) {
            $user->companies()->sync($companiesId);
        }

        return redirect()->route('employee-staff.index')->with('success', 'Employee created successfully.');
    }

    public function edit(User $employee): View
    {
        $this->ensureStaffRecord($employee);

        $designations = Designation::where('status', 'active')->get();
        $companies = Company::where('status', 'active')->get();
        $branches = Branch::whereIn('company_id', $employee->companies()->pluck('companies.id')->toArray())
            ->where('status', 'active')
            ->get();
        $departments = Department::where('status', 'active')->get();

        return view('employee-staff.edit', compact('designations', 'companies', 'employee', 'branches', 'departments'));
    }

    public function update(SaveEmployeeStaffRequest $request, User $employee): RedirectResponse
    {
        $this->ensureStaffRecord($employee);

        $data = $request->validated();
        $data['is_user'] = 'no';
        unset($data['role_id'], $data['password']);

        $companiesId = $data['company_id'] ?? [];
        unset($data['company_id']);

        $branchIds = $data['branch_id'] ?? [];
        unset($data['branch_id']);

        $departmentIds = $data['department_id'] ?? [];
        unset($data['department_id']);

        $employee->update($data);

        if (is_array($branchIds) && count($branchIds) > 0) {
            $employee->branches()->sync($branchIds);
        }

        if (is_array($departmentIds) && count($departmentIds) > 0) {
            $employee->departments()->sync($departmentIds);
        }

        if (is_array($companiesId) && count($companiesId) > 0) {
            $employee->companies()->sync($companiesId);
        }

        return redirect()->route('employee-staff.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        $this->ensureStaffRecord($employee);
        $employee->delete();

        return redirect()->route('employee-staff.index')->with('success', 'Employee deleted successfully.');
    }

    private function ensureStaffRecord(User $employee): void
    {
        if ((int) $employee->id === 1 || $employee->is_user === 'yes') {
            abort(404);
        }
    }
}
