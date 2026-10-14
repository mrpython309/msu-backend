<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCentre extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_centre_name',
        'cost_centre_description',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'cost_centre', 'cost_centre_name');
    }

    public function hasDependencies(): bool
    {
        return Asset::query()->where('cost_centre', $this->cost_centre_name)->exists();
    }
}
