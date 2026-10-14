<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'department_name',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

  public function users()
    {
        return $this->belongsToMany(User::class, 'department_user');
    }

    public function scopeVisibleToUser($query, $user)
    {
        // Super Admin / Admin → see all departments
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $query;
        }

        $departmentIds = $user->departments()->pluck('departments.id');

        return $query->where(function ($q) use ($departmentIds) {
                    // Asset belongs to user's branch
                    $q->whereIn('id', $departmentIds);
        });
        
    }
}
