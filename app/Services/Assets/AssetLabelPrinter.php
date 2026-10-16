<?php

namespace App\Services\Assets;

use App\Services\Assets\Exceptions\AssetLabelException;

class AssetLabelPrinter
{
    public function __construct(private readonly TsplAssetLabelBuilder $labels)
    {
    }

    /**
     * @return array{driver: string, target: string}|null
     */
    public function directPrinter(): ?array
    {
        $driver = (string) config('asset_label.driver', 'browser');

        if ($driver === 'network' && filled(config('asset_label.network.host'))) {
            return [
                'driver' => 'network',
                'target' => (string) config('asset_label.network.host').':'.(int) config('asset_label.network.port', 9100),
            ];
        }

        if ($driver === 'windows' && filled(config('asset_label.windows.name'))) {
            return [
                'driver' => 'windows',
                'target' => (string) config('asset_label.windows.name'),
            ];
        }

        return null;
    }

    /**
     * Send one TSPL job. Does not create or change the asset.
     */
    public function send(string $assetNumber, int $copies): string
    {
        $printer = $this->directPrinter();
        if ($printer === null) {
            $driver = (string) config('asset_label.driver', 'browser');
            if ($driver === 'network') {
                throw new AssetLabelException('The label printer address is not configured.');
            }
            if ($driver === 'windows') {
                throw new AssetLabelException('The label printer name is not configured.');
            }

            throw new AssetLabelException('Direct thermal printing is not configured on this server. Use the browser print dialog.');
        }

        $maxCopies = (int) config('asset_label.max_copies', 20);
        $commands = $this->labels->build($assetNumber, $copies, $maxCopies);
        $this->transport($printer)->send($commands);

        $labelWord = $copies === 1 ? 'label' : 'labels';

        return 'Sent '.$copies.' '.$labelWord.' for '.$this->labels->prepareAssetNumber($assetNumber).' to '.$printer['target'].'.';
    }

    /**
     * @param  array{driver: string, target: string}  $printer
     */
    private function transport(array $printer): AssetLabelTransport
    {
        if ($printer['driver'] === 'network') {
            return new NetworkAssetLabelTransport(
                (string) config('asset_label.network.host'),
                (int) config('asset_label.network.port', 9100),
                (int) config('asset_label.network.timeout', 5),
            );
        }

        return new WindowsAssetLabelTransport((string) config('asset_label.windows.name'));
    }
}
