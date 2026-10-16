<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asset label printer
    |--------------------------------------------------------------------------
    |
    | browser  — open the 50 mm label in the browser print dialog.
    | network  — send TSPL to a thermal printer on port 9100.
    | windows  — send TSPL to a local Windows printer by name.
    |
    | A browser cannot deliver raw TSPL to a USB or network printer on its own.
    | Use network or windows only when this server can reach that printer.
    |
    */

    'driver' => env('ASSET_LABEL_PRINTER_DRIVER', 'browser'),

    'max_copies' => 20,

    'network' => [
        'host' => env('ASSET_LABEL_PRINTER_HOST'),
        'port' => (int) env('ASSET_LABEL_PRINTER_PORT', 9100),
        'timeout' => (int) env('ASSET_LABEL_PRINTER_TIMEOUT', 5),
    ],

    'windows' => [
        'name' => env('ASSET_LABEL_PRINTER_NAME'),
    ],

];
