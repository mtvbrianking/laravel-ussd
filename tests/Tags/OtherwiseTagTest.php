<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OtherwiseTag;
use Bmatovu\Ussd\Tests\TestCase;

class OtherwiseTagTest extends TestCase
{
    public function testHandleOtherwise()
    {
        $this->cache->put('prefix_pre', '/*[1]');
        $this->cache->put('prefix_exp', '/*[1]/*[1]');
        $this->cache->put('prefix_breakpoints', '[{"break":"resume"}]');

        $xml = <<<'XML'
<choice>
    <otherwise>
        <response text="Lorem ipsum"/>
    </otherwise>
</choice>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new OtherwiseTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[1]/*[1]', $this->cache->get('prefix_exp'));
        static::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->cache->get('prefix_breakpoints'));
    }
}
