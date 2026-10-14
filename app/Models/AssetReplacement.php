<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReplacement extends Model
{
    protected $table = 'asset_replacements';

    protected $fillable = [
        'asset_id',
        'old_serial_no',
        'new_serial_no',
        'old_condition',
        'new_condition',
        'old_operational_status',
        'new_operational_status',
        'replacement_date',
        'replacement_reason',
        'company_id',
        'branch_id',
        'location_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = ['replacement_date' => 'date'];  

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

     public function company(){

        return $this->belongsTo(Company::class,'company_id');
    }

     public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function location()
    {
        return $this->belongsTo(BranchLocation::class, 'location_id');
    }
}
