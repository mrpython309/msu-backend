<?php

namespace App\Models;

use App\Services\CompanyLogoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Company extends Model
{
  use HasFactory;

  protected $fillable = [
    'name', 'code', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'pincode', 'contact_person',
    'contact_number', 'email', 'logo', 'status'
  ];

  protected $casts = [
    'status' => 'string',
  ];

  protected $appends = ['logo_url'];

  public function branches()
  {
    return $this->hasMany(Branch::class);
  }

  public function assets(){

    return $this->hasMany(Asset::class,'company_id','id');
  }

  public function assetAssignment(){

     return $this->hasMany(AssetAssignment::class,'to_company_id','id');
  }

  public function dealerLocations()
  {
    return $this->hasMany(DealerLocation::class);
  }

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  public function scopeVisibleToUser($query, $user)
  {
        // Super Admin / Admin → every company
       if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
        return $query;
       }

      $companyIds = $user->companies()->pluck('companies.id');

      if ($companyIds->isEmpty()) {
          return $query->whereRaw('1 = 0');
      }

      return $query->whereIn('id', $companyIds);
  }

  public function users(){

    return $this->belongsToMany(User::class)->withTimestamps();
  }

  public function hasDependencies()
  {
      return $this->branches()->exists()
          || $this->assets()->exists()
          || $this->assetAssignment()->exists()
          || $this->users()->exists();
  }

  public function getLogoUrlAttribute(): ?string
  {
      if (empty($this->logo)) {
          return null;
      }

      return asset_url_local_s3(CompanyLogoService::LOGO_PATH.'/'.$this->logo);
  }


}
