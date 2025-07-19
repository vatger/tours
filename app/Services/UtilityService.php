<?php

namespace App\Services;

use Illuminate\Support\Str;

class UtilityService
{
    public static function onlyNumbers($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }

    public static function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    public static function roleName($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return Str::lower($string);
    }

    public static function toLower($string)
    {
        return Str::lower($string);
    }

    public static function removeSpecialCharacters($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function changeCharacter($string, $beChange, $newCharacter)
    {
        return str_replace($beChange, $newCharacter, $string);
    }
}
