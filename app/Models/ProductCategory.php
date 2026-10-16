<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = [
        'category_name',
        'category_code',
        'status',
    ];

    public static function codeFromName(?string $name): string
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $name) ?? '', 0, 3));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(ProductSubCategory::class);
    }
}
