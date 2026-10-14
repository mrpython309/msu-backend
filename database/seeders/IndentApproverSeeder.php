<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates dedicated L1 / L2 / L3 approver roles + demo logins, an employee
 * login for raising indents, and a default 3-step approval workflow.
 */
class IndentApproverSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            'indent.approve.l1',
            'indent.approve.l2',
            'indent.approve.l3',
            'indent.approve.l4',
            'indent.approve.manager',
            'indent.approve.it_head',
            'indent.approve.finance',
            'indents:view',
            'indents:create',
            'indents:edit',
            'dashboard:view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $l1 = Role::firstOrCreate(['name' => 'L1 Approver', 'guard_name' => $guard]);
        $l2 = Role::firstOrCreate(['name' => 'L2 Approver', 'guard_name' => $guard]);
        $l3 = Role::firstOrCreate(['name' => 'L3 Approver', 'guard_name' => $guard]);
        $l4 = Role::firstOrCreate(['name' => 'L4 Approver', 'guard_name' => $guard]);
        $indentEmployee = Role::firstOrCreate(['name' => 'Indent Employee', 'guard_name' => $guard]);

        $l1->syncPermissions(['dashboard:view', 'indents:view', 'indent.approve.l1', 'indent.approve.manager']);
        $l2->syncPermissions(['dashboard:view', 'indents:view', 'indent.approve.l2', 'indent.approve.it_head']);
        $l3->syncPermissions(['dashboard:view', 'indents:view', 'indent.approve.l3', 'indent.approve.finance']);
        $l4->syncPermissions(['dashboard:view', 'indents:view', 'indent.approve.l4']);

        // Raises indents only — no approve permissions.
        $indentEmployee->syncPermissions([
            'dashboard:view',
            'indents:view',
            'indents:create',
            'indents:edit',
        ]);

        // Default workflow: always L1 → L2 → L3 (replace any previous default).
        ApprovalWorkflow::query()->whereNull('category_id')->delete();

        $steps = [
            [1, 'L1 Manager Approval', 'indent.approve.l1'],
            [2, 'L2 IT Head Approval', 'indent.approve.l2'],
            [3, 'L3 Finance Approval', 'indent.approve.l3'],
        ];

        foreach ($steps as [$seq, $name, $perm]) {
            ApprovalWorkflow::create([
                'workflow_name' => $name,
                'module' => ApprovalWorkflow::MODULE_INDENT,
                'category_id' => null,
                'sequence' => $seq,
                'permission_name' => $perm,
                'is_mandatory' => true,
                'is_active' => true,
            ]);
        }

        $departmentId = Department::query()->value('id');

        $accounts = [
            [
                'email' => 'employee@ks.com',
                'name' => 'Indent Employee',
                'role' => 'Indent Employee',
                'password' => '123456',
            ],
            [
                'email' => 'l1@ks.com',
                'name' => 'L1 Manager',
                'role' => 'L1 Approver',
                'password' => '123456',
            ],
            [
                'email' => 'l2@ks.com',
                'name' => 'L2 IT Head',
                'role' => 'L2 Approver',
                'password' => '123456',
            ],
            [
                'email' => 'l3@ks.com',
                'name' => 'L3 Finance',
                'role' => 'L3 Approver',
                'password' => '123456',
            ],
            [
                'email' => 'l4@ks.com',
                'name' => 'L4 Director',
                'role' => 'L4 Approver',
                'password' => '123456',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'employee_status' => 'active',
                ]
            );

            // Keep password predictable for demo accounts on re-seed.
            $user->forceFill([
                'name' => $account['name'],
                'password' => Hash::make($account['password']),
                'employee_status' => 'active',
            ])->save();

            $user->syncRoles([$account['role']]);

            if ($departmentId) {
                $user->departments()->syncWithoutDetaching([$departmentId]);
            }
        }
    }
}
