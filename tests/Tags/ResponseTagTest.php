<?php

namespace Bmatovu\Ussd\Tests\Tags;

use Bmatovu\Ussd\Exceptions\FlowBreakException;
use Bmatovu\Ussd\Tags\ResponseTag;
use Bmatovu\Ussd\Tests\TestCase;

class ResponseTagTest extends TestCase
{
    public function testHandleResponse()
    {
        $this->expectException(FlowBreakException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Thank you.');

        $node = $this->getNodeByTagName('<response text="Thank you."/>', 'response');

        $tag = new ResponseTag($node, $this->store);

        $tag->handle();
    }

    public function testResponseTranslation()
    {
        $this->expectException(FlowBreakException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Hello John.');

        $this->store->put('guest', 'John');

        $node = $this->getNodeByTagName('<response text="Hello {{guest}}."/>', 'response');

        $tag = new ResponseTag($node, $this->store);

        $tag->handle();
    }
}
