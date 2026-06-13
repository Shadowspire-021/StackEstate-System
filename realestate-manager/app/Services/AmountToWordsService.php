<?php

namespace App\Services;

class AmountToWordsService
{
    public static function convert($number)
    {
        $number = floor($number);
        if ($number == 0) {
            return 'Zero Only';
        }

        $words = array(
            0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );

        $result = '';

        // Crores (10,000,000+)
        if ($number >= 10000000) {
            $crore = floor($number / 10000000);
            $result .= self::convertGroup($crore, $words) . ' Crore ';
            $number %= 10000000;
        }

        // Lacs (100,000 - 9,999,999)
        if ($number >= 100000) {
            $lac = floor($number / 100000);
            $result .= self::convertGroup($lac, $words) . ' Lac ';
            $number %= 100000;
        }

        // Thousands (1,000 - 99,999)
        if ($number >= 1000) {
            $thousand = floor($number / 1000);
            $result .= self::convertGroup($thousand, $words) . ' Thousand ';
            $number %= 1000;
        }

        // Hundreds (100 - 999)
        if ($number >= 100) {
            $hundred = floor($number / 100);
            $result .= $words[$hundred] . ' Hundred ';
            $number %= 100;
        }

        // Remaining Tens/Ones (1 - 99)
        if ($number > 0) {
            $result .= self::convertGroup($number, $words);
        }

        return trim(preg_replace('/\s+/', ' ', $result)) . ' Only';
    }

    private static function convertGroup($number, $words)
    {
        if ($number < 20) {
            return $words[$number];
        }

        $tens = floor($number / 10) * 10;
        $ones = $number % 10;

        return trim($words[$tens] . ' ' . $words[$ones]);
    }
}
