<?php

namespace App\Services;

use App\Helpers\Files;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class SupportTicketCompletionFileService
{
    public const MAX_FILES = 2;

    public const MAX_FILE_KB = 20480;

    public const ALLOWED_MIMES = 'jpg,jpeg,png,webp,pdf,doc,docx';

    /** @return array<string, mixed> */
    public static function validationRules(bool $api = false): array
    {
        $fileRule = ['file', 'max:'.self::MAX_FILE_KB, 'mimes:'.self::ALLOWED_MIMES];

        if ($api) {
            return [
                'completed_ticket_commands' => 'required',
                'completed_file' => array_merge(['required_without:completed_files'], $fileRule),
                'completed_files' => ['required_without:completed_file', 'array', 'min:1', 'max:'.self::MAX_FILES],
                'completed_files.*' => $fileRule,
            ];
        }

        return [
            'completed_ticket_commands' => 'required',
            'completed_files' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'completed_files.*' => array_merge(['required'], $fileRule),
        ];
    }

    /** @return array<int, UploadedFile> */
    public function filesFromRequest(Request $request): array
    {
        if ($request->hasFile('completed_files')) {
            return array_values(array_filter($request->file('completed_files') ?? []));
        }

        if ($request->hasFile('completed_file') && $request->file('completed_file')->isValid()) {
            return [$request->file('completed_file')];
        }

        return [];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function store(array $files): array
    {
        $stored = [];

        foreach (array_slice($files, 0, self::MAX_FILES) as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $stored[] = Files::uploadCompressedLocalOrS3($file, SupportTicket::FILE_PATH);
            }
        }

        return $stored;
    }

    public function applyToTicket(SupportTicket $ticket, array $storedFiles): void
    {
        $ticket->completed_files = $storedFiles;
        $ticket->completed_file = $storedFiles[0] ?? null;
    }
}
