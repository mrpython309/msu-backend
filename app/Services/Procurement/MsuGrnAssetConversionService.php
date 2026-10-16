<?php

namespace App\Services\Procurement;

use App\AssetCondition;
use App\AssetOperationalStatus;
use App\AssetStatus;
use App\Enums\Procurement\MsuGrnAssetStatus;
use App\Models\Asset;
use App\Models\AssetName;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\Company;
use App\Models\Department;
use App\Models\MsuGrn;
use App\Models\MsuGrnItem;
use App\Models\MsuItemMaster;
use App\Models\MsuPurchaseOrderItem;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert MSU GRN received items into Asset records.
 * Only CAPEX lines move to Asset Management.
 * Group Asset creates one asset whose quantity is the received qty.
 * Otherwise one asset is created for each received unit.
 */
class MsuGrnAssetConversionService
{
    /** @var array<string, true>|null */
    private ?array $assetColumns = null;

    /**
     * @return array{assets: Collection<int, Asset>, count: int}
     */
    public function generateFromGrn(MsuGrn $grn, ?User $actor = null): array
    {
        return DB::transaction(function () use ($grn, $actor) {
            $lockedGrn = MsuGrn::query()
                ->with([
                    'items.itemMaster.productCategory',
                    'items.itemMaster.productSubCategory',
                    'items.itemMaster.hsn',
                    'items.purchaseOrderItem',
                    'purchaseOrder.branch.company',
                    'vendor',
                    'department',
                ])
                ->whereKey($grn->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedGrn->is_added_to_asset || $lockedGrn->assetStatusEnum() === MsuGrnAssetStatus::Approved) {
                throw new \RuntimeException('Assets have already been created for this GRN.');
            }

            if ($lockedGrn->assetStatusEnum() === MsuGrnAssetStatus::Cancelled) {
                throw new \RuntimeException('Asset conversion was cancelled for this GRN.');
            }

            $usesItemFlags = $lockedGrn->items->contains(
                fn (MsuGrnItem $item) => (bool) $item->is_capex
            );

            if (! $lockedGrn->isCapex() && ! $usesItemFlags) {
                throw new \RuntimeException('Only Capex GRNs can be converted to assets.');
            }

            $created = collect();

            foreach ($lockedGrn->items as $item) {
                $created = $created->merge(
                    $this->generateFromGrnItem($item, $lockedGrn, $actor, $usesItemFlags)
                );
            }

            $lockedGrn->update([
                'is_added_to_asset' => true,
                'asset_status' => MsuGrnAssetStatus::Approved,
            ]);

            return [
                'assets' => $created->values(),
                'count' => $created->count(),
            ];
        });
    }

    /**
     * @return Collection<int, Asset>
     */
    public function generateFromGrnItem(MsuGrnItem $item, MsuGrn $grn, ?User $actor = null, bool $usesItemFlags = true): Collection
    {
        $qty = max(0, (float) $item->received_quantity);
        $units = max(0, (int) floor($qty));
        if ($units < 1) {
            return collect();
        }

        $isCapex = $usesItemFlags ? (bool) $item->is_capex : true;
        if (! $isCapex) {
            return collect();
        }

        $isGroup = $usesItemFlags
            ? ((bool) $item->is_group_asset && $units > 1)
            : ($units > 1);

        $assetCount = $isGroup ? 1 : $units;
        $created = collect();

        for ($unitIndex = 1; $unitIndex <= $assetCount; $unitIndex++) {
            $asset = $this->createAssetForUnit($item, $grn, $unitIndex, $isGroup, $units, $assetCount, $actor);
            if ($asset) {
                $created->push($asset);
            }
        }

        return $created;
    }

