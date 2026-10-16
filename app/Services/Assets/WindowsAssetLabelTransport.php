<?php

namespace App\Services\Assets;

use App\Services\Assets\Exceptions\AssetLabelException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class WindowsAssetLabelTransport implements AssetLabelTransport
{
    public function __construct(private readonly string $printerName)
    {
        if (preg_match('/^[\p{L}\p{N} ._\-()]{1,128}$/u', $this->printerName) !== 1) {
            throw new AssetLabelException('The configured label printer name is not valid.');
        }
    }

    public function send(string $commands): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new AssetLabelException('Windows printer output is only available when the application is running on Windows.');
        }

        $directory = storage_path('app/asset-labels');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new AssetLabelException('The label could not be prepared for the printer.');
        }

        $file = $directory.DIRECTORY_SEPARATOR.'label-'.bin2hex(random_bytes(8)).'.prn';

        try {
            if (file_put_contents($file, $commands) === false) {
                throw new AssetLabelException('The label could not be prepared for the printer.');
            }

            $process = new Process([
                'powershell.exe',
                '-NoProfile',
                '-NonInteractive',
                '-ExecutionPolicy',
                'Bypass',
                '-File',
                resource_path('printer/send-raw-label.ps1'),
                '-PrinterName',
                $this->printerName,
                '-FilePath',
                $file,
            ]);
            $process->setTimeout(20);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::warning('Asset label Windows print failed', [
                    'printer' => $this->printerName,
                    'error' => trim($process->getErrorOutput().' '.$process->getOutput()),
                ]);

                throw new AssetLabelException('Could not send the label to the printer "'.$this->printerName.'".');
            }
        } finally {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
