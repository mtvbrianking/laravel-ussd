<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\UssdServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected CacheContract $cache;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setup();

        $this->cache = Container::getInstance()->make('cache')->store();

        // $this->cache->put('prefix_exp', '/*[1]');
    }

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
            UssdServiceProvider::class,
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
            // ...
        ];
    }

    protected function getNodeByTagName(string $xml, string $tagName): \DOMNode
    {
        $doc = new \DOMDocument();

        $doc->loadXML($xml);

        return $doc->getElementsByTagName($tagName)->item(0);
    }

    protected function getNodeByPathExp(string $xml, string $exp = '/*[1]'): \DOMNode
    {
        $doc = new \DOMDocument();

        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);

        return $xpath->query($exp)->item(0);
    }
}
