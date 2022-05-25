<?php

namespace Bmatovu\HelloWorld;

class HelloWorld
{
    /**
     * Greet a person.
     *
     * @param string $person Name
     *
     * @return string Greeting
     */
    public function greet(string $person = 'World'): string
    {
        return "Hello {$person}.";
    }
}
