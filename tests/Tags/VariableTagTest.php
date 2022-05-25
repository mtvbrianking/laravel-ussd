<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tests\TestCase;
use Bmatovu\Ussd\Tags\VariableTag;

class VariableTagTest extends TestCase
{
    public function test_handle_variable()
    {
        $doc = new \DOMDocument();

        $doc->loadXML('<variable name="color" value="blue"/>');

        $xpath = new \DOMXPath($doc);

        $tag = new VariableTag($xpath, $this->cache, 'prefix', 120);

        $node = $xpath->query('/*[1]')->item(0);

        $output = $tag->handle($node);

        $color = $this->cache->get('prefix_color');

        $this->assertEmpty($output);
        $this->assertEquals('blue', $color);
    }

    public function test_proccess_variable()
    {
        $doc = new \DOMDocument();

        $doc->loadXML('<variable name="color" value="blue"/>');

        $xpath = new \DOMXPath($doc);

        $tag = new VariableTag($xpath, $this->cache, 'prefix', 120);

        $node = $xpath->query('/*[1]')->item(0);

        $nothing = $tag->process($node, '');

        $this->assertNull($nothing);
    }
}
