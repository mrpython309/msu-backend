<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
      use HasFactory;

       protected $fillable = [
            'name', 'code', 'company_id', 'address', 'city', 'state', 'country',
            'contact_number', 'email', 'gst_number', 'contact_person', 'status',
        ];

        protected $casts = [
            'status' => 'string',
        ];

        public function company()
        {
            return $this->belongsTo(Company::class);
        }

        public function assets(){

            return $this->hasMany(Asset::class,'branch_id','id');
        }

        public function assetAssignment(){

            return $this->hasMany(AssetAssignment::class,'to_branch','id');
        }

        public function scopeActive($query)
        {
            return $query->where('status', 'active');
        }

        public function locations()
        {
            return $this->hasMany(BranchLocation::class);
        }

        public function users()
        {
            return $this->belongsToMany(User::class)->withTimestamps();
        }

        public function scopeVisibleToUser($query, $user)
        {
            // Super Admin / Admin → every branch
            if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
                return $query;
            }

            $companyIds = $user->companies()->pluck('companies.id');
            $branchIds = $user->branches()->pluck('branches.id');

            if ($companyIds->isEmpty() && $branchIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function ($q) use ($companyIds, $branchIds) {
                // Assigned company → all branches of that company
                if ($companyIds->isNotEmpty()) {
                    $q->whereIn('company_id', $companyIds);
                }

                // Also allow directly assigned branches (e.g. no company link yet)
                if ($branchIds->isNotEmpty()) {
                    $method = $companyIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $q->{$method}('id', $branchIds);
                }
            });
        }

        public function hasDependencies()
        {
            return $this->assets()->exists()
                || $this->assetAssignment()->exists()
                || $this->locations()->exists()
                || $this->users()->exists();
        }
}
