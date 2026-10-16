<?php

namespace App\Http\Controllers\Hsn;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hsn\HsnRequest;
use App\Models\Hsn;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HsnController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $hsns = $this->filteredHsns($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('hsns.index', compact('hsns', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->hsnRows($request), ['S.No', 'HSN Code', 'Description', 'Tax Rate (%)', 'Status'], 'hsns');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('HSN Master', ['S.No', 'HSN Code', 'Description', 'Tax Rate (%)', 'Status'], $this->hsnRows($request), 'hsns');
    }

    private function filteredHsns(Request $request): Builder
    {
        $query = Hsn::query();
        MasterListing::applySearch($query, $request, ['hsn_code', 'description']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function hsnRows(Request $request)
    {
        return $this->filteredHsns($request)->orderByDesc('id')->get()->values()->map(
            fn (Hsn $hsn, int $index) => [
                $index + 1,
                $hsn->hsn_code,
                $hsn->description,
                number_format((float) $hsn->tax_rate, 2),
                ucfirst((string) $hsn->status),
            ]
        );
    }

    public function create()
    {
        return view('hsns.create');
    }

    public function store(HsnRequest $request)
    {
        Hsn::create($request->validated());

        return redirect()
            ->route('hsns.index')
            ->with('success', 'HSN created successfully.');
    }

    public function edit(Hsn $hsn)
    {
        return view('hsns.edit', compact('hsn'));
    }

    public function update(HsnRequest $request, Hsn $hsn)
    {
        $hsn->update($request->validated());

        return redirect()
            ->route('hsns.index')
            ->with('success', 'HSN updated successfully.');
    }

    public function destroy(Hsn $hsn)
    {
        $hsn->delete();

        return redirect()
            ->route('hsns.index')
            ->with('success', 'HSN deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $hsn = Hsn::findOrFail($request->id);
        $hsn->status = $request->status;
        $hsn->save();

        return response()->json(['success' => true]);
    }
}
