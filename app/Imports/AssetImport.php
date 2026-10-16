<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\AssetImport as AssetImportModel;
use App\AssetCondition;
use App\AssetOperationalStatus;
use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Brand;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Asset;

class AssetImport implements ToCollection, WithChunkReading
{
    protected $operationalStatuses = [];
    protected $conditions = [];
    protected $vendors = [];
    protected $brands = [];
    protected $depreciationMethods = [];
    protected $assetStructures = [];
    protected $categories = [];
    protected $subCategories = [];
    protected $assetTypes = [];

    protected $existingSerials = [];
    protected $fileSerials = [];
    protected $existingAssetCodes = [];
    protected $fileAssetCodes = [];

    public function __construct()
    {
        $this->categories = AssetCategory::active()
            ->whereNull('parent_id')
            ->get()
            ->mapWithKeys(fn($cat) => [$this->normalize($cat->category_name) => $cat->id])
            ->toArray();

        $this->subCategories = AssetCategory::active()
            ->whereNotNull('parent_id')
            ->get()
            ->mapWithKeys(fn($cat) => [
                $this->normalize($cat->category_name) => [
                    'id' => $cat->id,
                    'parent_id' => $cat->parent_id
                ]
            ])->toArray();

        $this->assetTypes = AssetType::active()
            ->get()
            ->mapWithKeys(fn($type) => [
                $this->normalize($type->asset_type_name) => [
                    'id' => $type->id,
                    'category_id' => $type->category_id,
                    'sub_category_id' => $type->sub_category_id
                ]
            ])->toArray();

        $this->operationalStatuses = collect(AssetOperationalStatus::cases())
            ->flatMap(fn($case) => [
                $this->normalize($case->label()) => $case->value,
                $this->normalize($case->value) => $case->value
            ])
            ->merge([
                // Legacy spreadsheet labels → current enum values
                $this->normalize('Repair') => AssetOperationalStatus::UNDER_MAINTENANCE->value,
                $this->normalize('repair') => AssetOperationalStatus::UNDER_MAINTENANCE->value,
                $this->normalize('Not Working') => AssetOperationalStatus::DAMAGED->value,
                $this->normalize('not_working') => AssetOperationalStatus::DAMAGED->value,
                $this->normalize('Retired') => AssetOperationalStatus::DISPOSED->value,
                $this->normalize('retired') => AssetOperationalStatus::DISPOSED->value,
            ])
            ->toArray();

        $this->conditions = collect(AssetCondition::cases())
            ->flatMap(fn($case) => [
                $this->normalize($case->label()) => $case->value,
                $this->normalize($case->value) => $case->value
            ])->toArray();

        $this->vendors = Vendor::active()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [$this->normalize($name) => $id])
            ->toArray();

        $this->brands = Brand::active()
            ->pluck('id', 'brand_name')
            ->mapWithKeys(fn($id, $name) => [$this->normalize($name) => $id])
            ->toArray();

        $this->depreciationMethods = [
            'no depreciation' => 'no_depreciation',
            'reducing balance' => 'reducing_balance',
            'no_depreciation' => 'no_depreciation',
            'reducing_balance' => 'reducing_balance',
        ];

        $this->assetStructures = [
            'single asset' => 'single',
            'group asset'  => 'group',
            'single' => 'single',
            'group'  => 'group',
        ];

        $this->existingSerials = array_flip(
            Asset::pluck('serial_no')
                ->map(fn($s) => $this->normalize($s))
                ->toArray()
        );

        $this->existingAssetCodes = array_flip(
            Asset::pluck('asset_code')
                ->map(fn($s) => $this->normalize($s))
                ->toArray()
        );
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function normalize($value)
    {
        return strtolower(trim($value ?? ''));
    }

    private function getData($data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? ($data[$key] ?? $default) : $default;
    }

    public function collection(Collection $rows)
    {
        $header = [];
        $insertData = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) {
                $header = array_map(function ($h) {
                    $h = strtolower(trim($h));
                    $h = str_replace([' ', '-'], '_', $h);
                    return $h;
                }, $row->toArray());
                continue;
            }

            $data = array_combine($header, $row->toArray());
            $originalData = $data; // ✅ keep original
            $errors = [];

            // Normalize inputs
            $categoryInput = $this->normalize($this->getData($data, 'category', ''));
            $subCategoryInput = $this->normalize($this->getData($data, 'sub_category', ''));
            $assetTypeInput = $this->normalize($this->getData($data, 'asset_type', ''));
            $structureInput = $this->normalize($this->getData($data, 'asset_structure', ''));
            $operationalInput = $this->normalize($this->getData($data, 'operational_status', ''));
            $conditionInput = $this->normalize($this->getData($data, 'condition', ''));
            $supplierInput = $this->normalize($this->getData($data, 'supplier', ''));
            $brandInput = $this->normalize($this->getData($data, 'brand', ''));
            $methodInput = $this->normalize($this->getData($data, 'depreciation_method', ''));
            $serialInput = $this->normalize($this->getData($data, 'serial_no', ''));
            $assetCodeInput = $this->normalize($this->getData($data, 'asset_code', ''));

