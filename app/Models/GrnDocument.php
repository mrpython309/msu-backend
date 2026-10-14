<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnDocument extends Model
{
    protected $fillable = [
        'grn_id',
        'document_type',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
        'remarks',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePhotos($query)
    {
        return $query->where('document_type', 'photo');
    }

    public function typeLabel(): string
    {
        return match ($this->document_type) {
            'invoice' => 'Bill / Invoice',
            'delivery_challan' => 'Delivery Challan',
            'photo' => 'Photo',
            'inspection' => 'Inspection',
            default => ucwords(str_replace('_', ' ', (string) $this->document_type)),
        };
    }

    public function fileName(): string
    {
        return $this->original_name ?: basename((string) $this->path);
    }

    public function humanFileSize(): string
    {
        $bytes = (int) ($this->file_size ?? 0);
        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    }
}
