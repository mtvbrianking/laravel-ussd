<?php

namespace Bmatovu\Ussd\Tests;

class StoreTest extends TestCase
{
    public function testGetterSetter()
    {
        $this->store->alias = 'jdoe';

        $alias = $this->store->alias;

        static::assertSame('jdoe', $alias);

        // ....

        $this->store->ttl = 60;

        $ttl = $this->store->ttl;

        static::assertSame(60, $ttl);
    }
}
