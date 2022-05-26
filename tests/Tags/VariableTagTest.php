<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\VariableTag;
use Bmatovu\Ussd\Tests\TestCase;

class VariableTagTest extends TestCase
{
    public function testHandleVariable()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $node = $this->getNodeByTagName('<variable name="color" value="blue"/>', 'variable');

        $tag = new VariableTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('blue', $this->cache->get('prefix_color'));
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testProccessVariable()
    {
        $node = $this->getNodeByTagName('<variable name="color" value="blue"/>', 'variable');

        $tag = new VariableTag($node, $this->cache, 'prefix', 30);

        static::assertNull($tag->process(''));
    }
}
