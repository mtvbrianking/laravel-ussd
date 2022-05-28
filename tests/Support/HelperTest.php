<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Helper;
use Bmatovu\Ussd\Tests\TestCase;

class HelperTest extends TestCase
{
    public function testTranslate()
    {
        $this->cache->put('prefix_alias', 'jdoe');

        $text = Helper::translate($this->cache, 'prefix', 'Username: {alias}');

        static::assertSame('Username: jdoe', $text);
    }

    public function testTranslateNoMatches()
    {
        // $this->cache->put('prefix_alias', 'jdoe');

        $text = Helper::translate($this->cache, 'prefix', 'Username: alias');

        static::assertSame('Username: alias', $text);

        $text = Helper::translate($this->cache, 'prefix', 'Username: {alias}');

        static::assertSame('Username: {alias}', $text);
    }
}
