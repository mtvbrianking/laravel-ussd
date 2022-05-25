<?php

namespace Bmatovu\HelloWorld\Tests;

use Bmatovu\HelloWorld\HelloWorld;
use Illuminate\Container\Container;

class HelloWorldServiceProviderTest extends TestCase
{
    public function test_hello_world_is_bound()
    {
        $app = Container::getInstance();

        $helloWorld = $app->make('hello-world');

        $this->assertInstanceOf(HelloWorld::class, $helloWorld);
    }
}
