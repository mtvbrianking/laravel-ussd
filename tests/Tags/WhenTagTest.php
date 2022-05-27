<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\WhenTag;
use Bmatovu\Ussd\Tests\TestCase;

class WhenTagTest extends TestCase
{
    public function testHandleWhen()
    {
        $this->cache->put('prefix_pre', '/*[1]');
        $this->cache->put('prefix_exp', '/*[1]/*[1]');
        $this->cache->put('prefix_breakpoints', '[{"break":"resume"}]');

        $this->cache->put('prefix_gender', 'Male');

        $xml = <<<'XML'
<choice>
    <when key="gender" value="Male">
        <response text="Lorem ipsum"/>
    </when>
</choice>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new WhenTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[1]/*[1]', $this->cache->get('prefix_exp'));
        static::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->cache->get('prefix_breakpoints'));
    }
}
