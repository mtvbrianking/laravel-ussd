<?php

namespace Bmatovu\HelloWorld\Tests;

use Bmatovu\HelloWorld\HelloWorldFacade as HelloWorld;

class HelloWorldFacadeTest extends TestCase
{
    public function test_can_greet()
    {
        $greeting = HelloWorld::greet('Dummy');

        $this->assertEquals('Hello Dummy.', $greeting);
    }
}
