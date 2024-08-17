<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Arr;
use Bmatovu\Ussd\Tests\TestCase;

class ArrTest extends TestCase
{
    public function testDetermineAssociativeArray()
    {
        // Sequential arrays
        $arr_1 = ['a', 'b', 'c'];
        $arr_2 = [0 => 'a', 1 => 'b', 2 => 'c'];
        $arr_3 = ['0' => 'a', '1' => 'b', '2' => 'c'];

        // Associative arrays
        $arr_4 = [1 => 'a', 0 => 'b', 2 => 'c'];
        $arr_5 = ['1' => 'a', '0' => 'b', '2' => 'c'];
        $arr_6 = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        self::assertFalse(Arr::isAssoc([]));

        self::assertFalse(Arr::isAssoc($arr_1));
        self::assertFalse(Arr::isAssoc($arr_2));
        self::assertFalse(Arr::isAssoc($arr_3));

        self::assertTrue(Arr::isAssoc($arr_4));
        self::assertTrue(Arr::isAssoc($arr_5));
        self::assertTrue(Arr::isAssoc($arr_6));
    }

    public function testGetMissingKeys()
    {
        $required = ['a', 'b', 'c'];

        $given = ['c', 'd', 'e'];
        $missing = Arr::keysDiff($required, $given);
        self::assertSame($missing, ['a', 'b']);

        $given = ['c' => 3, 'd' => 4, 'e' => 5];
        $missing = Arr::keysDiff($required, $given);
        self::assertSame($missing, ['a', 'b']);
    }
}
