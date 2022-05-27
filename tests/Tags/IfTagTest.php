<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\IfTag;
use Bmatovu\Ussd\Tests\TestCase;

class IfTagTest extends TestCase
{
    public function testHandleIf()
    {
        $this->cache->put('prefix_pre', '');
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_gender', 'Male');

        $xml = <<<XML
    <if key="gender" value="Male">
        <dummy/>
    </if>
XML;

        $node = $this->getNodeByTagName($xml, 'if');

        $tag = new IfTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[1]/*[1]', $this->cache->get('prefix_exp'));
        static::assertSame('[{"\/*[1]\/*[2]":"\/*[2]"}]', $this->cache->get('prefix_breakpoints'));
    }

    public function testHandleSkipIf()
    {
        $this->cache->put('prefix_pre', '');
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_gender', 'Female');

        $node = $this->getNodeByTagName('<if key="gender" value="Male"></if>', 'if');

        $tag = new IfTag($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
        static::assertEmpty($this->cache->get('prefix_breakpoints'));
    }
}
