<?php

namespace App\Services\Procurement;

use App\AssetCondition;
use App\AssetOperationalStatus;
use App\AssetStatus;
use App\Enums\Procurement\GrnStatus;
use App\Enums\Procurement\ItemType;
use App\Models\Asset;
use App\Models\AssetName;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\Brand;
use App\Models\Company;
use App\Models\AssetWarranty;
use App\Models\ItemMaster;
use App\Models\MsuItemMaster;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * After GRN acceptance, asset-type items become one Asset record per accepted unit.
 */
class GrnAssetConversionService
{
    public function __construct(
        private readonly ProcurementActivityLogger $logger,
    ) {
    }

    /**
     * Repair GRN assets that have no warehouse: assign a valid fallback company/branch/location
     * so they appear correctly in the Assets module.
     */
    public function normalizeGrnAssetWarehouse(?User $actor = null): void
    {
        [$companyId, $branchId, $locationId] = $this->resolveWarehouseIds(new Grn(), $actor, []);

        if (! $companyId && ! $branchId) {
            return;
        }

        Asset::query()
            ->whereNotNull('grn_item_id')
            ->where(function ($q) {
                $q->whereNull('company_id')
                    ->orWhereNull('branch_id')
                    ->orWhereNull('location_id');
            })
            ->whereIn('status', [
                AssetStatus::AVAILABLE->value,
                AssetStatus::TRANSFERRED->value,
                AssetStatus::ASSIGNED->value,
            ])
            ->orderBy('id')
            ->chunkById(100, function ($assets) use ($companyId, $branchId, $locationId) {
                foreach ($assets as $asset) {
                    $updates = [];
                    if (! $asset->company_id && $companyId) {
                        $updates['company_id'] = $companyId;
                    }
                    if (! $asset->branch_id && $branchId) {
                        $updates['branch_id'] = $branchId;
                    }
                    if (! $asset->location_id && $locationId) {
                        $updates['location_id'] = $locationId;
                    }
                    if ($updates !== []) {
                        $asset->update($updates);
                    }
                }
            });
    }

