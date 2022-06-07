<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\IfTag;
use Bmatovu\Ussd\Tests\TestCase;

class IfTagTest extends TestCase
{
    public function testHandleIf()
    {
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/*[1]');

        $this->store->put('gender', 'Male');

        $xml = <<<'XML'
    <if key="gender" value="Male">
        <dummy/>
    </if>
XML;

        $node = $this->getNodeByTagName($xml, 'if');

        $tag = new IfTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[1]/*[1]', $this->store->get('_exp'));
        static::assertSame('[{"\/*[1]\/*[2]":"\/*[2]"}]', $this->store->get('_breakpoints'));
    }

    public function testHandleSkipIf()
    {
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/*[1]');

        $this->store->put('gender', 'Female');

        $node = $this->getNodeByTagName('<if key="gender" value="Male"></if>', 'if');

        $tag = new IfTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[2]', $this->store->get('_exp'));
        static::assertEmpty($this->store->get('_breakpoints'));
    }
}
