<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketAssignment extends Model
{
     protected $fillable = [
        'support_ticket_id',
        'user_id',
        'is_active',
        'released_at',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
       'created_at' => 'datetime',
        'updated_at' => 'datetime', 
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
