<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Arr;
use Bmatovu\Ussd\Tests\TestCase;

class ArrTest extends TestCase
{
    public function test_determine_associative_array()
    {
        // Sequential arrays
        $arr_1 = ['a', 'b', 'c'];
        $arr_2 = [0 => 'a', 1 => 'b', 2 => 'c'];
        $arr_3 = ['0' => 'a', '1' => 'b', '2' => 'c'];

        // Associative arrays
        $arr_4 = [1 => 'a', 0 => 'b', 2 => 'c'];
        $arr_5 = ['1' => 'a', '0' => 'b', '2' => 'c'];
        $arr_6 = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $this->assertFalse(Arr::isAssoc([]));

        $this->assertFalse(Arr::isAssoc($arr_1));
        $this->assertFalse(Arr::isAssoc($arr_2));
        $this->assertFalse(Arr::isAssoc($arr_3));

        $this->assertTrue(Arr::isAssoc($arr_4));
        $this->assertTrue(Arr::isAssoc($arr_5));
        $this->assertTrue(Arr::isAssoc($arr_6));
    }

    public function test_get_missing_keys()
    {
        $required = ['a', 'b', 'c'];

        $given = ['c', 'd', 'e'];
        $missing = Arr::keysDiff($required, $given);
        $this->assertEquals($missing, ['a', 'b']);

        $given = ['c' => 3, 'd' => 4, 'e' => 5];
        $missing = Arr::keysDiff($required, $given);
        $this->assertEquals($missing, ['a', 'b']);
    }
}
