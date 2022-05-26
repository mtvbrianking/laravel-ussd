<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Tags\ResponseTag;
use Bmatovu\Ussd\Tests\TestCase;

class ResponseTagTest extends TestCase
{
    public function testHandleResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Thank you.');

        $node = $this->getNodeByTagName('<response text="Thank you."/>', 'response');

        $tag = new ResponseTag($node, $this->cache, 'prefix', 30);

        $tag->handle();
    }

    public function testProccessResponse()
    {
        $node = $this->getNodeByTagName('<response text="Thank you."/>', 'response');

        $tag = new ResponseTag($node, $this->cache, 'prefix', 30);

        static::assertNull($tag->process(''));
    }
}
