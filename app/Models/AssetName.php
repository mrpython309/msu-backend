<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetName extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_normalized',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (AssetName $assetName): void {
            $assetName->name = trim((string) $assetName->name);
            $assetName->name_normalized = strtolower($assetName->name);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

