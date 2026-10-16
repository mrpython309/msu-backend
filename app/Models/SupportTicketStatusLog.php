<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SupportTicketStatusLog extends Model
{
     use HasFactory;

     protected $table = 'support_ticket_status_logs';

     protected $fillable = [
        'support_ticket_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

     protected $appends = [
        'old_status_label',
        'new_status_label',
    ];

    protected $casts = [
        'created_at',
        'updated_at'
    ];

    public function getOldStatusLabelAttribute()
    {
       return match ($this->old_status) {
            'open' => '<span class="badge bg-label-primary">Open</span>',
            'accepted' => '<span class="badge bg-label-info">Accepted</span>',
            'work_in_progress' => '<span class="badge bg-label-warning">Work In Progress</span>',
            'hold' => '<span class="badge bg-label-secondary">On Hold</span>',
            'in_purchase' => '<span class="badge bg-label-dark">In Purchase</span>',
            'completed' => '<span class="badge bg-label-success">Completed</span>',
            'closed' => '<span class="badge bg-label-danger">Closed</span>',
            default => '<span class="badge bg-label-light">Unknown</span>',
        };
    }

    public function getNewStatusLabelAttribute()
    {
        return match ($this->new_status) {
            'open' => '<span class="badge bg-label-primary">Open</span>',
            'accepted' => '<span class="badge bg-label-info">Accepted</span>',
            'work_in_progress' => '<span class="badge bg-label-warning">Work In Progress</span>',
            'hold' => '<span class="badge bg-label-secondary">On Hold</span>',
            'in_purchase' => '<span class="badge bg-label-dark">In Purchase</span>',
            'completed' => '<span class="badge bg-label-success">Completed</span>',
            'closed' => '<span class="badge bg-label-danger">Closed</span>',
            default => '<span class="badge bg-label-light">Unknown</span>',
        };
    }

    /**
     * Relationship: Ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Relationship: User (who changed status)
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
