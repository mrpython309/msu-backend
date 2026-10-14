<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\AssetCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds a useful default + a few category examples. Safe to re-run:
 * it only creates steps when a category has none configured yet.
 */
class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'indent.approve.manager',
            'indent.approve.it_head',
            'indent.approve.finance',
            'approval-workflows:manage',
            'indents:approve',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Approver step permissions + template management for Admin as well.
        $approverPerms = [
            'indent.approve.manager',
            'indent.approve.it_head',
            'indent.approve.finance',
            'indents:approve',
        ];
        foreach (['Super Admin', 'Admin', 'superadmin'] as $roleName) {
            if ($role = Role::where('name', $roleName)->where('guard_name', 'web')->first()) {
                $role->givePermissionTo($approverPerms);
            }
        }
        foreach (['Super Admin', 'superadmin', 'Admin'] as $roleName) {
            if ($role = Role::where('name', $roleName)->where('guard_name', 'web')->first()) {
                $role->givePermissionTo('approval-workflows:manage');
            }
        }

        // Default fallback: one manager approval for any category without its own flow.
        if (! ApprovalWorkflow::query()->whereNull('category_id')->exists()) {
            ApprovalWorkflow::create([
                'workflow_name' => 'Manager Approval',
                'module' => ApprovalWorkflow::MODULE_INDENT,
                'category_id' => null,
                'sequence' => 1,
                'permission_name' => 'indent.approve.manager',
                'is_mandatory' => true,
                'is_active' => true,
            ]);
        }

        $examples = [
            'CCTV' => [
                ['Manager Approval', 'indent.approve.manager'],
            ],
            'Laptop' => [
                ['Manager Approval', 'indent.approve.manager'],
                ['IT Head Approval', 'indent.approve.it_head'],
            ],
            'Furniture' => [
                ['Manager Approval', 'indent.approve.manager'],
            ],
            'Server' => [
                ['Manager Approval', 'indent.approve.manager'],
                ['IT Head Approval', 'indent.approve.it_head'],
                ['Finance Approval', 'indent.approve.finance'],
            ],
        ];

        foreach ($examples as $categoryName => $steps) {
            $category = AssetCategory::query()
                ->where('category_name', 'like', $categoryName)
                ->first();

            if (! $category) {
                continue;
            }

            if (ApprovalWorkflow::query()->where('category_id', $category->id)->exists()) {
                continue;
            }

            foreach ($steps as $i => [$stepName, $permission]) {
                ApprovalWorkflow::create([
                    'workflow_name' => $stepName,
                    'module' => ApprovalWorkflow::MODULE_INDENT,
                    'category_id' => $category->id,
                    'sequence' => $i + 1,
                    'permission_name' => $permission,
                    'is_mandatory' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