    /**
     * Process accepted GRN lines and create asset records for asset-type items.
     *
     * @return array{assets: Collection<int, Asset>}
     */
    public function generateFromGrn(Grn $grn, ?User $actor = null, array $defaults = [], bool $force = false): array
    {
        return DB::transaction(function () use ($grn, $actor, $defaults, $force) {
            $lockedGrn = Grn::query()
                ->with(['items.poItem.indentItem.category', 'items.poItem.indentItem.indent', 'items.poItem.purchaseOrder', 'purchaseOrder', 'vendor'])
                ->whereKey($grn->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $status = $lockedGrn->statusEnum();
            $hasApprovedItems = $lockedGrn->items->contains(fn ($i) => $i->isApproved());
            if (! $status?->allowsAssetGeneration() && ! $hasApprovedItems) {
                throw new ProcurementException('Assets can only be generated from an accepted GRN or after approved quality inspection.');
            }

            $created = collect();

            foreach ($lockedGrn->items as $item) {
                if (! $force && ! $this->resolveItemType($item)->createsAssetRecords()) {
                    continue;
                }

                $created = $created->merge(
                    $this->generateFromGrnItem($item, $lockedGrn, $actor, $defaults, $force)
                );
            }

            $this->logger->log($lockedGrn, 'grn.post_acceptance_processed', null, $lockedGrn->status, null, [
                'asset_count' => $created->count(),
            ], $actor?->id);

            return [
                'assets' => $created->values(),
            ];
        });
    }

    /**
     * @return Collection<int, Asset>
     */
    public function generateFromGrnItem(
        GrnItem $grnItem,
        ?Grn $grn = null,
        ?User $actor = null,
        array $defaults = [],
        bool $force = false,
    ): Collection {
        return DB::transaction(function () use ($grnItem, $grn, $actor, $defaults, $force) {
            /** @var GrnItem $lockedItem */
            $lockedItem = GrnItem::query()
                ->with(['poItem.indentItem.category', 'poItem.indentItem.indent', 'poItem.purchaseOrder', 'grn.purchaseOrder', 'grn.vendor', 'assets'])
                ->whereKey($grnItem->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $force && ! $this->resolveItemType($lockedItem)->createsAssetRecords()) {
                return collect();
            }

            if (! $lockedItem->isApproved()) {
                return collect();
            }

            $grn ??= $lockedItem->grn;
            $accepted = max(0, (int) $lockedItem->accepted_quantity);

            if ($accepted < 1) {
                return collect();
            }

            $serials = array_values(array_filter(
                is_array($lockedItem->serial_numbers) ? $lockedItem->serial_numbers : [],
                fn ($serial) => filled($serial)
            ));

            $existingByUnit = $lockedItem->assets
                ->whereNotNull('source_unit_index')
                ->keyBy('source_unit_index');

            $created = collect();
            $unitCost = $this->unitCost($lockedItem);

            for ($unit = 1; $unit <= $accepted; $unit++) {
                if ($existingByUnit->has($unit)) {
                    $existing = $existingByUnit->get($unit);
                    $this->patchAssetWarehouse($existing, $grn, $actor, $defaults);
                    $this->syncAssetNameCatalog($existing->asset_name);
                    $created->push($existing);
                    continue;
                }

                $asset = $this->createAssetForUnit($lockedItem, $grn, $unit, $serials[$unit - 1] ?? null, $unitCost, $defaults, $actor);
                $created->push($asset);
            }

            if ($lockedItem->assets_generated_at === null) {
                $lockedItem->update(['assets_generated_at' => now()]);
            }

            $lockedItem->load('assets');
            foreach ($lockedItem->assets as $asset) {
                $this->patchAssetWarehouse($asset, $grn, $actor, $defaults);
            }

            return $created->values();
        });
    }

    private function resolveItemType(GrnItem $item): ItemType
    {
        // MSU Item Master: always treat as asset-capable (CAPEX/OPEX removed)
        $msuItem = $this->resolveMsuItemMaster($item);
        if ($msuItem) {
            return ItemType::Asset;
        }

        $poItem = $item->poItem;
        if ($poItem && method_exists($poItem, 'resolvedItemType')) {
            return $poItem->resolvedItemType();
        }

        return ItemType::resolve(
            $poItem?->getRawOriginal('item_type') ?? $poItem?->item_type,
            $poItem?->indentItem?->category
        );
    }

    private function resolveMsuItemMaster(GrnItem $item): ?MsuItemMaster
    {
        $poItem = $item->poItem;
        $itemCode = $item->item_code ?? $poItem?->item_code;
        $itemName = $poItem?->indentItem?->item_name ?? $poItem?->item_name;

        if (! filled($itemCode) && ! filled($itemName)) {
            return null;
        }

        return MsuItemMaster::query()
            ->active()
            ->when(filled($itemCode), fn ($q) => $q->where('item_code', $itemCode))
            ->when(! filled($itemCode) && filled($itemName), fn ($q) => $q->where('item_name', $itemName))
            ->first();
    }

    private function createAssetForUnit(
        GrnItem $item,
        Grn $grn,
        int $unitIndex,
        ?string $serial,
        float $unitCost,
        array $defaults,
        ?User $actor = null,
    ): Asset {
        $poItem = $item->poItem;
        $indentItem = $poItem?->indentItem;
        $indent = $indentItem?->indent;
        $condition = $item->conditionEnum() ?? AssetCondition::NEW;

        $itemCode = $item->item_code ?? $poItem?->item_code;
        $itemName = $indentItem?->item_name ?? $poItem?->item_name;
        $itemMaster = null;
        if (filled($itemCode) || filled($itemName)) {
            $itemMaster = ItemMaster::query()->active()
                ->when(filled($itemCode), fn ($q) => $q->where('item_code', $itemCode))
                ->when(! filled($itemCode) && filled($itemName), fn ($q) => $q->where('item_name', $itemName))
                ->first();
        }

        [$categoryId, $subCategoryId] = $this->resolveCategoryIds($poItem, $indentItem, $defaults);
        $categoryId = $categoryId ?? $itemMaster?->category_id;

        $assetTypeId = $defaults['asset_type_id']
            ?? $itemMaster?->asset_type_id
            ?? $this->resolveAssetTypeId(
                $categoryId,
                $subCategoryId,
                $itemMaster?->item_name ?? $indentItem?->item_name ?? $poItem?->item_name
            );

        $assetName = $defaults['asset_name']
            ?? $itemMaster?->item_name
            ?? $indentItem?->item_name
            ?? $poItem?->item_name
            ?? ('GRN '.$grn->grn_no.' Unit '.$unitIndex);

        $serialNo = filled($serial)
            ? trim((string) $serial)
            : $this->fallbackSerial($grn, $item, $unitIndex, $poItem?->item_code);

        $warrantyInfo = $item->warranty
            ?? $poItem?->warranty
            ?? $poItem?->purchaseOrder?->quotation?->warranty
            ?? $this->extractWarrantyFromText($poItem?->description ?? $poItem?->remarks ?? $item->remarks ?? $itemMaster?->description)
            ?? $defaults['warrenty_information']
            ?? null;
        $warrantyExpires = null;
        if (filled($warrantyInfo) && preg_match('/(\d+)\s*(year|month|day)/i', (string) $warrantyInfo, $matches)) {
            $num = (int) $matches[1];
            $unit = strtolower($matches[2]);
            $baseDate = $defaults['purchase_date'] ?? $grn->received_date ?? $grn->bill_date ?? Carbon::today();
            $baseDate = Carbon::parse($baseDate);
            $warrantyExpires = match ($unit) {
                'year' => $baseDate->copy()->addYears($num),
                'month' => $baseDate->copy()->addMonths($num),
                'day' => $baseDate->copy()->addDays($num),
                default => null,
            };
        }

        [$companyId, $branchId, $locationId] = $this->resolveWarehouseIds($grn, $actor, $defaults);

        $payload = [
            'asset_name' => $assetName,
            'asset_structure' => 'single',
            'items_count' => 1,
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'asset_type_id' => $assetTypeId,
            'serial_no' => $serialNo,
            'purchase_date' => $defaults['purchase_date'] ?? $grn->received_date ?? $grn->bill_date,
            'purchase_cost' => $unitCost,
            'status' => AssetStatus::AVAILABLE,
            'operational_status' => AssetOperationalStatus::WORKING,
            'condition' => $condition,
            'description' => $this->buildDescription($item, $poItem),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'location_id' => $locationId,
            'supplier' => $grn->vendor_id,
            'grn_item_id' => $item->id,
            'source_unit_index' => $unitIndex,
            'purchase_order_id' => $grn->po_id,
            'indent_id' => $indent?->id,
            'department_id' => $indent?->department_id ?? $poItem?->purchaseOrder?->department_id,
            'brand_id' => $defaults['brand_id'] ?? $this->resolveBrandId($item->brand_name),
            'invoice_details' => $this->buildInvoiceDetails($grn),
            'warrenty_information' => $warrantyInfo,
            'warrenty_expires' => $warrantyExpires,
            'depreciation_method' => $defaults['depreciation_method'] ?? 'no_depreciation',
            'created_by' => $actor?->id,
        ];

        // Last resort: any active asset type so GRN acceptance is never blocked.
        if (empty($payload['asset_type_id'])) {
            $payload['asset_type_id'] = $this->ensureDefaultAssetType($categoryId, $payload['asset_name']);
        }

        if (empty($payload['asset_type_id'])) {
            throw new ProcurementException(
                'asset_type_id is required to create assets from GRN item ['.$item->id.']. '
                .'Create at least one active Asset Type under Asset → Asset Types, or mark the item as Consumable.'
            );
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $asset = DB::transaction(function () use ($payload) {
                    $payload['asset_code'] = Asset::generateAssetCode(
                        isset($payload['company_id']) ? (int) $payload['company_id'] : null,
                        isset($payload['branch_id']) ? (int) $payload['branch_id'] : null
                    );

                    return Asset::create($payload);
                });

                $this->syncAssetNameCatalog($asset->asset_name);
                $this->ensureAssetWarranty($asset, $grn, $warrantyInfo, $warrantyExpires, $actor);

                return $asset;
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e, 'assets_grn_item_unit_unique')) {
                    $existing = Asset::query()
                        ->where('grn_item_id', $item->id)
                        ->where('source_unit_index', $unitIndex)
                        ->first();

                    if ($existing) {
                        $this->syncAssetNameCatalog($existing->asset_name);
                        $this->ensureAssetWarranty($existing, $grn, $warrantyInfo, $warrantyExpires, $actor);

                        return $existing;
                    }
                }

                if ($this->isUniqueViolation($e, 'asset_code') || $attempt < 4) {
                    continue;
                }

                throw $e;
            }
        }

        throw new ProcurementException('Unable to allocate a unique asset code for GRN conversion.');
    }

    /**
     * Ensure GRN item names appear in the Asset Names catalog used by the Assets module.
     */
    private function syncAssetNameCatalog(?string $name): void
    {
        $name = trim((string) $name);
        if ($name === '') {
            return;
        }

        $normalized = strtolower($name);

        $existing = AssetName::query()->firstOrCreate(
            ['name_normalized' => $normalized],
            [
                'name' => $name,
                'status' => 'active',
            ]
        );

        if ($existing->status !== 'active') {
            $existing->update(['status' => 'active']);
        }
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?int} [company_id, branch_id, location_id]
     */
    private function resolveWarehouseIds(Grn $grn, ?User $actor, array $defaults): array
    {
        $companyId = $defaults['company_id'] ?? $this->validSettingId('warehouse_company_id', Company::class);
        $branchId = $defaults['branch_id'] ?? $this->validSettingId('warehouse_id', Branch::class);
        $locationId = $defaults['location_id'] ?? $this->validSettingId('warehouse_location', BranchLocation::class);

        // Settings often hold stale IDs after master data changes — fall back to first active records.
        if (! $branchId) {
            $branchId = Branch::query()->active()->orderBy('id')->value('id');
            $branchId = $branchId ? (int) $branchId : null;
        }

        if ($locationId && $branchId && ! BranchLocation::query()->whereKey($locationId)->where('branch_id', $branchId)->exists()) {
            $locationId = null;
        }

        if (! $locationId && $branchId) {
            $locationId = BranchLocation::query()
                ->where('branch_id', $branchId)
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                })
                ->orderBy('id')
                ->value('id');
            $locationId = $locationId ? (int) $locationId : null;
        }

        if (! $companyId && $branchId) {
            $companyId = Branch::query()->whereKey($branchId)->value('company_id');
            $companyId = $companyId ? (int) $companyId : null;
        }

        if (! $companyId) {
            $companyId = Company::query()->orderBy('id')->value('id');
            $companyId = $companyId ? (int) $companyId : null;
        }

        return [$companyId, $branchId, $locationId];
    }

    private function patchAssetWarehouse(Asset $asset, Grn $grn, ?User $actor, array $defaults): void
    {
        [$companyId, $branchId, $locationId] = $this->resolveWarehouseIds($grn, $actor, $defaults);

        if (! $companyId && ! $branchId) {
            return;
        }

        if ($asset->company_id && $asset->branch_id) {
            return;
        }

        $updates = [];
        if (! $asset->company_id && $companyId) {
            $updates['company_id'] = $companyId;
        }
        if (! $asset->branch_id && $branchId) {
            $updates['branch_id'] = $branchId;
        }
        if (! $asset->location_id && $locationId) {
            $updates['location_id'] = $locationId;
        }

        if ($updates !== []) {
            $asset->update($updates);
        }
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function validSettingId(string $settingKey, string $modelClass): ?int
    {
        $id = (int) setting($settingKey, 0);

        if ($id <= 0) {
            return null;
        }

        return $modelClass::query()->whereKey($id)->exists() ? $id : null;
    }

    private function resolveAssetTypeId(?int $categoryId, ?int $subCategoryId = null, ?string $itemName = null): ?int
    {
        // 1) Exact category + optional sub-category
        if ($categoryId) {
            $query = AssetType::query()->active()->where('category_id', $categoryId);

            if ($subCategoryId) {
                $match = (clone $query)->where('sub_category_id', $subCategoryId)->value('id');
                if ($match) {
                    return (int) $match;
                }
            }

            $id = $query->orderBy('id')->value('id');
            if ($id) {
                return (int) $id;
            }
        }

        // 2) Name match (item / category-like asset type)
        if (filled($itemName)) {
            $byName = AssetType::query()
                ->active()
                ->where('asset_type_name', 'like', '%'.mb_substr($itemName, 0, 40).'%')
                ->orderBy('id')
                ->value('id');

            if ($byName) {
                return (int) $byName;
            }
        }

        // 3) Any active asset type as soft fallback
        $any = AssetType::query()->active()->orderBy('id')->value('id');

        return $any ? (int) $any : null;
    }

    /**
     * Create a default active asset type for the category when none exist.
     */
    private function ensureDefaultAssetType(?int $categoryId, ?string $assetName = null): ?int
    {
        $existing = $this->resolveAssetTypeId($categoryId, null, $assetName);
        if ($existing) {
            return $existing;
        }

        $typeName = $assetName
            ? mb_substr(trim($assetName).' Type', 0, 100)
            : 'General Asset';

        $type = AssetType::query()->firstOrCreate(
            [
                'asset_type_name' => $typeName,
                'category_id' => $categoryId,
            ],
            [
                'asset_type_description' => 'Auto-created during GRN asset conversion',
                'status' => 'active',
            ]
        );

        if ($type->status !== 'active') {
            $type->update(['status' => 'active']);
        }

        return (int) $type->id;
    }

    private function unitCost(GrnItem $item): float
    {
        if ((float) ($item->poItem?->price ?? 0) > 0 && (int) ($item->poItem?->quantity ?? 0) > 0) {
            return round((float) $item->poItem->price, 3);
        }

        $qty = max(1, (int) ($item->poItem?->quantity ?: 1));
        $line = (float) ($item->poItem?->total ?? $item->poItem?->price ?? 0);

        return round($line / $qty, 3);
    }

    /**
     * @return array{0: ?int, 1: ?int} [category_id, sub_category_id]
     */
    private function resolveCategoryIds($poItem, $indentItem, array $defaults): array
    {
        if (! empty($defaults['category_id']) && ! empty($defaults['sub_category_id'])) {
            return [
                (int) $defaults['category_id'],
                (int) $defaults['sub_category_id'],
            ];
        }

        $catId = ! empty($defaults['category_id']) ? (int) $defaults['category_id'] : ($poItem?->item_category_id ? (int) $poItem->item_category_id : null);
        $subCatId = ! empty($defaults['sub_category_id']) ? (int) $defaults['sub_category_id'] : ($poItem?->item_sub_category_id ? (int) $poItem->item_sub_category_id : null);

        if (! $subCatId) {
            $indentCat = $indentItem?->category ?? ($catId ? \App\Models\AssetCategory::query()->find($catId) : null);
            if ($indentCat?->parent_id) {
                $catId = (int) $indentCat->parent_id;
                $subCatId = (int) $indentCat->id;
            }
        }

        if (! $subCatId && ($poItem?->item_name || $indentItem?->item_name || $poItem?->item_code)) {
            $name = $poItem?->item_name ?? $indentItem?->item_name;
            $code = $poItem?->item_code ?? $indentItem?->item_code;
            $itemMaster = \App\Models\ItemMaster::query()
                ->with(['assetType', 'category.parent'])
                ->when(filled($code), fn ($q) => $q->where('item_code', $code))
                ->when(! filled($code) && filled($name), fn ($q) => $q->where('item_name', $name))
                ->first();

            if ($itemMaster?->assetType?->sub_category_id) {
                $subCatId = (int) $itemMaster->assetType->sub_category_id;
                if (! $catId) {
                    $subCat = \App\Models\AssetCategory::query()->find($subCatId);
                    $catId = $subCat?->parent_id ?: $catId;
                }
            } elseif ($itemMaster?->category?->parent_id) {
                $subCatId = (int) $itemMaster->category->id;
                $catId = (int) $itemMaster->category->parent_id;
            }
        }

        return [$catId, $subCatId];
    }

    private function resolveBrandId(?string $brandName): ?int
    {
        $brandName = trim((string) $brandName);
        if ($brandName === '') {
            return null;
        }

        $existing = Brand::query()
            ->whereRaw('LOWER(brand_name) = ?', [mb_strtolower($brandName)])
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $brand = Brand::query()->create([
            'brand_name' => $brandName,
            'status' => 'active',
        ]);

        return (int) $brand->id;
    }

    private function fallbackSerial(Grn $grn, GrnItem $item, int $unitIndex, ?string $itemCode): string
    {
        $base = filled($itemCode)
            ? preg_replace('/\s+/', '-', trim($itemCode))
            : 'GRN-'.$grn->grn_no.'-'.$item->id;

        return mb_substr($base.'-U'.$unitIndex, 0, 255);
    }

    private function extractWarrantyFromText(?string $text): ?string
    {
        if (filled($text) && preg_match('/(?:warranty:?\s*)([0-9]+\s*(?:year|month|day)s?)/i', (string) $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function ensureAssetWarranty(Asset $asset, Grn $grn, ?string $warrantyInfo, ?Carbon $warrantyExpires, ?User $actor): void
    {
        if (filled($warrantyInfo) && ! $asset->warranties()->exists()) {
            try {
                AssetWarranty::create([
                    'asset_id' => $asset->id,
                    'warranty_type' => 'vendor',
                    'warranty_number' => 'WAR-'.$grn->grn_no.'-'.$asset->id,
                    'warranty_expire_date' => $warrantyExpires,
                    'notes' => 'Warranty extracted from GRN/PO: '.$warrantyInfo,
                    'created_by' => $actor?->id ?? auth()->id(),
                ]);
            } catch (\Throwable $e) {
                // Ignore if asset warranty creation fails due to missing optional constraints
            }
        }
    }

    private function buildInvoiceDetails(Grn $grn): ?string
    {
        $po = $grn->purchaseOrder;
        $vendor = $grn->vendor;
        $parts = array_filter([
            'GRN No: '.$grn->grn_no,
            $po ? 'PO No: '.$po->po_no : null,
            $vendor ? 'Vendor: '.$vendor->name : null,
            filled($grn->invoice_number) ? 'Bill No: '.$grn->invoice_number : null,
            $grn->bill_date ? 'Bill Date: '.$grn->bill_date->format('Y-m-d') : (
                $grn->invoice_date ? 'Bill Date: '.$grn->invoice_date->format('Y-m-d') : null
            ),
            filled($grn->bill_amount) ? 'Bill Amount: '.number_format((float) $grn->bill_amount, 2) : null,
            filled($grn->challan_number) ? 'Challan No: '.$grn->challan_number : null,
            $grn->challan_date ? 'Challan Date: '.$grn->challan_date->format('Y-m-d') : null,
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    private function buildDescription(GrnItem $item, $poItem = null): ?string
    {
        $parts = array_filter([
            filled($item->item_code ?? $poItem?->item_code) ? 'Item Code: '.($item->item_code ?? $poItem?->item_code) : null,
            filled($item->hsn_code ?? $poItem?->hsn_code) ? 'HSN: '.($item->hsn_code ?? $poItem?->hsn_code) : null,
            filled($item->manufacturer ?? $poItem?->manufacturer) ? 'Manufacturer: '.($item->manufacturer ?? $poItem?->manufacturer) : null,
            filled($item->marketer_name ?? $poItem?->marketer_name) ? 'Marketer: '.($item->marketer_name ?? $poItem?->marketer_name) : null,
            filled($item->batch_number) ? 'Batch: '.$item->batch_number : null,
            $item->brand_name ? 'Brand: '.$item->brand_name : null,
            $item->model_name ? 'Model: '.$item->model_name : null,
            $item->accessories ? 'Accessories: '.$item->accessories : null,
            $item->remarks,
            $poItem?->remarks ? 'PO Remarks: '.$poItem->remarks : null,
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    private function isUniqueViolation(QueryException $e, string $indexHint): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate') && str_contains($message, strtolower($indexHint));
    }
}
