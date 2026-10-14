<?php

namespace App\Http\Controllers\AssetCustodian;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCustodian\AssetCustodianRequest;
use App\Models\AssetCustodian;
use App\Models\Department;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCustodianController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $custodians = $this->filteredCustodians($request)->orderByDesc('id')->paginate($perPage)->withQueryString();
        $departments = Department::orderBy('department_name')->get();

        return view('asset-custodians.index', compact('custodians', 'departments', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->custodianRows($request),
            ['S.No', 'Custodian Name', 'Department', 'Mobile Number', 'Email', 'Status'],
            'asset-custodians'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Asset Custodian',
            ['S.No', 'Custodian Name', 'Department', 'Mobile Number', 'Email', 'Status'],
            $this->custodianRows($request),
            'asset-custodians'
        );
    }

    private function filteredCustodians(Request $request): Builder
    {
        $query = AssetCustodian::with('department');
        MasterListing::applySearch($query, $request, ['employee_name', 'mobile_number', 'email', 'department.department_name']);
        MasterListing::applyStatus($query, $request);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        return $query;
    }

    private function custodianRows(Request $request)
    {
        return $this->filteredCustodians($request)->orderByDesc('id')->get()->values()->map(
            fn (AssetCustodian $custodian, int $index) => [
                $index + 1,
                $custodian->employee_name,
                $custodian->department?->department_name,
                $custodian->mobile_number,
                $custodian->email,
                ucfirst((string) $custodian->status),
            ]
        );
    }

    public function create()
    {
        $departments = Department::active()->orderBy('department_name')->get();

        return view('asset-custodians.create', compact('departments'));
    }

    public function store(AssetCustodianRequest $request)
    {
        AssetCustodian::create($request->validated());

        return redirect()
            ->route('asset-custodians.index')
            ->with('success', 'Asset custodian created successfully.');
    }

    public function edit(AssetCustodian $assetCustodian)
    {
        $departments = Department::query()
            ->where(function ($q) use ($assetCustodian) {
                $q->active()->orWhere('id', $assetCustodian->department_id);
            })
            ->orderBy('department_name')
            ->get();

        return view('asset-custodians.edit', compact('assetCustodian', 'departments'));
    }

    public function update(AssetCustodianRequest $request, AssetCustodian $assetCustodian)
    {
        $assetCustodian->update($request->validated());

        return redirect()
            ->route('asset-custodians.index')
            ->with('success', 'Asset custodian updated successfully.');
    }

    public function destroy(AssetCustodian $assetCustodian)
    {
        $assetCustodian->delete();

        return redirect()
            ->route('asset-custodians.index')
            ->with('success', 'Asset custodian deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $custodian = AssetCustodian::findOrFail($request->id);
        $custodian->status = $request->status;
        $custodian->save();

        return response()->json(['success' => true]);
    }

    /**
     * Minimal JSON create for the asset form "+" (same flow as quick supplier).
     */
    public function quickStore(Request $request): JsonResponse
    {
        $request->merge([
            'employee_name' => trim((string) $request->input('employee_name', '')),
            'department_id' => $request->filled('department_id') ? $request->input('department_id') : null,
            'mobile_number' => trim((string) $request->input('mobile_number', '')) ?: null,
            'email' => trim((string) $request->input('email', '')) ?: null,
        ]);

        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'mobile_number' => 'nullable|string|max:20|unique:asset_custodians,mobile_number',
            'email' => 'nullable|email|max:255|unique:asset_custodians,email',
        ]);

        $name = $validated['employee_name'];
        $normalized = mb_strtolower($name);

        $existing = AssetCustodian::query()
            ->whereRaw('LOWER(TRIM(employee_name)) = ?', [$normalized])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existing->id,
                    'name' => $existing->employee_name,
                ],
                'message' => 'Asset custodian already exists.',
            ]);
        }

        $custodian = AssetCustodian::query()->create([
            'employee_name' => $name,
            'department_id' => $validated['department_id'] ?? null,
            'mobile_number' => $validated['mobile_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $custodian->id,
                'name' => $custodian->employee_name,
            ],
            'message' => 'Asset custodian created successfully.',
        ], 201);
    }
}