    private function createAssetForUnit(
        MsuGrnItem $item,
        MsuGrn $grn,
        int $unitIndex,
        bool $isGroup,
        int $units,
        int $assetCount,
        ?User $actor = null
    ): ?Asset {
        $existing = Asset::query()
            ->where('msu_grn_item_id', $item->id)
            ->where('source_unit_index', $unitIndex)
            ->first();

        if ($existing) {
            return $existing;
        }

        $itemMaster = $item->itemMaster
            ?: MsuItemMaster::with(['productCategory', 'productSubCategory', 'hsn'])->find($item->msu_item_master_id);

        $itemsCount = $isGroup ? $units : 1;

        $hsn = trim((string) (
            $item->hsn
            ?: $itemMaster?->hsn?->hsn_code
            ?: $itemMaster?->hsn?->code
            ?: ''
        ));

        $serialBase = $hsn !== '' ? $hsn : ('HSN-'.$item->id);
        if ($assetCount > 1) {
            $serialNo = $serialBase.'-'.$item->id.'-'.$unitIndex;
        } else {
            $serialNo = $serialBase;
            if (Asset::query()->where('serial_no', $serialNo)->exists()) {
                $serialNo = $serialNo.'-'.$item->id;
            }
        }

        $invoiceNumber = trim((string) ($grn->vendor_invoice_number ?? ''));

        [$companyId, $branchId, $locationId] = $this->resolveWarehouseIds($grn);
        $companyId = $this->idIfExists(Company::class, $companyId);
        $branchId = $this->idIfExists(Branch::class, $branchId);
        $locationId = $this->idIfExists(BranchLocation::class, $locationId);

        $assetName = $itemMaster?->item_name
            ?: $item->item_name
            ?: ('GRN '.$grn->grn_number);
        $assetName = mb_substr(trim($assetName), 0, 255);

        $description = sprintf(
            'Created from MSU GRN %s | Item: %s (%s)',
            $grn->grn_number,
            $item->item_name,
            $item->item_code
        );
        if (! $isGroup && $assetCount > 1) {
            $description .= sprintf(' | Unit %d of %d', $unitIndex, $assetCount);
        }

        $payload = [
            'asset_name' => $assetName,
            'asset_structure' => $isGroup ? 'group' : 'single',
            'items_count' => $itemsCount,
            'category_id' => null,
            'sub_category_id' => null,
            'asset_type_id' => $this->resolveRequiredAssetTypeId(),
            'product_category_id' => $this->idIfExists(ProductCategory::class, $itemMaster?->product_category_id),
            // A deleted product sub category must not block the GRN. Leave this blank.
            'product_sub_category_id' => $this->idIfExists(ProductSubCategory::class, $itemMaster?->product_sub_category_id),
            'serial_no' => $serialNo,
            'hsn_code' => $hsn !== '' ? $hsn : null,
            'purchase_date' => $grn->grn_date,
            'purchase_cost' => $this->assetInvoiceAmount($item, $isGroup, $assetCount, $unitIndex),
            'status' => AssetStatus::AVAILABLE,
            'operational_status' => AssetOperationalStatus::WORKING,
            'condition' => AssetCondition::NEW,
            'description' => trim($description),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'location_id' => $locationId,
            'supplier' => $this->idIfExists(Vendor::class, $grn->vendor_id),
            'msu_grn_item_id' => $item->id,
            'source_unit_index' => $unitIndex,
            'department_id' => $this->idIfExists(Department::class, $grn->department_id),
            'invoice_details' => $invoiceNumber !== '' ? $invoiceNumber : null,
            'depreciation_method' => 'no_depreciation',
            'created_by' => $actor?->id,
        ];

        $lastException = null;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $payload['asset_code'] = Asset::generateAssetCode(
                    $companyId ? (int) $companyId : null,
                    $branchId ? (int) $branchId : null
                );
                $asset = Asset::create($this->onlyExistingAssetColumns($payload));

                $this->syncAssetNameCatalog($asset->asset_name);

                return $asset;
            } catch (QueryException $e) {
                $lastException = $e;

                if ($this->isUniqueViolation($e, 'assets_msu_grn_item_unit_unique')) {
                    return Asset::query()
                        ->where('msu_grn_item_id', $item->id)
                        ->where('source_unit_index', $unitIndex)
                        ->first();
                }

                if ($this->isUniqueViolation($e, 'serial_no')) {
                    $payload['serial_no'] = $serialBase.'-'.$item->id.'-'.$unitIndex.'-'.($attempt + 1);
                    continue;
                }

                if ($this->isUniqueViolation($e, 'asset_code')) {
                    continue;
                }

                if ($this->isMissingForeignKey($e, 'product_sub_category_id')) {
                    $payload['product_sub_category_id'] = null;
                    continue;
                }

                if ($this->isMissingForeignKey($e, 'product_category_id')) {
                    $payload['product_category_id'] = null;
                    continue;
                }

                throw new \RuntimeException($this->conversionFailureMessage($e), 0, $e);
            }
        }

        throw new \RuntimeException(
            'Unable to allocate a unique asset code for MSU GRN conversion. '.$this->conversionFailureMessage($lastException),
            0,
            $lastException
        );
    }

    /**
     * Invoice amount is the GRN line net value (gross + tax on received qty).
     * A group asset keeps the full line net. Separate unit assets share that net
     * so their invoice amounts add up to the GRN net value.
     */
    private function assetInvoiceAmount(MsuGrnItem $item, bool $isGroup, int $assetCount, int $unitIndex): float
    {
        $lineNet = $this->grnLineNetValue($item);

        if ($isGroup || $assetCount <= 1) {
            return $lineNet;
        }

        $share = round($lineNet / $assetCount, 2);
        if ($unitIndex === $assetCount) {
            return round($lineNet - ($share * ($assetCount - 1)), 2);
        }

        return $share;
    }

    private function grnLineNetValue(MsuGrnItem $item): float
    {
        $receivedQty = (float) $item->received_quantity;
        $rate = (float) ($item->purchaseOrderItem?->price ?? 0);
        $taxPercent = (float) ($item->purchaseOrderItem?->tax ?? 0);

        return MsuPurchaseOrderItem::computeNetValue($receivedQty, $rate, $taxPercent);
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?int}
     */
    private function resolveWarehouseIds(MsuGrn $grn): array
    {
        $branch = $grn->purchaseOrder?->branch;
        $branchId = $branch?->id;
        $companyId = $branch?->company_id ?: $branch?->company?->id;

        if (! $branchId) {
            $branchId = Branch::query()->active()->orderBy('id')->value('id');
            $branchId = $branchId ? (int) $branchId : null;
        }

        if (! $companyId && $branchId) {
            $companyId = Branch::query()->whereKey($branchId)->value('company_id');
        }

        if (! $companyId) {
            $companyId = Company::query()->orderBy('id')->value('id');
        }

        $locationId = null;
        if ($branchId) {
            $locationId = BranchLocation::query()
                ->where('branch_id', $branchId)
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                })
                ->orderBy('id')
                ->value('id');

            // Ensure branch has at least one area so Assets list can show it
            if (! $locationId) {
                $location = BranchLocation::query()->firstOrCreate(
                    [
                        'branch_id' => $branchId,
                        'location_name' => 'Main',
                    ],
                    [
                        'status' => 'active',
                    ]
                );
                $locationId = $location->id;
            }
        }

        return [
            $companyId ? (int) $companyId : null,
            $branchId ? (int) $branchId : null,
            $locationId ? (int) $locationId : null,
        ];
    }

    private function syncAssetNameCatalog(?string $name): void
    {
        $name = trim((string) $name);
        if ($name === '') {
            return;
        }

        $existing = AssetName::query()->firstOrCreate(
            ['name_normalized' => strtolower($name)],
            [
                'name' => $name,
                'status' => 'active',
            ]
        );

        if ($existing->status !== 'active') {
            $existing->update(['status' => 'active']);
        }
    }

    private function isUniqueViolation(QueryException $e, string $indexHint): bool
    {
        $message = strtolower($e->getPrevious()?->getMessage() ?: $e->getMessage());

        return str_contains($message, 'duplicate')
            && str_contains($message, strtolower($indexHint));
    }

    /**
     * MySQL 1452 when the item still points at a product category row that was deleted.
     */
    private function isMissingForeignKey(QueryException $e, string $column): bool
    {
        $message = strtolower((string) $e->getPrevious()?->getMessage());

        return (str_contains($message, 'foreign key') || str_contains($message, '1452'))
            && str_contains($message, strtolower($column));
    }

    private function conversionFailureMessage(?QueryException $e): string
    {
        if (! $e) {
            return 'The asset could not be saved.';
        }

        $message = trim((string) ($e->getPrevious()?->getMessage() ?: $e->getMessage()));
        $message = trim((string) preg_replace('/\s+/', ' ', $message));

        if (str_contains(strtolower($message), 'purchase_cost') && str_contains(strtolower($message), 'out of range')) {
            return 'The GRN invoice amount is too large for the asset purchase cost column. Run database/sql/assets_purchase_cost_precision.sql on the database, then approve the GRN again.';
        }

        return 'Could not create the asset from this GRN. '.$message;
    }

    /**
     * asset_type_id is NOT NULL on databases that have not applied the
     * product-category migration. Leave it empty when the column allows null.
     */
    private function resolveRequiredAssetTypeId(): ?int
    {
        if (! $this->assetColumnExists('asset_type_id') || $this->columnIsNullable('assets', 'asset_type_id')) {
            return null;
        }

        $id = AssetType::query()->active()->orderBy('id')->value('id')
            ?? AssetType::query()->orderBy('id')->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) AssetType::query()->create([
            'asset_type_name' => 'General Asset',
            'asset_type_description' => 'Auto-created during MSU GRN asset conversion',
            'status' => 'active',
        ])->id;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function idIfExists(string $modelClass, mixed $id): ?int
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        return $modelClass::query()->whereKey($id)->exists() ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function onlyExistingAssetColumns(array $payload): array
    {
        if ($this->assetColumns === null) {
            $this->assetColumns = array_fill_keys(Schema::getColumnListing('assets'), true);
        }

        return array_intersect_key($payload, $this->assetColumns);
    }

    private function assetColumnExists(string $column): bool
    {
        if ($this->assetColumns === null) {
            $this->assetColumns = array_fill_keys(Schema::getColumnListing('assets'), true);
        }

        return isset($this->assetColumns[$column]);
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $info = collect(Schema::getColumns($table))->firstWhere('name', $column);

        return (bool) ($info['nullable'] ?? true);
    }
}
