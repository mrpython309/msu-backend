<?php

namespace App\Http\Controllers\EmployeeMaster;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeMaster\EmployeeMasterRequest;
use App\Models\Department;
use App\Models\EmployeeMaster;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EmployeeMasterController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $employees = $this->filteredEmployees($request)->orderByDesc('id')->paginate($perPage)->withQueryString();
        $departments = Department::orderBy('department_name')->get();

        return view('employee-masters.index', compact('employees', 'departments', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->employeeRows($request),
            ['S.No', 'Employee Name', 'Department', 'Mobile Number', 'Email', 'Status'],
            'employee-masters'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Employee Master',
            ['S.No', 'Employee Name', 'Department', 'Mobile Number', 'Email', 'Status'],
            $this->employeeRows($request),
            'employee-masters'
        );
    }

    private function filteredEmployees(Request $request): Builder
    {
        $query = EmployeeMaster::with('department');
        MasterListing::applySearch($query, $request, ['employee_name', 'mobile_number', 'email', 'department.department_name']);
        MasterListing::applyStatus($query, $request);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        return $query;
    }

    private function employeeRows(Request $request)
    {
        return $this->filteredEmployees($request)->orderByDesc('id')->get()->values()->map(
            fn (EmployeeMaster $employee, int $index) => [
                $index + 1,
                $employee->employee_name,
                $employee->department?->department_name,
                $employee->mobile_number,
                $employee->email,
                ucfirst((string) $employee->status),
            ]
        );
    }

    public function create()
    {
        $departments = Department::active()->orderBy('department_name')->get();

        return view('employee-masters.create', compact('departments'));
    }

    public function store(EmployeeMasterRequest $request)
    {
        EmployeeMaster::create($request->validated());

        return redirect()
            ->route('employee-masters.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(EmployeeMaster $employeeMaster)
    {
        $departments = Department::query()
            ->where(function ($q) use ($employeeMaster) {
                $q->active()->orWhere('id', $employeeMaster->department_id);
            })
            ->orderBy('department_name')
            ->get();

        return view('employee-masters.edit', compact('employeeMaster', 'departments'));
    }

    public function update(EmployeeMasterRequest $request, EmployeeMaster $employeeMaster)
    {
        $employeeMaster->update($request->validated());

        return redirect()
            ->route('employee-masters.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(EmployeeMaster $employeeMaster)
    {
        $employeeMaster->delete();

        return redirect()
            ->route('employee-masters.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $employee = EmployeeMaster::findOrFail($request->id);
        $employee->status = $request->status;
        $employee->save();

        return response()->json(['success' => true]);
    }
}
