<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OtherwiseTag;
use Bmatovu\Ussd\Tests\TestCase;

class OtherwiseTagTest extends TestCase
{
    public function testHandleOtherwise()
    {
        $this->store->put('_pre', '/*[1]');
        $this->store->put('_exp', '/*[1]/*[1]');
        $this->store->put('_breakpoints', '[{"break":"resume"}]');

        $xml = <<<'XML'
<choice>
    <otherwise>
        <response text="Lorem ipsum"/>
    </otherwise>
</choice>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new OtherwiseTag($node, $this->store);

        $output = $tag->handle();

        self::assertEmpty($output);
        self::assertSame('/*[1]/*[1]', $this->store->get('_pre'));
        self::assertSame('/*[1]/*[1]/*[1]', $this->store->get('_exp'));
        self::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->store->get('_breakpoints'));
    }
}
