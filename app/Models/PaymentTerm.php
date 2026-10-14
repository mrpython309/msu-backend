<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTerm extends Model
{
    protected $fillable = [
        'term_name',
        'payment_mode',
        'code',
        'description',
        'status',
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
            'description' => $this->term_name,
            'payment_mode' => $this->payment_mode,
            'code' => $this->code,
            'active' => $this->status === 'active',
            'label' => trim(
                ($this->payment_mode ? $this->payment_mode.' - ' : '')
                .($this->code ? $this->code.' - ' : '')
                .$this->term_name
            ),
        ];
    }
}
