<?php

use App\Helpers\Files;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


if (!function_exists('asset_url')) {

    // @codingStandardsIgnoreLine
    function asset_url($path)
    {
        $path = \App\Helpers\Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;
    
        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }
    
        return $storageUrl;
    }
    
    }



if (!function_exists('asset_url_local_s3')) {

    // @codingStandardsIgnoreLine
    function asset_url_local_s3($path)
    {
        if (config('filesystems.default') == 's3') {

            // Added  \Illuminate\Filesystem\AwsS3V3Adapter::macro('getClient', fn() => $this->client);
            // in AppServiceProvider as workaround
            /** @phpstan-ignore-next-line */
            $client = Storage::disk('s3')->getClient();

            $command = $client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $path
            ]);

            $request = $client->createPresignedRequest($command, '+30 minutes');

            return (string)$request->getUri();
        }

        $path = Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;
    }

}


if(!function_exists('setting')) {
    function setting($key, $default = null) {
        return cache()->rememberForever(
            "setting.$key",
            fn () => DB::table('settings')->where('key', $key)->value('value') ?? $default
        );
    }
}

if (! function_exists('asset_show_section_url')) {
    function asset_show_section_url($asset, string $tab): string
    {
        $assetId = $asset instanceof \App\Models\Asset ? $asset->id : (int) $asset;

        return route('asset.show', ['asset' => $assetId, 'tab' => $tab]).'#'.$tab.'-section';
    }
}

if (! function_exists('asset_section_action_url')) {
    /** URL for assignment/document/etc. sub-pages — keeps scroll at the tab section. */
    function asset_section_action_url(string $routeName, mixed $parameters, string $tab): string
    {
        return route($routeName, $parameters).'#'.$tab.'-section';
    }
}

if (! function_exists('redirect_to_asset_show_section')) {
    function redirect_to_asset_show_section($asset, string $tab): \Illuminate\Http\RedirectResponse
    {
        return redirect()
            ->to(asset_show_section_url($asset, $tab))
            ->with('scroll_asset_section', $tab);
    }
}
 
