<?php
namespace Bmatovu\HelloWorld\Tests;

use Bmatovu\HelloWorld\HelloWorldServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Add package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            HelloWorldServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'HelloWorld' => 'Bmatovu\HelloWorld\HelloWorldFacade',
        ];
    }
}
