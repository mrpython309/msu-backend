<?php

namespace App\Support;

class AmountInWords
{
    public static function indianRupees(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);
        if ($amount < 0) {
            return 'Minus '.self::indianRupees(abs($amount));
        }

        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $words = self::convertNumber($rupees).' Rupees';
        if ($paise > 0) {
            $words .= ' and '.self::convertNumber($paise).' Paise';
        }

        return $words.' Only';
    }

    private static function convertNumber(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety',
        ];

        $result = '';

        $crore = (int) floor($number / 10000000);
        $number %= 10000000;
        if ($crore > 0) {
            $result .= self::convertNumber($crore).' Crore ';
        }

        $lakh = (int) floor($number / 100000);
        $number %= 100000;
        if ($lakh > 0) {
            $result .= self::convertNumber($lakh).' Lakh ';
        }

        $thousand = (int) floor($number / 1000);
        $number %= 1000;
        if ($thousand > 0) {
            $result .= self::convertNumber($thousand).' Thousand ';
        }

        $hundred = (int) floor($number / 100);
        $number %= 100;
        if ($hundred > 0) {
            $result .= $words[$hundred].' Hundred ';
        }

        if ($number > 0) {
            if ($result !== '') {
                $result .= 'and ';
            }
            if ($number < 21) {
                $result .= $words[$number];
            } else {
                $result .= $words[(int) (floor($number / 10) * 10)];
                if ($number % 10 > 0) {
                    $result .= ' '.$words[$number % 10];
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', $result));
    }
}
