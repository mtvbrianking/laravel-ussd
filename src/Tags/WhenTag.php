<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\Tag;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

class WhenTag implements Tag
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
        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp");
        $breakpoints = (array) json_decode((string) $this->cache->get("{$this->prefix}_breakpoints"), true);

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $no_of_tags = $this->xpath->query('*', $node)->length;
        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);

        array_unshift($breakpoints, [$break => $this->incExp($pre)]);
        $this->cache->put("{$this->prefix}_breakpoints", json_encode($breakpoints), $this->ttl);

        // Log::debug("BP       -->", ['break' => $break, 'resume' => $this->incExp($pre)]);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", "{$exp}/*[1]", $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[1]"]);

        return '';
    }

    public function process(\DOMNode $node, ?string $answer): void
    {

    }
}
