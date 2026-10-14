<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetType extends Model
{
    use HasFactory;

     protected $fillable = [
        'asset_type_name',
        'category_id',
        'sub_category_id',
        'asset_type_description',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(AssetCategory::class,'sub_category_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_type_id');
    }


      public function scopeActive($query)
      {
            return $query->where('status', 'active');
      }

      public function scopeInactive($query)
      {
            return $query->where('status', 'inactive');
      }
}
