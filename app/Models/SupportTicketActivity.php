<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketActivity extends Model
{
     protected $fillable = [
        'support_ticket_id',
        'user_id',
        'event',
        'meta',
    ];

     protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

}
