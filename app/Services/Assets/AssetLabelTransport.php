<?php

namespace App\Services\Assets;

interface AssetLabelTransport
{
    public function send(string $commands): void;
}
