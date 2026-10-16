<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnHistory extends Model
{
    protected $fillable = [
        'grn_id',
        'action',
        'from_status',
        'to_status',
        'user_id',
        'comments',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
