<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\Tag;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

class VariableTag implements Tag
{
    use Expressions;

    protected \DOMXPath $xpath;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    public function __construct(\DOMXPath $xpath, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->xpath = $xpath;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(\DOMNode $node): ?string
    {
        $name = $node->attributes->getNamedItem('name')->nodeValue;
        $value = $node->attributes->getNamedItem('value')->nodeValue;

        $this->cache->put("{$this->prefix}_{$name}", $value, $this->ttl);

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp");

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        return '';
    }

    public function process(\DOMNode $node, ?string $answer): void
    {
    }
}
