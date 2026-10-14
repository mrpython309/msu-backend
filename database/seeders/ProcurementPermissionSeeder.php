<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Register every procurement submodule permission in the permissions table
 * so they appear under Master → Permissions and can be assigned on Roles.
 *
 * Submodules covered:
 * - Indents
 * - Purchase Requisitions
 * - RFQs
 * - Quotations
 * - Purchase Orders
 * - GRNs
 * - Dashboards / Reports
 * - Approval Templates / Workflows
 */
class ProcurementPermissionSeeder extends Seeder
{
    /** @var list<string> */
    public const PERMISSIONS = [
        // ── Indents ──────────────────────────────────────────────
        'indents:view',
        'indents:create',
        'indents:edit',
        'indents:delete',
        'indents:submit',
        'indents:approve',
        'indents:reject',
        'indents:send-back',
        'indents:cancel',
        'indents:print',
        'indent.approve.manager',
        'indent.approve.it_head',
        'indent.approve.finance',
        'indent.approve.l1',
        'indent.approve.l2',
        'indent.approve.l3',
        'indent.approve.l4',

        // ── Purchase Requisitions ────────────────────────────────
        'purchase-requisitions:view',
        'purchase-requisitions:create',
        'purchase-requisitions:edit',
        'purchase-requisitions:process',
        'purchase-requisitions:proceed-to-rfq',
        'purchase-requisitions:transfer-from-store',
        'purchase-requisitions:manual-close',
        'purchase-requisitions:print',
        'purchase-requisition.view',
        'purchase-requisition.process',
        'purchase-requisition.proceed-to-rfq',
        'purchase-requisition.transfer-from-store',
        'purchase-requisition.manual-close',
        'purchase-requisition.print',
        'purchase-requisition.close',

        // ── RFQs ─────────────────────────────────────────────────
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

        // ── Quotations ───────────────────────────────────────────
        'quotations:view',
        'quotations:create',
        'quotations:compare',
        'quotations:select-vendor',
        'quotation.view',
        'quotation.add',
        'quotation.compare',
        'quotation.select-vendor',
        'quotation.negotiate',

        // ── Purchase Orders ──────────────────────────────────────
        'purchase-orders:view',
        'purchase-orders:create',
        'purchase-orders:edit',
        'purchase-orders:delete',
        'purchase-orders:print',
        'purchase-orders:cancel',
        'purchase-orders:approve',
        'purchase-order.view',
        'purchase-order.create',
        'purchase-order.edit',
        'purchase-order.delete',
        'purchase-order.print',
        'purchase-order.cancel',
        'purchase-order.submit',
        'purchase-order.approve',
        'purchase-order.reject',
        'purchase-order.send-back',
        'purchase-order.payment',
        'purchase-order.pay',
        'purchase-orders:payment',

        // ── GRNs ─────────────────────────────────────────────────
        'grns:view',
        'grns:create',
        'grns:edit',
        'grns:approve',
        'grns:inspect',
        'grn.view',
        'grn.create',
        'grn.edit',
        'grn.approve',
        'grn.inspect',
        'grn.delete',

        // ── Dashboards / Reports ─────────────────────────────────
        'procurement.dashboard:view',
        'procurement.reports:view',
        'purchase-management.dashboard',
        'purchase-report.view',

        // ── Approval Templates / Workflows ───────────────────────
        'approval-workflows:manage',
        'approval-templates:view',
        'approval-templates:create',
        'approval-templates:edit',
        'approval-templates:delete',
        'approval-templates:manage',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, $guard);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Procurement permissions registered: '.count(self::PERMISSIONS));
    }
}
