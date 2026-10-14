<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Services\Procurement\QualityInspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QualityInspectionController extends Controller
{
    public function __construct(private readonly QualityInspectionService $service)
    {
    }

    public function index(Request $request)
    {
        $this->assertCanView();

        $query = Grn::query()
            ->with(['vendor', 'receivedBy', 'inspectedBy', 'approvedBy', 'items.poItem', 'items.inspectedBy', 'items.assets', 'qualityInspections.inspector'])
            ->where('status', '!=', 'Draft');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('grn_no', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $grns = $query->latest('id')->paginate(15)->withQueryString();

        $canPerform = $this->userCanPerform(Auth::user());

        return view('quality-inspections.index', compact('grns', 'canPerform'));
    }

    public function show(Grn $grn)
    {
        $this->assertCanView();

        $grn->load([
            'vendor',
            'receivedBy',
            'inspectedBy',
            'approvedBy',
            'purchaseOrder.items',
            'items.poItem.indentItem.category',
            'items.inspectedBy',
            'items.assets',
            'qualityInspections.grnItem.poItem',
            'qualityInspections.inspector',
        ]);

        $summary = $grn->inspectionSummary();
        $canPerform = $this->userCanPerform(Auth::user());

        return view('quality-inspections.show', compact('grn', 'summary', 'canPerform'));
    }

    public function inspectItem(Request $request, Grn $grn, GrnItem $item)
    {
        $this->assertCanPerform();
        abort_unless((int) $item->grn_id === (int) $grn->id, 404, 'Item does not belong to the specified GRN.');

        if (! empty($item->inspection_status) && ! in_array($item->inspection_status, ['Pending', 'Draft', ''], true)) {
            return back()->with('error', "This item has already been inspected ({$item->inspection_status}) and cannot be re-inspected.");
        }

        $validated = $request->validate([
            'inspection_status' => 'required|string|in:Approved,Rejected,Damaged,Hold',
            'remarks' => 'nullable|string|max:1000',
            'inspected_at' => 'nullable|date',
            'accepted_quantity' => 'nullable|integer|min:0',
            'rejected_quantity' => 'nullable|integer|min:0',
        ]);

        try {
            $this->service->inspectItem(
                $item,
                Auth::user(),
                $validated['inspection_status'],
                $validated['remarks'] ?? null,
                $validated['inspected_at'] ?? null,
                $validated['accepted_quantity'] ?? null,
                $validated['rejected_quantity'] ?? null
            );

            return back()->with('success', "Item inspection status successfully updated to [{$validated['inspection_status']}].");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function inspectBatch(Request $request, Grn $grn)
    {
        $this->assertCanPerform();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.grn_item_id' => 'required|integer',
            'items.*.inspection_status' => 'required|string|in:Approved,Rejected,Damaged,Hold,Pending',
            'items.*.remarks' => 'nullable|string|max:1000',
            'items.*.inspected_at' => 'nullable|date',
        ]);

        try {
            $this->service->inspectBatch($grn, Auth::user(), $validated['items']);
            return back()->with('success', 'Quality inspection performed successfully across items.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function createAssets(Request $request, Grn $grn, \App\Services\Procurement\GrnAssetConversionService $conversionService, \App\Services\Procurement\GrnService $grnService)
    {
        $this->assertCanPerform();

        $grn->load('items.assets');
        $approvedItems = $grn->items->filter(fn($i) => $i->isApproved());
        if ($approvedItems->isNotEmpty() && $approvedItems->every(fn($i) => $i->assets->isNotEmpty())) {
            return back();
        }

        try {
            $result = $conversionService->generateFromGrn($grn, Auth::user(), [], true);
            $assets = $result['assets'];
            $assetCount = count($assets);

            if ($assetCount === 0) {
                if ($grn->items->some(fn($i) => $i->assets->isNotEmpty())) {
                    return back();
                }
                return back()->with('error', 'No approved items found to convert into assets. Ensure items have Inspection Status = Approved.');
            }

            // Sync PO and PR completion status after successful asset creation
            $grnService->syncPoAndPrAfterGrnUpdate($grn, $assetCount, 0, Auth::user());

            return back()->with('success', "Successfully transferred to Assets! Created {$assetCount} asset record(s), now available in Assets inventory.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Asset Creation Failed: '.$e->getMessage());
        }
    }

    public function createItemAssets(Request $request, Grn $grn, \App\Models\GrnItem $item, \App\Services\Procurement\GrnAssetConversionService $conversionService, \App\Services\Procurement\GrnService $grnService)
    {
        $this->assertCanPerform();

        $item->load('assets', 'poItem');
        if ($item->assets->isNotEmpty()) {
            return back();
        }

        if (!$item->isApproved()) {
            return back()->with('error', 'Item must be approved during inspection before transferring to assets.');
        }

        try {
            $created = $conversionService->generateFromGrnItem($item, $grn, Auth::user(), [], true);
            $assetCount = $created->count();

            if ($assetCount === 0) {
                if ($item->assets()->exists()) {
                    return back();
                }
                return back()->with('error', 'Could not create asset record for this item.');
            }

            $grn->load('items.assets');
            $totalAssets = (int) $grn->items->flatMap->assets->unique('id')->count();
            $grnService->syncPoAndPrAfterGrnUpdate($grn, $totalAssets, 0, Auth::user());

            $itemName = $item->poItem?->item_name ?? 'Item';
            return back()->with('success', "Successfully transferred [{$itemName}] to Assets! Created {$assetCount} asset record(s), now available in Assets inventory.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Transfer to Asset Failed: '.$e->getMessage());
        }
    }

    private function assertCanView(): void
    {
        $user = Auth::user();
        $allowed = $user && (
            $user->can('quality-inspection.view') ||
            $user->can('quality-inspections:view') ||
            $user->can('quality-inspection.perform') ||
            $user->can('quality-inspections:perform') ||
            $user->hasRole(['Quality Inspector', 'Quality Control', 'Admin', 'superadmin', 'Super Admin'])
        );
        abort_unless($allowed, 403, 'Unauthorized to access the Quality Inspections module.');
    }

    private function assertCanPerform(): void
    {
        abort_unless($this->userCanPerform(Auth::user()), 403, 'Unauthorized to perform quality inspections or create assets.');
    }

    private function userCanPerform(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->can('quality-inspection.perform') ||
            $user->can('quality-inspections:perform')) {
            return true;
        }

        if ($user->hasRole(['Quality Inspector', 'Quality Control', 'Admin', 'superadmin', 'Super Admin'])) {
            return true;
        }

        return false;
    }
}
