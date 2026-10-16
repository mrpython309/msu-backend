<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerLocation extends Model
{
  use HasFactory;

  protected $fillable = [
    'name', 'code', 'company_id', 'address', 'city', 'state', 'country', 'contact_number', 'email', 'status'
  ];

  protected $casts = [
    'status' => 'string',
  ];

  public function company()
  {
    return $this->belongsTo(Company::class);
  }

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }
}
