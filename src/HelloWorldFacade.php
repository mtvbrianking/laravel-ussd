<?php

namespace Bmatovu\HelloWorld;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string greet(string $person = 'World')
 *
 * @see \Bmatovu\HelloWorld\HelloWorld
 */
class HelloWorldFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hello-world';
    }
}
