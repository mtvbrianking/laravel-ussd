<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\WhenTag;
use Bmatovu\Ussd\Tests\TestCase;

class WhenTagTest extends TestCase
{
    public function testHandleWhen()
    {
        $this->store->put('_pre', '/*[1]');
        $this->store->put('_exp', '/*[1]/*[1]');
        $this->store->put('_breakpoints', '[{"break":"resume"}]');

        $this->store->put('gender', 'Male');

        $xml = <<<'XML'
<choice>
    <when key="gender" value="Male">
        <response text="Lorem ipsum"/>
    </when>
</choice>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new WhenTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[1]/*[1]/*[1]', $this->store->get('_exp'));
        static::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->store->get('_breakpoints'));
    }
}
