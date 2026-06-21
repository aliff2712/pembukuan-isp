<?php

namespace App\Helpers;

class NumberToWordsHelper
{
    /**
     * Convert number to Indonesian words (terbilang)
     * 
     * @param float|int $number
     * @return string
     */
    public static function convert($number)
    {
        $number = abs($number);
        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 
            'sepuluh', 'sebelas'
        ];

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return self::convert($number - 10) . ' belas';
        } elseif ($number < 100) {
            return self::convert((int)($number / 10)) . ' puluh ' . self::convert($number % 10);
        } elseif ($number < 200) {
            return 'seratus ' . self::convert($number - 100);
        } elseif ($number < 1000) {
            return self::convert((int)($number / 100)) . ' ratus ' . self::convert($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . self::convert($number - 1000);
        } elseif ($number < 1000000) {
            return self::convert((int)($number / 1000)) . ' ribu ' . self::convert($number % 1000);
        } elseif ($number < 1000000000) {
            return self::convert((int)($number / 1000000)) . ' juta ' . self::convert($number % 1000000);
        } elseif ($number < 1000000000000) {
            return self::convert((int)($number / 1000000000)) . ' miliar ' . self::convert($number % 1000000000);
        } elseif ($number < 1000000000000000) {
            return self::convert((int)($number / 1000000000000)) . ' triliun ' . self::convert($number % 1000000000000);
        }

        return 'angka terlalu besar';
    }

    /**
     * Convert number to words with currency
     * 
     * @param float|int $number
     * @param string $currency
     * @return string
     */
    public static function convertWithCurrency($number, $currency = 'rupiah')
    {
        return ucwords(self::convert($number)) . ' ' . ucfirst($currency);
    }
}