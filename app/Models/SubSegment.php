<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubSegment extends Model
{
    
    protected $fillable = [
        'segment_id',
        'subsegment_name',
        'status'
    ];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

}
