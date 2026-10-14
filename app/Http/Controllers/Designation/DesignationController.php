<?php
namespace App\Http\Controllers\Designation;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\Designation\DesignationRequest;
class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $designations = $this->filteredDesignations($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('designation.index', compact('designations', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->designationRows($request), ['S.No', 'Designation', 'Status'], 'designations');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Designations', ['S.No', 'Designation', 'Status'], $this->designationRows($request), 'designations');
    }

    private function filteredDesignations(Request $request): Builder
    {
        $query = Designation::query();
        MasterListing::applySearch($query, $request, ['designation_name']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function designationRows(Request $request)
    {
        return $this->filteredDesignations($request)->orderByDesc('id')->get()->values()->map(
            fn (Designation $designation, int $index) => [$index + 1, $designation->designation_name, ucfirst((string) $designation->status)]
        );
    }

    public function create()
    {
        return view('designation.create');
    }

    public function edit(Designation $designation)
    {
        return view('designation.edit', compact('designation'));
    }

    public function store(DesignationRequest $request)
    {
        $validated = $request->validated();

        Designation::create($validated);

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation created successfully.');
    }

    public function update(DesignationRequest $request, Designation $designation)
    {
        $validated = $request->validated();

        $designation->update($validated);

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $designation = Designation::findOrFail($request->id);

        $designation->status = $request->status;
        $designation->save();

        return response()->json(['success' => true]);
    }

}
