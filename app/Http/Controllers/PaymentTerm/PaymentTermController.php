<?php

namespace App\Http\Controllers\PaymentTerm;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTerm\PaymentTermRequest;
use App\Models\PaymentTerm;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentTermController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $paymentTerms = $this->filteredPaymentTerms($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('payment-terms.index', compact('paymentTerms', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->paymentTermRows($request),
            ['S.No', 'Payment Mode', 'Payment Description', 'Short Name', 'Status'],
            'payment-terms'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Payment Terms',
            ['S.No', 'Payment Mode', 'Payment Description', 'Short Name', 'Status'],
            $this->paymentTermRows($request),
            'payment-terms'
        );
    }

    private function filteredPaymentTerms(Request $request): Builder
    {
        $query = PaymentTerm::query();
        MasterListing::applySearch($query, $request, ['payment_mode', 'term_name', 'code', 'description']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function paymentTermRows(Request $request)
    {
        return $this->filteredPaymentTerms($request)->orderByDesc('id')->get()->values()->map(
            fn (PaymentTerm $term, int $index) => [
                $index + 1,
                $term->payment_mode,
                $term->term_name,
                $term->code,
                ucfirst((string) $term->status),
            ]
        );
    }

    public function create()
    {
        return view('payment-terms.create');
    }

    public function store(PaymentTermRequest $request)
    {
        $data = $request->validated();
        if (empty($data['code'])) {
            $data['code'] = strtoupper(\Illuminate\Support\Str::slug(
                ($data['payment_mode'] ?? '').'_'.($data['term_name'] ?? ''),
                '_'
            )) ?: 'TERM';
            $data['code'] = substr($data['code'], 0, 50);
        }

        PaymentTerm::create($data);

        return redirect()
            ->route('payment-terms.index')
            ->with('success', 'Payment term created successfully.');
    }

    public function edit(PaymentTerm $paymentTerm)
    {
        return view('payment-terms.edit', compact('paymentTerm'));
    }

    public function update(PaymentTermRequest $request, PaymentTerm $paymentTerm)
    {
        $data = $request->validated();
        if (empty($data['code'])) {
            $data['code'] = strtoupper(\Illuminate\Support\Str::slug(
                ($data['payment_mode'] ?? '').'_'.($data['term_name'] ?? ''),
                '_'
            )) ?: 'TERM';
            $data['code'] = substr($data['code'], 0, 50);
        }

        $paymentTerm->update($data);

        return redirect()
            ->route('payment-terms.index')
            ->with('success', 'Payment term updated successfully.');
    }

    public function destroy(PaymentTerm $paymentTerm)
    {
        if ($paymentTerm->msuPurchaseOrders()->exists()) {
            return redirect()
                ->route('payment-terms.index')
                ->with('error', 'Cannot delete payment term linked to purchase orders.');
        }

        $paymentTerm->delete();

        return redirect()
            ->route('payment-terms.index')
            ->with('success', 'Payment term deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $paymentTerm = PaymentTerm::findOrFail($request->id);
        $paymentTerm->status = $request->status;
        $paymentTerm->save();

        return response()->json(['success' => true]);
    }

    public function listForManage(): JsonResponse
    {
        $items = PaymentTerm::query()
            ->orderBy('term_name')
            ->get()
            ->map(fn (PaymentTerm $term) => $term->toOptionArray())
            ->values();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function bulkSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:payment_terms,id',
            'items.*.payment_mode' => 'required|string|max:500',
            'items.*.description' => 'required|string|max:255',
            'items.*.code' => 'nullable|string|max:50',
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

        foreach ($validated['items'] as $index => $item) {
            $paymentMode = trim($item['payment_mode']);
            $description = trim($item['description']);
            $code = strtoupper(trim((string) ($item['code'] ?? '')));
            if ($code === '') {
                $code = strtoupper(Str::slug($paymentMode.'_'.$description, '_')) ?: 'TERM';
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
                    'message' => 'Duplicate payment description "'.$description.'" in the list.',
                ], 422);
            }

            $usedCodes[$code] = true;
            $usedNames[$nameKey] = true;

            $rows[] = [
                'id' => ! empty($item['id']) ? (int) $item['id'] : null,
                'payment_mode' => $paymentMode,
                'description' => $description,
                'code' => $code,
                'active' => (bool) $item['active'],
            ];
        }

        foreach ($rows as $row) {
            $codeExists = PaymentTerm::query()
                ->whereRaw('UPPER(code) = ?', [$row['code']])
                ->when($row['id'], fn ($q) => $q->where('id', '!=', $row['id']))
                ->when(! empty($deletedIds), fn ($q) => $q->whereNotIn('id', $deletedIds))
                ->exists();

            if ($codeExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Short name "'.$row['code'].'" already exists.',
                ], 422);
            }

            $nameExists = PaymentTerm::query()
                ->whereRaw('LOWER(TRIM(term_name)) = ?', [mb_strtolower($row['description'])])
                ->when($row['id'], fn ($q) => $q->where('id', '!=', $row['id']))
                ->when(! empty($deletedIds), fn ($q) => $q->whereNotIn('id', $deletedIds))
                ->exists();

            if ($nameExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment description "'.$row['description'].'" already exists.',
                ], 422);
            }
        }

        DB::transaction(function () use ($rows, $deletedIds) {
            foreach ($deletedIds as $id) {
                $term = PaymentTerm::find($id);
                if (! $term) {
                    continue;
                }
                if ($term->msuPurchaseOrders()->exists()) {
                    throw new HttpResponseException(response()->json([
                        'success' => false,
                        'message' => 'Cannot delete "'.$term->term_name.'" because it is linked to purchase orders. Mark it inactive instead.',
                    ], 422));
                }
                $term->delete();
            }

            foreach ($rows as $row) {
                $payload = [
                    'payment_mode' => $row['payment_mode'],
                    'term_name' => $row['description'],
                    'code' => $row['code'],
                    'status' => $row['active'] ? 'active' : 'inactive',
                ];

                if ($row['id']) {
                    PaymentTerm::whereKey($row['id'])->update($payload);
                } else {
                    PaymentTerm::create($payload);
                }
            }
        });
        $active = PaymentTerm::active()
            ->orderBy('term_name')
            ->get()
            ->map(fn (PaymentTerm $term) => $term->toOptionArray())
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Payment terms saved successfully.',
            'data' => $active,
        ]);
    }
}
