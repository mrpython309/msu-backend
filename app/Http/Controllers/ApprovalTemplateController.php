<?php

namespace App\Http\Controllers;

use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateStep;
use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApprovalTemplateController extends Controller
{
    public function index()
    {
        $templates = ApprovalTemplate::with(['category', 'steps.approver'])
            ->orderByRaw("CASE WHEN module = 'po' THEN 1 ELSE 0 END")
            ->orderByRaw('category_id IS NULL')
            ->orderBy('name')
            ->get();

        $categories = AssetCategory::query()->orderBy('category_name')->get(['id', 'category_name']);
        // Any active login can be an approver (Admin / Super Admin / employees / officers).
        $users = User::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $roles = Role::query()->orderBy('name')->pluck('name');
        $permissions = Permission::query()->orderBy('name')->pluck('name');

        return view('settings.approval-templates.index', compact(
            'templates',
            'categories',
            'users',
            'roles',
            'permissions'
        ));
    }

    public function store(Request $request)
    {
        $module = $request->input('module') === 'po' ? ApprovalTemplate::MODULE_PO : null;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'nullable',
                'integer',
                'exists:asset_categories,id',
                Rule::unique('approval_templates', 'category_id')->where(function ($q) use ($module) {
                    if ($module === ApprovalTemplate::MODULE_PO) {
                        $q->where('module', ApprovalTemplate::MODULE_PO);
                    } else {
                        $q->whereNull('module');
                    }
                }),
            ],
        ], [
            'category_id.unique' => $module === ApprovalTemplate::MODULE_PO
                ? 'A Purchase Order approval template already exists for this category.'
                : 'An Indent approval template already exists for this category.',
        ]);

        $categoryId = $data['category_id'] ?? null;

        if ($categoryId === null) {
            $defaultExists = ApprovalTemplate::query()
                ->whereNull('category_id')
                ->when(
                    $module === ApprovalTemplate::MODULE_PO,
                    fn ($q) => $q->where('module', ApprovalTemplate::MODULE_PO),
                    fn ($q) => $q->whereNull('module')
                )
                ->exists();

            if ($defaultExists) {
                $label = $module === ApprovalTemplate::MODULE_PO
                    ? 'A Default Purchase Order template already exists. Edit that one instead.'
                    : 'A Default Indent template already exists. Edit that one instead.';

                return back()->withInput()->withErrors(['category_id' => $label]);
            }
        }

        ApprovalTemplate::create([
            'name'        => $data['name'],
            'category_id' => $categoryId,
            'module'      => $module,
            'is_active'   => true,
        ]);

        $msg = $module === ApprovalTemplate::MODULE_PO
            ? 'PO approval template created. Add levels next (separate from Indent approvals).'
            : 'Indent approval template created. Add steps next.';

        return back()->with('success', $msg);
    }

    public function update(Request $request, ApprovalTemplate $approvalTemplate)
    {
        $module = $approvalTemplate->module === ApprovalTemplate::MODULE_PO
            ? ApprovalTemplate::MODULE_PO
            : null;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'nullable',
                'integer',
                'exists:asset_categories,id',
                Rule::unique('approval_templates', 'category_id')
                    ->ignore($approvalTemplate->id)
                    ->where(function ($q) use ($module) {
                        if ($module === ApprovalTemplate::MODULE_PO) {
                            $q->where('module', ApprovalTemplate::MODULE_PO);
                        } else {
                            $q->whereNull('module');
                        }
                    }),
            ],
        ], [
            'category_id.unique' => $module === ApprovalTemplate::MODULE_PO
                ? 'A Purchase Order approval template already exists for this category.'
                : 'An Indent approval template already exists for this category.',
        ]);

        $newCategoryId = $data['category_id'] ?? null;

        if ($newCategoryId === null) {
            $defaultExists = ApprovalTemplate::query()
                ->whereNull('category_id')
                ->where('id', '!=', $approvalTemplate->id)
                ->when(
                    $module === ApprovalTemplate::MODULE_PO,
                    fn ($q) => $q->where('module', ApprovalTemplate::MODULE_PO),
                    fn ($q) => $q->whereNull('module')
                )
                ->exists();

            if ($defaultExists) {
                return back()->withInput()->withErrors([
                    'category_id' => $module === ApprovalTemplate::MODULE_PO
                        ? 'A Default Purchase Order template already exists.'
                        : 'A Default Indent template already exists.',
                ]);
            }
        }

        $approvalTemplate->update([
            'name'        => $data['name'],
            'category_id' => $newCategoryId,
        ]);

        return back()->with('success', 'Template updated.');
    }

    public function toggle(ApprovalTemplate $approvalTemplate)
    {
        $approvalTemplate->update(['is_active' => ! $approvalTemplate->is_active]);

        return back()->with('success', 'Template '.($approvalTemplate->is_active ? 'enabled' : 'disabled').'.');
    }

    public function destroy(ApprovalTemplate $approvalTemplate)
    {
        $approvalTemplate->delete();

        return back()->with('success', 'Template deleted.');
    }

    public function storeStep(Request $request, ApprovalTemplate $approvalTemplate)
    {
        $data = $this->validateStep($request);
        $next = (int) $approvalTemplate->steps()->max('sequence') + 1;

        ApprovalTemplateStep::create([
            'approval_template_id' => $approvalTemplate->id,
            'sequence' => $next,
            ...$this->stepPayload($data, $request),
        ]);

        return back()->with('success', "Level {$next} added.");
    }

    public function updateStep(Request $request, ApprovalTemplateStep $step)
    {
        $data = $this->validateStep($request);
        $step->update($this->stepPayload($data, $request));

        return back()->with('success', 'Level updated.');
    }

    public function moveStep(Request $request, ApprovalTemplateStep $step)
    {
        $direction = $request->validate(['direction' => 'required|in:up,down'])['direction'];

        DB::transaction(function () use ($step, $direction) {
            $query = ApprovalTemplateStep::query()
                ->where('approval_template_id', $step->approval_template_id)
                ->lockForUpdate();

            $neighbour = $direction === 'up'
                ? (clone $query)->where('sequence', '<', $step->sequence)->orderByDesc('sequence')->first()
                : (clone $query)->where('sequence', '>', $step->sequence)->orderBy('sequence')->first();

            if (! $neighbour) {
                return;
            }

            $mine = $step->sequence;
            $step->update(['sequence' => $neighbour->sequence]);
            $neighbour->update(['sequence' => $mine]);
        });

        return back()->with('success', 'Approval order updated.');
    }

    public function destroyStep(ApprovalTemplateStep $step)
    {
        DB::transaction(function () use ($step) {
            $templateId = $step->approval_template_id;
            $step->delete();

            $remaining = ApprovalTemplateStep::query()
                ->where('approval_template_id', $templateId)
                ->orderBy('sequence')
                ->lockForUpdate()
                ->get();

            foreach ($remaining->values() as $i => $row) {
                if ((int) $row->sequence !== $i + 1) {
                    $row->update(['sequence' => $i + 1]);
                }
            }
        });

        return back()->with('success', 'Level removed.');
    }

    private function validateStep(Request $request): array
    {
        $type = $request->input('assignee_type', ApprovalTemplateStep::ASSIGNEE_USER);

        return $request->validate([
            'assignee_type' => 'required|in:user,role,permission',
            'approver_user_id' => [
                Rule::requiredIf($type === ApprovalTemplateStep::ASSIGNEE_USER),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'assignee_role' => [
                Rule::requiredIf($type === ApprovalTemplateStep::ASSIGNEE_ROLE),
                'nullable',
                'string',
                'max:255',
            ],
            'assignee_permission' => [
                Rule::requiredIf($type === ApprovalTemplateStep::ASSIGNEE_PERMISSION),
                'nullable',
                'string',
                'max:255',
            ],
            'is_mandatory' => 'nullable|boolean',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function stepPayload(array $data, Request $request): array
    {
        $type = $data['assignee_type'];

        return [
            'assignee_type' => $type,
            'approver_user_id' => $type === ApprovalTemplateStep::ASSIGNEE_USER ? ($data['approver_user_id'] ?? null) : null,
            'assignee_role' => $type === ApprovalTemplateStep::ASSIGNEE_ROLE ? ($data['assignee_role'] ?? null) : null,
            'assignee_permission' => $type === ApprovalTemplateStep::ASSIGNEE_PERMISSION ? ($data['assignee_permission'] ?? null) : null,
            'is_mandatory' => $request->has('is_mandatory'),
        ];
    }
}
