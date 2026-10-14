<?php

namespace App\Models;

use App\Enums\Procurement\MsuGrnAssetStatus;
use App\Enums\Procurement\MsuGrnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsuGrn extends Model
{
    protected $fillable = [
        'grn_number',
        'receipt_number',
        'msu_purchase_order_id',
        'branch_id',
        'vendor_id',
        'department_id',
        'employee_master_id',
        'vendor_invoice_number',
        'vendor_invoice_date',
        'expense_type',
        'grn_date',
        'status',
        'remarks',
        'miscellaneous_charges',
        'miscellaneous_tax',
        'is_added_to_stock',
        'is_added_to_asset',
        'asset_status',
        'asset_cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'vendor_invoice_date' => 'date',
        'grn_date' => 'date',
        'status' => MsuGrnStatus::class,
        'asset_status' => MsuGrnAssetStatus::class,
        'is_added_to_stock' => 'boolean',
        'is_added_to_asset' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(MsuPurchaseOrder::class, 'msu_purchase_order_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeMaster(): BelongsTo
    {
        return $this->belongsTo(EmployeeMaster::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MsuGrnItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusEnum(): MsuGrnStatus
    {
        if ($this->status instanceof MsuGrnStatus) {
            return $this->status;
        }

        return MsuGrnStatus::tryFrom((string) $this->status) ?? MsuGrnStatus::PartiallyReceived;
    }

    public function assetStatusEnum(): MsuGrnAssetStatus
    {
        if ($this->is_added_to_asset) {
            return MsuGrnAssetStatus::Approved;
        }

        if ($this->asset_status instanceof MsuGrnAssetStatus) {
            return $this->asset_status;
        }

        return MsuGrnAssetStatus::tryFrom((string) $this->asset_status) ?? MsuGrnAssetStatus::Pending;
    }

    public function isCapex(): bool
    {
        return strtolower(trim((string) $this->getAttribute('expense_type'))) === 'capex';
    }

    public function expenseTypeLabel(): string
    {
        return match (strtolower(trim((string) $this->getAttribute('expense_type')))) {
            'capex' => 'Capex',
            'opex' => 'Opex',
            default => '--',
        };
    }

    public function canApproveAsset(): bool
    {
        return $this->isCapex()
            && $this->assetStatusEnum()->canApprove()
            && ! $this->is_added_to_asset;
    }

    public function canCancelAsset(): bool
    {
        return $this->isCapex()
            && $this->assetStatusEnum()->canCancel()
            && ! $this->is_added_to_asset;
    }
}
