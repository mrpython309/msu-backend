<?php

namespace App\Services\Assets;

use App\Services\Assets\Exceptions\AssetLabelException;

class TsplAssetLabelBuilder
{
    public const MAX_ASSET_NUMBER_LENGTH = 40;

    /**
     * TSPL job for a 50 mm × 50 mm label with a 3 mm gap.
     * The QR code and the printed text both contain the asset number.
     */
    public function build(string $assetNumber, int $copies = 1, int $maxCopies = 20): string
    {
        $assetNumber = $this->prepareAssetNumber($assetNumber);
        $copies = $this->prepareCopies($copies, $maxCopies);
        $encoded = $this->escape($assetNumber);

        $lines = [
            'SIZE 50 mm, 50 mm',
            'GAP 3 mm, 0 mm',
            'SPEED 3',
            'DENSITY 8',
            'SET RIBBON ON',
            'DIRECTION 0,0',
            'REFERENCE 0,0',
            'OFFSET 0 mm',
            'SET PEEL OFF',
            'SET CUTTER OFF',
            'SET PARTIAL_CUTTER OFF',
            'SET TEAR ON',
            'CLS',
            'CODEPAGE 1252',
            'QRCODE 420,365,L,12,A,180,M2,S7,"'.$encoded.'"',
            'TEXT 439,456,"ROMAN.TTF",180,3,10,"'.$encoded.'"',
            'PRINT '.$copies.',1',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    public function prepareAssetNumber(string $assetNumber): string
    {
        $assetNumber = preg_replace('/\p{C}+/u', '', $assetNumber) ?? '';
        $assetNumber = trim($assetNumber);

        if ($assetNumber === '') {
            throw new AssetLabelException('This asset has no asset code, so a QR label cannot be printed.');
        }

        if (mb_strlen($assetNumber) > self::MAX_ASSET_NUMBER_LENGTH) {
            throw new AssetLabelException('This asset code is too long to print on a 50 mm label.');
        }

        if (! $this->fitsCodePage1252($assetNumber)) {
            throw new AssetLabelException('This asset code contains characters the label printer cannot print.');
        }

        return $assetNumber;
    }

    public function prepareCopies(int $copies, int $maxCopies = 20): int
    {
        if ($copies < 1) {
            throw new AssetLabelException('Print at least one label.');
        }

        if ($copies > $maxCopies) {
            throw new AssetLabelException('Print no more than '.$maxCopies.' labels at a time.');
        }

        return $copies;
    }

    /**
     * Keep quotation marks and backslashes inside the TSPL quoted string.
     */
    private function escape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    private function fitsCodePage1252(string $value): bool
    {
        if (function_exists('iconv')) {
            return @iconv('UTF-8', 'Windows-1252', $value) !== false;
        }

        return preg_match('/^[\x20-\x7E]+$/', $value) === 1;
    }
}
