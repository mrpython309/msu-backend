<?php

namespace App\Services;

use App\Helpers\Files;
use App\Models\SupportTicketCommand;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class SupportTicketReplyFileService
{
    public const MAX_FILES = 2;

    public const MAX_FILE_KB = 20480;

    public const ALLOWED_MIMES = 'jpg,jpeg,png,webp,pdf,doc,docx';

    /** @return array<string, mixed> */
    public static function attachmentValidationRules(bool $api = false): array
    {
        $fileRule = ['file', 'max:'.self::MAX_FILE_KB, 'mimes:'.self::ALLOWED_MIMES];

        if ($api) {
            return [
                'filename' => array_merge(['nullable'], $fileRule),
                'filenames' => ['nullable', 'array', 'max:'.self::MAX_FILES],
                'filenames.*' => $fileRule,
            ];
        }

        return [
            'filenames' => ['nullable', 'array', 'max:'.self::MAX_FILES],
            'filenames.*' => $fileRule,
        ];
    }

    /** @return array<int, UploadedFile> */
    public function filesFromRequest(Request $request): array
    {
        if ($request->hasFile('filenames')) {
            return array_values(array_filter($request->file('filenames') ?? []));
        }

        if ($request->hasFile('filename') && $request->file('filename')->isValid()) {
            return [$request->file('filename')];
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
                $stored[] = Files::uploadCompressedLocalOrS3($file, SupportTicketCommand::FILE_PATH);
            }
        }

        return $stored;
    }

    public function applyToCommand(SupportTicketCommand $command, array $storedFiles): void
    {
        $command->filenames = $storedFiles !== [] ? $storedFiles : null;
        $command->filename = $storedFiles[0] ?? null;
    }
}
