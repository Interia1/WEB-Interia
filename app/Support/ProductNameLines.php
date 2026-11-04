<?php

namespace App\Support;

class ProductNameLines
{
    public static function split(string $name): array
    {
        $lines = [];

        while (mb_strlen($name) > 60) {
            $length = 60;
            $part = mb_substr($name, 0, $length);

            if (mb_substr($name, $length, 1) !== ' ' && mb_substr($part, -1) !== ' ') {
                $space = mb_strrpos($part, ' ');
                if ($space !== false && $space > 0) {
                    $length = $space + 1;
                }
            }

            $lines[] = mb_substr($name, 0, $length);
            $name = mb_substr($name, $length);
        }

        $lines[] = $name;

        return $lines;
    }
}
