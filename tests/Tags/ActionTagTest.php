<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\ActionTag;
use Bmatovu\Ussd\Tests\TestCase;

class ActionTagTest extends TestCase
{
    public function testHandleAction()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_amount', '12500');

        $node = $this->getNodeByTagName('<action name="format-money" currency="USD"/>', 'action');

        $tag = new ActionTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('USD 12,500', $this->cache->get('prefix_amount'));
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }
}
