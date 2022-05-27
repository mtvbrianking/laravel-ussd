<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OptionTag;
use Bmatovu\Ussd\Tests\TestCase;

class OptionTagTest extends TestCase
{
    public function testHandleOption()
    {
        $this->cache->put('prefix_pre', '/*[1]');
        $this->cache->put('prefix_exp', '/*[1]/*[1]');
        $this->cache->put('prefix_breakpoints', '[{"break":"resume"}]');

        $xml = <<<XML
<options header="Choose Gender: ">
    <option text="Male">
        <response text="Lorem ipsum"/>
    </option>
</options>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new OptionTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[1]/*[1]', $this->cache->get('prefix_exp'));
        static::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->cache->get('prefix_breakpoints'));
    }
}
