<?php

namespace App\Services\Assets;

use App\Services\Assets\Exceptions\AssetLabelException;

class NetworkAssetLabelTransport implements AssetLabelTransport
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly int $timeout,
    ) {
        if ($this->host === '' || preg_match('/^[A-Za-z0-9.\-]+$/', $this->host) !== 1) {
            throw new AssetLabelException('The label printer address is not valid.');
        }

        if ($this->port < 1 || $this->port > 65535) {
            throw new AssetLabelException('The label printer port is not valid.');
        }

        if ($this->timeout < 1 || $this->timeout > 30) {
            throw new AssetLabelException('The label printer timeout is not valid.');
        }
    }

    public function send(string $commands): void
    {
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if ($socket === false) {
            throw new AssetLabelException(
                'Could not reach the label printer at '.$this->host.':'.$this->port.'.'
            );
        }

        stream_set_timeout($socket, $this->timeout);

        $offset = 0;
        $length = strlen($commands);

        try {
            while ($offset < $length) {
                $written = fwrite($socket, substr($commands, $offset));
                if ($written === false || $written === 0) {
                    throw new AssetLabelException('The label printer did not accept the print job.');
                }
                $offset += $written;
            }
        } finally {
            fclose($socket);
        }
    }
}
