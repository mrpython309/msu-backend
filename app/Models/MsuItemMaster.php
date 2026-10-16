<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsuItemMaster extends Model
{
    use SoftDeletes;

    public const FILE_PATH = 'msu-items';

    protected $fillable = [
        'item_code',
        'item_name',
        'product_category_id',
        'product_sub_category_id',
        'expense_type',
        'unit_id',
        'uom',
        'hsn_id',
        'image',
        'status',
        'threshold_quantity',
    ];

    protected $casts = [
        'threshold_quantity' => 'decimal:3',
    ];

    protected $appends = ['image_url', 'current_stock', 'stock_status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Current stock is total inward movements minus total outward movements,
     * matching getCurrentStockAttribute().
     */
    public function scopeWhereStockStatus($query, ?string $status)
    {
        if (! in_array($status, ['out_stock', 'low', 'in_stock'], true)) {
            return $query;
        }

        $stockSql = '(
            COALESCE((SELECT SUM(m.quantity) FROM msu_stock_movements m WHERE m.msu_item_master_id = msu_item_masters.id AND m.transaction_type = \'in\'), 0)
            - COALESCE((SELECT SUM(m.quantity) FROM msu_stock_movements m WHERE m.msu_item_master_id = msu_item_masters.id AND m.transaction_type = \'out\'), 0)
        )';

        if ($status === 'out_stock') {
            return $query->whereRaw($stockSql.' <= 0');
        }

        if ($status === 'low') {
            return $query->whereNotNull('threshold_quantity')
                ->whereRaw($stockSql.' > 0')
                ->whereRaw($stockSql.' <= msu_item_masters.threshold_quantity');
        }

        return $query->whereRaw($stockSql.' > 0')
            ->where(function ($inner) use ($stockSql) {
                $inner->whereNull('threshold_quantity')
                    ->orWhereRaw($stockSql.' > msu_item_masters.threshold_quantity');
            });
    }

    public function createsAssetRecords(): bool
    {
        return true;
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productSubCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function hsn(): BelongsTo
    {
        return $this->belongsTo(Hsn::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(MsuItemRate::class)->orderByDesc('effective_from')->orderByDesc('id');
    }

    public function getImageUrlAttribute(): string
    {
        return ! empty($this->image)
            ? asset_url_local_s3(self::FILE_PATH.'/'.$this->image)
            : '';
    }

    public static $stockCache = null;

    public function getCurrentStockAttribute(): float
    {
        if (self::$stockCache === null) {
            $inSums = MsuStockMovement::where('transaction_type', 'in')
                ->groupBy('msu_item_master_id')
                ->selectRaw('msu_item_master_id, SUM(quantity) as total')
                ->pluck('total', 'msu_item_master_id');
                
            $outSums = MsuStockMovement::where('transaction_type', 'out')
                ->groupBy('msu_item_master_id')
                ->selectRaw('msu_item_master_id, SUM(quantity) as total')
                ->pluck('total', 'msu_item_master_id');
                
            self::$stockCache = [
                'in' => $inSums->toArray(),
                'out' => $outSums->toArray(),
            ];
        }

        $in = (float) (self::$stockCache['in'][$this->id] ?? 0);
        $out = (float) (self::$stockCache['out'][$this->id] ?? 0);
        
        return $in - $out;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function isLowStock(): bool
    {
        if ($this->isOutOfStock()) {
            return false;
        }

        if ($this->threshold_quantity === null || $this->threshold_quantity === '') {
            return false;
        }

        return $this->current_stock <= (float) $this->threshold_quantity;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out Stock';
        }

        return $this->isLowStock() ? 'Low Stock' : 'In Stock';
    }

    public function stockStatusBadgeClass(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }

        return $this->isLowStock() ? 'warning' : 'success';
    }

    /**
     * Unit, price, and GST captured for a stock movement on a given date.
     * Price and GST come from the rate effective on that date, with HSN tax as fallback.
     *
     * @return array{unit: ?string, price: float, total: float, gst_percentage: float, gst_amount: float, grand_total: float}
     */
    public function commercialSnapshot(float|string $quantity, Carbon|string|null $asOf = null): array
    {
        $this->loadMissing(['unit', 'hsn']);

        $rate = $this->applicableRate($asOf);
        $price = round((float) ($rate->rate ?? 0), 2);
        $gstPercentage = ($rate !== null && $rate->tax !== null)
            ? round((float) $rate->tax, 2)
            : round((float) ($this->hsn?->tax_rate ?? 0), 2);
        $qty = round((float) $quantity, 3);
        $total = round($qty * $price, 2);
        $gstAmount = round($total * $gstPercentage / 100, 2);

        return [
            'unit' => $this->unit?->unit_name ?: ($this->uom ?: null),
            'price' => $price,
            'total' => $total,
            'gst_percentage' => $gstPercentage,
            'gst_amount' => $gstAmount,
            'grand_total' => round($total + $gstAmount, 2),
        ];
    }

    public function applicableRate(Carbon|string|null $asOf = null): ?MsuItemRate
    {
        $date = $asOf ? Carbon::parse($asOf)->toDateString() : now()->toDateString();

        return $this->rates()
            ->whereDate('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }

    public static function codePrefixForCategory(ProductCategory $category): string
    {
        if (! empty($category->category_code)) {
            $prefix = strtoupper(preg_replace('/[^a-zA-Z]/', '', (string) $category->category_code) ?? '');
            if (strlen($prefix) >= 3) {
                return substr($prefix, 0, 3);
            }
        }

        return ProductCategory::codeFromName($category->category_name) ?: 'ITM';
    }
}
