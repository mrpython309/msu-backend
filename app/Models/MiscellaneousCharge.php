<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiscellaneousCharge extends Model
{
    protected $fillable = [
        'charge_name',
        'code',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function msuPurchaseOrders(): HasMany
    {
        return $this->hasMany(MsuPurchaseOrder::class);
    }

    public function toOptionArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->charge_name,
            'code' => $this->code,
            'amount' => (float) $this->amount,
            'active' => $this->status === 'active',
            'label' => $this->code
                ? $this->code.' - '.$this->charge_name
                : $this->charge_name,
        ];
    }
}
