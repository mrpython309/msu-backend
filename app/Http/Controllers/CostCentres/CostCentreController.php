<?php

namespace App\Http\Controllers\CostCentres;

use App\Http\Controllers\Controller;
use App\Http\Requests\CostCentres\CostCentreRequest;
use App\Models\Asset;
use App\Models\CostCentre;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostCentreController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $costCentres = $this->filteredCostCentres($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('cost-centres.index', compact('costCentres', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->costCentreRows($request), ['S.No', 'Cost Centre', 'Status'], 'cost-centres');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Cost Centres', ['S.No', 'Cost Centre', 'Status'], $this->costCentreRows($request), 'cost-centres');
    }

    private function filteredCostCentres(Request $request): Builder
    {
        $query = CostCentre::query();
        MasterListing::applySearch($query, $request, ['cost_centre_name', 'cost_centre_description']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function costCentreRows(Request $request)
    {
        return $this->filteredCostCentres($request)->orderByDesc('id')->get()->values()->map(
            fn (CostCentre $costCentre, int $index) => [$index + 1, $costCentre->cost_centre_name, ucfirst((string) $costCentre->status)]
        );
    }

    public function create()
    {
        return view('cost-centres.create');
    }

    /**
     * Minimal JSON create for the asset form "+".
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cost_centre_name' => 'required|string|max:255',
            'cost_centre_description' => 'nullable|string',
        ]);

        $name = trim($validated['cost_centre_name']);

        $existing = CostCentre::query()
            ->whereRaw('LOWER(TRIM(cost_centre_name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->update(['status' => 'active']);
            }

            return response()->json([
                'success' => true,
                'data' => $existing->only(['id', 'cost_centre_name']),
                'message' => 'Cost Centre already exists.',
            ]);
        }

        $costCentre = CostCentre::query()->create([
            'cost_centre_name' => $name,
            'cost_centre_description' => $validated['cost_centre_description'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $costCentre->only(['id', 'cost_centre_name']),
            'message' => 'Cost Centre created successfully.',
        ], 201);
    }

    public function store(CostCentreRequest $request)
    {
        CostCentre::create($request->validated());

        return redirect()
            ->route('cost-centres.index')
            ->with('success', 'Cost Centre created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(CostCentre $costCentre)
    {
        return view('cost-centres.edit', compact('costCentre'));
    }

    public function update(CostCentreRequest $request, CostCentre $costCentre)
    {
        $validated = $request->validated();
        $previousName = $costCentre->cost_centre_name;

        $costCentre->update($validated);

        if ($previousName !== $costCentre->cost_centre_name) {
            Asset::query()
                ->where('cost_centre', $previousName)
                ->update(['cost_centre' => $costCentre->cost_centre_name]);
        }

        return redirect()
            ->route('cost-centres.index')
            ->with('success', 'Cost Centre updated successfully.');
    }

    public function destroy(CostCentre $costCentre)
    {
        if ($costCentre->hasDependencies()) {
            return back()->with('error', 'cannot delete cost centre because it has mapped with other records');
        }

        $costCentre->delete();

        return redirect()
            ->route('cost-centres.index')
            ->with('success', 'Cost Centre deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $costCentre = CostCentre::findOrFail($request->id);

        $costCentre->status = $request->status;
        $costCentre->save();

        return response()->json(['success' => true]);
    }
}
