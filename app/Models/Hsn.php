<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hsn extends Model
{
    protected $fillable = [
        'hsn_code',
        'description',
        'tax_rate',
        'status',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
