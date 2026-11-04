<?php

namespace Tests\Unit;

use App\Support\ProductNameLines;
use PHPUnit\Framework\TestCase;

class ProductNameLinesTest extends TestCase
{
    public function test_lines_preserve_the_name_and_never_exceed_sixty_characters(): void
    {
        foreach ([
            '',
            str_repeat('á', 59),
            str_repeat('á', 60),
            str_repeat('á', 61),
            str_repeat('á', 120),
            str_repeat('á', 121),
            str_repeat('a', 55).' dlhšie slovo',
            str_repeat(' ', 61).'názov',
        ] as $name) {
            $lines = ProductNameLines::split($name);

            $this->assertSame($name, implode('', $lines));
            foreach ($lines as $line) {
                $this->assertLessThanOrEqual(60, mb_strlen($line));
            }
        }
    }
}
