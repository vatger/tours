<?php

namespace App\Services;

use Illuminate\Support\Str;

class GeneratorService
{
    public static function randomNumber(int $length = 6, string $prefix = '', string $sufix = '')
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $prefix . $randomString . $sufix;
    }


    public static function randomAlphaNumeric($length = 16)
    {
        $randomString = Str::random($length);
        return $randomString;
    }
}
