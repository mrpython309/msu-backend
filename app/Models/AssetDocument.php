<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IconTrait;

class AssetDocument extends Model
{
    use IconTrait;

    protected $table = 'asset_documents';

    const FILE_PATH = 'asset-docs';   

    protected $fillable = [
        'asset_id',
        'document_name',
        'filename',
        'document_notes',
        'created_by',
        'updated_by',
    ];  

    protected $casts = [
        'created_at'=>'datetime'
    ];

     protected $appends = ['doc_url', 'icon'];

    public function getDocUrlAttribute()
    {
        return !empty($this->filename) ? asset_url_local_s3(AssetDocument::FILE_PATH . '/' . $this->filename) : '';
    }

    public function fileIconClass(): string
    {
        $ext = strtolower(pathinfo((string) $this->filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'bxs-file-pdf text-danger',
            'xls', 'xlsx', 'csv' => 'bxs-spreadsheet text-success',
            'doc', 'docx' => 'bxs-file-doc text-primary',
            default => 'bx-file text-secondary',
        };
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
