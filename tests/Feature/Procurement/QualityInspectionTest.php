<?php

namespace Tests\Feature\Procurement;

use App\Enums\Procurement\GrnStatus;
use App\Enums\Procurement\PurchaseOrderStatus;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\QualityInspection;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class QualityInspectionTest extends TestCase
{
    use DatabaseTransactions;

    private function setupTestGrn(): array
    {
        Permission::findOrCreate('quality-inspection.perform', 'web');
        Permission::findOrCreate('quality-inspection.view', 'web');
        Permission::findOrCreate('quality-inspections:view', 'web');
        Permission::findOrCreate('quality-inspections:perform', 'web');

        $inspector = User::factory()->create();
        $inspector->givePermissionTo(['quality-inspection.perform', 'quality-inspection.view']);

        $vendor = Vendor::create([
            'name' => 'Test Quality Vendor ' . uniqid(),
            'code' => 'TV-' . rand(1000, 9999),
            'status' => 'active',
        ]);

        $po = PurchaseOrder::create([
            'po_no' => 'PO-QI-' . uniqid(),
            'vendor_id' => $vendor->id,
            'status' => PurchaseOrderStatus::Approved->value,
            'purchase_date' => now(),
            'grand_total' => 10000,
        ]);

        $poItem = PoItem::create([
            'po_id' => $po->id,
            'item_name' => 'Inspection Test Consumable',
            'quantity' => 10,
            'unit' => 'Nos',
            'purchase_unit' => 'Nos',
            'price' => 500,
            'total' => 5000,
            'item_type' => 'consumable',
        ]);

        $grn = Grn::create([
            'grn_no' => 'GRN-QI-' . uniqid(),
            'po_id' => $po->id,
            'vendor_id' => $vendor->id,
            'received_by' => $inspector->id,
            'received_date' => now(),
            'status' => GrnStatus::PendingInspection->value,
            'inspection_status' => 'pending',
        ]);

        $grnItem1 = GrnItem::create([
            'grn_id' => $grn->id,
            'po_item_id' => $poItem->id,
            'ordered_quantity' => 5,
            'received_quantity' => 5,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_rate' => 500,
            'purchase_unit' => 'Nos',
            'inspection_status' => 'Pending',
        ]);

        $grnItem2 = GrnItem::create([
            'grn_id' => $grn->id,
            'po_item_id' => $poItem->id,
            'ordered_quantity' => 5,
            'received_quantity' => 5,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_rate' => 500,
            'purchase_unit' => 'Nos',
            'inspection_status' => 'Pending',
        ]);

        return [$grn, $grnItem1, $grnItem2, $po, $inspector];
    }

    public function test_can_perform_individual_item_inspection_with_required_fields(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        $response = $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem1]),
            [
                'inspection_status' => 'Approved',
                'remarks' => 'Item meets quality standards.',
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $grnItem1->refresh();
        $this->assertEquals('Approved', $grnItem1->inspection_status);
        $this->assertEquals($inspector->id, $grnItem1->inspected_by);
        $this->assertNotNull($grnItem1->inspected_at);
        $this->assertEquals('Item meets quality standards.', $grnItem1->inspection_remarks);
        $this->assertEquals(5, $grnItem1->accepted_quantity);

        $this->assertDatabaseHas('quality_inspections', [
            'grn_id' => $grn->id,
            'grn_item_id' => $grnItem1->id,
            'inspected_by' => $inspector->id,
            'inspection_status' => 'Approved',
            'remarks' => 'Item meets quality standards.',
        ]);
    }

    public function test_partial_inspection_and_pending_replacement_workflow(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        // Approve item 1
        $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem1]),
            ['inspection_status' => 'Approved', 'remarks' => 'Good unit']
        );

        // Reject item 2
        $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem2]),
            ['inspection_status' => 'Rejected', 'remarks' => 'Defective item, pending replacement']
        );

        $grnItem2->refresh();
        $this->assertEquals('Rejected', $grnItem2->inspection_status);
        $this->assertEquals(0, $grnItem2->accepted_quantity);
        $this->assertEquals(5, $grnItem2->rejected_quantity);

        $grn->refresh();
        $this->assertEquals(GrnStatus::PartiallyAccepted->value, $grn->status->value ?? $grn->status);

        // Verify PO remains open (PartiallyReceived) because accepted_quantity (5) < poItem quantity (10),
        // leaving the remaining rejected units pending for vendor replacement/return
        $po->refresh();
        $this->assertEquals(PurchaseOrderStatus::PartiallyReceived->value, $po->status->value ?? $po->status);
    }

    public function test_batch_inspection_updates_multiple_items_and_logs_history(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        $response = $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-batch', $grn),
            [
                'items' => [
                    [
                        'grn_item_id' => $grnItem1->id,
                        'inspection_status' => 'Approved',
                        'remarks' => 'Batch check ok',
                    ],
                    [
                        'grn_item_id' => $grnItem2->id,
                        'inspection_status' => 'Hold',
                        'remarks' => 'Awaiting manual check',
                    ],
                ],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $grnItem1->refresh();
        $grnItem2->refresh();
        $this->assertEquals('Approved', $grnItem1->inspection_status);
        $this->assertEquals('Hold', $grnItem2->inspection_status);

        $this->assertDatabaseHas('quality_inspections', [
            'grn_id' => $grn->id,
            'grn_item_id' => $grnItem1->id,
            'inspection_status' => 'Approved',
        ]);
        $this->assertDatabaseHas('quality_inspections', [
            'grn_id' => $grn->id,
            'grn_item_id' => $grnItem2->id,
            'inspection_status' => 'Hold',
        ]);
    }

    public function test_unauthorized_users_cannot_perform_inspections(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem1]),
            ['inspection_status' => 'Approved', 'remarks' => 'Unauthorized check']
        );

        $response->assertStatus(403);
    }

    public function test_create_assets_action_converts_only_approved_items_and_populates_details(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        $grnItem1->poItem->update([
            'item_type' => 'asset',
            'description' => 'IT Hardware Item - Warranty: 2 years',
        ]);
        $grnItem2->poItem->update([
            'item_type' => 'asset',
        ]);

        $category = \App\Models\AssetCategory::firstOrCreate(
            ['category_name' => 'IT Equipment'],
            ['category_code' => 'IT', 'status' => 'active']
        );
        $assetType = \App\Models\AssetType::firstOrCreate(
            ['asset_type_name' => 'General Equipment'],
            ['asset_type_code' => 'EQ', 'category_id' => $category->id, 'status' => 'active']
        );

        $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem1]),
            ['inspection_status' => 'Approved', 'remarks' => 'Passed QC']
        );
        $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem2]),
            ['inspection_status' => 'Rejected', 'remarks' => 'Defective units']
        );

        // Delete any auto-converted assets to test the dedicated create-assets trigger
        \App\Models\AssetWarranty::whereIn('asset_id', \App\Models\Asset::where('grn_item_id', $grnItem1->id)->pluck('id'))->delete();
        \App\Models\Asset::where('grn_item_id', $grnItem1->id)->delete();
        \App\Models\Asset::where('grn_item_id', $grnItem2->id)->delete();

        $response = $this->actingAs($inspector)->post(route('quality-inspections.create-assets', $grn));
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $approvedAssets = \App\Models\Asset::where('grn_item_id', $grnItem1->id)->get();
        $this->assertCount(5, $approvedAssets);

        $rejectedAssets = \App\Models\Asset::where('grn_item_id', $grnItem2->id)->get();
        $this->assertCount(0, $rejectedAssets);

        $firstAsset = $approvedAssets->first();
        $this->assertEquals($po->id, $firstAsset->purchase_order_id);
        $this->assertEquals($grn->vendor_id, $firstAsset->supplier);
        $this->assertEquals(500.00, (float) $firstAsset->purchase_cost);
        $this->assertEquals('2 years', $firstAsset->warrenty_information);
        $this->assertNotNull($firstAsset->warrenty_expires);

        $this->assertTrue($firstAsset->warranties()->exists());
        $this->assertStringContainsString('GRN No: ' . $grn->grn_no, (string) $firstAsset->invoice_details);
        $this->assertStringContainsString('PO No: ' . $po->po_no, (string) $firstAsset->invoice_details);
    }

    public function test_unauthorized_users_cannot_create_assets_and_grn_route_also_triggers_creation(): void
    {
        [$grn, $grnItem1, $grnItem2, $po, $inspector] = $this->setupTestGrn();

        $unauthorizedUser = User::factory()->create();
        $response = $this->actingAs($unauthorizedUser)->post(route('quality-inspections.create-assets', $grn));
        $response->assertStatus(403);

        $grnItem1->poItem->update(['item_type' => 'asset']);
        $this->actingAs($inspector)->post(
            route('quality-inspections.inspect-item', ['grn' => $grn, 'item' => $grnItem1]),
            ['inspection_status' => 'Approved', 'remarks' => 'Ready for creation']
        );
        \App\Models\AssetWarranty::whereIn('asset_id', \App\Models\Asset::where('grn_item_id', $grnItem1->id)->pluck('id'))->delete();
        \App\Models\Asset::where('grn_item_id', $grnItem1->id)->delete();

        Permission::findOrCreate('grn.view', 'web');
        $inspector->givePermissionTo('grn.view');

        $grnResponse = $this->actingAs($inspector)->post(route('grns.create-assets', $grn));
        $grnResponse->assertStatus(302);
        $grnResponse->assertSessionHas('success');

        $this->assertEquals(5, \App\Models\Asset::where('grn_item_id', $grnItem1->id)->count());
    }
}
