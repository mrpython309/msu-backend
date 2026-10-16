<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_issue_id',
        'material_request_item_id',
        'msu_item_master_id',
        'issue_qty',
        'remarks',
    ];

    public function materialIssue(): BelongsTo
    {
        return $this->belongsTo(MaterialIssue::class);
    }

    public function materialRequestItem(): BelongsTo
    {
        return $this->belongsTo(MaterialRequestItem::class);
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }
}
