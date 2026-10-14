<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'status'];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Status helper attributes
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'bg-label-success' : 'bg-label-danger';
    }

    public function subsegments()
    {
        return $this->hasMany(SubSegment::class);
    }
    
}
