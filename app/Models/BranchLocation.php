<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchLocation extends Model
{
     use HasFactory;

     protected $fillable = [
        'location_name',
        'branch_id',
        'status'
    ];

     public function scopeActive($query)
        {
            return $query->where('status', 'active');
        }

        public function branch()
        {
            return $this->belongsTo(Branch::class);
        }

        public function assets(){

            return $this->hasMany(Asset::class,'location_id','id');
        }

        public function assetAssignment(){

            return $this->hasMany(AssetAssignment::class,'to_location','id');
        }

        public function hasDependencies()
        {
            return $this->assets()->exists()
                || $this->assetAssignment()->exists();
        }


}
