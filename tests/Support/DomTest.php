<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Dom;
use Bmatovu\Ussd\Tests\TestCase;

class DomTest extends TestCase
{
    public function testNodeToStr()
    {
        $tag = '<variable name="color" value="blue"/>';

        $node = $this->getNodeByTagName($tag, 'variable');

        self::assertSame($tag, Dom::render($node));
    }
}
