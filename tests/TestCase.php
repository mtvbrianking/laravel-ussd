<?php

namespace Bmatovu\Ussd\Tests;

use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\UssdServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected Store $store;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setup();

        $this->store = new Store('file', 120, 'ussd_wScXk');
        $this->store->flush();
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

    protected function xmlToXpath(string $xml): \DOMXPath
    {
        $doc = new \DOMDocument();

        $doc->loadXML($xml);

        return new \DOMXPath($doc);
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
