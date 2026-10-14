<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Purchase Officer role + procurement permissions (dot-notation).
 * Includes GRN maintenance for POs they create.
 */
class PurchaseOfficerSeeder extends Seeder
{
    public const ROLE = 'Purchase Officer';

    /** @var list<string> */
    public const PERMISSIONS = [
        'purchase-requisition.view',
        'purchase-requisition.process',
        'rfq.view',
        'rfq.create',
        'rfq.edit',
        'rfq.send',
        'rfq.delete',
        'quotation.view',
        'quotation.add',
        'quotation.compare',
        'quotation.select-vendor',
        'quotation.negotiate',
        'purchase-order.view',
        'purchase-order.create',
        'purchase-order.edit',
        'purchase-order.delete',
        'purchase-order.print',
        'purchase-order.cancel',
        'purchase-report.view',
        // GRN view only — GRN create is restricted to store login
        'grn.view',
        'grn.edit',
        'grn.inspect',
        // Dashboard entry for Purchase Management
        'purchase-management.dashboard',
    ];

    /** Legacy colon permissions also granted so older checks keep working. */
    public const LEGACY_GRN_PERMISSIONS = [
        'grns:view',
        'grns:edit',
        'grns:approve',
    ];

    public const LEGACY_PO_PERMISSIONS = [
        'purchase-orders:view',
        'purchase-orders:create',
        'purchase-orders:edit',
        'purchase-orders:delete',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, $guard);
        }

        foreach (self::LEGACY_GRN_PERMISSIONS as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $role = Role::firstOrCreate(['name' => self::ROLE, 'guard_name' => $guard]);
        $role->syncPermissions(array_merge(
            self::PERMISSIONS,
            self::LEGACY_GRN_PERMISSIONS,
            self::LEGACY_PO_PERMISSIONS
        ));

        // Explicitly ensure Purchase Officer cannot create GRNs or record payments.
        foreach (['grn.create', 'grns:create', 'purchase-order.payment', 'purchase-order.pay', 'purchase-orders:payment'] as $perm) {
            if (Permission::where('name', $perm)->where('guard_name', $guard)->exists()
                && $role->hasPermissionTo($perm)) {
                $role->revokePermissionTo($perm);
            }
        }

        // Admins keep full access including the new permission set + legacy aliases.
        foreach (['superadmin', 'Super Admin', 'Admin', 'admin'] as $adminRole) {
            $admin = Role::where('name', $adminRole)->where('guard_name', $guard)->first();
            if ($admin) {
                $admin->givePermissionTo(array_merge(
                    self::PERMISSIONS,
                    self::LEGACY_GRN_PERMISSIONS,
                    self::LEGACY_PO_PERMISSIONS,
                    ['grn.create', 'grns:create', 'rfqs:view', 'rfqs:create', 'rfqs:edit', 'purchase-order.payment', 'purchase-order.pay', 'purchase-orders:payment']
                ));
            }
        }

        // Revoke PR module access from non–Purchase Officer roles.
        foreach (['Manager', 'L1 Approver', 'L2 Approver', 'L3 Approver', 'L4 Approver', 'Indent Employee', 'employee'] as $roleName) {
            $r = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if (! $r) {
                continue;
            }
            foreach (['purchase-requisition.view', 'purchase-requisition.process', 'purchase-requisitions:view'] as $perm) {
                if ($r->hasPermissionTo($perm)) {
                    $r->revokePermissionTo($perm);
                }
            }
        }

        // Indent Approvers / Managers must NOT process purchasing unless also Purchase Officer.
        $revoke = array_merge(self::PERMISSIONS, [
            'rfqs:create',
            'rfqs:edit',
            'purchase-orders:create',
            'purchase-orders:edit',
        ]);

        foreach (['Manager', 'L1 Approver', 'L2 Approver', 'L3 Approver', 'L4 Approver', 'Indent Employee'] as $roleName) {
            $r = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if (! $r) {
                continue;
            }
            foreach ($revoke as $perm) {
                if ($r->hasPermissionTo($perm)) {
                    $r->revokePermissionTo($perm);
                }
            }
            // Managers may still view PRs for oversight if they had view — revoke create/edit only kept above.
            // Spec: Indent Approvers cannot create RFQs/POs — revoke create/edit (done).
        }

        // Generic "employee" role currently has everything in AssetPermissionSeeder — strip purchase process.
        if ($employee = Role::where('name', 'employee')->where('guard_name', $guard)->first()) {
            foreach ($revoke as $perm) {
                if (Permission::where('name', $perm)->where('guard_name', $guard)->exists()
                    && $employee->hasPermissionTo($perm)) {
                    $employee->revokePermissionTo($perm);
                }
            }
        }

        $departmentId = Department::query()->value('id');

        $user = User::firstOrCreate(
            ['email' => 'purchase.officer@ks.com'],
            [
                'name' => 'Purchase Officer',
                'password' => Hash::make('123456'),
                'employee_status' => 'active',
            ]
        );

        $user->forceFill([
            'name' => 'Purchase Officer',
            'password' => Hash::make('123456'),
            'employee_status' => 'active',
        ])->save();

        $user->syncRoles([self::ROLE]);

        // Allow dashboard home if present.
        if (Permission::where('name', 'dashboard:view')->exists()) {
            $role->givePermissionTo('dashboard:view');
            $user->givePermissionTo('dashboard:view');
        }

        if ($departmentId) {
            $user->departments()->syncWithoutDetaching([$departmentId]);
        }

        // Store login — only this account may create GRNs.
        $storeEmail = (string) config('procurement.grn_create_email', 'store123@gmail.com');
        $storeRole = Role::firstOrCreate(['name' => 'Store Keeper', 'guard_name' => $guard]);

        foreach (['grn.view', 'grn.create', 'grn.edit', 'grn.inspect', 'grns:view', 'grns:create', 'grns:edit', 'grns:approve', 'dashboard:view'] as $perm) {
            Permission::findOrCreate($perm, $guard);
        }
        $storeRole->syncPermissions([
            'grn.view', 'grn.create', 'grn.edit', 'grn.inspect',
            'grns:view', 'grns:create', 'grns:edit', 'grns:approve',
            'dashboard:view',
        ]);

        $storeUser = User::firstOrCreate(
            ['email' => $storeEmail],
            [
                'name' => 'Store Keeper',
                'password' => Hash::make('123456'),
                'employee_status' => 'active',
            ]
        );
        $storeUser->forceFill([
            'name' => 'Store Keeper',
            'password' => Hash::make('123456'),
            'employee_status' => 'active',
        ])->save();
        $storeUser->syncRoles(['Store Keeper']);
        if ($departmentId) {
            $storeUser->departments()->syncWithoutDetaching([$departmentId]);
        }

        // Strip GRN create from other common roles (create remains email-gated in controller).
        foreach (['Manager', 'employee', 'Purchase Officer', 'L1 Approver', 'L2 Approver', 'L3 Approver', 'L4 Approver', 'Indent Employee'] as $roleName) {
            $r = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if (! $r) {
                continue;
            }
            foreach (['grn.create', 'grns:create'] as $perm) {
                if (Permission::where('name', $perm)->where('guard_name', $guard)->exists()
                    && $r->hasPermissionTo($perm)) {
                    $r->revokePermissionTo($perm);
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