            // Resolve IDs
            $categoryId = $this->categories[$categoryInput] ?? null;
            $subCategory = $this->subCategories[$subCategoryInput] ?? null;
            $subCategoryId = $subCategory['id'] ?? null;
            $assetType = $this->assetTypes[$assetTypeInput] ?? null;

            // VALIDATIONS (same as yours)
            if (!$categoryId) $errors[] = 'Invalid Category';
            if (!$subCategory) $errors[] = 'Invalid Sub Category';
            if ($categoryId && $subCategory && $subCategory['parent_id'] != $categoryId)
                $errors[] = 'Sub Category mismatch';

            if (!$assetType) $errors[] = 'Invalid Asset Type';
            if (empty($this->getData($data, 'asset_name'))) $errors[] = 'Asset Name required';

            // Asset Code validation
            if (!$assetCodeInput) {
                $errors[] = 'Asset Code required';
            } else {
                if (isset($this->fileAssetCodes[$assetCodeInput])) {
                    $errors[] = 'Duplicate Asset Code in file';
                } else {
                    $this->fileAssetCodes[$assetCodeInput] = true;
                }

                if (isset($this->existingAssetCodes[$assetCodeInput])) {
                    $errors[] = 'Asset Code already exists';
                }
            }

            // Serial
            if (!$serialInput) {
                $errors[] = 'Serial required';
            } else {
                if (isset($this->fileSerials[$serialInput])) {
                    $errors[] = 'Duplicate Serial in file';
                } else {
                    $this->fileSerials[$serialInput] = true;
                }

                if (isset($this->existingSerials[$serialInput])) {
                    $errors[] = 'Serial exists';
                }
            }

            // Structure
            $assetStructure = $this->assetStructures[$structureInput] ?? null;
            if (!$assetStructure) $errors[] = 'Invalid Asset Structure';

            // Enums
            if (!isset($this->operationalStatuses[$operationalInput])) {
                $errors[] = 'Invalid Operational Status';
            }

            if (!isset($this->conditions[$conditionInput])) {
                $errors[] = 'Invalid Condition';
            }

            if (!isset($this->depreciationMethods[$methodInput])) {
                $errors[] = 'Invalid Depreciation Method';
            }

            // Date
            try {
                $purchaseDate = is_numeric($this->getData($data, 'purchase_date'))
                    ? ExcelDate::excelToDateTimeObject($this->getData($data, 'purchase_date'))
                    : Carbon::createFromFormat('d-M-Y', $this->getData($data, 'purchase_date'));
            } catch (\Exception $e) {
                $errors[] = 'Invalid Purchase Date';
                $purchaseDate = null;
            }

            // Cost
            $costInput = preg_replace('/[^\d.]/', '', str_replace(',', '', $this->getData($data, 'purchase_cost', '')));

            if ($costInput === '' || !is_numeric($costInput)) {
                $errors[] = 'Invalid Cost';
            }

            // Insert
            $insertData[] = [
                'data' => json_encode(array_merge(

                    // ORIGINAL
                    collect($originalData)->mapWithKeys(fn($v, $k) => [$k . '_original' => $v])->toArray(),

                    // PROCESSED
                    [
                        'asset_name' => $this->getData($data, 'asset_name', ''),
                        'asset_code' => $this->getData($data, 'asset_code', ''),
                        'serial_no' => $this->getData($data, 'serial_no', ''),

                        'category_id' => $categoryId,
                        'sub_category_id' => $subCategoryId,
                        'asset_type_id' => $assetType['id'] ?? null,

                        'vendor_id' => $this->vendors[$supplierInput] ?? null,
                        'brand_id' => $this->brands[$brandInput] ?? null,

                        'asset_structure' => $assetStructure,
                        'operational_status' => $this->operationalStatuses[$operationalInput] ?? null,
                        'condition' => $this->conditions[$conditionInput] ?? null,
                        'depreciation_method' => $this->depreciationMethods[$methodInput] ?? null,

                        'purchase_cost' => (float) $costInput,
                        'purchase_date' => $purchaseDate?->format('Y-m-d'),
                        'depreciation_rate' => $this->getData($data, 'salvage_value'),
                        'items_count' => $this->getData($data, 'items_count', 1),
                    ]

                )),

                'errors' => json_encode($errors),
                'is_valid' => empty($errors),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            AssetImportModel::insert($insertData);
        }
    }
}
