<?php

namespace Bmatovu\HelloWorld\Tests;

use Bmatovu\HelloWorld\HelloWorld;

class HelloWorldTest extends TestCase
{
    public function test_can_greet()
    {
        $helloWorld = new HelloWorld;

        $greeting = $helloWorld->greet();

        $this->assertEquals('Hello World.', $greeting);

        $greeting = $helloWorld->greet('Dummy');

        $this->assertEquals('Hello Dummy.', $greeting);
    }
}
