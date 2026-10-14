<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates dedicated roles and sample logins for the split RFQ/Quotation workflow:
 * - User 1: RFQ Creator (Creates and sends RFQs)
 * - User 2: Quotation Negotiator (Records vendor quotations and performs negotiations)
 * - User 3: Vendor Selector (Compares quotations and selects the final vendor for PO)
 */
class RfqWorkflowRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $rfqCreatorPermissions = [
            'dashboard:view',
            'rfqs:view',
            'rfqs:create',
            'rfqs:edit',
            'rfqs:send',
            'rfqs:delete',
            'rfq.view',
            'rfq.create',
            'rfq.edit',
            'rfq.send',
            'rfq.close',
            'rfq.delete',
            'purchase-requisition.view',
            'purchase-requisitions:view',
            'quotations:view',
            'quotation.view',
        ];

        $quotationNegotiatorPermissions = [
            'dashboard:view',
            'rfqs:view',
            'rfq.view',
            'quotations:view',
            'quotation.view',
            'quotations:create',
            'quotation.add',
            'quotation.negotiate',
            'purchase-requisition.view',
            'purchase-requisitions:view',
        ];

        $vendorSelectorPermissions = [
            'dashboard:view',
            'rfqs:view',
            'rfq.view',
            'quotations:view',
            'quotation.view',
            'quotations:compare',
            'quotation.compare',
            'quotations:select-vendor',
            'quotation.select-vendor',
            'purchase-requisition.view',
            'purchase-requisitions:view',
        ];

        $allPermissions = array_unique(array_merge(
            $rfqCreatorPermissions,
            $quotationNegotiatorPermissions,
            $vendorSelectorPermissions
        ));

        foreach ($allPermissions as $permName) {
            Permission::findOrCreate($permName, $guard);
        }

        $rfqCreatorRole = Role::firstOrCreate(['name' => 'RFQ Creator', 'guard_name' => $guard]);
        $quotationNegotiatorRole = Role::firstOrCreate(['name' => 'Quotation Negotiator', 'guard_name' => $guard]);
        $vendorSelectorRole = Role::firstOrCreate(['name' => 'Vendor Selector', 'guard_name' => $guard]);

        $rfqCreatorRole->syncPermissions($rfqCreatorPermissions);
        $quotationNegotiatorRole->syncPermissions($quotationNegotiatorPermissions);
        $vendorSelectorRole->syncPermissions($vendorSelectorPermissions);

        $departmentId = Department::query()->value('id');

        $accounts = [
            [
                'email' => 'rfq.creator@ks.com',
                'name' => 'User 1 - RFQ Creator',
                'role' => 'RFQ Creator',
            ],
            [
                'email' => 'quotation.negotiator@ks.com',
                'name' => 'User 2 - Quotation Negotiator',
                'role' => 'Quotation Negotiator',
            ],
            [
                'email' => 'vendor.selector@ks.com',
                'name' => 'User 3 - Vendor Selector',
                'role' => 'Vendor Selector',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => '123456',
                    'department_id' => $departmentId,
                ]
            );

            $user->syncRoles($account['role']);
        }
    }
}
