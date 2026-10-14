<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;

     protected $fillable = [
        'brand_name',
        'brand_description',
        'status'
    ];


      public function scopeActive($query)
      {
            return $query->where('status', 'active');
      }

      public function scopeInactive($query)
      {
            return $query->where('status', 'inactive');
      }

      public function assets(){

          return $this->hasMany(Asset::class,'brand_id','id');
      }

      public function hasDependencies()
      {
      return $this->assets()->exists();
      }
}
