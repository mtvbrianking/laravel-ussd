<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\VariableTag;
use Bmatovu\Ussd\Tests\TestCase;

class VariableTagTest extends TestCase
{
    public function testHandleVariable()
    {
        $this->store->put('_exp', '/*[1]');

        $node = $this->getNodeByTagName('<variable name="color" value="blue"/>', 'variable');

        $tag = new VariableTag($node, $this->store);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('blue', $this->store->get('color'));
        static::assertSame('/*[1]', $this->store->get('_pre'));
        static::assertSame('/*[2]', $this->store->get('_exp'));
    }
}
