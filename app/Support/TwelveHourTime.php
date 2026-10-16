<?php

namespace App\Support;

use Carbon\CarbonInterface;

class TwelveHourTime
{
    /**
     * @return array{hour: int, minute: int, ampm: string}
     */
    public static function fromCarbon(?CarbonInterface $dt): array
    {
        if ($dt === null) {
            return ['hour' => 9, 'minute' => 0, 'ampm' => 'am'];
        }

        $dt = $dt->copy()->timezone(config('app.timezone'));
        $h24 = (int) $dt->format('H');
        $m = (int) $dt->format('i');
        $ampm = $h24 >= 12 ? 'pm' : 'am';
        $h12 = $h24 % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }

        return ['hour' => $h12, 'minute' => $m, 'ampm' => $ampm];
    }

    public static function formatLabel(?CarbonInterface $dt): string
    {
        if ($dt === null) {
            return '—';
        }
        $dt = $dt->copy()->timezone(config('app.timezone'));

        return strtolower($dt->format('g:ia')); // e.g. 10:30am
    }

    /**
     * @param  'am'|'pm'  $ampm
     */
    public static function to24h(int $hour12, int $minute, string $ampm): string
    {
        $ampm = strtolower($ampm) === 'pm' ? 'pm' : 'am';
        $hour12 = max(1, min(12, $hour12));
        $minute = max(0, min(59, $minute));

        if ($ampm === 'am') {
            $H = $hour12 === 12 ? 0 : $hour12;
        } else {
            $H = $hour12 === 12 ? 12 : $hour12 + 12;
        }

        return sprintf('%02d:%02d', $H, $minute);
    }
}
