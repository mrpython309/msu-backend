<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\Department\DepartmentRequest;

class DepartmentController extends Controller
{

    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $departments = $this->filteredDepartments($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('department.index', compact('departments', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->departmentRows($request), ['S.No', 'Department', 'Status'], 'departments');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Departments', ['S.No', 'Department', 'Status'], $this->departmentRows($request), 'departments');
    }

    private function filteredDepartments(Request $request): Builder
    {
        $query = Department::query();
        MasterListing::applySearch($query, $request, ['department_name']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function departmentRows(Request $request)
    {
        return $this->filteredDepartments($request)->orderByDesc('id')->get()->values()->map(
            fn (Department $department, int $index) => [$index + 1, $department->department_name, ucfirst((string) $department->status)]
        );
    }

    public function create()
    {
        return view('department.create');
    }

    public function store(DepartmentRequest $request)
    {
        $validated = $request->validated();

        $department=Department::create($validated);

        // $user=auth()->user();

        // $user->departments()->syncWithoutDetaching([$department->id]);

        return redirect()
            ->route('department.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('department.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $validated = $request->validated();

        $department->update($validated);

        return redirect()
            ->route('department.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()
            ->route('department.index')
            ->with('success', 'Department deleted successfully.');
    }

    
    public function updateStatus(Request $request)
    {
        $department = Department::findOrFail($request->id);

        $department->status = $request->status;
        $department->save();

        return response()->json(['success' => true]);
    }
    
}
