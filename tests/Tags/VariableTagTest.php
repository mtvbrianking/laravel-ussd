<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tests\TestCase;
use Bmatovu\Ussd\Tags\VariableTag;

class VariableTagTest extends TestCase
{
    public function test_handle_variable()
    {
        $node = $this->getNodeByTagName('<variable name="color" value="blue"/>', 'variable');

        $tag = new VariableTag($node, $this->cache, 'prefix', 120);

        $output = $tag->handle();

        $color = $this->cache->get('prefix_color');

        $this->assertEmpty($output);
        $this->assertEquals('blue', $color);
    }

    public function test_proccess_variable()
    {
        $node = $this->getNodeByTagName('<variable name="color" value="blue"/>', 'variable');

        $tag = new VariableTag($node, $this->cache, 'prefix', 120);

        $nothing = $tag->process('');

        $this->assertNull($nothing);
    }
}
