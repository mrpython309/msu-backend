<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\UnitRequest;
use App\Models\Unit;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $units = $this->filteredUnits($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('units.index', compact('units', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->unitRows($request), ['S.No', 'Unit Name', 'Status'], 'units');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Units', ['S.No', 'Unit Name', 'Status'], $this->unitRows($request), 'units');
    }

    private function filteredUnits(Request $request): Builder
    {
        $query = Unit::query();
        MasterListing::applySearch($query, $request, ['unit_name']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function unitRows(Request $request)
    {
        return $this->filteredUnits($request)->orderByDesc('id')->get()->values()->map(
            fn (Unit $unit, int $index) => [$index + 1, $unit->unit_name, ucfirst((string) $unit->status)]
        );
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(UnitRequest $request)
    {
        Unit::create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->items()->exists()) {
            return redirect()
                ->route('units.index')
                ->with('error', 'Cannot delete unit linked to item masters.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $unit = Unit::findOrFail($request->id);
        $unit->status = $request->status;
        $unit->save();

        return response()->json(['success' => true]);
    }
}
