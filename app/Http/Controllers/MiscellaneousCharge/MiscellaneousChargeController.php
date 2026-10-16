<?php

namespace App\Http\Controllers\MiscellaneousCharge;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiscellaneousCharge\MiscellaneousChargeRequest;
use App\Models\MiscellaneousCharge;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MiscellaneousChargeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $miscellaneousCharges = $this->filteredMiscellaneousCharges($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('miscellaneous-charges.index', compact('miscellaneousCharges', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->miscellaneousChargeRows($request),
            ['S.No', 'Description', 'Short Name', 'Status'],
            'miscellaneous-charges'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Miscellaneous Charges',
            ['S.No', 'Description', 'Short Name', 'Status'],
            $this->miscellaneousChargeRows($request),
            'miscellaneous-charges'
        );
    }

    private function filteredMiscellaneousCharges(Request $request): Builder
    {
        $query = MiscellaneousCharge::query();
        MasterListing::applySearch($query, $request, ['charge_name', 'code']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function miscellaneousChargeRows(Request $request)
    {
        return $this->filteredMiscellaneousCharges($request)->orderByDesc('id')->get()->values()->map(
            fn (MiscellaneousCharge $charge, int $index) => [
                $index + 1,
                $charge->charge_name,
                $charge->code,
                ucfirst((string) $charge->status),
            ]
        );
    }

    public function create()
    {
        return view('miscellaneous-charges.create');
    }

    public function store(MiscellaneousChargeRequest $request)
    {
        MiscellaneousCharge::create($request->validated());

        return redirect()
            ->route('miscellaneous-charges.index')
            ->with('success', 'Miscellaneous charge created successfully.');
    }

    public function edit(MiscellaneousCharge $miscellaneousCharge)
    {
        return view('miscellaneous-charges.edit', compact('miscellaneousCharge'));
    }

    public function update(MiscellaneousChargeRequest $request, MiscellaneousCharge $miscellaneousCharge)
    {
        $miscellaneousCharge->update($request->validated());

        return redirect()
            ->route('miscellaneous-charges.index')
            ->with('success', 'Miscellaneous charge updated successfully.');
    }

    public function destroy(MiscellaneousCharge $miscellaneousCharge)
    {
        if ($miscellaneousCharge->msuPurchaseOrders()->exists()) {
            return redirect()
                ->route('miscellaneous-charges.index')
                ->with('error', 'Cannot delete miscellaneous charge linked to purchase orders.');
        }

        $miscellaneousCharge->delete();

        return redirect()
            ->route('miscellaneous-charges.index')
            ->with('success', 'Miscellaneous charge deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $charge = MiscellaneousCharge::findOrFail($request->id);
        $charge->status = $request->status;
        $charge->save();

        return response()->json(['success' => true]);
    }

    public function listForManage(): JsonResponse
    {
        $items = MiscellaneousCharge::query()
            ->orderBy('charge_name')
            ->get()
            ->map(fn (MiscellaneousCharge $charge) => $charge->toOptionArray())
            ->values();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function bulkSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:miscellaneous_charges,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.code' => 'nullable|string|max:50',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.active' => 'required|boolean',
            'deleted_ids' => 'nullable|array',
            'deleted_ids.*' => 'integer',
        ]);

        $deletedIds = collect($validated['deleted_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $rows = [];
        $usedCodes = [];
        $usedNames = [];

        foreach ($validated['items'] as $item) {
            $description = trim($item['description']);
            $code = strtoupper(trim((string) ($item['code'] ?? '')));
            if ($code === '') {
                $code = strtoupper(Str::slug($description, '_')) ?: 'MISC';
                $code = substr($code, 0, 50);
            }

            $base = $code;
            $n = 1;
            while (isset($usedCodes[$code])) {
                $suffix = '_'.$n;
                $code = substr($base, 0, max(1, 50 - strlen($suffix))).$suffix;
                $n++;
            }

            $nameKey = mb_strtolower($description);
            if (isset($usedNames[$nameKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate description "'.$description.'" in the list.',
                ], 422);
            }

            $usedCodes[$code] = true;
            $usedNames[$nameKey] = true;

            $rows[] = [
                'id' => ! empty($item['id']) ? (int) $item['id'] : null,
                'description' => $description,
                'code' => $code,
                'amount' => (float) ($item['amount'] ?? 0),
                'active' => (bool) $item['active'],
            ];
        }

        foreach ($rows as $row) {
            $codeExists = MiscellaneousCharge::query()
                ->whereRaw('UPPER(code) = ?', [$row['code']])
                ->when($row['id'], fn ($q) => $q->where('id', '!=', $row['id']))
                ->when(! empty($deletedIds), fn ($q) => $q->whereNotIn('id', $deletedIds))
                ->exists();

            if ($codeExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code "'.$row['code'].'" already exists.',
                ], 422);
            }

            $nameExists = MiscellaneousCharge::query()
                ->whereRaw('LOWER(TRIM(charge_name)) = ?', [mb_strtolower($row['description'])])
                ->when($row['id'], fn ($q) => $q->where('id', '!=', $row['id']))
                ->when(! empty($deletedIds), fn ($q) => $q->whereNotIn('id', $deletedIds))
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Description "'.$row['description'].'" already exists.',
                ], 422);
            }
        }

        DB::transaction(function () use ($rows, $deletedIds) {
            foreach ($deletedIds as $id) {
                $charge = MiscellaneousCharge::find($id);
                if (! $charge) {
                    continue;
                }
                if ($charge->msuPurchaseOrders()->exists()) {
                    throw new HttpResponseException(response()->json([
                        'success' => false,
                        'message' => 'Cannot delete "'.$charge->charge_name.'" because it is linked to purchase orders. Mark it inactive instead.',
                    ], 422));
                }
                $charge->delete();
            }

            foreach ($rows as $row) {
                $payload = [
                    'charge_name' => $row['description'],
                    'code' => $row['code'],
                    'amount' => $row['amount'],
                    'status' => $row['active'] ? 'active' : 'inactive',
                ];

                if ($row['id']) {
                    MiscellaneousCharge::whereKey($row['id'])->update($payload);
                } else {
                    MiscellaneousCharge::create($payload);
                }
            }
        });

        $active = MiscellaneousCharge::active()
            ->orderBy('charge_name')
            ->get()
            ->map(fn (MiscellaneousCharge $charge) => $charge->toOptionArray())
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Miscellaneous charges saved successfully.',
            'data' => $active,
        ]);
    }
}
