<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsuStockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'adjustment_type',
        'employee_master_id',
        'department_id',
        'msu_item_master_id',
        'quantity',
        'unit',
        'price',
        'total',
        'gst_percentage',
        'gst_amount',
        'grand_total',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeMaster::class, 'employee_master_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::creating(function ($adjustment) {
            if (empty($adjustment->adjustment_number)) {
                $prefix = 'ADJ/' . date('Y/m/');
                
                $lastAdjustment = self::where('adjustment_number', 'LIKE', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastAdjustment) {
                    $lastNumberStr = substr($lastAdjustment->adjustment_number, strlen($prefix));
                    if (is_numeric($lastNumberStr)) {
                        $nextNumber = intval($lastNumberStr) + 1;
                    }
                }
                
                $adjustment->adjustment_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
