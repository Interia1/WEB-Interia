<?php

namespace App\Support;

class CartName
{
    public static function forButton(string $name): string
    {
        if (preg_match_all('/\X/u', $name, $characters) === false) {
            throw new \LogicException('Názov košíka nie je platný text UTF-8.');
        }

        return count($characters[0]) > 15
            ? implode('', array_slice($characters[0], 0, 15)).'…'
            : $name;
    }
}
