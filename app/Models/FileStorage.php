<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IconTrait;

class FileStorage extends Model
{
    use IconTrait;

    protected $table = 'file_storage';

    protected $appends = ['file_url', 'icon', 'size_format'];

    public function getFileUrlAttribute()
    {
        return asset_url($this->path . '/' . $this->filename);
    }

    public function getSizeFormatAttribute()
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        if ($bytes > 1) {
            return $bytes = $bytes . ' bytes';
        }

        if ($bytes == 1) {
            return $bytes = $bytes . ' byte';
        }

        return '0 bytes';

    }
}
