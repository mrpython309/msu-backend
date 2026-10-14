<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCustodian extends Model
{
    protected $fillable = [
        'employee_name',
        'department_id',
        'mobile_number',
        'email',
        'status',
    ];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['employee_name'] ?? '');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'custodian_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'assiged_emoloyee');
    }
}
