<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\IconTrait;

class SupportTicketCommand extends Model
{
    use HasFactory,IconTrait;

    protected $table = 'support_ticket_commands';

     const FILE_PATH = 'ticket-commands';   

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'ticket_response',
        'recording_duration',
        'voice_note',
        'filename',
        'filenames',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'filenames' => 'array',
    ];

      protected $appends = ['doc_url', 'icon','voicenote_url'];

    /* ===========================
       RELATIONSHIPS
       =========================== */

    public function getDocUrlAttribute()
    {
        $filename = $this->filename ?: ($this->filenames[0] ?? null);

        return $filename ? asset_url_local_s3(self::FILE_PATH.'/'.$filename) : '';
    }

    /** @return array<int, string> */
    public function getAttachmentFilenamesAttribute(): array
    {
        if (is_array($this->filenames) && $this->filenames !== []) {
            return array_values(array_filter($this->filenames));
        }

        return $this->filename ? [$this->filename] : [];
    }

    /** @return array<int, string> */
    public function getAttachmentUrlsAttribute(): array
    {
        return array_map(
            fn (string $filename) => asset_url_local_s3(self::FILE_PATH.'/'.$filename),
            $this->attachment_filenames
        );
    }

    public function getVoicenoteUrlAttribute()
    {
        return !empty($this->voice_note) ? asset_url_local_s3(SupportTicketCommand::FILE_PATH . '/' . $this->voice_note) : '';
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
