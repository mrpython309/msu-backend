<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\IndentStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetName;
use App\Models\AssetType;
use App\Models\Brand;
use App\Models\Department;
use App\Models\Indent;
use App\Models\IndentItem;
use App\Models\ItemMaster;
use App\Services\Procurement\ApprovalWorkflowService;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Services\Procurement\Exceptions\ProcurementException;
use App\Services\ReportPdfExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class IndentController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflow,
        private readonly ReportPdfExportService $pdf,
        private readonly DocumentNumberGenerator $documentNumbers,
    ) {
    }

    public function dashboard()
    {
        $indents = Indent::visibleToUser(Auth::user());

        $totalIndents = (clone $indents)->count();
        $pendingIndents = (clone $indents)->pendingApproval()->count();
        $completedIndents = (clone $indents)->whereIn('status', [
            IndentStatus::Completed->value,
            IndentStatus::ConvertedToPurchaseRequisition->value,
            IndentStatus::Closed->value,
        ])->count();
        $rejectedIndents = (clone $indents)->where('status', IndentStatus::Rejected->value)->count();
        $recentIndents = (clone $indents)
            ->with(['department', 'requestedBy'])
            ->latest()
            ->take(5)
            ->get();

        return view('indents.dashboard', compact(
            'totalIndents',
            'pendingIndents',
            'completedIndents',
            'rejectedIndents',
            'recentIndents'
        ));
    }

    public function index(Request $request)
    {
        $indents = Indent::visibleToUser(Auth::user())
            ->with(['department', 'fromStore', 'itemCategory', 'requestedBy', 'assignedApprover', 'approvalSteps.approver', 'approvalSteps.actedBy'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('indent_no'), fn ($q) => $q->where('indent_no', 'like', '%'.$request->indent_no.'%'))
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        $indents->getCollection()->transform(function (Indent $indent) {
            if ($indent->statusEnum()?->isPendingApproval()) {
                $indent = $this->workflow->syncPendingStepsFromTemplate($indent);
                $indent->setAttribute(
                    'index_approval_level',
                    $indent->currentApprovalLevelLabel() ?? ($indent->current_level ? 'Level '.$indent->current_level : null)
                );
                $indent->setAttribute('index_assigned_approver', $this->workflow->currentAssigneeDisplay($indent));
            } else {
                $level = $indent->currentApprovalLevelLabel()
                    ?? ($indent->current_level ? 'Level '.$indent->current_level : null);
                $assigned = $indent->assignedApprover?->name;

                if (! $assigned && $indent->relationLoaded('approvalSteps')) {
                    $lastActedStep = $indent->approvalSteps
                        ->whereNotNull('acted_at')
                        ->sortByDesc('sequence')
                        ->first();

                    if ($lastActedStep) {
                        $level = $level ?? 'Level ' . $lastActedStep->sequence;
                        $assigned = $lastActedStep->actedBy?->name ?? $lastActedStep->displayLabel();
                    } elseif ($indent->current_sequence) {
                        $step = $indent->approvalSteps
                            ->sortBy('sequence')
                            ->first(fn ($s) => (int) $s->sequence === (int) $indent->current_sequence);
                        $assigned = $step?->displayLabel();
                    }
                }

                $indent->setAttribute('index_approval_level', $level);
                $indent->setAttribute('index_assigned_approver', $assigned);
            }

            return $indent;
        });

        return view('indents.index', compact('indents'));
    }

    public function create()
    {
        $departments = Department::visibleToUser(Auth::user())
            ->orderBy('department_name')
            ->get();
        $categories = AssetCategory::query()
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        return view('indents.create', compact('departments', 'categories'));
    }

    public function suggestItems(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 500), 1), 1000);
        $results = collect();

        $itemMasters = ItemMaster::query()
            ->active()
            ->with(['category:id,category_name', 'assetType:id,asset_type_name'])
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('item_name', 'like', "%{$q}%")
                      ->orWhere('item_code', 'like', "%{$q}%");
            }))
            ->orderBy('item_name')
            ->limit($limit)
            ->get();

        foreach ($itemMasters as $item) {
            $results->push([
                'source' => 'item_master',
                'item_name' => $item->item_name,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->category_name,
                'asset_type_id' => $item->asset_type_id,
                'asset_type_name' => $item->assetType?->asset_type_name,
                'item_type' => 'asset',
                'unit' => $item->uom ?? 'Nos',
                'description' => $item->description,
                'meta' => 'Item Master' . ($item->assetType ? ' | ' . $item->assetType->asset_type_name : '') . ($item->category ? ' / ' . $item->category->category_name : ''),
            ]);
        }

        return response()->json([
            'results' => $results
                ->unique(fn ($row) => strtolower((string) $row['item_name']))
                ->take($limit)
                ->values(),
        ]);
    }

    public function searchMyAssets(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 20), 1), 50);

        $assets = Asset::query()
            ->visibleToUser(Auth::user())
            ->with([
                'category:id,category_name',
                'brand:id,brand_name',
                'assetType:id,asset_type_name',
            ])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('asset_name', 'like', "%{$q}%")
                        ->orWhere('asset_code', 'like', "%{$q}%")
                        ->orWhere('serial_no', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->limit($limit)
            ->get([
                'id',
                'asset_name',
                'asset_code',
                'serial_no',
                'category_id',
                'asset_type_id',
                'description',
                'purchase_cost',
                'brand_id',
            ]);

        $results = $assets->map(function (Asset $asset) {
            return [
                'id' => $asset->id,
                'item_name' => $asset->asset_name,
                'category_id' => $asset->category_id,
                'category_name' => $asset->category?->category_name,
                'item_type' => 'asset',
                'asset_type_id' => $asset->asset_type_id,
                'asset_type_name' => $asset->assetType?->asset_type_name,
                'unit' => 'Nos',
                'quantity' => 1,
                'estimated_price' => $asset->purchase_cost,
                'description' => $asset->description,
                'manufacturer' => $asset->brand?->brand_name,
                'asset_code' => $asset->asset_code,
                'serial_no' => $asset->serial_no,
                'composition' => $asset->assetType?->asset_type_name,
                'meta' => trim(collect([
                    $asset->asset_code,
                    $asset->serial_no ? 'S/N '.$asset->serial_no : null,
                    $asset->assetType?->asset_type_name,
                    $asset->category?->category_name,
                ])->filter()->implode(' · ')),
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    public function suggestFields(Request $request): JsonResponse
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 15), 1), 40);

        $results = match ($field) {
            'manufacturer' => $this->suggestManufacturerValues($q, $limit),
            'unit' => $this->suggestUnitValues($q, $limit),
            'description' => $this->suggestDescriptionValues($q, $limit),
            'category' => $this->suggestCategoryValues($q, $limit),
            'item_type' => $this->suggestItemTypeValues($q, $limit),
            'asset_type' => $this->suggestAssetTypeValues($q, $limit),
            'quantity' => $this->suggestQuantityValues($q, $limit),
            'store' => $this->suggestStoreValues($q, $limit),
            'all_store' => $this->suggestAllStoreValues($q, $limit),
            'priority' => $this->suggestPriorityValues($q, $limit),
            'tag' => $this->suggestTagValues($q, $limit),
            'purpose' => $this->suggestPurposeValues($q, $limit),
            'remarks' => $this->suggestRemarksValues($q, $limit),
            default => collect(),
        };

        return response()->json(['results' => $results->values()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateIndent($request);

        $indent = $this->documentNumbers->allocate('indent', Indent::class, 'indent_no', function (string $indentNo) use ($validated, $request) {
            $now = now();
            $isUrgent = $request->boolean('is_urgent') || ($validated['priority'] ?? '') === 'Urgent';
            $categoryId = $validated['item_category_id']
                ?? collect($validated['items'])->pluck('category_id')->filter()->first();

            $indent = Indent::create([
                'indent_no' => $indentNo,
                'request_date' => $now->toDateString(),
                'requested_at' => $now,
                'department_id' => $validated['from_store_id'] ?? $validated['department_id'],
                'from_store_id' => $validated['from_store_id'] ?? $validated['department_id'],
                'to_store_id' => $validated['to_store_id'] ?? null,
                'item_category_id' => $categoryId,
                'requested_by' => Auth::id(),
                'priority' => $validated['priority'],
                'is_urgent' => $isUrgent,
                'tag' => $validated['tag'] ?? null,
                'required_date' => $validated['required_date'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => IndentStatus::Draft,
                'current_level' => null,
            ]);

            foreach ($validated['items'] as $item) {
                $price = (float) ($item['estimated_price'] ?? 0);
                $qty = (int) $item['quantity'];

                $indent->items()->create([
                    'item_name' => $item['item_name'],
                    'category_id' => $item['category_id'] ?? $categoryId,
                    'item_type' => $item['item_type'] ?? null,
                    'description' => $item['description'] ?? null,
                    'manufacturer' => $item['manufacturer'] ?? null,
                    'composition' => $item['composition'] ?? null,
                    'item_remarks' => $item['item_remarks'] ?? null,
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'Nos',
                    'estimated_price' => $price,
                    'total_amount' => $qty * $price,
                ]);
            }

            return $indent;
        });

        if ($request->input('action') === 'submit') {
            try {
                $indent = $this->workflow->submit($indent, Auth::user(), $request->input('comments'));
            } catch (ProcurementException $e) {
                return $this->handleWorkflowException($e, 'indents.edit', ['indent' => $indent]);
            }

            return redirect()
                ->route('indents.show', $indent)
                ->with('success', 'Indent submitted for approval.');
        }

        return redirect()
            ->route('indents.index')
            ->with('success', 'Indent saved as draft.');
    }

    public function show(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $indent->load([
            'items.category',
            'approvalHistories.approver',
            'approvalSteps.approver',
            'approvalSteps.actedBy',
            'department',
            'fromStore',
            'toStore',
            'itemCategory',
            'requestedBy',
            'assignedApprover',
            'approvalTemplate',
        ]);

        if ($indent->statusEnum()?->isPendingApproval()) {
            $indent = $this->workflow->syncPendingStepsFromTemplate($indent);
            $indent->loadMissing([
                'items.category',
                'approvalHistories.approver',
                'approvalSteps.approver',
                'approvalSteps.actedBy',
                'department',
                'fromStore',
                'toStore',
                'itemCategory',
                'requestedBy',
                'assignedApprover',
                'approvalTemplate',
            ]);
        }

        $timeline = $this->workflow->timeline($indent);
        $user = Auth::user();
        $isRequester = (int) $indent->requested_by === (int) $user->id;
        $canActOnCurrentStep = $this->workflow->actorCanActOnIndent($indent, $user);
        $awaitingAssigneeLabel = $indent->statusEnum()?->isPendingApproval()
            ? $this->workflow->currentAssigneeDisplay($indent)
            : null;

        return view('indents.show', compact('indent', 'timeline', 'canActOnCurrentStep', 'isRequester', 'awaitingAssigneeLabel'));
    }

    public function approvalsDashboard(Request $request)
    {
        $user = Auth::user();
        $scope = $request->input('scope', 'all'); // all | mine

        // Waiting for Indent Approval: show all pending indents visible to this user
        // (including ones where they are a later step). Optionally filter to "assigned to me".
        $indents = Indent::query()
            ->visibleForApproval($user)
            ->pendingApproval()
            ->when($scope === 'mine', function ($q) use ($user) {
                $this->applyAssignedToUserScope($q, $user);
            })
            ->with(['department', 'fromStore', 'requestedBy', 'items.category', 'assignedApprover', 'itemCategory', 'approvalSteps'])
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->orderByRaw('CASE WHEN assigned_approver_id = ? THEN 0 ELSE 1 END', [$user->id])
            ->orderBy('submitted_at')
            ->paginate(15)
            ->appends($request->query());

        $indents->getCollection()->transform(function (Indent $indent) use ($user) {
            if ($indent->statusEnum()?->isPendingApproval()) {
                $indent = $this->workflow->syncPendingStepsFromTemplate($indent);
            }

            $indent->setAttribute('is_waiting_for_me', $this->workflow->userIsCurrentAssignee($indent, $user));
            $indent->setAttribute('awaiting_label', $this->workflow->currentAssigneeDisplay($indent));

            return $indent;
        });

        $waitingForMeQuery = Indent::query()
            ->visibleForApproval($user)
            ->pendingApproval();
        $this->applyAssignedToUserScope($waitingForMeQuery, $user);
        $waitingForMeCount = $waitingForMeQuery->count();

        $allWaitingCount = Indent::query()
            ->visibleForApproval($user)
            ->pendingApproval()
            ->count();

        return view('indents.approvals', compact('indents', 'scope', 'waitingForMeCount', 'allWaitingCount'));
    }

    public function edit(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent);

        if (! $indent->canBeEdited()) {
            return redirect()
                ->route('indents.show', $indent)
            ->with('error', 'This indent cannot be edited.');
        }

        $departments = Department::visibleToUser(Auth::user())
            ->orderBy('department_name')
            ->get();
        $categories = AssetCategory::query()
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
        $indent->load('items.category');

        return view('indents.create', compact('indent', 'departments', 'categories'));
    }

    public function update(Request $request, Indent $indent)
    {
        $this->ensureIndentIsVisible($indent);

        if (! $indent->canBeEdited()) {
            return redirect()
                ->route('indents.show', $indent)
            ->with('error', 'This indent cannot be updated.');
        }

        $validated = $this->validateIndent($request);

        $updated = DB::transaction(function () use ($indent, $validated, $request) {
            $isUrgent = $request->boolean('is_urgent') || ($validated['priority'] ?? '') === 'Urgent';
            $categoryId = $validated['item_category_id']
                ?? collect($validated['items'])->pluck('category_id')->filter()->first();

            $indent->update([
                'department_id' => $validated['from_store_id'] ?? $validated['department_id'],
                'from_store_id' => $validated['from_store_id'] ?? $validated['department_id'],
                'to_store_id' => $validated['to_store_id'] ?? null,
                'item_category_id' => $categoryId,
                'priority' => $validated['priority'],
                'is_urgent' => $isUrgent,
                'tag' => $validated['tag'] ?? null,
                'required_date' => $validated['required_date'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => IndentStatus::Draft,
                'current_level' => null,
                'current_sequence' => null,
                'approval_status' => null,
                'assigned_permission' => null,
                'assigned_approver_id' => null,
                'approval_template_id' => null,
                'approved_at' => null,
            ]);

            $indent->items()->delete();

            foreach ($validated['items'] as $item) {
                $price = (float) ($item['estimated_price'] ?? 0);
                $qty = (int) $item['quantity'];

                $indent->items()->create([
                    'item_name' => $item['item_name'],
                    'category_id' => $item['category_id'] ?? $categoryId,
                    'item_type' => $item['item_type'] ?? null,
                    'description' => $item['description'] ?? null,
                    'manufacturer' => $item['manufacturer'] ?? null,
                    'composition' => $item['composition'] ?? null,
                    'item_remarks' => $item['item_remarks'] ?? null,
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'Nos',
                    'estimated_price' => $price,
                    'total_amount' => $qty * $price,
                ]);
            }

            return $indent;
        });

        if ($request->input('action') === 'submit') {
            try {
                $updated = $this->workflow->submit($updated, Auth::user(), $request->input('comments'));
            } catch (ProcurementException $e) {
                return $this->handleWorkflowException($e, 'indents.edit', ['indent' => $indent]);
            }
        }

        return redirect()
            ->route('indents.show', $indent)
            ->with('success', $updated->status === IndentStatus::Draft ? 'Indent draft updated.' : 'Indent submitted for approval.');
    }

    public function destroy(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent);

        if (! $indent->canBeDeleted()) {
            return redirect()
                ->route('indents.index')
                ->with('error', 'Completed indents cannot be deleted.');
        }

        $indent->delete();

        return redirect()
            ->route('indents.index')
            ->with('success', 'Indent deleted successfully.');
    }

    public function approve(Request $request, Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $request->validate(['comments' => 'nullable|string|max:1000']);

        try {
            $updated = $this->workflow->approve($indent, Auth::user(), $request->comments);
        } catch (ProcurementException $e) {
            return $this->handleWorkflowException($e);
        }

        $message = $updated->statusEnum()?->isPendingApproval()
            ? "Approved. Indent moved to sequence {$updated->current_sequence}."
            : 'Indent fully approved. Purchase requisition created.';

        return redirect()
            ->route('indents.show', $updated)
            ->with('success', $message);
    }

    public function reject(Request $request, Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $request->validate(['comments' => 'required|string|max:1000']);

        try {
            $updated = $this->workflow->reject($indent, Auth::user(), $request->comments);
        } catch (ProcurementException $e) {
            return $this->handleWorkflowException($e);
        }

        return redirect()->route('indents.show', $updated)->with('success', 'Indent rejected.');
    }

    public function sendBack(Request $request, Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $request->validate(['comments' => 'required|string|max:1000']);

        try {
            $this->workflow->sendBack($indent, Auth::user(), $request->comments);
        } catch (ProcurementException $e) {
            return $this->handleWorkflowException($e);
        }

        return back()->with('success', 'Indent sent back for revision.');
    }

    public function submit(Request $request, Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);

        try {
            $updated = $this->workflow->submit($indent, Auth::user(), $request->input('comments'));
        } catch (ProcurementException $e) {
            return $this->handleWorkflowException($e, 'indents.show', ['indent' => $indent]);
        }

        return redirect()->route('indents.show', $updated)->with('success', 'Indent submitted for approval.');
    }

    public function cancel(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent);
        if (in_array($indent->status, [IndentStatus::ConvertedToPurchaseRequisition, IndentStatus::Closed], true)) {
            return back()->with('error', 'This indent can no longer be cancelled.');
        }
        $indent->update(['status' => IndentStatus::Closed, 'finalized_at' => now()]);
        return redirect()->route('indents.index')->with('success', 'Indent cancelled.');
    }

    public function print(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $indent->load(['items.category', 'department', 'requestedBy']);
        return view('indents.print', compact('indent'));
    }

    public function pdf(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $indent->load(['items.category', 'department', 'requestedBy']);
        return $this->pdf->download('indents.print', compact('indent'), "{$indent->indent_no}.pdf", 'portrait');
    }

    public function excel(Indent $indent)
    {
        $this->ensureIndentIsVisible($indent, true);
        $indent->load('items');
        return Excel::download(new class($indent) implements FromCollection, WithHeadings {
            public function __construct(private readonly Indent $indent) {}
            public function collection() { return $this->indent->items->map(fn ($item) => [$this->indent->indent_no, $item->item_name, $item->quantity, $item->unit, $item->estimated_price, $item->total_amount]); }
            public function headings(): array { return ['Indent No', 'Item', 'Quantity', 'Unit', 'Estimated Price', 'Total']; }
        }, "{$indent->indent_no}.xlsx");
    }

    private function validateIndent(Request $request): array
    {
        $departmentRule = Rule::exists('departments', 'id');

        if (! Auth::user()->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            $departmentRule = $departmentRule->whereIn(
                'id',
                Auth::user()->departments()->pluck('departments.id')->all()
            );
        }

        $data = $request->validate([
            'department_id' => ['nullable', 'integer', $departmentRule],
            'from_store_id' => ['required', 'integer', $departmentRule],
            'to_store_id' => ['nullable', 'integer', 'exists:departments,id'],
            'item_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'is_urgent' => 'nullable|boolean',
            'tag' => 'nullable|string|max:100',
            'required_date' => 'nullable|date',
            'purpose' => 'required|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.category_id' => 'nullable|integer|exists:asset_categories,id',
            'items.*.item_type' => 'nullable|in:asset,consumable',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.manufacturer' => 'nullable|string|max:255',
            'items.*.composition' => 'nullable|string|max:255',
            'items.*.item_remarks' => 'nullable|string|max:2000',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        return $data;
    }

    private function generateIndentNo(): string
    {
        return $this->documentNumbers->next('indent', Indent::class, 'indent_no');
    }

    private function ensureIndentIsVisible(Indent $indent, bool $includeApprovalAccess = false): void
    {
        $query = Indent::query();

        if ($includeApprovalAccess) {
            $query->visibleForApproval(Auth::user());
        } else {
            $query->visibleToUser(Auth::user());
        }

        abort_unless($query->whereKey($indent->getKey())->exists(), 404);
    }

    private function applyAssignedToUserScope(\Illuminate\Database\Eloquent\Builder $query, \App\Models\User $user): void
    {
        $roleNames = $user->getRoleNames()->all();
        $permissions = $user->getAllPermissions()->pluck('name')->all();

        $query->where(function ($inner) use ($user, $roleNames, $permissions) {
            $inner->where('assigned_approver_id', $user->id)
                ->orWhereHas('approvalSteps', function ($stepQ) use ($user, $roleNames, $permissions) {
                    $stepQ->whereColumn('indent_approval_steps.sequence', 'indents.current_sequence')
                        ->where('status', \App\Models\IndentApprovalStep::STATUS_PENDING)
                        ->where(function ($s) use ($user, $roleNames, $permissions) {
                            if ($roleNames !== []) {
                                $s->where(function ($roleQ) use ($roleNames) {
                                    $roleQ->where('assignee_type', 'role')
                                        ->where(function ($rn) use ($roleNames) {
                                            foreach ($roleNames as $roleName) {
                                                $rn->orWhereRaw('LOWER(assignee_role) = ?', [mb_strtolower((string) $roleName)]);
                                            }
                                        });
                                });
                            }
                            if ($permissions !== []) {
                                $s->orWhere(function ($permQ) use ($permissions) {
                                    $permQ->where('assignee_type', 'permission')
                                        ->whereIn('assignee_permission', $permissions);
                                });
                            }
                            $s->orWhere(function ($userQ) use ($user) {
                                $userQ->where(function ($t) {
                                    $t->whereNull('assignee_type')
                                        ->orWhere('assignee_type', 'user');
                                })->where('approver_user_id', $user->id);
                            });
                        });
                });
        });
    }

    private function handleWorkflowException(
        ProcurementException $e,
        ?string $route = null,
        array $routeParams = []
    ) {
        $redirect = $route
            ? redirect()->route($route, $routeParams)
            : back();

        return $redirect
            ->withInput()
            ->with('workflow_error', [
                'title' => 'Configure approval workflow first',
                'message' => $this->workflowErrorMessage($e),
            ]);
    }

    private function workflowErrorMessage(ProcurementException $e): string
    {
        $message = $e->getMessage();

        if (preg_match('/Level (\d+): no approver user configured\./', $message, $matches)) {
            return 'Level '.$matches[1].' has no approver user assigned. '
                .'Ask your Super Admin to open <strong>Settings → Approval Templates</strong> '
                .'and assign an approver for this item category before submitting.';
        }

        if (preg_match('/Level (\d+): no (role|permission) configured\./', $message, $matches)) {
            return 'Level '.$matches[1].' is missing an approver '.$matches[2].'. '
                .'Ask your Super Admin to complete the Approval Template for this item category.';
        }

        if (preg_match('/Level (\d+): configured approver user no longer exists\./', $message, $matches)) {
            return 'Level '.$matches[1].' points to a user that no longer exists. '
                .'Ask your Super Admin to update the Approval Template.';
        }

        if (preg_match('/Level (\d+): no active user found with (role|permission) \[(.+?)\]/', $message, $matches)) {
            return 'Level '.$matches[1].' could not find any active user with '.$matches[2].' ['.$matches[3].']. '
                .'Ask your Super Admin to assign that '.$matches[2].' or update the Approval Template.';
        }

        if (str_contains($message, 'No active Approval Template is configured')) {
            return $message.' Ask your Super Admin to create one under <strong>Settings → Approval Templates</strong>.';
        }

        if (str_contains($message, 'is incomplete for')) {
            return $message.' Or complete the <strong>Default</strong> template so it can be used as fallback.';
        }

        if (str_contains($message, 'could not resolve the first approver')) {
            return 'The approval workflow could not find a valid first approver. '
                .'Ask your Super Admin to configure Approval Templates under <strong>Settings → Approval Templates</strong>.';
        }

        return $message;
    }

    private function suggestStoreValues(string $q, int $limit)
    {
        return Department::visibleToUser(Auth::user())
            ->when($q !== '', fn ($query) => $query->where('department_name', 'like', "%{$q}%"))
            ->orderBy('department_name')
            ->limit($limit)
            ->get(['id', 'department_name'])
            ->map(fn (Department $department) => [
                'value' => (string) $department->id,
                'label' => $department->department_name,
                'meta' => 'Store / Department',
            ]);
    }

    private function suggestAllStoreValues(string $q, int $limit)
    {
        return Department::query()
            ->active()
            ->when($q !== '', fn ($query) => $query->where('department_name', 'like', "%{$q}%"))
            ->orderBy('department_name')
            ->limit($limit)
            ->get(['id', 'department_name'])
            ->map(fn (Department $department) => [
                'value' => (string) $department->id,
                'label' => $department->department_name,
                'meta' => 'Store / Department',
            ]);
    }

    private function suggestPriorityValues(string $q, int $limit)
    {
        return collect(['Low', 'Medium', 'High', 'Urgent'])
            ->when($q !== '', fn ($collection) => $collection->filter(
                fn ($value) => str_contains(strtolower($value), strtolower($q))
            ))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value, 'meta' => 'Priority']);
    }

    private function suggestTagValues(string $q, int $limit)
    {
        return Indent::query()
            ->whereNotNull('tag')
            ->where('tag', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('tag', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('tag')
            ->limit($limit)
            ->pluck('tag')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value, 'meta' => 'Tag']);
    }

    private function suggestPurposeValues(string $q, int $limit)
    {
        return Indent::query()
            ->whereNotNull('purpose')
            ->where('purpose', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('purpose', 'like', "%{$q}%"))
            ->latest('id')
            ->limit($limit * 3)
            ->pluck('purpose')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }

    private function suggestRemarksValues(string $q, int $limit)
    {
        return Indent::query()
            ->whereNotNull('remarks')
            ->where('remarks', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('remarks', 'like', "%{$q}%"))
            ->latest('id')
            ->limit($limit * 3)
            ->pluck('remarks')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }

    private function suggestManufacturerValues(string $q, int $limit)
    {
        $fromIndent = IndentItem::query()
            ->whereNotNull('manufacturer')
            ->where('manufacturer', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('manufacturer', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('manufacturer')
            ->limit($limit)
            ->pluck('manufacturer');

        $fromBrands = Brand::query()
            ->active()
            ->when($q !== '', fn ($query) => $query->where('brand_name', 'like', "%{$q}%"))
            ->orderBy('brand_name')
            ->limit($limit)
            ->pluck('brand_name');

        return $fromIndent
            ->merge($fromBrands)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }

    private function suggestCategoryValues(string $q, int $limit)
    {
        return AssetCategory::query()
            ->when($q !== '', fn ($query) => $query->where('category_name', 'like', "%{$q}%"))
            ->orderBy('category_name')
            ->limit($limit)
            ->get(['id', 'category_name'])
            ->map(fn (AssetCategory $category) => [
                'value' => (string) $category->id,
                'label' => $category->category_name,
                'category_id' => $category->id,
                'meta' => 'Category',
            ]);
    }

    private function suggestItemTypeValues(string $q, int $limit)
    {
        $types = collect([
            ['value' => 'asset', 'label' => 'Asset', 'meta' => 'Item type'],
            ['value' => 'consumable', 'label' => 'Consumable', 'meta' => 'Item type'],
        ]);

        return $types
            ->when($q !== '', fn ($collection) => $collection->filter(
                fn ($row) => str_contains(strtolower($row['label']), strtolower($q))
                    || str_contains(strtolower($row['value']), strtolower($q))
            ))
            ->take($limit)
            ->values();
    }

    private function suggestAssetTypeValues(string $q, int $limit)
    {
        return AssetType::query()
            ->active()
            ->when($q !== '', fn ($query) => $query->where('asset_type_name', 'like', "%{$q}%"))
            ->orderBy('asset_type_name')
            ->limit($limit)
            ->get(['id', 'asset_type_name'])
            ->map(fn (AssetType $type) => [
                'value' => $type->asset_type_name,
                'label' => $type->asset_type_name,
                'asset_type_id' => $type->id,
                'meta' => 'Asset type',
            ]);
    }

    private function suggestQuantityValues(string $q, int $limit)
    {
        $defaults = collect([1, 2, 3, 4, 5, 10, 12, 20, 25, 50, 100]);

        $fromIndent = IndentItem::query()
            ->whereNotNull('quantity')
            ->when($q !== '', fn ($query) => $query->where('quantity', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('quantity')
            ->limit($limit)
            ->pluck('quantity');

        return $defaults
            ->merge($fromIndent)
            ->map(fn ($value) => (string) (int) $value)
            ->filter(fn ($value) => (int) $value > 0)
            ->when($q !== '', fn ($collection) => $collection->filter(
                fn ($value) => str_starts_with($value, $q)
            ))
            ->unique()
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }

    private function suggestUnitValues(string $q, int $limit)
    {
        $defaults = collect(['Nos', 'Pcs', 'Box', 'Set', 'Kg', 'Litre', 'Meter', 'Pack']);

        $fromIndent = IndentItem::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('unit', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('unit')
            ->limit($limit)
            ->pluck('unit');

        return $defaults
            ->merge($fromIndent)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->when($q !== '', fn ($collection) => $collection->filter(
                fn ($value) => str_contains(strtolower($value), strtolower($q))
            ))
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }

    private function suggestDescriptionValues(string $q, int $limit)
    {
        return IndentItem::query()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('description', 'like', "%{$q}%"))
            ->latest('id')
            ->limit($limit * 3)
            ->pluck('description')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->take($limit)
            ->map(fn ($value) => ['value' => $value, 'label' => $value]);
    }
}
