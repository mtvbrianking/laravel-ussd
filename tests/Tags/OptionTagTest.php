<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\OptionTag;
use Bmatovu\Ussd\Tests\TestCase;

class OptionTagTest extends TestCase
{
    public function testHandleOption()
    {
        $this->store->put('_pre', '/*[1]');
        $this->store->put('_exp', '/*[1]/*[1]');
        $this->store->put('_breakpoints', '[{"break":"resume"}]');

        $xml = <<<'XML'
<options header="Choose Gender: ">
    <option text="Male">
        <response text="Lorem ipsum"/>
    </option>
</options>
XML;

        $node = $this->getNodeByPathExp($xml, '/*[1]/*[1]');

        $tag = new OptionTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('/*[1]/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[1]/*[1]/*[1]', $this->store->get('_exp'));
        static::assertSame('[{"\/*[1]\/*[1]\/*[2]":"\/*[2]"},{"break":"resume"}]', $this->store->get('_breakpoints'));
    }
}
