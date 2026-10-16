<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\WarrantyType;


class AssetWarranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'covered_parts',
        'warranty_type',
        'warranty_number',
        'warranty_expire_date',
        'usage_metric',
        'max_usage',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($asset) {
            $asset->created_by = Auth::id();
        });

        static::updating(function ($asset) {
            $asset->updated_by = Auth::id();
        });
    }

     protected $casts = [
        'warranty_type' => WarrantyType::class,
        'warranty_expire_date' => 'date',
    ];

    protected $appends = [
        'status_label'
    ];

    public function getStatusLabelAttribute()
    {
        return $this->isExpired() ? '<span class="badge bg-danger">Expired</span>' : '<span class="badge bg-success">Active</span>';
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isExpired(): bool
    {
        if ($this->warranty_expire_date) {
            return Carbon::today()->gt($this->warranty_expire_date);
        }

        return false;
    }


}
